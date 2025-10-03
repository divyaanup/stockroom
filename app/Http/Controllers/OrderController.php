<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\AuditLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class OrderController extends Controller
{
    public function index()
    {
        $orders = Order::with('customer')->latest()->get();
        return view('orders.index', compact('orders'));
    }
    public function show(Order $order)
    {
        $order->load(['customer', 'lines.product']);
        return view('orders.show', compact('order'));
    }
    // Change order status (with audit logging)
    public function updateStatus(Request $request, Order $order)
    {
        $request->validate([
            'status' => 'required|in:draft,placed,paid,fulfilled,cancelled',
            'reason' => 'nullable|string|max:255',
        ]);

        $oldStatus = $order->status;
        $newStatus = $request->input('status');

        if ($oldStatus === $newStatus) {
            return back()->with('warning', 'Status is already ' . $newStatus);
        }

        // Update order
        $order->status = $newStatus;
        $order->save();

        // Log the change
        AuditLog::create([
            'model_type' => Order::class,
            'model_id'   => $order->id,
            'actor_id'   => Auth::id(),
            'action'     => 'status_change',
            'from_state' => $oldStatus,
            'to_state'   => $newStatus,
            'reason'     => $request->input('reason'),
            'payload'    => json_encode(['order_id' => $order->id]),
        ]);

        return back()->with('success', "Order status updated to {$newStatus}");
    }

}
