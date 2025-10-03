<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
        Orders
        </h2>
    </x-slot>
    <div class="container my-5">
        <h2>Order #{{ $order->order_number }}</h2>
        <p><strong>Customer:</strong> {{ $order->customer->name }}</p>
        <p><strong>Email:</strong> {{ $order->customer->email }}</p>
        <p><strong>Status:</strong> {{ ucfirst($order->status) }}</p>
        <p><strong>Total:</strong> €{{ number_format($order->total, 2) }}</p>

        <h4>Items</h4>
        <table class="table table-bordered">
            <thead>
                <tr>
                    <th>Product</th>
                    <th>Qty</th>
                    <th>Unit Price (€)</th>
                    <th>Line Total (€)</th>
                </tr>
            </thead>
            <tbody>
                @foreach($order->lines as $line)
                <tr>
                    <td>{{ $line->product->name }}</td>
                    <td>{{ $line->quantity }}</td>
                    <td>{{ number_format($line->unit_price, 2) }}</td>
                    <td>{{ number_format($line->line_total, 2) }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>

        <h4>Change Status</h4>
        @if(session('success'))
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                {{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif
        @if(session('warning'))
            <div class="alert alert-warning alert-dismissible fade show" role="alert">
                {{ session('warning') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif
        <form action="{{ route('orders.updateStatus', $order) }}" method="POST" class="mt-3">
            @csrf
            @method('PATCH')

            <div class="mb-3">
                <label for="status" class="form-label">New Status</label>
                <select name="status" id="status" class="form-select">
                    @foreach(['draft','placed','paid','fulfilled','cancelled'] as $status)
                        <option value="{{ $status }}" @selected($order->status === $status)>
                            {{ ucfirst($status) }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="mb-3">
                <label for="reason" class="form-label">Reason (optional)</label>
                <input type="text" name="reason" id="reason" class="form-control">
            </div>

            <button type="submit" class="btn btn-primary">Update Status</button>
        </form>
    </div>
</x-app-layout>