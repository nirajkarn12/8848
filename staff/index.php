<?php
require_once __DIR__ . '/inc/bootstrap.php';
requireStaffLogin();

$staffId = (int)$_SESSION['staff']['staff_id'];
$pageTitle = 'My Jobs';
$today = date('Y-m-d');

$statement = $pdo->prepare("
    SELECT *
    FROM tbl_booking_assignment
    WHERE staff_id = ?
    ORDER BY
        CASE WHEN preferred_date = ? THEN 0 ELSE 1 END,
        preferred_date ASC,
        assignment_id DESC
");
$statement->execute(array($staffId, $today));
$jobs = $statement->fetchAll(PDO::FETCH_ASSOC);

$todayCount = 0;
$activeCount = 0;
$pendingCommission = 0.0;

foreach ($jobs as $job) {
    if (($job['preferred_date'] ?? '') === $today) {
        $todayCount++;
    }
    if (!in_array($job['job_status'], array('Completed', 'Cancelled'), true)) {
        $activeCount++;
    }
    if ($job['commission_status'] === 'pending' || $job['commission_status'] === 'approved') {
        $pendingCommission += (float)$job['commission_amount'];
    }
}

include __DIR__ . '/inc/header.php';
?>

<section class="content-header">
	<div class="content-header-left">
		<h1>My Jobs</h1>
	</div>
</section>

<section class="content">
	<div class="row">
		<div class="col-md-4 col-sm-6 col-xs-12">
			<div class="info-box">
				<span class="info-box-icon bg-aqua"><i class="fa fa-calendar"></i></span>
				<div class="info-box-content">
					<span class="info-box-text">Today</span>
					<span class="info-box-number"><?php echo (int)$todayCount; ?></span>
				</div>
			</div>
		</div>
		<div class="col-md-4 col-sm-6 col-xs-12">
			<div class="info-box">
				<span class="info-box-icon bg-green"><i class="fa fa-briefcase"></i></span>
				<div class="info-box-content">
					<span class="info-box-text">Active Jobs</span>
					<span class="info-box-number"><?php echo (int)$activeCount; ?></span>
				</div>
			</div>
		</div>
		<div class="col-md-4 col-sm-6 col-xs-12">
			<div class="info-box">
				<span class="info-box-icon bg-yellow"><i class="fa fa-money"></i></span>
				<div class="info-box-content">
					<span class="info-box-text">Pending Earnings</span>
					<span class="info-box-number">NZ$ <?php echo number_format($pendingCommission, 0); ?></span>
				</div>
			</div>
		</div>
	</div>

	<div class="row">
		<div class="col-md-12">
			<div class="box box-info">
				<div class="box-header with-border">
					<h3 class="box-title">Assigned Jobs</h3>
				</div>
				<div class="box-body table-responsive">
					<?php if (!$jobs) { ?>
						<div class="alert alert-info" style="margin:0;">No jobs assigned yet. Check back after admin assigns you an order.</div>
					<?php } else { ?>
					<table id="example1" class="table table-bordered table-hover table-striped">
						<thead>
							<tr>
								<th>#</th>
								<th>Service</th>
								<th>Client</th>
								<th>Phone</th>
								<th>Address</th>
								<th>Schedule</th>
								<th>Status</th>
								<th>Commission</th>
								<th width="160">Action</th>
							</tr>
						</thead>
						<tbody>
							<?php
							$i = 0;
							foreach ($jobs as $job) {
								$i++;
								?>
								<tr>
									<td><?php echo $i; ?></td>
									<td><?php echo htmlspecialchars($job['service_name']); ?></td>
									<td><?php echo htmlspecialchars($job['client_name']); ?></td>
									<td>
										<a href="tel:<?php echo htmlspecialchars(preg_replace('/\s+/', '', $job['client_phone'])); ?>">
											<?php echo htmlspecialchars($job['client_phone']); ?>
										</a>
									</td>
									<td><?php echo htmlspecialchars($job['service_address']); ?></td>
									<td><?php echo htmlspecialchars(trim(($job['preferred_date'] ?? '') . ' ' . ($job['preferred_time'] ?? ''))); ?></td>
									<td><span class="label label-primary"><?php echo htmlspecialchars($job['job_status']); ?></span></td>
									<td>NZ$ <?php echo number_format((float)$job['commission_amount'], 2); ?></td>
									<td>
										<a href="job.php?id=<?php echo (int)$job['assignment_id']; ?>" class="btn btn-primary btn-xs">View</a>
										<a href="<?php echo htmlspecialchars(mapsUrlForAddress($job['service_address'])); ?>" target="_blank" rel="noopener" class="btn btn-success btn-xs">Map</a>
									</td>
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
