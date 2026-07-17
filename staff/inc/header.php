<?php
require_once __DIR__ . '/bootstrap.php';
requireStaffLogin();
$staff = currentStaff();
$pageTitle = $pageTitle ?? 'My Jobs';
$cur_page = basename($_SERVER['SCRIPT_NAME']);
$adminCss = BASE_URL . 'admin/css/';
$photo = staffPhotoUrl($staff['photo'] ?? '');
?>
<!DOCTYPE html>
<html>
<head>
	<meta charset="utf-8">
	<meta http-equiv="X-UA-Compatible" content="IE=edge">
	<title><?php echo htmlspecialchars($pageTitle); ?> | Staff Panel</title>
	<meta content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no" name="viewport">
	<meta name="theme-color" content="#3c8dbc">
	<meta name="apple-mobile-web-app-capable" content="yes">
	<link rel="manifest" href="<?php echo STAFF_URL; ?>manifest.webmanifest">
	<link rel="apple-touch-icon" href="<?php echo STAFF_URL; ?>assets/icon-192.png">

	<link rel="stylesheet" href="<?php echo $adminCss; ?>bootstrap.min.css">
	<link rel="stylesheet" href="<?php echo $adminCss; ?>font-awesome.min.css">
	<link rel="stylesheet" href="<?php echo $adminCss; ?>ionicons.min.css">
	<link rel="stylesheet" href="<?php echo $adminCss; ?>dataTables.bootstrap.css">
	<link rel="stylesheet" href="<?php echo $adminCss; ?>AdminLTE.min.css">
	<link rel="stylesheet" href="<?php echo $adminCss; ?>_all-skins.min.css">
	<link rel="stylesheet" href="<?php echo BASE_URL; ?>admin/style.css">
</head>

<body class="hold-transition fixed skin-blue sidebar-mini">
	<div class="wrapper">

		<header class="main-header">
			<a href="<?php echo STAFF_URL; ?>index.php" class="logo">
				<span class="logo-lg"><b>STAFF</b> PANEL</span>
			</a>
			<nav class="navbar navbar-static-top">
				<a href="#" class="sidebar-toggle" data-toggle="offcanvas" role="button">
					<span class="sr-only">Toggle navigation</span>
				</a>
				<span style="float:left;line-height:50px;color:#fff;padding-left:15px;font-size:18px;">Staff Panel</span>
				<div class="navbar-custom-menu">
					<ul class="nav navbar-nav">
						<li class="dropdown user user-menu">
							<a href="#" class="dropdown-toggle" data-toggle="dropdown">
								<img src="<?php echo htmlspecialchars($photo); ?>" class="user-image" alt="User Image">
								<span class="hidden-xs"><?php echo htmlspecialchars($staff['full_name']); ?></span>
							</a>
							<ul class="dropdown-menu">
								<li class="user-header">
									<img src="<?php echo htmlspecialchars($photo); ?>" class="img-circle" alt="User Image">
									<p><?php echo htmlspecialchars($staff['full_name']); ?><small>Field Staff</small></p>
								</li>
								<li class="user-footer">
									<div class="pull-left">
										<a href="<?php echo STAFF_URL; ?>profile.php" class="btn btn-default btn-flat">Edit Profile</a>
									</div>
									<div class="pull-right">
										<a href="<?php echo STAFF_URL; ?>logout.php" class="btn btn-default btn-flat">Log out</a>
									</div>
								</li>
							</ul>
						</li>
					</ul>
				</div>
			</nav>
		</header>

		<aside class="main-sidebar">
			<section class="sidebar">
				<div class="user-panel">
					<div class="pull-left image">
						<img src="<?php echo htmlspecialchars($photo); ?>" class="img-circle" alt="User Image">
					</div>
					<div class="pull-left info">
						<p><?php echo htmlspecialchars($staff['full_name']); ?></p>
						<a href="#"><i class="fa fa-circle text-success"></i> Online</a>
					</div>
				</div>
				<ul class="sidebar-menu">
					<li class="header">MAIN NAVIGATION</li>
					<li class="<?php echo ($cur_page === 'index.php' || $cur_page === 'job.php') ? 'active' : ''; ?>">
						<a href="<?php echo STAFF_URL; ?>index.php">
							<i class="fa fa-briefcase"></i> <span>My Jobs</span>
						</a>
					</li>
					<li class="<?php echo ($cur_page === 'earnings.php') ? 'active' : ''; ?>">
						<a href="<?php echo STAFF_URL; ?>earnings.php">
							<i class="fa fa-money"></i> <span>My Earnings</span>
						</a>
					</li>
					<li class="<?php echo ($cur_page === 'profile.php') ? 'active' : ''; ?>">
						<a href="<?php echo STAFF_URL; ?>profile.php">
							<i class="fa fa-user"></i> <span>My Profile</span>
						</a>
					</li>
					<li>
						<a href="<?php echo STAFF_URL; ?>logout.php">
							<i class="fa fa-sign-out"></i> <span>Logout</span>
						</a>
					</li>
				</ul>
			</section>
		</aside>

		<div class="content-wrapper">
