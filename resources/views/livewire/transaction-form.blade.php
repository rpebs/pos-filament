<div>
    <form>
        @foreach($products as $index => $product)
            <div class="mb-4">
                <label for="product-{{ $index }}">Product</label>
                <select wire:model="products.{{ $index }}.id" id="product-{{ $index }}" class="form-select">
                    <option value="">Select Product</option>
                    @foreach(App\Models\Product::all() as $prod)
                        <option value="{{ $prod->id }}">{{ $prod->name }}</option>
                    @endforeach
                </select>

                <label for="quantity-{{ $index }}">Quantity</label>
                <input type="number" wire:model="products.{{ $index }}.quantity" id="quantity-{{ $index }}" class="form-input" min="1">

                <label for="price-{{ $index }}">Price</label>
                <input type="text" wire:model="products.{{ $index }}.price" id="price-{{ $index }}" class="form-input" readonly>

                <button type="button" wire:click="removeProduct({{ $index }})" class="btn btn-danger">Remove</button>
            </div>
        @endforeach

        <button type="button" wire:click="addProduct" class="btn btn-primary">Add Product</button>

        <div class="mt-4">
            <strong>Total Price: ${{ number_format($totalPrice, 2) }}</strong>
        </div>
    </form>
</div>
