<?php
function get_ext($pdo,$fname)
{

	$up_filename=$_FILES[$fname]["name"];
	$file_basename = substr($up_filename, 0, strripos($up_filename, '.')); // strip extention
	$file_ext = substr($up_filename, strripos($up_filename, '.')); // strip name
	return $file_ext;
}

/**
 * Allowed image extensions for admin uploads.
 * @param bool $includeIco include .ico (for favicon)
 */
function adminAllowedImageExtensions($includeIco = false) {
	$exts = array('jpg', 'jpeg', 'png', 'gif', 'webp');
	if ($includeIco) {
		$exts[] = 'ico';
	}
	return $exts;
}

function adminNormalizeUploadExt($filename) {
	return strtolower(pathinfo((string) $filename, PATHINFO_EXTENSION));
}

function adminIsAllowedImageExt($ext, $includeIco = false) {
	return in_array(strtolower((string) $ext), adminAllowedImageExtensions($includeIco), true);
}

function adminImageAcceptAttribute($includeIco = false) {
	$parts = array();
	foreach (adminAllowedImageExtensions($includeIco) as $ext) {
		$parts[] = '.' . $ext;
		$parts[] = 'image/' . ($ext === 'jpg' ? 'jpeg' : ($ext === 'ico' ? 'x-icon' : $ext));
	}
	return implode(',', array_unique($parts));
}

/**
 * Save an uploaded image into assets/uploads with a stable base name.
 * Deletes previous base-name variants (logo.png vs logo.jpg, etc.).
 *
 * @return array{ok:bool,filename:string,error:string}
 */
function adminSaveNamedImageUpload($filesKey, $baseName, $includeIco = false) {
	$upload = is_array($filesKey) ? $filesKey : array();
	$name = (string) ($upload['name'] ?? '');
	$tmp = (string) ($upload['tmp_name'] ?? '');
	$error = (int) ($upload['error'] ?? UPLOAD_ERR_NO_FILE);

	if ($name === '' || $error === UPLOAD_ERR_NO_FILE) {
		return array('ok' => false, 'filename' => '', 'error' => 'Please select an image file.<br>');
	}
	if ($error !== UPLOAD_ERR_OK || $tmp === '' || !is_uploaded_file($tmp)) {
		return array('ok' => false, 'filename' => '', 'error' => 'Image upload failed. Please try again.<br>');
	}

	$ext = adminNormalizeUploadExt($name);
	if (!adminIsAllowedImageExt($ext, $includeIco)) {
		$allowed = implode(', ', adminAllowedImageExtensions($includeIco));
		return array('ok' => false, 'filename' => '', 'error' => 'Invalid format. Allowed: ' . $allowed . '<br>');
	}

	// Soft MIME check (ico often reports as application/octet-stream)
	if (function_exists('finfo_open')) {
		$finfo = finfo_open(FILEINFO_MIME_TYPE);
		$mime = $finfo ? (string) finfo_file($finfo, $tmp) : '';
		if ($finfo) {
			finfo_close($finfo);
		}
		$okMimes = array(
			'image/jpeg', 'image/png', 'image/gif', 'image/webp',
			'image/x-icon', 'image/vnd.microsoft.icon', 'image/ico', 'image/icon',
			'application/octet-stream',
		);
		if ($mime !== '' && !in_array($mime, $okMimes, true) && strpos($mime, 'image/') !== 0) {
			return array('ok' => false, 'filename' => '', 'error' => 'File does not look like a valid image.<br>');
		}
	}

	$dir = dirname(__DIR__) . '/../assets/uploads/';
	$dir = realpath($dir) ?: (dirname(__DIR__) . '/../assets/uploads');
	$dir = rtrim(str_replace('\\', '/', $dir), '/') . '/';
	if (!is_dir($dir)) {
		@mkdir($dir, 0755, true);
	}

	$baseName = preg_replace('/[^a-zA-Z0-9_-]/', '', (string) $baseName);
	if ($baseName === '') {
		$baseName = 'image';
	}

	// Remove previous variants with any allowed extension
	foreach (adminAllowedImageExtensions(true) as $oldExt) {
		$oldPath = $dir . $baseName . '.' . $oldExt;
		if (is_file($oldPath)) {
			@unlink($oldPath);
		}
	}

	$finalName = $baseName . '.' . $ext;
	$dest = $dir . $finalName;
	if (!move_uploaded_file($tmp, $dest)) {
		return array('ok' => false, 'filename' => '', 'error' => 'Could not save uploaded image.<br>');
	}
	@chmod($dest, 0644);

	return array('ok' => true, 'filename' => $finalName, 'error' => '');
}

