<?php require_once('header.php'); ?>

<?php
$error_message = '';
$success_message = '';

function renderOrderItemRow($products, $productId = 0, $productName = '', $size = '', $color = '', $qty = 1, $unitPrice = '') {
    $html = '<tr class="item-row">';
    $html .= '<td><select class="form-control product-select" name="item_product_id[]">';
    $html .= '<option value="">Select Service</option>';
    foreach ($products as $product) {
        $selected = ($productId == $product['p_id']) ? 'selected' : '';
        $html .= '<option value="' . htmlspecialchars($product['p_id'], ENT_QUOTES) . '" data-name="' . htmlspecialchars($product['p_name'], ENT_QUOTES) . '" data-price="' . htmlspecialchars($product['p_current_price'], ENT_QUOTES) . '" ' . $selected . '>' . htmlspecialchars($product['p_name']) . '</option>';
    }
    $html .= '</select></td>';
    $html .= '<td><input type="text" class="form-control item-product-name" name="item_product_name[]" value="' . htmlspecialchars($productName, ENT_QUOTES) . '"></td>';
    $html .= '<td><input type="hidden" class="item-size" name="item_size[]" value=""><input type="hidden" class="item-color" name="item_color[]" value=""><input type="hidden" class="item-qty" name="item_qty[]" value="1">';
    $html .= '<input type="number" min="0" step="0.01" class="form-control item-unit-price" name="item_unit_price[]" value="' . htmlspecialchars((string)$unitPrice, ENT_QUOTES) . '"></td>';
    $html .= '<td><input type="text" class="form-control item-line-total" readonly value="0.00"></td>';
    $html .= '<td><button type="button" class="btn btn-danger btn-xs remove-row">Delete</button></td>';
    $html .= '</tr>';
    return $html;
}

$products = array();
$statement = $pdo->prepare("SELECT p_id, p_name, p_current_price FROM tbl_product WHERE p_is_active=1 ORDER BY p_name ASC");
$statement->execute();
$products = $statement->fetchAll(PDO::FETCH_ASSOC);

$customers = array();
$statement = $pdo->prepare("SELECT cust_id, cust_name, cust_email, cust_phone FROM tbl_customer ORDER BY cust_name ASC");
$statement->execute();
$customers = $statement->fetchAll(PDO::FETCH_ASSOC);

$customer_name = '';
$customer_email = '';
$customer_phone = '';
$selected_customer_id = '';
$payment_method = 'cod';
$payment_status = 'Pending';
$shipping_status = 'Pending';
$discount_type = 'percent';
$discount_value = 0;
$vat_percent = 0;
$paid_amount = 0;
$notes = '';

function columnExists($pdo, $table, $column) {
    $statement = $pdo->prepare("SHOW COLUMNS FROM `" . $table . "` LIKE ?");
    $statement->execute(array($column));
    return $statement->rowCount() > 0;
}

