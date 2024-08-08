<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\Pivot;

class ProductTransaction extends Pivot
{
    protected $table = 'product_transaction';

    protected $fillable = ['product_id', 'transaction_id', 'quantity', 'price', 'created_by'];

    public function products()
    {
        return $this->hasOne(Product::class, 'id', 'product_id');
    }
}
