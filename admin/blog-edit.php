<?php require_once('header.php'); ?>

<?php
function slugify_blog_edit($text) {
    $text = preg_replace('/[^\p{L}\p{N}]+/u', '-', strtolower(trim($text)));
    $text = preg_replace('/-+/', '-', trim($text, '-'));
    return $text;
}

if (!isset($_REQUEST['id'])) {
    header('location: blog.php');
    exit;
} else {
    $statement = $pdo->prepare("SELECT * FROM tbl_post WHERE post_id=?");
    $statement->execute(array($_REQUEST['id']));
    $total = $statement->rowCount();
    if ($total == 0) {
        header('location: blog.php');
        exit;
    }
}

if (isset($_POST['form1'])) {
    $valid = 1;

    $post_title = trim($_POST['post_title'] ?? '');
    $post_slug = trim($_POST['post_slug'] ?? '');
    $category_id = (int)($_POST['category_id'] ?? 0);
    $post_content = trim($_POST['post_content'] ?? '');
    $post_date = trim($_POST['post_date'] ?? '');
    $meta_title = trim($_POST['meta_title'] ?? '');
    $meta_keyword = trim($_POST['meta_keyword'] ?? '');
    $meta_description = trim($_POST['meta_description'] ?? '');

    if ($post_title == '') {
        $valid = 0;
        $error_message .= 'Blog title can not be empty<br>';
    }

    if ($post_slug == '') {
        $post_slug = slugify_blog_edit($post_title);
    }

    if ($category_id <= 0) {
        $valid = 0;
        $error_message .= 'Please select a category<br>';
    }

    if ($post_content == '') {
        $valid = 0;
        $error_message .= 'Blog content can not be empty<br>';
    }

    if ($post_date == '') {
        $post_date = date('d-m-Y');
    }

    $path = $_FILES['photo']['name'];
    $path_tmp = $_FILES['photo']['tmp_name'];

    if ($path != '') {
        $ext = strtolower(pathinfo($path, PATHINFO_EXTENSION));
        if (!in_array($ext, ['jpg', 'jpeg', 'png', 'webp'], true)) {
            $valid = 0;
            $error_message .= 'You must have to upload jpg, jpeg, png or webp file<br>';
        } else {
            $image_info = getimagesize($path_tmp);
            if ($image_info === false) {
                $valid = 0;
                $error_message .= 'The uploaded file is not a valid image<br>';
            }
        }
    }

    if ($valid == 1) {
        $current_photo = trim($_POST['current_photo'] ?? '');
        $photo_name = $current_photo;

        if ($path != '') {
            if ($current_photo != '' && file_exists('../assets/uploads/' . $current_photo)) {
                unlink('../assets/uploads/' . $current_photo);
            }
            $photo_name = 'blog-' . $_REQUEST['id'] . '-' . time() . rand(1000, 9999) . '.' . $ext;
            move_uploaded_file($path_tmp, '../assets/uploads/' . $photo_name);
        }

        $statement = $pdo->prepare("UPDATE tbl_post SET post_title=?, post_slug=?, post_content=?, post_date=?, photo=?, category_id=?, meta_title=?, meta_keyword=?, meta_description=? WHERE post_id=?");
        $statement->execute(array($post_title, $post_slug, $post_content, $post_date, $photo_name, $category_id, $meta_title, $meta_keyword, $meta_description, $_REQUEST['id']));

        header('location: blog.php?updated=1');
        exit;
    }
}

$statement = $pdo->prepare("SELECT * FROM tbl_post WHERE post_id=?");
$statement->execute(array($_REQUEST['id']));
$result = $statement->fetchAll(PDO::FETCH_ASSOC);
foreach ($result as $row) {
    $post_title = $row['post_title'];
    $post_slug = $row['post_slug'];
    $post_content = $row['post_content'];
    $post_date = $row['post_date'];
    $photo = $row['photo'];
    $category_id = $row['category_id'];
    $meta_title = $row['meta_title'];
    $meta_keyword = $row['meta_keyword'];
    $meta_description = $row['meta_description'];
}

$categories = $pdo->prepare("SELECT tcat_id AS id, tcat_name AS name FROM tbl_top_category ORDER BY tcat_name ASC");
$categories->execute();
$categories = $categories->fetchAll(PDO::FETCH_ASSOC);
?>

