-- Staff website / team display fields
ALTER TABLE `tbl_staff` ADD COLUMN IF NOT EXISTS `designation` varchar(150) NOT NULL DEFAULT '';
ALTER TABLE `tbl_staff` ADD COLUMN IF NOT EXISTS `rating` tinyint NOT NULL DEFAULT 5;
ALTER TABLE `tbl_staff` ADD COLUMN IF NOT EXISTS `facebook_url` varchar(255) NOT NULL DEFAULT '';
ALTER TABLE `tbl_staff` ADD COLUMN IF NOT EXISTS `instagram_url` varchar(255) NOT NULL DEFAULT '';
ALTER TABLE `tbl_staff` ADD COLUMN IF NOT EXISTS `show_on_website` tinyint NOT NULL DEFAULT 1;
