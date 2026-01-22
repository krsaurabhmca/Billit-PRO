-- ================================================================
-- RETURNS MANAGEMENT EXTENSION
-- Purpose: Track Sales Returns (Credit Note) and Purchase Returns (Debit Note)
-- ================================================================

-- 1. SALE RETURNS (Customer Returns Item -> Stock Increase)
CREATE TABLE IF NOT EXISTS `sale_returns` (
  `return_id` int(11) NOT NULL AUTO_INCREMENT,
  `return_number` varchar(50) NOT NULL, -- e.g. SR-0001
  `invoice_id` int(11) DEFAULT NULL,    -- Linked Invoice
  `customer_id` int(11) NOT NULL,
  `return_date` date NOT NULL,
  `subtotal` decimal(10,2) NOT NULL DEFAULT 0.00,
  `tax_amount` decimal(10,2) NOT NULL DEFAULT 0.00,
  `total_amount` decimal(10,2) NOT NULL DEFAULT 0.00,
  `reason` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `created_by` int(11) DEFAULT NULL,
  PRIMARY KEY (`return_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS `sale_return_items` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `return_id` int(11) NOT NULL,
  `product_id` int(11) NOT NULL,
  `batch_id` int(11) DEFAULT NULL,      -- Specific batch being returned
  `serial_id` int(11) DEFAULT NULL,     -- Specific serial being returned (if single item)
  `quantity` decimal(10,2) NOT NULL,
  `unit_price` decimal(10,2) NOT NULL,  -- Price at which it was credited
  `tax_amount` decimal(10,2) NOT NULL DEFAULT 0.00,
  `total_amount` decimal(10,2) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `return_id` (`return_id`),
  KEY `product_id` (`product_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 2. PURCHASE RETURNS (Return Item to Supplier -> Stock Decrease)
CREATE TABLE IF NOT EXISTS `purchase_returns` (
  `return_id` int(11) NOT NULL AUTO_INCREMENT,
  `return_number` varchar(50) NOT NULL, -- e.g. PR-0001
  `purchase_id` int(11) DEFAULT NULL,   -- Linked Purchase
  `supplier_id` int(11) NOT NULL,
  `return_date` date NOT NULL,
  `total_amount` decimal(10,2) NOT NULL DEFAULT 0.00,
  `reason` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `created_by` int(11) DEFAULT NULL,
  PRIMARY KEY (`return_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS `purchase_return_items` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `return_id` int(11) NOT NULL,
  `product_id` int(11) NOT NULL,
  `batch_id` int(11) DEFAULT NULL,
  `quantity` decimal(10,2) NOT NULL,
  `unit_price` decimal(10,2) NOT NULL,
  `total_amount` decimal(10,2) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `return_id` (`return_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
