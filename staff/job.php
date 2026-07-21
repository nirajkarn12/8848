<?php
require_once __DIR__ . '/inc/bootstrap.php';
requireStaffLogin();

$assignmentId = (int)($_GET['id'] ?? 0);
$staffId = (int)$_SESSION['staff']['staff_id'];

$statement = $pdo->prepare("SELECT * FROM tbl_booking_assignment WHERE assignment_id = ? AND staff_id = ? LIMIT 1");
$statement->execute(array($assignmentId, $staffId));
$job = $statement->fetch(PDO::FETCH_ASSOC);

if (!$job) {
    header('Location: ' . STAFF_URL . 'index.php');
    exit;
}

$serviceLat = normalizeMapCoordinate($job['service_lat'] ?? null, -90, 90);
$serviceLng = normalizeMapCoordinate($job['service_lng'] ?? null, -180, 180);

// Fallback to booking payment coordinates if assignment was created before map feature
if (($serviceLat === null || $serviceLng === null) && !empty($job['payment_row_id'])) {
    try {
        $payStmt = $pdo->prepare("SELECT service_lat, service_lng, service_address FROM tbl_payment WHERE id = ? LIMIT 1");
        $payStmt->execute(array((int)$job['payment_row_id']));
        $payRow = $payStmt->fetch(PDO::FETCH_ASSOC);
        if ($payRow) {
            if ($serviceLat === null) {
                $serviceLat = normalizeMapCoordinate($payRow['service_lat'] ?? null, -90, 90);
            }
            if ($serviceLng === null) {
                $serviceLng = normalizeMapCoordinate($payRow['service_lng'] ?? null, -180, 180);
            }
            if (empty($job['service_address']) && !empty($payRow['service_address'])) {
                $job['service_address'] = $payRow['service_address'];
            }
        }
    } catch (Throwable $e) {
        // ignore
    }
}

$pageTitle = 'Job Details';
include __DIR__ . '/inc/header.php';
?>

<section class="content-header">
	<div class="content-header-left">
		<h1>Job Details</h1>
	</div>
	<div class="content-header-right">
		<a href="<?php echo STAFF_URL; ?>index.php" class="btn btn-primary btn-sm">Back to Jobs</a>
	</div>
</section>

