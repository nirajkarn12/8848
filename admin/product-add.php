<?php require_once('header.php'); ?>

<?php
$error_message = '';
$success_message = '';

if (isset($_POST['form1'])) {

    $valid = 1;

    if (empty($_POST['tcat_id'])) {
        $valid = 0;
        $error_message .= "You must select a top level category<br>";
    }

    if (empty($_POST['mcat_id'])) {
        $valid = 0;
        $error_message .= "You must select a category<br>";
    }

    if (empty($_POST['p_name'])) {
        $valid = 0;
        $error_message .= "Service name cannot be empty<br>";
    }

    if (empty($_POST['p_current_price'])) {
        $valid = 0;
        $error_message .= "Current price cannot be empty<br>";
    }

    // Featured photo validation
    if (!empty($_FILES['p_featured_photo']['name'])) {
        $ext = strtolower(pathinfo($_FILES['p_featured_photo']['name'], PATHINFO_EXTENSION));
        if (!adminIsAllowedImageExt($ext, false)) {
            $valid = 0;
            $error_message .= "Featured photo must be jpg, jpeg, png, gif or webp<br>";
        }
    } else {
        $valid = 0;
        $error_message .= "Featured photo is required<br>";
    }

    if ($valid == 1) {

        $ecatId = resolveServiceEndCategory($pdo, (int)$_POST['mcat_id']);
        if ($ecatId <= 0) {
            $valid = 0;
            $error_message .= "Could not resolve category for this service<br>";
        }
    }

    if ($valid == 1) {

        // 1️⃣ INSERT PRODUCT FIRST (NO IMAGE)
        $staffCommissionType = $_POST['staff_commission_type'] ?? 'inherit';
        $staffCommissionValue = (float)($_POST['staff_commission_value'] ?? 0);
        if ($staffCommissionType === 'inherit') {
            $staffCommissionValue = 0;
        }

        $statement = $pdo->prepare("
            INSERT INTO tbl_product (
                p_name,
                p_old_price,
                p_current_price,
                p_qty,
                p_featured_photo,
                p_description,
                p_short_description,
                p_feature,
                p_condition,
                p_return_policy,
                p_total_view,
                p_is_featured,
                p_is_active,
                ecat_id
            ) VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?)
        ");

        if (!function_exists('adminCleanEditorHtml')) {
            function adminCleanEditorHtml($html) {
                $html = (string)$html;
                if ($html === '') return '';
                if (strpos($html, '&lt;') !== false && preg_match('/<(p|ul|ol|li|div|br)\b/i', $html) !== 1) {
                    $html = html_entity_decode($html, ENT_QUOTES | ENT_HTML5, 'UTF-8');
                }
                $html = preg_replace('/<span[^>]*class="[^"]*PDq2pG_selectionAnchor[^"]*"[^>]*>.*?<\/span>/is', '', $html);
                $html = preg_replace('/<span[^>]*aria-hidden="true"[^>]*>\s*<\/span>/is', '', $html);
                $html = preg_replace('/\s+data-(?:section-id|start|end|is-last-node|testid)="[^"]*"/i', '', $html);
                $html = preg_replace('/<(script|style|iframe)[^>]*>.*?<\/\1>/is', '', $html);
                return trim(strip_tags($html, '<p><br><strong><b><em><i><u><ul><ol><li><h1><h2><h3><h4><h5><h6><a><img><span><div><blockquote><table><thead><tbody><tr><th><td><hr>'));
            }
        }

        $statement->execute([
            $_POST['p_name'],
            $_POST['p_old_price'],
            $_POST['p_current_price'],
            1, // services don't use stock quantity
            '',
            adminCleanEditorHtml($_POST['p_description'] ?? ''),
            adminCleanEditorHtml($_POST['p_short_description'] ?? ''),
            adminCleanEditorHtml($_POST['p_feature'] ?? ''),
            $_POST['p_condition'],
            $_POST['p_return_policy'],
            0,
            $_POST['p_is_featured'],
            $_POST['p_is_active'],
            $ecatId
        ]);

        // 2️⃣ REAL PRODUCT ID
        $p_id = $pdo->lastInsertId();

        try {
            $commissionStmt = $pdo->prepare("UPDATE tbl_product SET staff_commission_type = ?, staff_commission_value = ? WHERE p_id = ?");
            $commissionStmt->execute(array($staffCommissionType, $staffCommissionValue, $p_id));
        } catch (PDOException $e) {
            // Phase 2 columns may not exist until migration is run.
        }

        // 3️⃣ FEATURED IMAGE UPLOAD
        $featured_name = 'product-featured-' . $p_id . '.' . $ext;
        move_uploaded_file(
            $_FILES['p_featured_photo']['tmp_name'],
            "../assets/uploads/$featured_name"
        );

        $statement = $pdo->prepare(
            "UPDATE tbl_product SET p_featured_photo=? WHERE p_id=?"
        );
        $statement->execute([$featured_name, $p_id]);

        // 4️⃣ OTHER PHOTOS
        if (!empty($_FILES['photo']['name'][0])) {

            for ($i = 0; $i < count($_FILES['photo']['name']); $i++) {

                $ext1 = strtolower(pathinfo($_FILES['photo']['name'][$i], PATHINFO_EXTENSION));

                if (adminIsAllowedImageExt($ext1, false)) {

                    $unique = time() . '_' . bin2hex(random_bytes(4));
                    $photo_name = $unique . '.' . $ext1;

                    move_uploaded_file(
                        $_FILES['photo']['tmp_name'][$i],
                        "../assets/uploads/product_photos/$photo_name"
                    );

                    $statement = $pdo->prepare(
                        "INSERT INTO tbl_product_photo (photo, p_id) VALUES (?,?)"
                    );
                    $statement->execute([$photo_name, $p_id]);
                }
            }
        }

        // 5️⃣ SIZE
        if (isset($_POST['size'])) {
            foreach ($_POST['size'] as $size_id) {
                $statement = $pdo->prepare(
                    "INSERT INTO tbl_product_size (size_id, p_id) VALUES (?,?)"
                );
                $statement->execute([$size_id, $p_id]);
            }
        }

        // 6️⃣ COLOR
        if (isset($_POST['color'])) {
            foreach ($_POST['color'] as $color_id) {
                $statement = $pdo->prepare(
                    "INSERT INTO tbl_product_color (color_id, p_id) VALUES (?,?)"
                );
                $statement->execute([$color_id, $p_id]);
            }
        }

        $success_message = "Service added successfully.";
    }
}
?>

