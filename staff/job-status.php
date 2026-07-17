<?php
require_once __DIR__ . '/inc/bootstrap.php';
requireStaffLogin();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ' . STAFF_URL . 'index.php');
    exit;
}

$assignmentId = (int)($_POST['assignment_id'] ?? 0);
$jobStatus = trim($_POST['job_status'] ?? '');
$staffNotes = trim($_POST['staff_notes'] ?? '');
$checkinLat = trim($_POST['checkin_lat'] ?? '');
$checkinLng = trim($_POST['checkin_lng'] ?? '');
$staffId = (int)$_SESSION['staff']['staff_id'];

if ($assignmentId <= 0 || !in_array($jobStatus, staffJobStatuses(), true)) {
    header('Location: ' . STAFF_URL . 'index.php');
    exit;
}

$statement = $pdo->prepare("SELECT assignment_id, payment_id, commission_status FROM tbl_booking_assignment WHERE assignment_id = ? AND staff_id = ? LIMIT 1");
$statement->execute(array($assignmentId, $staffId));
$job = $statement->fetch(PDO::FETCH_ASSOC);

if (!$job) {
    header('Location: ' . STAFF_URL . 'index.php');
    exit;
}

$commissionStatus = $job['commission_status'] ?: 'pending';
$completedAt = null;
$approvedAt = null;
$arrivedAt = null;
$lat = null;
$lng = null;

if ($jobStatus === 'Completed') {
    $commissionStatus = 'approved';
    $completedAt = date('Y-m-d H:i:s');
    $approvedAt = $completedAt;
}

if ($jobStatus === 'Arrived') {
    $arrivedAt = date('Y-m-d H:i:s');
    if ($checkinLat !== '' && $checkinLng !== '' && is_numeric($checkinLat) && is_numeric($checkinLng)) {
        $lat = (float)$checkinLat;
        $lng = (float)$checkinLng;
    }
}

$hasApprovedAt = false;
$hasArrivedAt = false;
$hasLat = false;
try {
    $hasApprovedAt = $pdo->query("SHOW COLUMNS FROM tbl_booking_assignment LIKE 'approved_at'")->rowCount() > 0;
    $hasArrivedAt = $pdo->query("SHOW COLUMNS FROM tbl_booking_assignment LIKE 'arrived_at'")->rowCount() > 0;
    $hasLat = $pdo->query("SHOW COLUMNS FROM tbl_booking_assignment LIKE 'checkin_lat'")->rowCount() > 0;
} catch (PDOException $e) {
    // ignore
}

$set = array('job_status = ?', 'staff_notes = ?');
$params = array($jobStatus, $staffNotes);

if ($jobStatus === 'Completed') {
    $set[] = 'commission_status = ?';
    $params[] = $commissionStatus;
    $set[] = 'completed_at = COALESCE(?, completed_at)';
    $params[] = $completedAt;
    if ($hasApprovedAt) {
        $set[] = 'approved_at = COALESCE(?, approved_at)';
        $params[] = $approvedAt;
    }
} elseif ($jobStatus !== 'Cancelled') {
    // Keep existing commission_status unless completing
}

if ($jobStatus === 'Arrived' && $hasArrivedAt) {
    $set[] = 'arrived_at = COALESCE(?, arrived_at)';
    $params[] = $arrivedAt;
    if ($hasLat && $lat !== null && $lng !== null) {
        $set[] = 'checkin_lat = COALESCE(?, checkin_lat)';
        $set[] = 'checkin_lng = COALESCE(?, checkin_lng)';
        $params[] = $lat;
        $params[] = $lng;
    }
}

$params[] = $assignmentId;
$params[] = $staffId;

$statement = $pdo->prepare("
    UPDATE tbl_booking_assignment
    SET " . implode(', ', $set) . "
    WHERE assignment_id = ? AND staff_id = ?
");
$statement->execute($params);

if ($jobStatus === 'Completed') {
    try {
        // Only mark booking completed when all non-cancelled assignments are completed
        $check = $pdo->prepare("
            SELECT COUNT(*) FROM tbl_booking_assignment
            WHERE payment_id = ? AND job_status NOT IN ('Completed', 'Cancelled')
        ");
        $check->execute(array($job['payment_id']));
        if ((int)$check->fetchColumn() === 0) {
            $statement = $pdo->prepare("UPDATE tbl_payment SET assignment_status = 'Completed', booking_status = 'Completed' WHERE payment_id = ?");
            $statement->execute(array($job['payment_id']));
        }
    } catch (PDOException $e) {
        // Optional columns may not exist yet.
    }
}

header('Location: ' . STAFF_URL . 'job.php?id=' . $assignmentId);
exit;
