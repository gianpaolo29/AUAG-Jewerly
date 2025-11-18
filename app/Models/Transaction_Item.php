<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Transaction_Item extends Model
{
    protected $table = 'transaction_items';
    protected $fillable = [
        'transaction_id',
        'product_id', 'pawn_item_id', 'repair_id',
        'quantity', 'unit_price', 'line_total',
    ];

    public function transaction() { return $this->belongsTo(Transaction::class); }
    public function product()     { return $this->belongsTo(\App\Models\Product::class); }
    public function pawnItem()    { return $this->belongsTo(\App\Models\Pawn::class, 'pawn_item_id'); } // adjust model if different
    public function repair()      { return $this->belongsTo(\App\Models\Repair::class); }
}
