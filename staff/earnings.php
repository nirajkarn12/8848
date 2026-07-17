<?php
require_once __DIR__ . '/inc/bootstrap.php';
requireStaffLogin();

$staffId = (int)$_SESSION['staff']['staff_id'];
$pageTitle = 'My Earnings';

$statement = $pdo->prepare("
    SELECT *
    FROM tbl_booking_assignment
    WHERE staff_id = ?
    ORDER BY assignment_id DESC
");
$statement->execute(array($staffId));
$jobs = $statement->fetchAll(PDO::FETCH_ASSOC);

$totals = array(
    'pending' => 0.0,
    'approved' => 0.0,
    'paid' => 0.0,
    'all' => 0.0,
);

foreach ($jobs as $job) {
    $amount = (float)$job['commission_amount'];
    $totals['all'] += $amount;
    if (isset($totals[$job['commission_status']])) {
        $totals[$job['commission_status']] += $amount;
    }
}

include __DIR__ . '/inc/header.php';
?>

<section class="content-header">
	<div class="content-header-left">
		<h1>My Earnings</h1>
	</div>
</section>

<section class="content">
	<div class="row">
		<div class="col-md-4 col-sm-6 col-xs-12">
			<div class="info-box">
				<span class="info-box-icon bg-yellow"><i class="fa fa-clock-o"></i></span>
				<div class="info-box-content">
					<span class="info-box-text">Pending</span>
					<span class="info-box-number">Rs. <?php echo number_format($totals['pending'], 2); ?></span>
				</div>
			</div>
		</div>
		<div class="col-md-4 col-sm-6 col-xs-12">
			<div class="info-box">
				<span class="info-box-icon bg-green"><i class="fa fa-check"></i></span>
				<div class="info-box-content">
					<span class="info-box-text">Approved</span>
					<span class="info-box-number">Rs. <?php echo number_format($totals['approved'], 2); ?></span>
				</div>
			</div>
		</div>
		<div class="col-md-4 col-sm-6 col-xs-12">
			<div class="info-box">
				<span class="info-box-icon bg-aqua"><i class="fa fa-money"></i></span>
				<div class="info-box-content">
					<span class="info-box-text">Paid</span>
					<span class="info-box-number">Rs. <?php echo number_format($totals['paid'], 2); ?></span>
				</div>
			</div>
		</div>
	</div>

	<div class="row">
		<div class="col-md-12">
			<div class="box box-info">
				<div class="box-header with-border">
					<h3 class="box-title">Commission History</h3>
				</div>
				<div class="box-body table-responsive">
					<?php if (!$jobs) { ?>
						<div class="alert alert-info" style="margin:0;">No earnings yet. Commissions appear after jobs are assigned.</div>
					<?php } else { ?>
					<table id="example1" class="table table-bordered table-hover table-striped">
						<thead>
							<tr>
								<th>#</th>
								<th>Service</th>
								<th>Client</th>
								<th>Schedule</th>
								<th>Job Status</th>
								<th>Commission</th>
								<th>Status</th>
								<th>Action</th>
							</tr>
						</thead>
						<tbody>
							<?php
							$i = 0;
							foreach ($jobs as $job) {
								$i++;
								$labelClass = 'default';
								if ($job['commission_status'] === 'approved') {
									$labelClass = 'success';
								} elseif ($job['commission_status'] === 'paid') {
									$labelClass = 'info';
								} elseif ($job['commission_status'] === 'pending') {
									$labelClass = 'warning';
								}
								?>
								<tr>
									<td><?php echo $i; ?></td>
									<td><?php echo htmlspecialchars($job['service_name']); ?></td>
									<td><?php echo htmlspecialchars($job['client_name']); ?></td>
									<td><?php echo htmlspecialchars(trim(($job['preferred_date'] ?? '') . ' ' . ($job['preferred_time'] ?? ''))); ?></td>
									<td><?php echo htmlspecialchars($job['job_status']); ?></td>
									<td><strong>Rs. <?php echo number_format((float)$job['commission_amount'], 2); ?></strong></td>
									<td><span class="label label-<?php echo $labelClass; ?>"><?php echo htmlspecialchars($job['commission_status']); ?></span></td>
									<td><a href="job.php?id=<?php echo (int)$job['assignment_id']; ?>" class="btn btn-primary btn-xs">View Job</a></td>
								</tr>
							<?php } ?>
						</tbody>
					</table>
					<?php } ?>
				</div>
			</div>
		</div>
	</div>
</section>

<?php include __DIR__ . '/inc/footer.php'; ?>
