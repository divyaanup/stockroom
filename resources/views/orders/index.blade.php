<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
        Orders
        </h2>
    </x-slot>
    <div class="container my-5">
        <h2 class="mb-4">Order List</h2>
        <table class="table table-bordered table-hover align-middle">
            <thead class="table-dark">
                <tr>
                    <th>#</th>
                    <th>Order No</th>
                    <th>Customer</th>
                    <th>Email</th>
                    <th>Status</th>
                    <th>Total (€)</th>
                    <th>Created At</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($orders as $order)
                    <tr>
                        <td>{{ $order->id }}</td>
                        <td><strong>{{ $order->order_number }}</strong></td>
                        <td>{{ $order->customer->name ?? 'N/A' }}</td>
                        <td>{{ $order->customer->email ?? 'N/A' }}</td>
                        <td>
                            <span class="badge 
                                @if($order->status == 'draft') bg-secondary 
                                @elseif($order->status == 'placed') bg-info
                                @elseif($order->status == 'paid') bg-warning
                                @elseif($order->status == 'fulfilled') bg-success
                                @elseif($order->status == 'cancelled') bg-danger
                                @endif">
                                {{ ucfirst($order->status) }}
                            </span>
                        </td>
                        <td>€{{ number_format($order->total, 2) }}</td>
                        <td>{{ $order->created_at->format('Y-m-d H:i') }}</td>
                        <td>
                            <a href="{{ route('orders.show', $order) }}" class="btn btn-sm btn-primary">
                                View
                            </a>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" class="text-center">No orders found</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</x-app-layout>