<section class="content-header">
    <div class="content-header-left">
        <h1>Edit Blog Post</h1>
    </div>
    <div class="content-header-right">
        <a href="blog.php" class="btn btn-primary btn-sm">View All</a>
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

            <form class="form-horizontal" action="" method="post" enctype="multipart/form-data">
                <input type="hidden" name="current_photo" value="<?php echo htmlspecialchars($photo); ?>">
                <div class="box box-info">
                    <div class="box-body">
                        <div class="form-group">
                            <label for="" class="col-sm-2 control-label">Blog Title <span>*</span></label>
                            <div class="col-sm-6">
                                <input type="text" autocomplete="off" class="form-control" name="post_title" value="<?php echo htmlspecialchars($post_title); ?>">
                            </div>
                        </div>
                        <div class="form-group">
                            <label for="" class="col-sm-2 control-label">Slug</label>
                            <div class="col-sm-6">
                                <input type="text" autocomplete="off" class="form-control" name="post_slug" value="<?php echo htmlspecialchars($post_slug); ?>">
                            </div>
                        </div>
                        <div class="form-group">
                            <label for="" class="col-sm-2 control-label">Category <span>*</span></label>
                            <div class="col-sm-6">
                                <select class="form-control select2" name="category_id">
                                    <option value="">Select Category</option>
                                    <?php foreach ($categories as $category) { ?>
                                        <option value="<?php echo (int)$category['id']; ?>" <?php if((int)$category_id == (int)$category['id']) { echo 'selected'; } ?>><?php echo htmlspecialchars($category['name']); ?></option>
                                    <?php } ?>
                                </select>
                            </div>
                        </div>
                        <div class="form-group">
                            <label for="" class="col-sm-2 control-label">Existing Image</label>
                            <div class="col-sm-9" style="padding-top:5px">
                                <img src="../assets/uploads/<?php echo htmlspecialchars($photo); ?>" alt="Blog Image" style="width:180px;border:1px solid #ddd;padding:5px;">
                            </div>
                        </div>
                        <div class="form-group">
                            <label for="" class="col-sm-2 control-label">Featured Image</label>
                            <div class="col-sm-6" style="padding-top:5px">
                                <input type="file" name="photo" id="blogPhotoInput" accept="image/jpg,image/jpeg,image/png,image/webp">
                                <img id="blogPhotoPreview" src="#" alt="Preview" style="display:none; width:180px; margin-top:10px; border:1px solid #ddd; padding:5px;">
                            </div>
                        </div>
                        <div class="form-group">
                            <label for="" class="col-sm-2 control-label">Blog Content <span>*</span></label>
                            <div class="col-sm-9">
                                <textarea class="form-control" name="post_content" id="editor1"><?php echo htmlspecialchars($post_content); ?></textarea>
                            </div>
                        </div>
                        <div class="form-group">
                            <label for="" class="col-sm-2 control-label">Publish Date</label>
                            <div class="col-sm-3">
                                <input type="text" autocomplete="off" class="form-control" name="post_date" value="<?php echo htmlspecialchars($post_date); ?>">
                            </div>
                        </div>
                        <div class="form-group">
                            <label for="" class="col-sm-2 control-label">Meta Title</label>
                            <div class="col-sm-6">
                                <input type="text" autocomplete="off" class="form-control" name="meta_title" value="<?php echo htmlspecialchars($meta_title); ?>">
                            </div>
                        </div>
                        <div class="form-group">
                            <label for="" class="col-sm-2 control-label">Meta Keywords</label>
                            <div class="col-sm-6">
                                <textarea class="form-control" name="meta_keyword" style="height:100px;"><?php echo htmlspecialchars($meta_keyword); ?></textarea>
                            </div>
                        </div>
                        <div class="form-group">
                            <label for="" class="col-sm-2 control-label">Meta Description</label>
                            <div class="col-sm-6">
                                <textarea class="form-control" name="meta_description" style="height:100px;"><?php echo htmlspecialchars($meta_description); ?></textarea>
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

<script>
    document.addEventListener('DOMContentLoaded', function () {
        var input = document.getElementById('blogPhotoInput');
        var preview = document.getElementById('blogPhotoPreview');
        if (input && preview) {
            input.addEventListener('change', function () {
                var file = this.files && this.files[0];
                if (!file) {
                    preview.style.display = 'none';
                    preview.src = '#';
                    return;
                }
                var reader = new FileReader();
                reader.onload = function (e) {
                    preview.src = e.target.result;
                    preview.style.display = 'block';
                };
                reader.readAsDataURL(file);
            });
        }
    });
</script>

<?php require_once('footer.php'); ?>
