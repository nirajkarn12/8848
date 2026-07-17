CREATE TABLE IF NOT EXISTS `tbl_testimonial` (
  `id` int NOT NULL AUTO_INCREMENT,
  `name` varchar(100) NOT NULL,
  `designation` varchar(150) NOT NULL DEFAULT '',
  `company` varchar(150) NOT NULL DEFAULT '',
  `review` text NOT NULL,
  `rating` tinyint NOT NULL DEFAULT 5,
  `photo` varchar(255) NOT NULL DEFAULT '',
  `status` varchar(20) NOT NULL DEFAULT 'Active',
  `sort_order` int NOT NULL DEFAULT 0,
  `created_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
