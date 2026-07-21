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
