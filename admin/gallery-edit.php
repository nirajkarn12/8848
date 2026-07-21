<?php require_once('header.php'); ?>

<?php
if (!isset($_REQUEST['id'])) {
	header('location: logout.php');
	exit;
}

try {
	$pdo->query("SELECT mcat_id FROM tbl_gallery LIMIT 1");
} catch (Throwable $e) {
	$pdo->exec("ALTER TABLE `tbl_gallery` ADD COLUMN `mcat_id` int NOT NULL DEFAULT 0 AFTER `photo`");
}

$categories = $pdo->query("
	SELECT m.mcat_id, m.mcat_name, t.tcat_name
	FROM tbl_mid_category m
	JOIN tbl_top_category t ON t.tcat_id = m.tcat_id
	ORDER BY t.tcat_name ASC, m.mcat_name ASC
")->fetchAll(PDO::FETCH_ASSOC);

$statement = $pdo->prepare("SELECT * FROM tbl_gallery WHERE id=?");
$statement->execute(array($_REQUEST['id']));
$total = $statement->rowCount();
$result = $statement->fetchAll(PDO::FETCH_ASSOC);
if ($total == 0) {
	header('location: logout.php');
	exit;
}

foreach ($result as $row) {
	$title = $row['title'];
	$content = $row['content'];
	$photo = $row['photo'];
	$mcat_id = (int)($row['mcat_id'] ?? 0);
	$status = $row['status'];
	$sort_order = $row['sort_order'];
}

if (isset($_POST['form1'])) {
	$valid = 1;

	if (empty($_POST['title'])) {
		$valid = 0;
		$error_message .= 'Title can not be empty<br>';
	}

	$mcat_id = (int)($_POST['mcat_id'] ?? 0);
	if ($mcat_id <= 0) {
		$valid = 0;
		$error_message .= 'Please select a category<br>';
	}

	$path = $_FILES['photo']['name'] ?? '';
	$path_tmp = $_FILES['photo']['tmp_name'] ?? '';
	$ext = '';

	if ($path !== '') {
		$ext = strtolower(pathinfo($path, PATHINFO_EXTENSION));
		if (!in_array($ext, array('jpg', 'jpeg', 'png', 'gif', 'webp'), true)) {
			$valid = 0;
			$error_message .= 'You must upload a jpg, jpeg, png, gif or webp file<br>';
		}
	}

	if ($valid == 1) {
		$title = trim($_POST['title']);
		$content = trim($_POST['content'] ?? '');
		$status = ($_POST['status'] ?? '') === 'Inactive' ? 'Inactive' : 'Active';
		$sort_order = (int)($_POST['sort_order'] ?? 0);

		if ($path === '') {
			$statement = $pdo->prepare("UPDATE tbl_gallery SET title=?, content=?, mcat_id=?, status=?, sort_order=? WHERE id=?");
			$statement->execute(array($title, $content, $mcat_id, $status, $sort_order, $_REQUEST['id']));
		} else {
			if (!empty($_POST['current_photo']) && file_exists('../assets/uploads/' . $_POST['current_photo'])) {
				@unlink('../assets/uploads/' . $_POST['current_photo']);
			}

			$final_name = 'gallery-' . (int)$_REQUEST['id'] . '.' . $ext;
			move_uploaded_file($path_tmp, '../assets/uploads/' . $final_name);

			$statement = $pdo->prepare("UPDATE tbl_gallery SET title=?, content=?, photo=?, mcat_id=?, status=?, sort_order=? WHERE id=?");
			$statement->execute(array($title, $content, $final_name, $mcat_id, $status, $sort_order, $_REQUEST['id']));
			$photo = $final_name;
		}

		header('location: gallery.php?updated=1');
		exit;
	}
}
?>

<section class="content-header">
	<div class="content-header-left">
		<h1>Edit Gallery Photo</h1>
	</div>
	<div class="content-header-right">
		<a href="gallery.php" class="btn btn-primary btn-sm">View All</a>
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

			<form class="form-horizontal" action="" method="post" enctype="multipart/form-data">
				<input type="hidden" name="current_photo" value="<?php echo htmlspecialchars($photo); ?>">
				<div class="box box-info">
					<div class="box-body">
						<div class="form-group">
							<label for="" class="col-sm-2 control-label">Category <span>*</span></label>
							<div class="col-sm-6">
								<select name="mcat_id" class="form-control" required>
									<option value="">Select category</option>
									<?php
									$currentTop = '';
									foreach ($categories as $cat) {
										if ($currentTop !== $cat['tcat_name']) {
											if ($currentTop !== '') {
												echo '</optgroup>';
											}
											$currentTop = $cat['tcat_name'];
											echo '<optgroup label="' . htmlspecialchars($currentTop) . '">';
										}
										$selected = ((int)$mcat_id === (int)$cat['mcat_id']) ? ' selected' : '';
										echo '<option value="' . (int)$cat['mcat_id'] . '"' . $selected . '>' . htmlspecialchars($cat['mcat_name']) . '</option>';
									}
									if ($currentTop !== '') {
										echo '</optgroup>';
									}
									?>
								</select>
							</div>
						</div>
						<div class="form-group">
							<label for="" class="col-sm-2 control-label">Title <span>*</span></label>
							<div class="col-sm-6">
								<input type="text" autocomplete="off" class="form-control" name="title" value="<?php echo htmlspecialchars($title); ?>">
							</div>
						</div>
						<div class="form-group">
							<label for="" class="col-sm-2 control-label">Caption</label>
							<div class="col-sm-6">
								<textarea class="form-control" name="content" style="height:140px;"><?php echo htmlspecialchars((string)$content); ?></textarea>
							</div>
						</div>
						<div class="form-group">
							<label for="" class="col-sm-2 control-label">Existing Photo</label>
							<div class="col-sm-9" style="padding-top:5px">
								<?php if ($photo !== '') { ?>
									<img src="../assets/uploads/<?php echo htmlspecialchars($photo); ?>" alt="Gallery Photo" style="width:220px;">
								<?php } else { ?>
									<span class="label label-default">No photo</span>
								<?php } ?>
							</div>
						</div>
						<div class="form-group">
							<label for="" class="col-sm-2 control-label">Change Photo</label>
							<div class="col-sm-6" style="padding-top:5px">
								<input type="file" name="photo"> (jpg, jpeg, png, gif, webp)
							</div>
						</div>
						<div class="form-group">
							<label for="" class="col-sm-2 control-label">Status</label>
							<div class="col-sm-3">
								<select name="status" class="form-control">
									<option value="Active" <?php if ($status === 'Active') echo 'selected'; ?>>Active</option>
									<option value="Inactive" <?php if ($status === 'Inactive') echo 'selected'; ?>>Inactive</option>
								</select>
							</div>
						</div>
						<div class="form-group">
							<label for="" class="col-sm-2 control-label">Sort Order</label>
							<div class="col-sm-3">
								<input type="number" class="form-control" name="sort_order" value="<?php echo (int)$sort_order; ?>">
							</div>
						</div>
						<div class="form-group">
							<label for="" class="col-sm-2 control-label"></label>
							<div class="col-sm-6">
								<button type="submit" class="btn btn-success pull-left" name="form1">Update</button>
							</div>
						</div>
					</div>
				</div>
			</form>
		</div>
	</div>
</section>

<?php require_once('footer.php'); ?>
