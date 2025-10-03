<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
        Products
        </h2>
    </x-slot>
    <div class="container my-5">
        <div class="row">
            <div class="col-md-8">
                <div class="products">
                    <h4>Available Products</h4>
                    <div class="product-grid">
                        @foreach($products as $product)
                            <div class="product">
                                <h3>{{ $product->name }}</h3>
                                <p>SKU: {{ $product->sku }}</p>
                                <p>Price: €{{ number_format($product->price, 2) }}</p>
                                <p>Stock: {{ $product->stock_on_hand }}</p>
                                <div>
                                    <input type="number" id="qty-{{ $product->id }}" min="1" value="1">
                                    <button onclick="addToCart({{ $product->id }}, '{{ $product->name }}', {{ $product->price }})">
                                    Add
                                    </button>
                                </div>
                            </div>
                        @endforeach
                    </div>
                    <div class="justify-content-between align-items-center" style="margin-top:10px;">
                        <div>{{ $products->links() }}</div>
                    </div>
                </div>
            </div>
            <!-- Right: cart -->
            <div class="col-md-4">
                <div class="cart">
                    <h2>Your Cart</h2>
                    <ul id="cart-items" class="cart-items"></ul>
                    <strong>Total: €<span id="cart-total">0.00</span></strong>
                    <br>
                    <a href="/checkout" id="checkout-link">
                    <button class="checkout-btn">Checkout</button>
                    </a>
                </div>
            </div>
        </div>
    </div>
    <script>
        let cart = [];
        let userId = {{ auth()->id() ?? 'null' }};
        let cartKey = `cart_${userId}`;
        function addToCart(productId, name, price) {
            const qty = parseInt(document.getElementById('qty-' + productId).value);
            let existing = cart.find(item => item.product_id === productId);
            if (existing) {
                existing.quantity += qty;
                alert(`${name} quantity updated in cart`);
            } else {
                cart.push({ product_id: productId, name, quantity: qty, unit_price: price });
                alert(`${name} added to cart`);
            }
            renderCart();
            localStorage.setItem(cartKey, JSON.stringify(cart));
        }

        function renderCart() {
            let list = document.getElementById('cart-items');
            let total = 0;
            list.innerHTML = '';
            cart.forEach(item => {
                const lineTotal = item.quantity * item.unit_price;
                total += lineTotal;
                let li = document.createElement('li');
                li.innerHTML = `
                    <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:5px;">
                        <div>
                            <strong>${item.name}</strong><br>
                            Qty: ${item.quantity} × €${item.unit_price.toFixed(2)}
                        </div>
                        <div style="padding-left: 29px;">
                            €${lineTotal.toFixed(2)}
                        </div>
                        <button onclick="removeFromCart(${item.product_id})"
                                style="margin-left:10px; background:red;color:white;border:none;padding:2px 6px;border-radius:4px;cursor:pointer;">
                                ×
                        </button>
                    </div>
                `;
                //cart = []; // empty the array
                //localStorage.removeItem('cart'); // remove from localStorage
                list.appendChild(li);
            });
            document.getElementById('cart-total').textContent = total.toFixed(2);
        }

        // Restore cart if exists
        window.onload = () => {
            const saved = localStorage.getItem(cartKey);
            if (saved) {
                cart = JSON.parse(saved);
                renderCart();
            }
        };
        function removeFromCart(productId) {
            cart = cart.filter(item => item.product_id !== productId);
            renderCart();
            localStorage.setItem(cartKey, JSON.stringify(cart));
        }
        document.querySelector('.checkout-btn').addEventListener('click', function (event) {
            const total = parseFloat(document.getElementById('cart-total').textContent);
            if (total <= 0) {
                event.preventDefault();
                alert('Your cart is empty. Please add items before checking out.');
                return false;
            }
            window.location.href = document.getElementById('checkout-link').href;
        });
    </script>
</x-app-layout>