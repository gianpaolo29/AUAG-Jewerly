<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Repair extends Model
{
    protected $fillable = [
        'price',
        'description',
        'customer_id',
        'status',
    ];


    public function customer() 
    { 
        return $this->belongsTo(\App\Models\User::class, 'customer_id'); 
    }
    public function images()   
    { 
        return $this->hasMany(\App\Models\RepairImage::class); 
    }


}