/**
 * Build an admin upload image URL with a filemtime cache-buster.
 * Fixes stale previews when uploads overwrite the same filename.
 */
function adminUploadUrl($filename, $subdir = '') {
	$filename = ltrim(str_replace('\\', '/', (string) $filename), '/');
	if ($filename === '' || preg_match('#^(https?:)?//#i', $filename)) {
		return $filename;
	}
	if (strpos($filename, 'assets/uploads/') === 0) {
		$relative = '../' . $filename;
	} elseif (strpos($filename, '../assets/uploads/') === 0) {
		$relative = $filename;
	} else {
		$prefix = $subdir !== '' ? (rtrim($subdir, '/') . '/') : '';
		$relative = '../assets/uploads/' . $prefix . $filename;
	}
	$fsPath = $relative;
	$queryPos = strpos($fsPath, '?');
	if ($queryPos !== false) {
		$fsPath = substr($fsPath, 0, $queryPos);
	}
	$v = is_file($fsPath) ? ((int) @filemtime($fsPath) . '-' . (int) @filesize($fsPath)) : (string) time();
	$base = preg_replace('/[?&]v=[^&]*/', '', $relative);
	$base = rtrim($base, '?&');
	return $base . (strpos($base, '?') !== false ? '&' : '?') . 'v=' . rawurlencode($v);
}

/**
 * Rewrite upload <img src> URLs in buffered admin HTML so replaced files show immediately.
 */
function adminBustUploadImageUrls($html) {
	return preg_replace_callback(
		'#(\bsrc\s*=\s*)(["\'])((?:\.\./)?assets/uploads/[^"\']+)\2#i',
		static function ($m) {
			$src = $m[3];
			$clean = preg_replace('/[?&]v=[^&]*/', '', $src);
			$clean = rtrim($clean, '?&');
			$fsPath = (strpos($clean, '../') === 0) ? $clean : ('../' . ltrim($clean, '/'));
			$v = is_file($fsPath) ? ((int) @filemtime($fsPath) . '-' . (int) @filesize($fsPath)) : (string) time();
			$bust = $clean . (strpos($clean, '?') !== false ? '&' : '?') . 'v=' . rawurlencode($v);
			return $m[1] . $m[2] . $bust . $m[2];
		},
		(string) $html
	);
}

function ext_check($pdo,$allowed_ext,$my_ext) 
{

	$arr1 = array();
	$arr1 = explode("|",$allowed_ext);	
	$count_arr1 = count(explode("|",$allowed_ext));	

	for($i=0;$i<$count_arr1;$i++)
	{
		$arr1[$i] = '.'.$arr1[$i];
	}
	

	$str = '';
	$stat = 0;
	for($i=0;$i<$count_arr1;$i++)
	{
		if($my_ext == $arr1[$i])
		{
			$stat = 1;
			break;
		}
	}

	if($stat == 1)
		return true; // file extension match
	else
		return false; // file extension not match
}


function get_ai_id($pdo,$tbl_name) 
{
	$statement = $pdo->prepare("SHOW TABLE STATUS LIKE '$tbl_name'");
	$statement->execute();
	$result = $statement->fetchAll(PDO::FETCH_ASSOC);
	foreach($result as $row)
	{
		$next_id = $row['Auto_increment'];
	}
	return $next_id;
}

/**
 * Services attach to mid category in the UI.
 * Internally we reuse tbl_end_category so existing product.ecat_id keeps working.
 */
