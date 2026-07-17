<?php require_once('header.php'); ?>
<?php require_once('inc/commission.php'); ?>

<?php
$staffFilter = (int)($_GET['staff_id'] ?? 0);
$statusFilter = trim($_GET['commission_status'] ?? '');
$dateFrom = trim($_GET['date_from'] ?? '');
$dateTo = trim($_GET['date_to'] ?? '');

$where = array('1=1');
$params = array();

if ($staffFilter > 0) {
    $where[] = 'a.staff_id = ?';
    $params[] = $staffFilter;
}

if (in_array($statusFilter, array('pending', 'approved', 'paid'), true)) {
    $where[] = 'a.commission_status = ?';
    $params[] = $statusFilter;
}

if ($dateFrom !== '') {
    $where[] = 'DATE(a.assigned_at) >= ?';
    $params[] = $dateFrom;
}

if ($dateTo !== '') {
    $where[] = 'DATE(a.assigned_at) <= ?';
    $params[] = $dateTo;
}

$sql = "
    SELECT a.*, s.full_name AS staff_name, s.phone AS staff_phone
    FROM tbl_booking_assignment a
    LEFT JOIN tbl_staff s ON s.staff_id = a.staff_id
    WHERE " . implode(' AND ', $where) . "
    ORDER BY a.assignment_id DESC
";
$statement = $pdo->prepare($sql);
$statement->execute($params);
$rows = $statement->fetchAll(PDO::FETCH_ASSOC);

$totals = array('pending' => 0.0, 'approved' => 0.0, 'paid' => 0.0, 'all' => 0.0);
foreach ($rows as $row) {
    $amount = (float)$row['commission_amount'];
    $totals['all'] += $amount;
    if (isset($totals[$row['commission_status']])) {
        $totals[$row['commission_status']] += $amount;
    }
}

$staffList = $pdo->query("SELECT staff_id, full_name FROM tbl_staff ORDER BY full_name ASC")->fetchAll(PDO::FETCH_ASSOC);
?>

<section class="content-header">
    <div class="content-header-left">
        <h1>Commission Report</h1>
    </div>
    <div class="content-header-right">
        <a href="commission-pay.php" class="btn btn-success btn-sm">Pay Commissions</a>
        <a href="staff-report.php" class="btn btn-primary btn-sm">Staff Report</a>
        <a href="commission-csv.php?<?php echo http_build_query(array_filter(array(
            'staff_id' => $staffFilter ?: null,
            'commission_status' => $statusFilter ?: null,
            'date_from' => $dateFrom ?: null,
            'date_to' => $dateTo ?: null,
        ))); ?>" class="btn btn-default btn-sm">Export CSV</a>
    </div>
</section>

