<?php
ob_start();
session_start();
include("inc/config.php");
include("inc/functions.php");
include("inc/CSRF_Protect.php");
$csrf = new CSRF_Protect();
$error_message = '';
$success_message = '';
$error_message1 = '';
$success_message1 = '';

// Check if the user is logged in or not
if(!isset($_SESSION['user'])) {
	header('location: login.php');
	exit;
}
?>

<!DOCTYPE html>
<html>
<head>
	<meta charset="utf-8">
	<meta http-equiv="X-UA-Compatible" content="IE=edge">
	<title>Admin Panel</title>

	<meta content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no" name="viewport">
	<?php
	$adminFaviconFile = '';
	try {
		$adminFavRow = $pdo->query("SELECT favicon, logo FROM tbl_settings WHERE id=1 LIMIT 1")->fetch(PDO::FETCH_ASSOC) ?: array();
		$adminFaviconFile = trim((string) ($adminFavRow['favicon'] ?? ''));
		if ($adminFaviconFile === '') {
			$adminFaviconFile = trim((string) ($adminFavRow['logo'] ?? ''));
		}
	} catch (Exception $e) {
		$adminFaviconFile = '';
	}
	if ($adminFaviconFile !== '' && is_file('../assets/uploads/' . $adminFaviconFile)):
		$adminFaviconUrl = htmlspecialchars(adminUploadUrl($adminFaviconFile), ENT_QUOTES, 'UTF-8');
	?>
	<link rel="icon" href="<?php echo $adminFaviconUrl; ?>">
	<link rel="shortcut icon" href="<?php echo $adminFaviconUrl; ?>">
	<?php endif; ?>

	<link rel="stylesheet" href="css/bootstrap.min.css">
	<link rel="stylesheet" href="css/font-awesome.min.css">
	<link rel="stylesheet" href="css/ionicons.min.css">
	<link rel="stylesheet" href="css/datepicker3.css">
	<link rel="stylesheet" href="css/all.css">
	<link rel="stylesheet" href="css/select2.min.css">
	<link rel="stylesheet" href="css/dataTables.bootstrap.css">
	<link rel="stylesheet" href="css/jquery.fancybox.css">
	<link rel="stylesheet" href="css/AdminLTE.min.css">
	<link rel="stylesheet" href="css/_all-skins.min.css">
	<link rel="stylesheet" href="css/on-off-switch.css"/>
	<link rel="stylesheet" href="css/summernote.css">
	<link rel="stylesheet" href="style.css">

</head>

<body class="hold-transition fixed skin-blue sidebar-mini">

	<div class="wrapper">

		<header class="main-header">

			<a href="index.php" class="logo">
				<span class="logo-lg"><strong>8848 Cleaning Service</strong></span>
			</a>

			<nav class="navbar navbar-static-top">
				
				<a href="#" class="sidebar-toggle" data-toggle="offcanvas" role="button">
					<span class="sr-only">Toggle navigation</span>
				</a>

				<span style="float:left;line-height:50px;color:#fff;padding-left:15px;font-size:18px;">Admin Panel</span>
    <!-- Top Bar ... User Inforamtion .. Login/Log out Area -->
				<div class="navbar-custom-menu">
					<ul class="nav navbar-nav">
						<li class="dropdown user user-menu">
							<a href="#" class="dropdown-toggle" data-toggle="dropdown">
								<img src="../assets/uploads/<?php echo $_SESSION['user']['photo']; ?>" class="user-image" alt="User Image">
								<span class="hidden-xs"><?php echo $_SESSION['user']['full_name']; ?></span>
							</a>
							<ul class="dropdown-menu">
								<li class="user-footer">
									<div>
										<a href="profile-edit.php" class="btn btn-default btn-flat">Edit Profile</a>
									</div>
									<div>
										<a href="logout.php" class="btn btn-default btn-flat">Log out</a>
									</div>
								</li>
							</ul>
						</li>
					</ul>
				</div>

			</nav>
		</header>

  		<?php $cur_page = substr($_SERVER["SCRIPT_NAME"],strrpos($_SERVER["SCRIPT_NAME"],"/")+1); ?>
<!-- Side Bar to Manage Shop Activities -->
  		<aside class="main-sidebar">
    		<section class="sidebar">
      
