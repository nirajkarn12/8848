<?php require_once('header.php'); ?>

<?php
if (isset($_GET['success'])) {
    $success_message = 'Blog is added successfully!';
} elseif (isset($_GET['updated'])) {
    $success_message = 'Blog is updated successfully!';
} elseif (isset($_GET['deleted'])) {
    $success_message = 'Blog is deleted successfully!';
}
?>

<section class="content-header">
    <div class="content-header-left">
        <h1>View Blog Posts</h1>
    </div>
    <div class="content-header-right">
        <a href="blog-add.php" class="btn btn-primary btn-sm">Add Blog Post</a>
    </div>
</section>

<section class="content">
    <div class="row">
        <div class="col-md-12">
            <?php if($error_message): ?>
            <div class="callout callout-danger">
                <p><?php echo $error_message; ?></p>
            </div>
            <?php endif; ?>

            <?php if($success_message): ?>
            <div class="callout callout-success">
                <p><?php echo $success_message; ?></p>
            </div>
            <?php endif; ?>

            <div class="box box-info">
                <div class="box-body table-responsive">
                    <table id="example1" class="table table-bordered table-hover table-striped">
                        <thead>
                            <tr>
                                <th width="30">#</th>
                                <th>Image</th>
                                <th>Title</th>
                                <th>Category</th>
                                <th>Date</th>
                                <th>Views</th>
                                <th>Slug</th>
                                <th width="120">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            $i = 0;
                            $statement = $pdo->prepare("SELECT p.*, c.tcat_name FROM tbl_post p LEFT JOIN tbl_top_category c ON c.tcat_id = p.category_id ORDER BY p.post_id DESC");
                            $statement->execute();
                            $result = $statement->fetchAll(PDO::FETCH_ASSOC);
                            foreach ($result as $row) {
                                $i++;
                                ?>
                                <tr>
                                    <td><?php echo $i; ?></td>
                                    <td style="width:110px;"><img src="../assets/uploads/<?php echo $row['photo']; ?>" alt="<?php echo $row['post_title']; ?>" style="width:100px;height:70px;object-fit:cover;"></td>
                                    <td><?php echo htmlspecialchars($row['post_title']); ?></td>
                                    <td><?php echo htmlspecialchars($row['tcat_name'] ?? 'Uncategorized'); ?></td>
                                    <td><?php echo htmlspecialchars($row['post_date']); ?></td>
                                    <td><?php echo (int)$row['total_view']; ?></td>
                                    <td><?php echo htmlspecialchars($row['post_slug']); ?></td>
                                    <td>
                                        <a href="blog-edit.php?id=<?php echo (int)$row['post_id']; ?>" class="btn btn-primary btn-xs">Edit</a>
                                        <a href="#" class="btn btn-danger btn-xs" data-href="blog-delete.php?id=<?php echo (int)$row['post_id']; ?>" data-toggle="modal" data-target="#confirm-delete">Delete</a>
                                    </td>
                                </tr>
                                <?php
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
                <p>Are you sure want to delete this blog post?</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal">Cancel</button>
                <a class="btn btn-danger btn-ok">Delete</a>
            </div>
        </div>
    </div>
</div>

<?php require_once('footer.php'); ?>
