<?php require_once('header.php'); ?>

<?php
if (isset($_GET['success'])) {
    $success_message = 'Client is added successfully!';
} elseif (isset($_GET['updated'])) {
    $success_message = 'Client is updated successfully!';
} elseif (isset($_GET['deleted'])) {
    $success_message = 'Client is deleted successfully!';
}

$tableReady = true;
try {
    $pdo->query("SELECT 1 FROM tbl_client LIMIT 1");
} catch (Throwable $e) {
    $tableReady = false;
    $error_message = 'Clients table is missing. Run <a href="run-client-migration.php">migration</a> first.';
}
?>

<section class="content-header">
    <div class="content-header-left">
        <h1>View Clients (Logos)</h1>
    </div>
    <div class="content-header-right">
        <a href="client-add.php" class="btn btn-primary btn-sm">Add Client</a>
    </div>
</section>

<section class="content">
    <div class="row">
        <div class="col-md-12">
            <?php if ($error_message): ?>
            <div class="callout callout-danger"><p><?php echo $error_message; ?></p></div>
            <?php endif; ?>
            <?php if ($success_message): ?>
            <div class="callout callout-success"><p><?php echo $success_message; ?></p></div>
            <?php endif; ?>

            <?php if ($tableReady): ?>
            <div class="box box-info">
                <div class="box-body table-responsive">
                    <table id="example1" class="table table-bordered table-hover table-striped">
                        <thead>
                            <tr>
                                <th width="30">#</th>
                                <th width="120">Logo</th>
                                <th>Name</th>
                                <th>Status</th>
                                <th>Order</th>
                                <th width="140">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            $i = 0;
                            $statement = $pdo->prepare("SELECT * FROM tbl_client ORDER BY sort_order ASC, id DESC");
                            $statement->execute();
                            $result = $statement->fetchAll(PDO::FETCH_ASSOC);
                            foreach ($result as $row) {
                                $i++;
                                ?>
                                <tr>
                                    <td><?php echo $i; ?></td>
                                    <td>
                                        <img src="../assets/uploads/<?php echo htmlspecialchars($row['logo']); ?>" alt="" style="max-width:110px;max-height:50px;object-fit:contain;background:#f5f5f5;padding:6px;">
                                    </td>
                                    <td><?php echo htmlspecialchars($row['name'] !== '' ? $row['name'] : '—'); ?></td>
                                    <td><?php echo htmlspecialchars($row['status']); ?></td>
                                    <td><?php echo (int)$row['sort_order']; ?></td>
                                    <td>
                                        <a href="client-edit.php?id=<?php echo (int)$row['id']; ?>" class="btn btn-primary btn-xs">Edit</a>
                                        <a href="#" class="btn btn-danger btn-xs" data-href="client-delete.php?id=<?php echo (int)$row['id']; ?>" data-toggle="modal" data-target="#confirm-delete">Delete</a>
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

<div class="modal fade" id="confirm-delete" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
                <h4 class="modal-title">Delete Confirmation</h4>
            </div>
            <div class="modal-body"><p>Are you sure want to delete this client logo?</p></div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal">Cancel</button>
                <a class="btn btn-danger btn-ok">Delete</a>
            </div>
        </div>
    </div>
</div>

<?php require_once('footer.php'); ?>
