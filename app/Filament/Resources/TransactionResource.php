<?php

namespace App\Filament\Resources;

use App\Filament\Resources\TransactionResource\Pages;
use App\Filament\Resources\TransactionResource\RelationManagers;
use App\Models\Product;
use App\Models\Transaction;
use Filament\Forms;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Forms\Get;
use Filament\Forms\Set;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class TransactionResource extends Resource
{
    protected static ?string $model = Transaction::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    public static function form(Form $form): Form
    {
        $products = Product::get();

        return $form
            ->schema([
                Hidden::make('id'),
                Grid::make(12)
                    ->schema([
                        Grid::make()
                            ->hiddenOn('edit')
                            ->schema([
                                Section::make('Transaction')
                                    ->schema([
                                        Repeater::make('products')
                                            // ->relationship()
                                            ->schema([
                                                Select::make('product_id')
                                                    ->label('Product')
                                                    ->reactive()
                                                    ->options(
                                                        $products->mapWithKeys(function (Product $product) {
                                                            return [$product->id => sprintf('%s (Rp. %s)', $product->name, $product->price)];
                                                        })
                                                    )
                                                    ->disableOptionsWhenSelectedInSiblingRepeaterItems()
                                                    ->searchable()
                                                    ->required()
                                                    ->afterStateUpdated(function ($state, callable $get, callable $set) {
                                                        if ($state) {
                                                            $set('quantity', 1); // Set default quantity to 1

                                                        }
                                                    }),
                                                TextInput::make('quantity')
                                                    ->required()
                                                    ->reactive()
                                                    ->numeric()
                                                    ->default(1)
                                                    ->minValue(1)
                                            ])
                                            ->columns(2)
                                            ->afterStateUpdated(function ($state, Get $get, Set $set) {
                                                static::updateTotalPrice($get, $set);
                                                \Log::info('Repeater state:', $state);
                                            })
                                            ->required(),
                                    ]),
                            ])
                            ->columnSpan(6),
                        Grid::make()
                            ->schema([
                                Section::make('Payment')
                                    ->schema([
                                        TextInput::make('total_price')
                                            ->readOnly()
                                            ->numeric()
                                            ->reactive(),
                                        TextInput::make('amount_given')
                                            ->label('Amount Given')
                                            ->numeric()
                                            ->required()
                                            ->reactive()
                                            ->minValue(0)
                                            ->live()
                                            ->afterStateUpdated(function ($state, callable $get, callable $set) {
                                                if(strlen($state) > 3) {
                                                    static::updateChange($get, $set);
                                                }
                                            }),
                                        TextInput::make('change')
                                            ->readOnly()
                                            ->live()
                                            ->numeric()
                                            ->label('Change'),
                                    ])

                            ])
                            ->columnSpan(6),
                    ]),

            ]);
    }



    protected static function updateProductPrice($productId, callable $get, callable $set)
    {
        $product = Product::find($productId);
        $quantity = $get('quantity');
        $set('quantity', $quantity);
        $price = $product->price * $quantity;
        $set('price', $price);
    }

    protected static function updateTotalPrice(callable $get, callable $set)
    {
        $selectedProducts = collect($get('products'))->filter(fn ($item) => !empty($item['product_id']) && !empty($item['quantity']));

        // Retrieve prices for all selected products
        $prices = Product::find($selectedProducts->pluck('product_id'))->pluck('price', 'id');

        // Calculate subtotal based on the selected products and quantities
        $subtotal = $selectedProducts->reduce(function ($subtotal, $product) use ($prices) {
            return $subtotal + ($prices[$product['product_id']] * $product['quantity']);
        }, 0);

        // Update the state with the new values
        $set('total_price', $subtotal);
        static::updateChange($get, $set);
    }

    protected static function updateChange(callable $get, callable $set)
    {
        $totalPrice = $get('total_price') ?: 0;
        $amountGiven = $get('amount_given') ?: 0;
        $change = $amountGiven - $totalPrice;
        $set('change', $change);
    }






    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('id')->sortable(),
                TextColumn::make('total_price')->sortable(),
                TextColumn::make('amount_given')->sortable(),
                TextColumn::make('change')->sortable(),
                TextColumn::make('created_at')->sortable(),
            ])
            ->filters([
                Tables\Filters\TrashedFilter::make(),
            ])
            ->actions([
                // Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListTransactions::route('/'),
            'create' => Pages\CreateTransaction::route('/create'),
            'edit' => Pages\EditTransaction::route('/{record}/edit'),
        ];
    }
}
