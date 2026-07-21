-- Service location coordinates (OpenStreetMap)
ALTER TABLE `tbl_payment` ADD COLUMN IF NOT EXISTS `service_lat` DECIMAL(10,7) NULL;
ALTER TABLE `tbl_payment` ADD COLUMN IF NOT EXISTS `service_lng` DECIMAL(10,7) NULL;

ALTER TABLE `tbl_booking_assignment` ADD COLUMN IF NOT EXISTS `service_lat` DECIMAL(10,7) NULL;
ALTER TABLE `tbl_booking_assignment` ADD COLUMN IF NOT EXISTS `service_lng` DECIMAL(10,7) NULL;
