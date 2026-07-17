<?php require_once('header.php'); ?>

<?php
$days = array(0 => 'Sunday', 1 => 'Monday', 2 => 'Tuesday', 3 => 'Wednesday', 4 => 'Thursday', 5 => 'Friday', 6 => 'Saturday');

if (!isset($_REQUEST['id'])) {
    header('location: staff.php');
    exit;
}

$staffId = (int)$_REQUEST['id'];
$statement = $pdo->prepare("SELECT * FROM tbl_staff WHERE staff_id = ?");
$statement->execute(array($staffId));
$staff = $statement->fetch(PDO::FETCH_ASSOC);

if (!$staff) {
    header('location: staff.php');
    exit;
}

$tableReady = true;
try {
    $pdo->query("SELECT 1 FROM tbl_staff_availability LIMIT 1");
} catch (PDOException $e) {
    $tableReady = false;
}

if (!$tableReady) {
    $error_message .= 'Run Phase 4 migration first: <a href="run-staff-phase4-migration.php">run-staff-phase4-migration.php</a><br>';
}

if ($tableReady && isset($_POST['form_add'])) {
    $day = (int)($_POST['day_of_week'] ?? -1);
    $start = trim($_POST['start_time'] ?? '08:00');
    $end = trim($_POST['end_time'] ?? '18:00');
    $isAvailable = isset($_POST['is_available']) ? 1 : 0;

    if ($day < 0 || $day > 6) {
        $error_message .= 'Please select a valid day.<br>';
    } else {
        $statement = $pdo->prepare("
            INSERT INTO tbl_staff_availability (staff_id, day_of_week, start_time, end_time, is_available)
            VALUES (?, ?, ?, ?, ?)
        ");
        $statement->execute(array($staffId, $day, $start . ':00', $end . ':00', $isAvailable));
        $success_message = 'Availability slot added.';
    }
}

if ($tableReady && isset($_GET['delete'])) {
    $availId = (int)$_GET['delete'];
    $statement = $pdo->prepare("DELETE FROM tbl_staff_availability WHERE availability_id = ? AND staff_id = ?");
    $statement->execute(array($availId, $staffId));
    $success_message = 'Availability slot deleted.';
}

if ($tableReady && isset($_POST['form_seed_week'])) {
    $pdo->prepare("DELETE FROM tbl_staff_availability WHERE staff_id = ?")->execute(array($staffId));
    $ins = $pdo->prepare("INSERT INTO tbl_staff_availability (staff_id, day_of_week, start_time, end_time, is_available) VALUES (?, ?, '08:00:00', '18:00:00', 1)");
    foreach (array(1, 2, 3, 4, 5, 6) as $d) { // Mon–Sat
        $ins->execute(array($staffId, $d));
    }
    $success_message = 'Weekday availability (Mon–Sat 8am–6pm) seeded.';
}

$rows = array();
if ($tableReady) {
    $statement = $pdo->prepare("SELECT * FROM tbl_staff_availability WHERE staff_id = ? ORDER BY day_of_week ASC, start_time ASC");
    $statement->execute(array($staffId));
    $rows = $statement->fetchAll(PDO::FETCH_ASSOC);
}
?>

<section class="content-header">
    <div class="content-header-left">
        <h1>Availability — <?php echo htmlspecialchars($staff['full_name']); ?></h1>
    </div>
    <div class="content-header-right">
        <a href="staff-edit.php?id=<?php echo $staffId; ?>" class="btn btn-primary btn-sm">Back to Staff</a>
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

            <?php if ($tableReady) { ?>
            <div class="box box-info">
                <div class="box-header with-border"><h3 class="box-title">Weekly Slots</h3></div>
                <div class="box-body">
                    <form method="post" style="margin-bottom:15px;">
                        <button type="submit" name="form_seed_week" class="btn btn-warning btn-sm" onclick="return confirm('Replace all slots with Mon–Sat 8am–6pm?');">Seed Mon–Sat 8–6</button>
                    </form>

                    <div class="table-responsive">
                        <table class="table table-bordered table-striped">
                            <thead>
                                <tr>
                                    <th>Day</th>
                                    <th>Start</th>
                                    <th>End</th>
                                    <th>Available</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (!$rows) { ?>
                                <tr><td colspan="5">No slots yet. Add below or seed Mon–Sat.</td></tr>
                                <?php } ?>
                                <?php foreach ($rows as $row) { ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($days[(int)$row['day_of_week']] ?? $row['day_of_week']); ?></td>
                                    <td><?php echo htmlspecialchars(substr($row['start_time'], 0, 5)); ?></td>
                                    <td><?php echo htmlspecialchars(substr($row['end_time'], 0, 5)); ?></td>
                                    <td><?php echo ((int)$row['is_available'] === 1) ? 'Yes' : 'No'; ?></td>
                                    <td>
                                        <a href="staff-availability.php?id=<?php echo $staffId; ?>&amp;delete=<?php echo (int)$row['availability_id']; ?>" class="btn btn-danger btn-xs" onclick="return confirm('Delete this slot?');">Delete</a>
                                    </td>
                                </tr>
                                <?php } ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <form class="form-horizontal" method="post">
                <div class="box box-primary">
                    <div class="box-header with-border"><h3 class="box-title">Add Slot</h3></div>
                    <div class="box-body">
                        <div class="form-group">
                            <label class="col-sm-2 control-label">Day</label>
                            <div class="col-sm-3">
                                <select name="day_of_week" class="form-control" required>
                                    <?php foreach ($days as $num => $label) { ?>
                                    <option value="<?php echo $num; ?>"><?php echo $label; ?></option>
                                    <?php } ?>
                                </select>
                            </div>
                        </div>
                        <div class="form-group">
                            <label class="col-sm-2 control-label">Start</label>
                            <div class="col-sm-2">
                                <input type="time" name="start_time" class="form-control" value="08:00" required>
                            </div>
                            <label class="col-sm-1 control-label">End</label>
                            <div class="col-sm-2">
                                <input type="time" name="end_time" class="form-control" value="18:00" required>
                            </div>
                        </div>
                        <div class="form-group">
                            <label class="col-sm-2 control-label">Available</label>
                            <div class="col-sm-4" style="padding-top:7px;">
                                <label><input type="checkbox" name="is_available" value="1" checked> Yes</label>
                            </div>
                        </div>
                        <div class="form-group">
                            <label class="col-sm-2 control-label"></label>
                            <div class="col-sm-4">
                                <button type="submit" name="form_add" class="btn btn-success">Add Slot</button>
                            </div>
                        </div>
                    </div>
                </div>
            </form>
            <?php } ?>
        </div>
    </div>
</section>

<?php require_once('footer.php'); ?>
