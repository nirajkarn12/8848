<?php
function get_ext($pdo,$fname)
{

	$up_filename=$_FILES[$fname]["name"];
	$file_basename = substr($up_filename, 0, strripos($up_filename, '.')); // strip extention
	$file_ext = substr($up_filename, strripos($up_filename, '.')); // strip name
	return $file_ext;
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