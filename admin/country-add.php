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
        $error_message .= "Country Name can not be empty<br>";
    } else {
    	$statement = $pdo->prepare("SELECT * FROM tbl_country WHERE country_name=?");
    	$statement->execute(array($countryName));
    	$total = $statement->rowCount();
    	if($total)
    	{
    		$valid = 0;
        	$error_message .= "location Name already exists<br>";
    	}
    }

    if($valid == 1) {
		if (columnExists($pdo, 'tbl_country', 'postal_code')) {
			$statement = $pdo->prepare("INSERT INTO tbl_country (country_name, postal_code) VALUES (?, ?)");
			$statement->execute(array($countryName, $postalCode));
		} else {
			$statement = $pdo->prepare("INSERT INTO tbl_country (country_name) VALUES (?)");
			$statement->execute(array($countryName));
		}
	
    	$success_message = 'location is added successfully.';
    }
}
?>

<section class="content-header">
	<div class="content-header-left">
		<h1>Add Area/Region</h1>
	</div>
	<div class="content-header-right">
		<a href="country.php" class="btn btn-primary btn-sm">View All</a>
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

			<form class="form-horizontal" action="" method="post">

				<div class="box box-info">
					<div class="box-body">
						<div class="form-group">
							<label for="" class="col-sm-2 control-label">Location Name <span>*</span></label>
							<div class="col-sm-4">
								<input type="text" class="form-control" name="country_name">
							</div>
						</div>
						<div class="form-group">
							<label for="" class="col-sm-2 control-label">Postal Code</label>
							<div class="col-sm-4">
								<input type="text" class="form-control" name="postal_code" placeholder="e.g. 1010">
							</div>
						</div>
						<div class="form-group">
							<label for="" class="col-sm-2 control-label"></label>
							<div class="col-sm-6">
								<button type="submit" class="btn btn-success pull-left" name="form1">Submit</button>
							</div>
						</div>
					</div>
				</div>

			</form>


		</div>
	</div>

</section>

<?php require_once('footer.php'); ?>