<?php
$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
header('Location: gallery-edit.php' . ($id > 0 ? '?id=' . $id : ''));
exit;
