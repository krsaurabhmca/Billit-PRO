-- ============================================================================
-- PURCHASE & BATCH/SERIAL TRACKING EXTENSION
-- ============================================================================

-- ----------------------------------------------------------------------------
-- Update Products Table
-- ----------------------------------------------------------------------------
-- Add tracking type to distinguish between normal, batch-tracked, and serial/IMEI items
ALTER TABLE products 
ADD COLUMN tracking_type ENUM('none', 'batch', 'serial') NOT NULL DEFAULT 'none' AFTER unit_of_measure;

-- ----------------------------------------------------------------------------
-- Table: purchases
-- Purpose: Store Purchase Invoice headers (Purchase Management)
-- ----------------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS purchases (
    purchase_id INT(11) NOT NULL AUTO_INCREMENT,
    supplier_id INT(11) NOT NULL,
    supplier_invoice_no VARCHAR(50),  -- Supplier's Bill No
    purchase_date DATE NOT NULL,
    total_amount DECIMAL(10, 2) NOT NULL DEFAULT 0.00,
    paid_amount DECIMAL(10, 2) DEFAULT 0.00,
    payment_status ENUM('paid', 'partial', 'unpaid') DEFAULT 'unpaid',
    status ENUM('received', 'pending', 'cancelled') DEFAULT 'received', 
    notes TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    created_by INT(11),
    PRIMARY KEY (purchase_id),
    INDEX idx_supplier (supplier_id),
    INDEX idx_date (purchase_date),
    FOREIGN KEY (supplier_id) REFERENCES suppliers(supplier_id) ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ----------------------------------------------------------------------------
-- Table: purchase_items
-- Purpose: Line items for purchases
-- ----------------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS purchase_items (
    item_id INT(11) NOT NULL AUTO_INCREMENT,
    purchase_id INT(11) NOT NULL,
    product_id INT(11) NOT NULL,
    quantity INT(11) NOT NULL,
    unit_cost DECIMAL(10, 2) NOT NULL,
    total_cost DECIMAL(10, 2) NOT NULL,
    -- Store Input Details for historic reference
    batch_no VARCHAR(50) DEFAULT NULL,
    expiry_date DATE DEFAULT NULL,
    serial_numbers TEXT DEFAULT NULL, -- Comma separated for reference
    PRIMARY KEY (item_id),
    INDEX idx_purchase (purchase_id),
    INDEX idx_product (product_id),
    FOREIGN KEY (purchase_id) REFERENCES purchases(purchase_id) ON DELETE CASCADE,
    FOREIGN KEY (product_id) REFERENCES products(product_id) ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ----------------------------------------------------------------------------
-- Table: product_batches
-- Purpose: Track inventory by batch number (for 'batch' tracking_type)
-- ----------------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS product_batches (
    batch_id INT(11) NOT NULL AUTO_INCREMENT,
    product_id INT(11) NOT NULL,
    batch_no VARCHAR(50) NOT NULL,
    expiry_date DATE DEFAULT NULL,
    purchase_item_id INT(11) DEFAULT NULL, -- Link to source
    quantity_received INT(11) NOT NULL,
    quantity_remaining INT(11) NOT NULL,
    status ENUM('active', 'expired', 'empty') DEFAULT 'active',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (batch_id),
    INDEX idx_product (product_id),
    INDEX idx_batch (batch_no),
    FOREIGN KEY (product_id) REFERENCES products(product_id) ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ----------------------------------------------------------------------------
-- Table: product_serials
-- Purpose: Track individual items by IMEI/Serial (for 'serial' tracking_type)
-- ----------------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS product_serials (
    serial_id INT(11) NOT NULL AUTO_INCREMENT,
    product_id INT(11) NOT NULL,
    serial_no VARCHAR(100) NOT NULL, -- IMEI or Serial
    purchase_item_id INT(11) DEFAULT NULL, -- Link to source
    is_sold TINYINT(1) DEFAULT 0,
    date_sold DATE DEFAULT NULL,
    invoice_item_id INT(11) DEFAULT NULL, -- Link to sale
    status ENUM('available', 'sold', 'returned', 'defective') DEFAULT 'available',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (serial_id),
    UNIQUE KEY unique_serial (product_id, serial_no), -- Serial unique per product
    INDEX idx_product (product_id),
    INDEX idx_status (status),
    FOREIGN KEY (product_id) REFERENCES products(product_id) ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Update invoice_items to support tracking
ALTER TABLE invoice_items 
ADD COLUMN batch_id INT(11) DEFAULT NULL AFTER product_id,
ADD COLUMN serial_ids TEXT DEFAULT NULL AFTER batch_id; -- Comma separated IDs from product_serials
