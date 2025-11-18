<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Customer extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'mobile_number',
    ];

    public function pawns()
    {
        return $this->hasMany(Pawn::class);
    }

    public function repairs()
{
    return $this->hasMany(Repair::class);
}
}

