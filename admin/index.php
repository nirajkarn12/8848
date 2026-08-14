<?php require_once('header.php'); ?>

<style>
    /* ===== GLOBAL CONTAINER ===== */
    .dashboard-wrapper {
        padding: 0 20px 20px 20px;
        max-width: 1600px;
        margin: 0 auto;
    }

    /* ===== DASHBOARD HEADER ===== */
    .dashboard-header {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: #fff;
        padding: 20px 30px;
        border-radius: 10px;
        margin-bottom: 25px;
        box-shadow: 0 8px 30px rgba(102, 126, 234, 0.3);
        display: flex;
        flex-wrap: wrap;
        justify-content: space-between;
        align-items: center;
        gap: 15px;
    }
    .dashboard-header h2 {
        font-weight: 300;
        font-size: 28px;
        margin: 0;
    }
    .dashboard-header .date {
        font-size: 14px;
        opacity: 0.9;
        margin-top: 2px;
    }
    .dashboard-header .header-actions {
        display: flex;
        flex-wrap: wrap;
        gap: 8px;
        align-items: center;
    }
    .dashboard-header .header-actions a {
        color: #fff;
        background: rgba(255,255,255,0.15);
        padding: 8px 18px;
        border-radius: 30px;
        font-size: 13px;
        text-decoration: none;
        transition: background 0.2s;
        border: 1px solid rgba(255,255,255,0.2);
        white-space: nowrap;
    }
    .dashboard-header .header-actions a:hover {
        background: rgba(255,255,255,0.3);
    }

    /* ===== DATE FILTER BAR ===== */
    .filter-bar {
        background: #fff;
        padding: 12px 20px;
        border-radius: 12px;
        box-shadow: 0 2px 10px rgba(0,0,0,0.04);
        margin-bottom: 25px;
        display: flex;
        flex-wrap: wrap;
        align-items: center;
        gap: 12px;
    }
    .filter-bar .filter-group {
        display: flex;
        align-items: center;
        gap: 6px;
        flex-wrap: wrap;
    }
    .filter-bar label {
        font-weight: 600;
        font-size: 14px;
        color: #555;
        margin: 0;
    }
    .filter-bar input[type="date"] {
        border: 1px solid #ddd;
        border-radius: 6px;
        padding: 6px 12px;
        font-size: 14px;
        background: #fafafa;
        transition: border 0.2s;
    }
    .filter-bar input[type="date"]:focus {
        border-color: #667eea;
        outline: none;
        box-shadow: 0 0 0 3px rgba(102,126,234,0.2);
    }
    .filter-bar .btn {
        border-radius: 30px;
        padding: 6px 20px;
        font-size: 13px;
        font-weight: 500;
        border: none;
        cursor: pointer;
        transition: all 0.2s;
    }
    .filter-bar .btn-primary {
        background: #667eea;
        color: #fff;
    }
    .filter-bar .btn-primary:hover {
        background: #5a6fd6;
        transform: translateY(-1px);
    }
    .filter-bar .btn-outline-secondary {
        background: transparent;
        border: 1px solid #ccc;
        color: #555;
    }
    .filter-bar .btn-outline-secondary:hover {
        background: #f0f0f0;
        border-color: #999;
    }
    .filter-bar .quick-presets {
        display: flex;
        gap: 6px;
        flex-wrap: wrap;
        margin-left: auto;
    }

    /* ===== STAT CARDS ===== */
    .stats-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(180px, 1fr));
        gap: 20px;
        margin-bottom: 30px;
    }
    .stat-card {
        background: #fff;
        padding: 18px 18px 16px;
        border-radius: 14px;
        box-shadow: 0 2px 12px rgba(0,0,0,0.06);
        border-left: 5px solid #667eea;
        transition: transform 0.2s, box-shadow 0.2s;
        position: relative;
    }
    .stat-card:hover {
        transform: translateY(-3px);
        box-shadow: 0 8px 25px rgba(0,0,0,0.08);
    }
    .stat-card .stat-number {
        font-size: 28px;
        font-weight: 700;
        line-height: 1.2;
        color: #1a1a2e;
    }
    .stat-card .stat-label {
        font-size: 13px;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        color: #888;
        margin-top: 4px;
    }
    .stat-card .stat-change {
        font-size: 13px;
        font-weight: 500;
        margin-left: 8px;
    }
    .stat-card .stat-change.up { color: #28a745; }
    .stat-card .stat-change.down { color: #dc3545; }
    .stat-card .stat-icon {
        position: absolute;
        right: 16px;
        top: 16px;
        font-size: 24px;
        opacity: 0.2;
    }
    .stat-card.border-revenue { border-left-color: #ff6b6b; }
    .stat-card.border-orders { border-left-color: #4ecdc4; }
    .stat-card.border-customers { border-left-color: #45b7d1; }
    .stat-card.border-aov { border-left-color: #f9ca24; }
    .stat-card.border-pending { border-left-color: #f0932b; }
    .stat-card.border-completed { border-left-color: #6ab04c; }
    .stat-card.border-unassigned { border-left-color: #e17055; }
    .stat-card.border-commission { border-left-color: #00b894; }
    .stat-card.border-due { border-left-color: #0984e3; }

    /* ===== CHARTS ===== */
    .charts-row {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
        gap: 20px;
        margin-bottom: 30px;
    }
    .chart-card {
        background: #fff;
        padding: 18px 18px 10px;
        border-radius: 14px;
        box-shadow: 0 2px 12px rgba(0,0,0,0.06);
    }
    .chart-card .chart-title {
        font-size: 16px;
        font-weight: 600;
        color: #222;
        margin-bottom: 12px;
        display: flex;
        justify-content: space-between;
        align-items: center;
    }
    .chart-card .chart-title small {
        font-weight: 400;
        font-size: 12px;
        color: #999;
    }
    .chart-container {
        height: 200px;
        position: relative;
    }
    .chart-container.tall { height: 260px; }

    /* ===== TABLES ===== */
    .table-card {
        background: #fff;
        padding: 18px;
        border-radius: 14px;
        box-shadow: 0 2px 12px rgba(0,0,0,0.06);
        margin-bottom: 20px;
    }
    .table-card .table-title {
        font-size: 16px;
        font-weight: 600;
        color: #222;
        margin-bottom: 12px;
    }
    .table-card table {
        width: 100%;
        font-size: 14px;
        border-collapse: collapse;
    }
    .table-card table th {
        text-align: left;
        padding: 10px 6px;
        border-bottom: 2px solid #eee;
        color: #555;
        font-weight: 600;
        font-size: 12px;
        text-transform: uppercase;
        letter-spacing: 0.3px;
    }
    .table-card table td {
        padding: 8px 6px;
        border-bottom: 1px solid #f5f5f5;
        color: #333;
    }
    .table-card table tr:last-child td { border-bottom: none; }
    .badge-status {
        padding: 3px 10px;
        border-radius: 30px;
        font-size: 11px;
        font-weight: 600;
        display: inline-block;
    }
    .badge-status.completed { background: #d4edda; color: #155724; }
    .badge-status.pending { background: #fff3cd; color: #856404; }
    .badge-status.shipping { background: #cce5ff; color: #004085; }

    /* ===== RESPONSIVE ===== */
    @media (max-width: 768px) {
        .dashboard-header { flex-direction: column; align-items: flex-start; }
        .dashboard-header .header-actions { width: 100%; }
        .filter-bar { flex-direction: column; align-items: stretch; }
        .filter-bar .quick-presets { margin-left: 0; }
        .stats-grid { grid-template-columns: repeat(2, 1fr); }
        .charts-row { grid-template-columns: 1fr; }
        .chart-container { height: 180px; }
        .chart-container.tall { height: 200px; }
    }
    @media (max-width: 480px) {
        .stats-grid { grid-template-columns: 1fr; }
        .dashboard-wrapper { padding: 0 10px 10px; }
    }
</style>

<section class="content-header" style="margin-bottom: 25px;">
    <h1>Dashboard</h1>
</section>

<div class="dashboard-wrapper">

<?php
// ============================================================
// 1. DATE FILTER HANDLING
// ============================================================
$from_date = isset($_GET['from_date']) ? $_GET['from_date'] : date('Y-m-01');
$to_date   = isset($_GET['to_date'])   ? $_GET['to_date']   : date('Y-m-d');

// ============================================================
// 2. FETCH ALL REQUIRED DATA (with date filters)
// ============================================================

// ---------- 2a. Total counts (static) ----------
$stmt = $pdo->query("SELECT COUNT(*) FROM tbl_top_category");
$total_top_category = $stmt->fetchColumn();
$stmt = $pdo->query("SELECT COUNT(*) FROM tbl_mid_category");
$total_mid_category = $stmt->fetchColumn();
$stmt = $pdo->query("SELECT COUNT(*) FROM tbl_end_category");
$total_end_category = $stmt->fetchColumn();
$stmt = $pdo->query("SELECT COUNT(*) FROM tbl_product");
$total_product = $stmt->fetchColumn();
$stmt = $pdo->query("SELECT COUNT(*) FROM tbl_customer WHERE cust_status='1'");
$total_customers = $stmt->fetchColumn();
$stmt = $pdo->query("SELECT COUNT(*) FROM tbl_subscriber WHERE subs_active='1'");
$total_subscriber = $stmt->fetchColumn();

// Staff / commission widgets (Phase 3)
$unassigned_bookings = 0;
$approved_commission_due = 0.0;
$pending_commission_total = 0.0;
try {
    if ($pdo->query("SHOW TABLES LIKE 'tbl_booking_assignment'")->rowCount() > 0) {
        $pending_commission_total = (float)$pdo->query("SELECT COALESCE(SUM(commission_amount),0) FROM tbl_booking_assignment WHERE commission_status='pending'")->fetchColumn();
        $approved_commission_due = (float)$pdo->query("SELECT COALESCE(SUM(commission_amount),0) FROM tbl_booking_assignment WHERE commission_status='approved'")->fetchColumn();
    }
    $colCheck = $pdo->query("SHOW COLUMNS FROM tbl_payment LIKE 'assignment_status'");
    if ($colCheck->rowCount() > 0) {
        $unassigned_bookings = (int)$pdo->query("SELECT COUNT(*) FROM tbl_payment WHERE assignment_status='Unassigned' OR assignment_status IS NULL OR assignment_status=''")->fetchColumn();
    }
} catch (PDOException $e) {
    // Staff tables may not exist yet.
}

// ---------- 2b. Dynamic KPIs ----------
// Revenue
$stmt = $pdo->prepare("SELECT COALESCE(SUM(paid_amount), 0) FROM tbl_payment WHERE payment_status='Completed' AND DATE(payment_date) BETWEEN ? AND ?");
$stmt->execute([$from_date, $to_date]);
$total_revenue = $stmt->fetchColumn();

// Total orders
$stmt = $pdo->prepare("SELECT COUNT(*) FROM tbl_payment WHERE DATE(payment_date) BETWEEN ? AND ?");
$stmt->execute([$from_date, $to_date]);
$total_orders = $stmt->fetchColumn();

// Completed orders
$stmt = $pdo->prepare("SELECT COUNT(*) FROM tbl_payment WHERE payment_status='Completed' AND DATE(payment_date) BETWEEN ? AND ?");
$stmt->execute([$from_date, $to_date]);
$completed_orders = $stmt->fetchColumn();

// Pending orders
$stmt = $pdo->prepare("SELECT COUNT(*) FROM tbl_payment WHERE payment_status='Pending' AND DATE(payment_date) BETWEEN ? AND ?");
$stmt->execute([$from_date, $to_date]);
$pending_orders = $stmt->fetchColumn();

// Average Order Value
$avg_order_value = ($completed_orders > 0) ? round($total_revenue / $completed_orders, 2) : 0;

// New customers
$stmt = $pdo->prepare("SELECT COUNT(*) FROM tbl_customer WHERE DATE(cust_datetime) BETWEEN ? AND ?");
$stmt->execute([$from_date, $to_date]);
$new_customers = $stmt->fetchColumn();

// Customer growth
$diff_days = (strtotime($to_date) - strtotime($from_date)) / (60*60*24) + 1;
$prev_from = date('Y-m-d', strtotime($from_date . " -$diff_days days"));
$prev_to   = date('Y-m-d', strtotime($to_date . " -$diff_days days"));
$stmt = $pdo->prepare("SELECT COUNT(*) FROM tbl_customer WHERE DATE(cust_datetime) BETWEEN ? AND ?");
$stmt->execute([$prev_from, $prev_to]);
$prev_customers = $stmt->fetchColumn();
$customer_change = ($prev_customers > 0) ? (($new_customers - $prev_customers) / $prev_customers) * 100 : 0;
$customer_change_text = ($customer_change >= 0 ? '+' : '') . number_format($customer_change, 1) . '%';
$customer_change_class = ($customer_change >= 0) ? 'up' : 'down';

// Revenue change
$stmt = $pdo->prepare("SELECT COALESCE(SUM(paid_amount), 0) FROM tbl_payment WHERE payment_status='Completed' AND DATE(payment_date) BETWEEN ? AND ?");
$stmt->execute([$prev_from, $prev_to]);
$prev_revenue = $stmt->fetchColumn();
$revenue_change = ($prev_revenue > 0) ? (($total_revenue - $prev_revenue) / $prev_revenue) * 100 : 0;
$revenue_change_text = ($revenue_change >= 0 ? '+' : '') . number_format($revenue_change, 1) . '%';
$revenue_change_class = ($revenue_change >= 0) ? 'up' : 'down';

// ---------- 2c. Revenue & Orders trend ----------
$period_days = (strtotime($to_date) - strtotime($from_date)) / (60*60*24) + 1;
$group_by = ($period_days <= 31) ? "DATE(payment_date)" : "DATE_FORMAT(payment_date, '%Y-%m')";
$label_format = ($period_days <= 31) ? "DATE(payment_date)" : "DATE_FORMAT(payment_date, '%Y-%m')";
$stmt = $pdo->prepare("
    SELECT $label_format as label, COALESCE(SUM(paid_amount), 0) as revenue, COUNT(*) as orders
    FROM tbl_payment
    WHERE payment_status='Completed' AND DATE(payment_date) BETWEEN ? AND ?
    GROUP BY $group_by
    ORDER BY payment_date ASC
");
$stmt->execute([$from_date, $to_date]);
$trend_data = $stmt->fetchAll(PDO::FETCH_ASSOC);
$trend_labels = array_column($trend_data, 'label');
$trend_revenue = array_column($trend_data, 'revenue');
$trend_orders = array_column($trend_data, 'orders');

// ---------- 2d. Payment method breakdown ----------
$stmt = $pdo->prepare("
    SELECT payment_method, COALESCE(SUM(paid_amount), 0) as total
    FROM tbl_payment
    WHERE payment_status='Completed' AND DATE(payment_date) BETWEEN ? AND ?
    GROUP BY payment_method
");
$stmt->execute([$from_date, $to_date]);
$method_data = $stmt->fetchAll(PDO::FETCH_ASSOC);
$method_labels = array_column($method_data, 'payment_method');
$method_values = array_column($method_data, 'total');
if (empty($method_labels)) {
    $method_labels = ['No Data'];
    $method_values = [1];
}
$method_colors = ['#ff6b6b', '#4ecdc4', '#45b7d1', '#f9ca24', '#6ab04c'];
$method_color_assigned = array_slice($method_colors, 0, count($method_labels));

// ---------- 2e. Order status breakdown ----------
$stmt = $pdo->prepare("
    SELECT 
        CASE 
            WHEN payment_status='Completed' AND shipping_status='Completed' THEN 'Completed'
            WHEN payment_status='Completed' AND shipping_status='Pending' THEN 'Shipping Pending'
            WHEN payment_status='Pending' THEN 'Payment Pending'
            ELSE 'Other'
        END as status,
        COUNT(*) as count
    FROM tbl_payment
    WHERE DATE(payment_date) BETWEEN ? AND ?
    GROUP BY status
");
$stmt->execute([$from_date, $to_date]);
$status_data = $stmt->fetchAll(PDO::FETCH_ASSOC);
$status_labels = array_column($status_data, 'status');
$status_counts = array_column($status_data, 'count');
if (empty($status_labels)) {
    $status_labels = ['No Orders'];
    $status_counts = [1];
}
$status_colors = ['#6ab04c', '#f0932b', '#eb4d4b', '#888'];

// ---------- 2f. Customer acquisition ----------
$stmt = $pdo->prepare("
    SELECT DATE_FORMAT(cust_datetime, '%Y-%m') as month, COUNT(*) as count
    FROM tbl_customer
    WHERE DATE(cust_datetime) BETWEEN ? AND ?
    GROUP BY month
    ORDER BY month ASC
");
$stmt->execute([$from_date, $to_date]);
$cust_trend = $stmt->fetchAll(PDO::FETCH_ASSOC);
$cust_labels = array_column($cust_trend, 'month');
$cust_counts = array_column($cust_trend, 'count');
if (empty($cust_labels)) {
    $cust_labels = ['No Data'];
    $cust_counts = [0];
}

// ---------- 2g. Top customers ----------
$stmt = $pdo->prepare("
    SELECT 
        c.cust_name,
        c.cust_email,
        COUNT(p.payment_id) as order_count,
        COALESCE(SUM(p.paid_amount), 0) as total_spent
    FROM tbl_payment p
    JOIN tbl_customer c ON p.customer_id = c.cust_id
    WHERE p.payment_status='Completed' AND DATE(p.payment_date) BETWEEN ? AND ?
    GROUP BY p.customer_id
    ORDER BY total_spent DESC
    LIMIT 5
");
$stmt->execute([$from_date, $to_date]);
$top_customers = $stmt->fetchAll(PDO::FETCH_ASSOC);

// ---------- 2h. Recent orders ----------
$stmt = $pdo->prepare("
    SELECT 
        p.payment_id,
        c.cust_name,
        p.paid_amount,
        p.payment_status,
        p.shipping_status,
        p.payment_date
    FROM tbl_payment p
    JOIN tbl_customer c ON p.customer_id = c.cust_id
    WHERE DATE(p.payment_date) BETWEEN ? AND ?
    ORDER BY p.payment_date DESC
    LIMIT 10
");
$stmt->execute([$from_date, $to_date]);
$recent_orders = $stmt->fetchAll(PDO::FETCH_ASSOC);

// ============================================================
// 3. PREPARE JSON FOR CHARTS
// ============================================================
$trend_labels_json = json_encode($trend_labels);
$trend_revenue_json = json_encode($trend_revenue);
$trend_orders_json = json_encode($trend_orders);
$method_labels_json = json_encode($method_labels);
$method_values_json = json_encode($method_values);
$method_colors_json = json_encode($method_color_assigned);
$status_labels_json = json_encode($status_labels);
$status_counts_json = json_encode($status_counts);
$status_colors_json = json_encode(array_slice($status_colors, 0, count($status_labels)));
$cust_labels_json = json_encode($cust_labels);
$cust_counts_json = json_encode($cust_counts);
?>

<!-- ============================================================
     DASHBOARD HEADER
     ============================================================ -->
<div class="dashboard-header">
    <div>
        <h2>Good Morning, Admin!</h2>
        <div class="date"><?php echo date('F d, Y'); ?></div>
    </div>
    <div class="header-actions">
        <a href="product-add.php"><i class="fa fa-plus"></i> Add Product</a>
        <a href="order.php"><i class="fa fa-list"></i> View Orders</a>
        <a href="customer.php"><i class="fa fa-users"></i> Customers</a>
    </div>
</div>

<!-- ============================================================
     DATE FILTER BAR
     ============================================================ -->
<form method="GET" class="filter-bar" id="filterForm">
    <div class="filter-group">
        <label for="from_date">From</label>
        <input type="date" name="from_date" id="from_date" value="<?php echo $from_date; ?>">
        <label for="to_date">To</label>
        <input type="date" name="to_date" id="to_date" value="<?php echo $to_date; ?>">
        <button type="submit" class="btn btn-primary"><i class="fa fa-filter"></i> Apply</button>
    </div>
    <div class="quick-presets">
        <button type="button" class="btn btn-outline-secondary" data-days="0">Today</button>
        <button type="button" class="btn btn-outline-secondary" data-days="6">This Week</button>
        <button type="button" class="btn btn-outline-secondary" data-days="29">This Month</button>
        <button type="button" class="btn btn-outline-secondary" data-days="89">Last 3 Months</button>
        <button type="button" class="btn btn-outline-secondary" data-days="364">This Year</button>
    </div>
</form>

<!-- ============================================================
     KPI STAT CARDS
     ============================================================ -->
<div class="stats-grid">
    <div class="stat-card border-revenue">
        <div class="stat-number">NZ$<?php echo number_format($total_revenue, 2); ?>
            <span class="stat-change <?php echo $revenue_change_class; ?>"><?php echo $revenue_change_text; ?></span>
        </div>
        <div class="stat-label">Total Revenue</div>
        <div class="stat-icon">💰</div>
    </div>

    <div class="stat-card border-orders">
        <div class="stat-number"><?php echo $total_orders; ?></div>
        <div class="stat-label">Total Orders</div>
        <div class="stat-icon">📦</div>
    </div>

    <div class="stat-card border-customers">
        <div class="stat-number"><?php echo $new_customers; ?>
            <span class="stat-change <?php echo $customer_change_class; ?>"><?php echo $customer_change_text; ?></span>
        </div>
        <div class="stat-label">New Customers</div>
        <div class="stat-icon">👤</div>
    </div>

    <div class="stat-card border-aov">
        <div class="stat-number">NZ$<?php echo number_format($avg_order_value, 2); ?></div>
        <div class="stat-label">Avg Order Value</div>
        <div class="stat-icon">📊</div>
    </div>

    <div class="stat-card border-completed">
        <div class="stat-number"><?php echo $completed_orders; ?></div>
        <div class="stat-label">Completed Orders</div>
        <div class="stat-icon">✅</div>
    </div>

    <div class="stat-card border-pending">
        <div class="stat-number"><?php echo $pending_orders; ?></div>
        <div class="stat-label">Pending Orders</div>
        <div class="stat-icon">⏳</div>
    </div>

    <div class="stat-card border-unassigned">
        <div class="stat-number"><?php echo (int)$unassigned_bookings; ?></div>
        <div class="stat-label"><a href="order.php" style="color:inherit;">Unassigned Bookings</a></div>
        <div class="stat-icon">👷</div>
    </div>

    <div class="stat-card border-commission">
        <div class="stat-number">NZ$<?php echo number_format($pending_commission_total, 0); ?></div>
        <div class="stat-label"><a href="commission.php?commission_status=pending" style="color:inherit;">Pending Commission</a></div>
        <div class="stat-icon">💸</div>
    </div>

    <div class="stat-card border-due">
        <div class="stat-number">NZ$<?php echo number_format($approved_commission_due, 0); ?></div>
        <div class="stat-label"><a href="commission-pay.php" style="color:inherit;">Approved Due (Pay)</a></div>
        <div class="stat-icon">✅</div>
    </div>
</div>

<!-- ============================================================
     CHART ROW 1: Revenue & Orders Trend
     ============================================================ -->
<div class="charts-row">
    <div class="chart-card" style="grid-column: 1 / -1;">
        <div class="chart-title">
            Revenue & Orders Trend
            <small><?php echo $from_date . ' – ' . $to_date; ?></small>
        </div>
        <div class="chart-container tall">
            <canvas id="trendChart"></canvas>
        </div>
    </div>
</div>

<!-- ============================================================
     CHART ROW 2: Payment Methods, Order Status, Customer Acquisition
     ============================================================ -->
<div class="charts-row">
    <div class="chart-card">
        <div class="chart-title">Payment Methods</div>
        <div class="chart-container">
            <canvas id="paymentMethodChart"></canvas>
        </div>
    </div>
    <div class="chart-card">
        <div class="chart-title">Order Status</div>
        <div class="chart-container">
            <canvas id="orderStatusChart"></canvas>
        </div>
    </div>
    <div class="chart-card">
        <div class="chart-title">Customer Acquisition</div>
        <div class="chart-container">
            <canvas id="customerChart"></canvas>
        </div>
    </div>
</div>

<!-- ============================================================
     TABLES: Top Customers & Recent Orders
     ============================================================ -->
<div class="row">
    <div class="col-md-6">
        <div class="table-card">
            <div class="table-title">🏆 Top Customers</div>
            <?php if (!empty($top_customers)): ?>
            <table>
                <thead><tr><th>Customer</th><th>Orders</th><th>Total Spent</th></tr></thead>
                <tbody>
                    <?php foreach ($top_customers as $cust): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($cust['cust_name']); ?><br><small><?php echo htmlspecialchars($cust['cust_email']); ?></small></td>
                        <td><?php echo $cust['order_count']; ?></td>
                        <td>NZ$<?php echo number_format($cust['total_spent'], 2); ?></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
            <?php else: ?>
            <p class="text-muted">No completed orders in this period.</p>
            <?php endif; ?>
        </div>
    </div>
    <div class="col-md-6">
        <div class="table-card">
            <div class="table-title">🕒 Recent Orders</div>
            <?php if (!empty($recent_orders)): ?>
            <table>
                <thead><tr><th>Order ID</th><th>Customer</th><th>Amount</th><th>Status</th><th>Date</th></tr></thead>
                <tbody>
                    <?php foreach ($recent_orders as $order): ?>
                    <tr>
                        <td>#<?php echo $order['payment_id']; ?></td>
                        <td><?php echo htmlspecialchars($order['cust_name']); ?></td>
                        <td>NZ$<?php echo number_format($order['paid_amount'], 2); ?></td>
                        <td>
                            <?php
                            $status_class = 'pending';
                            if ($order['payment_status'] == 'Completed' && $order['shipping_status'] == 'Completed') $status_class = 'completed';
                            elseif ($order['payment_status'] == 'Completed' && $order['shipping_status'] == 'Pending') $status_class = 'shipping';
                            ?>
                            <span class="badge-status <?php echo $status_class; ?>">
                                <?php echo $order['payment_status']; ?>
                                <?php if ($order['shipping_status'] == 'Pending') echo ' (Shipping)'; ?>
                            </span>
                        </td>
                        <td><?php echo date('d M Y', strtotime($order['payment_date'])); ?></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
            <?php else: ?>
            <p class="text-muted">No orders in this period.</p>
            <?php endif; ?>
        </div>
    </div>
</div>

<!-- ============================================================
     ADDITIONAL SYSTEM STATS
     ============================================================ -->
<div class="row" style="margin-top:20px;">
    <div class="col-lg-3 col-xs-6"><div class="small-box bg-aqua"><div class="inner"><h3><?php echo $total_product; ?></h3><p>Products</p></div><div class="icon"><i class="ion ion-android-cart"></i></div></div></div>
    <div class="col-lg-3 col-xs-6"><div class="small-box bg-yellow"><div class="inner"><h3><?php echo $pending_orders; ?></h3><p>Pending Orders (Filtered)</p></div><div class="icon"><i class="ion ion-ios-paper-outline"></i></div></div></div>
    <div class="col-lg-3 col-xs-6"><div class="small-box bg-green"><div class="inner"><h3><?php echo $completed_orders; ?></h3><p>Completed Orders (Filtered)</p></div><div class="icon"><i class="ion ion-checkmark-round"></i></div></div></div>
    <div class="col-lg-3 col-xs-6"><div class="small-box bg-red"><div class="inner"><h3><?php echo $total_customers; ?></h3><p>Active Customers</p></div><div class="icon"><i class="ion ion-person"></i></div></div></div>
</div>

</div> <!-- /dashboard-wrapper -->

<!-- ============================================================
     CHART.JS SCRIPTS
     ============================================================ -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    // ----- 1. Trend Chart (Bar + Line) -----
    const ctxTrend = document.getElementById('trendChart').getContext('2d');
    new Chart(ctxTrend, {
        type: 'bar',
        data: {
            labels: <?php echo $trend_labels_json; ?>,
            datasets: [
                {
                    label: 'Revenue ($)',
                    type: 'line',
                    data: <?php echo $trend_revenue_json; ?>,
                    borderColor: '#667eea',
                    backgroundColor: 'rgba(102,126,234,0.1)',
                    borderWidth: 3,
                    pointRadius: 3,
                    pointBackgroundColor: '#667eea',
                    tension: 0.2,
                    yAxisID: 'y',
                },
                {
                    label: 'Orders',
                    type: 'bar',
                    data: <?php echo $trend_orders_json; ?>,
                    backgroundColor: 'rgba(78,205,196,0.6)',
                    borderRadius: 4,
                    yAxisID: 'y1',
                }
            ]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: { position: 'top' },
                tooltip: { mode: 'index', intersect: false }
            },
            scales: {
                x: { grid: { display: false } },
                y: {
                    beginAtZero: true,
                    grid: { color: 'rgba(0,0,0,0.05)' },
                    position: 'left',
                },
                y1: {
                    beginAtZero: true,
                    grid: { display: false },
                    position: 'right',
                }
            }
        }
    });

    // ----- 2. Payment Method Doughnut -----
    new Chart(document.getElementById('paymentMethodChart'), {
        type: 'doughnut',
        data: {
            labels: <?php echo $method_labels_json; ?>,
            datasets: [{
                data: <?php echo $method_values_json; ?>,
                backgroundColor: <?php echo $method_colors_json; ?>,
                borderWidth: 0,
                cutout: '65%',
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: { position: 'bottom', labels: { boxWidth: 10 } }
            }
        }
    });

    // ----- 3. Order Status Doughnut -----
    new Chart(document.getElementById('orderStatusChart'), {
        type: 'doughnut',
        data: {
            labels: <?php echo $status_labels_json; ?>,
            datasets: [{
                data: <?php echo $status_counts_json; ?>,
                backgroundColor: <?php echo $status_colors_json; ?>,
                borderWidth: 0,
                cutout: '65%',
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: { position: 'bottom', labels: { boxWidth: 10 } }
            }
        }
    });

    // ----- 4. Customer Acquisition Bar -----
    new Chart(document.getElementById('customerChart'), {
        type: 'bar',
        data: {
            labels: <?php echo $cust_labels_json; ?>,
            datasets: [{
                label: 'New Customers',
                data: <?php echo $cust_counts_json; ?>,
                backgroundColor: '#45b7d1',
                borderRadius: 4,
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: { display: false }
            },
            scales: {
                x: { grid: { display: false } },
                y: { beginAtZero: true, grid: { color: 'rgba(0,0,0,0.05)' } }
            }
        }
    });

    // ----- 5. Quick date presets -----
    const presets = document.querySelectorAll('.quick-presets .btn');
    const fromInput = document.getElementById('from_date');
    const toInput = document.getElementById('to_date');
    const filterForm = document.getElementById('filterForm');

    presets.forEach(btn => {
        btn.addEventListener('click', function() {
            const days = parseInt(this.dataset.days);
            const today = new Date();
            let from = new Date(today);
            from.setDate(today.getDate() - days);
            const formatDate = (d) => d.toISOString().split('T')[0];
            fromInput.value = formatDate(from);
            toInput.value = formatDate(today);
            filterForm.submit();
        });
    });
});
</script>

<?php require_once('footer.php'); ?>