<section class="content">
	<div class="row">
		<div class="col-md-8">
			<div class="box box-info">
				<div class="box-header with-border">
					<h3 class="box-title"><?php echo htmlspecialchars($job['service_name']); ?></h3>
				</div>
				<div class="box-body">
					<table class="table table-bordered">
						<tr><th width="30%">Job Status</th><td><span class="label label-primary"><?php echo htmlspecialchars($job['job_status']); ?></span></td></tr>
						<tr><th>Client Name</th><td><?php echo htmlspecialchars($job['client_name']); ?></td></tr>
						<tr>
							<th>Client Phone</th>
							<td>
								<a href="tel:<?php echo htmlspecialchars(preg_replace('/\s+/', '', $job['client_phone'])); ?>">
									<?php echo htmlspecialchars($job['client_phone']); ?>
								</a>
							</td>
						</tr>
						<tr><th>Client Email</th><td><?php echo htmlspecialchars($job['client_email']); ?></td></tr>
						<tr>
							<th>Go To Address</th>
							<td><?php echo nl2br(htmlspecialchars($job['service_address'])); ?></td>
						</tr>
						<tr><th>Schedule</th><td><?php echo htmlspecialchars(trim(($job['preferred_date'] ?? 'Not set') . ' ' . ($job['preferred_time'] ?? ''))); ?></td></tr>
						<tr><th>Your Commission</th><td>Rs. <?php echo number_format((float)$job['commission_amount'], 2); ?> (<?php echo htmlspecialchars($job['commission_status']); ?>)</td></tr>
						<?php if (!empty($job['arrived_at'])) { ?>
						<tr>
							<th>Checked In</th>
							<td>
								<?php echo htmlspecialchars($job['arrived_at']); ?>
								<?php if (!empty($job['checkin_lat']) && !empty($job['checkin_lng'])) { ?>
									<br>
									<a target="_blank" rel="noopener" href="https://www.google.com/maps?q=<?php echo urlencode($job['checkin_lat'] . ',' . $job['checkin_lng']); ?>">
										View GPS map (<?php echo htmlspecialchars($job['checkin_lat']); ?>, <?php echo htmlspecialchars($job['checkin_lng']); ?>)
									</a>
								<?php } ?>
							</td>
						</tr>
						<?php } ?>
						<?php if (!empty($job['admin_notes'])) { ?>
						<tr><th>Admin Notes</th><td><?php echo nl2br(htmlspecialchars($job['admin_notes'])); ?></td></tr>
						<?php } ?>
					</table>
				</div>
			</div>

			<div class="box box-success staff-map">
				<div class="box-header with-border">
					<h3 class="box-title"><i class="fa fa-map"></i> Client Service Location (OpenStreetMap)</h3>
				</div>
				<div class="box-body">
					<p class="text-muted">Same map pin selected by the client while booking. Use directions to navigate.</p>
					<?php echo renderServiceLocationViewer([
						'lat' => $serviceLat,
						'lng' => $serviceLng,
						'address' => $job['service_address'] ?? '',
						'id' => 'staffJobMap',
						'class' => 'staff-map',
					]); ?>
				</div>
			</div>
		</div>

		<div class="col-md-4">
			<?php if (!in_array($job['job_status'], array('Completed', 'Cancelled'), true)) { ?>
			<div class="box box-success">
				<div class="box-header with-border">
					<h3 class="box-title">Quick Check-In</h3>
				</div>
				<div class="box-body">
					<p>When you arrive on site, check in to capture GPS (if allowed).</p>
					<button type="button" class="btn btn-success btn-block" id="btn-checkin">
						<i class="fa fa-map-marker"></i> Check In (Arrived)
					</button>
					<p id="checkin-status" class="help-block" style="margin-top:10px;"></p>
				</div>
			</div>

			<div class="box box-primary">
				<div class="box-header with-border">
					<h3 class="box-title">Update Status</h3>
				</div>
				<form method="post" action="job-status.php" class="form-horizontal" id="status-form">
					<input type="hidden" name="assignment_id" value="<?php echo (int)$job['assignment_id']; ?>">
					<input type="hidden" name="checkin_lat" id="checkin_lat" value="">
					<input type="hidden" name="checkin_lng" id="checkin_lng" value="">
					<div class="box-body">
						<div class="form-group">
							<label class="col-sm-12">New Status</label>
							<div class="col-sm-12">
								<select name="job_status" id="job_status" class="form-control" required>
									<?php foreach (staffJobStatuses() as $status) { ?>
									<option value="<?php echo htmlspecialchars($status); ?>" <?php echo ($job['job_status'] === $status) ? 'selected' : ''; ?>><?php echo htmlspecialchars($status); ?></option>
									<?php } ?>
								</select>
							</div>
						</div>
						<div class="form-group">
							<label class="col-sm-12">Staff Notes</label>
							<div class="col-sm-12">
								<textarea name="staff_notes" class="form-control" rows="3"><?php echo htmlspecialchars($job['staff_notes'] ?? ''); ?></textarea>
							</div>
						</div>
					</div>
					<div class="box-footer">
						<button type="submit" class="btn btn-primary">Save Status</button>
					</div>
				</form>
			</div>
			<?php } else { ?>
			<div class="box box-default">
				<div class="box-body">
					<p class="text-muted" style="margin:0;">This job is <?php echo htmlspecialchars($job['job_status']); ?>. Status can no longer be changed.</p>
				</div>
			</div>
			<?php } ?>
		</div>
	</div>
</section>

<?php if (!in_array($job['job_status'], array('Completed', 'Cancelled'), true)) { ?>
<script>
(function () {
    var form = document.getElementById('status-form');
    var btn = document.getElementById('btn-checkin');
    var statusEl = document.getElementById('checkin-status');

    function submitArrived(lat, lng) {
        document.getElementById('job_status').value = 'Arrived';
        document.getElementById('checkin_lat').value = lat || '';
        document.getElementById('checkin_lng').value = lng || '';
        form.submit();
    }

    if (btn) {
        btn.addEventListener('click', function () {
            statusEl.textContent = 'Getting location...';
            if (!navigator.geolocation) {
                statusEl.textContent = 'GPS not available — checking in without location.';
                submitArrived('', '');
                return;
            }
            navigator.geolocation.getCurrentPosition(function (pos) {
                submitArrived(pos.coords.latitude, pos.coords.longitude);
            }, function () {
                statusEl.textContent = 'Location denied — checking in without GPS.';
                submitArrived('', '');
            }, { enableHighAccuracy: true, timeout: 10000 });
        });
    }
})();
</script>
<?php } ?>

<?php echo serviceLocationAssets(); ?>
<?php include __DIR__ . '/inc/footer.php'; ?>
