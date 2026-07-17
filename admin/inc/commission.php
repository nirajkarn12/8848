<?php

function commissionColumnExists($pdo, $table, $column) {
    $statement = $pdo->prepare("SHOW COLUMNS FROM `" . $table . "` LIKE ?");
    $statement->execute(array($column));
    return $statement->rowCount() > 0;
}

function getDefaultCommissionSettings($pdo) {
    $defaults = array(
        'default_staff_commission_type' => 'percent',
        'default_staff_commission_value' => 35.00,
    );

    if (!commissionColumnExists($pdo, 'tbl_settings', 'default_staff_commission_type')) {
        return $defaults;
    }

    $statement = $pdo->query("SELECT default_staff_commission_type, default_staff_commission_value FROM tbl_settings WHERE id = 1 LIMIT 1");
    $row = $statement->fetch(PDO::FETCH_ASSOC);
    if (!$row) {
        return $defaults;
    }

    return array(
        'default_staff_commission_type' => $row['default_staff_commission_type'] ?: 'percent',
        'default_staff_commission_value' => (float)($row['default_staff_commission_value'] ?? 35),
    );
}

function getServiceCommissionRule($pdo, $productId) {
    $productId = (int)$productId;
    if ($productId <= 0) {
        return null;
    }

    if (!commissionColumnExists($pdo, 'tbl_product', 'staff_commission_type')) {
        return null;
    }

    $statement = $pdo->prepare("SELECT staff_commission_type, staff_commission_value FROM tbl_product WHERE p_id = ? LIMIT 1");
    $statement->execute(array($productId));
    $product = $statement->fetch(PDO::FETCH_ASSOC);
    if (!$product) {
        return null;
    }

    $type = $product['staff_commission_type'] ?: 'inherit';
    if ($type === '' || $type === 'inherit') {
        return null;
    }

    return array(
        'commission_type' => $type,
        'commission_value' => (float)$product['staff_commission_value'],
        'source' => 'service',
    );
}

function getPrimaryProductIdFromOrders($orders) {
    if (empty($orders) || !is_array($orders)) {
        return 0;
    }
    $first = $orders[0];
    return (int)($first['product_id'] ?? 0);
}

/**
 * Commission override stack (first match wins):
 * 1. Assignment override (admin custom)
 * 2. Service rule on tbl_product
 * 3. Staff default
 * 4. Global settings
 */
function resolveStaffCommissionRule($pdo, $staffId, $overrideType = '', $overrideValue = null, $productId = 0) {
    if ($overrideType !== '' && $overrideType !== 'inherit' && $overrideValue !== null && $overrideValue !== '') {
        return array(
            'commission_type' => $overrideType,
            'commission_value' => (float)$overrideValue,
            'source' => 'assignment',
        );
    }

    $serviceRule = getServiceCommissionRule($pdo, $productId);
    if ($serviceRule !== null) {
        return $serviceRule;
    }

    if ($staffId) {
        $statement = $pdo->prepare("SELECT default_commission_type, default_commission_value FROM tbl_staff WHERE staff_id = ? LIMIT 1");
        $statement->execute(array($staffId));
        $staff = $statement->fetch(PDO::FETCH_ASSOC);
        if ($staff && (float)$staff['default_commission_value'] > 0) {
            return array(
                'commission_type' => $staff['default_commission_type'] ?: 'percent',
                'commission_value' => (float)$staff['default_commission_value'],
                'source' => 'staff',
            );
        }
    }

    $settings = getDefaultCommissionSettings($pdo);
    return array(
        'commission_type' => $settings['default_staff_commission_type'],
        'commission_value' => (float)$settings['default_staff_commission_value'],
        'source' => 'global',
    );
}

function calculateCommissionAmount($baseAmount, $commissionType, $commissionValue) {
    $baseAmount = (float)$baseAmount;
    $commissionValue = (float)$commissionValue;

    if ($commissionType === 'fixed' || $commissionType === 'custom') {
        return round($commissionValue, 2);
    }

    return round($baseAmount * $commissionValue / 100, 2);
}

