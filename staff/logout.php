<?php
require_once __DIR__ . '/inc/bootstrap.php';
session_destroy();
header('Location: ' . STAFF_URL . 'login.php');
exit;
