<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../../config/database.php';

define('STAFF_URL', BASE_URL . 'staff/');

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

function staffPhotoUrl($photo) {
    if (empty($photo)) {
        return BASE_URL . 'assets/uploads/user-1.jpg';
    }
    return BASE_URL . 'assets/uploads/' . ltrim($photo, '/');
}

function staffJobStatuses() {
    return array('Assigned', 'En Route', 'Arrived', 'In Progress', 'Completed', 'Cancelled');
}

function mapsUrlForAddress($address) {
    return 'https://www.google.com/maps/search/?api=1&query=' . urlencode($address);
}
