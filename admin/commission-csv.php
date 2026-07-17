<?php
ob_start();
session_start();
require_once('inc/config.php');
require_once('inc/commission.php');

if (empty($_SESSION['user'])) {
    header('Location: login.php');
    exit;
}

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

header('Content-Type: text/csv; charset=utf-8');
header('Content-Disposition: attachment; filename=commission_report.csv');

$output = fopen('php://output', 'w');
fputcsv($output, array(
    'Assignment ID',
    'Assigned At',
    'Order ID',
    'Staff',
    'Staff Phone',
    'Service',
    'Client',
    'Job Status',
    'Commission Type',
    'Commission Value',
    'Commission Amount',
    'Commission Status',
    'Completed At',
    'Paid At',
));

foreach ($rows as $row) {
    fputcsv($output, array(
        $row['assignment_id'],
        $row['assigned_at'] ?? '',
        $row['payment_id'] ?? '',
        $row['staff_name'] ?? '',
        $row['staff_phone'] ?? '',
        $row['service_name'] ?? '',
        $row['client_name'] ?? '',
        $row['job_status'] ?? '',
        $row['commission_type'] ?? '',
        $row['commission_value'] ?? '',
        $row['commission_amount'] ?? '',
        $row['commission_status'] ?? '',
        $row['completed_at'] ?? '',
        $row['paid_at'] ?? '',
    ));
}

fclose($output);
exit;
