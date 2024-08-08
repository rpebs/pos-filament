<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Transaction extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = ['total_price', 'amount_given', 'change'];

    public function products()
    {
        return $this->belongsToMany(Product::class, 'product_transaction')
            ->withPivot('quantity', 'price')
            ->withTimestamps();
    }
}
