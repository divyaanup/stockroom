<?php


namespace App\Services;


use App\Models\Order;
use App\Models\OrderLine;
use App\Models\Product;
use App\Models\AuditLog;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;
use Exception;


class OrderService
{
    //Create a order and compute totals.
    public function createDraft(int $customerId, array $lines): Order
    {
        return DB::transaction(function () use ($customerId, $lines) {
            $order = Order::create([
                'order_number' => $this->generateOrderNumber(),
                'customer_id' => $customerId,
                'status' => Order::STATUS_DRAFT,
                'total' => 0,
            ]);


            foreach ($lines as $l) {
                $lineTotal = bcmul((string)$l['quantity'], (string)$l['unit_price'], 2);
                $order->lines()->create([
                    'product_id' => $l['product_id'],
                    'quantity' => $l['quantity'],
                    'unit_price' => $l['unit_price'],
                    'line_total' => $lineTotal,
                ]);
            }

            $order->recalcTotal();

            // audit create
            AuditLog::create([
                'model_type' => Order::class,
                'model_id' => $order->id,
                'actor_id' => auth()->id(),
                'action' => 'created',
                'from_state' => null,
                'to_state' => $order->status,
                'reason' => null,
                'payload' => ['lines' => $order->lines()->get()->toArray()],
            ]);
            return $order;
        });
    }


    protected function generateOrderNumber(): string
    {
        return 'ORD-' . strtoupper(Str::random(8));
    }


    public function placeOrder(Order $order): Order
    {
        if ($order->status !== Order::STATUS_DRAFT) {
            throw new Exception('Only draft orders can be placed.');
        }


        return DB::transaction(function () use ($order) {
            // reload lines
            $order->refresh();
            $lines = $order->lines()->get();


            foreach ($lines as $line) {
                // lock product row
                $product = Product::where('id', $line->product_id)->lockForUpdate()->first();


                if (!$product) {
                throw new Exception("Product {$line->product_id} not found");
                }


                if ($product->stock_on_hand < $line->quantity) {
                throw new Exception("Insufficient stock for product {$product->id} ({$product->name}). Requested: {$line->quantity}, Available: {$product->stock_on_hand}");
                }


                // decrement stock
                $product->stock_on_hand = $product->stock_on_hand - $line->quantity;
                $product->save();
            }
            $from = $order->status;
            $order->status = Order::STATUS_PLACED;
            $order->save();

            AuditLog::create([
                'model_type' => Order::class,
                'model_id' => $order->id,
                'actor_id' => auth()->id(),
                'action' => 'status_added',
                'from_state' => $from,
                'to_state' => $order->status,
                'reason' => null,
                'payload' => ['note' => 'order placed, stock reserved'],
            ]);
            return $order;
        });
    }


    /**
    * Generic status transition helper with audit logging
    */
    public function changeStatus(Order $order, string $to, ?string $reason = null)
    {
        $allowed = [
            Order::STATUS_DRAFT,
            Order::STATUS_PLACED,
            Order::STATUS_PAID,
            Order::STATUS_FULFILLED,
            Order::STATUS_CANCELLED,
        ];


        if (!in_array($to, $allowed)) {
            throw new Exception('Invalid status');
        }


        $from = $order->status;


        $order->status = $to;
        $order->save();

        AuditLog::create([
            'model_type' => Order::class,
            'model_id' => $order->id,
            'actor_id' => auth()->id(),
            'action' => 'status_change',
            'from_state' => $from,
            'to_state' => $to,
            'reason' => $reason,
            'payload' => null,
        ]);
        return $order;
    }
}