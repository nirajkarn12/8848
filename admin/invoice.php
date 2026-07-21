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

$company = getInvoiceCompanyProfile($pdo);
$dueDays = (int)$company['due_days'];
$isPending = in_array(strtolower((string)$order['payment_status']), array('incomplete', 'pending'), true);
$qrPayload = rawurlencode('Invoice ' . $order['payment_id'] . ' | ' . $company['site_name']);
?>

<section class="content-header">
    <div class="content-header-left">
        <h1>Invoice</h1>
    </div>
    <div class="content-header-right">
        <button class="btn btn-primary btn-sm" onclick="window.print()">Print Invoice</button>
        <a href="order.php" class="btn btn-default btn-sm">Back</a>
    </div>
</section>

<div class="invoice-print-area">
    <section class="content">
        <div class="row">
        <?php for ($copyIndex = 0; $copyIndex < 2; $copyIndex++): ?>
            <div class="col-sm-6">
                <div class="invoice-card">
                    <div class="invoice-watermark" aria-hidden="true">
                        <img src="<?php echo htmlspecialchars($company['logo_url']); ?>" alt="">
                    </div>

                    <div class="invoice-header">
                        <div class="invoice-brand">
                            <img class="invoice-logo" src="<?php echo htmlspecialchars($company['logo_url']); ?>" alt="<?php echo htmlspecialchars($company['site_name']); ?>">
                            <div>
                                <h2><?php echo htmlspecialchars($company['site_name']); ?></h2>
                                <p>
                                    <?php if (!empty($company['address'])): ?>
                                        <?php echo nl2br(htmlspecialchars($company['address'])); ?><br>
                                    <?php endif; ?>
                                    <?php if (!empty($company['phone'])): ?>Phone: <?php echo htmlspecialchars($company['phone']); ?><br><?php endif; ?>
                                    <?php if (!empty($company['email'])): ?>Email: <?php echo htmlspecialchars($company['email']); ?><br><?php endif; ?>
                                    <?php if (!empty($company['vat_no'])): ?>VAT/PAN: <?php echo htmlspecialchars($company['vat_no']); ?><?php endif; ?>
                                </p>
                            </div>
                        </div>
                        <div class="invoice-meta">
                            <h3>Invoice</h3>
                            <p>
                                <strong>No:</strong> <?php echo htmlspecialchars($order['payment_id']); ?><br>
                                <strong>Date:</strong> <?php echo htmlspecialchars($order['payment_date']); ?><br>
                                <strong>Status:</strong> <?php echo htmlspecialchars($order['payment_status']); ?><br>
                                <strong>Method:</strong> <?php echo htmlspecialchars($order['payment_method']); ?><br>
                                <strong>Copy:</strong> <?php echo $copyIndex === 0 ? 'Office' : 'Customer'; ?>
                            </p>
                        </div>
                    </div>

                    <div class="invoice-blocks">
                        <div class="invoice-block">
                            <h4>Bill To</h4>
                            <p>
                                <strong><?php echo htmlspecialchars($order['customer_name']); ?></strong><br>
                                <?php if (!empty($order['customer_phone'])): ?>Phone: <?php echo htmlspecialchars($order['customer_phone']); ?><br><?php endif; ?>
                                <?php if (!empty($order['customer_email'])): ?>Email: <?php echo htmlspecialchars($order['customer_email']); ?><?php endif; ?>
                            </p>
                        </div>
                        <div class="invoice-block">
                            <h4>Service Location</h4>
                            <p>
                                <?php if (!empty($order['service_address'])): ?>
                                    <?php echo nl2br(htmlspecialchars($order['service_address'])); ?><br>
                                <?php else: ?>
                                    Not provided<br>
                                <?php endif; ?>
                                <?php if (!empty($order['preferred_date']) || !empty($order['preferred_time'])): ?>
                                    Schedule: <?php echo htmlspecialchars(trim(($order['preferred_date'] ?? '') . ' ' . ($order['preferred_time'] ?? ''))); ?>
                                <?php endif; ?>
                            </p>
                        </div>
                    </div>

                    <div class="table-responsive invoice-table">
                        <table class="table table-bordered table-striped">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>Service</th>
                                    <th>Price</th>
                                    <th>Total</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php $i = 1; foreach ($items as $item):
                                    $lineTotal = (float)$item['line_total'];
                                    if ($lineTotal <= 0) {
                                        $lineTotal = (float)$item['unit_price'];
                                    }
                                ?>
                                <tr>
                                    <td><?php echo $i++; ?></td>
                                    <td><?php echo htmlspecialchars($item['product_name']); ?></td>
                                    <td>Rs. <?php echo number_format((float)$item['unit_price'], 2); ?></td>
                                    <td>Rs. <?php echo number_format($lineTotal, 2); ?></td>
                                </tr>
                                <?php endforeach; ?>
                                <?php if (!$items): ?>
                                <tr><td colspan="4" class="text-center">No services found for this booking.</td></tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>

                    <div class="invoice-totals">
                        <div class="qr-notes-box">
                            <div class="qr-code-box">
                                <img src="https://api.qrserver.com/v1/create-qr-code/?size=90x90&data=<?php echo $qrPayload; ?>" alt="QR Code" class="qr-code">
                                <small><?php echo htmlspecialchars($company['site_name']); ?></small>
                            </div>
                        </div>
                        <div class="totals-box">
                            <table class="table table-bordered totals-table">
                                <tr><th>Subtotal</th><td>Rs. <?php echo number_format((float)($order['subtotal'] ?? 0), 2); ?></td></tr>
                                <tr><th>Discount</th><td>Rs. <?php echo number_format((float)($order['discount_amount'] ?? 0), 2); ?></td></tr>
                                <tr><th>VAT</th><td>Rs. <?php echo number_format((float)($order['vat_amount'] ?? 0), 2); ?></td></tr>
                                <tr class="grand-total"><th>Grand Total</th><td>Rs. <?php echo number_format((float)($order['grand_total'] ?? 0), 2); ?></td></tr>
                                <tr><th>Paid</th><td>Rs. <?php echo number_format((float)($order['paid_amount'] ?? 0), 2); ?></td></tr>
                                <tr><th>Due</th><td>Rs. <?php echo number_format((float)($order['due_amount'] ?? 0), 2); ?></td></tr>
                            </table>
                        </div>
                    </div>

                    <div class="invoice-status-section">
                        <?php if ($isPending): ?>
                            <div class="status-message incomplete">
                                <p>Please pay within <strong><?php echo $dueDays; ?> days</strong> from the invoice date.</p>
                            </div>
                        <?php else: ?>
                            <div class="status-message completed">
                                <p><?php echo htmlspecialchars($company['footer_note']); ?></p>
                            </div>
                        <?php endif; ?>

                        <div class="invoice-footer-section">
                            <?php if (!empty($order['notes'])): ?>
                                <div class="footer-notes">
                                    <p><strong>Notes:</strong> <?php echo nl2br(htmlspecialchars($order['notes'])); ?></p>
                                </div>
                            <?php endif; ?>
                            <?php if (!empty($company['copyright'])): ?>
                                <div class="footer-notes" style="margin-top:6px;">
                                    <p><?php echo htmlspecialchars($company['copyright']); ?></p>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        <?php endfor; ?>
        </div>
    </section>