<section class="content-header">
	<div class="content-header-left">
		<h1>Add Service</h1>
	</div>
	<div class="content-header-right">
		<a href="product.php" class="btn btn-primary btn-sm">View All</a>
	</div>
</section>


<section class="content">

	<div class="row">
		<div class="col-md-12">

			<?php if($error_message): ?>
			<div class="callout callout-danger">
			
			<p>
			<?php echo $error_message; ?>
			</p>
			</div>
			<?php endif; ?>

			<?php if($success_message): ?>
			<div class="callout callout-success">
			
			<p><?php echo $success_message; ?></p>
			</div>
			<?php endif; ?>

			<form class="form-horizontal" action="" method="post" enctype="multipart/form-data">

				<div class="box box-info">
					<div class="box-body">
						<div class="form-group">
							<label for="" class="col-sm-3 control-label">Top Category <span>*</span></label>
							<div class="col-sm-4">
								<select name="tcat_id" class="form-control select2 top-cat">
									<option value="">Select Top Category</option>
									<?php
									$statement = $pdo->prepare("SELECT * FROM tbl_top_category ORDER BY tcat_name ASC");
									$statement->execute();
									$result = $statement->fetchAll(PDO::FETCH_ASSOC);	
									foreach ($result as $row) {
										?>
										<option value="<?php echo $row['tcat_id']; ?>"><?php echo $row['tcat_name']; ?></option>
										<?php
									}
									?>
								</select>
							</div>
						</div>
						<div class="form-group">
							<label for="" class="col-sm-3 control-label">Category <span>*</span></label>
							<div class="col-sm-4">
								<select name="mcat_id" class="form-control select2 mid-cat">
									<option value="">Select Category</option>
								</select>
							</div>
						</div>
						<div class="form-group">
							<label for="" class="col-sm-3 control-label">Service Name <span>*</span></label>
							<div class="col-sm-4">
								<input type="text" name="p_name" class="form-control">
							</div>
						</div>	
						<div class="form-group">
							<label for="" class="col-sm-3 control-label">Old Price <br><span style="font-size:10px;font-weight:normal;">(In Rs.)</span></label>
							<div class="col-sm-4">
								<input type="text" name="p_old_price" class="form-control">
							</div>
						</div>
						<div class="form-group">
							<label for="" class="col-sm-3 control-label">Current Price <span>*</span><br><span style="font-size:10px;font-weight:normal;">(In Rs.)</span></label>
							<div class="col-sm-4">
								<input type="text" name="p_current_price" class="form-control">
							</div>
						</div>	
						<div class="form-group">
							<label for="" class="col-sm-3 control-label">Featured Photo <span>*</span></label>
							<div class="col-sm-4" style="padding-top:4px;">
								<input type="file" name="p_featured_photo">
							</div>
						</div>
						<div class="form-group">
							<label for="" class="col-sm-3 control-label">Other Photos</label>
							<div class="col-sm-4" style="padding-top:4px;">
								<table id="ProductTable" style="width:100%;">
			                        <tbody>
			                            <tr>
			                                <td>
			                                    <div class="upload-btn">
			                                        <input type="file" name="photo[]" style="margin-bottom:5px;">
			                                    </div>
			                                </td>
			                                <td style="width:28px;"><a href="javascript:void()" class="Delete btn btn-danger btn-xs">X</a></td>
			                            </tr>
			                        </tbody>
			                    </table>
							</div>
							<div class="col-sm-2">
			                    <input type="button" id="btnAddNew" value="Add Item" style="margin-top: 5px;margin-bottom:10px;border:0;color: #fff;font-size: 14px;border-radius:3px;" class="btn btn-warning btn-xs">
			                </div>
						</div>
						<div class="form-group">
							<label for="" class="col-sm-3 control-label">Description</label>
							<div class="col-sm-8">
								<textarea name="p_description" class="form-control" cols="30" rows="10" id="editor1"></textarea>
							</div>
						</div>
						<div class="form-group">
							<label for="" class="col-sm-3 control-label">Short Description</label>
							<div class="col-sm-8">
								<textarea name="p_short_description" class="form-control" cols="30" rows="10" id="editor2"></textarea>
							</div>
						</div>
						<div class="form-group">
							<label for="" class="col-sm-3 control-label">Features</label>
							<div class="col-sm-8">
								<textarea name="p_feature" class="form-control" cols="30" rows="10" id="editor3"></textarea>
							</div>
						</div>
						<div class="form-group">
							<label for="" class="col-sm-3 control-label">Conditions</label>
							<div class="col-sm-8">
								<textarea name="p_condition" class="form-control" cols="30" rows="10" id="editor4"></textarea>
							</div>
						</div>
						<div class="form-group">
							<label for="" class="col-sm-3 control-label">Return Policy</label>
							<div class="col-sm-8">
								<textarea name="p_return_policy" class="form-control" cols="30" rows="10" id="editor5"></textarea>
							</div>
						</div>
						<div class="form-group">
							<label for="" class="col-sm-3 control-label">Is Featured?</label>
							<div class="col-sm-8">
								<select name="p_is_featured" class="form-control" style="width:auto;">
									<option value="0">No</option>
									<option value="1">Yes</option>
								</select> 
							</div>
						</div>
						<div class="form-group">
							<label for="" class="col-sm-3 control-label">Is Active?</label>
							<div class="col-sm-8">
								<select name="p_is_active" class="form-control" style="width:auto;">
									<option value="0">No</option>
									<option value="1">Yes</option>
								</select> 
							</div>
						</div>
						<div class="form-group">
							<label for="" class="col-sm-3 control-label">Staff Commission</label>
							<div class="col-sm-3">
								<select name="staff_commission_type" class="form-control">
									<option value="inherit" <?php echo (($_POST['staff_commission_type'] ?? 'inherit') === 'inherit') ? 'selected' : ''; ?>>Inherit (staff/global)</option>
									<option value="percent" <?php echo (($_POST['staff_commission_type'] ?? '') === 'percent') ? 'selected' : ''; ?>>Percentage</option>
									<option value="fixed" <?php echo (($_POST['staff_commission_type'] ?? '') === 'fixed') ? 'selected' : ''; ?>>Fixed Amount</option>
								</select>
							</div>
							<div class="col-sm-2">
								<input type="number" step="0.01" min="0" class="form-control" name="staff_commission_value" placeholder="Value" value="<?php echo htmlspecialchars($_POST['staff_commission_value'] ?? '0'); ?>">
							</div>
							<div class="col-sm-3">
								<p class="help-block">Optional per-service commission rule for staff.</p>
							</div>
						</div>
						<div class="form-group">
							<label for="" class="col-sm-3 control-label"></label>
							<div class="col-sm-6">
								<button type="submit" class="btn btn-success pull-left" name="form1">Add Service</button>
							</div>
						</div>
					</div>
				</div>

			</form>


		</div>
	</div>

</section>

<?php require_once('footer.php'); ?>