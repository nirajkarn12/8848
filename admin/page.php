<?php require_once('header.php'); ?>

<?php
function adminPageBannerPreview($filename) {
    $filename = trim((string) $filename);
    $path = '../assets/uploads/' . $filename;
    if ($filename === '' || !is_file($path)) {
        return '<span class="label label-warning">No image uploaded yet</span>';
    }
    $v = (int) @filemtime($path);
    return '<img src="../assets/uploads/' . htmlspecialchars($filename, ENT_QUOTES, 'UTF-8') . '?v=' . $v . '" class="existing-photo" alt="Banner" style="height:120px;width:auto;max-width:100%;object-fit:cover;border:1px solid #ddd;border-radius:4px;">';
}

function adminSavePageBanner($filesKey, $oldFilename, $prefix) {
    $path = $filesKey['name'] ?? '';
    $pathTmp = $filesKey['tmp_name'] ?? '';
    if ($path === '' || (int)($filesKey['error'] ?? UPLOAD_ERR_NO_FILE) === UPLOAD_ERR_NO_FILE) {
        return array('ok' => true, 'filename' => '', 'changed' => false, 'error' => '');
    }
    if ((int)($filesKey['error'] ?? 0) !== UPLOAD_ERR_OK) {
        return array('ok' => false, 'filename' => '', 'changed' => false, 'error' => 'Image upload failed<br>');
    }
    $ext = strtolower(pathinfo($path, PATHINFO_EXTENSION));
    if (!in_array($ext, array('jpg', 'jpeg', 'png', 'gif', 'webp'), true)) {
        return array('ok' => false, 'filename' => '', 'changed' => false, 'error' => 'You must upload jpg, jpeg, png, gif or webp file<br>');
    }

    // Convert webp -> jpg for better admin/browser compatibility
    if ($ext === 'webp' && function_exists('imagecreatefromwebp')) {
        $img = @imagecreatefromwebp($pathTmp);
        if ($img) {
            $finalName = $prefix . '-' . time() . '.jpg';
            $ok = imagejpeg($img, '../assets/uploads/' . $finalName, 90);
            imagedestroy($img);
            if ($ok) {
                $oldFilename = trim((string) $oldFilename);
                if ($oldFilename !== '' && is_file('../assets/uploads/' . $oldFilename)) {
                    @unlink('../assets/uploads/' . $oldFilename);
                }
                return array('ok' => true, 'filename' => $finalName, 'changed' => true, 'error' => '');
            }
        }
    }

    $finalName = $prefix . '-' . time() . '.' . $ext;
    if (!move_uploaded_file($pathTmp, '../assets/uploads/' . $finalName)) {
        return array('ok' => false, 'filename' => '', 'changed' => false, 'error' => 'Could not save uploaded image<br>');
    }
    $oldFilename = trim((string) $oldFilename);
    if ($oldFilename !== '' && is_file('../assets/uploads/' . $oldFilename)) {
        @unlink('../assets/uploads/' . $oldFilename);
    }
    return array('ok' => true, 'filename' => $finalName, 'changed' => true, 'error' => '');
}

if(isset($_POST['form_about'])) {
    
    $valid = 1;

    if(empty($_POST['about_title'])) {
        $valid = 0;
        $error_message .= 'Title can not be empty<br>';
    }

    if(empty($_POST['about_content'])) {
        $valid = 0;
        $error_message .= 'Content can not be empty<br>';
    }

    $currentAboutBanner = '';
    $statement = $pdo->prepare("SELECT about_banner FROM tbl_page WHERE id=1");
    $statement->execute();
    $currentAboutBanner = (string) $statement->fetchColumn();

    $bannerResult = adminSavePageBanner($_FILES['about_banner'] ?? array(), $currentAboutBanner, 'about-banner');
    if (!$bannerResult['ok']) {
        $valid = 0;
        $error_message .= $bannerResult['error'];
    }

    if($valid == 1) {
        if($bannerResult['changed']) {
            $statement = $pdo->prepare("UPDATE tbl_page SET about_title=?,about_content=?,about_banner=?,about_meta_title=?,about_meta_keyword=?,about_meta_description=? WHERE id=1");
            $statement->execute(array($_POST['about_title'],$_POST['about_content'],$bannerResult['filename'],$_POST['about_meta_title'],$_POST['about_meta_keyword'],$_POST['about_meta_description']));
        } else {
            $statement = $pdo->prepare("UPDATE tbl_page SET about_title=?,about_content=?,about_meta_title=?,about_meta_keyword=?,about_meta_description=? WHERE id=1");
            $statement->execute(array($_POST['about_title'],$_POST['about_content'],$_POST['about_meta_title'],$_POST['about_meta_keyword'],$_POST['about_meta_description']));
        }

        $success_message = 'About Page Information is updated successfully.';
    }
    
}