</div>

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
    background: linear-gradient(135deg, #062a62 0%, #0b4f9c 100%);
    color: #fff;
    border-top-left-radius: 8px;
    border-top-right-radius: 8px;
    gap: 12px;
}
.invoice-brand {
    display: flex;
    gap: 10px;
    align-items: flex-start;
}
.invoice-logo {
    width: 54px;
    height: 54px;
    object-fit: contain;
    background: #fff;
    border-radius: 8px;
    padding: 4px;
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
.invoice-block h4 {
    margin-bottom: 4px;
    color: #062a62;
    font-size: 11px;
}
.invoice-block p {
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
    background: #eef5ff;
    color: #062a62;
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
    gap: 4px;
}
.qr-code {
    border: 1px solid #062a62;
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
    color: #062a62;
}
.invoice-status-section {
    padding: 6px 16px;
    border-top: 1px solid #e2e8f0;
}
.status-message {
    padding: 6px 8px;
    border-radius: 4px;
    text-align: center;
    margin: 0 0 6px;
}
.status-message.incomplete {
    background: linear-gradient(135deg, #fef08a 0%, #fcd34d 100%);
    border-left: 3px solid #f59e0b;
    color: #92400e;
}
.status-message.completed {
    background: linear-gradient(135deg, #dcfce7 0%, #bbf7d0 100%);
    border-left: 3px solid #10b981;
    color: #065f46;
}
.status-message p {
    margin: 0;
    font-size: 10px;
}
.invoice-footer-section {
    display: flex;
    flex-direction: column;
    padding: 4px 0 8px;
}
.footer-notes p {
    margin: 0;
    color: #4b5563;
    font-size: 9px;
    line-height: 1.3;
    word-break: break-word;
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
        margin: 0 !important;
        break-inside: auto !important;
        page-break-inside: auto !important;
    }
    .invoice-watermark {
        display: block !important;
        position: absolute;
        top: 50%;
        left: 50%;
        transform: translate(-50%, -50%);
        width: min(70%, 320px);
        max-width: 70%;
        opacity: 0.08;
        z-index: 0;
        pointer-events: none;
    }
    .invoice-header,
    .invoice-blocks,
    .invoice-table,
    .invoice-totals,
    .invoice-status-section {
        position: relative;
        z-index: 1;
    }
    .qr-notes-box,
    .qr-code-box,
    .qr-code {
        display: none !important;
    }
    .totals-box {
        width: 100% !important;
    }
    @page {
        size: A4 landscape;
        margin: 6mm;
    }
}

@media (max-width: 991px) {
    .invoice-header,
    .invoice-blocks,
    .invoice-totals {
        flex-direction: column;
    }
    .invoice-block,
    .totals-box,
    .qr-notes-box {
        width: 100%;
    }
    .invoice-meta {
        text-align: left;
        margin-top: 10px;
    }
}
</style>

<?php require_once('footer.php'); ?>
