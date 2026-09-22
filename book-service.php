<?php
require_once __DIR__ . '/inc/functions.php';

$pageTitle = t('book_now');

$preselect = (int)($_GET['service'] ?? 0);

/*
|--------------------------------------------------------------------------
| Load Services
|--------------------------------------------------------------------------
*/
$services = $pdo->query(
    'SELECT 
        p_id,
        p_name,
        p_short_description,
        p_featured_photo,
        p_qty,
        ecat_id
     FROM tbl_product
     WHERE p_is_active = 1
     ORDER BY p_is_featured DESC, p_name ASC'
)->fetchAll();


/*
|--------------------------------------------------------------------------
| Load Website Settings
|--------------------------------------------------------------------------
|
| These settings are managed from the admin panel:
|
| contact_address
| contact_map_iframe
|
*/
$settings = $pdo->query(
    'SELECT * FROM tbl_settings LIMIT 1'
)->fetch();

$serviceRegions = $pdo->query(
    'SELECT country_id, country_name, postal_code FROM tbl_country ORDER BY country_name ASC'
)->fetchAll(PDO::FETCH_ASSOC);


/*
|--------------------------------------------------------------------------
| Default Address
|--------------------------------------------------------------------------
|
| Uses the same address shown on contact.php.
|
*/
$defaultAddress = trim(
    $settings['contact_address'] ?? ''
);


/*
|--------------------------------------------------------------------------
| Default Map Coordinates
|--------------------------------------------------------------------------
|
| Extract latitude and longitude from the Google Maps iframe stored in:
|
| tbl_settings.contact_map_iframe
|
| Google Maps embed URLs commonly contain:
|
| !2dLONGITUDE
| !3dLATITUDE
|
| Example:
|
| !2d174.75669337373034
| !3d-36.852346679702755
|
|--------------------------------------------------------------------------
*/
$defaultLat = null;
$defaultLng = null;

$mapIframe = trim(
    $settings['contact_map_iframe'] ?? ''
);

if ($mapIframe !== '') {

    /*
     * Extract longitude and latitude from the iframe.
     */
    if (
        preg_match(
            '/!2d(-?\d+(?:\.\d+)?)!3d(-?\d+(?:\.\d+)?)/',
            $mapIframe,
            $matches
        )
    ) {

        /*
         * Google Maps format:
         *
         * !2d = longitude
         * !3d = latitude
         */

        $defaultLng = normalizeMapCoordinate(
            $matches[1],
            -180,
            180
        );

        $defaultLat = normalizeMapCoordinate(
            $matches[2],
            -90,
            90
        );
    }
}


/*
|--------------------------------------------------------------------------
| Fallback Location
|--------------------------------------------------------------------------
|
| If the iframe does not contain valid coordinates,
| use Auckland as the fallback location.
|
| Latitude:  27.7172
| Longitude: 85.3240
|
*/
if ($defaultLat === null) {
    $defaultLat = 27.7172;
}

if ($defaultLng === null) {
    $defaultLng = 85.3240;
}


