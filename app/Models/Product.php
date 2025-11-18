<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Product extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'category_id',
        'description',
        'price',
        'quantity',
    ];

    protected $casts = [
        'price'    => 'decimal:2',
        'quantity' => 'integer',
    ];

    public function category()
    {
        return $this->belongsTo(Category::class, 'category_id');
        return $this->pictures()->orderByDesc('is_primary')->orderBy('id');
    }

     
    public function pictures()
    {
        return $this->morphMany(\App\Models\PictureUrl::class, 'imageable')
            ->orderByDesc('is_primary')->orderBy('id');
    }

 
    public function primaryPicture()
    {
        return $this->morphOne(\App\Models\PictureUrl::class, 'imageable')
            ->where('is_primary', true);
    }

        public function getMainImageUrlAttribute(): string
    {
        
        $first = $this->pictureUrls->first();
        return $first?->url ?? asset('placeholder.jpg');
    }
    
}


