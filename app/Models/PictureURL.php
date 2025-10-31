<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PictureUrl extends Model
{
    protected $fillable = ['url', 'alt', 'is_primary'];
    protected $casts = ['is_primary' => 'boolean'];

    public function imageable()
    {
        return $this->morphTo();
    }
}
