<?php require_once('header.php'); ?>

<section class="content-header">
	<div class="content-header-left">
		<h1>View Staff</h1>
	</div>
	<div class="content-header-right">
		<a href="staff-add.php" class="btn btn-primary btn-sm">Add Staff</a>
	</div>
</section>

<section class="content">
	<div class="row">
		<div class="col-md-12">
			<div class="box box-info">
				<div class="box-body table-responsive">
					<table id="example1" class="table table-bordered table-hover table-striped">
						<thead>
							<tr>
								<th width="10">#</th>
								<th width="80">Photo</th>
								<th width="150">Name</th>
								<th width="150">Email</th>
								<th width="100">Phone</th>
								<th width="100">Role</th>
								<th width="70">Website</th>
								<th width="120">Default Commission</th>
								<th width="60">Status</th>
								<th width="80">Action</th>
							</tr>
						</thead>
						<tbody>
							<?php
							$i = 0;
							$statement = $pdo->prepare("SELECT * FROM tbl_staff ORDER BY staff_id DESC");
							$statement->execute();
							$result = $statement->fetchAll(PDO::FETCH_ASSOC);
							foreach ($result as $row) {
								$i++;
								$commissionLabel = ($row['default_commission_type'] === 'fixed')
									? 'Rs. ' . number_format((float)$row['default_commission_value'], 2)
									: number_format((float)$row['default_commission_value'], 2) . '%';
								?>
								<tr class="<?php echo ($row['status'] === 'Active') ? 'bg-g' : 'bg-r'; ?>">
									<td><?php echo $i; ?></td>
									<td><img src="../assets/uploads/<?php echo htmlspecialchars($row['photo']); ?>" alt="" style="width:50px;height:50px;object-fit:cover;border-radius:50%;"></td>
									<td><?php echo htmlspecialchars($row['full_name']); ?></td>
									<td><?php echo htmlspecialchars($row['email']); ?></td>
									<td><?php echo htmlspecialchars($row['phone']); ?></td>
									<td><?php echo htmlspecialchars($row['designation'] ?? ''); ?></td>
									<td><?php echo !empty($row['show_on_website']) ? 'Yes' : 'No'; ?></td>
									<td><?php echo $commissionLabel; ?></td>
									<td><?php echo htmlspecialchars($row['status']); ?></td>
									<td>
										<a href="staff-edit.php?id=<?php echo (int)$row['staff_id']; ?>" class="btn btn-primary btn-xs">Edit</a>
										<a href="staff-availability.php?id=<?php echo (int)$row['staff_id']; ?>" class="btn btn-warning btn-xs">Availability</a>
										<a href="#" class="btn btn-danger btn-xs" data-href="staff-delete.php?id=<?php echo (int)$row['staff_id']; ?>" data-toggle="modal" data-target="#confirm-delete">Delete</a>
									</td>
								</tr>
								<?php
							}
							if ($i === 0) {
								echo '<tr><td colspan="10" class="text-center">No staff yet. <a href="staff-add.php">Add staff</a> or run <a href="run-staff-migration.php">migration</a>.</td></tr>';
							}
							?>
						</tbody>
					</table>
				</div>
			</div>
		</div>
	</div>
</section>

<div class="modal fade" id="confirm-delete" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
                <h4 class="modal-title" id="myModalLabel">Delete Confirmation</h4>
            </div>
            <div class="modal-body">
                <p>Are you sure want to delete this staff member?</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal">Cancel</button>
                <a class="btn btn-danger btn-ok">Delete</a>
            </div>
        </div>
    </div>
</div>

<?php require_once('footer.php'); ?>
