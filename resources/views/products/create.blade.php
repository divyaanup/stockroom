<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
        Create Product
        </h2>
    </x-slot>
    <div class="container my-5">
        <div class="row justify-content-center">
            <div class="col-md-6 col-lg-5">
                <div class="card">
                    <div class="card-body">
                        @if(session('success'))
                            <div class="alert alert-success alert-dismissible fade show" role="alert">
                                {{ session('success') }}
                                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                            </div>
                        @endif
                        <form method="POST" action="{{ route('products.store') }}">
                            @csrf
                            <div class="mb-3">
                                <label for="name" class="form-label">Product Name *</label>
                                <input type="text" id="name" name="name" class="form-control" required>
                            </div>
                            @error('name')
                                <div class="text-sm text-red-600">{{ $message }}</div>
                            @enderror
                            <div class="mb-3">
                                <label for="sku" class="form-label">SKU *</label>
                                <input type="text" id="sku" name="sku" class="form-control" required>
                            </div>
                            @error('sku')
                                <div class="text-sm text-red-600">{{ $message }}</div>
                            @enderror
                            <div class="mb-3">
                                <label for="price" class="form-label">Price *</label>
                                <input type="number" id="price" name="price" class="form-control" step="0.01" required>
                            </div>
                            @error('price')
                                <div class="text-sm text-red-600">{{ $message }}</div>
                            @enderror
                            <div class="mb-3">
                                <label for="stock_on_hand" class="form-label">Stock on Hand *</label>
                                <input type="number" id="stock_on_hand" name="stock_on_hand" class="form-control" required>
                            </div>
                            @error('stock_on_hand')
                                <div class="text-sm text-red-600">{{ $message }}</div>
                            @enderror
                            <div class="mb-3">
                                <label for="reorder_threshold" class="form-label">Reorder Threshold *</label>
                                <input type="number" id="reorder_threshold" name="reorder_threshold" class="form-control" required>
                            </div>
                            @error('reorder_threshold')
                                <div class="text-sm text-red-600">{{ $message }}</div>
                            @enderror
                            <div class="mb-3">
                                <label for="status" class="form-label">Status *</label>
                                <select name="status" class="form-control">
                                    <option value="active">Active</option>
                                    <option value="inactive">Inactive</option>
                                </select>
                            </div>
                            <div class="mb-3">
                                <label for="tags" class="form-label">Tags (comma-separated)</label>
                                <input type="tags" id="tags" name="tags" class="form-control">
                            </div>

                            <button type="submit" class="btn btn-primary">Save Product</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