function resolveServiceEndCategory($pdo, $mcatId)
{
	$mcatId = (int) $mcatId;
	if ($mcatId <= 0) {
		return 0;
	}

	$statement = $pdo->prepare("SELECT ecat_id FROM tbl_end_category WHERE mcat_id = ? ORDER BY ecat_id ASC LIMIT 1");
	$statement->execute(array($mcatId));
	$row = $statement->fetch(PDO::FETCH_ASSOC);
	if ($row) {
		return (int) $row['ecat_id'];
	}

	$statement = $pdo->prepare("SELECT mcat_name FROM tbl_mid_category WHERE mcat_id = ? LIMIT 1");
	$statement->execute(array($mcatId));
	$mid = $statement->fetch(PDO::FETCH_ASSOC);
	$ecatName = $mid && !empty($mid['mcat_name']) ? $mid['mcat_name'] : 'Services';

	$statement = $pdo->prepare("INSERT INTO tbl_end_category (ecat_name, mcat_id) VALUES (?, ?)");
	$statement->execute(array($ecatName, $mcatId));
	return (int) $pdo->lastInsertId();
}

function ensureServiceLocationColumns($pdo) {
	static $done = false;
	if ($done) {
		return;
	}
	$done = true;
	$targets = array(
		'tbl_payment' => array('service_lat', 'service_lng'),
		'tbl_booking_assignment' => array('service_lat', 'service_lng'),
	);
	foreach ($targets as $table => $columns) {
		foreach ($columns as $column) {
			try {
				$statement = $pdo->prepare("SHOW COLUMNS FROM `" . $table . "` LIKE ?");
				$statement->execute(array($column));
				if ($statement->rowCount() === 0) {
					$pdo->exec("ALTER TABLE `" . $table . "` ADD COLUMN `" . $column . "` DECIMAL(10,7) NULL");
				}
			} catch (Exception $e) {
				// ignore
			}
		}
	}
}

function normalizeMapCoordinate($value, $min, $max) {
	if ($value === null || $value === '') {
		return null;
	}
	if (!is_numeric($value)) {
		return null;
	}
	$num = (float) $value;
	if ($num < $min || $num > $max) {
		return null;
	}
	return round($num, 7);
}

function adminServiceLocationAssets() {
	$base = rtrim(BASE_URL, '/') . '/';
	return '<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" crossorigin="">'
		. '<link rel="stylesheet" href="' . $base . 'assets/css/service-location-map.css?v=20260721">'
		. '<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js" crossorigin=""></script>'
		. '<script src="' . $base . 'assets/js/service-location-map.js?v=20260721"></script>';
}

function adminRenderServiceLocationPicker($lat = '', $lng = '', $addressInput = '#service_address') {
	ob_start();
	?>
	<div class="service-location-map-wrap admin-map" data-service-map="picker" data-address-input="<?php echo htmlspecialchars($addressInput); ?>">
		<div class="service-location-map-toolbar">
			<input type="search" class="form-control input-sm" data-map-search placeholder="Search place, street, or landmark...">
			<button type="button" class="btn btn-default btn-sm" data-map-search-btn>Search</button>
			<button type="button" class="btn btn-default btn-sm" data-map-locate-btn>Use my location</button>
		</div>
		<div class="service-location-map-canvas" data-map-canvas></div>
		<div class="service-location-map-meta" data-map-meta>Tap the map or search to pin the cleaning location.</div>
		<div class="service-location-map-meta" data-map-status></div>
		<input type="hidden" name="service_lat" data-map-lat value="<?php echo htmlspecialchars((string)$lat); ?>">
		<input type="hidden" name="service_lng" data-map-lng value="<?php echo htmlspecialchars((string)$lng); ?>">
	</div>
	<?php
	return ob_get_clean();
}

