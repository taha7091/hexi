@extends('layouts.admin')

@section('title', 'POS Interface')

@push('styles')
<style>
.pos-container {
    height: calc(100vh - 120px);
    overflow: hidden;
}

.products-panel {
    height: 100%;
    overflow-y: auto;
    background: #f8f9fa;
    border-right: 1px solid #dee2e6;
}

.cart-panel {
    height: 100%;
    display: flex;
    flex-direction: column;
    background: white;
}

.product-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(120px, 1fr));
    gap: 10px;
    padding: 15px;
}

.product-card {
    background: white;
    border: 1px solid #dee2e6;
    border-radius: 8px;
    padding: 10px;
    text-align: center;
    cursor: pointer;
    transition: all 0.2s;
    min-height: 100px;
}

.product-card:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 8px rgba(0,0,0,0.1);
}

.product-card.out-of-stock {
    opacity: 0.5;
    cursor: not-allowed;
}

.product-image {
    width: 60px;
    height: 60px;
    object-fit: cover;
    border-radius: 4px;
    margin-bottom: 5px;
}

.product-name {
    font-size: 12px;
    font-weight: bold;
    margin-bottom: 3px;
    line-height: 1.2;
}

.product-price {
    color: #28a745;
    font-weight: bold;
    font-size: 14px;
}

.cart-items {
    flex: 1;
    overflow-y: auto;
    max-height: 400px;
}

.cart-item {
    display: flex;
    align-items: center;
    padding: 10px;
    border-bottom: 1px solid #eee;
}

.cart-item-image {
    width: 40px;
    height: 40px;
    object-fit: cover;
    border-radius: 4px;
    margin-right: 10px;
}

.cart-item-details {
    flex: 1;
}

.cart-item-name {
    font-weight: bold;
    font-size: 14px;
}

.cart-item-price {
    color: #6c757d;
    font-size: 12px;
}

.quantity-controls {
    display: flex;
    align-items: center;
    margin: 5px 0;
}

.quantity-btn {
    width: 25px;
    height: 25px;
    border: 1px solid #ddd;
    background: white;
    cursor: pointer;
    display: flex;
    align-items: center;
    justify-content: center;
}

.quantity-input {
    width: 50px;
    text-align: center;
    border: 1px solid #ddd;
    border-left: none;
    border-right: none;
    height: 25px;
}

.cart-totals {
    padding: 15px;
    border-top: 2px solid #dee2e6;
    background: #f8f9fa;
}

.total-row {
    display: flex;
    justify-content: space-between;
    margin-bottom: 5px;
}

.total-row.final {
    font-weight: bold;
    font-size: 18px;
    border-top: 1px solid #ddd;
    padding-top: 10px;
    margin-top: 10px;
}

.group-tabs {
    display: flex;
    flex-wrap: wrap;
    gap: 5px;
    padding: 10px;
    background: white;
    border-bottom: 1px solid #dee2e6;
}

.group-tab {
    padding: 8px 15px;
    background: #e9ecef;
    border: none;
    border-radius: 20px;
    cursor: pointer;
    font-size: 12px;
    transition: all 0.2s;
}

.group-tab.active {
    background: #007bff;
    color: white;
}

.search-box {
    padding: 10px;
    background: white;
    border-bottom: 1px solid #dee2e6;
}
</style>
@endpush