/*
|--------------------------------------------------------------------------
| Handle Booking Form Submission
|--------------------------------------------------------------------------
*/
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    /*
     * CSRF validation
     */
    if (!verifyCsrf($_POST['csrf_token'] ?? '')) {

        setFlash(
            'danger',
            loadLang('invalid_request')
        );

        header('Location: book-service.php');
        exit;
    }


    /*
     * Selected service
     */
    $serviceId = (int)(
        $_POST['service_id'] ?? 0
    );
    $serviceRegion = trim((string)($_POST['service_region'] ?? ''));
    $servicePostalCode = trim((string)($_POST['service_postal_code'] ?? ''));

    /*
     * Service map coordinates
     */
    $serviceLat = normalizeMapCoordinate(
        $_POST['service_lat'] ?? null,
        -90,
        90
    );

    $serviceLng = normalizeMapCoordinate(
        $_POST['service_lng'] ?? null,
        -180,
        180
    );


    /*
     * Require map pin
     */
    if (
        $serviceLat === null ||
        $serviceLng === null
    ) {

        setFlash(
            'danger',
            loadLang('map_pin_required')
        );

        header('Location: book-service.php');
        exit;
    }


    /*
     * Validate selected service
     */
    if ($serviceId > 0) {

        $stmt = $pdo->prepare(
            'SELECT
                p_id,
                p_name,
                p_featured_photo
             FROM tbl_product
             WHERE p_id = ?
             AND p_is_active = 1
             LIMIT 1'
        );

        $stmt->execute([
            $serviceId
        ]);

        $product = $stmt->fetch();


        /*
         * Valid product found
         */
        if ($product) {

            /*
             * Create cart if it doesn't exist.
             */
            if (!isset($_SESSION['cart'])) {
                $_SESSION['cart'] = [];
            }


            /*
             * Product ID
             */
            $id = (int)$product['p_id'];


            /*
             * Add service to cart.
             */
            $_SESSION['cart'][$id] = [
                'product_id' => $id,

                'product_name' => $product['p_name'],

                'photo' => $product['p_featured_photo'],

                'quantity' => 1,

                'notes' => trim(
                    $_POST['notes'] ??
                    ($_SESSION['cart'][$id]['notes'] ?? '')
                ),
            ];


            /*
             * Save booking information.
             */
            $_SESSION['booking_pref'] = [

                /*
                 * Use customer-entered address.
                 *
                 * If empty, use the admin-managed
                 * contact address.
                 */
                'service_address' => trim(
                    $_POST['service_address']
                    ?? $defaultAddress
                ),

                'service_region' => $serviceRegion,
                'service_postal_code' => $servicePostalCode,

                /*
                 * Customer-selected map location.
                 */
                'service_lat' => $serviceLat,

                'service_lng' => $serviceLng,


                /*
                 * Preferred date.
                 */
                'preferred_date' => trim(
                    $_POST['preferred_date'] ?? ''
                ),


                /*
                 * Preferred time.
                 */
                'preferred_time' => trim(
                    $_POST['preferred_time'] ?? ''
                ),


                /*
                 * Customer name.
                 */
                'customer_name' => trim(
                    $_POST['customer_name'] ?? ''
                ),


                /*
                 * Phone.
                 */
                'phone' => trim(
                    $_POST['phone'] ?? ''
                ),


                /*
                 * Email.
                 */
                'email' => trim(
                    $_POST['email'] ?? ''
                ),
            ];


            /*
             * Success message.
             */
            setFlash(
                'success',
                loadLang('service_added_complete_booking')
            );


            /*
             * Continue to checkout.
             */
            header('Location: checkout.php');
            exit;
        }
    }


    /*
     * Invalid service.
     */
    setFlash(
        'danger',
        loadLang('choose_valid_service')
    );

    header('Location: book-service.php');
    exit;
}


/*
|--------------------------------------------------------------------------
| Existing Booking Preferences
|--------------------------------------------------------------------------
*/
$pref = $_SESSION['booking_pref'] ?? [];


/*
|--------------------------------------------------------------------------
| Default Address
|--------------------------------------------------------------------------
|
| If there is no previously saved booking address,
| use the address from tbl_settings.
|
*/
if (
    !isset($pref['service_address']) ||
    trim($pref['service_address']) === ''
) {

    $pref['service_address'] = $defaultAddress;
}

if (!isset($pref['service_region'])) {
    $pref['service_region'] = '';
}
if (!isset($pref['service_postal_code'])) {
    $pref['service_postal_code'] = '';
}


/*
|--------------------------------------------------------------------------
| Header
|--------------------------------------------------------------------------
*/
include __DIR__ . '/inc/header.php';


/*
|--------------------------------------------------------------------------
| Breadcrumbs
|--------------------------------------------------------------------------
*/
$breadcrumbs = [
    [
        'label' => t('home'),
        'url' => BASE_URL
    ],
    [
        'label' => t('book_now'),
        'url' => ''
    ]
];

echo renderBreadcrumbs($breadcrumbs);

echo renderFlash();

?>

<div class="book-page-head mb-4">

    <div class="section-kicker">
        <?php echo t('book_service'); ?>
    </div>

    <h1 class="section-title mb-2">
        <?php echo t('book_now'); ?>
    </h1>

    <p class="text-muted mb-0">
        <?php echo t('shop_collection_subtitle'); ?>
    </p>

</div>


