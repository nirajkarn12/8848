<?php
require_once('inc/config.php');

function migrationTableExists($pdo, $table) {
    $statement = $pdo->prepare("SHOW TABLES LIKE ?");
    $statement->execute(array($table));
    return $statement->rowCount() > 0;
}

$messages = array();
$errors = array();

try {
    if (!migrationTableExists($pdo, 'tbl_testimonial')) {
        $pdo->exec("
            CREATE TABLE `tbl_testimonial` (
              `id` int NOT NULL AUTO_INCREMENT,
              `name` varchar(100) NOT NULL,
              `designation` varchar(150) NOT NULL DEFAULT '',
              `company` varchar(150) NOT NULL DEFAULT '',
              `review` text NOT NULL,
              `rating` tinyint NOT NULL DEFAULT 5,
              `photo` varchar(255) NOT NULL DEFAULT '',
              `status` varchar(20) NOT NULL DEFAULT 'Active',
              `sort_order` int NOT NULL DEFAULT 0,
              `created_at` datetime DEFAULT NULL,
              PRIMARY KEY (`id`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
        ");
        $messages[] = 'Created tbl_testimonial';
    } else {
        $messages[] = 'Skipped tbl_testimonial (exists)';
    }

    $count = (int)$pdo->query("SELECT COUNT(*) FROM tbl_testimonial")->fetchColumn();
    if ($count === 0) {
        $seed = $pdo->prepare("INSERT INTO tbl_testimonial (name, designation, company, review, rating, photo, status, sort_order, created_at) VALUES (?, ?, ?, ?, ?, '', 'Active', ?, NOW())");
        $seed->execute(array('Anisha Shrestha', 'Homeowner', 'Kathmandu', 'The team arrived on time and left our home spotless. Booking online was easy and communication was clear.', 5, 1));
        $seed->execute(array('Rajesh Thapa', 'Office Manager', 'Lalitpur', 'We use 8848 Cleaning Service for our office every week. Reliable staff and consistent quality.', 5, 2));
        $seed->execute(array('Maya Gurung', 'Apartment Owner', 'Bhaktapur', 'Deep clean before moving in was excellent. Highly recommend for anyone who wants a professional finish.', 4, 3));
        $messages[] = 'Seeded 3 demo testimonials';
    } else {
        $messages[] = 'Skipped seed (testimonials already exist)';
    }
} catch (Throwable $e) {
    $errors[] = $e->getMessage();
}
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Testimonial Migration</title>
    <link rel="stylesheet" href="css/bootstrap.min.css">
</head>
<body class="container" style="padding:40px;">
    <h2>Testimonial / Reviews Migration</h2>
    <?php foreach ($messages as $m) { ?>
        <div class="alert alert-success"><?php echo htmlspecialchars($m); ?></div>
    <?php } ?>
    <?php foreach ($errors as $e) { ?>
        <div class="alert alert-danger"><?php echo htmlspecialchars($e); ?></div>
    <?php } ?>
    <p><a class="btn btn-primary" href="testimonial.php">Go to Testimonials</a></p>
</body>
</html>