if (isset($_POST['form1'])) {
    $valid = 1;
    $selected_customer_id = trim($_POST['customer_id'] ?? '');
    $customer_name = trim($_POST['customer_name'] ?? '');
    $customer_email = trim($_POST['customer_email'] ?? '');
    $customer_phone = trim($_POST['customer_phone'] ?? '');
    $payment_method = trim($_POST['payment_method'] ?? 'cod');

    if ($selected_customer_id !== '') {
        $statement = $pdo->prepare("SELECT cust_name, cust_email, cust_phone FROM tbl_customer WHERE cust_id=?");
        $statement->execute(array($selected_customer_id));
        $customerData = $statement->fetch(PDO::FETCH_ASSOC);
        if ($customerData) {
            $customer_name = $customerData['cust_name'] ?? '';
            $customer_email = $customerData['cust_email'] ?? '';
            $customer_phone = $customerData['cust_phone'] ?? '';
        }
    }

    if ($selected_customer_id === '') {
        $valid = 0;
        $error_message .= 'Please select a customer.<br>';
    }
    $payment_status = trim($_POST['payment_status'] ?? 'Pending');
    $shipping_status = trim($_POST['shipping_status'] ?? 'Pending');
    $discount_type = trim($_POST['discount_type'] ?? 'percent');
    $discount_value = (float)($_POST['discount_value'] ?? 0);
    $vat_percent = (float)($_POST['vat_percent'] ?? 0);
    $paid_amount = (float)($_POST['paid_amount'] ?? 0);
    $notes = trim($_POST['notes'] ?? '');

    if ($customer_name === '') {
        $valid = 0;
        $error_message .= 'Customer name can not be empty<br>';
    }

    if ($customer_email === '') {
        $valid = 0;
        $error_message .= 'Customer email can not be empty<br>';
    }

    if ($customer_phone === '') {
        $valid = 0;
        $error_message .= 'Customer phone can not be empty<br>';
    }

    if (!filter_var($customer_email, FILTER_VALIDATE_EMAIL)) {
        $valid = 0;
        $error_message .= 'Customer email is invalid<br>';
    }

    $items = array();
    $productIds = $_POST['item_product_id'] ?? array();
    $productNames = $_POST['item_product_name'] ?? array();
    $sizes = $_POST['item_size'] ?? array();
    $colors = $_POST['item_color'] ?? array();
    $quantities = $_POST['item_qty'] ?? array();
    $unitPrices = $_POST['item_unit_price'] ?? array();

    if (is_array($productIds) && count($productIds) > 0) {
        foreach ($productIds as $index => $productId) {
            $productId = (int)$productId;
            if ($productId <= 0) {
                continue;
            }

            $quantity = 1;
            $unitPrice = (float)($unitPrices[$index] ?? 0);
            if ($unitPrice < 0) {
                $valid = 0;
                $error_message .= 'Each selected service must have a valid price.<br>';
                break;
            }

            $items[] = array(
                'product_id' => $productId,
                'product_name' => trim($productNames[$index] ?? ''),
                'size' => '',
                'color' => '',
                'quantity' => $quantity,
                'unit_price' => $unitPrice,
            );
        }
    }

    if (empty($items)) {
        $valid = 0;
        $error_message .= 'Please add at least one service.<br>';
    }

    if ($valid == 1) {
        $subtotal = 0;
        foreach ($items as $item) {
            $subtotal += $item['quantity'] * $item['unit_price'];
        }

        $discountAmount = ($discount_type === 'amount') ? $discount_value : ($subtotal * ($discount_value / 100));
        $discountAmount = round($discountAmount, 2);
        $vatAmount = round((($subtotal - $discountAmount) * ($vat_percent / 100)), 2);
        $grandTotal = round((($subtotal - $discountAmount) + $vatAmount), 2);
        $dueAmount = round(($grandTotal - $paid_amount), 2);

        $payment_id = 'ORD-' . date('YmdHis');

        try {
            $pdo->beginTransaction();

            $paymentColumns = array('customer_id','customer_name','customer_email','payment_date','txnid','paid_amount','card_number','card_cvv','card_month','card_year','bank_transaction_info','payment_method','payment_status','shipping_status','payment_id');
            $paymentValues = array((int)$selected_customer_id, $customer_name, $customer_email, date('Y-m-d H:i:s'), '', $paid_amount, '', '', '', '', '', $payment_method, $payment_status, $shipping_status, $payment_id);
            if (columnExists($pdo, 'tbl_payment', 'customer_phone')) { $paymentColumns[] = 'customer_phone'; $paymentValues[] = $customer_phone; }

            if (columnExists($pdo, 'tbl_payment', 'subtotal')) { $paymentColumns[] = 'subtotal'; $paymentValues[] = $subtotal; }
            if (columnExists($pdo, 'tbl_payment', 'discount_type')) { $paymentColumns[] = 'discount_type'; $paymentValues[] = $discount_type; }
            if (columnExists($pdo, 'tbl_payment', 'discount_value')) { $paymentColumns[] = 'discount_value'; $paymentValues[] = $discount_value; }
            if (columnExists($pdo, 'tbl_payment', 'discount_amount')) { $paymentColumns[] = 'discount_amount'; $paymentValues[] = $discountAmount; }
            if (columnExists($pdo, 'tbl_payment', 'vat_percent')) { $paymentColumns[] = 'vat_percent'; $paymentValues[] = $vat_percent; }
            if (columnExists($pdo, 'tbl_payment', 'vat_amount')) { $paymentColumns[] = 'vat_amount'; $paymentValues[] = $vatAmount; }
            if (columnExists($pdo, 'tbl_payment', 'grand_total')) { $paymentColumns[] = 'grand_total'; $paymentValues[] = $grandTotal; }
            if (columnExists($pdo, 'tbl_payment', 'due_amount')) { $paymentColumns[] = 'due_amount'; $paymentValues[] = $dueAmount; }
            if (columnExists($pdo, 'tbl_payment', 'notes')) { $paymentColumns[] = 'notes'; $paymentValues[] = $notes; }
            if (columnExists($pdo, 'tbl_payment', 'created_at')) { $paymentColumns[] = 'created_at'; $paymentValues[] = date('Y-m-d H:i:s'); }
            if (columnExists($pdo, 'tbl_payment', 'updated_at')) { $paymentColumns[] = 'updated_at'; $paymentValues[] = date('Y-m-d H:i:s'); }

            $paymentPlaceholders = implode(',', array_fill(0, count($paymentValues), '?'));
            $paymentSql = "INSERT INTO tbl_payment (" . implode(', ', array_map(function ($column) { return '`' . $column . '`'; }, $paymentColumns)) . ") VALUES (" . $paymentPlaceholders . ")";
            $statement = $pdo->prepare($paymentSql);
            $statement->execute($paymentValues);

            foreach ($items as $item) {
                $lineTotal = round(($item['quantity'] * $item['unit_price']), 2);
                $orderColumns = array('product_id', 'product_name', 'size', 'color', 'quantity', 'unit_price', 'payment_id');
                $orderValues = array($item['product_id'], $item['product_name'], $item['size'], $item['color'], $item['quantity'], $item['unit_price'], $payment_id);
                if (columnExists($pdo, 'tbl_order', 'line_total')) { $orderColumns[] = 'line_total'; $orderValues[] = $lineTotal; }
                $orderPlaceholders = implode(',', array_fill(0, count($orderValues), '?'));
                $orderSql = "INSERT INTO tbl_order (" . implode(', ', array_map(function ($column) { return '`' . $column . '`'; }, $orderColumns)) . ") VALUES (" . $orderPlaceholders . ")";
                $statement = $pdo->prepare($orderSql);
                $statement->execute($orderValues);
            }

            $pdo->commit();
            $success_message = 'Order is added successfully.';
            $customer_name = '';
            $customer_email = '';
            $customer_phone = '';
            $selected_customer_id = '';
            $payment_method = 'cod';
            $payment_status = 'Pending';
            $shipping_status = 'Pending';
            $discount_type = 'percent';
            $discount_value = 0;
            $vat_percent = 0;
            $paid_amount = 0;
            $notes = '';
        } catch (Exception $e) {
            $pdo->rollBack();
            $error_message = 'Unable to save order: ' . $e->getMessage();
        }
    }
}
?>

