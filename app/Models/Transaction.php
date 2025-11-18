<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Transaction extends Model
{
    protected $fillable = [
        'customer_id', 'staff_id', 'type'
    ];

    public function items()     { return $this->hasMany(Transaction_Item::class); }
    public function customer()  { return $this->belongsTo(\App\Models\Customer::class); }
    public function staff()     { return $this->belongsTo(\App\Models\User::class, 'staff_id'); }
}
