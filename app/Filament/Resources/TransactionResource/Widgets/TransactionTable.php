<?php

namespace App\Filament\Widgets;

use App\Models\ProductTransaction;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;

class TransactionTable extends BaseWidget
{
    public $transaction_id ;

    public function mount($transaction_id)
    {
        $this->transaction_id = $transaction_id;
    }
    public function table(Table $table): Table
    {
        return $table
            ->query(
               ProductTransaction::query()->where('transaction_id', $this->transaction_id),
            )
            ->columns([
                Tables\Columns\TextColumn::make('id'),
                Tables\Columns\TextColumn::make('products.name'),
                Tables\Columns\TextColumn::make('quantity'),
                Tables\Columns\TextColumn::make('price'),
            ]);
    }
}
