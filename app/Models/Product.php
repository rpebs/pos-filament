<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Product extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = ['name', 'price', 'stock', 'description', 'image', 'quantity'];

    public function transactions()
    {
        return $this->belongsToMany(Transaction::class, 'product_transaction')
            ->withPivot('quantity', 'price')
            ->withTimestamps();
    }

    public function productTransaction()
    {
        return $this->belongsToMany(ProductTransaction::class, 'transaction_id');
    }
}
