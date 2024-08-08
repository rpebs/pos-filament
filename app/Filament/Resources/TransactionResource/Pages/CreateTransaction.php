<?php

namespace App\Filament\Resources\TransactionResource\Pages;

use App\Filament\Resources\TransactionResource;
use App\Models\Product;
use App\Models\ProductTransaction;
use App\Models\Transaction;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class CreateTransaction extends CreateRecord
{
    protected static string $resource = TransactionResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $products = $data['products'];

        // Check if stock is sufficient
        foreach ($products as $product) {
            $productModel = Product::find($product['product_id']);
            if (!$productModel || $productModel->quantity < $product['quantity']) {


                Notification::make()
                    ->title('Transaction Failed')
                    ->danger()
                    ->body('Stok produk ' . $productModel->name . ' tidak mencukupi.')
                    ->send();

                $this->halt();

                // throw new \Exception(
                //     'Stok produk ' . $productModel->name . ' tidak mencukupi.'
                // );
            }
        }

        return $data;
    }


    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }

    protected function afterCreate(): void
    {
        // Ambil data produk dari form
        $products = $this->form->getState()['products'];

        // Pastikan record adalah instance dari Transaction
        // if (!$record instanceof Transaction) {
        //     return;
        // }

        // dd($products);

        DB::transaction(function () use ($products) {
            // Loop melalui setiap produk dan tambahkan ke tabel pivot
            foreach ($products as $product) {
                $productModel = Product::find($product['product_id']);
                $price = $productModel->price;

                $this->record->products()->attach($product['product_id'], [
                    'quantity' => $product['quantity'],
                    'price' => $price * $product['quantity'],
                ]);

                // ProductTransaction::create([
                //     'transaction_id' => $this->record->id,
                //     'product_id' => $product['product_id'],
                //     'quantity' => $product['quantity'],
                //     'price' => $price * $product['quantity'],
                // ]);

                // Mengupdate stok produk
                if ($productModel) {

                    $productModel->quantity -= $product['quantity'];
                    $productModel->save();
                }

                DB::commit();
            }
        });
    }
}
