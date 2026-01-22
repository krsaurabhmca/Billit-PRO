-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Jan 23, 2026 at 12:46 AM
-- Server version: 10.4.32-MariaDB
-- PHP Version: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `inventory_management`
--

-- --------------------------------------------------------

--
-- Table structure for table `access_logs`
--

CREATE TABLE `access_logs` (
  `log_id` int(11) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `username` varchar(50) DEFAULT NULL,
  `action` varchar(100) NOT NULL,
  `details` text DEFAULT NULL,
  `ip_address` varchar(45) DEFAULT NULL,
  `user_agent` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `access_logs`
--

INSERT INTO `access_logs` (`log_id`, `user_id`, `username`, `action`, `details`, `ip_address`, `user_agent`, `created_at`) VALUES
(1, 1, 'admin', 'Login', 'User logged in successfully', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2026-01-22 21:52:51');

-- --------------------------------------------------------

--
-- Table structure for table `categories`
--

CREATE TABLE `categories` (
  `category_id` int(11) NOT NULL,
  `category_name` varchar(100) NOT NULL,
  `description` text DEFAULT NULL,
  `status` enum('active','inactive') NOT NULL DEFAULT 'active',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `categories`
--

INSERT INTO `categories` (`category_id`, `category_name`, `description`, `status`, `created_at`, `updated_at`) VALUES
(1, 'Electronics', 'Electronic devices and accessories', 'active', '2026-01-22 20:05:37', '2026-01-22 20:05:37'),
(2, 'Office Supplies', 'Office stationery and supplies', 'active', '2026-01-22 20:05:37', '2026-01-22 20:05:37'),
(3, 'Furniture', 'Office and home furniture', 'active', '2026-01-22 20:05:37', '2026-01-22 20:05:37'),
(4, 'Hardware', 'Hardware tools and equipment', 'active', '2026-01-22 20:05:37', '2026-01-22 20:05:37'),
(5, 'Software', 'Software licenses and applications', 'active', '2026-01-22 20:05:37', '2026-01-22 20:05:37'),
(6, 'New Cateogry', 'New Cateogry', 'active', '2026-01-22 21:53:25', '2026-01-22 21:53:25');

-- --------------------------------------------------------

--
-- Table structure for table `company_settings`
--

CREATE TABLE `company_settings` (
  `setting_id` int(11) NOT NULL,
  `company_name` varchar(200) NOT NULL,
  `company_address` text DEFAULT NULL,
  `company_city` varchar(100) DEFAULT NULL,
  `company_state` varchar(100) DEFAULT NULL,
  `company_state_code` varchar(2) DEFAULT NULL,
  `company_pincode` varchar(10) DEFAULT NULL,
  `company_gstin` varchar(15) DEFAULT NULL,
  `company_pan` varchar(10) DEFAULT NULL,
  `company_phone` varchar(20) DEFAULT NULL,
  `company_email` varchar(100) DEFAULT NULL,
  `company_logo` varchar(255) DEFAULT NULL,
  `invoice_prefix` varchar(10) DEFAULT 'INV',
  `invoice_start_number` int(11) DEFAULT 1,
  `terms_conditions` text DEFAULT NULL,
  `bank_name` varchar(100) DEFAULT NULL,
  `bank_account_number` varchar(50) DEFAULT NULL,
  `bank_ifsc` varchar(20) DEFAULT NULL,
  `bank_branch` varchar(100) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `invoice_color` varchar(20) DEFAULT '#2563eb'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `company_settings`
--

INSERT INTO `company_settings` (`setting_id`, `company_name`, `company_address`, `company_city`, `company_state`, `company_state_code`, `company_pincode`, `company_gstin`, `company_pan`, `company_phone`, `company_email`, `company_logo`, `invoice_prefix`, `invoice_start_number`, `terms_conditions`, `bank_name`, `bank_account_number`, `bank_ifsc`, `bank_branch`, `created_at`, `updated_at`, `invoice_color`) VALUES
(1, 'OfferPlant', '2B Kumar Bhwan , Umanagar\r\nBazar Samiti, Chapra 841301', 'Chapra', 'Bihar', '10', '841301', '27AAAAA0000A1Z5', '', '+91-9876543210', 'info@yourcompany.com', 'assets/uploads/company_logo_1769122003.png', 'INV', 1, '1. Payment is due within 30 days of invoice date.\r\n2. Please make cheques payable to Your Company Name.\r\n3. Goods once sold will not be taken back.\r\n4. Subject to Mumbai Jurisdiction.', 'UNION BANK OF INDIA', '100301838', 'SBIN010039', 'Main Branch Chapra', '2026-01-22 20:19:28', '2026-01-22 23:04:34', '#433d41');

-- --------------------------------------------------------

--
-- Table structure for table `customers`
--

CREATE TABLE `customers` (
  `customer_id` int(11) NOT NULL,
  `customer_name` varchar(200) NOT NULL,
  `customer_type` enum('B2B','B2C') NOT NULL DEFAULT 'B2C',
  `contact_person` varchar(100) DEFAULT NULL,
  `email` varchar(100) DEFAULT NULL,
  `phone` varchar(20) DEFAULT NULL,
  `gstin` varchar(15) DEFAULT NULL,
  `pan` varchar(10) DEFAULT NULL,
  `billing_address` text DEFAULT NULL,
  `billing_city` varchar(100) DEFAULT NULL,
  `billing_state` varchar(100) DEFAULT NULL,
  `billing_state_code` varchar(2) DEFAULT NULL,
  `billing_pincode` varchar(10) DEFAULT NULL,
  `shipping_address` text DEFAULT NULL,
  `shipping_city` varchar(100) DEFAULT NULL,
  `shipping_state` varchar(100) DEFAULT NULL,
  `shipping_state_code` varchar(2) DEFAULT NULL,
  `shipping_pincode` varchar(10) DEFAULT NULL,
  `credit_limit` decimal(10,2) DEFAULT 0.00,
  `opening_balance` decimal(10,2) DEFAULT 0.00,
  `status` enum('active','inactive') NOT NULL DEFAULT 'active',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `customers`
--

INSERT INTO `customers` (`customer_id`, `customer_name`, `customer_type`, `contact_person`, `email`, `phone`, `gstin`, `pan`, `billing_address`, `billing_city`, `billing_state`, `billing_state_code`, `billing_pincode`, `shipping_address`, `shipping_city`, `shipping_state`, `shipping_state_code`, `shipping_pincode`, `credit_limit`, `opening_balance`, `status`, `created_at`, `updated_at`) VALUES
(1, 'ABC Enterprises Pvt Ltd', 'B2B', 'Rajesh Kumar', 'rajesh@abcenterprises.com', '+91-9876543211', '27BBBBB1111B1Z5', NULL, '456 Corporate Avenue', 'Mumbai', 'Maharashtra', '27', '400001', NULL, NULL, NULL, NULL, NULL, 0.00, 0.00, 'active', '2026-01-22 20:19:28', '2026-01-22 20:19:28'),
(2, 'XYZ Trading Co', 'B2B', 'Priya Sharma', 'priya@xyztrading.com', '+91-9876543212', '29CCCCC2222C1Z5', NULL, '789 Trade Center', 'Bangalore', 'Karnataka', '29', '560001', NULL, NULL, NULL, NULL, NULL, 0.00, 0.00, 'active', '2026-01-22 20:19:28', '2026-01-22 20:19:28'),
(3, 'Walk-in Customer', 'B2C', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0.00, 0.00, 'active', '2026-01-22 20:19:28', '2026-01-22 20:19:28'),
(4, 'Retail Customer', 'B2C', 'Amit Patel', 'amit@gmail.com', '+91-9876543213', NULL, NULL, '321 Residential Area', 'Mumbai', 'Maharashtra', '27', '400002', NULL, NULL, NULL, NULL, NULL, 0.00, 0.00, 'active', '2026-01-22 20:19:28', '2026-01-22 20:19:28'),
(5, 'KUMAR SAURABH', 'B2C', 'KUMAR SAURABH', '', '09431426600', '', NULL, 'Umanagar\r\nChapra saran', 'Chapra', 'Bihar', '10', '841301', NULL, NULL, NULL, NULL, NULL, 0.00, 0.00, 'active', '2026-01-22 20:28:12', '2026-01-22 20:28:12');

-- --------------------------------------------------------

--
-- Table structure for table `invoices`
--

CREATE TABLE `invoices` (
  `invoice_id` int(11) NOT NULL,
  `invoice_number` varchar(50) NOT NULL,
  `invoice_date` date NOT NULL,
  `customer_id` int(11) NOT NULL,
  `customer_name` varchar(200) NOT NULL,
  `customer_gstin` varchar(15) DEFAULT NULL,
  `customer_address` text DEFAULT NULL,
  `customer_state_code` varchar(2) DEFAULT NULL,
  `subtotal` decimal(10,2) NOT NULL DEFAULT 0.00,
  `discount_type` enum('percentage','amount') DEFAULT 'percentage',
  `discount_value` decimal(10,2) DEFAULT 0.00,
  `discount_amount` decimal(10,2) DEFAULT 0.00,
  `taxable_amount` decimal(10,2) NOT NULL DEFAULT 0.00,
  `cgst_amount` decimal(10,2) DEFAULT 0.00,
  `sgst_amount` decimal(10,2) DEFAULT 0.00,
  `igst_amount` decimal(10,2) DEFAULT 0.00,
  `total_tax` decimal(10,2) DEFAULT 0.00,
  `round_off` decimal(10,2) DEFAULT 0.00,
  `total_amount` decimal(10,2) NOT NULL DEFAULT 0.00,
  `amount_paid` decimal(10,2) DEFAULT 0.00,
  `amount_due` decimal(10,2) DEFAULT 0.00,
  `payment_status` enum('unpaid','partial','paid') NOT NULL DEFAULT 'unpaid',
  `invoice_status` enum('draft','finalized','cancelled') NOT NULL DEFAULT 'draft',
  `notes` text DEFAULT NULL,
  `terms_conditions` text DEFAULT NULL,
  `created_by` int(11) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `invoices`
--

INSERT INTO `invoices` (`invoice_id`, `invoice_number`, `invoice_date`, `customer_id`, `customer_name`, `customer_gstin`, `customer_address`, `customer_state_code`, `subtotal`, `discount_type`, `discount_value`, `discount_amount`, `taxable_amount`, `cgst_amount`, `sgst_amount`, `igst_amount`, `total_tax`, `round_off`, `total_amount`, `amount_paid`, `amount_due`, `payment_status`, `invoice_status`, `notes`, `terms_conditions`, `created_by`, `created_at`, `updated_at`) VALUES
(1, 'INV0001', '2026-01-23', 5, 'KUMAR SAURABH', '', 'Umanagar\r\nChapra saran', '10', 349.98, 'percentage', 10.00, 35.00, 314.98, 0.00, 0.00, 56.70, 56.70, 0.32, 372.00, 372.00, 0.00, 'paid', 'finalized', '', '1. Payment is due within 30 days of invoice date.\r\n2. Please make cheques payable to Your Company Name.\r\n3. Goods once sold will not be taken back.\r\n4. Subject to Mumbai Jurisdiction.', 1, '2026-01-22 20:35:55', '2026-01-22 21:07:30'),
(2, 'INV0002', '2026-01-23', 5, 'KUMAR SAURABH', '', 'Umanagar\r\nChapra saran', '10', 19.99, 'percentage', 10.00, 2.00, 17.99, 0.00, 0.00, 3.24, 3.24, -0.23, 21.00, 21.00, 0.00, 'paid', 'finalized', '', '1. Payment is due within 30 days of invoice date.\r\n2. Please make cheques payable to Your Company Name.\r\n3. Goods once sold will not be taken back.\r\n4. Subject to Mumbai Jurisdiction.', 1, '2026-01-22 22:20:20', '2026-01-22 22:20:33'),
(3, 'INV0003', '2026-01-23', 5, 'KUMAR SAURABH', '', 'Umanagar\r\nChapra saran', '10', 10000.00, 'percentage', 0.00, 0.00, 10000.00, 0.00, 0.00, 1800.00, 1800.00, 0.00, 11800.00, 11800.00, 0.00, 'paid', 'finalized', '', '1. Payment is due within 30 days of invoice date.\r\n2. Please make cheques payable to Your Company Name.\r\n3. Goods once sold will not be taken back.\r\n4. Subject to Mumbai Jurisdiction.', 1, '2026-01-22 22:37:26', '2026-01-22 22:40:33'),
(4, 'INV0004', '2026-01-23', 5, 'KUMAR SAURABH', '', 'Umanagar\r\nChapra saran', '10', 10000.00, 'amount', 100.00, 100.00, 9900.00, 891.00, 891.00, 0.00, 1782.00, 0.00, 11682.00, 5000.00, 6682.00, 'partial', 'finalized', '', '1. Payment is due within 30 days of invoice date.\r\n2. Please make cheques payable to Your Company Name.\r\n3. Goods once sold will not be taken back.\r\n4. Subject to Mumbai Jurisdiction.', 1, '2026-01-22 22:51:15', '2026-01-22 22:52:46');

-- --------------------------------------------------------

--
-- Table structure for table `invoice_items`
--

CREATE TABLE `invoice_items` (
  `item_id` int(11) NOT NULL,
  `invoice_id` int(11) NOT NULL,
  `product_id` int(11) NOT NULL,
  `batch_id` int(11) DEFAULT NULL,
  `serial_ids` text DEFAULT NULL,
  `product_name` varchar(200) NOT NULL,
  `product_code` varchar(50) DEFAULT NULL,
  `hsn_code` varchar(20) DEFAULT NULL,
  `description` text DEFAULT NULL,
  `quantity` decimal(10,2) NOT NULL,
  `unit_of_measure` varchar(20) DEFAULT NULL,
  `unit_price` decimal(10,2) NOT NULL,
  `item_total` decimal(10,2) NOT NULL,
  `discount_percentage` decimal(5,2) DEFAULT 0.00,
  `discount_amount` decimal(10,2) DEFAULT 0.00,
  `taxable_amount` decimal(10,2) NOT NULL,
  `gst_rate` decimal(5,2) NOT NULL DEFAULT 0.00,
  `cgst_rate` decimal(5,2) DEFAULT 0.00,
  `cgst_amount` decimal(10,2) DEFAULT 0.00,
  `sgst_rate` decimal(5,2) DEFAULT 0.00,
  `sgst_amount` decimal(10,2) DEFAULT 0.00,
  `igst_rate` decimal(5,2) DEFAULT 0.00,
  `igst_amount` decimal(10,2) DEFAULT 0.00,
  `total_amount` decimal(10,2) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `invoice_items`
--

INSERT INTO `invoice_items` (`item_id`, `invoice_id`, `product_id`, `batch_id`, `serial_ids`, `product_name`, `product_code`, `hsn_code`, `description`, `quantity`, `unit_of_measure`, `unit_price`, `item_total`, `discount_percentage`, `discount_amount`, `taxable_amount`, `gst_rate`, `cgst_rate`, `cgst_amount`, `sgst_rate`, `sgst_amount`, `igst_rate`, `igst_amount`, `total_amount`, `created_at`) VALUES
(2, 1, 7, NULL, NULL, 'Office Chair', 'FURN001', '9401', NULL, 1.00, 'pcs', 149.99, 149.99, 0.00, 0.00, 134.99, 18.00, 0.00, 0.00, 0.00, 0.00, 18.00, 24.30, 159.29, '2026-01-22 21:06:00'),
(3, 1, 3, NULL, NULL, 'LED Monitor 24\"', 'ELEC003', '8528', NULL, 1.00, 'pcs', 199.99, 199.99, 0.00, 0.00, 179.99, 18.00, 0.00, 0.00, 0.00, 0.00, 18.00, 32.40, 212.39, '2026-01-22 21:06:00'),
(4, 2, 9, NULL, NULL, 'Screwdriver Set', 'HARD001', '8205', NULL, 1.00, 'set', 19.99, 19.99, 0.00, 0.00, 17.99, 18.00, 0.00, 0.00, 0.00, 0.00, 18.00, 3.24, 21.23, '2026-01-22 22:20:20'),
(6, 3, 12, NULL, NULL, 'Mobile', 'MOB', '', NULL, 1.00, 'pcs', 10000.00, 10000.00, 0.00, 0.00, 10000.00, 18.00, 0.00, 0.00, 0.00, 0.00, 18.00, 1800.00, 11800.00, '2026-01-22 22:39:49'),
(7, 4, 12, NULL, '3', 'Mobile', 'MOB', '', NULL, 1.00, 'pcs', 10000.00, 10000.00, 0.00, 0.00, 9900.00, 18.00, 9.00, 891.00, 9.00, 891.00, 0.00, 0.00, 11682.00, '2026-01-22 22:51:15');

-- --------------------------------------------------------

--
-- Table structure for table `password_resets`
--

CREATE TABLE `password_resets` (
  `id` int(11) NOT NULL,
  `email` varchar(100) NOT NULL,
  `token` varchar(255) NOT NULL,
  `expiry` datetime NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `payments`
--

CREATE TABLE `payments` (
  `payment_id` int(11) NOT NULL,
  `invoice_id` int(11) NOT NULL,
  `payment_date` date NOT NULL,
  `payment_method` enum('cash','card','upi','bank_transfer','cheque','other') NOT NULL,
  `payment_amount` decimal(10,2) NOT NULL,
  `reference_number` varchar(100) DEFAULT NULL,
  `notes` text DEFAULT NULL,
  `created_by` int(11) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `payments`
--

INSERT INTO `payments` (`payment_id`, `invoice_id`, `payment_date`, `payment_method`, `payment_amount`, `reference_number`, `notes`, `created_by`, `created_at`) VALUES
(1, 1, '2026-01-23', 'cash', 300.00, 'Testt', '', 1, '2026-01-22 21:06:48'),
(2, 1, '2026-01-23', 'cash', 72.00, 'TXns18/78282', '', 1, '2026-01-22 21:07:30'),
(3, 2, '2026-01-23', 'cash', 21.00, '', '', 1, '2026-01-22 22:20:33'),
(4, 3, '2026-01-23', 'upi', 10000.00, 'TXN 238832', '', 1, '2026-01-22 22:40:17'),
(5, 3, '2026-01-23', 'cash', 1800.00, '', 'All Clear', 1, '2026-01-22 22:40:33'),
(6, 4, '2026-01-23', 'cash', 5000.00, '', 'Dues 7 do=in me denge', 1, '2026-01-22 22:52:46');

-- --------------------------------------------------------

--
-- Table structure for table `products`
--

CREATE TABLE `products` (
  `product_id` int(11) NOT NULL,
  `product_code` varchar(50) NOT NULL,
  `product_name` varchar(200) NOT NULL,
  `hsn_code` varchar(20) DEFAULT NULL,
  `description` text DEFAULT NULL,
  `category_id` int(11) NOT NULL,
  `supplier_id` int(11) DEFAULT NULL,
  `unit_price` decimal(10,2) NOT NULL DEFAULT 0.00,
  `gst_rate` decimal(5,2) NOT NULL DEFAULT 18.00,
  `quantity_in_stock` int(11) NOT NULL DEFAULT 0,
  `reorder_level` int(11) NOT NULL DEFAULT 10,
  `unit_of_measure` varchar(20) DEFAULT 'pcs',
  `tracking_type` enum('none','batch','serial') NOT NULL DEFAULT 'none',
  `status` enum('active','inactive','discontinued') NOT NULL DEFAULT 'active',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `products`
--

INSERT INTO `products` (`product_id`, `product_code`, `product_name`, `hsn_code`, `description`, `category_id`, `supplier_id`, `unit_price`, `gst_rate`, `quantity_in_stock`, `reorder_level`, `unit_of_measure`, `tracking_type`, `status`, `created_at`, `updated_at`) VALUES
(1, 'ELEC001', 'Wireless Mouse', '8471', 'Ergonomic wireless mouse with USB receiver', 1, 1, 25.99, 18.00, 150, 20, 'pcs', 'none', 'active', '2026-01-22 20:05:37', '2026-01-22 20:19:28'),
(2, 'ELEC002', 'USB Keyboard', '8471', 'Standard USB keyboard with numeric keypad', 1, 1, 35.50, 18.00, 80, 15, 'pcs', 'none', 'active', '2026-01-22 20:05:37', '2026-01-22 20:19:28'),
(3, 'ELEC003', 'LED Monitor 24\"', '8528', '24-inch Full HD LED monitor', 1, 1, 199.99, 18.00, 44, 10, 'pcs', 'none', 'active', '2026-01-22 20:05:37', '2026-01-22 21:06:00'),
(4, 'OFF001', 'A4 Paper Ream', '4802', '500 sheets white A4 paper', 2, 2, 5.99, 12.00, 200, 50, 'ream', 'none', 'active', '2026-01-22 20:05:37', '2026-01-22 20:19:28'),
(5, 'OFF002', 'Ballpoint Pen Blue', '9608', 'Blue ink ballpoint pen', 2, 2, 0.50, 12.00, 500, 100, 'pcs', 'none', 'active', '2026-01-22 20:05:37', '2026-01-22 20:19:28'),
(6, 'OFF003', 'Stapler Standard', '8305', 'Standard office stapler', 2, 2, 8.99, 18.00, 75, 15, 'pcs', 'none', 'active', '2026-01-22 20:05:37', '2026-01-22 20:19:28'),
(7, 'FURN001', 'Office Chair', '9401', 'Ergonomic office chair with adjustable height', 3, 4, 149.99, 18.00, 29, 5, 'pcs', 'none', 'active', '2026-01-22 20:05:37', '2026-01-22 21:06:00'),
(8, 'FURN002', 'Desk Lamp', '9405', 'LED desk lamp with adjustable arm', 3, 4, 29.99, 18.00, 60, 10, 'pcs', 'none', 'active', '2026-01-22 20:05:37', '2026-01-22 20:19:28'),
(9, 'HARD001', 'Screwdriver Set', '8205', '10-piece screwdriver set', 4, 3, 19.99, 18.00, 39, 8, 'set', 'none', 'active', '2026-01-22 20:05:37', '2026-01-22 22:20:20'),
(10, 'HARD002', 'Hammer', '8205', 'Claw hammer with rubber grip', 4, 3, 12.50, 18.00, 35, 10, 'pcs', 'none', 'active', '2026-01-22 20:05:37', '2026-01-22 20:19:28'),
(11, 'TEst003', 'Test 2', NULL, 'Test', 3, 3, 100.00, 18.00, 100, 10, 'ltr', 'none', 'active', '2026-01-22 20:12:59', '2026-01-22 20:13:22'),
(12, 'MOB', 'Mobile', NULL, '', 1, NULL, 10000.00, 18.00, 4, 10, 'pcs', 'serial', 'active', '2026-01-22 22:35:15', '2026-01-22 23:33:56');

-- --------------------------------------------------------

--
-- Table structure for table `product_batches`
--

CREATE TABLE `product_batches` (
  `batch_id` int(11) NOT NULL,
  `product_id` int(11) NOT NULL,
  `batch_no` varchar(50) NOT NULL,
  `expiry_date` date DEFAULT NULL,
  `purchase_item_id` int(11) DEFAULT NULL,
  `quantity_received` int(11) NOT NULL,
  `quantity_remaining` int(11) NOT NULL,
  `status` enum('active','expired','empty') DEFAULT 'active',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `product_serials`
--

CREATE TABLE `product_serials` (
  `serial_id` int(11) NOT NULL,
  `product_id` int(11) NOT NULL,
  `serial_no` varchar(100) NOT NULL,
  `purchase_item_id` int(11) DEFAULT NULL,
  `is_sold` tinyint(1) DEFAULT 0,
  `date_sold` date DEFAULT NULL,
  `invoice_item_id` int(11) DEFAULT NULL,
  `status` enum('available','sold','returned','defective') DEFAULT 'available',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `product_serials`
--

INSERT INTO `product_serials` (`serial_id`, `product_id`, `serial_no`, `purchase_item_id`, `is_sold`, `date_sold`, `invoice_item_id`, `status`, `created_at`) VALUES
(1, 12, 'SN01', 1, 0, NULL, NULL, 'available', '2026-01-22 22:36:38'),
(2, 12, 'SN03', 1, 0, NULL, NULL, 'available', '2026-01-22 22:36:38'),
(3, 12, 'SNO5', 1, 1, '2026-01-23', 7, 'sold', '2026-01-22 22:36:38'),
(4, 12, 'P1003', 2, 0, NULL, NULL, 'available', '2026-01-22 22:36:38'),
(5, 12, 'P2004', 2, 0, NULL, NULL, 'available', '2026-01-22 22:36:38');

-- --------------------------------------------------------

--
-- Table structure for table `purchases`
--

CREATE TABLE `purchases` (
  `purchase_id` int(11) NOT NULL,
  `supplier_id` int(11) NOT NULL,
  `supplier_invoice_no` varchar(50) DEFAULT NULL,
  `purchase_date` date NOT NULL,
  `total_amount` decimal(10,2) NOT NULL DEFAULT 0.00,
  `paid_amount` decimal(10,2) DEFAULT 0.00,
  `payment_status` enum('paid','partial','unpaid') DEFAULT 'unpaid',
  `status` enum('received','pending','cancelled') DEFAULT 'received',
  `notes` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `created_by` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `purchases`
--

INSERT INTO `purchases` (`purchase_id`, `supplier_id`, `supplier_invoice_no`, `purchase_date`, `total_amount`, `paid_amount`, `payment_status`, `status`, `notes`, `created_at`, `created_by`) VALUES
(1, 2, '', '2026-01-23', 71000.00, 0.00, 'unpaid', 'received', '', '2026-01-22 22:36:38', 1);

-- --------------------------------------------------------

--
-- Table structure for table `purchase_items`
--

CREATE TABLE `purchase_items` (
  `item_id` int(11) NOT NULL,
  `purchase_id` int(11) NOT NULL,
  `product_id` int(11) NOT NULL,
  `quantity` int(11) NOT NULL,
  `unit_cost` decimal(10,2) NOT NULL,
  `total_cost` decimal(10,2) NOT NULL,
  `batch_no` varchar(50) DEFAULT NULL,
  `expiry_date` date DEFAULT NULL,
  `serial_numbers` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `purchase_items`
--

INSERT INTO `purchase_items` (`item_id`, `purchase_id`, `product_id`, `quantity`, `unit_cost`, `total_cost`, `batch_no`, `expiry_date`, `serial_numbers`) VALUES
(1, 1, 12, 3, 13000.00, 39000.00, NULL, NULL, 'SN01,SN03,SNO5'),
(2, 1, 12, 2, 16000.00, 32000.00, NULL, NULL, 'P1003,P2004');

-- --------------------------------------------------------

--
-- Table structure for table `purchase_returns`
--

CREATE TABLE `purchase_returns` (
  `return_id` int(11) NOT NULL,
  `return_number` varchar(50) NOT NULL,
  `purchase_id` int(11) DEFAULT NULL,
  `supplier_id` int(11) NOT NULL,
  `return_date` date NOT NULL,
  `total_amount` decimal(10,2) NOT NULL DEFAULT 0.00,
  `reason` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `created_by` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `purchase_return_items`
--

CREATE TABLE `purchase_return_items` (
  `id` int(11) NOT NULL,
  `return_id` int(11) NOT NULL,
  `product_id` int(11) NOT NULL,
  `batch_id` int(11) DEFAULT NULL,
  `quantity` decimal(10,2) NOT NULL,
  `unit_price` decimal(10,2) NOT NULL,
  `total_amount` decimal(10,2) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `sale_returns`
--

CREATE TABLE `sale_returns` (
  `return_id` int(11) NOT NULL,
  `return_number` varchar(50) NOT NULL,
  `invoice_id` int(11) DEFAULT NULL,
  `customer_id` int(11) NOT NULL,
  `return_date` date NOT NULL,
  `subtotal` decimal(10,2) NOT NULL DEFAULT 0.00,
  `tax_amount` decimal(10,2) NOT NULL DEFAULT 0.00,
  `total_amount` decimal(10,2) NOT NULL DEFAULT 0.00,
  `reason` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `created_by` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `sale_returns`
--

INSERT INTO `sale_returns` (`return_id`, `return_number`, `invoice_id`, `customer_id`, `return_date`, `subtotal`, `tax_amount`, `total_amount`, `reason`, `created_at`, `created_by`) VALUES
(3, 'SR-20260123-921', 3, 5, '2026-01-23', 0.00, 0.00, 10000.00, '', '2026-01-22 23:33:56', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `sale_return_items`
--

CREATE TABLE `sale_return_items` (
  `id` int(11) NOT NULL,
  `return_id` int(11) NOT NULL,
  `product_id` int(11) NOT NULL,
  `batch_id` int(11) DEFAULT NULL,
  `serial_id` int(11) DEFAULT NULL,
  `quantity` decimal(10,2) NOT NULL,
  `unit_price` decimal(10,2) NOT NULL,
  `tax_amount` decimal(10,2) NOT NULL DEFAULT 0.00,
  `total_amount` decimal(10,2) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `sale_return_items`
--

INSERT INTO `sale_return_items` (`id`, `return_id`, `product_id`, `batch_id`, `serial_id`, `quantity`, `unit_price`, `tax_amount`, `total_amount`) VALUES
(3, 3, 12, NULL, NULL, 1.00, 10000.00, 0.00, 10000.00);

-- --------------------------------------------------------

--
-- Table structure for table `smtp_settings`
--

CREATE TABLE `smtp_settings` (
  `id` int(11) NOT NULL,
  `host` varchar(255) NOT NULL,
  `port` int(5) NOT NULL DEFAULT 587,
  `username` varchar(255) NOT NULL,
  `password` varchar(255) NOT NULL,
  `encryption` enum('tls','ssl','none') NOT NULL DEFAULT 'tls',
  `from_email` varchar(255) NOT NULL,
  `from_name` varchar(255) NOT NULL,
  `status` enum('active','inactive') NOT NULL DEFAULT 'active',
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `smtp_settings`
--

INSERT INTO `smtp_settings` (`id`, `host`, `port`, `username`, `password`, `encryption`, `from_email`, `from_name`, `status`, `updated_at`) VALUES
(1, 'smtp.gmail.com', 587, 'your-email@gmail.com', '', 'tls', 'noreply@billit.com', 'Billit Notification', 'active', '2026-01-22 21:49:27');

-- --------------------------------------------------------

--
-- Table structure for table `stock_transactions`
--

CREATE TABLE `stock_transactions` (
  `transaction_id` int(11) NOT NULL,
  `product_id` int(11) NOT NULL,
  `transaction_type` enum('stock_in','stock_out','adjustment') NOT NULL,
  `quantity` int(11) NOT NULL,
  `unit_price` decimal(10,2) DEFAULT NULL,
  `total_amount` decimal(10,2) DEFAULT NULL,
  `reference_number` varchar(50) DEFAULT NULL,
  `notes` text DEFAULT NULL,
  `transaction_date` datetime NOT NULL DEFAULT current_timestamp(),
  `created_by` int(11) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `stock_transactions`
--

INSERT INTO `stock_transactions` (`transaction_id`, `product_id`, `transaction_type`, `quantity`, `unit_price`, `total_amount`, `reference_number`, `notes`, `transaction_date`, `created_by`, `created_at`) VALUES
(1, 1, 'stock_in', 100, 25.99, 2599.00, 'PO-2026-001', 'Initial stock purchase', '2026-01-15 10:00:00', 1, '2026-01-22 20:05:37'),
(2, 1, 'stock_in', 50, 25.99, 1299.50, 'PO-2026-002', 'Restock order', '2026-01-20 14:30:00', 1, '2026-01-22 20:05:37'),
(3, 2, 'stock_in', 80, 35.50, 2840.00, 'PO-2026-003', 'Initial stock purchase', '2026-01-15 10:00:00', 1, '2026-01-22 20:05:37'),
(4, 3, 'stock_in', 50, 199.99, 9999.50, 'PO-2026-004', 'Initial stock purchase', '2026-01-16 11:00:00', 1, '2026-01-22 20:05:37'),
(5, 3, 'stock_out', 5, 199.99, 999.95, 'SO-2026-001', 'Sales order', '2026-01-22 09:15:00', 2, '2026-01-22 20:05:37'),
(6, 4, 'stock_in', 200, 5.99, 1198.00, 'PO-2026-005', 'Bulk paper order', '2026-01-17 13:00:00', 1, '2026-01-22 20:05:37'),
(7, 5, 'stock_in', 500, 0.50, 250.00, 'PO-2026-006', 'Pen bulk order', '2026-01-18 15:00:00', 1, '2026-01-22 20:05:37'),
(8, 12, 'stock_in', 3, 13000.00, 39000.00, 'PUR-1', 'Purchase received', '2026-01-23 00:00:00', NULL, '2026-01-22 22:36:38'),
(9, 12, 'stock_in', 2, 16000.00, 32000.00, 'PUR-1', 'Purchase received', '2026-01-23 00:00:00', NULL, '2026-01-22 22:36:38');

-- --------------------------------------------------------

--
-- Table structure for table `suppliers`
--

CREATE TABLE `suppliers` (
  `supplier_id` int(11) NOT NULL,
  `supplier_name` varchar(100) NOT NULL,
  `contact_person` varchar(100) DEFAULT NULL,
  `email` varchar(100) DEFAULT NULL,
  `phone` varchar(20) DEFAULT NULL,
  `address` text DEFAULT NULL,
  `city` varchar(50) DEFAULT NULL,
  `country` varchar(50) DEFAULT NULL,
  `status` enum('active','inactive') NOT NULL DEFAULT 'active',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `suppliers`
--

INSERT INTO `suppliers` (`supplier_id`, `supplier_name`, `contact_person`, `email`, `phone`, `address`, `city`, `country`, `status`, `created_at`, `updated_at`) VALUES
(1, 'Tech Solutions Ltd', 'John Smith', 'john@techsolutions.com', '+1-555-0101', '123 Tech Street', 'New York', 'USA', 'active', '2026-01-22 20:05:37', '2026-01-22 20:05:37'),
(2, 'Office Depot Inc', 'Sarah Johnson', 'sarah@officedepot.com', '+1-555-0102', '456 Supply Avenue', 'Chicago', 'USA', 'active', '2026-01-22 20:05:37', '2026-01-22 20:05:37'),
(3, 'Global Hardware Co', 'Mike Wilson', 'mike@globalhardware.com', '+1-555-0103', '789 Hardware Road', 'Los Angeles', 'USA', 'active', '2026-01-22 20:05:37', '2026-01-22 20:05:37'),
(4, 'Premium Furniture', 'Emily Brown', 'emily@premiumfurniture.com', '+1-555-0104', '321 Furniture Lane', 'Boston', 'USA', 'active', '2026-01-22 20:05:37', '2026-01-22 20:05:37');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `user_id` int(11) NOT NULL,
  `username` varchar(50) NOT NULL,
  `password` varchar(255) NOT NULL,
  `full_name` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL,
  `role` enum('admin','manager','staff') NOT NULL DEFAULT 'staff',
  `status` enum('active','inactive') NOT NULL DEFAULT 'active',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`user_id`, `username`, `password`, `full_name`, `email`, `role`, `status`, `created_at`, `updated_at`) VALUES
(1, 'admin', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'System Administrator', 'admin@inventory.com', 'admin', 'active', '2026-01-22 20:05:37', '2026-01-22 20:05:37'),
(2, 'manager', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Store Manager', 'manager@inventory.com', 'manager', 'active', '2026-01-22 20:05:37', '2026-01-22 20:05:37'),
(3, 'staff', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Staff Member', 'staff@inventory.com', 'staff', 'active', '2026-01-22 20:05:37', '2026-01-22 20:05:37');

-- --------------------------------------------------------

--
-- Stand-in structure for view `view_gst_summary`
-- (See below for the actual view)
--
CREATE TABLE `view_gst_summary` (
`month` varchar(7)
,`total_taxable` decimal(32,2)
,`total_cgst` decimal(32,2)
,`total_sgst` decimal(32,2)
,`total_igst` decimal(32,2)
,`total_gst` decimal(32,2)
,`total_invoice_value` decimal(32,2)
,`invoice_count` bigint(21)
);

-- --------------------------------------------------------

--
-- Stand-in structure for view `view_invoice_summary`
-- (See below for the actual view)
--
CREATE TABLE `view_invoice_summary` (
`invoice_id` int(11)
,`invoice_number` varchar(50)
,`invoice_date` date
,`customer_name` varchar(200)
,`customer_type` enum('B2B','B2C')
,`subtotal` decimal(10,2)
,`discount_amount` decimal(10,2)
,`taxable_amount` decimal(10,2)
,`cgst_amount` decimal(10,2)
,`sgst_amount` decimal(10,2)
,`igst_amount` decimal(10,2)
,`total_tax` decimal(10,2)
,`total_amount` decimal(10,2)
,`amount_paid` decimal(10,2)
,`amount_due` decimal(10,2)
,`payment_status` enum('unpaid','partial','paid')
,`invoice_status` enum('draft','finalized','cancelled')
,`created_by_name` varchar(50)
,`created_at` timestamp
);

-- --------------------------------------------------------

--
-- Stand-in structure for view `view_low_stock_products`
-- (See below for the actual view)
--
CREATE TABLE `view_low_stock_products` (
`product_id` int(11)
,`product_code` varchar(50)
,`product_name` varchar(200)
,`category_name` varchar(100)
,`quantity_in_stock` int(11)
,`reorder_level` int(11)
,`shortage_quantity` bigint(12)
,`unit_price` decimal(10,2)
);

-- --------------------------------------------------------

--
-- Stand-in structure for view `view_product_inventory`
-- (See below for the actual view)
--
CREATE TABLE `view_product_inventory` (
`product_id` int(11)
,`product_code` varchar(50)
,`product_name` varchar(200)
,`description` text
,`category_name` varchar(100)
,`supplier_name` varchar(100)
,`unit_price` decimal(10,2)
,`quantity_in_stock` int(11)
,`reorder_level` int(11)
,`unit_of_measure` varchar(20)
,`total_value` decimal(20,2)
,`status` enum('active','inactive','discontinued')
,`created_at` timestamp
,`updated_at` timestamp
);

-- --------------------------------------------------------

--
-- Structure for view `view_gst_summary`
--
DROP TABLE IF EXISTS `view_gst_summary`;

CREATE ALGORITHM=UNDEFINED DEFINER=`root`@`localhost` SQL SECURITY DEFINER VIEW `view_gst_summary`  AS SELECT date_format(`invoices`.`invoice_date`,'%Y-%m') AS `month`, sum(`invoices`.`taxable_amount`) AS `total_taxable`, sum(`invoices`.`cgst_amount`) AS `total_cgst`, sum(`invoices`.`sgst_amount`) AS `total_sgst`, sum(`invoices`.`igst_amount`) AS `total_igst`, sum(`invoices`.`total_tax`) AS `total_gst`, sum(`invoices`.`total_amount`) AS `total_invoice_value`, count(0) AS `invoice_count` FROM `invoices` WHERE `invoices`.`invoice_status` = 'finalized' GROUP BY date_format(`invoices`.`invoice_date`,'%Y-%m') ORDER BY date_format(`invoices`.`invoice_date`,'%Y-%m') DESC ;

-- --------------------------------------------------------

--
-- Structure for view `view_invoice_summary`
--
DROP TABLE IF EXISTS `view_invoice_summary`;

CREATE ALGORITHM=UNDEFINED DEFINER=`root`@`localhost` SQL SECURITY DEFINER VIEW `view_invoice_summary`  AS SELECT `i`.`invoice_id` AS `invoice_id`, `i`.`invoice_number` AS `invoice_number`, `i`.`invoice_date` AS `invoice_date`, `c`.`customer_name` AS `customer_name`, `c`.`customer_type` AS `customer_type`, `i`.`subtotal` AS `subtotal`, `i`.`discount_amount` AS `discount_amount`, `i`.`taxable_amount` AS `taxable_amount`, `i`.`cgst_amount` AS `cgst_amount`, `i`.`sgst_amount` AS `sgst_amount`, `i`.`igst_amount` AS `igst_amount`, `i`.`total_tax` AS `total_tax`, `i`.`total_amount` AS `total_amount`, `i`.`amount_paid` AS `amount_paid`, `i`.`amount_due` AS `amount_due`, `i`.`payment_status` AS `payment_status`, `i`.`invoice_status` AS `invoice_status`, `u`.`username` AS `created_by_name`, `i`.`created_at` AS `created_at` FROM ((`invoices` `i` join `customers` `c` on(`i`.`customer_id` = `c`.`customer_id`)) left join `users` `u` on(`i`.`created_by` = `u`.`user_id`)) ORDER BY `i`.`invoice_date` DESC, `i`.`invoice_id` DESC ;

-- --------------------------------------------------------

--
-- Structure for view `view_low_stock_products`
--
DROP TABLE IF EXISTS `view_low_stock_products`;

CREATE ALGORITHM=UNDEFINED DEFINER=`root`@`localhost` SQL SECURITY DEFINER VIEW `view_low_stock_products`  AS SELECT `p`.`product_id` AS `product_id`, `p`.`product_code` AS `product_code`, `p`.`product_name` AS `product_name`, `c`.`category_name` AS `category_name`, `p`.`quantity_in_stock` AS `quantity_in_stock`, `p`.`reorder_level` AS `reorder_level`, `p`.`reorder_level`- `p`.`quantity_in_stock` AS `shortage_quantity`, `p`.`unit_price` AS `unit_price` FROM (`products` `p` join `categories` `c` on(`p`.`category_id` = `c`.`category_id`)) WHERE `p`.`quantity_in_stock` <= `p`.`reorder_level` AND `p`.`status` = 'active' ORDER BY `p`.`reorder_level`- `p`.`quantity_in_stock` DESC ;

-- --------------------------------------------------------

--
-- Structure for view `view_product_inventory`
--
DROP TABLE IF EXISTS `view_product_inventory`;

CREATE ALGORITHM=UNDEFINED DEFINER=`root`@`localhost` SQL SECURITY DEFINER VIEW `view_product_inventory`  AS SELECT `p`.`product_id` AS `product_id`, `p`.`product_code` AS `product_code`, `p`.`product_name` AS `product_name`, `p`.`description` AS `description`, `c`.`category_name` AS `category_name`, `s`.`supplier_name` AS `supplier_name`, `p`.`unit_price` AS `unit_price`, `p`.`quantity_in_stock` AS `quantity_in_stock`, `p`.`reorder_level` AS `reorder_level`, `p`.`unit_of_measure` AS `unit_of_measure`, `p`.`quantity_in_stock`* `p`.`unit_price` AS `total_value`, `p`.`status` AS `status`, `p`.`created_at` AS `created_at`, `p`.`updated_at` AS `updated_at` FROM ((`products` `p` join `categories` `c` on(`p`.`category_id` = `c`.`category_id`)) left join `suppliers` `s` on(`p`.`supplier_id` = `s`.`supplier_id`)) ORDER BY `p`.`product_name` ASC ;

--
-- Indexes for dumped tables
--

--
-- Indexes for table `access_logs`
--
ALTER TABLE `access_logs`
  ADD PRIMARY KEY (`log_id`),
  ADD KEY `idx_user_id` (`user_id`),
  ADD KEY `idx_action` (`action`),
  ADD KEY `idx_created_at` (`created_at`);

--
-- Indexes for table `categories`
--
ALTER TABLE `categories`
  ADD PRIMARY KEY (`category_id`),
  ADD KEY `idx_category_name` (`category_name`),
  ADD KEY `idx_status` (`status`);

--
-- Indexes for table `company_settings`
--
ALTER TABLE `company_settings`
  ADD PRIMARY KEY (`setting_id`);

--
-- Indexes for table `customers`
--
ALTER TABLE `customers`
  ADD PRIMARY KEY (`customer_id`),
  ADD KEY `idx_customer_name` (`customer_name`),
  ADD KEY `idx_customer_type` (`customer_type`),
  ADD KEY `idx_status` (`status`);

--
-- Indexes for table `invoices`
--
ALTER TABLE `invoices`
  ADD PRIMARY KEY (`invoice_id`),
  ADD UNIQUE KEY `invoice_number` (`invoice_number`),
  ADD UNIQUE KEY `unique_invoice_number` (`invoice_number`),
  ADD KEY `idx_invoice_date` (`invoice_date`),
  ADD KEY `idx_customer` (`customer_id`),
  ADD KEY `idx_payment_status` (`payment_status`),
  ADD KEY `idx_invoice_status` (`invoice_status`),
  ADD KEY `created_by` (`created_by`);

--
-- Indexes for table `invoice_items`
--
ALTER TABLE `invoice_items`
  ADD PRIMARY KEY (`item_id`),
  ADD KEY `idx_invoice` (`invoice_id`),
  ADD KEY `idx_product` (`product_id`);

--
-- Indexes for table `password_resets`
--
ALTER TABLE `password_resets`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_email` (`email`),
  ADD KEY `idx_token` (`token`);

--
-- Indexes for table `payments`
--
ALTER TABLE `payments`
  ADD PRIMARY KEY (`payment_id`),
  ADD KEY `idx_invoice` (`invoice_id`),
  ADD KEY `idx_payment_date` (`payment_date`),
  ADD KEY `created_by` (`created_by`);

--
-- Indexes for table `products`
--
ALTER TABLE `products`
  ADD PRIMARY KEY (`product_id`),
  ADD UNIQUE KEY `product_code` (`product_code`),
  ADD UNIQUE KEY `unique_product_code` (`product_code`),
  ADD KEY `idx_product_name` (`product_name`),
  ADD KEY `idx_category` (`category_id`),
  ADD KEY `idx_supplier` (`supplier_id`),
  ADD KEY `idx_status` (`status`);

--
-- Indexes for table `product_batches`
--
ALTER TABLE `product_batches`
  ADD PRIMARY KEY (`batch_id`),
  ADD KEY `idx_product` (`product_id`),
  ADD KEY `idx_batch` (`batch_no`);

--
-- Indexes for table `product_serials`
--
ALTER TABLE `product_serials`
  ADD PRIMARY KEY (`serial_id`),
  ADD UNIQUE KEY `unique_serial` (`product_id`,`serial_no`),
  ADD KEY `idx_product` (`product_id`),
  ADD KEY `idx_status` (`status`);

--
-- Indexes for table `purchases`
--
ALTER TABLE `purchases`
  ADD PRIMARY KEY (`purchase_id`),
  ADD KEY `idx_supplier` (`supplier_id`),
  ADD KEY `idx_date` (`purchase_date`);

--
-- Indexes for table `purchase_items`
--
ALTER TABLE `purchase_items`
  ADD PRIMARY KEY (`item_id`),
  ADD KEY `idx_purchase` (`purchase_id`),
  ADD KEY `idx_product` (`product_id`);

--
-- Indexes for table `purchase_returns`
--
ALTER TABLE `purchase_returns`
  ADD PRIMARY KEY (`return_id`);

--
-- Indexes for table `purchase_return_items`
--
ALTER TABLE `purchase_return_items`
  ADD PRIMARY KEY (`id`),
  ADD KEY `return_id` (`return_id`);

--
-- Indexes for table `sale_returns`
--
ALTER TABLE `sale_returns`
  ADD PRIMARY KEY (`return_id`);

--
-- Indexes for table `sale_return_items`
--
ALTER TABLE `sale_return_items`
  ADD PRIMARY KEY (`id`),
  ADD KEY `return_id` (`return_id`),
  ADD KEY `product_id` (`product_id`);

--
-- Indexes for table `smtp_settings`
--
ALTER TABLE `smtp_settings`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `stock_transactions`
--
ALTER TABLE `stock_transactions`
  ADD PRIMARY KEY (`transaction_id`),
  ADD KEY `idx_product` (`product_id`),
  ADD KEY `idx_transaction_type` (`transaction_type`),
  ADD KEY `idx_transaction_date` (`transaction_date`),
  ADD KEY `idx_created_by` (`created_by`);

--
-- Indexes for table `suppliers`
--
ALTER TABLE `suppliers`
  ADD PRIMARY KEY (`supplier_id`),
  ADD KEY `idx_supplier_name` (`supplier_name`),
  ADD KEY `idx_status` (`status`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`user_id`),
  ADD UNIQUE KEY `username` (`username`),
  ADD KEY `idx_username` (`username`),
  ADD KEY `idx_status` (`status`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `access_logs`
--
ALTER TABLE `access_logs`
  MODIFY `log_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `categories`
--
ALTER TABLE `categories`
  MODIFY `category_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `company_settings`
--
ALTER TABLE `company_settings`
  MODIFY `setting_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `customers`
--
ALTER TABLE `customers`
  MODIFY `customer_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `invoices`
--
ALTER TABLE `invoices`
  MODIFY `invoice_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `invoice_items`
--
ALTER TABLE `invoice_items`
  MODIFY `item_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT for table `password_resets`
--
ALTER TABLE `password_resets`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `payments`
--
ALTER TABLE `payments`
  MODIFY `payment_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `products`
--
ALTER TABLE `products`
  MODIFY `product_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- AUTO_INCREMENT for table `product_batches`
--
ALTER TABLE `product_batches`
  MODIFY `batch_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `product_serials`
--
ALTER TABLE `product_serials`
  MODIFY `serial_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `purchases`
--
ALTER TABLE `purchases`
  MODIFY `purchase_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `purchase_items`
--
ALTER TABLE `purchase_items`
  MODIFY `item_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `purchase_returns`
--
ALTER TABLE `purchase_returns`
  MODIFY `return_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `purchase_return_items`
--
ALTER TABLE `purchase_return_items`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `sale_returns`
--
ALTER TABLE `sale_returns`
  MODIFY `return_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `sale_return_items`
--
ALTER TABLE `sale_return_items`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `smtp_settings`
--
ALTER TABLE `smtp_settings`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `stock_transactions`
--
ALTER TABLE `stock_transactions`
  MODIFY `transaction_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT for table `suppliers`
--
ALTER TABLE `suppliers`
  MODIFY `supplier_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `user_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `invoices`
--
ALTER TABLE `invoices`
  ADD CONSTRAINT `invoices_ibfk_1` FOREIGN KEY (`customer_id`) REFERENCES `customers` (`customer_id`) ON UPDATE CASCADE,
  ADD CONSTRAINT `invoices_ibfk_2` FOREIGN KEY (`created_by`) REFERENCES `users` (`user_id`) ON DELETE SET NULL ON UPDATE CASCADE;

--
-- Constraints for table `invoice_items`
--
ALTER TABLE `invoice_items`
  ADD CONSTRAINT `invoice_items_ibfk_1` FOREIGN KEY (`invoice_id`) REFERENCES `invoices` (`invoice_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `invoice_items_ibfk_2` FOREIGN KEY (`product_id`) REFERENCES `products` (`product_id`) ON UPDATE CASCADE;

--
-- Constraints for table `payments`
--
ALTER TABLE `payments`
  ADD CONSTRAINT `payments_ibfk_1` FOREIGN KEY (`invoice_id`) REFERENCES `invoices` (`invoice_id`) ON UPDATE CASCADE,
  ADD CONSTRAINT `payments_ibfk_2` FOREIGN KEY (`created_by`) REFERENCES `users` (`user_id`) ON DELETE SET NULL ON UPDATE CASCADE;

--
-- Constraints for table `products`
--
ALTER TABLE `products`
  ADD CONSTRAINT `products_ibfk_1` FOREIGN KEY (`category_id`) REFERENCES `categories` (`category_id`) ON UPDATE CASCADE,
  ADD CONSTRAINT `products_ibfk_2` FOREIGN KEY (`supplier_id`) REFERENCES `suppliers` (`supplier_id`) ON DELETE SET NULL ON UPDATE CASCADE;

--
-- Constraints for table `product_batches`
--
ALTER TABLE `product_batches`
  ADD CONSTRAINT `product_batches_ibfk_1` FOREIGN KEY (`product_id`) REFERENCES `products` (`product_id`);

--
-- Constraints for table `product_serials`
--
ALTER TABLE `product_serials`
  ADD CONSTRAINT `product_serials_ibfk_1` FOREIGN KEY (`product_id`) REFERENCES `products` (`product_id`);

--
-- Constraints for table `purchases`
--
ALTER TABLE `purchases`
  ADD CONSTRAINT `purchases_ibfk_1` FOREIGN KEY (`supplier_id`) REFERENCES `suppliers` (`supplier_id`);

--
-- Constraints for table `purchase_items`
--
ALTER TABLE `purchase_items`
  ADD CONSTRAINT `purchase_items_ibfk_1` FOREIGN KEY (`purchase_id`) REFERENCES `purchases` (`purchase_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `purchase_items_ibfk_2` FOREIGN KEY (`product_id`) REFERENCES `products` (`product_id`);

--
-- Constraints for table `stock_transactions`
--
ALTER TABLE `stock_transactions`
  ADD CONSTRAINT `stock_transactions_ibfk_1` FOREIGN KEY (`product_id`) REFERENCES `products` (`product_id`) ON UPDATE CASCADE,
  ADD CONSTRAINT `stock_transactions_ibfk_2` FOREIGN KEY (`created_by`) REFERENCES `users` (`user_id`) ON DELETE SET NULL ON UPDATE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
