-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Jan 23, 2026 at 09:38 AM
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
(1, 'General', NULL, 'active', '2026-01-23 06:46:32', '2026-01-23 06:46:32');

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
(1, 'OfferPlant Technologies', 'Kumar Bhawan Umanagar\r\nNear bazar Samiiti', 'Saran', 'Bihar', '10', '841301', '10AACCO5419Q1ZB', '', '9431426600', 'ask@offerplant.com', 'assets/uploads/company_logo_1769152345.png', 'INV', 1, '1. term 1\r\n2. term 2 here\r\n3. Terms 3', 'State Bank of India', '37215658849', 'SBIN0000054', 'Main Branch Chapra', '2026-01-23 06:46:21', '2026-01-23 07:41:57', '#aa35f8');

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
(1, 'Walk-in Customer', 'B2C', NULL, NULL, '', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0.00, 0.00, 'active', '2026-01-23 06:46:39', '2026-01-23 06:46:39'),
(2, 'Saurabh', 'B2C', NULL, NULL, '9431426600', NULL, NULL, '', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0.00, 0.00, 'active', '2026-01-23 07:35:27', '2026-01-23 07:35:27'),
(3, 'Kumar Saurabh', 'B2C', NULL, NULL, '9431426601', NULL, NULL, 'test', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0.00, 0.00, 'active', '2026-01-23 07:37:14', '2026-01-23 07:37:14'),
(4, 'Kumar Gaurav', 'B2C', NULL, NULL, '8102930609', NULL, NULL, '', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0.00, 0.00, 'active', '2026-01-23 08:09:28', '2026-01-23 08:09:28');

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
(1, 'INV0001', '2026-01-23', 1, 'Walk-in Customer', '', '', '', 200.00, 'percentage', 18.00, 36.00, 164.00, 0.00, 0.00, 29.52, 29.52, 0.48, 194.00, 150.00, 44.00, 'partial', 'finalized', '', '', 1, '2026-01-23 06:49:52', '2026-01-23 08:05:59'),
(2, 'INV0002', '2026-01-23', 1, 'Walk-in Customer', '', '', '', 24000.00, 'percentage', 10.00, 2400.00, 21600.00, 0.00, 0.00, 0.00, 0.00, 0.00, 21600.00, 10000.00, 11600.00, 'partial', 'finalized', '', '', 1, '2026-01-23 06:56:09', '2026-01-23 06:56:30'),
(3, 'INV0003', '2026-01-23', 1, 'Walk-in Customer', '', '', '', 100.00, 'amount', 0.00, 0.00, 100.00, 0.00, 0.00, 0.00, 0.00, 0.00, 100.00, 0.00, 100.00, 'unpaid', 'finalized', 'POS Sale', '1. term 1\r\n2. term 2 here\r\n3. Terms 3', 1, '2026-01-23 07:24:28', '2026-01-23 07:24:28'),
(4, 'INV0004', '2026-01-23', 1, 'Walk-in Customer', '', '', '', 100.00, 'amount', 0.00, 0.00, 100.00, 0.00, 0.00, 0.00, 0.00, 0.00, 100.00, 0.00, 100.00, 'unpaid', 'finalized', 'POS Sale', '1. term 1\r\n2. term 2 here\r\n3. Terms 3', 1, '2026-01-23 07:37:52', '2026-01-23 07:37:52'),
(11, 'INV0005', '2026-01-23', 2, 'Saurabh', '', '', '', 100.00, 'amount', 0.00, 0.00, 100.00, 0.00, 0.00, 12.00, 12.00, 0.00, 112.00, 112.00, 0.00, 'paid', 'finalized', 'POS Sale', '1. term 1\r\n2. term 2 here\r\n3. Terms 3', 1, '2026-01-23 08:19:00', '2026-01-23 08:19:00'),
(12, 'INV0006', '2026-01-23', 1, 'Walk-in Customer', '', '', '', 24300.00, 'amount', 0.00, 0.00, 24300.00, 0.00, 0.00, 4356.00, 4356.00, 0.00, 28656.00, 28656.00, 0.00, 'paid', 'finalized', 'POS Sale', '1. term 1\r\n2. term 2 here\r\n3. Terms 3', 1, '2026-01-23 08:28:57', '2026-01-23 08:28:57'),
(13, 'INV0007', '2026-01-23', 2, 'Saurabh', '', '', '', 12000.00, 'percentage', 0.00, 0.00, 12000.00, 0.00, 0.00, 2160.00, 2160.00, 0.00, 14160.00, 12160.00, 2000.00, 'partial', 'finalized', '', '1. term 1\r\n2. term 2 here\r\n3. Terms 3', 1, '2026-01-23 08:31:32', '2026-01-23 08:32:04');

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
(1, 1, 1, NULL, NULL, 'Test', 'PROD289', '', NULL, 2.00, 'pcs', 100.00, 200.00, 0.00, 0.00, 164.00, 18.00, 0.00, 0.00, 0.00, 0.00, 18.00, 29.52, 193.52, '2026-01-23 06:49:52'),
(2, 2, 2, NULL, '2,3', 'Samsung', 'PRD0089', '', NULL, 2.00, 'pcs', 12000.00, 24000.00, 0.00, 0.00, 21600.00, 18.00, 0.00, 0.00, 0.00, 0.00, 18.00, 0.00, 21600.00, '2026-01-23 06:56:09'),
(3, 3, 1, NULL, NULL, 'Test', 'PROD289', '', NULL, 1.00, 'pcs', 100.00, 100.00, 0.00, 0.00, 100.00, 18.00, 0.00, 0.00, 0.00, 0.00, 18.00, 0.00, 100.00, '2026-01-23 07:24:28'),
(4, 4, 1, NULL, NULL, 'Test', 'PROD289', '', NULL, 1.00, 'pcs', 100.00, 100.00, 0.00, 0.00, 100.00, 18.00, 0.00, 0.00, 0.00, 0.00, 18.00, 0.00, 100.00, '2026-01-23 07:37:52'),
(12, 11, 1, NULL, NULL, 'Test', 'PROD289', '', NULL, 1.00, 'pcs', 100.00, 100.00, 0.00, 0.00, 100.00, 12.00, 0.00, 0.00, 0.00, 0.00, 12.00, 12.00, 112.00, '2026-01-23 08:19:00'),
(13, 12, 1, NULL, NULL, 'Test', 'PROD289', '', NULL, 3.00, 'pcs', 100.00, 300.00, 0.00, 0.00, 300.00, 12.00, 0.00, 0.00, 0.00, 0.00, 12.00, 36.00, 336.00, '2026-01-23 08:28:57'),
(14, 12, 2, NULL, NULL, 'Samsung', 'PRD0089', '', NULL, 2.00, 'pcs', 12000.00, 24000.00, 0.00, 0.00, 24000.00, 18.00, 0.00, 0.00, 0.00, 0.00, 18.00, 4320.00, 28320.00, '2026-01-23 08:28:57'),
(15, 13, 2, NULL, '4', 'Samsung', 'PRD0089', '', NULL, 1.00, 'pcs', 12000.00, 12000.00, 0.00, 0.00, 12000.00, 18.00, 0.00, 0.00, 0.00, 0.00, 18.00, 2160.00, 14160.00, '2026-01-23 08:31:32');

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
(1, 2, '2026-01-23', 'cash', 10000.00, '', '', 1, '2026-01-23 06:56:30'),
(2, 1, '2026-01-23', 'cash', 50.00, '', '', 1, '2026-01-23 07:15:13'),
(3, 1, '2026-01-23', 'cash', 100.00, '', '', 1, '2026-01-23 08:05:59'),
(4, 11, '2026-01-23', 'cash', 112.00, 'POS-1769156340-351', 'POS Payment', NULL, '2026-01-23 08:19:00'),
(5, 12, '2026-01-23', 'cash', 28656.00, 'POS-1769156937-180', 'POS Payment', NULL, '2026-01-23 08:28:57'),
(6, 13, '2026-01-23', 'upi', 10160.00, '', '', 1, '2026-01-23 08:31:48'),
(7, 13, '2026-01-23', 'card', 2000.00, '', '', 1, '2026-01-23 08:32:04');

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
(1, 'PROD289', 'Test', '1231233', '', 1, NULL, 100.00, 12.00, 2, 10, 'pcs', 'none', 'active', '2026-01-23 06:46:32', '2026-01-23 08:28:57'),
(2, 'PRD0089', 'Samsung', NULL, '', 1, 1, 12000.00, 18.00, 0, 10, 'pcs', 'serial', 'active', '2026-01-23 06:52:27', '2026-01-23 08:31:32'),
(3, 'PRD0090', 'VIVO V9', '25354534', '16GB 256GB Snapdragon', 1, 1, 19000.00, 12.00, 0, 3, 'pcs', 'serial', 'active', '2026-01-23 07:52:50', '2026-01-23 07:52:50');

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
(1, 2, 'SN001', 2, 0, NULL, NULL, 'available', '2026-01-23 06:54:13'),
(2, 2, 'SN002', 2, 1, '2026-01-23', 2, 'sold', '2026-01-23 06:54:13'),
(3, 2, 'SN005', 2, 1, '2026-01-23', 2, 'sold', '2026-01-23 06:54:13'),
(4, 2, 'SM009', 2, 1, '2026-01-23', 15, 'sold', '2026-01-23 06:54:13'),
(5, 2, 'SNO98', 2, 0, NULL, NULL, 'available', '2026-01-23 06:54:13');

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
(1, 1, 'INV-001', '2026-01-23', 1000.00, 0.00, 'unpaid', 'received', '', '2026-01-23 06:48:48', 1),
(2, 1, 'INV-004/2027', '2026-01-23', 145000.00, 0.00, 'unpaid', 'received', '', '2026-01-23 06:54:13', 1);

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
(1, 1, 1, 10, 100.00, 1000.00, NULL, NULL, NULL),
(2, 2, 2, 5, 29000.00, 145000.00, NULL, NULL, 'SN001,SN002,SN005,SM009,SNO98');

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
(1, 1, 'stock_in', 10, 100.00, 1000.00, 'PUR-1', 'Purchase received', '2026-01-23 00:00:00', NULL, '2026-01-23 06:48:48'),
(2, 2, 'stock_in', 5, 29000.00, 145000.00, 'PUR-2', 'Purchase received', '2026-01-23 00:00:00', NULL, '2026-01-23 06:54:13');

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
(1, 'Hardware Solution', '', '', '', '', '', '', 'active', '2026-01-23 06:48:09', '2026-01-23 06:48:09');

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
  MODIFY `log_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `categories`
--
ALTER TABLE `categories`
  MODIFY `category_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `company_settings`
--
ALTER TABLE `company_settings`
  MODIFY `setting_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `customers`
--
ALTER TABLE `customers`
  MODIFY `customer_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `invoices`
--
ALTER TABLE `invoices`
  MODIFY `invoice_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=14;

--
-- AUTO_INCREMENT for table `invoice_items`
--
ALTER TABLE `invoice_items`
  MODIFY `item_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=16;

--
-- AUTO_INCREMENT for table `password_resets`
--
ALTER TABLE `password_resets`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `payments`
--
ALTER TABLE `payments`
  MODIFY `payment_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT for table `products`
--
ALTER TABLE `products`
  MODIFY `product_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

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
  MODIFY `purchase_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

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
  MODIFY `return_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `sale_return_items`
--
ALTER TABLE `sale_return_items`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `smtp_settings`
--
ALTER TABLE `smtp_settings`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `stock_transactions`
--
ALTER TABLE `stock_transactions`
  MODIFY `transaction_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `suppliers`
--
ALTER TABLE `suppliers`
  MODIFY `supplier_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

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