function formatCommissionRuleLabel($rule) {
    $type = $rule['commission_type'] ?? 'percent';
    $value = (float)($rule['commission_value'] ?? 0);
    $source = $rule['source'] ?? 'global';

    if ($type === 'fixed' || $type === 'custom') {
        $valueLabel = 'Rs. ' . number_format($value, 2);
    } else {
        $valueLabel = number_format($value, 2) . '%';
    }

    $sourceLabels = array(
        'assignment' => 'Owner override',
        'service' => 'Service rule',
        'staff' => 'Staff default',
        'global' => 'Global default',
    );

    return $valueLabel . ' (' . ($sourceLabels[$source] ?? $source) . ')';
}

function getBookingBaseAmount($payment, $orders) {
    $grandTotal = (float)($payment['grand_total'] ?? 0);
    if ($grandTotal > 0) {
        return $grandTotal;
    }

    $subtotal = (float)($payment['subtotal'] ?? 0);
    if ($subtotal > 0) {
        return $subtotal;
    }

    $sum = 0.0;
    foreach ($orders as $order) {
        $lineTotal = (float)($order['line_total'] ?? 0);
        if ($lineTotal > 0) {
            $sum += $lineTotal;
        } else {
            $sum += ((float)$order['unit_price'] * (float)$order['quantity']);
        }
    }

    return $sum > 0 ? $sum : (float)($payment['paid_amount'] ?? 0);
}