if(isset($_POST['form_faq'])) {
    
    $valid = 1;

    if(empty($_POST['faq_title'])) {
        $valid = 0;
        $error_message .= 'Title can not be empty<br>';
    }

    $path = $_FILES['faq_banner']['name'];
    $path_tmp = $_FILES['faq_banner']['tmp_name'];

    if($path != '') {
        $ext = pathinfo( $path, PATHINFO_EXTENSION );
        $file_name = basename( $path, '.' . $ext );
        if( $ext!='jpg' && $ext!='png' && $ext!='jpeg' && $ext!='gif' ) {
            $valid = 0;
            $error_message .= 'You must have to upload jpg, jpeg, gif or png file<br>';
        }
    }

    if($valid == 1) {

        if($path != '') {
            // removing the existing photo
            $statement = $pdo->prepare("SELECT * FROM tbl_page WHERE id=1");
            $statement->execute();
            $result = $statement->fetchAll(PDO::FETCH_ASSOC);                           
            foreach ($result as $row) {
                $faq_banner = $row['faq_banner'];
                unlink('../assets/uploads/'.$faq_banner);
            }

            // updating the data
            $final_name = 'faq-banner'.'.'.$ext;
            move_uploaded_file( $path_tmp, '../assets/uploads/'.$final_name );

            // updating the database
            $statement = $pdo->prepare("UPDATE tbl_page SET faq_title=?,faq_banner=?,faq_meta_title=?,faq_meta_keyword=?,faq_meta_description=? WHERE id=1");
            $statement->execute(array($_POST['faq_title'],$final_name,$_POST['faq_meta_title'],$_POST['faq_meta_keyword'],$_POST['faq_meta_description']));
        } else {
            // updating the database
            $statement = $pdo->prepare("UPDATE tbl_page SET faq_title=?,faq_meta_title=?,faq_meta_keyword=?,faq_meta_description=? WHERE id=1");
            $statement->execute(array($_POST['faq_title'],$_POST['faq_meta_title'],$_POST['faq_meta_keyword'],$_POST['faq_meta_description']));
        }

        $success_message = 'FAQ Page Information is updated successfully.';
        
    }
    
}



if(isset($_POST['form_contact'])) {
    
    $valid = 1;

    if(empty($_POST['contact_title'])) {
        $valid = 0;
        $error_message .= 'Title can not be empty<br>';
    }

    $currentContactBanner = '';
    $statement = $pdo->prepare("SELECT contact_banner FROM tbl_page WHERE id=1");
    $statement->execute();
    $currentContactBanner = (string) $statement->fetchColumn();

    $bannerResult = adminSavePageBanner($_FILES['contact_banner'] ?? array(), $currentContactBanner, 'contact-banner');
    if (!$bannerResult['ok']) {
        $valid = 0;
        $error_message .= $bannerResult['error'];
    }

    if($valid == 1) {
        if($bannerResult['changed']) {
            $statement = $pdo->prepare("UPDATE tbl_page SET contact_title=?,contact_banner=?,contact_meta_title=?,contact_meta_keyword=?,contact_meta_description=? WHERE id=1");
            $statement->execute(array($_POST['contact_title'],$bannerResult['filename'],$_POST['contact_meta_title'],$_POST['contact_meta_keyword'],$_POST['contact_meta_description']));
        } else {
            $statement = $pdo->prepare("UPDATE tbl_page SET contact_title=?,contact_meta_title=?,contact_meta_keyword=?,contact_meta_description=? WHERE id=1");
            $statement->execute(array($_POST['contact_title'],$_POST['contact_meta_title'],$_POST['contact_meta_keyword'],$_POST['contact_meta_description']));
        }

        $success_message = 'Contact Page Information is updated successfully.';
    }
    
}


?>

<section class="content-header">
    <div class="content-header-left">
        <h1>Page Settings</h1>
    </div>
</section>

