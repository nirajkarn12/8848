<?php require_once('header.php'); ?>
<?php require_once('inc/commission.php'); ?>

<?php
// Check if 'id' is provided
if(!isset($_GET['id']) || empty($_GET['id'])) {
    header('Location: order.php');
    exit;
}

$id = (int)$_GET['id'];

// Fetch payment details
$statement = $pdo->prepare("SELECT * FROM tbl_payment WHERE id = ?");
$statement->execute([$id]);
$payment = $statement->fetch(PDO::FETCH_ASSOC);

if(!$payment) {
    header('Location: order.php');
    exit;
}

// Fetch order items for this payment
$statement = $pdo->prepare("SELECT * FROM tbl_order WHERE payment_id = ?");
$statement->execute([$payment['payment_id']]);
$orders = $statement->fetchAll(PDO::FETCH_ASSOC);
$assignments = getAssignmentsForPayment($pdo, $payment['payment_id']);
$assignment = $assignments ? $assignments[0] : null;

// Helper function to check if a column exists (reuse logic)
function columnExists($pdo, $table, $column) {
    $stmt = $pdo->prepare("SHOW COLUMNS FROM `" . $table . "` LIKE ?");
    $stmt->execute([$column]);
    return $stmt->rowCount() > 0;
}
?>

<section class="content-header">
    <div class="content-header-left">
        <h1>Order Details</h1>
    </div>
    <div class="content-header-right">
        <a href="order.php" class="btn btn-primary btn-sm">Back to Orders</a>
        <a href="order-edit.php?id=<?php echo $id; ?>" class="btn btn-warning btn-sm">Edit Order</a>
        <a href="order-assign.php?id=<?php echo $id; ?>" class="btn btn-success btn-sm">Assign Staff</a>
    </div>
</section>

