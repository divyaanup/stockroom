<?php

namespace App\Http\Controllers;

use App\Models\Product;
use Illuminate\Http\Request;
use App\Jobs\ImportProductsJob;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;
use App\Models\Customer;
use App\Models\Order;
use App\Models\OrderLine;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

class ProductController extends Controller
{
    use AuthorizesRequests;
    public function dashboard()
    {
        $products = Product::where('status', 'active')->paginate(9);
        return view('products.dashboard', ['products' => $products]);
    }
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $allowedSorts = ['name','sku','price','status','tag'];
        $sort = in_array($request->input('sort'), $allowedSorts) ? $request->input('sort') : 'name';
        $direction = $request->input('direction', 'asc') === 'desc' ? 'desc' : 'asc';
        $per_page = $request->input('per_page', 10);

        $products = Product::query()
            ->search($request->input('q'))
            ->status($request->input('status'))
            ->tag($request->input('tag'))
            ->orderBy($sort, $direction)
            ->paginate($per_page)
            ->withQueryString();
        return view('products.index', ['products' => $products]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $this->authorize('create', Product::class);
        return view('products.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'sku' => 'required|string|unique:products',
            'price' => 'required|numeric|min:0',
            'stock_on_hand' => 'required|integer|min:0',
            'reorder_threshold' => 'required|integer|min:0',
            'status' => 'required'
        ]);
        $validated['tags'] = $request->input('tags')? explode(',', $request->input('tags'))
    : [];
        Product::create($validated);

        return redirect()->route('products.create')->with('success', 'Product added successfully.');
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Product $product)
    {
        return view('products.edit', ['product' => $product]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Product $product)
    {
       $validated = $request->validate([
            'name' => 'required|string|max:255',
            'sku' => 'required|string|unique:products,sku,' . $product->id,
            'price' => 'required|numeric|min:0',
            'stock_on_hand' => 'required|integer|min:0',
            'reorder_threshold' => 'required|integer|min:0',
            'status' => 'required',
            'tags' => 'nullable|string',
        ]);
    
        // Convert comma-separated tags to array
        $validated['tags'] = $request->input('tags') 
            ? array_map('trim', explode(',', $request->input('tags'))) 
            : [];
    
        $product->update($validated);
    
        return redirect()->route('products.index')->with('success', 'Product updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Product $product)
    {
        /*if(auth()->user()->cannot('delete', $product)) {
            abort(403);
        }*/
        $product->delete();
        return redirect()->route('products.index')->with('success', 'Product deleted successfully.');
    }
    public function import()
    {
        $this->authorize('import', Product::class);
        return view('products.import');
    }
    public function upload(Request $request)
    {
        $this->authorize('upload', Product::class);
        $request->validate([
            'csv_file' => 'required|mimes:csv,txt|max:10240',
        ]);
        if (!Storage::exists('imports')) {
            Storage::makeDirectory('imports');
        }

        $path = $request->file('csv_file')->store('private/imports');
        // Dispatch queued job
        ImportProductsJob::dispatch($path, 1);

        return back()->with('success', 'CSV uploaded successfully. Processing will run in the background.');
    }
    public function checkout()
    {
        return view('products.checkout');
    }

    public function placeOrder(Request $request)
    {
        $cart = $request->input('cart', []);

        if (empty($cart)) {
            return back()->with('error', 'Cart is empty');
        }

        DB::beginTransaction();
        try {
            $customer = Customer::firstOrCreate(
                ['email' => $request->email],
                $request->only('name','phone')
            );

            $order = Order::create([
                'customer_id' => $customer->id,
                'status' => 'draft',
                'total' => 0
            ]);

            $total = 0;
            foreach($cart as $item){
                $lineTotal = $item['quantity'] * $item['unit_price'];
                $total += $lineTotal;

                OrderLine::create([
                    'order_id' => $order->id,
                    'product_id' => $item['product_id'],
                    'quantity' => $item['quantity'],
                    'unit_price' => $item['unit_price'],
                    'line_total' => $lineTotal
                ]);
            }

            $order->total = $total;
            $order->status = 'placed';
            $order->save();

            // Decrement stock safely
            foreach($order->lines as $line){
                $product = Product::lockForUpdate()->find($line->product_id);
                if($product->stock_on_hand < $line->quantity){
                    throw new \Exception("Insufficient stock for {$product->name}");
                }
                $product->stock_on_hand -= $line->quantity;
                $product->save();
            }

            DB::commit();
            return redirect()->route('checkout')->with('success', 'Order placed successfully!');

        } catch(\Exception $e) {
            DB::rollBack();
            return back()->with('error', $e->getMessage());
        }
    }
}