<section class="content-header">
    <div class="content-header-left">
        <h1>Add Order</h1>
    </div>
    <div class="content-header-right">
        <a href="order.php" class="btn btn-primary btn-sm">View All</a>
    </div>
</section>

<section class="content">
    <div class="row">
        <div class="col-md-12">
            <?php if ($error_message): ?>
            <div class="callout callout-danger">
                <p><?php echo $error_message; ?></p>
            </div>
            <?php endif; ?>

            <?php if ($success_message): ?>
            <div class="callout callout-success">
                <p><?php echo $success_message; ?></p>
            </div>
            <?php endif; ?>

            <form class="form-horizontal" action="" method="post" id="orderForm">
                <input type="hidden" name="form1" value="1">
                <div class="box box-info">
                    <div class="box-body">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="box box-solid">
                                    <div class="box-header with-border">
                                        <h3 class="box-title">Customer Information</h3>
                                    </div>
                                    <div class="box-body">
                                        <div class="form-group">
                                            <label class="col-sm-4 control-label">Customer Name <span>*</span></label>
                                            <div class="col-sm-8">
                                                <select name="customer_id" id="customer_select" class="form-control select2" required>
                                                    <option value="">Select Customer</option>
                                                    <?php foreach ($customers as $customer): ?>
                                                        <option value="<?php echo (int)$customer['cust_id']; ?>"
                                                            data-name="<?php echo htmlspecialchars($customer['cust_name'], ENT_QUOTES); ?>"
                                                            data-email="<?php echo htmlspecialchars($customer['cust_email'], ENT_QUOTES); ?>"
                                                            data-phone="<?php echo htmlspecialchars($customer['cust_phone'] ?? '', ENT_QUOTES); ?>"
                                                            <?php echo ($selected_customer_id == $customer['cust_id']) ? 'selected' : ''; ?>>
                                                            <?php echo htmlspecialchars($customer['cust_name']); ?>
                                                        </option>
                                                    <?php endforeach; ?>
                                                </select>
                                                <input type="hidden" name="customer_name" value="<?php echo htmlspecialchars($customer_name, ENT_QUOTES); ?>">
                                            </div>
                                        </div>
                                        <div class="form-group">
                                            <label class="col-sm-4 control-label">Customer Email <span>*</span></label>
                                            <div class="col-sm-8">
                                                <input type="email" class="form-control" name="customer_email" value="<?php echo htmlspecialchars($customer_email, ENT_QUOTES); ?>" readonly required>
                                            </div>
                                        </div>
                                        <div class="form-group">
                                            <label class="col-sm-4 control-label">Customer Phone <span>*</span></label>
                                            <div class="col-sm-8">
                                                <input type="tel" class="form-control" name="customer_phone" value="<?php echo htmlspecialchars($customer_phone, ENT_QUOTES); ?>" readonly required>
                                            </div>
                                        </div>
                                        <div class="form-group">
                                            <label class="col-sm-4 control-label">Payment Method</label>
                                            <div class="col-sm-8">
                                                <select name="payment_method" class="form-control">
                                                    <option value="cod" <?php if($payment_method=='cod') echo 'selected'; ?>>COD</option>
                                                    <option value="esewa" <?php if($payment_method=='esewa') echo 'selected'; ?>>Esewa</option>
                                                    <option value="khalti" <?php if($payment_method=='khalti') echo 'selected'; ?>>Khalti</option>
                                                </select>
                                            </div>
                                        </div>
                                        <div class="form-group">
                                            <label class="col-sm-4 control-label">Payment Status</label>
                                            <div class="col-sm-8">
                                                <select name="payment_status" class="form-control">
                                                    <option value="Pending" <?php if($payment_status=='Pending') echo 'selected'; ?>>Pending</option>
                                                    <option value="Completed" <?php if($payment_status=='Completed') echo 'selected'; ?>>Completed</option>
                                                </select>
                                            </div>
                                        </div>
                                        <div class="form-group">
                                            <label class="col-sm-4 control-label">Shipping Status</label>
                                            <div class="col-sm-8">
                                                <select name="shipping_status" class="form-control">
                                                    <option value="Pending" <?php if($shipping_status=='Pending') echo 'selected'; ?>>Pending</option>
                                                    <option value="Completed" <?php if($shipping_status=='Completed') echo 'selected'; ?>>Completed</option>
                                                </select>
                                            </div>
                                        </div>
                                        <div class="form-group">
                                            <label class="col-sm-4 control-label">Notes</label>
                                            <div class="col-sm-8">
                                                <textarea class="form-control" name="notes" rows="4"><?php echo htmlspecialchars($notes, ENT_QUOTES); ?></textarea>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="box box-solid">
                                    <div class="box-header with-border">
                                        <h3 class="box-title">Order Summary</h3>
                                    </div>
                                    <div class="box-body">
                                        <div class="form-group">
                                            <label class="col-sm-5 control-label">Discount Type</label>
                                            <div class="col-sm-7">
                                                <select id="discount_type" name="discount_type" class="form-control">
                                                    <option value="percent" <?php if($discount_type=='percent') echo 'selected'; ?>>Percentage</option>
                                                    <option value="amount" <?php if($discount_type=='amount') echo 'selected'; ?>>Amount</option>
                                                </select>
                                            </div>
                                        </div>
                                        <div class="form-group">
                                            <label class="col-sm-5 control-label">Discount Value</label>
                                            <div class="col-sm-7">
                                                <input type="number" min="0" step="0.01" class="form-control" id="discount_value" name="discount_value" value="<?php echo htmlspecialchars((string)$discount_value, ENT_QUOTES); ?>">
                                            </div>
                                        </div>
                                        <div class="form-group">
                                            <label class="col-sm-5 control-label">VAT (%)</label>
                                            <div class="col-sm-7">
                                                <input type="number" min="0" step="0.01" class="form-control" id="vat_percent" name="vat_percent" value="<?php echo htmlspecialchars((string)$vat_percent, ENT_QUOTES); ?>">
                                            </div>
                                        </div>
                                        <div class="form-group">
                                            <label class="col-sm-5 control-label">Paid Amount</label>
                                            <div class="col-sm-7">
                                                <input type="number" min="0" step="0.01" class="form-control" id="paid_amount" name="paid_amount" value="<?php echo htmlspecialchars((string)$paid_amount, ENT_QUOTES); ?>">
                                            </div>
                                        </div>
                                        <div class="form-group">
                                            <label class="col-sm-5 control-label">Subtotal</label>
                                            <div class="col-sm-7">
                                                <input type="text" class="form-control" id="subtotal" readonly value="0.00">
                                            </div>
                                        </div>
                                        <div class="form-group">
                                            <label class="col-sm-5 control-label">Discount Amount</label>
                                            <div class="col-sm-7">
                                                <input type="text" class="form-control" id="discount_amount" readonly value="0.00">
                                            </div>
                                        </div>
                                        <div class="form-group">
                                            <label class="col-sm-5 control-label">VAT Amount</label>
                                            <div class="col-sm-7">
                                                <input type="text" class="form-control" id="vat_amount" readonly value="0.00">
                                            </div>
                                        </div>
                                        <div class="form-group">
                                            <label class="col-sm-5 control-label">Grand Total</label>
                                            <div class="col-sm-7">
                                                <input type="text" class="form-control" id="grand_total" readonly value="0.00">
                                            </div>
                                        </div>
                                        <div class="form-group">
                                            <label class="col-sm-5 control-label">Due Amount</label>
                                            <div class="col-sm-7">
                                                <input type="text" class="form-control" id="due_amount" readonly value="0.00">
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="box box-solid">
                            <div class="box-header with-border">
                                <h3 class="box-title">Order Items</h3>
                            </div>
                            <div class="box-body">
                                <div class="table-responsive">
                                    <table class="table table-bordered table-striped">
                                        <thead>
                                            <tr>
                                                <th>Service</th>
                                                <th>Service Name</th>
                                                <th>Price</th>
                                                <th>Total</th>
                                                <th>Delete</th>
                                            </tr>
                                        </thead>
                                        <tbody id="itemsTableBody">
                                            <?php echo renderOrderItemRow($products); ?>
                                        </tbody>
                                    </table>
                                </div>
                                <button type="button" id="addItemBtn" class="btn btn-default btn-sm"><i class="fa fa-plus"></i> Add Item</button>
                            </div>
                        </div>
                    </div>
                    <div class="box-footer text-right">
                        <button type="submit" class="btn btn-success" name="form1">Save Order</button>
                        <a href="order.php" class="btn btn-default">Cancel</a>
                    </div>
                </div>
            </form>
        </div>
    </div>
