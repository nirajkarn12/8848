<?php
require_once __DIR__ . '/bootstrap.php';
requireStaffLogin();
$staff = currentStaff();
$pageTitle = $pageTitle ?? 'My Jobs';
$cur_page = basename($_SERVER['SCRIPT_NAME']);
$adminCss = BASE_URL . 'admin/css/';
$photo = staffPhotoUrl($staff['photo'] ?? '');
$siteBrandName = trim((string) getSiteSetting('site_name', SITE_NAME));
date_default_timezone_set('Pacific/Auckland');
$aucklandNow = new DateTime('now', new DateTimeZone('Pacific/Auckland'));
$aucklandDateTime = $aucklandNow->format('d M Y, H:i:s');
?>
<!DOCTYPE html>
<html>
<head>
	<meta charset="utf-8">
	<meta http-equiv="X-UA-Compatible" content="IE=edge">
	<title><?php echo htmlspecialchars($pageTitle); ?> | <?php echo htmlspecialchars($siteBrandName); ?></title>
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
				<span class="logo-lg">STAFF PANEL</span>
			</a>
			<nav class="navbar navbar-static-top">
				<a href="#" class="sidebar-toggle" data-toggle="offcanvas" role="button">
					<span class="sr-only">Toggle navigation</span>
				</a>
				<span style="float:left;display:inline-flex;align-items:center;height:34px;margin:8px 12px 8px 12px;padding:0 14px;border-radius:999px;background:rgba(255,255,255,0.12);border:1px solid rgba(255,255,255,0.18);color:#fff;font-size:12px;font-weight:700;letter-spacing:0.4px;box-shadow:inset 0 1px 0 rgba(255,255,255,0.12);">
					<i class="fa fa-clock-o" style="margin-right:7px;font-size:13px;"></i>
					<span><?php echo htmlspecialchars($aucklandDateTime); ?> NZST</span>
				</span>
				<div class="navbar-custom-menu">
					<ul class="nav navbar-nav">
						<li class="dropdown notifications-menu">
							<a href="#" class="dropdown-toggle" data-toggle="dropdown" aria-expanded="false" style="position:relative;">
								<i class="fa fa-bell-o"></i>
								<span class="label label-warning" style="position:absolute;top:8px;right:8px;min-width:18px;height:18px;line-height:16px;border-radius:50%;padding:0 5px;font-size:10px;font-weight:700;">3</span>
							</a>
							<ul class="dropdown-menu" style="width:290px;max-width:90vw;">
								<li class="header" style="padding:10px 15px;border-bottom:1px solid #f4f4f4;font-size:12px;font-weight:600;">You have 3 notifications</li>
								<li>
									<ul class="menu" style="list-style:none;padding:0;margin:0;max-height:260px;overflow-y:auto;">
										<li style="padding:10px 15px;border-bottom:1px solid #f4f4f4;">
											<div style="display:flex;align-items:flex-start;justify-content:space-between;gap:10px;">
												<a href="#" style="display:block;color:#333;white-space:normal;line-height:1.4;flex:1;">
													<i class="fa fa-calendar text-aqua" style="margin-right:8px;"></i>
													New booking assigned for today.
												</a>
												<a href="#" style="font-size:11px;color:#3c8dbc;white-space:nowrap;">Mark as read</a>
											</div>
										</li>
										<li style="padding:10px 15px;border-bottom:1px solid #f4f4f4;">
											<div style="display:flex;align-items:flex-start;justify-content:space-between;gap:10px;">
												<a href="#" style="display:block;color:#333;white-space:normal;line-height:1.4;flex:1;">
													<i class="fa fa-exclamation-circle text-yellow" style="margin-right:8px;"></i>
													Job status updated by admin.
												</a>
												<a href="#" style="font-size:11px;color:#3c8dbc;white-space:nowrap;">Mark as read</a>
											</div>
										</li>
										<li style="padding:10px 15px;">
											<div style="display:flex;align-items:flex-start;justify-content:space-between;gap:10px;">
												<a href="#" style="display:block;color:#333;white-space:normal;line-height:1.4;flex:1;">
													<i class="fa fa-money text-green" style="margin-right:8px;"></i>
													Earnings update is ready to review.
												</a>
												<a href="#" style="font-size:11px;color:#3c8dbc;white-space:nowrap;">Mark as read</a>
											</div>
										</li>
									</ul>
								</li>
							</ul>
						</li>
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
