<?php
require_once __DIR__ . '/../inc/functions.php';
$pageTitle = 'Register';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verifyCsrf($_POST['csrf_token'] ?? '')) {
        setFlash('danger', 'Invalid request.');
        header('Location: ' . BASE_URL . 'account/register.php');
        exit;
    }

    $name = trim($_POST['cust_name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $phone = trim($_POST['phone'] ?? '');
    $password = trim($_POST['password'] ?? '');
    $confirm = trim($_POST['confirm'] ?? '');

    if ($name === '' || $email === '' || $phone === '' || $password === '' || $confirm === '') {
        setFlash('danger', 'Please fill all fields.');
        header('Location: ' . BASE_URL . 'account/register.php');
        exit;
    }

    if ($password !== $confirm) {
        setFlash('danger', 'Passwords do not match.');
        header('Location: ' . BASE_URL . 'account/register.php');
        exit;
    }

    $check = $pdo->prepare('SELECT cust_id FROM tbl_customer WHERE cust_email = ? LIMIT 1');
    $check->execute([$email]);
    if ($check->fetch()) {
        setFlash('danger', 'This email already exists.');
        header('Location: ' . BASE_URL . 'account/register.php');
        exit;
    }

    $stmt = $pdo->prepare('INSERT INTO tbl_customer (cust_name, cust_cname, cust_email, cust_phone, cust_country, cust_address, cust_city, cust_state, cust_zip, cust_b_name, cust_b_cname, cust_b_phone, cust_b_country, cust_b_address, cust_b_city, cust_b_state, cust_b_zip, cust_s_name, cust_s_cname, cust_s_phone, cust_s_country, cust_s_address, cust_s_city, cust_s_state, cust_s_zip, cust_password, cust_token, cust_datetime, cust_timestamp, cust_status) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)');
    $stmt->execute([
        $name, '', $email, $phone, 0, '', '', '', '', '', '', '', 0, '', '', '', '', '', '', '', 0, '', '', '', '', '', '', date('Y-m-d H:i:s'), time(), 1
    ]);

    $customerId = $pdo->lastInsertId();
    $hashed = password_hash($password, PASSWORD_DEFAULT);
    $pdo->prepare('UPDATE tbl_customer SET cust_password = ? WHERE cust_id = ?')->execute([$hashed, $customerId]);

    $_SESSION['customer_id'] = $customerId;
    $_SESSION['customer_name'] = $name;
    setFlash('success', 'Registration successful.');
    header('Location: ' . BASE_URL . 'account/profile.php');
    exit;
}

include __DIR__ . '/../inc/header.php';
$breadcrumbs = [
    ['label' => 'Home', 'url' => BASE_URL],
    ['label' => 'Register', 'url' => '']
];
echo renderBreadcrumbs($breadcrumbs);
?>
<div class="row justify-content-center">
  <div class="col-lg-6">
    <div class="card card-hover p-4">
      <h3 class="fw-bold mb-3">Create account</h3>
      <form method="post" class="row g-3">
        <input type="hidden" name="csrf_token" value="<?php echo e(csrfToken()); ?>">
        <div class="col-md-6"><label class="form-label">Full name</label><input class="form-control" name="cust_name" required></div>
        <div class="col-md-6"><label class="form-label">Email</label><input class="form-control" type="email" name="email" required></div>
        <div class="col-md-6"><label class="form-label">Phone</label><input class="form-control" name="phone" required></div>
        <div class="col-md-6"><label class="form-label">Password</label><input class="form-control" type="password" name="password" required></div>
        <div class="col-12"><label class="form-label">Confirm password</label><input class="form-control" type="password" name="confirm" required></div>
        <div class="col-12"><button class="btn btn-dark">Register</button></div>
        <div class="col-12 text-center small text-muted">
          Already have an account? <a href="<?php echo BASE_URL; ?>account/login.php" class="text-decoration-none fw-bold">Login here</a>
        </div>
      </form>
    </div>
  </div>
</div>
<?php include __DIR__ . '/../inc/footer.php'; ?>
