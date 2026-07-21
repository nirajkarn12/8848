<?php require_once('header.php'); ?>
<?php require_once('inc/commission.php'); ?>

<?php
if (!isset($_GET['id']) || empty($_GET['id'])) {
    header('Location: order.php');
    exit;
}

$id = (int)$_GET['id'];
$statement = $pdo->prepare("SELECT * FROM tbl_payment WHERE id = ?");
$statement->execute(array($id));
$payment = $statement->fetch(PDO::FETCH_ASSOC);

if (!$payment) {
    header('Location: order.php');
    exit;
}

$statement = $pdo->prepare("SELECT * FROM tbl_order WHERE payment_id = ?");
$statement->execute(array($payment['payment_id']));
$orders = $statement->fetchAll(PDO::FETCH_ASSOC);

$baseAmount = getBookingBaseAmount($payment, $orders);
$firstOrder = $orders[0] ?? null;
$serviceName = $firstOrder ? $firstOrder['product_name'] : 'Service';
$productId = getPrimaryProductIdFromOrders($orders);

ensureServiceLocationColumns($pdo);
$service_address = $payment['service_address'] ?? '';
$service_lat = normalizeMapCoordinate($payment['service_lat'] ?? null, -90, 90);
$service_lng = normalizeMapCoordinate($payment['service_lng'] ?? null, -180, 180);
$preferred_date = $payment['preferred_date'] ?? '';
$preferred_time = $payment['preferred_time'] ?? '';
$emailNotice = '';
$hasShareCol = commissionColumnExists($pdo, 'tbl_booking_assignment', 'commission_share_percent');
$hasAssignLat = commissionColumnExists($pdo, 'tbl_booking_assignment', 'service_lat');
$hasAssignLng = commissionColumnExists($pdo, 'tbl_booking_assignment', 'service_lng');

// Auto-suggest next staff (GET action)
if (isset($_GET['auto_suggest'])) {
    $suggested = getNextStaffForAutoAssign($pdo, $preferred_date);
    if ($suggested) {
        $_POST['staff_id'] = (int)$suggested['staff_id'];
        $success_message = 'Suggested staff: ' . $suggested['full_name'] . ' (round-robin' . ($preferred_date ? ' + availability' : '') . ').';
    } else {
        $error_message .= 'No active staff available to suggest.<br>';
    }
}

