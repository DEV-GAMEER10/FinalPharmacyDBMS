<?php
//new_sale.php
require_once '../config/database.php';
session_start();

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] == 'process_sale') {
    try {
        $pdo->beginTransaction();
        
        $customer_name = $_POST['customer_name'] ?? null;
        $customer_phone = $_POST['customer_phone'] ?? null;
        $payment_method = $_POST['payment_method'] ?? 'CASH';
        $discount = (float)($_POST['discount'] ?? 0);
        $tax = (float)($_POST['tax'] ?? 0);
        
        $items = json_decode($_POST['items'], true);
        $total_amount = 0;
        
        // Calculate total
        foreach ($items as $item) {
            $total_amount += (float)$item['quantity'] * (float)$item['price'];
        }
        
        $final_amount = $total_amount - $discount + $tax;
        
        // Insert sale record
        $sale_stmt = $pdo->prepare("INSERT INTO sales (CustomerName, CustomerPhone, TotalAmount, Discount, Tax, FinalAmount, PaymentMethod) VALUES (?, ?, ?, ?, ?, ?, ?)");
        $sale_stmt->execute([$customer_name, $customer_phone, $total_amount, $discount, $tax, $final_amount, $payment_method]);
        
        $sale_id = $pdo->lastInsertId();
        
        // Insert sale items
        $item_stmt = $pdo->prepare("INSERT INTO sales_items (SaleID, ItemID, ItemName, BatchNumber, Category, TypeForm, Quantity, UnitPrice, TotalPrice) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
        
        foreach ($items as $item) {
            $total_price = (float)$item['quantity'] * (float)$item['price'];
            $item_stmt->execute([
                $sale_id,
                $item['item_id'],
                $item['name'],
                $item['batch'],
                $item['category'] ?? '',
                $item['type_form'] ?? '',
                $item['quantity'],
                $item['price'],
                $total_price
            ]);
        }
        
        $pdo->commit();
        
        // Redirect to invoice
        header("Location: invoice.php?id=" . $sale_id);
        exit;
        
    } catch (Exception $e) {
        $pdo->rollBack();
        $error = "Error processing sale: " . $e->getMessage();
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>New Sale - Pharmacy Management</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        body { background-color: #f8f9fa; }
        .card { border-radius: 15px; box-shadow: 0 4px 6px rgba(0,0,0,0.1); }
        .item-search { position: relative; }
        .search-results { 
            position: absolute; 
            top: 100%; 
            left: 0; 
            right: 0; 
            background: white; 
            border: 1px solid #ddd; 
            border-radius: 5px; 
            max-height: 200px; 
            overflow-y: auto; 
            z-index: 1000;
            display: none;
        }
        .search-item { 
            padding: 10px; 
            cursor: pointer; 
            border-bottom: 1px solid #eee;
        }
        .search-item:hover { background-color: #f8f9fa; }
        .cart-total { 
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); 
            color: white; 
            font-size: 1.2em; 
        }
    </style>
</head>
<body>
    <div class="container-fluid mt-4">
        <!-- Header -->
        <div class="row mb-4">
            <div class="col-12 d-flex justify-content-between align-items-center">
                <h1 class="h3 text-primary"><i class="fas fa-shopping-cart"></i> New Sale</h1>
                <a href="index.php" class="btn btn-secondary">
                    <i class="fas fa-arrow-left"></i> Back to Dashboard
                </a>
            </div>
        </div>

        <?php if (isset($error)): ?>
        <div class="alert alert-danger"><?php echo $error; ?></div>
        <?php endif; ?>

        <div class="row">
            <!-- Left Side - Product Selection -->
            <div class="col-md-8">
                <div class="card">
                    <div class="card-header bg-primary text-white">
                        <h5 class="mb-0"><i class="fas fa-search"></i> Search & Add Products</h5>
                    </div>
                    <div class="card-body">
                        <!-- Search Box -->
                        <div class="item-search mb-4">
                            <input type="text" id="itemSearch" class="form-control form-control-lg" 
                                   placeholder="Search by product name, batch number, or category...">
                            <div class="search-results" id="searchResults"></div>
                        </div>

                        <!-- Cart Items Table -->
                        <div class="table-responsive">
                            <table class="table table-striped" id="cartTable">
                                <thead class="table-dark">
                                    <tr>
                                        <th>Product</th>
                                        <th>Batch</th>
                                        <th>Available</th>
                                        <th>Qty</th>
                                        <th>Price</th>
                                        <th>Total</th>
                                        <th>Action</th>
                                    </tr>
                                </thead>
                                <tbody id="cartItems">
                                    <tr id="emptyCart">
                                        <td colspan="7" class="text-center text-muted">
                                            <i class="fas fa-shopping-cart fa-3x mb-3"></i>
                                            <br>Cart is empty. Search and add products to start sale.
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Right Side - Sale Summary -->
            <div class="col-md-4">
                <div class="card">
                    <div class="card-header bg-success text-white">
                        <h5 class="mb-0"><i class="fas fa-calculator"></i> Sale Summary</h5>
                    </div>
                    <div class="card-body">
                        <form method="POST" id="saleForm">
                            <input type="hidden" name="action" value="process_sale">
                            <input type="hidden" name="items" id="cartData">

                            <!-- Customer Details -->
                            <div class="mb-3">
                                <label class="form-label">Customer Name (Optional)</label>
                                <input type="text" name="customer_name" class="form-control" placeholder="Enter customer name">
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Phone Number (Optional)</label>
                                <input type="tel" name="customer_phone" class="form-control" placeholder="Enter phone number">
                            </div>

                            <!-- Payment Method -->
                            <div class="mb-3">
                                <label class="form-label">Payment Method</label>
                                <select name="payment_method" class="form-select">
                                    <option value="CASH">Cash</option>
                                    <option value="CARD">Card</option>
                                    <option value="UPI">UPI</option>
                                </select>
                            </div>

                            <!-- Amounts -->
                            <div class="row mb-3">
                                <div class="col-6">
                                    <label class="form-label">Discount (₹)</label>
                                    <input type="number" name="discount" id="discount" class="form-control" 
                                           value="0" step="0.01" min="0">
                                </div>
                                <div class="col-6">
                                    <label class="form-label">Tax (₹)</label>
                                    <input type="number" name="tax" id="tax" class="form-control" 
                                           value="0" step="0.01" min="0">
                                </div>
                            </div>

                            <!-- Total Summary -->
                            <div class="card cart-total">
                                <div class="card-body text-center">
                                    <div class="row">
                                        <div class="col-6">
                                            <strong>Items:</strong>
                                            <div id="totalItems">0</div>
                                        </div>
                                        <div class="col-6">
                                            <strong>Quantity:</strong>
                                            <div id="totalQty">0</div>
                                        </div>
                                    </div>
                                    <hr class="text-white">
                                    <div class="row">
                                        <div class="col-12">
                                            <strong>Subtotal:</strong>
                                            <div class="h4" id="subtotal">₹0.00</div>
                                        </div>
                                    </div>
                                    <div class="row">
                                        <div class="col-6">
                                            <small>Discount:</small>
                                            <div id="discountAmount">₹0.00</div>
                                        </div>
                                        <div class="col-6">
                                            <small>Tax:</small>
                                            <div id="taxAmount">₹0.00</div>
                                        </div>
                                    </div>
                                    <hr class="text-white">
                                    <div class="h3" id="finalTotal">₹0.00</div>
                                </div>
                            </div>

                            <!-- Action Buttons -->
                            <div class="d-grid gap-2 mt-3">
                                <button type="submit" class="btn btn-success btn-lg" id="processSale" disabled>
                                    <i class="fas fa-check"></i> Process Sale
                                </button>
                                <button type="button" class="btn btn-warning" onclick="clearCart()">
                                    <i class="fas fa-trash"></i> Clear Cart
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script>
        let cart = [];

        $(document).ready(function() {
            // Search functionality
            $('#itemSearch').on('input', function() {
                const query = $(this).val();
                if (query.length >= 2) {
                    searchProducts(query);
                } else {
                    $('#searchResults').hide();
                }
            });

            // Update totals when discount or tax changes
            $('#discount, #tax').on('input', updateTotals);
        });

        function searchProducts(query) {
            $.ajax({
                url: 'search_products.php',
                method: 'POST',
                data: { query: query },
                dataType: 'json',
                success: function(products) {
                    let html = '';
                    products.forEach(function(product) {
                        html += `
                            <div class="search-item" onclick="addToCart(${product.ItemID}, '${product.ItemName}', '${product.BatchNumber}', ${product.SellingPrice || product.CostPrice}, ${product.Quantity}, '${product.Category}', '${product.TypeForm}')">
                                <strong>${product.ItemName}</strong> - ${product.Category}
                                <br><small class="text-muted">Batch: ${product.BatchNumber} | Available: ${product.Quantity} | Price: ₹${product.SellingPrice || product.CostPrice}</small>
                            </div>
                        `;
                    });
                    $('#searchResults').html(html).show();
                }
            });
        }

        function addToCart(itemId, name, batch, price, available, category, typeForm) {
            // Check if item already exists in cart
            const existingItem = cart.find(item => item.item_id === itemId && item.batch === batch);
            
            if (existingItem) {
                if (existingItem.quantity < available) {
                    existingItem.quantity++;
                } else {
                    alert('Cannot add more items. Stock limit reached.');
                    return;
                }
            } else {
                cart.push({
                    item_id: itemId,
                    name: name,
                    batch: batch,
                    category: category,
                    type_form: typeForm,
                    price: price,
                    quantity: 1,
                    available: available
                });
            }

            updateCartDisplay();
            $('#itemSearch').val('');
            $('#searchResults').hide();
        }

        function updateCartDisplay() {
            const tbody = $('#cartItems');
            tbody.empty();

            if (cart.length === 0) {
                tbody.html(`
                    <tr id="emptyCart">
                        <td colspan="7" class="text-center text-muted">
                            <i class="fas fa-shopping-cart fa-3x mb-3"></i>
                            <br>Cart is empty. Search and add products to start sale.
                        </td>
                    </tr>
                `);
                $('#processSale').prop('disabled', true);
            } else {
                cart.forEach(function(item, index) {
                    const total = item.quantity * item.price;
                    tbody.append(`
                        <tr>
                            <td><strong>${item.name}</strong></td>
                            <td>${item.batch}</td>
                            <td><span class="badge bg-info">${item.available}</span></td>
                            <td>
                                <div class="input-group" style="width: 100px;">
                                    <button class="btn btn-outline-secondary btn-sm" type="button" onclick="updateQuantity(${index}, -1)">-</button>
                                    <input type="number" class="form-control form-control-sm text-center" value="${item.quantity}" min="1" max="${item.available}" onchange="setQuantity(${index}, this.value)">
                                    <button class="btn btn-outline-secondary btn-sm" type="button" onclick="updateQuantity(${index}, 1)">+</button>
                                </div>
                            </td>
                            <td>₹${item.price}</td>
                            <td><strong>₹${total.toFixed(2)}</strong></td>
                            <td>
                                <button class="btn btn-danger btn-sm" onclick="removeFromCart(${index})">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </td>
                        </tr>
                    `);
                });
                $('#processSale').prop('disabled', false);
            }

            updateTotals();
        }

        function updateQuantity(index, change) {
            const item = cart[index];
            const newQuantity = item.quantity + change;
            
            if (newQuantity >= 1 && newQuantity <= item.available) {
                item.quantity = newQuantity;
                updateCartDisplay();
            }
        }

        function setQuantity(index, quantity) {
            const item = cart[index];
            const newQuantity = parseInt(quantity);
            
            if (newQuantity >= 1 && newQuantity <= item.available) {
                item.quantity = newQuantity;
                updateCartDisplay();
            }
        }

        function removeFromCart(index) {
            cart.splice(index, 1);
            updateCartDisplay();
        }

        function clearCart() {
            cart = [];
            updateCartDisplay();
        }

        function updateTotals() {
            let subtotal = 0;
            let totalItems = cart.length;
            let totalQty = 0;

            cart.forEach(function(item) {
                subtotal += item.quantity * item.price;
                totalQty += item.quantity;
            });

            const discount = parseFloat($('#discount').val()) || 0;
            const tax = parseFloat($('#tax').val()) || 0;
            const finalTotal = subtotal - discount + tax;

            $('#totalItems').text(totalItems);
            $('#totalQty').text(totalQty);
            $('#subtotal').text('₹' + subtotal.toFixed(2));
            $('#discountAmount').text('₹' + discount.toFixed(2));
            $('#taxAmount').text('₹' + tax.toFixed(2));
            $('#finalTotal').text('₹' + finalTotal.toFixed(2));

            // Update hidden form data
            $('#cartData').val(JSON.stringify(cart));
        }
    </script>
</body>
</html>