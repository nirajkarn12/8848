<?php require_once('header.php'); ?>
<?php require_once('inc/commission.php'); ?>

<?php
$error_message = $error_message ?? '';
$success_message = $success_message ?? '';

$staffFilter = (int)($_GET['staff_id'] ?? $_POST['staff_id'] ?? 0);

if (isset($_POST['form_pay'])) {
    $ids = isset($_POST['assignment_ids']) && is_array($_POST['assignment_ids'])
        ? array_map('intval', $_POST['assignment_ids'])
        : array();
    $ids = array_values(array_filter($ids));

    if (!$ids) {
        $error_message .= 'Please select at least one approved commission to mark as paid.<br>';
    } else {
        $placeholders = implode(',', array_fill(0, count($ids), '?'));
        $hasPaidAt = commissionColumnExists($pdo, 'tbl_booking_assignment', 'paid_at');

        if ($hasPaidAt) {
            $sql = "
                UPDATE tbl_booking_assignment
                SET commission_status = 'paid', paid_at = NOW()
                WHERE assignment_id IN ($placeholders)
                  AND commission_status = 'approved'
            ";
        } else {
            $sql = "
                UPDATE tbl_booking_assignment
                SET commission_status = 'paid'
                WHERE assignment_id IN ($placeholders)
                  AND commission_status = 'approved'
            ";
        }

        $statement = $pdo->prepare($sql);
        $statement->execute($ids);
        $updated = $statement->rowCount();

        if ($updated > 0) {
            $success_message = $updated . ' commission(s) marked as paid.';
        } else {
            $error_message .= 'No approved commissions were updated. They may already be paid.<br>';
        }
    }
}

$where = array("a.commission_status = 'approved'");
$params = array();
if ($staffFilter > 0) {
    $where[] = 'a.staff_id = ?';
    $params[] = $staffFilter;
}

$sql = "
    SELECT a.*, s.full_name AS staff_name, s.phone AS staff_phone
    FROM tbl_booking_assignment a
    LEFT JOIN tbl_staff s ON s.staff_id = a.staff_id
    WHERE " . implode(' AND ', $where) . "
    ORDER BY a.completed_at ASC, a.assignment_id ASC
";
$statement = $pdo->prepare($sql);
$statement->execute($params);
$rows = $statement->fetchAll(PDO::FETCH_ASSOC);

$approvedTotal = 0.0;
foreach ($rows as $row) {
    $approvedTotal += (float)$row['commission_amount'];
}

$staffList = $pdo->query("SELECT staff_id, full_name FROM tbl_staff ORDER BY full_name ASC")->fetchAll(PDO::FETCH_ASSOC);
?>

<section class="content-header">
    <div class="content-header-left">
        <h1>Pay Staff Commissions</h1>
    </div>
    <div class="content-header-right">
        <a href="commission.php?commission_status=approved" class="btn btn-primary btn-sm">Commission Report</a>
        <a href="staff-report.php" class="btn btn-default btn-sm">Staff Report</a>
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
                        <a href="commission-pay.php" class="btn btn-default">Reset</a>
                    </form>

                    <p>
                        Approved balance ready to pay:
                        <strong>Rs. <?php echo number_format($approvedTotal, 2); ?></strong>
                        (<?php echo count($rows); ?> job<?php echo count($rows) === 1 ? '' : 's'; ?>)
                    </p>

                    <?php if (!$rows) { ?>
                        <div class="alert alert-info">No approved commissions waiting for payout.</div>
                    <?php } else { ?>
                    <form method="post" action="">
                        <input type="hidden" name="staff_id" value="<?php echo (int)$staffFilter; ?>">
                        <div class="table-responsive">
                            <table class="table table-bordered table-hover table-striped">
                                <thead>
                                    <tr>
                                        <th width="40"><input type="checkbox" id="check-all"></th>
                                        <th>Completed</th>
                                        <th>Order</th>
                                        <th>Staff</th>
                                        <th>Service</th>
                                        <th>Client</th>
                                        <th>Commission</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($rows as $row) { ?>
                                    <tr>
                                        <td>
                                            <input type="checkbox" class="pay-check" name="assignment_ids[]" value="<?php echo (int)$row['assignment_id']; ?>">
                                        </td>
                                        <td><?php echo htmlspecialchars($row['completed_at'] ?? $row['approved_at'] ?? '—'); ?></td>
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
                                        <td><strong>Rs. <?php echo number_format((float)$row['commission_amount'], 2); ?></strong></td>
                                    </tr>
                                    <?php } ?>
                                </tbody>
                            </table>
                        </div>
                        <button type="submit" name="form_pay" class="btn btn-success" onclick="return confirm('Mark selected commissions as Paid?');">
                            Mark Selected as Paid
                        </button>
                    </form>
                    <?php } ?>
                </div>
            </div>
        </div>
    </div>
</section>

<script>
document.getElementById('check-all') && document.getElementById('check-all').addEventListener('change', function () {
    var checks = document.querySelectorAll('.pay-check');
    for (var i = 0; i < checks.length; i++) {
        checks[i].checked = this.checked;
    }
});
</script>

<?php require_once('footer.php'); ?>
