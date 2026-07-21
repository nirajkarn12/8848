<?php
include 'inc/config.php';
if (!empty($_POST['id'])) {
	$id = $_POST['id'];

	$statement = $pdo->prepare("SELECT * FROM tbl_mid_category WHERE tcat_id=? ORDER BY mcat_name ASC");
	$statement->execute(array($id));
	$result = $statement->fetchAll(PDO::FETCH_ASSOC);
	?><option value="">Select Category</option><?php
	foreach ($result as $row) {
		?>
        <option value="<?php echo $row['mcat_id']; ?>"><?php echo $row['mcat_name']; ?></option>
        <?php
	}
}