<section class="content">
    <div class="row">
        <div class="col-md-12">
            <div class="box box-info">
                <div class="box-body">
                    <form method="get" class="form-inline" style="margin-bottom:15px;">
                        <div class="form-group" style="margin-right:10px;">
                            <label>Staff</label>
                            <select name="staff_id" class="form-control">
                                <option value="0">All Staff</option>
                                <?php foreach ($staffList as $staff) { ?>
                                <option value="<?php echo (int)$staff['staff_id']; ?>" <?php echo ($staffFilter === (int)$staff['staff_id']) ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($staff['full_name']); ?>
                                </option>
                                <?php } ?>
                            </select>
                        </div>
                        <div class="form-group" style="margin-right:10px;">
                            <label>Status</label>
                            <select name="commission_status" class="form-control">
                                <option value="">All</option>
                                <option value="pending" <?php echo ($statusFilter === 'pending') ? 'selected' : ''; ?>>Pending</option>
                                <option value="approved" <?php echo ($statusFilter === 'approved') ? 'selected' : ''; ?>>Approved</option>
                                <option value="paid" <?php echo ($statusFilter === 'paid') ? 'selected' : ''; ?>>Paid</option>
                            </select>
                        </div>
                        <div class="form-group" style="margin-right:10px;">
                            <label>From</label>
                            <input type="date" name="date_from" class="form-control" value="<?php echo htmlspecialchars($dateFrom); ?>">
                        </div>
                        <div class="form-group" style="margin-right:10px;">
                            <label>To</label>
                            <input type="date" name="date_to" class="form-control" value="<?php echo htmlspecialchars($dateTo); ?>">
                        </div>
                        <button type="submit" class="btn btn-primary">Filter</button>
                        <a href="commission.php" class="btn btn-default">Reset</a>
                    </form>

                    <div class="row" style="margin-bottom:15px;">
                        <div class="col-sm-3"><div class="info-box bg-aqua"><div class="info-box-content"><span class="info-box-text">Total</span><span class="info-box-number">Rs. <?php echo number_format($totals['all'], 2); ?></span></div></div></div>
                        <div class="col-sm-3"><div class="info-box bg-yellow"><div class="info-box-content"><span class="info-box-text">Pending</span><span class="info-box-number">Rs. <?php echo number_format($totals['pending'], 2); ?></span></div></div></div>
                        <div class="col-sm-3"><div class="info-box bg-green"><div class="info-box-content"><span class="info-box-text">Approved</span><span class="info-box-number">Rs. <?php echo number_format($totals['approved'], 2); ?></span></div></div></div>
                        <div class="col-sm-3"><div class="info-box bg-purple"><div class="info-box-content"><span class="info-box-text">Paid</span><span class="info-box-number">Rs. <?php echo number_format($totals['paid'], 2); ?></span></div></div></div>
                    </div>

                    <div class="table-responsive">
                        <table id="example1" class="table table-bordered table-hover table-striped">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>Assigned</th>
                                    <th>Order</th>
                                    <th>Staff</th>
                                    <th>Service</th>
                                    <th>Client</th>
                                    <th>Job Status</th>
                                    <th>Commission</th>
                                    <th>Status</th>
                                    <th>Completed</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                $i = 0;
                                foreach ($rows as $row) {
                                    $i++;
                                    $ruleLabel = ($row['commission_type'] === 'percent')
                                        ? number_format((float)$row['commission_value'], 2) . '%'
                                        : 'Rs. ' . number_format((float)$row['commission_value'], 2);
                                    ?>
                                    <tr>
                                        <td><?php echo $i; ?></td>
                                        <td><?php echo htmlspecialchars($row['assigned_at'] ?? ''); ?></td>
                                        <td>
                                            <?php if (!empty($row['payment_row_id'])) { ?>
                                                <a href="order-show.php?id=<?php echo (int)$row['payment_row_id']; ?>"><?php echo htmlspecialchars($row['payment_id']); ?></a>
                                            <?php } else { ?>
                                                <?php echo htmlspecialchars($row['payment_id']); ?>
                                            <?php } ?>
                                        </td>
                                        <td><?php echo htmlspecialchars($row['staff_name'] ?? '—'); ?></td>
                                        <td><?php echo htmlspecialchars($row['service_name']); ?></td>
                                        <td><?php echo htmlspecialchars($row['client_name']); ?></td>
                                        <td><?php echo htmlspecialchars($row['job_status']); ?></td>
                                        <td>
                                            Rs. <?php echo number_format((float)$row['commission_amount'], 2); ?>
                                            <br><small class="text-muted"><?php echo htmlspecialchars($ruleLabel); ?></small>
                                        </td>
                                        <td><?php echo htmlspecialchars($row['commission_status']); ?></td>
                                        <td><?php echo htmlspecialchars($row['completed_at'] ?? '—'); ?></td>
                                        <td>
                                            <?php if ($row['commission_status'] === 'approved') { ?>
                                                <a href="commission-pay.php?staff_id=<?php echo (int)$row['staff_id']; ?>" class="btn btn-xs btn-success">Mark Paid</a>
                                            <?php } elseif (!empty($row['payment_row_id'])) { ?>
                                                <a href="order-show.php?id=<?php echo (int)$row['payment_row_id']; ?>" class="btn btn-xs btn-info">Order</a>
                                            <?php } else { ?>
                                                —
                                            <?php } ?>
                                        </td>
                                    </tr>
                                <?php } ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<?php require_once('footer.php'); ?>
