SELECT
    'tbl_referral' AS table_name,
    id,
    'referral' AS record_type,
    created_at AS recent_date
FROM tbl_referral
WHERE created_at >= NOW() - INTERVAL 2 WEEK

UNION ALL

SELECT
    'tbl_referral_points',
    id,
    'points',
    created_at
FROM tbl_referral_points
WHERE created_at >= NOW() - INTERVAL 2 WEEK

UNION ALL

SELECT
    'tbl_payment',
    id,
    'payment',
    payment_date
FROM tbl_payment
WHERE payment_date >= NOW() - INTERVAL 2 WEEK

UNION ALL

SELECT
    'tbl_booking_assignment',
    assignment_id,
    'assignment',
    assigned_at
FROM tbl_booking_assignment
WHERE assigned_at >= NOW() - INTERVAL 2 WEEK

UNION ALL

SELECT
    'tbl_contact_inquiry',
    id,
    'inquiry',
    created_at
FROM tbl_contact_inquiry
WHERE created_at >= NOW() - INTERVAL 2 WEEK

ORDER BY recent_date DESC;