function getInvoiceCompanyProfile($pdo) {
	static $profile = null;
	if ($profile !== null) {
		return $profile;
	}

	$row = [];
	try {
		$statement = $pdo->query("SELECT * FROM tbl_settings WHERE id=1 LIMIT 1");
		$row = $statement ? ($statement->fetch(PDO::FETCH_ASSOC) ?: []) : [];
	} catch (Exception $e) {
		$row = [];
	}

	$logoFile = !empty($row['logo']) ? $row['logo'] : '';
	$logoUrl = '';
	if ($logoFile !== '') {
		$logoUrl = rtrim(BASE_URL, '/') . '/assets/uploads/' . ltrim($logoFile, '/');
	} else {
		$logoUrl = rtrim(BASE_URL, '/') . '/assets/images/placeholder.png';
	}

	$profile = array(
		'site_name' => !empty($row['site_name']) ? $row['site_name'] : '8848 Cleaning Service',
		'logo' => $logoFile,
		'logo_url' => $logoUrl,
		'address' => $row['contact_address'] ?? '',
		'email' => $row['contact_email'] ?? '',
		'phone' => $row['contact_phone'] ?? '',
		'copyright' => $row['footer_copyright'] ?? '',
		'about' => $row['footer_about'] ?? '',
		'vat_no' => $row['invoice_vat_no'] ?? '',
		'due_days' => (int)($row['invoice_due_days'] ?? 30),
		'footer_note' => $row['invoice_footer_note'] ?? 'Thank you for choosing our cleaning service.',
	);
	if ($profile['due_days'] <= 0) {
		$profile['due_days'] = 30;
	}
	return $profile;
}

function adminRenderServiceLocationViewer($lat, $lng, $address = '') {
	$lat = normalizeMapCoordinate($lat, -90, 90);
	$lng = normalizeMapCoordinate($lng, -180, 180);
	$query = ($lat !== null && $lng !== null) ? ($lat . ',' . $lng) : $address;
	$google = 'https://www.google.com/maps/search/?api=1&query=' . rawurlencode($query);
	$directions = 'https://www.google.com/maps/dir/?api=1&destination=' . rawurlencode($query);
	$osm = ($lat !== null && $lng !== null)
		? ('https://www.openstreetmap.org/?mlat=' . rawurlencode((string)$lat) . '&mlon=' . rawurlencode((string)$lng) . '#map=16/' . rawurlencode((string)$lat) . '/' . rawurlencode((string)$lng))
		: ('https://www.openstreetmap.org/search?query=' . rawurlencode((string)$address));
	ob_start();
	?>
	<div class="service-location-map-wrap admin-map" data-service-map="view" data-lat="<?php echo htmlspecialchars((string)$lat); ?>" data-lng="<?php echo htmlspecialchars((string)$lng); ?>" data-address="<?php echo htmlspecialchars((string)$address); ?>">
		<div class="service-location-map-canvas" data-map-canvas></div>
		<div class="service-location-map-meta" data-map-meta></div>
		<div class="service-location-map-actions">
			<a class="btn btn-success btn-sm" data-map-directions href="<?php echo htmlspecialchars($directions); ?>" target="_blank" rel="noopener"><i class="fa fa-location-arrow"></i> Get directions</a>
			<a class="btn btn-primary btn-sm" data-map-google href="<?php echo htmlspecialchars($google); ?>" target="_blank" rel="noopener"><i class="fa fa-map-marker"></i> Google Maps</a>
			<a class="btn btn-default btn-sm" data-map-osm href="<?php echo htmlspecialchars($osm); ?>" target="_blank" rel="noopener">OpenStreetMap</a>
		</div>
	</div>
	<?php
	return ob_get_clean();
}

