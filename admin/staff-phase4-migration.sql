-- Phase 4: Advanced staff features
-- Run via admin/run-staff-phase4-migration.php

ALTER TABLE `tbl_booking_assignment`
  ADD COLUMN IF NOT EXISTS `arrived_at` datetime DEFAULT NULL,
  ADD COLUMN IF NOT EXISTS `checkin_lat` decimal(10,7) DEFAULT NULL,
  ADD COLUMN IF NOT EXISTS `checkin_lng` decimal(10,7) DEFAULT NULL,
  ADD COLUMN IF NOT EXISTS `commission_share_percent` decimal(5,2) NOT NULL DEFAULT '100.00';

CREATE TABLE IF NOT EXISTS `tbl_staff_availability` (
  `availability_id` int NOT NULL AUTO_INCREMENT,
  `staff_id` int NOT NULL,
  `day_of_week` tinyint NOT NULL COMMENT '0=Sun .. 6=Sat',
  `start_time` time NOT NULL DEFAULT '08:00:00',
  `end_time` time NOT NULL DEFAULT '18:00:00',
  `is_available` tinyint NOT NULL DEFAULT 1,
  PRIMARY KEY (`availability_id`),
  KEY `idx_staff_day` (`staff_id`, `day_of_week`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS `tbl_staff_auto_assign` (
  `id` int NOT NULL DEFAULT 1,
  `last_staff_id` int NOT NULL DEFAULT 0,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

INSERT IGNORE INTO `tbl_staff_auto_assign` (`id`, `last_staff_id`) VALUES (1, 0);
