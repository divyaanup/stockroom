<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
        Checkout
        </h2>
    </x-slot>
    <div class="container my-5">
        <div class="row">
            <div class="col-md-6">
                <form id="checkout-form" method="POST">
                    <div class="mb-3">
                        <label>Name *</label>
                        <input type="text" id="name" name="name" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label>Email *</label>
                        <input type="email" id="email" name="email" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label>Phone</label>
                        <input type="text" id="phone" name="phone" class="form-control">
                    </div>

                    <h3>Order Summary</h3>
                    <ul id="cart-items"></ul>
                    <p style="font-weight: bold; float:right;">Total: €<span id="cart-total">0.00</span></p>

                    <button type="submit" class="btn btn-success" style="margin-top:35px;">Place Order</button>
                </form>
            </div>
        </div>    
    </div>
    <script>
    let userId = {{ auth()->id() ?? 'null' }};
    let cartKey = `cart_${userId}`;
    let cart = JSON.parse(localStorage.getItem(cartKey) || '[]');

    function renderCart() {
        let list = document.getElementById('cart-items');
        let total = 0;
        list.innerHTML = '';
        cart.forEach(item => {
            const lineTotal = item.quantity * item.unit_price;
            total += lineTotal;
            let li = document.createElement('li');
            li.innerHTML = `
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                    <strong>${item.name}</strong><br>
                    <small>${item.quantity} × €${item.unit_price.toFixed(2)}</small>
                    </div>
                    <div>
                    <span class="fw-bold">€${lineTotal.toFixed(2)}</span>
                    </div>
                </div>
                `;
            list.appendChild(li);
        });
        document.getElementById('cart-total').textContent = total.toFixed(2);
    }

    renderCart();

    document.getElementById('checkout-form').addEventListener('submit', async function(e) {
        e.preventDefault();

        if(cart.length === 0) {
            alert("Cart is empty!");
            return;
        }

        const customer = {
            name: document.getElementById('name').value,
            email: document.getElementById('email').value,
            phone: document.getElementById('phone').value
        };

        try {
            // 1. Create customer
            const custRes = await fetch('/api/customers', {
                method: 'POST',
                headers: {'Content-Type':'application/json'},
                body: JSON.stringify(customer)
            });
            const cust = await custRes.json();

            // 2. Create order
            const orderRes = await fetch('/api/order', {
                method: 'POST',
                headers: {'Content-Type':'application/json'},
                body: JSON.stringify({ customer_id: cust.id, lines: cart })
            });
            const order = await orderRes.json();

            if(order.error) {
                alert(order.error);
                return;
            }

            // 3. Place order (decrement stock)
            const placeRes = await fetch(`/api/orders/${order.order.id}/place`, {
                method: 'POST',
                headers: {'Content-Type':'application/json'}
            });
            const placed = await placeRes.json();

            if(placed.error) {
                alert('Stock error: ' + placed.error);
            } else {
                alert('Order placed successfully!');
                localStorage.removeItem(cartKey);
                window.location.href = '/orders';
            }

        } catch(err) {
            console.error(err);
            alert("Something went wrong. Please try again.");
        }
    });
    </script>
</x-app-layout>