function ensureReferralTables() {
	global $pdo;
	static $ready = null;
	if ($ready !== null) {
		return $ready;
	}
	try {
				$pdo->exec("CREATE TABLE IF NOT EXISTS tbl_referral_settings (id INT UNSIGNED NOT NULL AUTO_INCREMENT, is_active TINYINT(1) NOT NULL DEFAULT 1, discount_type ENUM('percent','amount') NOT NULL DEFAULT 'percent', discount_value DECIMAL(10,2) NOT NULL DEFAULT 10.00, bonus_points INT UNSIGNED NOT NULL DEFAULT 100, points_per_dollar INT UNSIGNED NOT NULL DEFAULT 100, minimum_order_amount DECIMAL(10,2) NOT NULL DEFAULT 0.00, terms TEXT NULL, updated_at DATETIME NULL, PRIMARY KEY (id)) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
				$pdo->exec("CREATE TABLE IF NOT EXISTS tbl_referral (id INT UNSIGNED NOT NULL AUTO_INCREMENT, referral_code VARCHAR(32) NOT NULL, referrer_customer_id INT UNSIGNED NOT NULL, referrer_name VARCHAR(255) NOT NULL, referrer_email VARCHAR(255) NOT NULL, referee_name VARCHAR(255) NOT NULL, referee_email VARCHAR(255) NOT NULL, referee_phone VARCHAR(50) NOT NULL, status ENUM('Pending','Converted','Cancelled') NOT NULL DEFAULT 'Pending', discount_type ENUM('percent','amount') NOT NULL DEFAULT 'percent', discount_value DECIMAL(10,2) NOT NULL DEFAULT 0.00, discount_amount DECIMAL(10,2) NOT NULL DEFAULT 0.00, awarded_points INT UNSIGNED NOT NULL DEFAULT 0, referee_customer_id INT UNSIGNED NULL, payment_id INT UNSIGNED NULL, created_at DATETIME NOT NULL, converted_at DATETIME NULL, PRIMARY KEY (id), UNIQUE KEY uq_referral_code (referral_code), KEY idx_referral_referrer (referrer_customer_id), KEY idx_referral_status (status)) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
				$settingsBonus = $pdo->query("SHOW COLUMNS FROM `tbl_referral_settings` LIKE 'bonus_points'");
				if ($settingsBonus && $settingsBonus->rowCount() === 0) { $pdo->exec("ALTER TABLE `tbl_referral_settings` ADD COLUMN `bonus_points` INT UNSIGNED NOT NULL DEFAULT 100 AFTER `discount_value`"); }
				$pointsRate = $pdo->query("SHOW COLUMNS FROM `tbl_referral_settings` LIKE 'points_per_dollar'");
				if ($pointsRate && $pointsRate->rowCount() === 0) { $pdo->exec("ALTER TABLE `tbl_referral_settings` ADD COLUMN `points_per_dollar` INT UNSIGNED NOT NULL DEFAULT 100 AFTER `bonus_points`"); }
				$awardedPoints = $pdo->query("SHOW COLUMNS FROM `tbl_referral` LIKE 'awarded_points'");
				if ($awardedPoints && $awardedPoints->rowCount() === 0) { $pdo->exec("ALTER TABLE `tbl_referral` ADD COLUMN `awarded_points` INT UNSIGNED NOT NULL DEFAULT 0 AFTER `discount_amount`"); }
				$pdo->exec("CREATE TABLE IF NOT EXISTS tbl_referral_points (id INT UNSIGNED NOT NULL AUTO_INCREMENT, customer_id INT UNSIGNED NOT NULL, referral_id INT UNSIGNED NULL, points INT NOT NULL DEFAULT 0, reason VARCHAR(255) NOT NULL DEFAULT 'Successful referral', created_at DATETIME NOT NULL, PRIMARY KEY (id), UNIQUE KEY uq_referral_points_referral (referral_id), KEY idx_referral_points_customer (customer_id)) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
				$pointsColumn = $pdo->query("SHOW COLUMNS FROM `tbl_referral_points` LIKE 'points'");
				$pointsDefinition = $pointsColumn ? $pointsColumn->fetch(PDO::FETCH_ASSOC) : null;
				if ($pointsDefinition && stripos((string) $pointsDefinition['Type'], 'unsigned') !== false) { $pdo->exec("ALTER TABLE `tbl_referral_points` MODIFY COLUMN `points` INT NOT NULL DEFAULT 0"); }
				$referralIdColumn = $pdo->query("SHOW COLUMNS FROM `tbl_referral_points` LIKE 'referral_id'");
				$referralIdDefinition = $referralIdColumn ? $referralIdColumn->fetch(PDO::FETCH_ASSOC) : null;
				if ($referralIdDefinition && stripos((string) $referralIdDefinition['Null'], 'NO') !== false) { $pdo->exec("ALTER TABLE `tbl_referral_points` MODIFY COLUMN `referral_id` INT UNSIGNED NULL"); }
		$index = $pdo->query("SHOW INDEX FROM `tbl_referral` WHERE Key_name = 'uq_referral_code'");
		if ($index && $index->rowCount() > 0) {
			$pdo->exec('ALTER TABLE `tbl_referral` DROP INDEX `uq_referral_code`');
		}
		$pdo->exec("INSERT INTO tbl_referral_settings (id, is_active, discount_type, discount_value, minimum_order_amount, terms, updated_at) SELECT 1, 1, 'percent', 10.00, 0.00, 'Referral discount applies to the referred customer''s first eligible booking only.', NOW() WHERE NOT EXISTS (SELECT 1 FROM tbl_referral_settings WHERE id = 1)");
		$ready = true;
	} catch (Throwable $e) {
		$ready = false;
	}
	return $ready;
}

function getReferralSettings() {
	global $pdo;
	if (!ensureReferralTables()) {
		return null;
	}
	$row = $pdo->query('SELECT * FROM tbl_referral_settings WHERE id = 1 LIMIT 1')->fetch(PDO::FETCH_ASSOC);
	return $row ?: null;
}

function getReferralPoints($customerId) {
	global $pdo;
	ensureReferralTables();
	$stmt = $pdo->prepare('SELECT COALESCE(SUM(points), 0) FROM tbl_referral_points WHERE customer_id = ?');
	$stmt->execute([(int) $customerId]);
	return (int) $stmt->fetchColumn();
}

function ensurePromoCodeTable() {
	global $pdo;
	static $ready = null;
	if ($ready !== null) {
		return $ready;
	}

	try {
		$pdo->exec("CREATE TABLE IF NOT EXISTS tbl_promo_code (
			id INT UNSIGNED NOT NULL AUTO_INCREMENT,
			code VARCHAR(50) NOT NULL,
			discount_type ENUM('percent','amount') NOT NULL DEFAULT 'percent',
			discount_value DECIMAL(10,2) NOT NULL DEFAULT 0.00,
			minimum_order_amount DECIMAL(10,2) NOT NULL DEFAULT 0.00,
			is_active TINYINT(1) NOT NULL DEFAULT 1,
			usage_limit INT UNSIGNED NOT NULL DEFAULT 0,
			used_count INT UNSIGNED NOT NULL DEFAULT 0,
			valid_from DATETIME NULL,
			valid_to DATETIME NULL,
			description VARCHAR(255) NULL,
			created_at DATETIME NOT NULL,
			updated_at DATETIME NULL,
			PRIMARY KEY (id),
			UNIQUE KEY uq_promo_code (code)
		) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
		$ready = true;
	} catch (Throwable $e) {
		$ready = false;
	}

	return $ready;
}

function getPromoCodeDetails($code) {
	global $pdo;
	if (!ensurePromoCodeTable()) {
		return null;
	}

	$code = strtoupper(trim((string) $code));
	if ($code === '') {
		return null;
	}

	$stmt = $pdo->prepare('SELECT * FROM tbl_promo_code WHERE code = ? LIMIT 1');
	$stmt->execute([$code]);
	$row = $stmt->fetch(PDO::FETCH_ASSOC);
	if (!$row || (int) ($row['is_active'] ?? 0) !== 1) {
		return null;
	}

	$now = new DateTimeImmutable('now');
	if (!empty($row['valid_from']) && new DateTimeImmutable($row['valid_from']) > $now) {
		return null;
	}
	if (!empty($row['valid_to']) && new DateTimeImmutable($row['valid_to']) < $now) {
		return null;
	}

	$usageLimit = (int) ($row['usage_limit'] ?? 0);
	if ($usageLimit > 0 && (int) ($row['used_count'] ?? 0) >= $usageLimit) {
		return null;
	}

	return $row;
}

function promoDiscountAmount($subtotal, $settings) {
	$subtotal = max(0, (float) $subtotal);
	$minimum = max(0, (float) ($settings['minimum_order_amount'] ?? 0));
	if ($subtotal < $minimum) {
		return 0.0;
	}
	$value = max(0, (float) ($settings['discount_value'] ?? 0));
	$amount = (($settings['discount_type'] ?? 'percent') === 'amount') ? $value : ($subtotal * $value / 100);
	return round(min($subtotal, $amount), 2);
}

function getPromoCodeList() {
	global $pdo;
	ensurePromoCodeTable();
	$statement = $pdo->query('SELECT * FROM tbl_promo_code ORDER BY created_at DESC, id DESC');
	return $statement ? $statement->fetchAll(PDO::FETCH_ASSOC) : [];
}