@section('content')
<div class="container-fluid pos-container">
    <div class="row h-100">
        <!-- Products Panel -->
        <div class="col-md-8 products-panel">
            <!-- Search -->
            <div class="search-box">
                <input type="text" class="form-control" id="productSearch" placeholder="Search products...">
            </div>

            <!-- Group Tabs -->
            <div class="group-tabs">
                <button class="group-tab active" data-group="all">All Products</button>
                @foreach($groups as $group)
                    <button class="group-tab" data-group="{{ $group->id }}" style="background-color: {{ $group->color ?? '#e9ecef' }}20">
                        @if($group->icon)
                            <i class="{{ $group->icon }}"></i>
                        @endif
                        {{ $group->name }}
                    </button>
                @endforeach
            </div>

            <!-- Products Grid -->
            <div class="product-grid" id="productsGrid">
                @foreach($products as $product)
                    <div class="product-card {{ $product->isOutOfStock() ? 'out-of-stock' : '' }}" 
                         data-product-id="{{ $product->id }}"
                         data-group-id="{{ $product->group_id }}"
                         data-name="{{ strtolower($product->name) }}"
                         data-sku="{{ strtolower($product->sku) }}">
                        @if($product->image_url)
                            <img src="{{ $product->image_url }}" alt="{{ $product->name }}" class="product-image">
                        @else
                            <div class="product-image bg-light d-flex align-items-center justify-content-center">
                                <i class="fas fa-box text-muted"></i>
                            </div>
                        @endif
                        <div class="product-name">{{ $product->name }}</div>
                        <div class="product-price">${{ number_format($product->price, 2) }}</div>
                        @if($product->isOutOfStock())
                            <small class="text-danger">Out of Stock</small>
                        @elseif($product->isLowStock())
                            <small class="text-warning">Low Stock ({{ $product->stock_quantity }})</small>
                        @endif
                    </div>
                @endforeach
            </div>
        </div>

        <!-- Cart Panel -->
        <div class="col-md-4 cart-panel">
            <div class="p-3 border-bottom">
                <h5 class="mb-0">Current Sale</h5>
                <small class="text-muted">Sale #<span id="saleNumber">{{ date('Ymd') }}-{{ str_pad(rand(1, 999), 3, '0', STR_PAD_LEFT) }}</span></small>
            </div>

            <!-- Cart Items -->
            <div class="cart-items" id="cartItems">
                <div class="text-center py-4 text-muted">
                    <i class="fas fa-shopping-cart fa-2x mb-2"></i>
                    <p>Cart is empty<br>Click on products to add them</p>
                </div>
            </div>

            <!-- Cart Totals -->
            <div class="cart-totals">
                <div class="total-row">
                    <span>Subtotal:</span>
                    <span id="subtotal">$0.00</span>
                </div>
                <div class="total-row">
                    <span>Tax:</span>
                    <span id="tax">$0.00</span>
                </div>
                <div class="total-row">
                    <span>Discount:</span>
                    <span id="discount">$0.00</span>
                </div>
                <div class="total-row final">
                    <span>Total:</span>
                    <span id="total">$0.00</span>
                </div>

                <div class="mt-3">
                    <div class="row">
                        <div class="col-6">
                            <button class="btn btn-outline-secondary btn-block" id="clearCart">
                                <i class="fas fa-trash"></i> Clear
                            </button>
                        </div>
                        <div class="col-6">
                            <button class="btn btn-success btn-block" id="processPayment" disabled>
                                <i class="fas fa-credit-card"></i> Pay
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Payment Modal -->
<div class="modal fade" id="paymentModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Process Payment</h5>
                <button type="button" class="close" data-dismiss="modal">
                    <span>&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <div class="row">
                    <div class="col-md-6">
                        <h6>Payment Methods</h6>
                        <div class="payment-methods">
                            <button class="btn btn-outline-primary btn-block mb-2 payment-method" data-method="cash">
                                <i class="fas fa-money-bill"></i> Cash
                            </button>
                            <button class="btn btn-outline-primary btn-block mb-2 payment-method" data-method="card">
                                <i class="fas fa-credit-card"></i> Card
                            </button>
                            <button class="btn btn-outline-primary btn-block mb-2 payment-method" data-method="bank">
                                <i class="fas fa-university"></i> Bank Transfer
                            </button>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <h6>Amount Details</h6>
                        <div class="form-group">
                            <label>Total Amount</label>
                            <input type="text" class="form-control" id="paymentTotal" readonly>
                        </div>
                        <div class="form-group">
                            <label>Amount Received</label>
                            <input type="number" class="form-control" id="amountReceived" step="0.01">
                        </div>
                        <div class="form-group">
                            <label>Change</label>
                            <input type="text" class="form-control" id="changeAmount" readonly>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-success" id="completeSale">Complete Sale</button>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
let cart = [];
let currentPaymentMethod = '';

