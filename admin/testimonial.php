<?php require_once('header.php'); ?>

<?php
if (isset($_GET['success'])) {
    $success_message = 'Testimonial is added successfully!';
} elseif (isset($_GET['updated'])) {
    $success_message = 'Testimonial is updated successfully!';
} elseif (isset($_GET['deleted'])) {
    $success_message = 'Testimonial is deleted successfully!';
}

$tableReady = true;
try {
    $pdo->query("SELECT 1 FROM tbl_testimonial LIMIT 1");
} catch (Throwable $e) {
    $tableReady = false;
    $error_message = 'Testimonials table is missing. Run <a href="run-testimonial-migration.php">migration</a> first.';
}
?>

<section class="content-header">
    <div class="content-header-left">
        <h1>View Testimonials / Reviews</h1>
    </div>
    <div class="content-header-right">
        <a href="testimonial-add.php" class="btn btn-primary btn-sm">Add Review</a>
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

            <?php if ($tableReady): ?>
            <div class="box box-info">
                <div class="box-body table-responsive">
                    <table id="example1" class="table table-bordered table-hover table-striped">
                        <thead>
                            <tr>
                                <th width="30">#</th>
                                <th width="80">Photo</th>
                                <th>Name</th>
                                <th>Role</th>
                                <th>Rating</th>
                                <th>Status</th>
                                <th>Order</th>
                                <th width="140">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            $i = 0;
                            $statement = $pdo->prepare("SELECT * FROM tbl_testimonial ORDER BY sort_order ASC, id DESC");
                            $statement->execute();
                            $result = $statement->fetchAll(PDO::FETCH_ASSOC);
                            foreach ($result as $row) {
                                $i++;
                                $photoHtml = $row['photo'] !== ''
                                    ? '<img src="../assets/uploads/' . htmlspecialchars($row['photo']) . '" style="width:60px;height:60px;object-fit:cover;border-radius:50%;">'
                                    : '<span class="label label-default">No photo</span>';
                                ?>
                                <tr>
                                    <td><?php echo $i; ?></td>
                                    <td><?php echo $photoHtml; ?></td>
                                    <td><?php echo htmlspecialchars($row['name']); ?></td>
                                    <td><?php echo htmlspecialchars(trim($row['designation'] . ($row['company'] !== '' ? ' · ' . $row['company'] : ''))); ?></td>
                                    <td><?php echo (int)$row['rating']; ?> / 5</td>
                                    <td><?php echo htmlspecialchars($row['status']); ?></td>
                                    <td><?php echo (int)$row['sort_order']; ?></td>
                                    <td>
                                        <a href="testimonial-edit.php?id=<?php echo (int)$row['id']; ?>" class="btn btn-primary btn-xs">Edit</a>
                                        <a href="#" class="btn btn-danger btn-xs" data-href="testimonial-delete.php?id=<?php echo (int)$row['id']; ?>" data-toggle="modal" data-target="#confirm-delete">Delete</a>
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
                <p>Are you sure want to delete this testimonial?</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal">Cancel</button>
                <a class="btn btn-danger btn-ok">Delete</a>
            </div>
        </div>
    </div>
</div>

<?php require_once('footer.php'); ?>
