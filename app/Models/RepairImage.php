<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
    
class RepairImage extends Model
{
    protected $fillable = [
        'repair_id',
        'url',
    ];

    public function repair()
{
    return $this->belongsTo(\App\Models\Repair::class);
}



}
