-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1:3306
-- Generation Time: Jul 21, 2026 at 07:31 AM
-- Server version: 9.1.0
-- PHP Version: 8.2.10

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `resinnep_ecommerceweb`
--

-- --------------------------------------------------------

--
-- Table structure for table `tbl_booking_assignment`
--

DROP TABLE IF EXISTS `tbl_booking_assignment`;
CREATE TABLE IF NOT EXISTS `tbl_booking_assignment` (
  `assignment_id` int NOT NULL AUTO_INCREMENT,
  `payment_id` varchar(255) NOT NULL,
  `payment_row_id` int NOT NULL DEFAULT '0',
  `staff_id` int NOT NULL,
  `assigned_by` int NOT NULL DEFAULT '0',
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
  `approved_at` datetime DEFAULT NULL,
  `paid_at` datetime DEFAULT NULL,
  `arrived_at` datetime DEFAULT NULL,
  `checkin_lat` decimal(10,7) DEFAULT NULL,
  `checkin_lng` decimal(10,7) DEFAULT NULL,
  `commission_share_percent` decimal(5,2) NOT NULL DEFAULT '100.00',
  `service_lat` decimal(10,7) DEFAULT NULL,
  `service_lng` decimal(10,7) DEFAULT NULL,
  PRIMARY KEY (`assignment_id`),
  KEY `idx_payment_id` (`payment_id`),
  KEY `idx_staff_id` (`staff_id`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `tbl_booking_assignment`
--

INSERT INTO `tbl_booking_assignment` (`assignment_id`, `payment_id`, `payment_row_id`, `staff_id`, `assigned_by`, `assigned_at`, `job_status`, `service_address`, `preferred_date`, `preferred_time`, `client_name`, `client_phone`, `client_email`, `service_name`, `service_amount`, `commission_type`, `commission_value`, `commission_amount`, `commission_status`, `staff_notes`, `admin_notes`, `completed_at`, `approved_at`, `paid_at`, `arrived_at`, `checkin_lat`, `checkin_lng`, `commission_share_percent`, `service_lat`, `service_lng`) VALUES
(1, 'ORD-20260703172557', 90, 1, 1, '2026-07-17 15:05:45', 'Completed', 'nnbnbn', '2026-07-22', '10', 'ram', '9999999999', 'admin@mail.com', 'Dress Mould ', 0.00, 'fixed', 100.00, 100.00, 'approved', '', 'fgfgfgfgfgf', '2026-07-17 15:12:44', '2026-07-17 15:12:44', NULL, '2026-07-17 15:06:27', NULL, NULL, 100.00, NULL, NULL),
(2, 'ORD-20260717165001', 91, 1, 1, '2026-07-17 16:50:19', 'Assigned', 'Manamaiju\r\nkathmandu', NULL, '', 'hala', '9999999999', 'pandey@gmail.com', 'Dress Mould ', 0.00, 'percent', 35.00, 0.00, 'pending', NULL, '', NULL, NULL, NULL, NULL, NULL, NULL, 100.00, NULL, NULL),
(3, 'ORD-20260721121404', 92, 1, 1, '2026-07-21 12:15:11', 'Assigned', 'New Millenium School, Barahi 1st Cross, Kupondole, Lalitpur-10, Lalitpur, Lalitpur Metropolitan City, Lalitpur, Bagamati Province, 00779, Nepal', '2026-07-25', '23:46', 'ram', '9999999999', 'pandey@gmail.com', 'Rose container wuth lid ', 0.00, 'percent', 35.00, 0.00, 'pending', NULL, '', NULL, NULL, NULL, NULL, NULL, NULL, 100.00, 27.6855000, 85.3229000);

-- --------------------------------------------------------

--
-- Table structure for table `tbl_client`
--

DROP TABLE IF EXISTS `tbl_client`;
CREATE TABLE IF NOT EXISTS `tbl_client` (
  `id` int NOT NULL AUTO_INCREMENT,
  `name` varchar(150) NOT NULL DEFAULT '',
  `logo` varchar(255) NOT NULL,
  `website_url` varchar(255) NOT NULL DEFAULT '',
  `status` varchar(20) NOT NULL DEFAULT 'Active',
  `sort_order` int NOT NULL DEFAULT '0',
  `created_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `tbl_client`
--

INSERT INTO `tbl_client` (`id`, `name`, `logo`, `website_url`, `status`, `sort_order`, `created_at`) VALUES
(1, 'bnnbn', 'client-1.png', '', 'Active', 0, '2026-07-17 16:18:24');

-- --------------------------------------------------------

--
-- Table structure for table `tbl_color`
--

DROP TABLE IF EXISTS `tbl_color`;
CREATE TABLE IF NOT EXISTS `tbl_color` (
  `color_id` int NOT NULL AUTO_INCREMENT,
  `color_name` varchar(255) NOT NULL,
  PRIMARY KEY (`color_id`)
) ENGINE=InnoDB AUTO_INCREMENT=32 DEFAULT CHARSET=latin1;

--
-- Dumping data for table `tbl_color`
--

INSERT INTO `tbl_color` (`color_id`, `color_name`) VALUES
(1, 'Red'),
(2, 'Black'),
(3, 'Blue'),
(4, 'Yellow'),
(5, 'Green'),
(6, 'White'),
(7, 'Orange'),
(8, 'Brown'),
(9, 'Tan'),
(10, 'Pink'),
(11, 'Mixed'),
(12, 'Lightblue'),
(13, 'Violet'),
(14, 'Light Purple'),
(15, 'Salmon'),
(16, 'Gold'),
(17, 'Gray'),
(18, 'Ash'),
(19, 'Maroon'),
(20, 'Silver'),
(21, 'Dark Clay'),
(22, 'Cognac'),
(23, 'Coffee'),
(24, 'Charcoal'),
(25, 'Navy'),
(26, 'Fuchsia'),
(27, 'Olive'),
(28, 'Burgundy'),
(29, 'Midnight Blue'),
(30, 'Rose Gold'),
(31, 'Golden ');

-- --------------------------------------------------------

--
-- Table structure for table `tbl_country`
--

DROP TABLE IF EXISTS `tbl_country`;
CREATE TABLE IF NOT EXISTS `tbl_country` (
  `country_id` int NOT NULL AUTO_INCREMENT,
  `country_name` varchar(100) NOT NULL DEFAULT '',
  PRIMARY KEY (`country_id`)
) ENGINE=MyISAM AUTO_INCREMENT=251 DEFAULT CHARSET=utf8mb3;

--
-- Dumping data for table `tbl_country`
--

INSERT INTO `tbl_country` (`country_id`, `country_name`) VALUES
(250, 'kathmandu');

-- --------------------------------------------------------

--
-- Table structure for table `tbl_customer`
--

DROP TABLE IF EXISTS `tbl_customer`;
CREATE TABLE IF NOT EXISTS `tbl_customer` (
  `cust_id` int NOT NULL AUTO_INCREMENT,
  `cust_name` varchar(100) NOT NULL,
  `cust_cname` varchar(100) NOT NULL,
  `cust_email` varchar(100) NOT NULL,
  `cust_phone` varchar(50) NOT NULL,
  `cust_country` int NOT NULL,
  `cust_address` text NOT NULL,
  `cust_city` varchar(100) NOT NULL,
  `cust_state` varchar(100) NOT NULL,
  `cust_zip` varchar(30) NOT NULL,
  `cust_b_name` varchar(100) NOT NULL,
  `cust_b_cname` varchar(100) NOT NULL,
  `cust_b_phone` varchar(50) NOT NULL,
  `cust_b_country` int NOT NULL,
  `cust_b_address` text NOT NULL,
  `cust_b_city` varchar(100) NOT NULL,
  `cust_b_state` varchar(100) NOT NULL,
  `cust_b_zip` varchar(30) NOT NULL,
  `cust_s_name` varchar(100) NOT NULL,
  `cust_s_cname` varchar(100) NOT NULL,
  `cust_s_phone` varchar(50) NOT NULL,
  `cust_s_country` int NOT NULL,
  `cust_s_address` text NOT NULL,
  `cust_s_city` varchar(100) NOT NULL,
  `cust_s_state` varchar(100) NOT NULL,
  `cust_s_zip` varchar(30) NOT NULL,
  `cust_password` varchar(100) NOT NULL,
  `cust_token` varchar(255) NOT NULL,
  `cust_datetime` varchar(100) NOT NULL,
  `cust_timestamp` varchar(100) NOT NULL,
  `cust_status` int NOT NULL,
  PRIMARY KEY (`cust_id`)
) ENGINE=InnoDB AUTO_INCREMENT=38 DEFAULT CHARSET=latin1;

--
-- Dumping data for table `tbl_customer`
--

INSERT INTO `tbl_customer` (`cust_id`, `cust_name`, `cust_cname`, `cust_email`, `cust_phone`, `cust_country`, `cust_address`, `cust_city`, `cust_state`, `cust_zip`, `cust_b_name`, `cust_b_cname`, `cust_b_phone`, `cust_b_country`, `cust_b_address`, `cust_b_city`, `cust_b_state`, `cust_b_zip`, `cust_s_name`, `cust_s_cname`, `cust_s_phone`, `cust_s_country`, `cust_s_address`, `cust_s_city`, `cust_s_state`, `cust_s_zip`, `cust_password`, `cust_token`, `cust_datetime`, `cust_timestamp`, `cust_status`) VALUES
(32, 'uday kumar pandit', '', 'sastikatrading@gmail.com', '9801076273', 250, '', 'Biratnagar', 'bagmati', '', '', '', '', 0, '', '', '', '', '', '', '', 0, '', '', '', '', '', '', '', '', 1),
(33, 'uday kumar pandit', '', 'nirajkarna66@gmail.com', '9801076273', 0, '', '', '', '', '', '', '', 0, '', '', '', '', '', '', '', 0, '', '', '', '', '$2y$10$abgFL9/9eEHwljuFgILvbuyastlecoCU.oYc.cxJoJ35FBC69N0C6', '', '2026-07-02 11:18:40', '1782970420', 1),
(34, 'salman', '', 'pandey@gmail.com', '9999999999', 0, '', '', '', '', '', '', '', 0, '', '', '', '', '', '', '', 0, '', '', '', '', '$2y$10$8bzjz1NNJyrNDctXcpA6Iugfzt292LOLJfA0sf4CTP4ewTZArjLgi', '', '2026-07-02 16:32:29', '1782989249', 1),
(35, 'up', '', 'na66@gmail.com', '9999999999', 0, '', '', '', '', '', '', '', 0, '', '', '', '', '', '', '', 0, '', '', '', '', '$2y$10$Vyp6TWdyXJ3tK2rMS5L.5eTZEmuPxUkZTLQtf5T1AslfYX4H/RH5.', '', '2026-07-02 16:53:43', '1782990523', 1),
(36, 'ram', '', 'rajan@gmail.com', '9999999999', 0, '', '', '', '', '', '', '', 0, '', '', '', '', '', '', '', 0, '', '', '', '', '$2y$10$BzKdig1DDnaWo2RZ2dSPi.FxEE6e.MMT62XkEBZ3XSqb8H2X80ozG', '', '2026-07-02 18:08:42', '1782995022', 1),
(37, 'ram', '', 'admin@mail.com', '9999999999', 0, '', '', '', '', '', '', '', 0, '', '', '', '', '', '', '', 0, '', '', '', '', '$2y$10$jsujCUIDDNBHluBPitnYWehP2KijZQkXNnJic8cdV8B0t/qt2Y2zW', '', '2026-07-03 12:23:26', '1783060706', 1);

-- --------------------------------------------------------

--
-- Table structure for table `tbl_customer_message`
--

DROP TABLE IF EXISTS `tbl_customer_message`;
CREATE TABLE IF NOT EXISTS `tbl_customer_message` (
  `customer_message_id` int NOT NULL AUTO_INCREMENT,
  `subject` varchar(255) NOT NULL,
  `message` text NOT NULL,
  `order_detail` text NOT NULL,
  `cust_id` int NOT NULL,
  PRIMARY KEY (`customer_message_id`)
) ENGINE=MyISAM AUTO_INCREMENT=20 DEFAULT CHARSET=latin1;

--
-- Dumping data for table `tbl_customer_message`
--

INSERT INTO `tbl_customer_message` (`customer_message_id`, `subject`, `message`, `order_detail`, `cust_id`) VALUES
(9, 'hehfkfnf', 'youhfde bd ebcdd ', '\r\nCustomer Name: Ramesh Khatri<br>\r\nCustomer Email: rameshkhatri6789@gmail.com<br>\r\nPayment Method: esewa<br>\r\nPayment Date: 2025-09-08 15:47:48<br>\r\nPayment Details: <br><br>\r\nPaid Amount: 1538<br>\r\nPayment Status: Completed<br>\r\nShipping Status: Completed<br>\r\nPayment Id: 1757339268<br>\r\n            \r\n<br><b><u>Product Item 1</u></b><br>\r\nProduct Name: Amazfit GTS 3 Smart Watch for Android iPhone<br>\r\nSize: <br>\r\nColor: <br>\r\nQuantity: 8<br>\r\nUnit Price: 179<br>\r\n            \r\n<br><b><u>Product Item 2</u></b><br>\r\nProduct Name: Loose-fit One-Shoulder Cutout Rib Knit Maxi Dress<br>\r\nSize: <br>\r\nColor: <br>\r\nQuantity: 1<br>\r\nUnit Price: 39<br>\r\n            \r\n<br><b><u>Product Item 3</u></b><br>\r\nProduct Name: Women\'s Tea Length Dress with Rosette Detail (Petite & Regular)<br>\r\nSize: <br>\r\nColor: <br>\r\nQuantity: 1<br>\r\nUnit Price: 67<br>\r\n            ', 20),
(10, 'ghgh', 'ghghghghgh', '\r\nCustomer Name: niraj karna<br>\r\nCustomer Email: nirajkarna66@gmail.com<br>\r\nPayment Method: cod<br>\r\nPayment Date: 2025-10-08 06:15:09<br>\r\nPayment Details: <br><br>\r\nPaid Amount: 300<br>\r\nPayment Status: Pending<br>\r\nShipping Status: Pending<br>\r\nPayment Id: 1759896909<br>\r\n            \r\n<br><b><u>Product Item 1</u></b><br>\r\nProduct Name: Women\'s Plus-Size Shirt Dress with Gold Hardware<br>\r\nSize: <br>\r\nColor: <br>\r\nQuantity: 2<br>\r\nUnit Price: 100<br>\r\n            ', 23),
(11, 'gnnbnnnn', 'bnbnnbnbnb', '\r\nCustomer Name: niraj karna<br>\r\nCustomer Email: nirajkarna66@gmail.com<br>\r\nPayment Method: cod<br>\r\nPayment Date: 2025-10-08 16:18:55<br>\r\nPayment Details: <br><br>\r\nPaid Amount: 220<br>\r\nPayment Status: Completed<br>\r\nShipping Status: Completed<br>\r\nPayment Id: 1759933135<br>\r\n            \r\n<br><b><u>Product Item 1</u></b><br>\r\nProduct Name: New full cover<br>\r\nSize: <br>\r\nColor: <br>\r\nQuantity: 1<br>\r\nUnit Price: 100<br>\r\n            ', 24),
(12, 'gnnbnnnn', 'bnbnnbnbnb', '\r\nCustomer Name: niraj karna<br>\r\nCustomer Email: nirajkarna66@gmail.com<br>\r\nPayment Method: cod<br>\r\nPayment Date: 2025-10-08 16:18:55<br>\r\nPayment Details: <br><br>\r\nPaid Amount: 220<br>\r\nPayment Status: Completed<br>\r\nShipping Status: Completed<br>\r\nPayment Id: 1759933135<br>\r\n            \r\n<br><b><u>Product Item 1</u></b><br>\r\nProduct Name: New full cover<br>\r\nSize: <br>\r\nColor: <br>\r\nQuantity: 1<br>\r\nUnit Price: 100<br>\r\n            ', 24),
(13, 'about order 25', 'thank you payment received .', '\r\nCustomer Name: sabita tamang <br>\r\nCustomer Email: grg.cwang@gmail.com<br>\r\nPayment Method: esewa<br>\r\nPayment Date: 2025-11-01 14:27:19<br>\r\nPayment Details: <br><br>\r\nPaid Amount: 1650<br>\r\nPayment Status: Completed<br>\r\nShipping Status: Pending<br>\r\nPayment Id: 1762007239<br>\r\n            \r\n<br><b><u>Product Item 1</u></b><br>\r\nProduct Name: Peony mold <br>\r\nSize: <br>\r\nColor: <br>\r\nQuantity: 3<br>\r\nUnit Price: 550<br>\r\n            ', 25),
(14, 'delivery status ', 'delivery has been completed .thank you for choosing us .', '\r\nCustomer Name: sabita tamang <br>\r\nCustomer Email: grg.cwang@gmail.com<br>\r\nPayment Method: esewa<br>\r\nPayment Date: 2025-11-01 14:27:19<br>\r\nPayment Details: <br><br>\r\nPaid Amount: 1650<br>\r\nPayment Status: Completed<br>\r\nShipping Status: Completed<br>\r\nPayment Id: 1762007239<br>\r\n            \r\n<br><b><u>Product Item 1</u></b><br>\r\nProduct Name: Peony mold <br>\r\nSize: <br>\r\nColor: <br>\r\nQuantity: 3<br>\r\nUnit Price: 550<br>\r\n            ', 25),
(15, 'nm', 'nmnmnmnm', '\r\nCustomer Name: niraj<br>\r\nCustomer Email: nirajkarna66@gmail.com<br>\r\nPayment Method: esewa<br>\r\nPayment Date: 2026-06-30 16:16:02<br>\r\nPayment Details: <br><br>\r\nPaid Amount: 1234000<br>\r\nPayment Status: Completed<br>\r\nShipping Status: Pending<br>\r\nPayment Id: ORD-1782815462<br>\r\n            \r\n<br><b><u>Product Item 1</u></b><br>\r\nProduct Name: 3 in 1 Floral Topping mold<br>\r\nSize: <br>\r\nColor: <br>\r\nQuantity: 3<br>\r\nUnit Price: 380<br>\r\n            ', 0),
(16, 'df', 'dfg', '\r\nCustomer Name: niraj<br>\r\nCustomer Email: nirajkarna66@gmail.com<br>\r\nPayment Method: esewa<br>\r\nPayment Date: 2026-06-30 16:16:02<br>\r\nPayment Details: <br><br>\r\nPaid Amount: 1234000<br>\r\nPayment Status: Completed<br>\r\nShipping Status: Completed<br>\r\nPayment Id: ORD-1782815462<br>\r\n            \r\n<br><b><u>Product Item 1</u></b><br>\r\nProduct Name: 3 in 1 Floral Topping mold<br>\r\nSize: <br>\r\nColor: <br>\r\nQuantity: 3<br>\r\nUnit Price: 380<br>\r\n            ', 0),
(17, 'df', 'hgjhnnbnb', '\r\nCustomer Name: uday kumar pandit<br>\r\nCustomer Email: nirajkarna66@gmail.com<br>\r\nPayment Method: esewa<br>\r\nPayment Date: 2026-07-02 18:11:20<br>\r\nPayment Details: <br><br>\r\nPaid Amount: 350<br>\r\nPayment Status: Completed<br>\r\nShipping Status: Completed<br>\r\nPayment Id: ORD-20260702181120<br>\r\n            \r\n<br><b><u>Product Item 1</u></b><br>\r\nProduct Name: Panda Mould<br>\r\nSize: <br>\r\nColor: <br>\r\nQuantity: 1<br>\r\nUnit Price: 360<br>\r\n            ', 33),
(18, 'df', 'hgjhnnbnb', '\r\nCustomer Name: uday kumar pandit<br>\r\nCustomer Email: nirajkarna66@gmail.com<br>\r\nPayment Method: esewa<br>\r\nPayment Date: 2026-07-02 18:11:20<br>\r\nPayment Details: <br><br>\r\nPaid Amount: 350<br>\r\nPayment Status: Completed<br>\r\nShipping Status: Completed<br>\r\nPayment Id: ORD-20260702181120<br>\r\n            \r\n<br><b><u>Product Item 1</u></b><br>\r\nProduct Name: Panda Mould<br>\r\nSize: <br>\r\nColor: <br>\r\nQuantity: 1<br>\r\nUnit Price: 360<br>\r\n            ', 33),
(19, 'df', 'hgjhnnbnb', '\r\nCustomer Name: uday kumar pandit<br>\r\nCustomer Email: nirajkarna66@gmail.com<br>\r\nPayment Method: esewa<br>\r\nPayment Date: 2026-07-02 18:11:20<br>\r\nPayment Details: <br><br>\r\nPaid Amount: 350<br>\r\nPayment Status: Completed<br>\r\nShipping Status: Completed<br>\r\nPayment Id: ORD-20260702181120<br>\r\n            \r\n<br><b><u>Product Item 1</u></b><br>\r\nProduct Name: Panda Mould<br>\r\nSize: <br>\r\nColor: <br>\r\nQuantity: 1<br>\r\nUnit Price: 360<br>\r\n            ', 33);

-- --------------------------------------------------------

--
-- Table structure for table `tbl_end_category`
--

DROP TABLE IF EXISTS `tbl_end_category`;
CREATE TABLE IF NOT EXISTS `tbl_end_category` (
  `ecat_id` int NOT NULL AUTO_INCREMENT,
  `ecat_name` varchar(255) NOT NULL,
  `mcat_id` int NOT NULL,
  PRIMARY KEY (`ecat_id`)
) ENGINE=InnoDB AUTO_INCREMENT=89 DEFAULT CHARSET=latin1;

--
-- Dumping data for table `tbl_end_category`
--

INSERT INTO `tbl_end_category` (`ecat_id`, `ecat_name`, `mcat_id`) VALUES
(80, 'Round', 18),
(81, 'Hook', 18),
(82, 'Floral Topping Mold', 19),
(83, ' Flower Mold ', 19),
(84, 'Leaf Mold ', 19),
(85, 'All Moulds', 19),
(86, 'Heart Moulds ', 19),
(87, 'Wick Trimmer ', 22),
(88, 'Teddy Mould ', 19);

-- --------------------------------------------------------

--
-- Table structure for table `tbl_faq`
--

DROP TABLE IF EXISTS `tbl_faq`;
CREATE TABLE IF NOT EXISTS `tbl_faq` (
  `faq_id` int NOT NULL AUTO_INCREMENT,
  `faq_title` varchar(255) NOT NULL,
  `faq_content` text NOT NULL,
  PRIMARY KEY (`faq_id`)
) ENGINE=InnoDB AUTO_INCREMENT=9 DEFAULT CHARSET=latin1;

--
-- Dumping data for table `tbl_faq`
--

INSERT INTO `tbl_faq` (`faq_id`, `faq_title`, `faq_content`) VALUES
(1, 'How to find an item?', '<h3 class=\"checkout-complete-box font-bold txt16\" style=\"box-sizing: inherit; text-rendering: optimizeLegibility; margin: 0.2rem 0px 0.5rem; padding: 0px; line-height: 1.4; background-color: rgb(250, 250, 250);\"><font color=\"#222222\" face=\"opensans, Helvetica Neue, Helvetica, Helvetica, Arial, sans-serif\"><span style=\"font-size: 15.7143px;\"><b>We have a wide range of fabulous products to choose from.</b></span></font></h3><h3 class=\"checkout-complete-box font-bold txt16\" style=\"box-sizing: inherit; text-rendering: optimizeLegibility; margin: 0.2rem 0px 0.5rem; padding: 0px; line-height: 1.4; background-color: rgb(250, 250, 250);\"><span style=\"font-size: 15.7143px; color: rgb(34, 34, 34); font-family: opensans, \" helvetica=\"\" neue\",=\"\" helvetica,=\"\" arial,=\"\" sans-serif;\"=\"\">Tip 1: If you\'re looking for a specific product, use the keyword search box located at the top of the site. Simply type what you are looking for, and prepare to be amazed!</span></h3><h3 class=\"checkout-complete-box font-bold txt16\" style=\"box-sizing: inherit; text-rendering: optimizeLegibility; margin: 0.2rem 0px 0.5rem; padding: 0px; line-height: 1.4; background-color: rgb(250, 250, 250);\"><font color=\"#222222\" face=\"opensans, Helvetica Neue, Helvetica, Helvetica, Arial, sans-serif\"><span style=\"font-size: 15.7143px;\">Tip 2: If you want to explore a category of products, use the Shop Categories in the upper menu, and navigate through your favorite categories where we\'ll feature the best products in each.</span></font><br><br></h3>\r\n'),
(2, 'What is your return policy?', '<p><span style=\"color: rgb(10, 10, 10); font-family: opensans, &quot;Helvetica Neue&quot;, Helvetica, Helvetica, Arial, sans-serif; font-size: 14px; text-align: center;\">You have 15 days to make a refund request after your order has been delivered.</span><br></p>\r\n'),
(3, ' I received a defective/damaged item, can I get a refund?', '<p>In case the item you received is damaged or defective, you could return an item in the same condition as you received it with the original box and/or packaging intact. Once we receive the returned item, we will inspect it and if the item is found to be defective or damaged, we will process the refund along with any shipping fees incurred.<br></p>\r\n'),
(4, 'When are ‘Returns’ not possible?', '<p class=\"a  \" style=\"box-sizing: inherit; text-rendering: optimizeLegibility; line-height: 1.6; margin-bottom: 0.714286rem; padding: 0px; font-size: 14px; color: rgb(10, 10, 10); font-family: opensans, &quot;Helvetica Neue&quot;, Helvetica, Helvetica, Arial, sans-serif; background-color: rgb(250, 250, 250);\">There are a few certain scenarios where it is difficult for us to support returns:</p><ol style=\"box-sizing: inherit; line-height: 1.6; margin-right: 0px; margin-bottom: 0px; margin-left: 1.25rem; padding: 0px; list-style-position: outside; color: rgb(10, 10, 10); font-family: opensans, &quot;Helvetica Neue&quot;, Helvetica, Helvetica, Arial, sans-serif; font-size: 14px; background-color: rgb(250, 250, 250);\"><li style=\"box-sizing: inherit; margin: 0px; padding: 0px; font-size: inherit;\">Return request is made outside the specified time frame, of 15 days from delivery.</li><li style=\"box-sizing: inherit; margin: 0px; padding: 0px; font-size: inherit;\">Product is used, damaged, or is not in the same condition as you received it.</li><li style=\"box-sizing: inherit; margin: 0px; padding: 0px; font-size: inherit;\">Specific categories like innerwear, lingerie, socks and clothing freebies etc.</li><li style=\"box-sizing: inherit; margin: 0px; padding: 0px; font-size: inherit;\">Defective products which are covered under the manufacturer\'s warranty.</li><li style=\"box-sizing: inherit; margin: 0px; padding: 0px; font-size: inherit;\">Any consumable item which has been used or installed.</li><li style=\"box-sizing: inherit; margin: 0px; padding: 0px; font-size: inherit;\">Products with tampered or missing serial numbers.</li><li style=\"box-sizing: inherit; margin: 0px; padding: 0px; font-size: inherit;\">Anything missing from the package you\'ve received including price tags, labels, original packing, freebies and accessories.</li><li style=\"box-sizing: inherit; margin: 0px; padding: 0px; font-size: inherit;\">Fragile items, hygiene related items.</li></ol>\r\n'),
(8, 'How to place order from website ?', '<p>contact us&nbsp;</p>');

-- --------------------------------------------------------

--
-- Table structure for table `tbl_gallery`
--

DROP TABLE IF EXISTS `tbl_gallery`;
CREATE TABLE IF NOT EXISTS `tbl_gallery` (
  `id` int NOT NULL AUTO_INCREMENT,
  `title` varchar(200) NOT NULL DEFAULT '',
  `content` text,
  `photo` varchar(255) NOT NULL DEFAULT '',
  `mcat_id` int NOT NULL DEFAULT '0',
  `status` varchar(20) NOT NULL DEFAULT 'Active',
  `sort_order` int NOT NULL DEFAULT '0',
  `created_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `tbl_gallery`
--

INSERT INTO `tbl_gallery` (`id`, `title`, `content`, `photo`, `mcat_id`, `status`, `sort_order`, `created_at`) VALUES
(1, 'Easy Returns', 'Return any item before 15 days!', 'gallery-1.jpg', 21, 'Active', 1, '2026-07-21 12:33:37'),
(2, 'Fast Shipping', 'Items are shipped within 24 hours.', 'gallery-2.jpg', 20, 'Active', 2, '2026-07-21 12:33:37'),
(3, 'Secure Checkout', 'Providing Secure Checkout Options for all', 'gallery-3.jpg', 20, 'Active', 3, '2026-07-21 12:33:37'),
(4, '100% satisfaction', 'customer\'s satisfaction', 'gallery-4.jpg', 20, 'Active', 4, '2026-07-21 12:33:37');

-- --------------------------------------------------------

--
-- Table structure for table `tbl_language`
--

DROP TABLE IF EXISTS `tbl_language`;
CREATE TABLE IF NOT EXISTS `tbl_language` (
  `lang_id` int NOT NULL AUTO_INCREMENT,
  `lang_name` varchar(255) NOT NULL,
  `lang_value` text NOT NULL,
  PRIMARY KEY (`lang_id`)
) ENGINE=MyISAM AUTO_INCREMENT=164 DEFAULT CHARSET=latin1;

--
-- Dumping data for table `tbl_language`
--

INSERT INTO `tbl_language` (`lang_id`, `lang_name`, `lang_value`) VALUES
(1, 'Currency', '$'),
(2, 'Search Product', 'Search Product'),
(3, 'Search', 'Search'),
(4, 'Submit', 'Submit'),
(5, 'Update', 'Update'),
(6, 'Read More', 'Read More'),
(7, 'Serial', 'Serial'),
(8, 'Photo', 'Photo'),
(9, 'Login', 'Login'),
(10, 'Customer Login', 'Customer Login'),
(11, 'Click here to login', 'Click here to login'),
(12, 'Back to Login Page', 'Back to Login Page'),
(13, 'Logged in as', 'Logged in as'),
(14, 'Logout', 'Logout'),
(15, 'Register', 'Register'),
(16, 'Customer Registration', 'Customer Registration'),
(17, 'Registration Successful', 'Registration Successful'),
(18, 'Cart', 'Cart'),
(19, 'View Cart', 'View Cart'),
(20, 'Update Cart', 'Update Cart'),
(21, 'Back to Cart', 'Back to Cart'),
(22, 'Checkout', 'Checkout'),
(23, 'Proceed to Checkout', 'Proceed to Checkout'),
(24, 'Orders', 'Orders'),
(25, 'Order History', 'Order History'),
(26, 'Order Details', 'Order Details'),
(27, 'Payment Date and Time', 'Payment Date and Time'),
(28, 'Transaction ID', 'Transaction ID'),
(29, 'Paid Amount', 'Paid Amount'),
(30, 'Payment Status', 'Payment Status'),
(31, 'Payment Method', 'Payment Method'),
(32, 'Payment ID', 'Payment ID'),
(33, 'Payment Section', 'Payment Section'),
(34, 'Select Payment Method', 'Select Payment Method'),
(35, 'Select a Method', 'Select a Method'),
(36, 'PayPal', 'PayPal'),
(37, 'Stripe', 'Stripe'),
(38, 'Bank Deposit', 'Bank Deposit'),
(39, 'Card Number', 'Card Number'),
(40, 'CVV', 'CVV'),
(41, 'Month', 'Month'),
(42, 'Year', 'Year'),
(43, 'Send to this Details', 'Send to this Details'),
(44, 'Transaction Information', 'Transaction Information'),
(45, 'Include transaction id and other information correctly', 'Include transaction id and other information correctly'),
(46, 'Pay Now', 'Pay Now'),
(47, 'Product Name', 'Product Name'),
(48, 'Product Details', 'Product Details'),
(49, 'Categories', 'Categories'),
(50, 'Category:', 'Category:'),
(51, 'All Products Under', 'All Products Under'),
(52, 'Select Size', 'Select Size'),
(53, 'Select Color', 'Select Color'),
(54, 'Product Price', 'Product Price'),
(55, 'Quantity', 'Quantity'),
(56, 'Out of Stock', 'Out of Stock'),
(57, 'Share This', 'Share This'),
(58, 'Share This Product', 'Share This Product'),
(59, 'Product Description', 'Product Description'),
(60, 'Features', 'Features'),
(61, 'Conditions', 'Conditions'),
(62, 'Return Policy', 'Return Policy'),
(63, 'Reviews', 'Reviews'),
(64, 'Review', 'Review'),
(65, 'Give a Review', 'Give a Review'),
(66, 'Write your comment (Optional)', 'Write your comment (Optional)'),
(67, 'Submit Review', 'Submit Review'),
(68, 'You already have given a rating!', 'You already have given a rating!'),
(69, 'You must have to login to give a review', 'You must have to login to give a review'),
(70, 'No description found', 'No description found'),
(71, 'No feature found', 'No feature found'),
(72, 'No condition found', 'No condition found'),
(73, 'No return policy found', 'No return policy found'),
(74, 'Review not found', 'Review not found'),
(75, 'Customer Name', 'Customer Name'),
(76, 'Comment', 'Comment'),
(77, 'Comments', 'Comments'),
(78, 'Rating', 'Rating'),
(79, 'Previous', 'Previous'),
(80, 'Next', 'Next'),
(81, 'Sub Total', 'Sub Total'),
(82, 'Total', 'Total'),
(83, 'Action', 'Action'),
(84, 'Shipping Cost', 'Shipping Cost'),
(85, 'Continue Shopping', 'Continue Shopping'),
(86, 'Update Billing Address', 'Update Billing Address'),
(87, 'Update Shipping Address', 'Update Shipping Address'),
(88, 'Update Billing and Shipping Info', 'Update Billing and Shipping Info'),
(89, 'Dashboard', 'Dashboard'),
(90, 'Welcome to the Dashboard', 'Welcome to the Dashboard'),
(91, 'Back to Dashboard', 'Back to Dashboard'),
(92, 'Subscribe', 'Subscribe'),
(93, 'Subscribe To Our Newsletter', 'Subscribe To Our Newsletter'),
(94, 'Email Address', 'Email Address'),
(95, 'Enter Your Email Address', 'Enter Your Email Address'),
(96, 'Password', 'Password'),
(97, 'Forget Password', 'Forget Password'),
(98, 'Retype Password', 'Retype Password'),
(99, 'Update Password', 'Update Password'),
(100, 'New Password', 'New Password'),
(101, 'Retype New Password', 'Retype New Password'),
(102, 'Full Name', 'Full Name'),
(103, 'Company Name', 'Company Name'),
(104, 'Phone Number', 'Phone Number'),
(105, 'Address', 'Address'),
(106, 'Country', 'Country'),
(107, 'City', 'City'),
(108, 'State', 'State'),
(109, 'Zip Code', 'Zip Code'),
(110, 'About Us', 'About Us'),
(111, 'Featured Posts', 'Featured Posts'),
(112, 'Popular Posts', 'Popular Posts'),
(113, 'Recent Posts', 'Recent Posts'),
(114, 'Contact Information', 'Contact Information'),
(115, 'Contact Form', 'Contact Form'),
(116, 'Our Office', 'Our Office'),
(117, 'Update Profile', 'Update Profile'),
(118, 'Send Message', 'Send Message'),
(119, 'Message', 'Message'),
(120, 'Find Us On Map', 'Find Us On Map'),
(121, 'Congratulation! Payment is successful.', 'Congratulation! Payment is successful.'),
(122, 'Billing and Shipping Information is updated successfully.', 'Billing and Shipping Information is updated successfully.'),
(123, 'Customer Name can not be empty.', 'Customer Name can not be empty.'),
(124, 'Phone Number can not be empty.', 'Phone Number can not be empty.'),
(125, 'Address can not be empty.', 'Address can not be empty.'),
(126, 'You must have to select a country.', 'You must have to select a country.'),
(127, 'City can not be empty.', 'City can not be empty.'),
(128, 'State can not be empty.', 'State can not be empty.'),
(129, 'Zip Code can not be empty.', 'Zip Code can not be empty.'),
(130, 'Profile Information is updated successfully.', 'Profile Information is updated successfully.'),
(131, 'Email Address can not be empty', 'Email Address can not be empty'),
(132, 'Email and/or Password can not be empty.', 'Email and/or Password can not be empty.'),
(133, 'Email Address does not match.', 'Email Address does not match.'),
(134, 'Email address must be valid.', 'Email address must be valid.'),
(135, 'You email address is not found in our system.', 'You email address is not found in our system.'),
(136, 'Please check your email and confirm your subscription.', 'Please check your email and confirm your subscription.'),
(137, 'Your email is verified successfully. You can now login to our website.', 'Your email is verified successfully. You can now login to our website.'),
(138, 'Password can not be empty.', 'Password can not be empty.'),
(139, 'Passwords do not match.', 'Passwords do not match.'),
(140, 'Please enter new and retype passwords.', 'Please enter new and retype passwords.'),
(141, 'Password is updated successfully.', 'Password is updated successfully.'),
(142, 'To reset your password, please click on the link below.', 'To reset your password, please click on the link below.'),
(143, 'PASSWORD RESET REQUEST - YOUR WEBSITE.COM', 'PASSWORD RESET REQUEST - YOUR WEBSITE.COM'),
(144, 'The password reset email time (24 hours) has expired. Please again try to reset your password.', 'The password reset email time (24 hours) has expired. Please again try to reset your password.'),
(145, 'A confirmation link is sent to your email address. You will get the password reset information in there.', 'A confirmation link is sent to your email address. You will get the password reset information in there.'),
(146, 'Password is reset successfully. You can now login.', 'Password is reset successfully. You can now login.'),
(147, 'Email Address Already Exists', 'Email Address Already Exists.'),
(148, 'Sorry! Your account is inactive. Please contact to the administrator.', 'Sorry! Your account is inactive. Please contact to the administrator.'),
(149, 'Change Password', 'Change Password'),
(150, 'Registration Email Confirmation for YOUR WEBSITE', 'Registration Email Confirmation for YOUR WEBSITE.'),
(151, 'Thank you for your registration! Your account has been created. To active your account click on the link below:', 'Thank you for your registration! Your account has been created. To active your account click on the link below:'),
(152, 'Your registration is completed. Please check your email address to follow the process to confirm your registration.', 'Your registration is completed. Please check your email address to follow the process to confirm your registration.'),
(153, 'No Product Found', 'No Product Found'),
(154, 'Add to Cart', 'Add to Cart'),
(155, 'Related Products', 'Related Products'),
(156, 'See all related products from below', 'See all the related products from below'),
(157, 'Size', 'Size'),
(158, 'Color', 'Color'),
(159, 'Price', 'Price'),
(160, 'Please login as customer to checkout', 'Please login as customer to checkout'),
(161, 'Billing Address', 'Billing Address'),
(162, 'Shipping Address', 'Shipping Address'),
(163, 'Rating is Submitted Successfully!', 'Rating is Submitted Successfully!');

-- --------------------------------------------------------

--
-- Table structure for table `tbl_mid_category`
--

DROP TABLE IF EXISTS `tbl_mid_category`;
CREATE TABLE IF NOT EXISTS `tbl_mid_category` (
  `mcat_id` int NOT NULL AUTO_INCREMENT,
  `mcat_name` varchar(255) NOT NULL,
  `tcat_id` int NOT NULL,
  PRIMARY KEY (`mcat_id`)
) ENGINE=InnoDB AUTO_INCREMENT=38 DEFAULT CHARSET=latin1;

--
-- Dumping data for table `tbl_mid_category`
--

INSERT INTO `tbl_mid_category` (`mcat_id`, `mcat_name`, `tcat_id`) VALUES
(18, 'Diwali Special', 6),
(19, 'Silicone molds', 6),
(20, 'Glass Jar', 6),
(21, 'Beginner Kit', 6),
(22, 'Essential Tools', 6),
(23, 'Fragrance', 6),
(24, 'Wax Melter', 6),
(25, 'Silicone molds', 8),
(26, 'Deep Casting Molds', 7),
(27, 'Jewelry Molds', 7),
(28, 'Color', 7),
(29, 'Resin', 7),
(30, 'Essential Tools', 7),
(31, 'Resin Beginner Tools', 7),
(32, 'Candle Box', 8),
(33, 'Sticker', 8),
(34, 'Ribbon', 8),
(35, 'Wrapping Paper', 8),
(36, 'Fragile Sticker', 8);

-- --------------------------------------------------------

--
-- Table structure for table `tbl_order`
--

DROP TABLE IF EXISTS `tbl_order`;
CREATE TABLE IF NOT EXISTS `tbl_order` (
  `id` int NOT NULL AUTO_INCREMENT,
  `product_id` int NOT NULL,
  `product_name` varchar(255) NOT NULL,
  `size` varchar(100) NOT NULL,
  `color` varchar(100) NOT NULL,
  `quantity` varchar(50) NOT NULL,
  `unit_price` varchar(50) NOT NULL,
  `payment_id` varchar(255) NOT NULL,
  `line_total` decimal(10,2) DEFAULT '0.00',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=128 DEFAULT CHARSET=latin1;

--
-- Dumping data for table `tbl_order`
--

INSERT INTO `tbl_order` (`id`, `product_id`, `product_name`, `size`, `color`, `quantity`, `unit_price`, `payment_id`, `line_total`) VALUES
(34, 108, 'Daisy Floral Mold', '', '', '2', '300', '1', 0.00),
(117, 167, 'Dress Mould ', '', '', '1', '0', 'ORD-20260703172557', 0.00),
(118, 169, 'Dress Mould ', '', '', '1', '0', 'ORD-20260717165001', 0.00),
(119, 151, 'Couple Hug Mould ', '', '', '1', '0', 'ORD-20260717165001', 0.00),
(123, 138, 'Rose Bouquet Mould', '', '', '2', '1250', 'ORD-20260721121404', 2500.00),
(124, 108, 'Daisy Floral Mold', '', '', '1', '300', 'ORD-20260721121404', 300.00),
(125, 111, 'Mini Peony Mold', '', '', '1', '450', 'ORD-20260721121404', 450.00),
(127, 171, 'niraj', '', '', '1', '360', 'ORD-20260721123812', 360.00);

-- --------------------------------------------------------

--
-- Table structure for table `tbl_page`
--

DROP TABLE IF EXISTS `tbl_page`;
CREATE TABLE IF NOT EXISTS `tbl_page` (
  `id` int NOT NULL AUTO_INCREMENT,
  `about_title` varchar(255) NOT NULL,
  `about_content` text NOT NULL,
  `about_banner` varchar(255) NOT NULL,
  `about_meta_title` varchar(255) NOT NULL,
  `about_meta_keyword` text NOT NULL,
  `about_meta_description` text NOT NULL,
  `faq_title` varchar(255) NOT NULL,
  `faq_banner` varchar(255) NOT NULL,
  `faq_meta_title` varchar(255) NOT NULL,
  `faq_meta_keyword` text NOT NULL,
  `faq_meta_description` text NOT NULL,
  `blog_title` varchar(255) NOT NULL,
  `blog_banner` varchar(255) NOT NULL,
  `blog_meta_title` varchar(255) NOT NULL,
  `blog_meta_keyword` text NOT NULL,
  `blog_meta_description` text NOT NULL,
  `contact_title` varchar(255) NOT NULL,
  `contact_banner` varchar(255) NOT NULL,
  `contact_meta_title` varchar(255) NOT NULL,
  `contact_meta_keyword` text NOT NULL,
  `contact_meta_description` text NOT NULL,
  `pgallery_title` varchar(255) NOT NULL,
  `pgallery_banner` varchar(255) NOT NULL,
  `pgallery_meta_title` varchar(255) NOT NULL,
  `pgallery_meta_keyword` text NOT NULL,
  `pgallery_meta_description` text NOT NULL,
  `vgallery_title` varchar(255) NOT NULL,
  `vgallery_banner` varchar(255) NOT NULL,
  `vgallery_meta_title` varchar(255) NOT NULL,
  `vgallery_meta_keyword` text NOT NULL,
  `vgallery_meta_description` text NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=latin1;

--
-- Dumping data for table `tbl_page`
--

INSERT INTO `tbl_page` (`id`, `about_title`, `about_content`, `about_banner`, `about_meta_title`, `about_meta_keyword`, `about_meta_description`, `faq_title`, `faq_banner`, `faq_meta_title`, `faq_meta_keyword`, `faq_meta_description`, `blog_title`, `blog_banner`, `blog_meta_title`, `blog_meta_keyword`, `blog_meta_description`, `contact_title`, `contact_banner`, `contact_meta_title`, `contact_meta_keyword`, `contact_meta_description`, `pgallery_title`, `pgallery_banner`, `pgallery_meta_title`, `pgallery_meta_keyword`, `pgallery_meta_description`, `vgallery_title`, `vgallery_banner`, `vgallery_meta_title`, `vgallery_meta_keyword`, `vgallery_meta_description`) VALUES
(1, 'About Us', '<div><b>Welcome to Raiseâ€™N Studio - your one-stop destination for candle, resin, and craft supplies.</b></div><div>We are passionate about helping makers, artists, and small business owners turn their creative dreams into reality.</div><div><br></div><div>At Raiseâ€™N Studio, youâ€™ll find a wide range of premium-quality materials from candle jars, wax, fragrances, and wicks to resin kits, molds, pigments, and accessories Â everything carefully curated to bring out the best in your creations.</div><div><span style=\"background-color: rgb(231, 99, 99);\"><br></span></div><div><span style=\"background-color: rgb(255, 255, 255); color: rgb(231, 99, 99); font-weight: bold;\">Our journey began with a love for handmade art the warmth of candles and the beauty of resin crafts inspired us to build a space where creativity truly shines.</span></div><div><span style=\"background-color: rgb(255, 255, 255); color: rgb(231, 99, 99); font-weight: bold;\">Today, Raiseâ€™N Studio proudly serves crafters across Nepal, providing not just materials but also inspiration, guidance, and community.</span></div><div><br></div><div>We believe every handmade piece tells a story â€” of patience, creativity, and love.</div><div>And weâ€™re here to make that story even more beautiful with our quality supplies and heartfelt service.</div><div>â€Ž</div><div>Â  Â  Â  Â  Â  Â  Â  Â  Â  Â  Â  Â  Â  Â  Â  Â  Â  Â  Â  Â  Â  Â  Â  Â  Â  Â  Â  Â  Â  Â  Â  Â  Â  Â  Â  Â  Â <span style=\"font-family: \"Times New Roman\"; font-weight: bold;\"> Â Create.Inspire.Shine - With Raise\'N StudioÂ </span></div><div>Â  Â  Â  Â  Â  Â  Â  Â  Â  Â  Â  Â  Â  Â  Â  Â  Â  Â  Â  Â  Â  Â  Â  Â  Â  Â  Â  Â  Â  Â  Â  Â  Â  Â  Â  Â  Â  Â  Â  Â  Â  Â  Â Â </div>', 'about-banner.jpg', 'About 8848 Cleaning Service', 'about 8848 cleaning service, cleaning company Kathmandu, professional cleaners Nepal', 'Learn about 8848 Cleaning Service — professional home and office cleaning in Kathmandu with trained staff and easy online booking.', 'FAQ', 'faq-banner.jpg', 'FAQ | 8848 Cleaning Service', 'cleaning FAQ, booking questions, 8848 cleaning service help', 'Frequently asked questions about booking, pricing, and cleaning services with 8848 Cleaning Service.', 'Blog', 'blog-banner.jpg', 'Ecommerce - Blog', '', '', 'Contact Us', 'contact-banner.jpg', 'Contact 8848 Cleaning Service', 'contact 8848 cleaning, cleaning service phone Kathmandu, book cleaning Nepal', 'Contact 8848 Cleaning Service for home and office cleaning in Kathmandu. Call, email, or send a message to book your service.', 'Photo Gallery', 'pgallery-banner.jpg', 'Ecommerce - Photo Gallery', '', '', 'Video Gallery', 'vgallery-banner.jpg', 'Ecommerce - Video Gallery', '', '');

-- --------------------------------------------------------

--
-- Table structure for table `tbl_payment`
--

DROP TABLE IF EXISTS `tbl_payment`;
CREATE TABLE IF NOT EXISTS `tbl_payment` (
  `id` int NOT NULL AUTO_INCREMENT,
  `customer_id` int NOT NULL,
  `customer_name` varchar(255) NOT NULL,
  `customer_email` varchar(255) NOT NULL,
  `payment_date` varchar(50) NOT NULL,
  `txnid` varchar(255) NOT NULL,
  `paid_amount` int NOT NULL,
  `card_number` varchar(50) NOT NULL,
  `card_cvv` varchar(10) NOT NULL,
  `card_month` varchar(10) NOT NULL,
  `card_year` varchar(10) NOT NULL,
  `bank_transaction_info` text NOT NULL,
  `payment_method` varchar(20) NOT NULL,
  `payment_QR` varchar(255) DEFAULT NULL,
  `payment_status` varchar(25) NOT NULL,
  `shipping_status` varchar(20) NOT NULL,
  `payment_id` varchar(255) NOT NULL,
  `subtotal` decimal(10,2) DEFAULT '0.00',
  `discount_type` varchar(20) DEFAULT 'percent',
  `discount_value` decimal(10,2) DEFAULT '0.00',
  `discount_amount` decimal(10,2) DEFAULT '0.00',
  `vat_percent` decimal(10,2) DEFAULT '0.00',
  `vat_amount` decimal(10,2) DEFAULT '0.00',
  `grand_total` decimal(10,2) DEFAULT '0.00',
  `due_amount` decimal(10,2) DEFAULT '0.00',
  `notes` text,
  `created_at` datetime DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL,
  `customer_phone` varchar(20) DEFAULT NULL,
  `service_address` text,
  `preferred_date` date DEFAULT NULL,
  `preferred_time` varchar(30) DEFAULT NULL,
  `booking_status` varchar(25) DEFAULT 'Pending',
  `assignment_status` varchar(25) DEFAULT 'Unassigned',
  `service_lat` decimal(10,7) DEFAULT NULL,
  `service_lng` decimal(10,7) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=94 DEFAULT CHARSET=latin1;

--
-- Dumping data for table `tbl_payment`
--

INSERT INTO `tbl_payment` (`id`, `customer_id`, `customer_name`, `customer_email`, `payment_date`, `txnid`, `paid_amount`, `card_number`, `card_cvv`, `card_month`, `card_year`, `bank_transaction_info`, `payment_method`, `payment_QR`, `payment_status`, `shipping_status`, `payment_id`, `subtotal`, `discount_type`, `discount_value`, `discount_amount`, `vat_percent`, `vat_amount`, `grand_total`, `due_amount`, `notes`, `created_at`, `updated_at`, `customer_phone`, `service_address`, `preferred_date`, `preferred_time`, `booking_status`, `assignment_status`, `service_lat`, `service_lng`) VALUES
(91, 34, 'hala', 'pandey@gmail.com', '2026-07-17 16:50:01', '', 0, '', '', '', '', '', 'enquiry', NULL, 'Pending', 'Pending', 'ORD-20260717165001', 0.00, 'percent', 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, '', '2026-07-17 16:50:01', '2026-07-17 16:50:01', '9999999999', 'Manamaiju\r\nkathmandu', NULL, '', 'Confirmed', 'Assigned', NULL, NULL),
(90, 37, 'ram', 'admin@mail.com', '2026-07-03 17:25:57', '', 0, '', '', '', '', '', 'enquiry', NULL, 'Pending', 'Pending', 'ORD-20260703172557', 0.00, 'percent', 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, '\n', '2026-07-03 17:25:57', '2026-07-03 17:25:57', '9999999999', 'nnbnbn', '2026-07-22', '10', 'Completed', 'Completed', NULL, NULL),
(92, 34, 'salman', 'pandey@gmail.com', '2026-07-21 12:14:04', '', 0, '', '', '', '', '', 'cod', NULL, 'Pending', 'Pending', 'ORD-20260721121404', 3250.00, 'percent', 0.00, 0.00, 0.00, 0.00, 3250.00, 3250.00, '', '2026-07-21 12:14:04', '2026-07-21 12:16:34', '9999999999', 'New Millenium School, Barahi 1st Cross, Kupondole, Lalitpur-10, Lalitpur, Lalitpur Metropolitan City, Lalitpur, Bagamati Province, 00779, Nepal', '2026-07-25', '23:46', 'Confirmed', 'Assigned', 27.6855000, 85.3229000),
(93, 34, 'salman', 'pandey@gmail.com', '2026-07-21 12:38:12', '', 0, '', '', '', '', '', 'esewa', NULL, 'Pending', 'Pending', 'ORD-20260721123812', 360.00, 'percent', 0.00, 0.00, 0.00, 0.00, 360.00, 360.00, 'Area: gggh, ghghgh, ghghgh\r\nniraj: cfv', '2026-07-21 12:38:12', '2026-07-21 12:39:11', '9999999999', 'Gokarneshwar, Gokarneshwar Municipality, Kathmandu, Bagamati Province, 20187, Nepal', '2026-07-22', '14:22', 'Pending', 'Unassigned', 27.7262875, 85.3759059);

-- --------------------------------------------------------

--
-- Table structure for table `tbl_photo`
--

DROP TABLE IF EXISTS `tbl_photo`;
CREATE TABLE IF NOT EXISTS `tbl_photo` (
  `id` int NOT NULL AUTO_INCREMENT,
  `caption` varchar(255) NOT NULL,
  `photo` varchar(255) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=latin1;

--
-- Dumping data for table `tbl_photo`
--

INSERT INTO `tbl_photo` (`id`, `caption`, `photo`) VALUES
(1, 'Photo 1', 'photo-1.jpg'),
(2, 'Photo 2', 'photo-2.jpg'),
(3, 'Photo 3', 'photo-3.jpg'),
(4, 'Photo 4', 'photo-4.jpg'),
(5, 'Photo 5', 'photo-5.jpg'),
(6, 'Photo 6', 'photo-6.jpg');

-- --------------------------------------------------------

--
-- Table structure for table `tbl_post`
--

DROP TABLE IF EXISTS `tbl_post`;
CREATE TABLE IF NOT EXISTS `tbl_post` (
  `post_id` int NOT NULL AUTO_INCREMENT,
  `post_title` varchar(255) NOT NULL,
  `post_slug` varchar(255) NOT NULL,
  `post_content` text NOT NULL,
  `post_date` varchar(255) NOT NULL,
  `photo` varchar(255) NOT NULL,
  `category_id` int NOT NULL,
  `total_view` int NOT NULL,
  `meta_title` varchar(255) NOT NULL,
  `meta_keyword` text NOT NULL,
  `meta_description` text NOT NULL,
  PRIMARY KEY (`post_id`)
) ENGINE=InnoDB AUTO_INCREMENT=12 DEFAULT CHARSET=latin1;

--
-- Dumping data for table `tbl_post`
--

INSERT INTO `tbl_post` (`post_id`, `post_title`, `post_slug`, `post_content`, `post_date`, `photo`, `category_id`, `total_view`, `meta_title`, `meta_keyword`, `meta_description`) VALUES
(9, 'Nostrum copiosae argumentum has', 'nostrum-copiosae-argumentum-has', '<p>Lorem ipsum dolor sit amet, qui case probo velit no, an postea scaevola partiendo mei. Id mea fuisset perpetua referrentur. Ut everti ceteros mei, alii discere eum no, duo id malis iuvaret. Ad sint everti accusam vel, ea viderer suscipiantur pri. Brute option minimum in cum, ignota iuvaret an pro.</p>\r\n\r\n<p>Solum atqui intellegebat mea an. Ne ius alterum aliquam. Ea nec populo aliquid mentitum, vis in meliore atomorum, sanctus consequat vituperatoribus duo ea. Ad doctus pertinacia ius, virtute fuisset id has, eum ut modo principes. Qui eu labore adversarium, oporteat delicata qui ut, an qui meliore principes. Id aliquid dolorum nam.</p>\r\n\r\n<p>Reque pericula philosophia ut mei, volumus eligendi mandamus has an. In nobis consulatu pri, has at timeam scaevola, has simul quaeque et. Te nec sale accumsan. Dolorem prodesset efficiendi sea ea.</p>\r\n\r\n<p>Et habeo modus debitis pri, vel quis fierent albucius ne. Ea animal meliore usu, nec etiam dolorum atomorum at, nam in audire mandamus omittantur. Cu ius dicam officiis molestiae, mea volumus officiis cotidieque no. Ut vel possim interpretaris, idque probatus antiopam has ad. Facilisi qualisque te sea, no dolorum mnesarchum usu.</p>\r\n\r\n<p>Eum tota graeci impetus an, eirmod invenire rationibus ne mel. Ignota habemus eum ex, vis omnesque delicata perpetua an. Sit id modo invidunt sapientem, ne eum vocibus dolores phaedrum. Case praesent appellantur eu per.</p>\r\n', '05-09-2017', 'news-9.jpg', 1, 12, 'Nostrum copiosae argumentum has', '', ''),
(10, 'An labores explicari qui eu', 'an-labores-explicari-qui-eu', '<p>Lorem ipsum dolor sit amet, qui case probo velit no, an postea scaevola partiendo mei. Id mea fuisset perpetua referrentur. Ut everti ceteros mei, alii discere eum no, duo id malis iuvaret. Ad sint everti accusam vel, ea viderer suscipiantur pri. Brute option minimum in cum, ignota iuvaret an pro.</p>\r\n\r\n<p>Solum atqui intellegebat mea an. Ne ius alterum aliquam. Ea nec populo aliquid mentitum, vis in meliore atomorum, sanctus consequat vituperatoribus duo ea. Ad doctus pertinacia ius, virtute fuisset id has, eum ut modo principes. Qui eu labore adversarium, oporteat delicata qui ut, an qui meliore principes. Id aliquid dolorum nam.</p>\r\n\r\n<p>Reque pericula philosophia ut mei, volumus eligendi mandamus has an. In nobis consulatu pri, has at timeam scaevola, has simul quaeque et. Te nec sale accumsan. Dolorem prodesset efficiendi sea ea.</p>\r\n\r\n<p>Et habeo modus debitis pri, vel quis fierent albucius ne. Ea animal meliore usu, nec etiam dolorum atomorum at, nam in audire mandamus omittantur. Cu ius dicam officiis molestiae, mea volumus officiis cotidieque no. Ut vel possim interpretaris, idque probatus antiopam has ad. Facilisi qualisque te sea, no dolorum mnesarchum usu.</p>\r\n\r\n<p>Eum tota graeci impetus an, eirmod invenire rationibus ne mel. Ignota habemus eum ex, vis omnesque delicata perpetua an. Sit id modo invidunt sapientem, ne eum vocibus dolores phaedrum. Case praesent appellantur eu per.</p>', '05-09-2017', 'blog-10-17829922648649.jpg', 8, 4, 'An labores explicari qui eu', '', ''),
(11, 'Lorem ipsum dolor sit amet', 'lorem-ipsum-dolor-sit-amet', '<p>Lorem ipsum dolor sit amet, qui case probo velit no, an postea scaevola partiendo mei. Id mea fuisset perpetua referrentur. Ut everti ceteros mei, alii discere eum no, duo id malis iuvaret. Ad sint everti accusam vel, ea viderer suscipiantur pri. Brute option minimum in cum, ignota iuvaret an pro.</p>\r\n\r\n<p>Solum atqui intellegebat mea an. Ne ius alterum aliquam. Ea nec populo aliquid mentitum, vis in meliore atomorum, sanctus consequat vituperatoribus duo ea. Ad doctus pertinacia ius, virtute fuisset id has, eum ut modo principes. Qui eu labore adversarium, oporteat delicata qui ut, an qui meliore principes. Id aliquid dolorum nam.</p>\r\n\r\n<p>Reque pericula philosophia ut mei, volumus eligendi mandamus has an. In nobis consulatu pri, has at timeam scaevola, has simul quaeque et. Te nec sale accumsan. Dolorem prodesset efficiendi sea ea.</p>\r\n\r\n<p>Et habeo modus debitis pri, vel quis fierent albucius ne. Ea animal meliore usu, nec etiam dolorum atomorum at, nam in audire mandamus omittantur. Cu ius dicam officiis molestiae, mea volumus officiis cotidieque no. Ut vel possim interpretaris, idque probatus antiopam has ad. Facilisi qualisque te sea, no dolorum mnesarchum usu.</p>\r\n\r\n<p>Eum tota graeci impetus an, eirmod invenire rationibus ne mel. Ignota habemus eum ex, vis omnesque delicata perpetua an. Sit id modo invidunt sapientem, ne eum vocibus dolores phaedrum. Case praesent appellantur eu per.</p>', '05-09-2017', 'blog-11-17829922559555.jpg', 6, 18, 'Lorem ipsum dolor sit amet', '', '');

-- --------------------------------------------------------

--
-- Table structure for table `tbl_product`
--

DROP TABLE IF EXISTS `tbl_product`;
CREATE TABLE IF NOT EXISTS `tbl_product` (
  `p_id` int NOT NULL AUTO_INCREMENT,
  `p_name` varchar(255) NOT NULL,
  `p_old_price` varchar(10) NOT NULL,
  `p_current_price` varchar(10) NOT NULL,
  `p_qty` int NOT NULL,
  `p_featured_photo` varchar(255) NOT NULL,
  `p_description` text NOT NULL,
  `p_short_description` text NOT NULL,
  `p_feature` text NOT NULL,
  `p_condition` text NOT NULL,
  `p_return_policy` text NOT NULL,
  `p_total_view` int NOT NULL,
  `p_is_featured` int NOT NULL,
  `p_is_active` int NOT NULL,
  `ecat_id` int NOT NULL,
  `staff_commission_type` varchar(20) NOT NULL DEFAULT 'inherit',
  `staff_commission_value` decimal(10,2) NOT NULL DEFAULT '0.00',
  PRIMARY KEY (`p_id`)
) ENGINE=InnoDB AUTO_INCREMENT=172 DEFAULT CHARSET=latin1;

--
-- Dumping data for table `tbl_product`
--

INSERT INTO `tbl_product` (`p_id`, `p_name`, `p_old_price`, `p_current_price`, `p_qty`, `p_featured_photo`, `p_description`, `p_short_description`, `p_feature`, `p_condition`, `p_return_policy`, `p_total_view`, `p_is_featured`, `p_is_active`, `ecat_id`, `staff_commission_type`, `staff_commission_value`) VALUES
(108, 'Daisy Floral Mold ', '350', '300', 15, 'product-featured-108.jpg', '<p>Add a touch of elegance to your crafts with our Daisy Flower Silicone Mold, designed to create perfectly detailed daisy-shaped pieces every time. Made from high-quality, flexible silicone, this mold captures each petal with precisionâ€”giving your resin, wax, soap, or plaster projects a realistic floral finish.<br></p>', 'High-quality Daisy Flower Silicone Mold for resin, wax, soap & plaster crafting. Flexible, durable, and easy to demoldâ€”perfect for creating detailed daisy-shaped charms, embeds, and dÃ©cor pieces.\r\n', '<p>âœ” Features:</p><p><span class=\"Apple-tab-span\" style=\"white-space:pre\">	</span>â€¢<span class=\"Apple-tab-span\" style=\"white-space:pre\">	</span>Premium high-quality silicone</p><p><span class=\"Apple-tab-span\" style=\"white-space:pre\">	</span>â€¢<span class=\"Apple-tab-span\" style=\"white-space:pre\">	</span>Durable, flexible & long-lasting</p><p><span class=\"Apple-tab-span\" style=\"white-space:pre\">	</span>â€¢<span class=\"Apple-tab-span\" style=\"white-space:pre\">	</span>Compatible with resin, wax, soap, plaster & more</p><p><span class=\"Apple-tab-span\" style=\"white-space:pre\">	</span>â€¢<span class=\"Apple-tab-span\" style=\"white-space:pre\">	</span>Sharp daisy petal detailing for a clean finish</p><p><span class=\"Apple-tab-span\" style=\"white-space:pre\">	</span>â€¢<span class=\"Apple-tab-span\" style=\"white-space:pre\">	</span>Easy to demold and clean</p><p><span class=\"Apple-tab-span\" style=\"white-space:pre\">	</span>â€¢<span class=\"Apple-tab-span\" style=\"white-space:pre\">	</span>Does not deform over time</p><p><br></p><p>âœ” Perfect For:</p><p><span class=\"Apple-tab-span\" style=\"white-space:pre\">	</span>â€¢<span class=\"Apple-tab-span\" style=\"white-space:pre\">	</span>Resin charms & keychains</p><p><span class=\"Apple-tab-span\" style=\"white-space:pre\">	</span>â€¢<span class=\"Apple-tab-span\" style=\"white-space:pre\">	</span>Candle embeds and wax dÃ©cor</p><p><span class=\"Apple-tab-span\" style=\"white-space:pre\">	</span>â€¢<span class=\"Apple-tab-span\" style=\"white-space:pre\">	</span>Soap making</p><p><span class=\"Apple-tab-span\" style=\"white-space:pre\">	</span>â€¢<span class=\"Apple-tab-span\" style=\"white-space:pre\">	</span>Mini dÃ©cor pieces</p><p><span class=\"Apple-tab-span\" style=\"white-space:pre\">	</span>â€¢<span class=\"Apple-tab-span\" style=\"white-space:pre\">	</span>DIY gifts & craft projects</p>', '', '', 0, 1, 1, 83, 'inherit', 0.00),
(109, '3 in 1 Floral Topping mold', '450', '380', 12, 'product-featured-109.jpg', '', '', '<p>âœ” Features:</p><p><span class=\"Apple-tab-span\" style=\"white-space:pre\">	</span>â€¢<span class=\"Apple-tab-span\" style=\"white-space:pre\">	</span>Premium high-quality silicone</p><p><span class=\"Apple-tab-span\" style=\"white-space:pre\">	</span>â€¢<span class=\"Apple-tab-span\" style=\"white-space:pre\">	</span>Durable, flexible & long-lasting</p><p><span class=\"Apple-tab-span\" style=\"white-space:pre\">	</span>â€¢<span class=\"Apple-tab-span\" style=\"white-space:pre\">	</span>Compatible with resin, wax, soap, plaster & more</p><p><span class=\"Apple-tab-span\" style=\"white-space:pre\">	</span>â€¢<span class=\"Apple-tab-span\" style=\"white-space:pre\">	</span>Sharp daisy petal detailing for a clean finish</p><p><span class=\"Apple-tab-span\" style=\"white-space:pre\">	</span>â€¢<span class=\"Apple-tab-span\" style=\"white-space:pre\">	</span>Easy to demold and clean</p><p><span class=\"Apple-tab-span\" style=\"white-space:pre\">	</span>â€¢<span class=\"Apple-tab-span\" style=\"white-space:pre\">	</span>Does not deform over time</p><p><br></p><p>âœ” Perfect For:</p><p><span class=\"Apple-tab-span\" style=\"white-space:pre\">	</span>â€¢<span class=\"Apple-tab-span\" style=\"white-space:pre\">	</span>Resin charms & keychains</p><p><span class=\"Apple-tab-span\" style=\"white-space:pre\">	</span>â€¢<span class=\"Apple-tab-span\" style=\"white-space:pre\">	</span>Candle embeds and wax dÃ©cor</p><p><span class=\"Apple-tab-span\" style=\"white-space:pre\">	</span>â€¢<span class=\"Apple-tab-span\" style=\"white-space:pre\">	</span>Soap making</p><p><span class=\"Apple-tab-span\" style=\"white-space:pre\">	</span>â€¢<span class=\"Apple-tab-span\" style=\"white-space:pre\">	</span>Mini dÃ©cor pieces</p><p><span class=\"Apple-tab-span\" style=\"white-space:pre\">	</span>â€¢<span class=\"Apple-tab-span\" style=\"white-space:pre\">	</span>DIY gifts & craft projects</p>', '', '', 0, 1, 1, 82, 'inherit', 0.00),
(110, 'Peony flower mold ', '600', '550', 10, 'product-featured-110.jpg', '', '', '', '', '', 0, 1, 1, 83, 'inherit', 0.00),
(111, 'Mini Peony Mold ', '500', '450', 10, 'product-featured-111.jpg', '', '', '', '', '', 0, 1, 1, 83, 'inherit', 0.00),
(112, 'Multiple leaf Mold ', '580', '480', 10, 'product-featured-112.jpg', '', '', '', '', '', 0, 1, 1, 84, 'inherit', 0.00),
(113, 'Bubble Mold ', '550', '450', 20, 'product-featured-113.jpeg', '', '', '', '', '', 0, 1, 1, 85, 'inherit', 0.00),
(114, 'Mini Bubble Mold ', '150', '100', 20, 'product-featured-114.jpeg', '', '', '', '', '', 0, 1, 1, 85, 'inherit', 0.00),
(116, 'Mini Heart Moulds ', '450', '380', 5, 'product-featured-116.jpeg', '', '', '', '', '', 0, 1, 1, 86, 'inherit', 0.00),
(117, 'Heart Pattern Mould ', '450', '380', 5, 'product-featured-117.jpeg', '', '', '', '', '', 0, 1, 1, 86, 'inherit', 0.00),
(118, 'Wick Scissors', '850', '750', 10, 'product-featured-118.jpeg', '', '', '', '', '', 0, 1, 1, 87, 'inherit', 0.00),
(119, 'Two Dog Mould ', '1250', '1050', 3, 'product-featured-119.jpeg', '', '', '', '', '', 0, 1, 1, 85, 'inherit', 0.00),
(120, 'Baby Sleeping Mould', '750', '645', 5, 'product-featured-120.jpeg', '', '', '', '', '', 0, 1, 1, 85, 'inherit', 0.00),
(121, 'Baby Angel Mould ', '650', '525', 5, 'product-featured-121.jpeg', '', '', '', '', '', 0, 1, 1, 85, 'inherit', 0.00),
(122, 'Birthday Head Teddy Mould ', '400', '350', 6, 'product-featured-122.jpeg', '', '', '', '', '', 0, 1, 1, 88, 'inherit', 0.00),
(123, 'Teddy Mould ', '450', '380', 5, 'product-featured-123.jpeg', '', '', '', '', '', 0, 1, 1, 88, 'inherit', 0.00),
(124, 'Rose Heart Mould ', '650', '550', 5, 'product-featured-124.jpeg', '', '', '', '', '', 0, 1, 1, 86, 'inherit', 0.00),
(125, 'Heart Rose Big ', '750', '650', 5, 'product-featured-125.jpeg', '', '', '', '', '', 0, 1, 1, 86, 'inherit', 0.00),
(126, 'Sheep Mould ', '900', '875', 5, 'product-featured-126.jpeg', '', '', '', '', '', 0, 1, 1, 85, 'inherit', 0.00),
(127, 'Girl Candle Mould ', '1050', '950', 3, 'product-featured-127.jpeg', '', '', '', '', '', 0, 1, 1, 85, 'inherit', 0.00),
(128, 'Teddy with bow ', '750', '665', 3, 'product-featured-128.jpeg', '', '', '', '', '', 0, 1, 1, 88, 'inherit', 0.00),
(129, 'Heart Rabbit ', '1450', '1250', 3, 'product-featured-129.jpeg', '', '', '', '', '', 0, 1, 1, 88, 'inherit', 0.00),
(131, 'Teddy layer Mould ', '950', '790', 3, 'product-featured-131.jpeg', '', '', '', '', '', 0, 1, 1, 85, 'inherit', 0.00),
(132, 'The Venus Candle Mould ', '1040', '945', 3, 'product-featured-132.jpeg', '', '', '', '', '', 0, 1, 1, 85, 'inherit', 0.00),
(133, 'The Venus Mould ', '1050', '945', 3, 'product-featured-133.jpeg', '', '', '', '', '', 0, 1, 1, 85, 'inherit', 0.00),
(134, 'Big Rose Mould ', '1950', '1750', 3, 'product-featured-134.jpeg', '', '', '', '', '', 0, 1, 1, 83, 'inherit', 0.00),
(135, 'Layer Flower Mould ', '550', '495', 3, 'product-featured-135.jpeg', '', '', '', '', '', 0, 1, 1, 83, 'inherit', 0.00),
(136, 'Flower Layer Mould ', '550', '496', 3, 'product-featured-136.jpeg', '', '', '', '', '', 0, 1, 1, 83, 'inherit', 0.00),
(137, 'Big Teddy Rose Mould ', '1050', '975', 3, 'product-featured-137.jpeg', '', '', '', '', '', 0, 1, 1, 88, 'inherit', 0.00),
(138, 'Rose Bouquet Mould ', '1450', '1250', 3, 'product-featured-138.jpeg', '', '', '', '', '', 0, 1, 1, 83, 'inherit', 0.00),
(139, 'Tulip bouquet Mould ', '1260', '1050', 3, 'product-featured-139.jpeg', '', '', '', '', '', 0, 1, 1, 83, 'inherit', 0.00),
(140, 'Pig Layer Mould ', '1060', '950', 3, 'product-featured-140.jpeg', '', '', '', '', '', 0, 1, 1, 88, 'inherit', 0.00),
(141, 'Teddy Hug Mould ', '750', '685', 3, 'product-featured-141.jpeg', '', '', '', '', '', 0, 1, 1, 88, 'inherit', 0.00),
(142, '3D Rose Mould ', '650', '550', 8, 'product-featured-142.jpeg', '', '', '', '', '', 0, 1, 1, 83, 'inherit', 0.00),
(143, 'Setting teddy Mould ', '1750', '1450', 3, 'product-featured-143.jpeg', '', '', '', '', '', 0, 1, 1, 88, 'inherit', 0.00),
(144, 'Blind Fold Lady ', '1060', '960', 4, 'product-featured-144.jpeg', '', '', '', '', '', 0, 1, 1, 85, 'inherit', 0.00),
(145, 'Rose Mould ', '750', '650', 3, 'product-featured-145.jpeg', '', '', '', '', '', 0, 1, 1, 83, 'inherit', 0.00),
(146, 'Flower Bouquet Mould ', '1350', '1150', 3, 'product-featured-146.jpeg', '', '', '', '', '', 0, 1, 1, 83, 'inherit', 0.00),
(147, 'Two Dog  Mould ', '1050', '965', 3, 'product-featured-147.jpeg', '', '', '', '', '', 0, 1, 1, 85, 'inherit', 0.00),
(148, 'Rabbit long Ear Mould ', '1650', '1350', 3, 'product-featured-148.jpeg', '', '', '', '', '', 0, 1, 1, 85, 'inherit', 0.00),
(149, 'Cute Sheep Mould ', '960', '875', 8, 'product-featured-149.jpeg', '', '', '', '', '', 0, 1, 1, 85, 'inherit', 0.00),
(150, 'Teddy Holding Baby ', '950', '725', 3, 'product-featured-150.jpeg', '', '', '', '', '', 0, 1, 1, 88, 'inherit', 0.00),
(151, 'Couple Hug Mould ', '950', '865', 4, 'product-featured-151.jpeg', '', '', '', '', '', 0, 1, 1, 85, 'inherit', 0.00),
(152, '3 layer Cat ', '960', '885', 4, 'product-featured-152.jpeg', '', '', '', '', '', 0, 1, 1, 85, 'inherit', 0.00),
(153, 'Teddy Rose Heart Mould ', '850', '665', 5, 'product-featured-153.jpeg', '', '', '', '', '', 0, 1, 1, 88, 'inherit', 0.00),
(154, 'Big swirl Aesthetic Mould ', '1450', '1125', 3, 'product-featured-154.jpeg', '', '', '', '', '', 0, 1, 1, 85, 'inherit', 0.00),
(155, 'Small Swirl Mould ', '750', '580', 3, 'product-featured-155.jpeg', '', '', '', '', '', 0, 1, 1, 85, 'inherit', 0.00),
(156, 'Love Heart ', '1650', '1450', 3, 'product-featured-156.jpeg', '', '', '', '', '', 0, 1, 1, 86, 'inherit', 0.00),
(157, 'Lotus Mould ', '850', '750', 3, 'product-featured-157.jpeg', '', '', '', '', '', 0, 1, 1, 83, 'inherit', 0.00),
(158, 'Heart Rose Mould ', '650', '550', 3, 'product-featured-158.jpeg', '', '', '', '', '', 0, 1, 1, 86, 'inherit', 0.00),
(159, 'Two Teddy Holding Bouquet ', '1550', '1350', 3, 'product-featured-159.jpeg', '', '', '', '', '', 0, 1, 1, 88, 'inherit', 0.00),
(160, 'Braided Aesthetic Mould ', '1250', '950', 3, 'product-featured-160.jpeg', '', '', '', '', '', 0, 1, 1, 85, 'inherit', 0.00),
(161, 'Braided Mould ', '650', '550', 3, 'product-featured-161.jpeg', '', '', '', '', '', 0, 1, 1, 85, 'inherit', 0.00),
(162, 'Succulent Turtle Mould ', '1250', '925', 3, 'product-featured-162.jpeg', '', '', '', '', '', 0, 1, 1, 85, 'inherit', 0.00),
(163, 'Pig Mould ', '450', '360', 2, 'product-featured-163.jpeg', '', '', '', '', '', 2, 1, 1, 85, 'inherit', 0.00),
(164, 'Panda Mould ', '450', '360', 5, 'product-featured-164.jpg', '', '', '', '', '', 35, 1, 1, 85, 'inherit', 0.00),
(165, 'Cat Mould ', '450', '360', 2, 'product-featured-165.png', '', '', '', '', '', 5, 1, 1, 85, 'inherit', 0.00),
(166, 'Rose container wuth lid ', '1350', '1135', 3, 'product-featured-166.jpeg', '', '', '', '', '', 4, 1, 1, 86, 'inherit', 0.00),
(167, 'Dress Mould ', '1250', '950', 5, 'product-featured-167.jpeg', '', '', '', '', '', 7, 1, 1, 85, 'inherit', 0.00),
(168, 'Dress Mould ', '1250', '950', 3, 'product-featured-168.jpeg', '', '', '', '', '', 0, 1, 1, 85, 'inherit', 0.00),
(169, 'Dress Mould ', '1250', '950', 3, 'product-featured-169.jpeg', '', '', '', '', '', 4, 1, 1, 85, 'inherit', 0.00),
(170, 'Dress Mould ', '1250', '950', 3, 'product-featured-170.jpeg', '', '', '', '', '', 1, 1, 1, 85, 'inherit', 0.00),
(171, 'niraj', '450', '360', 2, 'product-featured-171.jpg', '<li data-section-id=\"uo526x\" data-start=\"334\" data-end=\"345\">Material:<span aria-hidden=\"true\" class=\"PDq2pG_selectionAnchor\"></span>\r\n</li><li data-section-id=\"1uwkdrj\" data-start=\"346\" data-end=\"354\">\r\nColor:\r\n</li><li data-section-id=\"178ffhz\" data-start=\"355\" data-end=\"362\">\r\nSize:\r\n</li><li data-section-id=\"5erv02\" data-start=\"363\" data-end=\"372\">\r\nWeight:\r\n</li><li data-section-id=\"md9n91\" data-start=\"373\" data-end=\"384\">\r\nFeatures:\r\n</li><li data-section-id=\"1j1e9hj\" data-start=\"385\" data-end=\"404\">\r\nPackage Includes:\r\n</li><p>\r\n\r\n\r\n\r\n\r\n\r\n</p><li data-section-id=\"1oioglh\" data-start=\"405\" data-end=\"432\" data-is-last-node=\"\">\r\nWarranty: (if applicable)</li>', '<li data-section-id=\"uo526x\" data-start=\"334\" data-end=\"345\">Material:<span aria-hidden=\"true\" class=\"PDq2pG_selectionAnchor\"></span>\r\n</li><li data-section-id=\"1uwkdrj\" data-start=\"346\" data-end=\"354\">\r\nColor:\r\n</li><li data-section-id=\"178ffhz\" data-start=\"355\" data-end=\"362\">\r\nSize:\r\n</li><li data-section-id=\"5erv02\" data-start=\"363\" data-end=\"372\">\r\nWeight:\r\n</li><li data-section-id=\"md9n91\" data-start=\"373\" data-end=\"384\">\r\nFeatures:\r\n</li><li data-section-id=\"1j1e9hj\" data-start=\"385\" data-end=\"404\">\r\nPackage Includes:\r\n</li><p>\r\n\r\n\r\n\r\n\r\n\r\n</p><li data-section-id=\"1oioglh\" data-start=\"405\" data-end=\"432\" data-is-last-node=\"\">\r\nWarranty: (if applicable)</li>', '<li data-section-id=\"uo526x\" data-start=\"334\" data-end=\"345\">Material:<span aria-hidden=\"true\" class=\"PDq2pG_selectionAnchor\"></span>\r\n</li><li data-section-id=\"1uwkdrj\" data-start=\"346\" data-end=\"354\">\r\nColor:\r\n</li><li data-section-id=\"178ffhz\" data-start=\"355\" data-end=\"362\">\r\nSize:\r\n</li><li data-section-id=\"5erv02\" data-start=\"363\" data-end=\"372\">\r\nWeight:\r\n</li><li data-section-id=\"md9n91\" data-start=\"373\" data-end=\"384\">\r\nFeatures:\r\n</li><li data-section-id=\"1j1e9hj\" data-start=\"385\" data-end=\"404\">\r\nPackage Includes:\r\n</li><p>\r\n\r\n\r\n\r\n\r\n\r\n</p><li data-section-id=\"1oioglh\" data-start=\"405\" data-end=\"432\" data-is-last-node=\"\">\r\nWarranty: (if applicable)</li>', '', '', 8, 1, 1, 83, 'inherit', 0.00);

-- --------------------------------------------------------

--
-- Table structure for table `tbl_product_color`
--

DROP TABLE IF EXISTS `tbl_product_color`;
CREATE TABLE IF NOT EXISTS `tbl_product_color` (
  `id` int NOT NULL AUTO_INCREMENT,
  `color_id` int NOT NULL,
  `p_id` int NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=286 DEFAULT CHARSET=latin1;

--
-- Dumping data for table `tbl_product_color`
--

INSERT INTO `tbl_product_color` (`id`, `color_id`, `p_id`) VALUES
(69, 1, 4),
(70, 4, 4),
(77, 6, 6),
(82, 2, 12),
(83, 9, 13),
(84, 3, 14),
(85, 2, 15),
(86, 6, 15),
(87, 3, 16),
(88, 3, 17),
(89, 2, 18),
(90, 3, 19),
(91, 1, 20),
(92, 8, 21),
(93, 2, 22),
(94, 2, 23),
(95, 2, 25),
(96, 5, 26),
(97, 2, 27),
(98, 4, 27),
(99, 5, 28),
(100, 7, 29),
(101, 10, 30),
(102, 11, 31),
(103, 14, 32),
(105, 2, 34),
(106, 1, 35),
(107, 3, 36),
(109, 6, 38),
(110, 2, 39),
(111, 11, 42),
(149, 3, 10),
(150, 6, 9),
(151, 3, 8),
(152, 7, 7),
(159, 2, 77),
(163, 17, 79),
(164, 2, 78),
(167, 3, 80),
(168, 2, 81),
(172, 1, 82),
(173, 2, 82),
(174, 4, 82),
(281, 2, 118),
(282, 20, 118),
(283, 30, 118),
(284, 31, 118),
(285, 2, 170);

-- --------------------------------------------------------

--
-- Table structure for table `tbl_product_photo`
--

DROP TABLE IF EXISTS `tbl_product_photo`;
CREATE TABLE IF NOT EXISTS `tbl_product_photo` (
  `pp_id` int NOT NULL AUTO_INCREMENT,
  `photo` varchar(255) NOT NULL,
  `p_id` int NOT NULL,
  PRIMARY KEY (`pp_id`)
) ENGINE=InnoDB AUTO_INCREMENT=208 DEFAULT CHARSET=latin1;

--
-- Dumping data for table `tbl_product_photo`
--

INSERT INTO `tbl_product_photo` (`pp_id`, `photo`, `p_id`) VALUES
(142, '142.jpg', 108),
(143, '143.jpg', 108),
(144, '144.jpg', 109),
(145, '145.jpg', 110),
(146, '146.jpg', 111),
(147, '147.jpg', 111),
(148, '148.jpg', 112),
(149, '149.jpg', 112),
(150, '150.jpg', 112),
(151, '151.jpeg', 113),
(152, '152.jpeg', 114),
(153, '153.jpeg', 114),
(155, '155.jpeg', 116),
(156, '156.jpeg', 117),
(157, '157.jpeg', 118),
(158, '158.jpeg', 118),
(159, '159.jpeg', 118),
(160, '160.jpeg', 118),
(161, '161.jpeg', 118),
(162, '162.jpeg', 120),
(163, '163.jpeg', 121),
(164, '164.jpeg', 123),
(165, '165.jpeg', 126),
(166, '166.jpeg', 128),
(167, '167.jpeg', 129),
(170, '170.jpeg', 132),
(171, '171.jpeg', 133),
(172, '172.jpeg', 134),
(173, '173.jpeg', 134),
(174, '174.jpeg', 134),
(175, '175.jpeg', 138),
(176, '176.jpeg', 139),
(177, '177.jpeg', 140),
(178, '178.jpeg', 142),
(179, '179.jpeg', 143),
(180, '180.jpeg', 143),
(181, '181.jpeg', 144),
(182, '182.jpeg', 145),
(183, '183.jpeg', 146),
(184, '184.jpeg', 147),
(185, '185.jpeg', 149),
(186, '186.jpeg', 150),
(187, '187.jpeg', 151),
(188, '188.jpeg', 153),
(189, '189.jpeg', 154),
(190, '190.jpeg', 155),
(191, '191.jpeg', 157),
(192, '192.jpeg', 157),
(193, '193.jpeg', 158),
(194, '194.jpeg', 158),
(195, '195.jpeg', 160),
(196, '196.jpeg', 161),
(197, '197.jpeg', 162),
(198, '198.jpeg', 162),
(199, '199.jpeg', 166),
(200, '200.jpg', 164),
(201, '201.png', 164),
(202, '202.jpg', 164),
(203, '1783397820_2f4a526a.jpg', 171),
(204, '1783397820_0424504c.jpg', 171),
(205, '1783397820_56b9d6aa.jpeg', 171),
(206, '206.jpg', 171),
(207, '207.jpg', 171);

-- --------------------------------------------------------

--
-- Table structure for table `tbl_product_size`
--

DROP TABLE IF EXISTS `tbl_product_size`;
CREATE TABLE IF NOT EXISTS `tbl_product_size` (
  `id` int NOT NULL AUTO_INCREMENT,
  `size_id` int NOT NULL,
  `p_id` int NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=467 DEFAULT CHARSET=latin1;

--
-- Dumping data for table `tbl_product_size`
--

INSERT INTO `tbl_product_size` (`id`, `size_id`, `p_id`) VALUES
(44, 1, 6),
(56, 8, 12),
(57, 9, 12),
(58, 10, 12),
(59, 11, 12),
(60, 12, 12),
(61, 13, 12),
(62, 9, 13),
(63, 11, 13),
(64, 13, 13),
(65, 15, 13),
(66, 9, 14),
(67, 11, 14),
(68, 12, 14),
(69, 13, 14),
(70, 9, 15),
(71, 11, 15),
(72, 13, 15),
(73, 15, 16),
(74, 16, 16),
(75, 17, 16),
(76, 16, 17),
(77, 17, 17),
(78, 14, 18),
(79, 15, 18),
(80, 16, 18),
(81, 17, 18),
(82, 15, 19),
(83, 16, 19),
(84, 17, 19),
(85, 14, 20),
(86, 15, 20),
(87, 17, 20),
(88, 15, 21),
(89, 17, 21),
(90, 15, 22),
(91, 16, 22),
(92, 17, 22),
(93, 15, 23),
(94, 16, 23),
(95, 17, 23),
(96, 18, 25),
(97, 19, 25),
(98, 20, 25),
(99, 21, 25),
(100, 19, 26),
(101, 21, 26),
(102, 22, 26),
(103, 23, 26),
(104, 19, 27),
(105, 20, 27),
(106, 21, 27),
(107, 22, 27),
(108, 19, 28),
(109, 20, 28),
(110, 21, 28),
(111, 19, 29),
(112, 20, 29),
(113, 22, 29),
(114, 1, 30),
(115, 2, 30),
(116, 3, 30),
(117, 4, 30),
(118, 23, 31),
(119, 26, 32),
(123, 2, 34),
(124, 2, 35),
(125, 2, 36),
(126, 3, 36),
(129, 2, 38),
(130, 3, 38),
(131, 4, 38),
(132, 5, 38),
(133, 27, 39),
(134, 8, 42),
(210, 3, 10),
(211, 4, 10),
(212, 5, 10),
(213, 6, 10),
(214, 3, 9),
(215, 4, 9),
(216, 3, 8),
(217, 4, 8),
(218, 2, 7),
(219, 3, 7),
(220, 4, 7),
(249, 1, 79),
(250, 2, 79),
(251, 3, 79),
(252, 1, 78),
(253, 2, 78),
(254, 3, 78),
(255, 4, 78),
(256, 5, 78),
(259, 26, 80),
(262, 3, 82),
(263, 4, 82),
(463, 48, 119),
(464, 8, 124),
(465, 9, 125),
(466, 8, 170);

-- --------------------------------------------------------

--
-- Table structure for table `tbl_rating`
--

DROP TABLE IF EXISTS `tbl_rating`;
CREATE TABLE IF NOT EXISTS `tbl_rating` (
  `rt_id` int NOT NULL AUTO_INCREMENT,
  `p_id` int NOT NULL,
  `cust_id` int NOT NULL,
  `comment` text NOT NULL,
  `rating` int NOT NULL,
  PRIMARY KEY (`rt_id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `tbl_service`
--

DROP TABLE IF EXISTS `tbl_service`;
CREATE TABLE IF NOT EXISTS `tbl_service` (
  `id` int NOT NULL AUTO_INCREMENT,
  `title` varchar(255) NOT NULL,
  `content` text NOT NULL,
  `photo` varchar(255) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=12 DEFAULT CHARSET=latin1;

--
-- Dumping data for table `tbl_service`
--

INSERT INTO `tbl_service` (`id`, `title`, `content`, `photo`) VALUES
(5, 'Easy Returns', 'Return any item before 15 days!', 'service-5.png'),
(7, 'Fast Shipping', 'Items are shipped within 24 hours.', 'service-7.png'),
(9, 'Secure Checkout', 'Providing Secure Checkout Options for all', 'service-9.png'),
(11, '100% satisfaction ', 'customer\'s satisfaction ', 'service-11.png');

-- --------------------------------------------------------

--
-- Table structure for table `tbl_settings`
--

DROP TABLE IF EXISTS `tbl_settings`;
CREATE TABLE IF NOT EXISTS `tbl_settings` (
  `id` int NOT NULL AUTO_INCREMENT,
  `logo` varchar(255) NOT NULL,
  `site_name` varchar(255) NOT NULL DEFAULT '8848 Cleaning Service',
  `favicon` varchar(255) NOT NULL,
  `footer_about` text NOT NULL,
  `footer_copyright` text NOT NULL,
  `contact_address` text NOT NULL,
  `contact_email` varchar(50) NOT NULL,
  `contact_phone` varchar(50) NOT NULL,
  `contact_fax` varchar(50) NOT NULL,
  `contact_map_iframe` text NOT NULL,
  `receive_email` varchar(50) NOT NULL,
  `receive_email_subject` varchar(50) NOT NULL,
  `receive_email_thank_you_message` text NOT NULL,
  `forget_password_message` text NOT NULL,
  `total_recent_post_footer` int NOT NULL,
  `total_popular_post_footer` int NOT NULL,
  `total_recent_post_sidebar` int NOT NULL,
  `total_popular_post_sidebar` int NOT NULL,
  `total_featured_product_home` int NOT NULL,
  `total_latest_product_home` int NOT NULL,
  `total_popular_product_home` int NOT NULL,
  `meta_title_home` text NOT NULL,
  `meta_keyword_home` text NOT NULL,
  `meta_description_home` text NOT NULL,
  `banner_login` varchar(100) NOT NULL,
  `banner_registration` varchar(100) NOT NULL,
  `banner_forget_password` varchar(255) NOT NULL,
  `banner_reset_password` varchar(255) NOT NULL,
  `banner_search` text NOT NULL,
  `banner_cart` text NOT NULL,
  `banner_checkout` text NOT NULL,
  `banner_product_category` text NOT NULL,
  `banner_blog` text NOT NULL,
  `cta_title` text NOT NULL,
  `cta_content` text NOT NULL,
  `cta_read_more_text` text NOT NULL,
  `cta_read_more_url` text NOT NULL,
  `cta_photo` varchar(255) NOT NULL,
  `featured_product_title` text NOT NULL,
  `featured_product_subtitle` text NOT NULL,
  `latest_product_title` text NOT NULL,
  `latest_product_subtitle` text NOT NULL,
  `popular_product_title` text NOT NULL,
  `popular_product_subtitle` text NOT NULL,
  `testimonial_title` text NOT NULL,
  `testimonial_subtitle` text NOT NULL,
  `testimonial_photo` text NOT NULL,
  `blog_title` text NOT NULL,
  `blog_subtitle` text NOT NULL,
  `newsletter_text` text NOT NULL,
  `paypal_email` text NOT NULL,
  `stripe_public_key` varchar(255) NOT NULL,
  `stripe_secret_key` varchar(255) NOT NULL,
  `bank_detail` text NOT NULL,
  `before_head` text NOT NULL,
  `after_body` text NOT NULL,
  `before_body` text NOT NULL,
  `home_service_on_off` int NOT NULL,
  `home_welcome_on_off` int NOT NULL,
  `home_featured_product_on_off` int NOT NULL,
  `home_latest_product_on_off` int NOT NULL,
  `home_popular_product_on_off` int NOT NULL,
  `home_testimonial_on_off` int NOT NULL,
  `home_blog_on_off` int NOT NULL,
  `newsletter_on_off` int NOT NULL,
  `ads_above_welcome_on_off` int NOT NULL,
  `ads_above_featured_product_on_off` int NOT NULL,
  `ads_above_latest_product_on_off` int NOT NULL,
  `ads_above_popular_product_on_off` int NOT NULL,
  `ads_above_testimonial_on_off` int NOT NULL,
  `ads_category_sidebar_on_off` int NOT NULL,
  `default_staff_commission_type` varchar(20) DEFAULT 'percent',
  `default_staff_commission_value` decimal(10,2) DEFAULT '35.00',
  `marquee_on_off` tinyint NOT NULL DEFAULT '1',
  `marquee_notices` text,
  `invoice_vat_no` varchar(100) NOT NULL DEFAULT '',
  `invoice_due_days` int NOT NULL DEFAULT '30',
  `invoice_footer_note` text,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=latin1;

--
-- Dumping data for table `tbl_settings`
--

INSERT INTO `tbl_settings` (`id`, `logo`, `site_name`, `favicon`, `footer_about`, `footer_copyright`, `contact_address`, `contact_email`, `contact_phone`, `contact_fax`, `contact_map_iframe`, `receive_email`, `receive_email_subject`, `receive_email_thank_you_message`, `forget_password_message`, `total_recent_post_footer`, `total_popular_post_footer`, `total_recent_post_sidebar`, `total_popular_post_sidebar`, `total_featured_product_home`, `total_latest_product_home`, `total_popular_product_home`, `meta_title_home`, `meta_keyword_home`, `meta_description_home`, `banner_login`, `banner_registration`, `banner_forget_password`, `banner_reset_password`, `banner_search`, `banner_cart`, `banner_checkout`, `banner_product_category`, `banner_blog`, `cta_title`, `cta_content`, `cta_read_more_text`, `cta_read_more_url`, `cta_photo`, `featured_product_title`, `featured_product_subtitle`, `latest_product_title`, `latest_product_subtitle`, `popular_product_title`, `popular_product_subtitle`, `testimonial_title`, `testimonial_subtitle`, `testimonial_photo`, `blog_title`, `blog_subtitle`, `newsletter_text`, `paypal_email`, `stripe_public_key`, `stripe_secret_key`, `bank_detail`, `before_head`, `after_body`, `before_body`, `home_service_on_off`, `home_welcome_on_off`, `home_featured_product_on_off`, `home_latest_product_on_off`, `home_popular_product_on_off`, `home_testimonial_on_off`, `home_blog_on_off`, `newsletter_on_off`, `ads_above_welcome_on_off`, `ads_above_featured_product_on_off`, `ads_above_latest_product_on_off`, `ads_above_popular_product_on_off`, `ads_above_testimonial_on_off`, `ads_category_sidebar_on_off`, `default_staff_commission_type`, `default_staff_commission_value`, `marquee_on_off`, `marquee_notices`, `invoice_vat_no`, `invoice_due_days`, `invoice_footer_note`) VALUES
(1, 'logo.png', '8848 Cleaning Service', 'favicon.png', '<p>Lorem ipsum dolor sit amet, omnis signiferumque in mei, mei ex enim concludaturque. Senserit salutandi euripidis no per, modus maiestatis scribentur est an.Â Ea suas pertinax has.</p>\r\n', '© {YEAR} 8848 Cleaning Service. All rights reserved.', 'Manamaiju,kathmandu ', 'contact@sastikatrading.com.np', '+977-9869224134', '', '<iframe src=\"https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d3531.197527116017!2d85.31022257581755!3d27.742052876162564!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x39eb19005931a1db%3A0x582712fc68092e57!2sRaise%E2%80%99N%20Studio!5e0!3m2!1sen!2snp!4v1762004893741!5m2!1sen!2snp\" width=\"600\" height=\"450\" style=\"border:0;\" allowfullscreen=\"\" loading=\"lazy\" referrerpolicy=\"no-referrer-when-downgrade\"></iframe>', 'support@ecommercephp.com', 'Visitor Email Message from Ecommerce Site PHP', 'Thank you for sending email. We will contact you shortly.', 'A confirmation link is sent to your email address. You will get the password reset information in there.', 4, 4, 5, 5, 5, 6, 8, '8848 Cleaning Service | Home & Office Cleaning in Kathmandu', '8848 cleaning service, home cleaning Kathmandu, office cleaning, deep cleaning Nepal, book cleaner online', 'Professional home and office cleaning in Kathmandu. Book trusted cleaners online with 8848 Cleaning Service — flexible scheduling and reliable results.', 'banner_login.jpg', 'banner_registration.jpg', 'banner_forget_password.jpg', 'banner_reset_password.jpg', 'banner_search.jpg', 'banner_cart.jpg', 'banner_checkout.jpg', 'banner_product_category.jpg', 'banner_blog.jpg', 'Welcome To Our Ecommerce Website', 'Lorem ipsum dolor sit amet, an labores explicari qui, eu nostrum copiosae argumentum has. Latine propriae quo no, unum ridens expetenda id sit, \r\nat usu eius eligendi singulis. Sea ocurreret principes ne. At nonumy aperiri pri, nam quodsi copiosae intellegebat et, ex deserunt euripidis usu. ', 'Read More', '#', 'cta.jpg', 'Featured Products', 'Our list on Top Featured Products', 'Latest Products', 'Our list of recently added products', 'Popular Products', 'Popular products based on customer\'s choice', 'Testimonials', 'See what our clients tell about us', 'testimonial.jpg', 'Latest Blog', 'See all our latest articles and news from below', 'Sign-up to our newsletter for latest promotions and discounts.', 'admin@ecom.com', 'pk_test_0SwMWadgu8DwmEcPdUPRsZ7b', 'sk_test_TFcsLJ7xxUtpALbDo1L5c1PN', 'Bank Name: WestView Bank\r\nAccount Number: CA100270589600\r\nBranch Name: CA Branch\r\nCountry: USA', '', '<div id=\"fb-root\"></div>\r\n<script>(function(d, s, id) {\r\n  var js, fjs = d.getElementsByTagName(s)[0];\r\n  if (d.getElementById(id)) return;\r\n  js = d.createElement(s); js.id = id;\r\n  js.src = \"//connect.facebook.net/en_US/sdk.js#xfbml=1&version=v2.10&appId=323620764400430\";\r\n  fjs.parentNode.insertBefore(js, fjs);\r\n}(document, \'script\', \'facebook-jssdk\'));</script>', '<!--Start of Tawk.to Script-->\r\n<script type=\"text/javascript\">\r\nvar Tawk_API=Tawk_API||{}, Tawk_LoadStart=new Date();\r\n(function(){\r\nvar s1=document.createElement(\"script\"),s0=document.getElementsByTagName(\"script\")[0];\r\ns1.async=true;\r\ns1.src=\'https://embed.tawk.to/69089c9c39dbe71958a1931a/1j94q7hio\';\r\ns1.charset=\'UTF-8\';\r\ns1.setAttribute(\'crossorigin\',\'*\');\r\ns0.parentNode.insertBefore(s1,s0);\r\n})();\r\n</script>\r\n<!--End of Tawk.to Script-->', 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 'percent', 35.00, 1, NULL, '', 30, 'Thank you for choosing our cleaning service. We appreciate your trust.');

-- --------------------------------------------------------

--
-- Table structure for table `tbl_shipping_cost`
--

DROP TABLE IF EXISTS `tbl_shipping_cost`;
CREATE TABLE IF NOT EXISTS `tbl_shipping_cost` (
  `shipping_cost_id` int NOT NULL AUTO_INCREMENT,
  `country_id` int NOT NULL,
  `amount` varchar(20) NOT NULL,
  PRIMARY KEY (`shipping_cost_id`)
) ENGINE=InnoDB AUTO_INCREMENT=12 DEFAULT CHARSET=latin1;

--
-- Dumping data for table `tbl_shipping_cost`
--

INSERT INTO `tbl_shipping_cost` (`shipping_cost_id`, `country_id`, `amount`) VALUES
(1, 228, '11'),
(2, 167, '100'),
(3, 13, '8'),
(4, 230, '0'),
(5, 2, '100'),
(6, 154, '12345'),
(7, 44, '100'),
(8, 246, '120'),
(9, 247, '234'),
(10, 249, '120'),
(11, 250, '300');

-- --------------------------------------------------------

--
-- Table structure for table `tbl_shipping_cost_all`
--

DROP TABLE IF EXISTS `tbl_shipping_cost_all`;
CREATE TABLE IF NOT EXISTS `tbl_shipping_cost_all` (
  `sca_id` int NOT NULL AUTO_INCREMENT,
  `amount` varchar(20) NOT NULL,
  PRIMARY KEY (`sca_id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=latin1;

--
-- Dumping data for table `tbl_shipping_cost_all`
--

INSERT INTO `tbl_shipping_cost_all` (`sca_id`, `amount`) VALUES
(1, '300');

-- --------------------------------------------------------

--
-- Table structure for table `tbl_size`
--

DROP TABLE IF EXISTS `tbl_size`;
CREATE TABLE IF NOT EXISTS `tbl_size` (
  `size_id` int NOT NULL AUTO_INCREMENT,
  `size_name` varchar(255) NOT NULL,
  PRIMARY KEY (`size_id`)
) ENGINE=InnoDB AUTO_INCREMENT=49 DEFAULT CHARSET=latin1;

--
-- Dumping data for table `tbl_size`
--

INSERT INTO `tbl_size` (`size_id`, `size_name`) VALUES
(1, '9.5*9*4.2cm'),
(8, '7.5*4*6'),
(9, '8.5*4.5*7cm'),
(10, '33'),
(12, '35'),
(13, '36'),
(14, '37'),
(15, '38'),
(16, '39'),
(19, '42'),
(20, '43'),
(21, '44'),
(22, '45'),
(23, '46'),
(24, '47'),
(25, '48'),
(26, 'Free Size'),
(27, 'One Size for All'),
(28, '10'),
(29, '12 Months'),
(30, '2T'),
(31, '3T'),
(32, '4T'),
(33, '5T'),
(34, '6 Years'),
(35, '7 Years'),
(36, '8 Years'),
(37, '10 Years'),
(38, '12 Years'),
(39, '14 Years'),
(40, '256 GB'),
(41, '128 GB'),
(42, '14 Plus'),
(43, '16 Plus'),
(44, '18 Plus'),
(45, '20 Plus'),
(46, '22 Plus'),
(47, '24 Plus'),
(48, '8.9*4.8cm');

-- --------------------------------------------------------

--
-- Table structure for table `tbl_slider`
--

DROP TABLE IF EXISTS `tbl_slider`;
CREATE TABLE IF NOT EXISTS `tbl_slider` (
  `id` int NOT NULL AUTO_INCREMENT,
  `photo` varchar(255) NOT NULL,
  `heading` varchar(255) NOT NULL,
  `content` text NOT NULL,
  `button_text` varchar(255) NOT NULL,
  `button_url` varchar(255) NOT NULL,
  `position` varchar(255) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=15 DEFAULT CHARSET=latin1;

--
-- Dumping data for table `tbl_slider`
--

INSERT INTO `tbl_slider` (`id`, `photo`, `heading`, `content`, `button_text`, `button_url`, `position`) VALUES
(13, 'slider-13.jpg', '', '', '', '', 'Left');

-- --------------------------------------------------------

--
-- Table structure for table `tbl_social`
--

DROP TABLE IF EXISTS `tbl_social`;
CREATE TABLE IF NOT EXISTS `tbl_social` (
  `social_id` int NOT NULL AUTO_INCREMENT,
  `social_name` varchar(30) NOT NULL,
  `social_url` varchar(255) NOT NULL,
  `social_icon` varchar(30) NOT NULL,
  PRIMARY KEY (`social_id`)
) ENGINE=InnoDB AUTO_INCREMENT=17 DEFAULT CHARSET=latin1;

--
-- Dumping data for table `tbl_social`
--

INSERT INTO `tbl_social` (`social_id`, `social_name`, `social_url`, `social_icon`) VALUES
(1, 'Facebook', 'https://www.facebook.com/techgathanepal/', 'fa fa-facebook'),
(2, 'Twitter', 'https://www.twitter.com/#', 'fa fa-twitter'),
(3, 'LinkedIn', '', 'fa fa-linkedin'),
(4, 'Google Plus', '', 'fa fa-google-plus'),
(5, 'Pinterest', '', 'fa fa-pinterest'),
(6, 'YouTube', 'https://www.youtube.com/123', 'fa fa-youtube'),
(7, 'Instagram', 'https://www.instagram.com/techgathanepal/', 'fa fa-instagram'),
(8, 'Tumblr', '', 'fa fa-tumblr'),
(9, 'Flickr', '', 'fa fa-flickr'),
(10, 'Reddit', '', 'fa fa-reddit'),
(11, 'Snapchat', '', 'fa fa-snapchat'),
(12, 'WhatsApp', '', 'fa fa-whatsapp'),
(13, 'Quora', '', 'fa fa-quora'),
(14, 'StumbleUpon', '', 'fa fa-stumbleupon'),
(15, 'Delicious', '', 'fa fa-delicious'),
(16, 'Digg', '', 'fa fa-digg');

-- --------------------------------------------------------

--
-- Table structure for table `tbl_staff`
--

DROP TABLE IF EXISTS `tbl_staff`;
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
  `designation` varchar(150) NOT NULL DEFAULT '',
  `rating` tinyint NOT NULL DEFAULT '5',
  `facebook_url` varchar(255) NOT NULL DEFAULT '',
  `instagram_url` varchar(255) NOT NULL DEFAULT '',
  `show_on_website` tinyint NOT NULL DEFAULT '1',
  PRIMARY KEY (`staff_id`),
  UNIQUE KEY `email` (`email`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `tbl_staff`
--

INSERT INTO `tbl_staff` (`staff_id`, `full_name`, `email`, `phone`, `password`, `photo`, `address`, `default_commission_type`, `default_commission_value`, `status`, `created_at`, `designation`, `rating`, `facebook_url`, `instagram_url`, `show_on_website`) VALUES
(1, 'Demo Staff', 'staff@gmail.com', '9800000000', '$2y$10$9ZQdweYb3dGRxZ7eHe8sZOR7tWOt5wucQb3lmYLIOu0cPi9XpwSye', 'staff-1.jpg', '', 'percent', 35.00, 'Active', '2026-07-17 14:32:52', '', 4, 'https://www.facebook.com/neweraacademyedu/', 'https://www.instagram.com/jaishreepatange/', 1);

-- --------------------------------------------------------

--
-- Table structure for table `tbl_staff_auto_assign`
--

DROP TABLE IF EXISTS `tbl_staff_auto_assign`;
CREATE TABLE IF NOT EXISTS `tbl_staff_auto_assign` (
  `id` int NOT NULL DEFAULT '1',
  `last_staff_id` int NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `tbl_staff_auto_assign`
--

INSERT INTO `tbl_staff_auto_assign` (`id`, `last_staff_id`) VALUES
(1, 1);

-- --------------------------------------------------------

--
-- Table structure for table `tbl_staff_availability`
--

DROP TABLE IF EXISTS `tbl_staff_availability`;
CREATE TABLE IF NOT EXISTS `tbl_staff_availability` (
  `availability_id` int NOT NULL AUTO_INCREMENT,
  `staff_id` int NOT NULL,
  `day_of_week` tinyint NOT NULL,
  `start_time` time NOT NULL DEFAULT '08:00:00',
  `end_time` time NOT NULL DEFAULT '18:00:00',
  `is_available` tinyint NOT NULL DEFAULT '1',
  PRIMARY KEY (`availability_id`),
  KEY `idx_staff_day` (`staff_id`,`day_of_week`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `tbl_staff_availability`
--

INSERT INTO `tbl_staff_availability` (`availability_id`, `staff_id`, `day_of_week`, `start_time`, `end_time`, `is_available`) VALUES
(1, 1, 1, '08:00:00', '18:00:00', 1),
(2, 1, 1, '08:00:00', '18:00:00', 1);

-- --------------------------------------------------------

--
-- Table structure for table `tbl_subscriber`
--

DROP TABLE IF EXISTS `tbl_subscriber`;
CREATE TABLE IF NOT EXISTS `tbl_subscriber` (
  `subs_id` int NOT NULL AUTO_INCREMENT,
  `subs_email` varchar(255) NOT NULL,
  `subs_date` varchar(100) NOT NULL,
  `subs_date_time` varchar(100) NOT NULL,
  `subs_hash` varchar(255) NOT NULL,
  `subs_active` int NOT NULL,
  PRIMARY KEY (`subs_id`)
) ENGINE=InnoDB AUTO_INCREMENT=26 DEFAULT CHARSET=latin1;

--
-- Dumping data for table `tbl_subscriber`
--

INSERT INTO `tbl_subscriber` (`subs_id`, `subs_email`, `subs_date`, `subs_date_time`, `subs_hash`, `subs_active`) VALUES
(19, 'nirajkarna66@gmail.com', '2025-10-08', '2025-10-08 04:07:56', '1791c7d104ea59c65b8daa829b1f35fa', 1),
(23, 'mendhonepal@gmail.com', '2025-11-01', '2025-11-01 14:57:28', 'eb976e48c84d9d78f7396bb203957a5a', 1),
(25, 'rajan@gmail.com', '2026-07-02', '2026-07-02 11:46:05', '58d782cd7c5118a5', 1);

-- --------------------------------------------------------

--
-- Table structure for table `tbl_testimonial`
--

DROP TABLE IF EXISTS `tbl_testimonial`;
CREATE TABLE IF NOT EXISTS `tbl_testimonial` (
  `id` int NOT NULL AUTO_INCREMENT,
  `name` varchar(100) NOT NULL,
  `designation` varchar(150) NOT NULL DEFAULT '',
  `company` varchar(150) NOT NULL DEFAULT '',
  `review` text NOT NULL,
  `rating` tinyint NOT NULL DEFAULT '5',
  `photo` varchar(255) NOT NULL DEFAULT '',
  `status` varchar(20) NOT NULL DEFAULT 'Active',
  `sort_order` int NOT NULL DEFAULT '0',
  `created_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `tbl_testimonial`
--

INSERT INTO `tbl_testimonial` (`id`, `name`, `designation`, `company`, `review`, `rating`, `photo`, `status`, `sort_order`, `created_at`) VALUES
(1, 'Anisha Shrestha', 'Homeowner', 'Kathmandu', 'The team arrived on time and left our home spotless. Booking online was easy and communication was clear.', 5, 'testimonial-1.jpg', 'Active', 1, '2026-07-17 15:46:19'),
(2, 'Rajesh Thapa', 'Office Manager', 'Lalitpur', 'We use 8848 Cleaning Service for our office every week. Reliable staff and consistent quality.', 5, '', 'Active', 2, '2026-07-17 15:46:19'),
(3, 'Maya Gurung', 'Apartment Owner', 'Bhaktapur', 'Deep clean before moving in was excellent. Highly recommend for anyone who wants a professional finish.', 4, '', 'Active', 3, '2026-07-17 15:46:19');

-- --------------------------------------------------------

--
-- Table structure for table `tbl_top_category`
--

DROP TABLE IF EXISTS `tbl_top_category`;
CREATE TABLE IF NOT EXISTS `tbl_top_category` (
  `tcat_id` int NOT NULL AUTO_INCREMENT,
  `tcat_name` varchar(255) NOT NULL,
  `show_on_menu` int NOT NULL,
  PRIMARY KEY (`tcat_id`)
) ENGINE=InnoDB AUTO_INCREMENT=11 DEFAULT CHARSET=latin1;

--
-- Dumping data for table `tbl_top_category`
--

INSERT INTO `tbl_top_category` (`tcat_id`, `tcat_name`, `show_on_menu`) VALUES
(6, 'Candle Raw Material', 1),
(7, 'Resin Raw Material', 1),
(8, 'Packaging Raw Material', 1);

-- --------------------------------------------------------

--
-- Table structure for table `tbl_user`
--

DROP TABLE IF EXISTS `tbl_user`;
CREATE TABLE IF NOT EXISTS `tbl_user` (
  `id` int NOT NULL AUTO_INCREMENT,
  `full_name` varchar(100) NOT NULL,
  `email` varchar(255) NOT NULL,
  `phone` varchar(100) NOT NULL,
  `password` varchar(255) NOT NULL,
  `photo` varchar(255) NOT NULL,
  `role` varchar(30) NOT NULL,
  `status` varchar(10) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=latin1;

--
-- Dumping data for table `tbl_user`
--

INSERT INTO `tbl_user` (`id`, `full_name`, `email`, `phone`, `password`, `photo`, `role`, `status`) VALUES
(1, 'Administrator', 'admin@mail.com', '9813766623', 'e10adc3949ba59abbe56e057f20f883e', 'user-1.jpg', 'Super Admin', 'Active'),
(2, 'Christine', 'christine@mail.com', '4444444444', '81dc9bdb52d04dc20036dbd8313ed055', 'user-13.jpg', 'Admin', 'Active');

-- --------------------------------------------------------

--
-- Table structure for table `tbl_video`
--

DROP TABLE IF EXISTS `tbl_video`;
CREATE TABLE IF NOT EXISTS `tbl_video` (
  `id` int NOT NULL AUTO_INCREMENT,
  `title` varchar(255) NOT NULL,
  `iframe_code` text NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=latin1;

--
-- Dumping data for table `tbl_video`
--

INSERT INTO `tbl_video` (`id`, `title`, `iframe_code`) VALUES
(1, 'Video 1', '<iframe width=\"560\" height=\"315\" src=\"https://www.youtube.com/embed/L3XAFSMdVWU\" frameborder=\"0\" allow=\"autoplay; encrypted-media\" allowfullscreen></iframe>'),
(2, 'Video 2', '<iframe width=\"560\" height=\"315\" src=\"https://www.youtube.com/embed/sinQ06YzbJI\" frameborder=\"0\" allow=\"autoplay; encrypted-media\" allowfullscreen></iframe>'),
(4, 'Video 3', '<iframe width=\"560\" height=\"315\" src=\"https://www.youtube.com/embed/ViZNgU-Yt-Y\" frameborder=\"0\" allow=\"autoplay; encrypted-media\" allowfullscreen></iframe>');
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
