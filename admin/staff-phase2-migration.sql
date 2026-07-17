-- Phase 2: Commission rules & job workflow
-- Run via admin/run-staff-phase2-migration.php or import in phpMyAdmin

ALTER TABLE `tbl_product`
  ADD COLUMN IF NOT EXISTS `staff_commission_type` varchar(20) NOT NULL DEFAULT 'inherit',
  ADD COLUMN IF NOT EXISTS `staff_commission_value` decimal(10,2) NOT NULL DEFAULT '0.00';

ALTER TABLE `tbl_booking_assignment`
  ADD COLUMN IF NOT EXISTS `approved_at` datetime DEFAULT NULL,
  ADD COLUMN IF NOT EXISTS `paid_at` datetime DEFAULT NULL;