</section>

<script>
document.addEventListener('DOMContentLoaded', function () {
    var tableBody = document.getElementById('itemsTableBody');
    var addItemBtn = document.getElementById('addItemBtn');

    function formatNumber(value) {
        return (parseFloat(value) || 0).toFixed(2);
    }

    function recalculateSummary() {
        var subtotal = 0;
        document.querySelectorAll('.item-row').forEach(function (row) {
            subtotal += parseFloat(row.querySelector('.item-line-total').value || 0);
        });

        var discountType = document.getElementById('discount_type').value;
        var discountValue = parseFloat(document.getElementById('discount_value').value) || 0;
        var discountAmount = discountType === 'amount' ? discountValue : (subtotal * (discountValue / 100));
        var vatPercent = parseFloat(document.getElementById('vat_percent').value) || 0;
        var vatAmount = (subtotal - discountAmount) * (vatPercent / 100);
        var grandTotal = (subtotal - discountAmount) + vatAmount;
        var paidAmount = parseFloat(document.getElementById('paid_amount').value) || 0;
        var dueAmount = grandTotal - paidAmount;

        document.getElementById('subtotal').value = formatNumber(subtotal);
        document.getElementById('discount_amount').value = formatNumber(discountAmount);
        document.getElementById('vat_amount').value = formatNumber(vatAmount);
        document.getElementById('grand_total').value = formatNumber(grandTotal);
        document.getElementById('due_amount').value = formatNumber(dueAmount);
    }

    function calculateRow(row) {
        var unitPrice = parseFloat(row.querySelector('.item-unit-price').value) || 0;
        row.querySelector('.item-line-total').value = formatNumber(unitPrice);
        recalculateSummary();
    }

    var customerSelect = document.getElementById('customer_select');
    var customerNameField = document.querySelector('input[name="customer_name"]');
    var customerEmailField = document.querySelector('input[name="customer_email"]');
    var customerPhoneField = document.querySelector('input[name="customer_phone"]');

    function fillCustomerDetails() {
        if (!customerSelect || !customerNameField || !customerEmailField || !customerPhoneField) {
            return;
        }

        var selectedOption = customerSelect.options[customerSelect.selectedIndex];
        if (!selectedOption || !selectedOption.value) {
            customerNameField.value = '';
            customerEmailField.value = '';
            customerPhoneField.value = '';
            return;
        }

        customerNameField.value = selectedOption.getAttribute('data-name') || '';
        customerEmailField.value = selectedOption.getAttribute('data-email') || '';
        customerPhoneField.value = selectedOption.getAttribute('data-phone') || '';
    }

    if (customerSelect) {
        customerSelect.addEventListener('change', fillCustomerDetails);
        if (window.jQuery && typeof jQuery(customerSelect).on === 'function') {
            jQuery(customerSelect).on('select2:select select2:unselect', fillCustomerDetails);
        }
        fillCustomerDetails();
    }

    function bindRow(row) {
        row.querySelector('.product-select').addEventListener('change', function () {
            var selectedOption = this.options[this.selectedIndex];
            row.querySelector('.item-product-name').value = selectedOption.getAttribute('data-name') || '';
            row.querySelector('.item-unit-price').value = selectedOption.getAttribute('data-price') || 0;
            calculateRow(row);
        });

        row.querySelector('.item-unit-price').addEventListener('input', function () {
            calculateRow(row);
        });

        row.querySelector('.remove-row').addEventListener('click', function () {
            if (document.querySelectorAll('.item-row').length > 1) {
                row.remove();
                recalculateSummary();
            } else {
                alert('At least one service is required.');
            }
        });
    }

    document.querySelectorAll('.item-row').forEach(bindRow);
    recalculateSummary();

    addItemBtn.addEventListener('click', function () {
        var newRow = document.querySelector('.item-row').cloneNode(true);
        newRow.querySelector('.product-select').value = '';
        newRow.querySelector('.item-product-name').value = '';
        newRow.querySelector('.item-qty').value = '1';
        newRow.querySelector('.item-unit-price').value = '';
        newRow.querySelector('.item-line-total').value = '0.00';
        tableBody.appendChild(newRow);
        bindRow(newRow);
        recalculateSummary();
    });

    ['discount_type', 'discount_value', 'vat_percent', 'paid_amount'].forEach(function (id) {
        document.getElementById(id).addEventListener('input', recalculateSummary);
        document.getElementById(id).addEventListener('change', recalculateSummary);
    });
});
</script>

<?php require_once('footer.php'); ?>
