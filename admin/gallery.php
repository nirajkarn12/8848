<?php require_once('header.php'); ?>

<?php
if (isset($_GET['success'])) {
	$count = isset($_GET['count']) ? (int)$_GET['count'] : 1;
	$success_message = $count > 1
		? $count . ' gallery photos added successfully!'
		: 'Gallery item is added successfully!';
} elseif (isset($_GET['updated'])) {
	$success_message = 'Gallery item is updated successfully!';
} elseif (isset($_GET['deleted'])) {
	$success_message = 'Gallery item is deleted successfully!';
}

$tableReady = true;
try {
	$pdo->query("SELECT 1 FROM tbl_gallery LIMIT 1");
} catch (Throwable $e) {
	$tableReady = false;
	$error_message = 'Gallery table is missing. Run <a href="run-gallery-migration.php">migration</a> first.';
}

$hasCategory = false;
if ($tableReady) {
	try {
		$pdo->query("SELECT mcat_id FROM tbl_gallery LIMIT 1");
		$hasCategory = true;
	} catch (Throwable $e) {
		$error_message = 'Category column missing. Run <a href="run-gallery-category-migration.php">category migration</a> first.';
	}
}
?>

<section class="content-header">
	<div class="content-header-left">
		<h1>Gallery</h1>
	</div>
	<div class="content-header-right">
		<a href="gallery-add.php" class="btn btn-primary btn-sm">Add Photos</a>
	</div>
</section>

<section class="content">
	<div class="row">
		<div class="col-md-12">
			<?php if ($error_message): ?>
			<div class="callout callout-danger">
				<p><?php echo $error_message; ?></p>
			</div>
			<?php endif; ?>

			<?php if ($success_message): ?>
			<div class="callout callout-success">
				<p><?php echo $success_message; ?></p>
			</div>
			<?php endif; ?>

			<?php if ($tableReady && $hasCategory): ?>
			<div class="box box-info">
				<div class="box-body table-responsive">
					<table id="example1" class="table table-bordered table-hover table-striped">
						<thead>
							<tr>
								<th width="30">#</th>
								<th>Photo</th>
								<th>Title</th>
								<th>Category</th>
								<th>Caption</th>
								<th width="90">Status</th>
								<th width="70">Order</th>
								<th width="120">Action</th>
							</tr>
						</thead>
						<tbody>
							<?php
							$i = 0;
							$statement = $pdo->prepare("
								SELECT g.*, m.mcat_name, t.tcat_name
								FROM tbl_gallery g
								LEFT JOIN tbl_mid_category m ON m.mcat_id = g.mcat_id
								LEFT JOIN tbl_top_category t ON t.tcat_id = m.tcat_id
								ORDER BY g.sort_order ASC, g.id DESC
							");
							$statement->execute();
							$result = $statement->fetchAll(PDO::FETCH_ASSOC);
							foreach ($result as $row) {
								$i++;
								$catLabel = 'Uncategorized';
								if (!empty($row['mcat_name'])) {
									$catLabel = trim(($row['tcat_name'] ? $row['tcat_name'] . ' › ' : '') . $row['mcat_name']);
								}
								?>
								<tr>
									<td><?php echo $i; ?></td>
									<td style="width:130px;">
										<?php if ($row['photo'] !== '') { ?>
											<img src="../assets/uploads/<?php echo htmlspecialchars($row['photo']); ?>" alt="<?php echo htmlspecialchars($row['title']); ?>" style="width:120px;height:80px;object-fit:cover;">
										<?php } else { ?>
											<span class="label label-default">No photo</span>
										<?php } ?>
									</td>
									<td><?php echo htmlspecialchars($row['title']); ?></td>
									<td><?php echo htmlspecialchars($catLabel); ?></td>
									<td><?php echo htmlspecialchars(mb_strimwidth((string)$row['content'], 0, 100, '…')); ?></td>
									<td><?php echo htmlspecialchars($row['status']); ?></td>
									<td><?php echo (int)$row['sort_order']; ?></td>
									<td>
										<a href="gallery-edit.php?id=<?php echo (int)$row['id']; ?>" class="btn btn-primary btn-xs">Edit</a>
										<a href="#" class="btn btn-danger btn-xs" data-href="gallery-delete.php?id=<?php echo (int)$row['id']; ?>" data-toggle="modal" data-target="#confirm-delete">Delete</a>
									</td>
								</tr>
								<?php
							}
							?>
						</tbody>
					</table>
				</div>
			</div>
			<?php endif; ?>
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
				<p>Are you sure want to delete this gallery item?</p>
			</div>
			<div class="modal-footer">
				<button type="button" class="btn btn-default" data-dismiss="modal">Cancel</button>
				<a class="btn btn-danger btn-ok">Delete</a>
			</div>
		</div>
	</div>
</div>

<?php require_once('footer.php'); ?>
