<?php require_once('header.php'); ?>

<?php
if (!isset($_GET['id'])) {
    header('location: order.php');
    exit;
}

$statement = $pdo->prepare("SELECT * FROM tbl_payment WHERE id=?");
$statement->execute(array($_GET['id']));
$order = $statement->fetch(PDO::FETCH_ASSOC);

if (!$order) {
    header('location: order.php');
    exit;
}

$statement = $pdo->prepare("SELECT * FROM tbl_order WHERE payment_id=? ORDER BY id ASC");
$statement->execute(array($order['payment_id']));
$items = $statement->fetchAll(PDO::FETCH_ASSOC);
?>

<section class="content-header">
    <div class="content-header-left">
        <h1>Invoice</h1>
    </div>
    <div class="content-header-right">
        <button class="btn btn-primary btn-sm" onclick="window.print()">Print Invoice</button>
            <div class="content-header-right">
        <a href="order.php" class="btn btn-primary btn-sm">Back</a>
    </div>
    </div>
</section>

<div class="invoice-print-area">
    <section class="content">
        <div class="row">
        <?php for ($copyIndex = 0; $copyIndex < 2; $copyIndex++): ?>
            <div class="col-sm-6">
                <div class="invoice-card">
                    <div class="invoice-watermark" aria-hidden="true">
                        <img src="../assets/images/placeholder.png" alt="">
                    </div>
                    <div class="invoice-header">
                        <div>
                            <h2>KOSHI SUPPLIER</h2>
                            <p>
                                Samakhushi, Kathmandu, Nepal<br>
                                Phone: +977-9810159923 / +977-9801095151<br>
                                Email: info@koshisupplier.com.np<br>
                                VAT No: 301908717
                            </p>
                        </div>
                        <div class="invoice-meta">
                            <h3>Invoice</h3>
                            <p>
                                <strong>No:</strong> <?php echo htmlspecialchars($order['payment_id']); ?><br>
                                <strong>Date:</strong> <?php echo htmlspecialchars($order['payment_date']); ?><br>
                                <strong>Status:</strong> <?php echo htmlspecialchars($order['payment_status']); ?><br>
                                <strong>Method:</strong> <?php echo htmlspecialchars($order['payment_method']); ?>
                            </p>
                        </div>
                    </div>

                    <div class="invoice-blocks">
                        <div class="invoice-block-full">
                            <h4>Bill To</h4>
                            <p>
                                <strong><?php echo htmlspecialchars($order['customer_name']); ?></strong> | 
                                <?php echo htmlspecialchars($order['customer_phone']); ?> | 
                                <?php echo htmlspecialchars($order['customer_email']); ?>
                            </p>
                        </div>
                    </div>

                    <div class="table-responsive invoice-table">
                        <table class="table table-bordered table-striped">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>Product</th>
                                    <th>Size</th>
                                    <th>Color</th>
                                    <th>Qty</th>
                                    <th>Unit Price</th>
                                    <th>Total</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php $i = 1; foreach ($items as $item): ?>
                                <tr>
                                    <td><?php echo $i++; ?></td>
                                    <td><?php echo htmlspecialchars($item['product_name']); ?></td>
                                    <td><?php echo htmlspecialchars($item['size']); ?></td>
                                    <td><?php echo htmlspecialchars($item['color']); ?></td>
                                    <td><?php echo htmlspecialchars($item['quantity']); ?></td>
                                    <td><?php echo number_format((float)$item['unit_price'], 2); ?></td>
                                    <td><?php echo number_format((float)$item['line_total'], 2); ?></td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>

                    <div class="invoice-totals">
                        <div class="qr-notes-box">
                            <div class="qr-code-box">
                                <img src="https://api.qrserver.com/v1/create-qr-code/?size=90x90&data=Invoice<?php echo htmlspecialchars($order['payment_id']); ?>" alt="QR Code" class="qr-code">
                            </div>
                        </div>
                        <div class="totals-box">
                            <table class="table table-bordered totals-table">
                                <tr><th>Subtotal</th><td><?php echo number_format((float)($order['subtotal'] ?? 0), 2); ?></td></tr>
                                <tr><th>Discount</th><td><?php echo number_format((float)($order['discount_amount'] ?? 0), 2); ?></td></tr>
                                <tr><th>VAT</th><td><?php echo number_format((float)($order['vat_amount'] ?? 0), 2); ?></td></tr>
                                <tr class="grand-total"><th>Grand Total</th><td><?php echo number_format((float)($order['grand_total'] ?? 0), 2); ?></td></tr>
                                <tr><th>Paid</th><td><?php echo number_format((float)($order['paid_amount'] ?? 0), 2); ?></td></tr>
                                <tr><th>Due</th><td><?php echo number_format((float)($order['due_amount'] ?? 0), 2); ?></td></tr>
                            </table>
                        </div>
                    </div>

                    <!-- Status Message & Notes -->
                    <div class="invoice-status-section">
                        <?php if (strtolower($order['payment_status']) === 'incomplete' || strtolower($order['payment_status']) === 'pending'): ?>
                            <div class="status-message incomplete">
                                <p>⏰ Please pay within <strong>30 days</strong> from the invoice date.</p>
                            </div>
                        <?php else: ?>
                            <div class="status-message completed">
                                <p>✓ Thank you for being our valued customer!</p>
                            </div>
                        <?php endif; ?>
                        
                    <!-- Notes Footer -->
                    <div class="invoice-footer-section">
                        <?php if (!empty($order['notes'])): ?>
                            <div class="footer-notes">
                                <p><strong>Notes:</strong> <?php echo htmlspecialchars($order['notes']); ?></p>
                            </div>
                        <?php endif; ?>
                    </div>
                    </div>

                </div>
            </div>
        <?php endfor; ?>
    </div>