<section class="content">
    <div class="row">
        <div class="col-md-12">
            <div class="box box-primary">
                <div class="box-body">
                    <!-- Order ID and Invoice ID -->
                    <div class="row">
                        <div class="col-md-6">
                            <table class="table table-bordered table-striped">
                                <tr><th width="35%">Booking ID</th><td><?php echo htmlspecialchars($payment['payment_id']); ?></td></tr>
                                <tr><th>Invoice ID</th><td><?php echo htmlspecialchars($payment['id']); ?></td></tr>
                                <tr><th>Customer Name</th><td><?php echo htmlspecialchars($payment['customer_name']); ?></td></tr>
                                <tr><th>Customer Email</th><td><?php echo htmlspecialchars($payment['customer_email']); ?></td></tr>
                                <tr><th>Customer Phone</th><td><?php echo htmlspecialchars($payment['customer_phone'] ?? ''); ?></td></tr>
                                <tr><th>Payment Method</th><td><?php echo htmlspecialchars($payment['payment_method']); ?></td></tr>
                                <tr><th>Payment Status</th><td><?php echo htmlspecialchars($payment['payment_status']); ?></td></tr>
                                <tr><th>Visit Status</th><td><?php echo htmlspecialchars($payment['shipping_status']); ?></td></tr>
                                <?php if (!empty($payment['booking_status'])): ?>
                                <tr><th>Booking Status</th><td><?php echo htmlspecialchars($payment['booking_status']); ?></td></tr>
                                <?php endif; ?>
                                <tr><th>Payment Date</th><td><?php echo htmlspecialchars($payment['payment_date']); ?></td></tr>
                                <?php if(!empty($payment['notes'])): ?>
                                <tr><th>Notes</th><td><?php echo nl2br(htmlspecialchars($payment['notes'])); ?></td></tr>
                                <?php endif; ?>
                                <?php if(!empty($payment['service_address'])): ?>
                                <tr><th>Service Address</th><td><?php echo nl2br(htmlspecialchars($payment['service_address'])); ?></td></tr>
                                <?php endif; ?>
                                <?php if (!empty($payment['service_lat']) && !empty($payment['service_lng'])): ?>
                                <tr><th>Map Pin</th><td><?php echo htmlspecialchars($payment['service_lat'] . ', ' . $payment['service_lng']); ?></td></tr>
                                <?php endif; ?>
                                <?php if(!empty($payment['preferred_date']) || !empty($payment['preferred_time'])): ?>
                                <tr><th>Preferred Schedule</th><td><?php echo htmlspecialchars(trim(($payment['preferred_date'] ?? '') . ' ' . ($payment['preferred_time'] ?? ''))); ?></td></tr>
                                <?php endif; ?>
                                <?php if(!empty($payment['assignment_status'])): ?>
                                <tr><th>Assignment Status</th><td><?php echo htmlspecialchars($payment['assignment_status']); ?></td></tr>
                                <?php endif; ?>
                            </table>
                        </div>
                        <div class="col-md-6">
                            <table class="table table-bordered table-striped">
                                <tr><th>Subtotal</th><td><?php echo number_format((float)($payment['subtotal'] ?? 0), 2); ?></td></tr>
                                <tr>
                                    <th>Discount</th>
                                    <td>
                                        <?php
                                        $discountType = $payment['discount_type'] ?? '';
                                        $discountValue = (float)($payment['discount_value'] ?? 0);
                                        $discountAmount = (float)($payment['discount_amount'] ?? 0);
                                        if($discountType == 'percent') {
                                            echo $discountValue . '%';
                                        } elseif($discountType == 'amount') {
                                            echo 'Rs.' . number_format($discountValue, 2);
                                        } else {
                                            echo 'None';
                                        }
                                        echo ' (Amount: $' . number_format($discountAmount, 2) . ')';
                                        ?>
                                    </td>
                                </tr>
                                <tr><th>VAT (%)</th><td><?php echo number_format((float)($payment['vat_percent'] ?? 0), 2); ?>%</td></tr>
                                <tr><th>VAT Amount</th><td><?php echo number_format((float)($payment['vat_amount'] ?? 0), 2); ?></td></tr>
                                <tr><th>Grand Total</th><td><strong><?php echo number_format((float)($payment['grand_total'] ?? 0), 2); ?></strong></td></tr>
                                <tr><th>Paid Amount</th><td><?php echo number_format((float)($payment['paid_amount'] ?? 0), 2); ?></td></tr>
                                <tr><th>Due Amount</th><td><?php echo number_format((float)($payment['due_amount'] ?? 0), 2); ?></td></tr>
                                <?php if(!empty($payment['txnid'])): ?>
                                <tr><th>Transaction ID</th><td><?php echo htmlspecialchars($payment['txnid']); ?></td></tr>
                                <?php endif; ?>
                                <?php if(!empty($payment['card_number']) || !empty($payment['card_cvv']) || !empty($payment['card_month']) || !empty($payment['card_year'])): ?>
                                <tr><th>Card Details</th>
                                    <td>
                                        <?php
                                        $cardInfo = [];
                                        if(!empty($payment['card_number'])) $cardInfo[] = 'Number: ' . htmlspecialchars($payment['card_number']);
                                        if(!empty($payment['card_cvv'])) $cardInfo[] = 'CVV: ' . htmlspecialchars($payment['card_cvv']);
                                        if(!empty($payment['card_month'])) $cardInfo[] = 'Month: ' . htmlspecialchars($payment['card_month']);
                                        if(!empty($payment['card_year'])) $cardInfo[] = 'Year: ' . htmlspecialchars($payment['card_year']);
                                        echo implode('<br>', $cardInfo);
                                        ?>
                                    </td>
                                </tr>
                                <?php endif; ?>
                                <?php if(!empty($payment['bank_transaction_info'])): ?>
                                <tr><th>Bank Transaction Info</th><td><?php echo nl2br(htmlspecialchars($payment['bank_transaction_info'])); ?></td></tr>
                                <?php endif; ?>
                            </table>
                        </div>
                    </div>

                    <h3>Staff Assignment</h3>
                    <div class="table-responsive" style="margin-bottom:25px;">
                        <?php if ($assignments) { ?>
                        <table class="table table-bordered table-striped">
                            <thead>
                                <tr>
                                    <th>Staff</th>
                                    <th>Job Status</th>
                                    <th>Share</th>
                                    <th>Commission</th>
                                    <th>Arrived / Check-in</th>
                                    <th>Schedule</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($assignments as $aRow) { ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($aRow['staff_name']); ?> (<?php echo htmlspecialchars($aRow['staff_phone']); ?>)</td>
                                    <td><?php echo htmlspecialchars($aRow['job_status']); ?></td>
                                    <td><?php echo number_format((float)($aRow['commission_share_percent'] ?? 100), 0); ?>%</td>
                                    <td>Rs. <?php echo number_format((float)$aRow['commission_amount'], 2); ?> (<?php echo htmlspecialchars($aRow['commission_status']); ?>)</td>
                                    <td>
                                        <?php echo htmlspecialchars($aRow['arrived_at'] ?? '—'); ?>
                                        <?php if (!empty($aRow['checkin_lat']) && !empty($aRow['checkin_lng'])) { ?>
                                            <br><small><a target="_blank" rel="noopener" href="https://www.google.com/maps?q=<?php echo urlencode($aRow['checkin_lat'] . ',' . $aRow['checkin_lng']); ?>">GPS map</a></small>
                                        <?php } ?>
                                    </td>
                                    <td><?php echo htmlspecialchars(trim(($aRow['preferred_date'] ?? '') . ' ' . ($aRow['preferred_time'] ?? ''))); ?></td>
                                </tr>
                                <?php } ?>
                            </tbody>
                        </table>
                        <p><strong>Address:</strong> <?php echo nl2br(htmlspecialchars($assignments[0]['service_address'] ?? '')); ?></p>
                        <a href="order-assign.php?id=<?php echo $id; ?>" class="btn btn-primary btn-sm">Add / Change Staff</a>
                        <?php } else { ?>
                        <div class="alert alert-warning">No staff assigned yet. <a href="order-assign.php?id=<?php echo $id; ?>">Assign staff now</a></div>
                        <?php } ?>
                    </div>

                    <!-- Booked Services -->
                    <h3>Booked Services</h3>
                    <div class="table-responsive">
                        <table class="table table-bordered table-striped">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>Service Name</th>
                                    <th>Price</th>
                                    <th>Total</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                $counter = 0;
                                $subtotalCheck = 0;
                                foreach($orders as $item) {
                                    $counter++;
                                    $lineTotal = (float)($item['line_total'] ?? 0);
                                    if ($lineTotal <= 0) {
                                        $lineTotal = (float)$item['unit_price'];
                                    }
                                    $subtotalCheck += $lineTotal;
                                    echo '<tr>';
                                    echo '<td>' . $counter . '</td>';
                                    echo '<td>' . htmlspecialchars($item['product_name']) . '</td>';
                                    echo '<td>Rs. ' . number_format((float)$item['unit_price'], 2) . '</td>';
                                    echo '<td>Rs. ' . number_format($lineTotal, 2) . '</td>';
                                    echo '</tr>';
                                }
                                if($counter == 0) {
                                    echo '<tr><td colspan="4" class="text-center">No services found for this booking.</td></tr>';
                                }
                                ?>
                            </tbody>
                            <tfoot>
                                <tr>
                                    <th colspan="3" class="text-right">Subtotal (from items)</th>
                                    <th>Rs. <?php echo number_format($subtotalCheck, 2); ?></th>
                                </tr>
                                <?php if(!empty($payment['discount_amount']) && (float)$payment['discount_amount'] > 0): ?>
                                <tr>
                                    <th colspan="3" class="text-right">Discount</th>
                                    <th>- Rs. <?php echo number_format((float)$payment['discount_amount'], 2); ?></th>
                                </tr>
                                <?php endif; ?>
                                <?php if(!empty($payment['vat_amount']) && (float)$payment['vat_amount'] > 0): ?>
                                <tr>
                                    <th colspan="3" class="text-right">VAT</th>
                                    <th>+ Rs. <?php echo number_format((float)$payment['vat_amount'], 2); ?></th>
                                </tr>
                                <?php endif; ?>
                                <tr>
                                    <th colspan="3" class="text-right">Grand Total</th>
                                    <th><strong>Rs. <?php echo number_format((float)$payment['grand_total'], 2); ?></strong></th>
                                </tr>
                                <tr>
                                    <th colspan="3" class="text-right">Paid</th>
                                    <th>Rs. <?php echo number_format((float)$payment['paid_amount'], 2); ?></th>
                                </tr>
                                <tr>
                                    <th colspan="3" class="text-right">Due</th>
                                    <th>Rs. <?php echo number_format((float)$payment['due_amount'], 2); ?></th>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                </div>
                <!-- /.box-body -->
                <div class="box-footer">
                    <a href="order.php" class="btn btn-default">Back to Orders</a>
                    <a href="invoice.php?id=<?php echo $id; ?>" class="btn btn-info">View Invoice</a>
                    <a href="order-edit.php?id=<?php echo $id; ?>" class="btn btn-warning">Edit Order</a>
                </div>
            </div>

            <?php if (!empty($payment['service_address']) || (!empty($payment['service_lat']) && !empty($payment['service_lng']))) { ?>
            <div class="box box-success">
                <div class="box-header with-border">
                    <h3 class="box-title">Client Service Location (OpenStreetMap)</h3>
                </div>
                <div class="box-body">
                    <?php echo adminRenderServiceLocationViewer($payment['service_lat'] ?? null, $payment['service_lng'] ?? null, $payment['service_address'] ?? ''); ?>
                </div>
            </div>
            <?php } ?>
            <!-- /.box -->
        </div>
    </div>
</section>

<?php echo adminServiceLocationAssets(); ?>
<?php require_once('footer.php'); ?>