<div class="row g-4 align-items-start book-page-columns">


    <!-- =========================================================
         BOOKING FORM
    ========================================================== -->

    <div class="col-lg-7">

        <div class="card card-hover p-4 booking-panel">

            <form method="post" class="row g-3">


                <!-- CSRF TOKEN -->
                <input
                    type="hidden"
                    name="csrf_token"
                    value="<?php echo e(csrfToken()); ?>"
                >


                <!-- =================================================
                     SERVICE
                ================================================== -->

                <div class="col-12">

                    <label class="form-label">
                        <?php echo t('product'); ?>
                    </label>

                    <select
                        class="form-select"
                        name="service_id"
                        required
                    >

                        <option value="">
                            <?php echo t('select_service'); ?>
                        </option>

                        <?php foreach ($services as $svc) { ?>

                            <option
                                value="<?php echo (int)$svc['p_id']; ?>"
                                <?php
                                echo $preselect === (int)$svc['p_id']
                                    ? 'selected'
                                    : '';
                                ?>
                            >
                                <?php echo e($svc['p_name']); ?>
                            </option>

                        <?php } ?>

                    </select>

                </div>


                <!-- =================================================
                     CUSTOMER NAME
                ================================================== -->

                <div class="col-md-6">

                    <label class="form-label">
                        <?php echo t('customer_name'); ?>
                    </label>

                    <input
                        class="form-control"
                        name="customer_name"
                        value="<?php echo e(
                            $pref['customer_name'] ?? ''
                        ); ?>"
                    >

                </div>


                <!-- =================================================
                     PHONE
                ================================================== -->

                <div class="col-md-6">

                    <label class="form-label">
                        <?php echo t('phone'); ?>
                    </label>

                    <input
                        class="form-control"
                        name="phone"
                        value="<?php echo e(
                            $pref['phone'] ?? ''
                        ); ?>"
                    >

                </div>


                <!-- =================================================
                     EMAIL
                ================================================== -->

                <div class="col-12">

                    <label class="form-label">
                        <?php echo t('email_address'); ?>
                    </label>

                    <input
                        class="form-control"
                        type="email"
                        name="email"
                        value="<?php echo e(
                            $pref['email'] ?? ''
                        ); ?>"
                    >

                </div>


                <!-- =================================================
                     SERVICE REGION
                ================================================== -->

                <div class="col-md-6">

                    <label class="form-label">
                        Region
                    </label>

                    <select class="form-select" name="service_region" id="service_region">
                        <option value="">Select region</option>
                        <?php foreach ($serviceRegions as $region) { ?>
                            <option value="<?php echo e($region['country_name']); ?>" data-postal="<?php echo e($region['postal_code'] ?? ''); ?>" <?php echo (trim((string)($pref['service_region'] ?? '')) === trim((string)$region['country_name'])) ? 'selected' : ''; ?>>
                                <?php echo e($region['country_name']); ?>
                            </option>
                        <?php } ?>
                    </select>

                </div>

                <!-- =================================================
                     POSTAL CODE
                ================================================== -->

                <div class="col-md-6">

                    <label class="form-label">
                        Postal code
                    </label>

                    <input
                        class="form-control"
                        type="text"
                        name="service_postal_code"
                        id="service_postal_code"
                        value="<?php echo e($pref['service_postal_code'] ?? ''); ?>"
                        placeholder="e.g. 1010"
                    >

                </div>

                <!-- =================================================
                     SERVICE ADDRESS
                ================================================== -->

                <div class="col-12">

                    <label class="form-label">
                        <?php echo t('service_address'); ?>
                    </label>

                    <textarea
                        class="form-control"
                        id="service_address"
                        name="service_address"
                        rows="3"
                        required
                    ><?php echo e(
                        $pref['service_address']
                        ?? $defaultAddress
                    ); ?></textarea>

                </div>

                <script>
                    (function () {
                        var regionSelect = document.getElementById('service_region');
                        var postalInput = document.getElementById('service_postal_code');
                        if (!regionSelect || !postalInput) return;
                        function applyPostalCode() {
                            var selected = regionSelect.options[regionSelect.selectedIndex];
                            if (!selected) return;
                            var postal = selected.getAttribute('data-postal') || '';
                            if (postal && (!postalInput.value || postalInput.value === '')) {
                                postalInput.value = postal;
                            }
                        }
                        regionSelect.addEventListener('change', applyPostalCode);
                        applyPostalCode();
                    })();
                </script>

                <!-- =================================================
                     MAP
                ================================================== -->

                <div class="col-12">

                    <label class="form-label fw-semibold">
                        <?php echo t('map_pin_location'); ?>
                    </label>

                    <?php

                    /*
                     * OpenStreetMap default location:
                     *
                     * 1. Existing booking coordinates
                     * 2. Coordinates from admin contact_map_iframe
                     * 3. Auckland fallback
                     */

                    echo renderServiceLocationPicker([

                        /*
                         * Connect map address to address textarea.
                         */
                        'address_input' => '#service_address',


                        /*
                         * Latitude.
                         */
                        'lat' => !empty(
                            $pref['service_lat']
                        )
                            ? $pref['service_lat']
                            : $defaultLat,


                        /*
                         * Longitude.
                         */
                        'lng' => !empty(
                            $pref['service_lng']
                        )
                            ? $pref['service_lng']
                            : $defaultLng,


                        /*
                         * Unique map ID.
                         */
                        'id' => 'bookServiceMapPicker',


                        /*
                         * Require map location.
                         */
                        'required' => true,

                    ]);

                    ?>

                </div>


                <!-- =================================================
                     PREFERRED DATE
                ================================================== -->

                <div class="col-md-6">

                    <label class="form-label">
                        <?php echo t('preferred_date'); ?>
                    </label>

                    <input
                        class="form-control"
                        type="date"
                        name="preferred_date"
                        min="<?php echo date('Y-m-d'); ?>"
                        value="<?php echo e(
                            $pref['preferred_date'] ?? ''
                        ); ?>"
                    >

                </div>


                <!-- =================================================
                     PREFERRED TIME
                ================================================== -->

                <div class="col-md-6">

                    <label class="form-label">
                        <?php echo t('preferred_time'); ?>
                    </label>

                    <input
                        class="form-control"
                        type="time"
                        name="preferred_time"
                        value="<?php echo e(
                            $pref['preferred_time'] ?? ''
                        ); ?>"
                    >

                </div>


                <!-- =================================================
                     NOTES
                ================================================== -->

                <div class="col-12">

                    <label class="form-label">
                        <?php echo t('notes'); ?>
                    </label>

                    <textarea
                        class="form-control"
                        name="notes"
                        rows="2"
                        placeholder="<?php echo t('notes_placeholder'); ?>"
                    ></textarea>

                </div>


                <!-- =================================================
                     SUBMIT
                ================================================== -->

                <div class="col-12">

                    <button
                        type="submit"
                        class="btn btn-dark btn-lg"
                    >
                        <?php echo t('proceed_booking'); ?>
                    </button>

                </div>

            </form>

        </div>

    </div>


    <!-- =========================================================
         SIDEBAR
    ========================================================== -->

    <div class="col-lg-5">

        <div class="book-page-sidebar">


            <!-- =================================================
                 CTA
            ================================================== -->

            <div class="cta-band book-side-cta">

                <div class="cta-band-inner">

                    <h2>
                        <?php echo t('cta_ready_title'); ?>
                    </h2>

                    <p>
                        <?php echo t('cta_ready_text'); ?>
                    </p>

                    <a
                        href="products.php"
                        class="btn btn-light btn-sm"
                    >
                        <?php echo t('shop_collection'); ?>
                    </a>

                </div>

            </div>


            <!-- =================================================
                 BOOKING STEPS
            ================================================== -->

            <div class="how-grid book-side-steps">


                <!-- STEP 01 -->
                <article class="how-card">

                    <div class="how-step-num">
                        01
                    </div>

                    <h3>
                        <?php echo t('how_step_1_title'); ?>
                    </h3>

                    <p>
                        <?php echo t('how_step_1_text'); ?>
                    </p>

                </article>


                <!-- STEP 02 -->
                <article class="how-card">

                    <div class="how-step-num">
                        02
                    </div>

                    <h3>
                        <?php echo t('how_step_2_title'); ?>
                    </h3>

                    <p>
                        <?php echo t('how_step_2_text'); ?>
                    </p>

                </article>


                <!-- STEP 03 -->
                <article class="how-card">

                    <div class="how-step-num">
                        03
                    </div>

                    <h3>
                        <?php echo t('how_step_3_title'); ?>
                    </h3>

                    <p>
                        <?php echo t('how_step_3_text'); ?>
                    </p>

                </article>


            </div>

        </div>

    </div>

</div>


<?php

/*
|--------------------------------------------------------------------------
| Service Location Assets
|--------------------------------------------------------------------------
*/
echo serviceLocationAssets();

?>


<?php

/*
|--------------------------------------------------------------------------
| Footer
|--------------------------------------------------------------------------
*/
include __DIR__ . '/inc/footer.php';

?>