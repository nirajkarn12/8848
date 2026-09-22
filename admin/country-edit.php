<?php require_once('header.php'); ?>

<?php
if (!function_exists('columnExists')) {
    function columnExists($pdo, $table, $column) {
        $statement = $pdo->prepare("SHOW COLUMNS FROM `" . $table . "` LIKE ?");
        $statement->execute(array($column));
        return $statement->rowCount() > 0;
    }
}

ensureServiceLocationColumns($pdo);

if(isset($_POST['form1'])) {
	$valid = 1;
    $countryName = trim($_POST['country_name'] ?? '');
    $postalCode = trim($_POST['postal_code'] ?? '');

    if($countryName === '') {
        $valid = 0;
        $error_message .= "location Name can not be empty<br>";
    } else {
		$statement = $pdo->prepare("SELECT * FROM tbl_country WHERE country_id=?");
		$statement->execute(array($_REQUEST['id']));
		$result = $statement->fetchAll(PDO::FETCH_ASSOC);
		foreach($result as $row) {
			$current_country_name = $row['country_name'];
		}

		$statement = $pdo->prepare("SELECT * FROM tbl_country WHERE country_name=? and country_name!=?");
    	$statement->execute(array($countryName,$current_country_name));
    	$total = $statement->rowCount();									
    	if($total) {
    		$valid = 0;
        	$error_message .= 'location name already exists<br>';
    	}
    }

    if($valid == 1) {
		if (columnExists($pdo, 'tbl_country', 'postal_code')) {
			$statement = $pdo->prepare("UPDATE tbl_country SET country_name=?, postal_code=? WHERE country_id=?");
			$statement->execute(array($countryName, $postalCode, $_REQUEST['id']));
		} else {
			$statement = $pdo->prepare("UPDATE tbl_country SET country_name=? WHERE country_id=?");
			$statement->execute(array($countryName, $_REQUEST['id']));
		}

    	$success_message = 'location is updated successfully.';
    }
}
?>

<?php
if(!isset($_REQUEST['id'])) {
	header('location: logout.php');
	exit;
} else {
	// Check the id is valid or not
	$statement = $pdo->prepare("SELECT * FROM tbl_country WHERE country_id=?");
	$statement->execute(array($_REQUEST['id']));
	$total = $statement->rowCount();
	$result = $statement->fetchAll(PDO::FETCH_ASSOC);
	if( $total == 0 ) {
		header('location: logout.php');
		exit;
	}
}
?>

<section class="content-header">
	<div class="content-header-left">
		<h1>Edit Location</h1>
	</div>
	<div class="content-header-right">
		<a href="country.php" class="btn btn-primary btn-sm">View All</a>
	</div>
</section>


<?php							
foreach ($result as $row) {
	$country_name = $row['country_name'];
	$postal_code = $row['postal_code'] ?? '';
}
?>

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

        <form class="form-horizontal" action="" method="post">

        <div class="box box-info">

            <div class="box-body">
                <div class="form-group">
                    <label for="" class="col-sm-2 control-label">Location Name <span>*</span></label>
                    <div class="col-sm-4">
                        <input type="text" class="form-control" name="country_name" value="<?php echo $country_name; ?>">
                    </div>
                </div>
                <div class="form-group">
                    <label for="" class="col-sm-2 control-label">Postal Code</label>
                    <div class="col-sm-4">
                        <input type="text" class="form-control" name="postal_code" value="<?php echo htmlspecialchars($postal_code); ?>" placeholder="e.g. 1010">
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

<div class="modal fade" id="confirm-delete" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
                <h4 class="modal-title" id="myModalLabel">Delete Confirmation</h4>
            </div>
            <div class="modal-body">
                Are you sure want to delete this item?
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal">Cancel</button>
                <a class="btn btn-danger btn-ok">Delete</a>
            </div>
        </div>
    </div>
</div>

<?php require_once('footer.php'); ?>