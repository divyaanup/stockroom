<?php
namespace App\Http\Controllers\Api;
use App\Http\Controllers\Controller;
use App\Models\Customer;
use App\Models\Order;
use App\Models\OrderLine;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use App\Events\OrderPlaced;
use App\Models\AuditLog;

class OrderController extends Controller
{
    // API to create customer
    public function storeCustomer(Request $request) {
       $customer = Customer::firstOrCreate(
            ['email' => $request->email],
            $request->only('name','phone')
        );
        return response()->json($customer);
    }

    // API to create order
    public function storeOrder(Request $request) {
        $order = Order::create([
            'customer_id' => $request->customer_id,
            'status' => 'draft',
            'total' => 0,
            'order_number' => 'ORD-' . Str::upper(Str::random(8)),
        ]);

        $total = 0;
        foreach($request->lines as $line) {
            $lineTotal = $line['quantity'] * $line['unit_price'];
            $total += $lineTotal;
            OrderLine::create([
                'order_id' => $order->id,
                'product_id' => $line['product_id'],
                'quantity' => $line['quantity'],
                'unit_price' => $line['unit_price'],
                'line_total' => $lineTotal
            ]);
        }

        $order->total = $total;
        $order->save();

        return response()->json(['order'=>$order]);
    }

    // API to place order and decrement stock
    public function placeOrder($orderId) {
        $order = Order::with('lines')->findOrFail($orderId);

        try {
            DB::transaction(function() use($order){
                foreach($order->lines as $line){
                    $product = Product::lockForUpdate()->find($line->product_id);
                    if($product->stock_on_hand < $line->quantity){
                        throw new \Exception("Insufficient stock for {$product->name}");
                    }
                    $product->stock_on_hand -= $line->quantity;
                    $product->save();
                }
                $from = $order->status;
                $order->status = 'placed';
                $order->save();
                event(new OrderPlaced($order));

                AuditLog::create([
                    'model_type' => Order::class,
                    'model_id' => $order->id,
                    'actor_id' => 1,
                    'action' => 'status_added',
                    'from_state' => $from,
                    'to_state' => $order->status,
                    'reason' => null,
                    'payload' => ['note' => 'order placed, stock reserved'],
                ]);
            });
            return response()->json(['success'=>true]);
        } catch(\Exception $e) {
            return response()->json(['error'=>$e->getMessage()]);
        }
    }
    public function store(Request $request)
    {
        $request->validate([
            'customer_id' => 'required|exists:customers,id',
            'lines' => 'required|array|min:1',
            'lines.*.product_id' => 'required|exists:products,id',
            'lines.*.quantity' => 'required|integer|min:1',
        ]);

        return DB::transaction(function () use ($request) {
            // Create order draft
            $order = Order::create([
                'customer_id' => $request->customer_id,
                'status' => 'draft',
                'total' => 0,
            ]);

            $total = 0;

            foreach ($request->lines as $line) {
                $product = Product::lockForUpdate()->find($line['product_id']);

                if ($product->stock < $line['quantity']) {
                    throw new \Exception("Insufficient stock for {$product->name}");
                }

                $lineTotal = $line['quantity'] * $product->price;
                $total += $lineTotal;

                OrderLine::create([
                    'order_id' => $order->id,
                    'product_id' => $product->id,
                    'quantity' => $line['quantity'],
                    'unit_price' => $product->price,
                    'line_total' => $lineTotal,
                ]);

                // decrement stock
                $product->decrement('stock', $line['quantity']);
            }

            $order->update([
                'status' => 'placed',
                'total' => $total,
            ]);

            return response()->json([
                'message' => 'Order placed successfully',
                'order' => $order->load('lines'),
            ], 201);
        });
    }

    // Show single order
    public function show($id)
    {
        $order = Order::with('lines.product')->findOrFail($id);
        return response()->json($order);
    }

}