<?php

namespace App\Filament\Resources\TransactionResource\Pages;

use App\Filament\Resources\TransactionResource;
use App\Filament\Widgets\TransactionTable;
use App\Models\Product;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditTransaction extends EditRecord
{
    protected static string $resource = TransactionResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make()
                ->after(function () {
                    $transaction = $this->record;

                    foreach ($transaction->products as $product) {
                        $product->increment('quantity', $product->pivot->quantity);
                    }
                })
        ];
    }

    protected function mutateFormDataBeforeSave(array $data): array
    {
        $totalPrice = 0;

        foreach ($data['products'] as &$product) {
            $productModel = Product::find($product['product_id']);
            $totalPrice += $product['quantity'] * $product['price'];

            if ($productModel->quantity < $product['quantity']) {
                throw new \Exception("Stok produk " . $productModel->name . " tidak mencukupi.");
            }
        }

        $data['total_price'] = $totalPrice;
        $data['change'] = $data['amount_given'] - $totalPrice;

        return $data;
    }

    protected function afterSave(): void
    {
        $transaction = $this->record;

        foreach ($transaction->products as $product) {
            $originalQuantity = $transaction->getOriginal('products')[$product->id]['pivot_quantity'] ?? 0;
            $product->increment('quantity', $originalQuantity);
            $product->decrement('quantity', $product->pivot->quantity);
        }
    }
    public function getFooterWidgetsColumns(): array|int|string
    {
        return 1;
    }
    protected function getFooterWidgets(): array
    {
        // dd($this->record->id);
        return [
            TransactionTable::make(
                [
                    'transaction_id' => $this->record->id
                ]
            )
        ];
    }
}
