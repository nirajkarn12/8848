-- Phase 1: Staff assignment system
-- Run once via admin/run-staff-migration.php or import in phpMyAdmin

CREATE TABLE IF NOT EXISTS `tbl_staff` (
  `staff_id` int NOT NULL AUTO_INCREMENT,
  `full_name` varchar(100) NOT NULL,
  `email` varchar(255) NOT NULL,
  `phone` varchar(50) NOT NULL DEFAULT '',
  `password` varchar(255) NOT NULL,
  `photo` varchar(255) NOT NULL DEFAULT 'user-1.jpg',
  `address` text,
  `default_commission_type` varchar(20) NOT NULL DEFAULT 'percent',
  `default_commission_value` decimal(10,2) NOT NULL DEFAULT '0.00',
  `status` varchar(20) NOT NULL DEFAULT 'Active',
  `created_at` datetime DEFAULT NULL,
  PRIMARY KEY (`staff_id`),
  UNIQUE KEY `email` (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS `tbl_booking_assignment` (
  `assignment_id` int NOT NULL AUTO_INCREMENT,
  `payment_id` varchar(255) NOT NULL,
  `payment_row_id` int NOT NULL DEFAULT 0,
  `staff_id` int NOT NULL,
  `assigned_by` int NOT NULL DEFAULT 0,
  `assigned_at` datetime DEFAULT NULL,
  `job_status` varchar(30) NOT NULL DEFAULT 'Assigned',
  `service_address` text,
  `preferred_date` date DEFAULT NULL,
  `preferred_time` varchar(30) DEFAULT NULL,
  `client_name` varchar(255) NOT NULL DEFAULT '',
  `client_phone` varchar(50) NOT NULL DEFAULT '',
  `client_email` varchar(255) NOT NULL DEFAULT '',
  `service_name` varchar(255) NOT NULL DEFAULT '',
  `service_amount` decimal(10,2) NOT NULL DEFAULT '0.00',
  `commission_type` varchar(20) NOT NULL DEFAULT 'percent',
  `commission_value` decimal(10,2) NOT NULL DEFAULT '0.00',
  `commission_amount` decimal(10,2) NOT NULL DEFAULT '0.00',
  `commission_status` varchar(20) NOT NULL DEFAULT 'pending',
  `staff_notes` text,
  `admin_notes` text,
  `completed_at` datetime DEFAULT NULL,
  PRIMARY KEY (`assignment_id`),
  KEY `idx_payment_id` (`payment_id`),
  KEY `idx_staff_id` (`staff_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

ALTER TABLE `tbl_payment` ADD COLUMN IF NOT EXISTS `service_address` text NULL;
ALTER TABLE `tbl_payment` ADD COLUMN IF NOT EXISTS `preferred_date` date NULL;
ALTER TABLE `tbl_payment` ADD COLUMN IF NOT EXISTS `preferred_time` varchar(30) NULL;
ALTER TABLE `tbl_payment` ADD COLUMN IF NOT EXISTS `booking_status` varchar(25) DEFAULT 'Pending';
ALTER TABLE `tbl_payment` ADD COLUMN IF NOT EXISTS `assignment_status` varchar(25) DEFAULT 'Unassigned';

ALTER TABLE `tbl_settings` ADD COLUMN IF NOT EXISTS `default_staff_commission_type` varchar(20) DEFAULT 'percent';
ALTER TABLE `tbl_settings` ADD COLUMN IF NOT EXISTS `default_staff_commission_value` decimal(10,2) DEFAULT '35.00';
