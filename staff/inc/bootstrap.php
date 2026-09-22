<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../../inc/functions.php';

if (!defined('STAFF_URL')) {
    define('STAFF_URL', BASE_URL . 'staff/');
}

function staffIsLoggedIn() {
    return !empty($_SESSION['staff']['staff_id']);
}

function requireStaffLogin() {
    if (!staffIsLoggedIn()) {
        header('Location: ' . STAFF_URL . 'login.php');
        exit;
    }
}

function currentStaff() {
    return $_SESSION['staff'] ?? null;
}

function staffEnsureResetFields() {
    global $pdo;

    if (!$pdo) {
        return;
    }

    try {
        $columns = $pdo->query("SHOW COLUMNS FROM tbl_staff")->fetchAll(PDO::FETCH_COLUMN, 0);
        $columns = array_map('strtolower', $columns);

        if (!in_array('reset_code', $columns, true)) {
            $pdo->exec("ALTER TABLE tbl_staff ADD COLUMN reset_code VARCHAR(20) NOT NULL DEFAULT ''");
        }

        if (!in_array('reset_code_expires_at', $columns, true)) {
            $pdo->exec("ALTER TABLE tbl_staff ADD COLUMN reset_code_expires_at DATETIME NULL");
        }
    } catch (Throwable $e) {
        // Ignore DB migration failures here; caller will still attempt the reset flow.
    }
}

function staffPhotoUrl($photo) {
    if (empty($photo)) {
        return BASE_URL . 'assets/uploads/user-1.jpg';
    }
    return BASE_URL . 'assets/uploads/' . ltrim($photo, '/');
}

function staffJobStatuses() {
    return array('Assigned', 'En Route', 'Arrived', 'In Progress', 'Completed', 'Cancelled');
}

function mapsUrlForAddress($address, $lat = null, $lng = null) {
    return mapsUrlForCoordinates(
        normalizeMapCoordinate($lat, -90, 90),
        normalizeMapCoordinate($lng, -180, 180),
        $address
    );
}
