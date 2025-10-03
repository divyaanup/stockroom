<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
        Products List
        </h2>
    </x-slot>

    <div class="container my-5">
        @if(session('success'))
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                {{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif
        <form method="GET" class="row g-2 mb-3">
            <div class="col-md-4">
                <input type="search" name="q" value="{{ request('q') }}" class="form-control" placeholder="Search name or SKU">
            </div>

            <div class="col-md-2">
                <select name="status" class="form-select">
                <option value="">All status</option>
                <option value="active" {{ request('status')=='active' ? 'selected':'' }}>Active</option>
                <option value="inactive" {{ request('status')=='inactive' ? 'selected':'' }}>Inactive</option>
                </select>
            </div>

            <div class="col-md-3">
                <input type="text" name="tag" value="{{ request('tag') }}" class="form-control" placeholder="Tag">
            </div>

            <div class="col-md-3">
                <button class="btn btn-outline-primary">Filter</button>
                <a href="{{ route('products.index') }}" class="btn btn-outline-primary">Reset</a>
            </div>
        </form>
        <table class="table table-striped table-bordered">
            <thead class="table-dark">
                <tr>
                    <th>#</th>
                    <th><a href="{{ request()->fullUrlWithQuery(['sort'=>'name','direction' => request('direction') === 'asc' ? 'desc' : 'asc']) }}">
                Name</a></th>
                    <th><a href="{{ request()->fullUrlWithQuery(['sort'=>'sku','direction' => request('direction') === 'asc' ? 'desc' : 'asc']) }}">
                    SKU</a></th>
                    <th>Price</th>
                    <th>Stock On Hand</th>
                    <th>Reorder Threshold</th>
                    <th><a href="{{ request()->fullUrlWithQuery(['sort'=>'status','direction' => request('direction') === 'asc' ? 'desc' : 'asc']) }}">
                    Status</a></th>
                    <th><a href="{{ request()->fullUrlWithQuery(['sort'=>'tags','direction' => request('direction') === 'asc' ? 'desc' : 'asc']) }}">
                    Tags</a></th>
                    <th>Created At</th>
                    <th>Edit</th>
                    <th>Delete</th>
                </tr>
            </thead>
            <tbody>
                @forelse($products as $product)
                <tr>
                    <td>{{ $product->id }}</td>
                    <td>{{ $product->name }}</td>
                    <td>{{ $product->sku }}</td>
                    <td>€{{ number_format($product->price, 2) }}</td>
                    <td>{{ $product->stock_on_hand }}</td>
                    <td>{{ $product->reorder_threshold }}</td>
                    <td>
                        <span class="badge {{ $product->status === 'active' ? 'bg-success' : 'bg-secondary' }}">
                            {{ ucfirst($product->status) }}
                        </span>
                    </td>
                    <td>
                        @if(!empty($product->tags))
                            {{ implode(', ', $product->tags) }}
                        @else
                            -
                        @endif
                    </td>
                    <td>{{ $product->created_at->format('Y-m-d') }}</td>
                    @can('edit', $product)
                    <td>
                        <a href="{{ route('products.edit', $product->id) }}" class="btn btn-sm btn-warning">Edit</a>
                    </td>
                    @endcan
                    @can('delete', $product)
                    <td>
                        <form action="{{ route('products.destroy', $product->id) }}" method="POST" class="d-inline"
                        onsubmit="return confirm('Are you sure you want to delete this product?');">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-sm btn-danger">Delete</button>
                        </form>
                    </td>
                    @endcan
                </tr>
                @empty
                <tr>
                    <td colspan="9" class="text-center">No products found.</td>
                </tr>
                @endforelse
            </tbody>
        </table>

        <div class="justify-content-between align-items-center">
            <div>{{ $products->links() }}</div>
        </div>
    </div>
</x-app-layout>