<ul class="sidebar-menu">

    <!-- Dashboard -->
    <li class="<?php if($cur_page == 'index.php') { echo 'active'; } ?>">
        <a href="index.php">
            <i class="fa fa-dashboard"></i>
            <span>Dashboard</span>
        </a>
    </li>


    <!-- OPERATIONS -->
    <li class="treeview <?php
        if(
            $cur_page == 'order.php' ||
            $cur_page == 'order-add.php' ||
            $cur_page == 'order-edit.php' ||
            $cur_page == 'order-show.php' ||
            $cur_page == 'order-assign.php' ||
            $cur_page == 'customer.php' ||
            $cur_page == 'customer-add.php' ||
            $cur_page == 'customer-edit.php' ||
            $cur_page == 'contact-inquiry.php' ||
            $cur_page == 'contact-inquiry-add.php'
        ) {
            echo 'active';
        }
    ?>">
        <a href="#">
            <i class="fa fa-briefcase"></i>
            <span>Operations</span>
            <span class="pull-right-container">
                <i class="fa fa-angle-left pull-right"></i>
            </span>
        </a>

        <ul class="treeview-menu">

            <li class="<?php
                if(
                    $cur_page == 'order.php' ||
                    $cur_page == 'order-add.php' ||
                    $cur_page == 'order-edit.php' ||
                    $cur_page == 'order-show.php' ||
                    $cur_page == 'order-assign.php'
                ) {
                    echo 'active';
                }
            ?>">
                <a href="order.php">
                    <i class="fa fa-calendar-check-o"></i>
                    Booking Management
                </a>
            </li>

            <li class="<?php
                if(
                    $cur_page == 'customer.php' ||
                    $cur_page == 'customer-add.php' ||
                    $cur_page == 'customer-edit.php'
                ) {
                    echo 'active';
                }
            ?>">
                <a href="customer.php">
                    <i class="fa fa-users"></i>
                    Registered Customers
                </a>
            </li>

            <li class="<?php
                if(
                    $cur_page == 'contact-inquiry.php' ||
                    $cur_page == 'contact-inquiry-add.php'
                ) {
                    echo 'active';
                }
            ?>">
                <a href="contact-inquiry.php">
                    <i class="fa fa-envelope"></i>
                    Contact Inquiries
                </a>
            </li>

        </ul>
    </li>


    <!-- REFERRALS -->
    <li class="<?php if($cur_page == 'referral.php' || $cur_page == 'referral-edit.php' || $cur_page == 'referral-delete.php') { echo 'active'; } ?>">
        <a href="referral.php">
            <i class="fa fa-share-alt"></i>
            <span>Referral Offer</span>
        </a>
    </li>


    <!-- SERVICES -->
    <li class="treeview <?php
        if(
            $cur_page == 'product.php' ||
            $cur_page == 'product-add.php' ||
            $cur_page == 'product-edit.php' ||
            $cur_page == 'country.php' ||
            $cur_page == 'country-add.php' ||
            $cur_page == 'country-edit.php' ||
            $cur_page == 'top-category.php' ||
            $cur_page == 'top-category-add.php' ||
            $cur_page == 'top-category-edit.php' ||
            $cur_page == 'mid-category.php' ||
            $cur_page == 'mid-category-add.php' ||
            $cur_page == 'mid-category-edit.php' ||
            $cur_page == 'end-category.php' ||
            $cur_page == 'end-category-add.php' ||
            $cur_page == 'end-category-edit.php' ||
            $cur_page == 'size.php' ||
            $cur_page == 'size-add.php' ||
            $cur_page == 'size-edit.php' ||
            $cur_page == 'color.php' ||
            $cur_page == 'color-add.php' ||
            $cur_page == 'color-edit.php' ||
            $cur_page == 'shipping-cost.php' ||
            $cur_page == 'shipping-cost-edit.php'
        ) {
            echo 'active';
        }
    ?>">
        <a href="#">
            <i class="fa fa-cogs"></i>
            <span>Services</span>
            <span class="pull-right-container">
                <i class="fa fa-angle-left pull-right"></i>
            </span>
        </a>

        <ul class="treeview-menu">

            <li class="<?php
                if(
                    $cur_page == 'product.php' ||
                    $cur_page == 'product-add.php' ||
                    $cur_page == 'product-edit.php'
                ) {
                    echo 'active';
                }
            ?>">
                <a href="product.php">
                    <i class="fa fa-list-alt"></i>
                    Service Catalog
                </a>
            </li>

            <li class="<?php
                if(
                    $cur_page == 'top-category.php' ||
                    $cur_page == 'top-category-add.php' ||
                    $cur_page == 'top-category-edit.php' ||
                    $cur_page == 'mid-category.php' ||
                    $cur_page == 'mid-category-add.php' ||
                    $cur_page == 'mid-category-edit.php' ||
                    $cur_page == 'end-category.php' ||
                    $cur_page == 'end-category-add.php' ||
                    $cur_page == 'end-category-edit.php'
                ) {
                    echo 'active';
                }
            ?>">
                <a href="top-category.php">
                    <i class="fa fa-sitemap"></i>
                    Service Categories
                </a>
            </li>

            <li class="<?php
                if(
                    $cur_page == 'country.php' ||
                    $cur_page == 'country-add.php' ||
                    $cur_page == 'country-edit.php'
                ) {
                    echo 'active';
                }
            ?>">
                <a href="country.php">
                    <i class="fa fa-map-marker"></i>
                    Regions / Locations
                </a>
            </li>

        </ul>
    </li>


    <!-- STAFF & FINANCE -->
    <li class="treeview <?php
        if(
            $cur_page == 'staff.php' ||
            $cur_page == 'staff-add.php' ||
            $cur_page == 'staff-edit.php' ||
            $cur_page == 'staff-availability.php' ||
            $cur_page == 'commission.php' ||
            $cur_page == 'commission-pay.php' ||
            $cur_page == 'staff-report.php'
        ) {
            echo 'active';
        }
    ?>">
        <a href="#">
            <i class="fa fa-id-card"></i>
            <span>Staff & Finance</span>
            <span class="pull-right-container">
                <i class="fa fa-angle-left pull-right"></i>
            </span>
        </a>

        <ul class="treeview-menu">

            <li class="<?php
                if(
                    $cur_page == 'staff.php' ||
                    $cur_page == 'staff-add.php' ||
                    $cur_page == 'staff-edit.php' ||
                    $cur_page == 'staff-availability.php'
                ) {
                    echo 'active';
                }
            ?>">
                <a href="staff.php">
                    <i class="fa fa-users"></i>
                    Staff Management
                </a>
            </li>

            <li class="<?php
                if($cur_page == 'commission.php') {
                    echo 'active';
                }
            ?>">
                <a href="commission.php">
                    <i class="fa fa-bar-chart"></i>
                    Commission Report
                </a>
            </li>

            <li class="<?php
                if($cur_page == 'commission-pay.php') {
                    echo 'active';
                }
            ?>">
                <a href="commission-pay.php">
                    <i class="fa fa-money"></i>
                    Pay Commissions
                </a>
            </li>

            <li class="<?php
                if($cur_page == 'staff-report.php') {
                    echo 'active';
                }
            ?>">
                <a href="staff-report.php">
                    <i class="fa fa-file-text-o"></i>
                    Staff Report
                </a>
            </li>

        </ul>
    </li>


    <!-- WEBSITE CONTENT -->
    <li class="treeview <?php
        if(
            $cur_page == 'slider.php' ||
            $cur_page == 'gallery.php' ||
            $cur_page == 'gallery-add.php' ||
            $cur_page == 'gallery-edit.php' ||
            $cur_page == 'faq.php' ||
            $cur_page == 'faq-add.php' ||
            $cur_page == 'faq-edit.php' ||
            $cur_page == 'testimonial.php' ||
            $cur_page == 'testimonial-add.php' ||
            $cur_page == 'testimonial-edit.php' ||
            $cur_page == 'client.php' ||
            $cur_page == 'client-add.php' ||
            $cur_page == 'client-edit.php' ||
            $cur_page == 'blog.php' ||
            $cur_page == 'blog-add.php' ||
            $cur_page == 'blog-edit.php'
        ) {
            echo 'active';
        }
    ?>">
        <a href="#">
            <i class="fa fa-edit"></i>
            <span>Website Content</span>
            <span class="pull-right-container">
                <i class="fa fa-angle-left pull-right"></i>
            </span>
        </a>

        <ul class="treeview-menu">

            <li class="<?php if($cur_page == 'slider.php') { echo 'active'; } ?>">
                <a href="slider.php">
                    <i class="fa fa-picture-o"></i>
                    Sliders
                </a>
            </li>

            <li class="<?php
                if(
                    $cur_page == 'gallery.php' ||
                    $cur_page == 'gallery-add.php' ||
                    $cur_page == 'gallery-edit.php'
                ) {
                    echo 'active';
                }
            ?>">
                <a href="gallery.php">
                    <i class="fa fa-camera"></i>
                    Gallery
                </a>
            </li>

            <li class="<?php
                if(
                    $cur_page == 'blog.php' ||
                    $cur_page == 'blog-add.php' ||
                    $cur_page == 'blog-edit.php'
                ) {
                    echo 'active';
                }
            ?>">
                <a href="blog.php">
                    <i class="fa fa-newspaper-o"></i>
                    Blog
                </a>
            </li>

            <li class="<?php
                if(
                    $cur_page == 'faq.php' ||
                    $cur_page == 'faq-add.php' ||
                    $cur_page == 'faq-edit.php'
                ) {
                    echo 'active';
                }
            ?>">
                <a href="faq.php">
                    <i class="fa fa-question-circle"></i>
                    FAQ
                </a>
            </li>

            <li class="<?php
                if(
                    $cur_page == 'testimonial.php' ||
                    $cur_page == 'testimonial-add.php' ||
                    $cur_page == 'testimonial-edit.php'
                ) {
                    echo 'active';
                }
            ?>">
                <a href="testimonial.php">
                    <i class="fa fa-star"></i>
                    Testimonials / Reviews
                </a>
            </li>

            <li class="<?php
                if(
                    $cur_page == 'client.php' ||
                    $cur_page == 'client-add.php' ||
                    $cur_page == 'client-edit.php'
                ) {
                    echo 'active';
                }
            ?>">
                <a href="client.php">
                    <i class="fa fa-building"></i>
                    Clients / Logos
                </a>
            </li>

        </ul>
    </li>


    <!-- WEBSITE MANAGEMENT -->
    <li class="treeview <?php
        if(
            $cur_page == 'settings.php' ||
            $cur_page == 'page.php' ||
            $cur_page == 'social-media.php' ||
            $cur_page == 'subscriber.php'
        ) {
            echo 'active';
        }
    ?>">
        <a href="#">
            <i class="fa fa-globe"></i>
            <span>Website Management</span>
            <span class="pull-right-container">
                <i class="fa fa-angle-left pull-right"></i>
            </span>
        </a>

        <ul class="treeview-menu">

            <li class="<?php if($cur_page == 'settings.php') { echo 'active'; } ?>">
                <a href="settings.php">
                    <i class="fa fa-sliders"></i>
                    Website Settings
                </a>
            </li>

            <li class="<?php if($cur_page == 'page.php') { echo 'active'; } ?>">
                <a href="page.php">
                    <i class="fa fa-file-text-o"></i>
                    Page Settings
                </a>
            </li>

            <li class="<?php if($cur_page == 'social-media.php') { echo 'active'; } ?>">
                <a href="social-media.php">
                    <i class="fa fa-share-alt"></i>
                    Social Media
                </a>
            </li>

            <li class="<?php if($cur_page == 'subscriber.php') { echo 'active'; } ?>">
                <a href="subscriber.php">
                    <i class="fa fa-envelope-o"></i>
                    Subscribers
                </a>
            </li>

        </ul>
    </li>


    <!-- ACCOUNT -->
    <li class="treeview">
        <a href="#">
            <i class="fa fa-user-circle"></i>
            <span>Account</span>
            <span class="pull-right-container">
                <i class="fa fa-angle-left pull-right"></i>
            </span>
        </a>

        <ul class="treeview-menu">

            <li>
                <a href="profile-edit.php">
                    <i class="fa fa-user"></i>
                    My Profile
                </a>
            </li>

            <li>
                <a href="logout.php">
                    <i class="fa fa-sign-out"></i>
                    Log Out
                </a>
            </li>

        </ul>
    </li>

</ul>
    		</section>
  		</aside>

  		<div class="content-wrapper">