$(document).ready(function() {
    // Product search
    $('#productSearch').on('keyup', function() {
        const searchTerm = $(this).val().toLowerCase();
        $('.product-card').each(function() {
            const name = $(this).data('name');
            const sku = $(this).data('sku');
            if (name.includes(searchTerm) || sku.includes(searchTerm)) {
                $(this).show();
            } else {
                $(this).hide();
            }
        });
    });

    // Group filtering
    $('.group-tab').on('click', function() {
        $('.group-tab').removeClass('active');
        $(this).addClass('active');
        
        const groupId = $(this).data('group');
        $('.product-card').each(function() {
            if (groupId === 'all' || $(this).data('group-id') == groupId) {
                $(this).show();
            } else {
                $(this).hide();
            }
        });
    });

    // Add product to cart
    $(document).on('click', '.product-card:not(.out-of-stock)', function() {
        const productId = $(this).data('product-id');
        const productName = $(this).find('.product-name').text();
        const productPrice = parseFloat($(this).find('.product-price').text().replace('$', ''));
        
        addToCart(productId, productName, productPrice);
    });

    // Payment method selection
    $('.payment-method').on('click', function() {
        $('.payment-method').removeClass('btn-primary').addClass('btn-outline-primary');
        $(this).removeClass('btn-outline-primary').addClass('btn-primary');
        currentPaymentMethod = $(this).data('method');
    });

    // Amount received calculation
    $('#amountReceived').on('input', function() {
        const total = parseFloat($('#paymentTotal').val()) || 0;
        const received = parseFloat($(this).val()) || 0;
        const change = received - total;
        $('#changeAmount').val(change >= 0 ? '$' + change.toFixed(2) : '$0.00');
    });

    // Process payment
    $('#processPayment').on('click', function() {
        if (cart.length === 0) return;
        
        const total = calculateTotal();
        $('#paymentTotal').val('$' + total.toFixed(2));
        $('#paymentModal').modal('show');
    });

    // Complete sale
    $('#completeSale').on('click', function() {
        if (!currentPaymentMethod) {
            alert('Please select a payment method');
            return;
        }
        
        const saleData = {
            items: cart,
            payment_method: currentPaymentMethod,
            total_amount: calculateTotal(),
            _token: '{{ csrf_token() }}'
        };
        
        // Here you would send the sale data to your backend
        console.log('Sale data:', saleData);
        
        // For demo purposes, just clear the cart and close modal
        clearCart();
        $('#paymentModal').modal('hide');
        alert('Sale completed successfully!');
    });

    // Clear cart
    $('#clearCart').on('click', function() {
        if (confirm('Are you sure you want to clear the cart?')) {
            clearCart();
        }
    });
});

function addToCart(productId, name, price) {
    const existingItem = cart.find(item => item.productId === productId);
    
    if (existingItem) {
        existingItem.quantity += 1;
    } else {
        cart.push({
            productId: productId,
            name: name,
            price: price,
            quantity: 1
        });
    }
    
    updateCartDisplay();
}

function updateCartDisplay() {
    const cartContainer = $('#cartItems');
    
    if (cart.length === 0) {
        cartContainer.html(`
            <div class="text-center py-4 text-muted">
                <i class="fas fa-shopping-cart fa-2x mb-2"></i>
                <p>Cart is empty<br>Click on products to add them</p>
            </div>
        `);
        $('#processPayment').prop('disabled', true);
    } else {
        let cartHtml = '';
        cart.forEach((item, index) => {
            cartHtml += `
                <div class="cart-item">
                    <div class="cart-item-details">
                        <div class="cart-item-name">${item.name}</div>
                        <div class="cart-item-price">$${item.price.toFixed(2)} each</div>
                        <div class="quantity-controls">
                            <button class="quantity-btn" onclick="updateQuantity(${index}, -1)">-</button>
                            <input type="number" class="quantity-input" value="${item.quantity}" 
                                   onchange="setQuantity(${index}, this.value)" min="1">
                            <button class="quantity-btn" onclick="updateQuantity(${index}, 1)">+</button>
                            <button class="btn btn-sm btn-outline-danger ml-2" onclick="removeFromCart(${index})">
                                <i class="fas fa-trash"></i>
                            </button>
                        </div>
                    </div>
                    <div class="text-right">
                        <strong>$${(item.price * item.quantity).toFixed(2)}</strong>
                    </div>
                </div>
            `;
        });
        cartContainer.html(cartHtml);
        $('#processPayment').prop('disabled', false);
    }
    
    updateTotals();
}

function updateQuantity(index, change) {
    cart[index].quantity += change;
    if (cart[index].quantity <= 0) {
        cart.splice(index, 1);
    }
    updateCartDisplay();
}

function setQuantity(index, quantity) {
    const qty = parseInt(quantity);
    if (qty > 0) {
        cart[index].quantity = qty;
    } else {
        cart.splice(index, 1);
    }
    updateCartDisplay();
}

function removeFromCart(index) {
    cart.splice(index, 1);
    updateCartDisplay();
}

function calculateTotal() {
    return cart.reduce((total, item) => total + (item.price * item.quantity), 0);
}

function updateTotals() {
    const subtotal = calculateTotal();
    const tax = subtotal * 0.1; // 10% tax
    const discount = 0; // No discount for now
    const total = subtotal + tax - discount;
    
    $('#subtotal').text('$' + subtotal.toFixed(2));
    $('#tax').text('$' + tax.toFixed(2));
    $('#discount').text('$' + discount.toFixed(2));
    $('#total').text('$' + total.toFixed(2));
}

function clearCart() {
    cart = [];
    updateCartDisplay();
}
</script>
@endpush
