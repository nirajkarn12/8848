<?php
$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
header('Location: gallery-delete.php' . ($id > 0 ? '?id=' . $id : ''));
exit;
