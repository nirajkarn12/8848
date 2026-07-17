<?php require_once('header.php'); ?>
<?php require_once('inc/commission.php'); ?>

<?php
$staffFilter = (int)($_GET['staff_id'] ?? 0);

$sql = "
    SELECT
        s.staff_id,
        s.full_name,
        s.phone,
        s.email,
        s.status,
        COUNT(a.assignment_id) AS total_jobs,
        SUM(CASE WHEN a.job_status = 'Completed' THEN 1 ELSE 0 END) AS jobs_completed,
        COALESCE(SUM(a.commission_amount), 0) AS commission_total,
        COALESCE(SUM(CASE WHEN a.commission_status = 'pending' THEN a.commission_amount ELSE 0 END), 0) AS commission_pending,
        COALESCE(SUM(CASE WHEN a.commission_status = 'approved' THEN a.commission_amount ELSE 0 END), 0) AS commission_approved,
        COALESCE(SUM(CASE WHEN a.commission_status = 'paid' THEN a.commission_amount ELSE 0 END), 0) AS commission_paid
    FROM tbl_staff s
    LEFT JOIN tbl_booking_assignment a ON a.staff_id = s.staff_id
";

$params = array();
if ($staffFilter > 0) {
    $sql .= " WHERE s.staff_id = ? ";
    $params[] = $staffFilter;
}

$sql .= " GROUP BY s.staff_id, s.full_name, s.phone, s.email, s.status ORDER BY s.full_name ASC";

$statement = $pdo->prepare($sql);
$statement->execute($params);
$rows = $statement->fetchAll(PDO::FETCH_ASSOC);

$staffList = $pdo->query("SELECT staff_id, full_name FROM tbl_staff ORDER BY full_name ASC")->fetchAll(PDO::FETCH_ASSOC);
?>

<section class="content-header">
    <div class="content-header-left">
        <h1>Staff Commission Report</h1>
    </div>
    <div class="content-header-right">
        <a href="commission-pay.php" class="btn btn-success btn-sm">Pay Commissions</a>
        <a href="commission.php" class="btn btn-primary btn-sm">Commission Report</a>
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
                        <button type="submit" class="btn btn-primary">Filter</button>
                        <a href="staff-report.php" class="btn btn-default">Reset</a>
                    </form>

                    <div class="table-responsive">
                        <table id="example1" class="table table-bordered table-hover table-striped">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>Staff</th>
                                    <th>Phone</th>
                                    <th>Status</th>
                                    <th>Jobs</th>
                                    <th>Completed</th>
                                    <th>Total Earned</th>
                                    <th>Pending</th>
                                    <th>Approved (Due)</th>
                                    <th>Paid</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                $i = 0;
                                foreach ($rows as $row) {
                                    $i++;
                                    $balanceDue = (float)$row['commission_approved'];
                                    ?>
                                    <tr>
                                        <td><?php echo $i; ?></td>
                                        <td><?php echo htmlspecialchars($row['full_name']); ?></td>
                                        <td><?php echo htmlspecialchars($row['phone']); ?></td>
                                        <td><?php echo htmlspecialchars($row['status']); ?></td>
                                        <td><?php echo (int)$row['total_jobs']; ?></td>
                                        <td><?php echo (int)$row['jobs_completed']; ?></td>
                                        <td>Rs. <?php echo number_format((float)$row['commission_total'], 2); ?></td>
                                        <td>Rs. <?php echo number_format((float)$row['commission_pending'], 2); ?></td>
                                        <td><strong>Rs. <?php echo number_format($balanceDue, 2); ?></strong></td>
                                        <td>Rs. <?php echo number_format((float)$row['commission_paid'], 2); ?></td>
                                        <td>
                                            <a href="commission.php?staff_id=<?php echo (int)$row['staff_id']; ?>" class="btn btn-xs btn-info">View</a>
                                            <?php if ($balanceDue > 0) { ?>
                                            <a href="commission-pay.php?staff_id=<?php echo (int)$row['staff_id']; ?>" class="btn btn-xs btn-success">Pay</a>
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
