<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;

class Pawn extends Model
{
    use HasFactory;

    protected $table = 'pawn_items';

    protected $fillable = [
        'customer_id',
        'title',
        'description',
        'price',
        'interest_cost', // base interest (if any)
        'due_date',
        'status',
    ];

    protected $casts = [
        'due_date'      => 'date',
        'price'         => 'decimal:2',
        'interest_cost' => 'decimal:2',
    ];

    // Expose computed fields
    protected $appends = [
        'is_overdue',
        'months_overdue',
        'overdue_interest',
        'total_due',
        'payable_today',
    ];

    /* ----------------------------
     | Relationships
     |-----------------------------*/
    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }

    /* ----------------------------
     | Scopes
     |-----------------------------*/
    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    public function scopeOverdue($q)
    {
        return $q->where('status', 'active')
                 ->whereDate('due_date', '<', now()->toDateString());
    }

    /* ----------------------------
     | Helpers
     |-----------------------------*/
    public function isOverdue(): bool
    {
        return $this->status === 'active'
            && $this->due_date instanceof Carbon
            && now()->timezone(config('app.timezone'))
                ->greaterThan($this->due_date->copy()->endOfDay());
    }

    /* ----------------------------
     | Accessors (appended)
     |-----------------------------*/

    // Boolean flag
    public function getIsOverdueAttribute(): bool
    {
        return $this->isOverdue();
    }

    // Number of STARTED months overdue (ceil-by-started-month rule)
    public function getMonthsOverdueAttribute(): int
    {
        if (! $this->isOverdue() || ! $this->due_date) {
            return 0;
        }

        $now = now()->startOfDay();
        $due = $this->due_date->copy()->startOfDay();

        // Full month difference
        $full = $due->diffInMonths($now);

        // If there's any extra days beyond the full months, count as another started month
        $bumped = $due->copy()->addMonths($full);
        if ($bumped->lt($now)) {
            $full++;
        }

        // Once past due even 1 day, counts as 1 month
        return max(1, $full);
    }

    // 3% per started overdue month based on principal (price)
    public function getOverdueInterestAttribute(): float
    {
        $rate = 0.03; // 3%/month
        $months = $this->months_overdue;
        $principal = (float) $this->price;

        return round($principal * $rate * $months, 2);
    }

    // Total due today = principal + base interest + overdue interest
    public function getTotalDueAttribute(): float
    {
        $principal    = (float) $this->price;
        $baseInterest = (float) $this->interest_cost;
        $overdue      = (float) $this->overdue_interest;

        return round($principal + $baseInterest + $overdue, 2);
    }

    // Alias for UI clarity
    public function getPayableTodayAttribute(): float
    {
        return $this->total_due;
    }

    // (Optional) Keep a simple total amount without overdue
    public function getTotalAmountAttribute(): float
    {
        return round((float) $this->price + (float) $this->interest_cost, 2);
    }

        public function transactions()
    {
        return $this->hasMany(Transaction::class, 'pawn_item_id');
    }
}