<?php
$statement = $pdo->prepare("SELECT * FROM tbl_page WHERE id=1");
$statement->execute();
$result = $statement->fetchAll(PDO::FETCH_ASSOC);                           
foreach ($result as $row) {
    $about_title = $row['about_title'];
    $about_content = $row['about_content'];
    $about_banner = $row['about_banner'];
    $about_meta_title = $row['about_meta_title'];
    $about_meta_keyword = $row['about_meta_keyword'];
    $about_meta_description = $row['about_meta_description'];
    $faq_title = $row['faq_title'];
    $faq_banner = $row['faq_banner'];
    $faq_meta_title = $row['faq_meta_title'];
    $faq_meta_keyword = $row['faq_meta_keyword'];
    $faq_meta_description = $row['faq_meta_description'];
    $contact_title = $row['contact_title'];
    $contact_banner = $row['contact_banner'];
    $contact_meta_title = $row['contact_meta_title'];
    $contact_meta_keyword = $row['contact_meta_keyword'];
    $contact_meta_description = $row['contact_meta_description'];

}
?>


<section class="content" style="min-height:auto;margin-bottom: -30px;">
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
        </div>
    </div>
</section>

<section class="content">

    <div class="row">
        <div class="col-md-12">
                            
                <div class="nav-tabs-custom">
                    <?php
                    $activeTab = 'tab_1';
                    if (!empty($success_message) && stripos($success_message, 'Contact') !== false) {
                        $activeTab = 'tab_4';
                    } elseif (!empty($success_message) && stripos($success_message, 'FAQ') !== false) {
                        $activeTab = 'tab_2';
                    } elseif (isset($_GET['tab']) && in_array($_GET['tab'], array('tab_1', 'tab_2', 'tab_4'), true)) {
                        $activeTab = $_GET['tab'];
                    }
                    ?>
                    <ul class="nav nav-tabs">
                        <li class="<?php echo $activeTab === 'tab_1' ? 'active' : ''; ?>"><a href="#tab_1" data-toggle="tab">About Us</a></li>
                        <li class="<?php echo $activeTab === 'tab_2' ? 'active' : ''; ?>"><a href="#tab_2" data-toggle="tab">FAQ</a></li>
                        <li class="<?php echo $activeTab === 'tab_4' ? 'active' : ''; ?>"><a href="#tab_4" data-toggle="tab">Contact</a></li>
                    </ul>

                    <!-- About us Page Content -->

                    <div class="tab-content">
                        <div class="tab-pane <?php echo $activeTab === 'tab_1' ? 'active' : ''; ?>" id="tab_1">
                            <form class="form-horizontal" action="" method="post" enctype="multipart/form-data">
                            <div class="box box-info">
                                <div class="box-body">
                                    <div class="form-group">
                                        <label for="" class="col-sm-3 control-label">Page Title * </label>
                                        <div class="col-sm-5">
                                            <input class="form-control" type="text" name="about_title" value="<?php echo $about_title; ?>">
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <label for="" class="col-sm-3 control-label">Page Content * </label>
                                        <div class="col-sm-8">
                                            <textarea class="form-control" name="about_content" id="editor1"><?php echo $about_content; ?></textarea>
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <label for="" class="col-sm-3 control-label">Existing Banner Photo</label>
                                        <div class="col-sm-6" style="padding-top:6px;">
                                            <?php echo adminPageBannerPreview($about_banner); ?>
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <label for="" class="col-sm-3 control-label">New Banner Photo</label>
                                        <div class="col-sm-6" style="padding-top:6px;">
                                            <input type="file" name="about_banner" accept=".jpg,.jpeg,.png,.gif,.webp">
                                            <p class="help-block">This image is used on About section. jpg/png/gif/webp</p>
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <label for="" class="col-sm-3 control-label">Meta Title</label>
                                        <div class="col-sm-8">
                                            <input class="form-control" type="text" name="about_meta_title" value="<?php echo $about_meta_title; ?>">
                                        </div>
                                    </div>             
                                    <div class="form-group">
                                        <label for="" class="col-sm-3 control-label">Meta Keyword </label>
                                        <div class="col-sm-8">
                                            <textarea class="form-control" name="about_meta_keyword" style="height:100px;"><?php echo $about_meta_keyword; ?></textarea>
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <label for="" class="col-sm-3 control-label">Meta Description </label>
                                        <div class="col-sm-8">
                                            <textarea class="form-control" name="about_meta_description" style="height:100px;"><?php echo $about_meta_description; ?></textarea>
                                        </div>
                                    </div>                                    
                                    <div class="form-group">
                                        <label for="" class="col-sm-3 control-label"></label>
                                        <div class="col-sm-6">
                                            <button type="submit" class="btn btn-success pull-left" name="form_about">Update</button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            </form>
                        </div>

        <!-- FAQ Page Content -->

                        <div class="tab-pane <?php echo $activeTab === 'tab_2' ? 'active' : ''; ?>" id="tab_2">
                            <form class="form-horizontal" action="" method="post" enctype="multipart/form-data">
                            <div class="box box-info">
                                <div class="box-body">
                                    <div class="form-group">
                                        <label for="" class="col-sm-3 control-label">Page Title * </label>
                                        <div class="col-sm-5">
                                            <input class="form-control" type="text" name="faq_title" value="<?php echo $faq_title; ?>">
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <label for="" class="col-sm-3 control-label">Existing Banner Photo</label>
                                        <div class="col-sm-6" style="padding-top:6px;">
                                            <img src="../assets/uploads/<?php echo $faq_banner; ?>" class="existing-photo" style="height:80px;">
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <label for="" class="col-sm-3 control-label">New Banner Photo</label>
                                        <div class="col-sm-6" style="padding-top:6px;">
                                            <input type="file" name="faq_banner">
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <label for="" class="col-sm-3 control-label">Meta Title</label>
                                        <div class="col-sm-8">
                                            <input class="form-control" type="text" name="faq_meta_title" value="<?php echo $faq_meta_title; ?>">
                                        </div>
                                    </div>             
                                    <div class="form-group">
                                        <label for="" class="col-sm-3 control-label">Meta Keyword </label>
                                        <div class="col-sm-8">
                                            <textarea class="form-control" name="faq_meta_keyword" style="height:100px;"><?php echo $faq_meta_keyword; ?></textarea>
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <label for="" class="col-sm-3 control-label">Meta Description </label>
                                        <div class="col-sm-8">
                                            <textarea class="form-control" name="faq_meta_description" style="height:100px;"><?php echo $faq_meta_description; ?></textarea>
                                        </div>
                                    </div>                                    
                                    <div class="form-group">
                                        <label for="" class="col-sm-3 control-label"></label>
                                        <div class="col-sm-6">
                                            <button type="submit" class="btn btn-success pull-left" name="form_faq">Update</button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            </form>
                        </div>

                        <!-- End of FAQ Page Content -->

                        <div class="tab-pane <?php echo $activeTab === 'tab_4' ? 'active' : ''; ?>" id="tab_4">
                            <form class="form-horizontal" action="" method="post" enctype="multipart/form-data">
                            <div class="box box-info">
                                <div class="box-body">
                                    <div class="form-group">
                                        <label for="" class="col-sm-3 control-label">Page Title * </label>
                                        <div class="col-sm-5">
                                            <input class="form-control" type="text" name="contact_title" value="<?php echo $contact_title; ?>">
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <label for="" class="col-sm-3 control-label">Existing Banner Photo</label>
                                        <div class="col-sm-6" style="padding-top:6px;">
                                            <?php echo adminPageBannerPreview($contact_banner); ?>
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <label for="" class="col-sm-3 control-label">Contact Image *</label>
                                        <div class="col-sm-6" style="padding-top:6px;">
                                            <input type="file" name="contact_banner" accept=".jpg,.jpeg,.png,.gif,.webp">
                                            <p class="help-block">Shown on homepage Contact section (form + image). jpg/png/gif/webp</p>
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <label for="" class="col-sm-3 control-label">Meta Title</label>
                                        <div class="col-sm-8">
                                            <input class="form-control" type="text" name="contact_meta_title" value="<?php echo $contact_meta_title; ?>">
                                        </div>
                                    </div>             
                                    <div class="form-group">
                                        <label for="" class="col-sm-3 control-label">Meta Keyword </label>
                                        <div class="col-sm-8">
                                            <textarea class="form-control" name="contact_meta_keyword" style="height:100px;"><?php echo $contact_meta_keyword; ?></textarea>
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <label for="" class="col-sm-3 control-label">Meta Description </label>
                                        <div class="col-sm-8">
                                            <textarea class="form-control" name="contact_meta_description" style="height:100px;"><?php echo $contact_meta_description; ?></textarea>
                                        </div>
                                    </div>                                    
                                    <div class="form-group">
                                        <label for="" class="col-sm-3 control-label"></label>
                                        <div class="col-sm-6">
                                            <button type="submit" class="btn btn-success pull-left" name="form_contact">Update</button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            </form>
                        </div>



                

            </form>
        </div>
    </div>

</section>

<?php require_once('footer.php'); ?>