function getActiveAssignmentForPayment($pdo, $paymentId) {
    $statement = $pdo->prepare("
        SELECT a.*, s.full_name AS staff_name, s.phone AS staff_phone, s.email AS staff_email
        FROM tbl_booking_assignment a
        LEFT JOIN tbl_staff s ON s.staff_id = a.staff_id
        WHERE a.payment_id = ?
          AND a.job_status != 'Cancelled'
        ORDER BY a.assignment_id DESC
        LIMIT 1
    ");
    $statement->execute(array($paymentId));
    return $statement->fetch(PDO::FETCH_ASSOC);
}

function getAssignmentsForPayment($pdo, $paymentId) {
    $statement = $pdo->prepare("
        SELECT a.*, s.full_name AS staff_name, s.phone AS staff_phone, s.email AS staff_email
        FROM tbl_booking_assignment a
        LEFT JOIN tbl_staff s ON s.staff_id = a.staff_id
        WHERE a.payment_id = ?
          AND a.job_status != 'Cancelled'
        ORDER BY a.assignment_id ASC
    ");
    $statement->execute(array($paymentId));
    return $statement->fetchAll(PDO::FETCH_ASSOC);
}

function staffAlreadyAssignedToPayment($pdo, $paymentId, $staffId) {
    $statement = $pdo->prepare("
        SELECT assignment_id
        FROM tbl_booking_assignment
        WHERE payment_id = ? AND staff_id = ? AND job_status != 'Cancelled'
        LIMIT 1
    ");
    $statement->execute(array($paymentId, $staffId));
    return (bool)$statement->fetch(PDO::FETCH_ASSOC);
}

function applyCommissionShare($commissionAmount, $sharePercent) {
    $sharePercent = (float)$sharePercent;
    if ($sharePercent <= 0) {
        $sharePercent = 100;
    }
    if ($sharePercent > 100) {
        $sharePercent = 100;
    }
    return round(((float)$commissionAmount) * $sharePercent / 100, 2);
}

/**
 * Round-robin next active staff, optionally filtered by preferred date availability.
 */
function getNextStaffForAutoAssign($pdo, $preferredDate = '') {
    $dayOfWeek = null;
    if ($preferredDate !== '') {
        $ts = strtotime($preferredDate);
        if ($ts !== false) {
            $dayOfWeek = (int)date('w', $ts);
        }
    }

    $lastId = 0;
    try {
        $lastId = (int)$pdo->query("SELECT last_staff_id FROM tbl_staff_auto_assign WHERE id = 1")->fetchColumn();
    } catch (PDOException $e) {
        $lastId = 0;
    }

    $sql = "SELECT s.staff_id, s.full_name, s.phone, s.email, s.default_commission_type, s.default_commission_value
            FROM tbl_staff s
            WHERE s.status = 'Active'";
    $params = array();

    if ($dayOfWeek !== null) {
        try {
            $hasAvail = $pdo->query("SHOW TABLES LIKE 'tbl_staff_availability'")->rowCount() > 0;
        } catch (PDOException $e) {
            $hasAvail = false;
        }
        if ($hasAvail) {
            // Prefer staff with matching availability; if none configured for anyone, fall back to all
            $check = $pdo->prepare("SELECT COUNT(*) FROM tbl_staff_availability WHERE day_of_week = ? AND is_available = 1");
            $check->execute(array($dayOfWeek));
            if ((int)$check->fetchColumn() > 0) {
                $sql .= " AND EXISTS (
                    SELECT 1 FROM tbl_staff_availability av
                    WHERE av.staff_id = s.staff_id AND av.day_of_week = ? AND av.is_available = 1
                )";
                $params[] = $dayOfWeek;
            }
        }
    }

    $sql .= " ORDER BY s.staff_id ASC";
    $statement = $pdo->prepare($sql);
    $statement->execute($params);
    $staffList = $statement->fetchAll(PDO::FETCH_ASSOC);

    if (!$staffList) {
        return null;
    }

    $chosen = null;
    foreach ($staffList as $staff) {
        if ((int)$staff['staff_id'] > $lastId) {
            $chosen = $staff;
            break;
        }
    }
    if ($chosen === null) {
        $chosen = $staffList[0];
    }

    try {
        $upd = $pdo->prepare("UPDATE tbl_staff_auto_assign SET last_staff_id = ? WHERE id = 1");
        $upd->execute(array((int)$chosen['staff_id']));
    } catch (PDOException $e) {
        // optional table
    }

    return $chosen;
}

function sendStaffAssignmentEmail($pdo, $staff, $assignment, $payment) {
    if (empty($staff['email'])) {
        return false;
    }

    require_once __DIR__ . '/../../PHPMailer/src/PHPMailer.php';
    require_once __DIR__ . '/../../PHPMailer/src/SMTP.php';
    require_once __DIR__ . '/../../PHPMailer/src/Exception.php';

    $mail = new PHPMailer\PHPMailer\PHPMailer(true);
    $schedule = trim(($assignment['preferred_date'] ?? '') . ' ' . ($assignment['preferred_time'] ?? ''));
    if ($schedule === '') {
        $schedule = 'Not set';
    }

    $body = '
<html><body>
<p>Hello ' . htmlspecialchars($staff['full_name']) . ',</p>
<p>You have been assigned a new job.</p>
<ul>
<li><strong>Service:</strong> ' . htmlspecialchars($assignment['service_name']) . '</li>
<li><strong>Client:</strong> ' . htmlspecialchars($assignment['client_name']) . '</li>
<li><strong>Phone:</strong> ' . htmlspecialchars($assignment['client_phone']) . '</li>
<li><strong>Address:</strong> ' . nl2br(htmlspecialchars($assignment['service_address'])) . '</li>
<li><strong>Schedule:</strong> ' . htmlspecialchars($schedule) . '</li>
<li><strong>Estimated commission:</strong> Rs. ' . number_format((float)$assignment['commission_amount'], 2) . '</li>
</ul>
<p>Log in to the staff portal to view details and update job status.</p>
</body></html>
';

    $mail->isSMTP();
    $mail->Host = SMTP_HOST;
    $mail->SMTPAuth = true;
    $mail->Username = SMTP_USER;
    $mail->Password = SMTP_PASS;
    $mail->SMTPSecure = PHPMailer\PHPMailer\PHPMailer::ENCRYPTION_SMTPS;
    $mail->Port = SMTP_PORT;
    $mail->setFrom(SMTP_FROM_EMAIL, SMTP_FROM_NAME);
    $mail->addAddress($staff['email'], $staff['full_name']);
    $mail->isHTML(true);
    $mail->Subject = 'New job assigned — ' . ($assignment['service_name'] ?: $payment['payment_id']);
    $mail->Body = $body;
    $mail->send();
    return true;
}