</section>

<style>
.invoice-card {
    border: 1px solid #e2e8f0;
    border-radius: 8px;
    background: #ffffff;
    margin-bottom: 20px;
    box-shadow: 0 2px 8px rgba(0,0,0,0.05);
    position: relative;
    overflow: hidden;
}
.invoice-watermark {
    display: none;
}
.invoice-watermark img {
    width: 100%;
    height: auto;
    display: block;
}
.invoice-header {
    display: flex;
    justify-content: space-between;
    align-items: flex-start;
    padding: 12px 16px;
    background: linear-gradient(135deg, #1d4ed8 0%, #3b82f6 100%);
    color: #fff;
    border-top-left-radius: 8px;
    border-top-right-radius: 8px;
}
.invoice-header h2,
.invoice-header h3 {
    margin: 0;
    font-size: 18px;
}
.invoice-header p {
    margin: 4px 0 0;
    line-height: 1.3;
    color: rgba(255,255,255,0.9);
    font-size: 11px;
}
.invoice-meta {
    text-align: right;
}
.invoice-meta strong {
    color: #f8fafc;
}
.invoice-meta p {
    margin: 2px 0;
    font-size: 11px;
}
.invoice-blocks {
    display: flex;
    justify-content: space-between;
    padding: 8px 16px;
    border-bottom: 1px solid #e2e8f0;
    gap: 16px;
}
.invoice-block {
    width: 48%;
}
.invoice-block-full {
    width: 100%;
}
.invoice-block h4,
.invoice-block-full h4 {
    margin-bottom: 4px;
    color: #1d4ed8;
    font-size: 11px;
}
.invoice-block p,
.invoice-block-full p {
    margin: 0;
    line-height: 1.3;
    font-size: 10px;
}
.invoice-table {
    padding: 0 16px 12px;
}
.invoice-table .table {
    margin-bottom: 0;
    font-size: 11px;
}
.invoice-table th {
    background: #eff6ff;
    color: #1d4ed8;
    border-color: #dbeafe;
    padding: 6px !important;
}
.invoice-table td {
    padding: 6px !important;
}
.invoice-totals {
    display: flex;
    justify-content: space-between;
    align-items: flex-start;
    padding: 8px 16px;
    gap: 16px;
}
.qr-notes-box {
    width: 35%;
    padding: 0;
    background: transparent;
    display: flex;
    flex-direction: column;
    align-items: center;
}
.qr-code-box {
    text-align: center;
    display: flex;
    flex-direction: column;
    align-items: center;
}
.qr-code {
    border: 1px solid #1d4ed8;
    padding: 4px;
    border-radius: 4px;
    background: #f8fafc;
    width: 90px;
    height: 90px;
    display: block;
}
.totals-box {
    width: 50%;
}
.totals-table {
    font-size: 11px;
}
.totals-table th,
.totals-table td {
    border-color: #e2e8f0;
    padding: 4px 8px !important;
}
.totals-table .grand-total th,
.totals-table .grand-total td {
    font-weight: 700;
    background: #e0f2fe;
    color: #1d4ed8;
}

/* Status Message Styles */
.invoice-status-section {
    padding: 6px 16px;
    border-top: 1px solid #e2e8f0;
    border-bottom: 1px solid #e2e8f0;
}
.status-message {
    padding: 6px 8px;
    border-radius: 4px;
    text-align: center;
    margin: 0;
}
.status-message.incomplete {
    background: linear-gradient(135deg, #fef08a 0%, #fcd34d 100%);
    border-left: 3px solid #f59e0b;
    color: #92400e;
}
.status-message.incomplete p {
    margin: 0;
    font-size: 10px;
}
.status-message.completed {
    background: linear-gradient(135deg, #dcfce7 0%, #bbf7d0 100%);
    border-left: 3px solid #10b981;
    color: #065f46;
}
.status-message.completed p {
    margin: 0;
    font-size: 10px;
}

/* Footer Section - Notes */
.invoice-footer-section {
    display: flex;
    justify-content: center;
    padding: 8px 16px;
    border-bottom: 1px solid #e2e8f0;
}
.footer-notes {
    flex-grow: 1;
    text-align: left;
}
.footer-notes p {
    margin: 0;
    color: #4b5563;
    font-size: 9px;
    line-height: 1.3;
    word-break: break-word;
}

/* Signature Section */
.invoice-signature {
    display: none;
}


@media print {

    * {
        -webkit-print-color-adjust: exact !important;
        print-color-adjust: exact !important;
    }

    html, body {
        margin: 0;
        padding: 0;
        width: 100%;
        font-size: 10px;
    }

    body {
        background: #fff;
    }

    .content-header,
    .btn,
    .box-header,
    .main-footer,
    .main-header,
    .main-sidebar,
    .sidebar,
    .control-sidebar,
    .navbar,
    .navbar-custom-menu {
        display: none !important;
    }

    .invoice-print-area {
        width: 100%;
        overflow: hidden;
    }

    .content {
        margin: 0;
        padding: 0;
    }

    .row {
        display: flex !important;
        flex-wrap: nowrap !important;
        width: 100%;
        margin: 0;
    }

    .col-sm-6 {
        width: 50% !important;
        max-width: 50% !important;
        flex: 0 0 50% !important;
        padding: 4mm !important;
        box-sizing: border-box;
    }

    .invoice-card {
margin:0!important;
    break-inside:auto!important;
    page-break-inside:auto!important;
    }
  .invoice-watermark {
        display: block !important;
        position: absolute;
        top: 50%;
        left: 50%;
        transform: translate(-50%, -50%);
        width: min(70%, 320px);
        max-width: 70%;
        opacity: 0.06;
        z-index: 0;
        pointer-events: none;
        -webkit-print-color-adjust: exact !important;
        print-color-adjust: exact !important;
    }
    /* Hide QR Code */
    .qr-notes-box,
    .qr-code-box,
    .qr-code {
        display: none !important;
    }

    /* Totals take full width */
    .totals-box {
        width: 100% !important;
    }

    .table {
        width: 100%;
        border-collapse: collapse;
        font-size: 9px;
    }

    .table th,
    .table td {
        border: 1px solid #ddd;
        padding: 4px !important;
    }

    @page {
        size: A4 landscape;
        margin: 6mm;
    }
}

@media (max-width: 991px) {
    .invoice-header {
        padding: 10px 12px;
    }
    .invoice-header h2 {
        font-size: 14px;
    }
    .invoice-blocks,
    .invoice-totals {
        flex-direction: column;
    }
    .invoice-block,
    .invoice-block-full,
    .totals-box,
    .qr-notes-box { 
        width: 100%; 
    }
    .invoice-meta { 
        text-align: left; 
        margin-top: 10px; 
    }
    .col-sm-6 { 
        width: 100%; 
        float: none; 
    }
    .invoice-footer-section { 
        flex-direction: column; 
    }
    .terms-conditions { 
        margin-top: 10px; 
    }
}
</style>

<?php require_once('footer.php'); ?>