if (isset($_POST['form1'])) {
    $valid = 1;
    $staffId = (int)($_POST['staff_id'] ?? 0);
    $service_address = trim($_POST['service_address'] ?? '');
    $service_lat = normalizeMapCoordinate($_POST['service_lat'] ?? null, -90, 90);
    $service_lng = normalizeMapCoordinate($_POST['service_lng'] ?? null, -180, 180);
    $preferred_date = trim($_POST['preferred_date'] ?? '');
    $preferred_time = trim($_POST['preferred_time'] ?? '');
    $admin_notes = trim($_POST['admin_notes'] ?? '');
    $overrideType = trim($_POST['commission_type'] ?? 'inherit');
    $overrideValue = $_POST['commission_value'] ?? '';
    $sharePercent = (float)($_POST['commission_share_percent'] ?? 100);
    if ($sharePercent <= 0) {
        $sharePercent = 100;
    }
    if ($sharePercent > 100) {
        $sharePercent = 100;
    }

    if ($staffId <= 0) {
        $valid = 0;
        $error_message .= 'Please select a staff member.<br>';
    }

    if ($service_address === '') {
        $valid = 0;
        $error_message .= 'Service address is required so staff knows where to go.<br>';
    }

    if ($valid == 1 && staffAlreadyAssignedToPayment($pdo, $payment['payment_id'], $staffId)) {
        $valid = 0;
        $error_message .= 'This staff is already assigned to this booking. Pick another staff for multi-assign.<br>';
    }

    if ($valid == 1) {
        $rule = resolveStaffCommissionRule($pdo, $staffId, $overrideType, $overrideValue, $productId);
        $fullCommission = calculateCommissionAmount($baseAmount, $rule['commission_type'], $rule['commission_value']);
        $commissionAmount = $hasShareCol ? applyCommissionShare($fullCommission, $sharePercent) : $fullCommission;
        $assignedBy = (int)($_SESSION['user']['id'] ?? 0);

        if ($hasShareCol && $hasAssignLat && $hasAssignLng) {
            $statement = $pdo->prepare("
                INSERT INTO tbl_booking_assignment (
                    payment_id, payment_row_id, staff_id, assigned_by, assigned_at, job_status,
                    service_address, service_lat, service_lng, preferred_date, preferred_time,
                    client_name, client_phone, client_email,
                    service_name, service_amount,
                    commission_type, commission_value, commission_amount, commission_status,
                    commission_share_percent, admin_notes
                ) VALUES (?, ?, ?, ?, NOW(), 'Assigned', ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'pending', ?, ?)
            ");
            $statement->execute(array(
                $payment['payment_id'],
                $id,
                $staffId,
                $assignedBy,
                $service_address,
                $service_lat,
                $service_lng,
                $preferred_date !== '' ? $preferred_date : null,
                $preferred_time,
                $payment['customer_name'],
                $payment['customer_phone'] ?? '',
                $payment['customer_email'],
                $serviceName,
                $baseAmount,
                $rule['commission_type'],
                $rule['commission_value'],
                $commissionAmount,
                $sharePercent,
                $admin_notes
            ));
        } elseif ($hasShareCol) {
            $statement = $pdo->prepare("
                INSERT INTO tbl_booking_assignment (
                    payment_id, payment_row_id, staff_id, assigned_by, assigned_at, job_status,
                    service_address, preferred_date, preferred_time,
                    client_name, client_phone, client_email,
                    service_name, service_amount,
                    commission_type, commission_value, commission_amount, commission_status,
                    commission_share_percent, admin_notes
                ) VALUES (?, ?, ?, ?, NOW(), 'Assigned', ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'pending', ?, ?)
            ");
            $statement->execute(array(
                $payment['payment_id'],
                $id,
                $staffId,
                $assignedBy,
                $service_address,
                $preferred_date !== '' ? $preferred_date : null,
                $preferred_time,
                $payment['customer_name'],
                $payment['customer_phone'] ?? '',
                $payment['customer_email'],
                $serviceName,
                $baseAmount,
                $rule['commission_type'],
                $rule['commission_value'],
                $commissionAmount,
                $sharePercent,
                $admin_notes
            ));
        } elseif ($hasAssignLat && $hasAssignLng) {
            $statement = $pdo->prepare("
                INSERT INTO tbl_booking_assignment (
                    payment_id, payment_row_id, staff_id, assigned_by, assigned_at, job_status,
                    service_address, service_lat, service_lng, preferred_date, preferred_time,
                    client_name, client_phone, client_email,
                    service_name, service_amount,
                    commission_type, commission_value, commission_amount, commission_status,
                    admin_notes
                ) VALUES (?, ?, ?, ?, NOW(), 'Assigned', ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'pending', ?)
            ");
            $statement->execute(array(
                $payment['payment_id'],
                $id,
                $staffId,
                $assignedBy,
                $service_address,
                $service_lat,
                $service_lng,
                $preferred_date !== '' ? $preferred_date : null,
                $preferred_time,
                $payment['customer_name'],
                $payment['customer_phone'] ?? '',
                $payment['customer_email'],
                $serviceName,
                $baseAmount,
                $rule['commission_type'],
                $rule['commission_value'],
                $commissionAmount,
                $admin_notes
            ));
        } else {
            $statement = $pdo->prepare("
                INSERT INTO tbl_booking_assignment (
                    payment_id, payment_row_id, staff_id, assigned_by, assigned_at, job_status,
                    service_address, preferred_date, preferred_time,
                    client_name, client_phone, client_email,
                    service_name, service_amount,
                    commission_type, commission_value, commission_amount, commission_status,
                    admin_notes
                ) VALUES (?, ?, ?, ?, NOW(), 'Assigned', ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'pending', ?)
            ");
            $statement->execute(array(
                $payment['payment_id'],
                $id,
                $staffId,
                $assignedBy,
                $service_address,
                $preferred_date !== '' ? $preferred_date : null,
                $preferred_time,
                $payment['customer_name'],
                $payment['customer_phone'] ?? '',
                $payment['customer_email'],
                $serviceName,
                $baseAmount,
                $rule['commission_type'],
                $rule['commission_value'],
                $commissionAmount,
                $admin_notes
            ));
        }

        $updateSql = "UPDATE tbl_payment SET service_address = ?, preferred_date = ?, preferred_time = ?, assignment_status = 'Assigned'";
        $updateParams = array($service_address, $preferred_date !== '' ? $preferred_date : null, $preferred_time);
        if (commissionColumnExists($pdo, 'tbl_payment', 'service_lat')) {
            $updateSql .= ", service_lat = ?";
            $updateParams[] = $service_lat;
        }
        if (commissionColumnExists($pdo, 'tbl_payment', 'service_lng')) {
            $updateSql .= ", service_lng = ?";
            $updateParams[] = $service_lng;
        }

        if (commissionColumnExists($pdo, 'tbl_payment', 'booking_status')) {
            $updateSql .= ", booking_status = 'Confirmed'";
        }

        $updateSql .= " WHERE id = ?";
        $updateParams[] = $id;

        $statement = $pdo->prepare($updateSql);
        $statement->execute($updateParams);

        $staffStmt = $pdo->prepare("SELECT staff_id, full_name, email, phone FROM tbl_staff WHERE staff_id = ? LIMIT 1");
        $staffStmt->execute(array($staffId));
        $staffRow = $staffStmt->fetch(PDO::FETCH_ASSOC);

        $assignmentPayload = array(
            'service_name' => $serviceName,
            'client_name' => $payment['customer_name'],
            'client_phone' => $payment['customer_phone'] ?? '',
            'service_address' => $service_address,
            'preferred_date' => $preferred_date,
            'preferred_time' => $preferred_time,
            'commission_amount' => $commissionAmount,
        );

        if ($staffRow) {
            try {
                if (sendStaffAssignmentEmail($pdo, $staffRow, $assignmentPayload, $payment)) {
                    $emailNotice = ' Assignment email sent to ' . $staffRow['email'] . '.';
                }
            } catch (Exception $e) {
                $emailNotice = ' Assignment saved, but email could not be sent.';
            }
        }

        $shareNote = $hasShareCol ? (' Share: ' . number_format($sharePercent, 0) . '%.') : '';
        $success_message = 'Staff assigned successfully. Commission: Rs. ' . number_format($commissionAmount, 2)
            . ' via ' . formatCommissionRuleLabel($rule) . '.' . $shareNote . $emailNotice;
    }
}

$assignments = getAssignmentsForPayment($pdo, $payment['payment_id']);
$staffList = $pdo->query("SELECT staff_id, full_name, phone, email, default_commission_type, default_commission_value FROM tbl_staff WHERE status = 'Active' ORDER BY full_name ASC")->fetchAll(PDO::FETCH_ASSOC);
$selectedStaffId = (int)($_POST['staff_id'] ?? 0);
$selectedOverrideType = trim($_POST['commission_type'] ?? 'inherit');
$selectedOverrideValue = $_POST['commission_value'] ?? '';
$selectedShare = (float)($_POST['commission_share_percent'] ?? ($assignments ? 50 : 100));
$previewRule = resolveStaffCommissionRule($pdo, $selectedStaffId, $selectedOverrideType, $selectedOverrideValue, $productId);
$previewFull = calculateCommissionAmount($baseAmount, $previewRule['commission_type'], $previewRule['commission_value']);
$previewAmount = applyCommissionShare($previewFull, $selectedShare);
$serviceRule = getServiceCommissionRule($pdo, $productId);
?>

<section class="content-header">
    <div class="content-header-left">
        <h1>Assign Staff to Order</h1>
    </div>
    <div class="content-header-right">
        <a href="order-assign.php?id=<?php echo $id; ?>&amp;auto_suggest=1" class="btn btn-warning btn-sm">Auto-Suggest Staff</a>
        <a href="order-show.php?id=<?php echo $id; ?>" class="btn btn-primary btn-sm">Back to Order</a>
    </div>
</section>

<section class="content">
    <div class="row">
        <div class="col-md-12">
            <?php if (!empty($error_message)) { ?>
            <div class="callout callout-danger"><p><?php echo $error_message; ?></p></div>
            <?php } ?>
            <?php if (!empty($success_message)) { ?>
            <div class="callout callout-success"><p><?php echo $success_message; ?></p></div>
            <?php } ?>

            <div class="box box-primary">
                <div class="box-body">
                    <table class="table table-bordered">
                        <tr><th width="30%">Order ID</th><td><?php echo htmlspecialchars($payment['payment_id']); ?></td></tr>
                        <tr><th>Client</th><td><?php echo htmlspecialchars($payment['customer_name']); ?> | <?php echo htmlspecialchars($payment['customer_phone'] ?? ''); ?></td></tr>
                        <tr><th>Service</th><td><?php echo htmlspecialchars($serviceName); ?></td></tr>
                        <tr><th>Booking Amount (commission base)</th><td><strong>Rs. <?php echo number_format($baseAmount, 2); ?></strong></td></tr>
                        <tr>
                            <th>Service commission rule</th>
                            <td>
                                <?php if ($serviceRule) { ?>
                                    <?php echo htmlspecialchars(formatCommissionRuleLabel($serviceRule)); ?>
                                <?php } else { ?>
                                    Inherit (staff/global default)
                                <?php } ?>
                            </td>
                        </tr>
                    </table>
                </div>
            </div>

            <?php if ($assignments) { ?>
            <div class="box box-success">
                <div class="box-header with-border"><h3 class="box-title">Assigned Staff (<?php echo count($assignments); ?>)</h3></div>
                <div class="box-body table-responsive">
                    <table class="table table-bordered table-striped">
                        <thead>
                            <tr>
                                <th>Staff</th>
                                <th>Job Status</th>
                                <th>Share</th>
                                <th>Commission</th>
                                <th>Arrived</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($assignments as $row) { ?>
                            <tr>
                                <td><?php echo htmlspecialchars($row['staff_name']); ?> (<?php echo htmlspecialchars($row['staff_phone']); ?>)</td>
                                <td><?php echo htmlspecialchars($row['job_status']); ?></td>
                                <td><?php echo number_format((float)($row['commission_share_percent'] ?? 100), 0); ?>%</td>
                                <td>Rs. <?php echo number_format((float)$row['commission_amount'], 2); ?> (<?php echo htmlspecialchars($row['commission_status']); ?>)</td>
                                <td><?php echo htmlspecialchars($row['arrived_at'] ?? '—'); ?></td>
                            </tr>
                            <?php } ?>
                        </tbody>
                    </table>
                    <p class="text-muted">You can assign additional staff below (multi-staff). Use commission share % to split earnings.</p>
                </div>
            </div>
            <?php } ?>

            <form class="form-horizontal" action="" method="post" id="assign-form">
                <div class="box box-info">
                    <div class="box-header with-border"><h3 class="box-title"><?php echo $assignments ? 'Add Another Staff' : 'Assignment Details'; ?></h3></div>
                    <div class="box-body">
                        <div class="form-group">
                            <label class="col-sm-2 control-label">Select Staff *</label>
                            <div class="col-sm-4">
                                <select name="staff_id" id="staff_id" class="form-control select2" required>
                                    <option value="">Select Staff</option>
                                    <?php foreach ($staffList as $staff) { ?>
                                    <option
                                        value="<?php echo (int)$staff['staff_id']; ?>"
                                        data-type="<?php echo htmlspecialchars($staff['default_commission_type']); ?>"
                                        data-value="<?php echo htmlspecialchars($staff['default_commission_value']); ?>"
                                        <?php echo ($selectedStaffId === (int)$staff['staff_id']) ? 'selected' : ''; ?>
                                    >
                                        <?php echo htmlspecialchars($staff['full_name']); ?> (<?php echo htmlspecialchars($staff['phone']); ?>)
                                    </option>
                                    <?php } ?>
                                </select>
                            </div>
                        </div>
                        <div class="form-group">
                            <label class="col-sm-2 control-label">Service Address *</label>
                            <div class="col-sm-6">
                                <textarea class="form-control" id="service_address" name="service_address" rows="3" required><?php echo htmlspecialchars($service_address); ?></textarea>
                                <p class="help-block">Pin the same client location on the map so staff can navigate easily.</p>
                                <?php echo adminRenderServiceLocationPicker($service_lat ?? '', $service_lng ?? '', '#service_address'); ?>
                            </div>
                        </div>
                        <div class="form-group">
                            <label class="col-sm-2 control-label">Preferred Date</label>
                            <div class="col-sm-3">
                                <input type="date" class="form-control" name="preferred_date" id="preferred_date" value="<?php echo htmlspecialchars($preferred_date); ?>">
                            </div>
                        </div>
                        <div class="form-group">
                            <label class="col-sm-2 control-label">Preferred Time</label>
                            <div class="col-sm-3">
                                <input type="text" class="form-control" name="preferred_time" placeholder="e.g. 10:00 AM" value="<?php echo htmlspecialchars($preferred_time); ?>">
                            </div>
                        </div>
                        <div class="form-group">
                            <label class="col-sm-2 control-label">Commission Rule</label>
                            <div class="col-sm-2">
                                <select name="commission_type" id="commission_type" class="form-control">
                                    <option value="inherit" <?php echo ($selectedOverrideType === 'inherit') ? 'selected' : ''; ?>>Auto (service → staff → global)</option>
                                    <option value="percent" <?php echo ($selectedOverrideType === 'percent') ? 'selected' : ''; ?>>Owner override %</option>
                                    <option value="fixed" <?php echo ($selectedOverrideType === 'fixed') ? 'selected' : ''; ?>>Owner fixed Rs.</option>
                                    <option value="custom" <?php echo ($selectedOverrideType === 'custom') ? 'selected' : ''; ?>>Owner custom amount</option>
                                </select>
                            </div>
                            <div class="col-sm-2">
                                <input type="number" step="0.01" min="0" class="form-control" name="commission_value" id="commission_value" placeholder="Override value" value="<?php echo htmlspecialchars($selectedOverrideValue); ?>">
                            </div>
                        </div>
                        <?php if ($hasShareCol) { ?>
                        <div class="form-group">
                            <label class="col-sm-2 control-label">Commission Share %</label>
                            <div class="col-sm-2">
                                <input type="number" step="0.01" min="1" max="100" class="form-control" name="commission_share_percent" id="commission_share_percent" value="<?php echo htmlspecialchars($selectedShare); ?>">
                            </div>
                            <div class="col-sm-6">
                                <p class="help-block">For multi-staff jobs, e.g. 50 + 50. Final commission = calculated × share%.</p>
                            </div>
                        </div>
                        <?php } ?>
                        <div class="form-group">
                            <label class="col-sm-2 control-label">Preview</label>
                            <div class="col-sm-6">
                                <p class="help-block" id="commission-preview">
                                    Estimated commission: <strong>Rs. <?php echo number_format($previewAmount, 2); ?></strong><br>
                                    <span class="text-muted"><?php echo htmlspecialchars(formatCommissionRuleLabel($previewRule)); ?></span>
                                </p>
                            </div>
                        </div>
                        <div class="form-group">
                            <label class="col-sm-2 control-label">Admin Notes</label>
                            <div class="col-sm-6">
                                <textarea class="form-control" name="admin_notes" rows="2"><?php echo htmlspecialchars($_POST['admin_notes'] ?? ''); ?></textarea>
                            </div>
                        </div>
                        <div class="form-group">
                            <label class="col-sm-2 control-label"></label>
                            <div class="col-sm-6">
                                <button type="submit" class="btn btn-success" name="form1"><?php echo $assignments ? 'Add Staff' : 'Assign Staff'; ?></button>
                            </div>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>
</section>

<script>
(function () {
    var baseAmount = <?php echo json_encode((float)$baseAmount); ?>;
    var serviceRule = <?php echo json_encode($serviceRule ?: null); ?>;
    var globalRule = <?php echo json_encode(getDefaultCommissionSettings($pdo)); ?>;

    function calcAmount(type, value) {
        value = parseFloat(value) || 0;
        if (type === 'fixed' || type === 'custom') {
            return value;
        }
        return Math.round((baseAmount * value / 100) * 100) / 100;
    }

    function resolveRule() {
        var overrideType = document.getElementById('commission_type').value;
        var overrideValue = document.getElementById('commission_value').value;
        if (overrideType !== 'inherit' && overrideValue !== '') {
            return { commission_type: overrideType, commission_value: parseFloat(overrideValue), source: 'assignment' };
        }
        if (serviceRule) {
            return serviceRule;
        }
        var staffSelect = document.getElementById('staff_id');
        var option = staffSelect.options[staffSelect.selectedIndex];
        if (option && option.value) {
            var staffValue = parseFloat(option.getAttribute('data-value') || '0');
            if (staffValue > 0) {
                return {
                    commission_type: option.getAttribute('data-type') || 'percent',
                    commission_value: staffValue,
                    source: 'staff'
                };
            }
        }
        return {
            commission_type: globalRule.default_staff_commission_type || 'percent',
            commission_value: parseFloat(globalRule.default_staff_commission_value || 35),
            source: 'global'
        };
    }

    function sourceLabel(source) {
        return {
            assignment: 'Owner override',
            service: 'Service rule',
            staff: 'Staff default',
            global: 'Global default'
        }[source] || source;
    }

    function updatePreview() {
        var rule = resolveRule();
        var amount = calcAmount(rule.commission_type, rule.commission_value);
        var shareEl = document.getElementById('commission_share_percent');
        var share = shareEl ? (parseFloat(shareEl.value) || 100) : 100;
        var finalAmount = Math.round((amount * share / 100) * 100) / 100;
        var valueLabel = (rule.commission_type === 'fixed' || rule.commission_type === 'custom')
            ? ('Rs. ' + amount.toFixed(2))
            : ((parseFloat(rule.commission_value) || 0).toFixed(2) + '%');
        document.getElementById('commission-preview').innerHTML =
            'Estimated commission: <strong>Rs. ' + finalAmount.toFixed(2) + '</strong>' +
            (share < 100 ? (' <span class="text-muted">(' + share + '% of Rs. ' + amount.toFixed(2) + ')</span>') : '') +
            '<br><span class="text-muted">' + valueLabel + ' (' + sourceLabel(rule.source) + ')</span>';
    }

    ['staff_id', 'commission_type', 'commission_value', 'commission_share_percent'].forEach(function (id) {
        var el = document.getElementById(id);
        if (el) {
            el.addEventListener('change', updatePreview);
            el.addEventListener('keyup', updatePreview);
        }
    });
})();
</script>

<?php echo adminServiceLocationAssets(); ?>
<?php require_once('footer.php'); ?>
