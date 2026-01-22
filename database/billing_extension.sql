-- ============================================================================
-- BILLING & GST SYSTEM - DATABASE EXTENSION
-- ============================================================================
-- Description: Additional tables for billing and invoice management with GST
-- Author: Inventory Management System
-- Date: 2026-01-23
-- ============================================================================

USE inventory_management;

-- ============================================================================
-- STEP 1: Create Company Settings Table
-- ============================================================================

CREATE TABLE company_settings (
    setting_id INT(11) NOT NULL AUTO_INCREMENT,
    company_name VARCHAR(200) NOT NULL,
    company_address TEXT,
    company_city VARCHAR(100),
    company_state VARCHAR(100),
    company_state_code VARCHAR(2),
    company_pincode VARCHAR(10),
    company_gstin VARCHAR(15),
    company_pan VARCHAR(10),
    company_phone VARCHAR(20),
    company_email VARCHAR(100),
    company_logo VARCHAR(255),
    invoice_prefix VARCHAR(10) DEFAULT 'INV',
    invoice_start_number INT(11) DEFAULT 1,
    terms_conditions TEXT,
    bank_name VARCHAR(100),
    bank_account_number VARCHAR(50),
    bank_ifsc VARCHAR(20),
    bank_branch VARCHAR(100),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (setting_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================================
-- STEP 2: Create Customers Table
-- ============================================================================

CREATE TABLE customers (
    customer_id INT(11) NOT NULL AUTO_INCREMENT,
    customer_name VARCHAR(200) NOT NULL,
    customer_type ENUM('B2B', 'B2C') NOT NULL DEFAULT 'B2C',
    contact_person VARCHAR(100),
    email VARCHAR(100),
    phone VARCHAR(20),
    gstin VARCHAR(15),
    pan VARCHAR(10),
    billing_address TEXT,
    billing_city VARCHAR(100),
    billing_state VARCHAR(100),
    billing_state_code VARCHAR(2),
    billing_pincode VARCHAR(10),
    shipping_address TEXT,
    shipping_city VARCHAR(100),
    shipping_state VARCHAR(100),
    shipping_state_code VARCHAR(2),
    shipping_pincode VARCHAR(10),
    credit_limit DECIMAL(10, 2) DEFAULT 0.00,
    opening_balance DECIMAL(10, 2) DEFAULT 0.00,
    status ENUM('active', 'inactive') NOT NULL DEFAULT 'active',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (customer_id),
    INDEX idx_customer_name (customer_name),
    INDEX idx_customer_type (customer_type),
    INDEX idx_status (status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================================
-- STEP 3: Create Invoices Table
-- ============================================================================

CREATE TABLE invoices (
    invoice_id INT(11) NOT NULL AUTO_INCREMENT,
    invoice_number VARCHAR(50) NOT NULL UNIQUE,
    invoice_date DATE NOT NULL,
    customer_id INT(11) NOT NULL,
    customer_name VARCHAR(200) NOT NULL,
    customer_gstin VARCHAR(15),
    customer_address TEXT,
    customer_state_code VARCHAR(2),
    subtotal DECIMAL(10, 2) NOT NULL DEFAULT 0.00,
    discount_type ENUM('percentage', 'amount') DEFAULT 'percentage',
    discount_value DECIMAL(10, 2) DEFAULT 0.00,
    discount_amount DECIMAL(10, 2) DEFAULT 0.00,
    taxable_amount DECIMAL(10, 2) NOT NULL DEFAULT 0.00,
    cgst_amount DECIMAL(10, 2) DEFAULT 0.00,
    sgst_amount DECIMAL(10, 2) DEFAULT 0.00,
    igst_amount DECIMAL(10, 2) DEFAULT 0.00,
    total_tax DECIMAL(10, 2) DEFAULT 0.00,
    round_off DECIMAL(10, 2) DEFAULT 0.00,
    total_amount DECIMAL(10, 2) NOT NULL DEFAULT 0.00,
    amount_paid DECIMAL(10, 2) DEFAULT 0.00,
    amount_due DECIMAL(10, 2) DEFAULT 0.00,
    payment_status ENUM('unpaid', 'partial', 'paid') NOT NULL DEFAULT 'unpaid',
    invoice_status ENUM('draft', 'finalized', 'cancelled') NOT NULL DEFAULT 'draft',
    notes TEXT,
    terms_conditions TEXT,
    created_by INT(11),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (invoice_id),
    UNIQUE KEY unique_invoice_number (invoice_number),
    INDEX idx_invoice_date (invoice_date),
    INDEX idx_customer (customer_id),
    INDEX idx_payment_status (payment_status),
    INDEX idx_invoice_status (invoice_status),
    FOREIGN KEY (customer_id) REFERENCES customers(customer_id) ON DELETE RESTRICT ON UPDATE CASCADE,
    FOREIGN KEY (created_by) REFERENCES users(user_id) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================================
-- STEP 4: Create Invoice Items Table
-- ============================================================================

CREATE TABLE invoice_items (
    item_id INT(11) NOT NULL AUTO_INCREMENT,
    invoice_id INT(11) NOT NULL,
    product_id INT(11) NOT NULL,
    product_name VARCHAR(200) NOT NULL,
    product_code VARCHAR(50),
    hsn_code VARCHAR(20),
    description TEXT,
    quantity DECIMAL(10, 2) NOT NULL,
    unit_of_measure VARCHAR(20),
    unit_price DECIMAL(10, 2) NOT NULL,
    item_total DECIMAL(10, 2) NOT NULL,
    discount_percentage DECIMAL(5, 2) DEFAULT 0.00,
    discount_amount DECIMAL(10, 2) DEFAULT 0.00,
    taxable_amount DECIMAL(10, 2) NOT NULL,
    gst_rate DECIMAL(5, 2) NOT NULL DEFAULT 0.00,
    cgst_rate DECIMAL(5, 2) DEFAULT 0.00,
    cgst_amount DECIMAL(10, 2) DEFAULT 0.00,
    sgst_rate DECIMAL(5, 2) DEFAULT 0.00,
    sgst_amount DECIMAL(10, 2) DEFAULT 0.00,
    igst_rate DECIMAL(5, 2) DEFAULT 0.00,
    igst_amount DECIMAL(10, 2) DEFAULT 0.00,
    total_amount DECIMAL(10, 2) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (item_id),
    INDEX idx_invoice (invoice_id),
    INDEX idx_product (product_id),
    FOREIGN KEY (invoice_id) REFERENCES invoices(invoice_id) ON DELETE CASCADE ON UPDATE CASCADE,
    FOREIGN KEY (product_id) REFERENCES products(product_id) ON DELETE RESTRICT ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================================
-- STEP 5: Create Payments Table
-- ============================================================================

CREATE TABLE payments (
    payment_id INT(11) NOT NULL AUTO_INCREMENT,
    invoice_id INT(11) NOT NULL,
    payment_date DATE NOT NULL,
    payment_method ENUM('cash', 'card', 'upi', 'bank_transfer', 'cheque', 'other') NOT NULL,
    payment_amount DECIMAL(10, 2) NOT NULL,
    reference_number VARCHAR(100),
    notes TEXT,
    created_by INT(11),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (payment_id),
    INDEX idx_invoice (invoice_id),
    INDEX idx_payment_date (payment_date),
    FOREIGN KEY (invoice_id) REFERENCES invoices(invoice_id) ON DELETE RESTRICT ON UPDATE CASCADE,
    FOREIGN KEY (created_by) REFERENCES users(user_id) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================================
-- STEP 6: Add GST Fields to Products Table
-- ============================================================================

ALTER TABLE products 
ADD COLUMN hsn_code VARCHAR(20) AFTER product_name,
ADD COLUMN gst_rate DECIMAL(5, 2) NOT NULL DEFAULT 18.00 AFTER unit_price;

-- ============================================================================
-- STEP 7: Insert Default Company Settings
-- ============================================================================

INSERT INTO company_settings 
(company_name, company_address, company_city, company_state, company_state_code, 
 company_gstin, company_phone, company_email, invoice_prefix, invoice_start_number, 
 terms_conditions) 
VALUES 
('Your Company Name', 
 '123 Business Street', 
 'Mumbai', 
 'Maharashtra', 
 '27',
 '27AAAAA0000A1Z5',
 '+91-9876543210',
 'info@yourcompany.com',
 'INV',
 1,
 '1. Payment is due within 30 days of invoice date.
2. Please make cheques payable to Your Company Name.
3. Goods once sold will not be taken back.
4. Subject to Mumbai Jurisdiction.');

-- ============================================================================
-- STEP 8: Insert Sample Customers
-- ============================================================================

INSERT INTO customers 
(customer_name, customer_type, contact_person, email, phone, gstin, 
 billing_address, billing_city, billing_state, billing_state_code, billing_pincode, status) 
VALUES 
('ABC Enterprises Pvt Ltd', 'B2B', 'Rajesh Kumar', 'rajesh@abcenterprises.com', '+91-9876543211', 
 '27BBBBB1111B1Z5', '456 Corporate Avenue', 'Mumbai', 'Maharashtra', '27', '400001', 'active'),

('XYZ Trading Co', 'B2B', 'Priya Sharma', 'priya@xyztrading.com', '+91-9876543212', 
 '29CCCCC2222C1Z5', '789 Trade Center', 'Bangalore', 'Karnataka', '29', '560001', 'active'),

('Walk-in Customer', 'B2C', NULL, NULL, NULL, NULL, 
 NULL, NULL, NULL, NULL, NULL, 'active'),

('Retail Customer', 'B2C', 'Amit Patel', 'amit@gmail.com', '+91-9876543213', NULL, 
 '321 Residential Area', 'Mumbai', 'Maharashtra', '27', '400002', 'active');

-- ============================================================================
-- STEP 9: Update Products with HSN and GST Rate
-- ============================================================================

UPDATE products SET hsn_code = '8471', gst_rate = 18.00 WHERE product_id = 1; -- Wireless Mouse
UPDATE products SET hsn_code = '8471', gst_rate = 18.00 WHERE product_id = 2; -- USB Keyboard
UPDATE products SET hsn_code = '8528', gst_rate = 18.00 WHERE product_id = 3; -- LED Monitor
UPDATE products SET hsn_code = '4802', gst_rate = 12.00 WHERE product_id = 4; -- A4 Paper
UPDATE products SET hsn_code = '9608', gst_rate = 12.00 WHERE product_id = 5; -- Ballpoint Pen
UPDATE products SET hsn_code = '8305', gst_rate = 18.00 WHERE product_id = 6; -- Stapler
UPDATE products SET hsn_code = '9401', gst_rate = 18.00 WHERE product_id = 7; -- Office Chair
UPDATE products SET hsn_code = '9405', gst_rate = 18.00 WHERE product_id = 8; -- Desk Lamp
UPDATE products SET hsn_code = '8205', gst_rate = 18.00 WHERE product_id = 9; -- Screwdriver Set
UPDATE products SET hsn_code = '8205', gst_rate = 18.00 WHERE product_id = 10; -- Hammer

-- ============================================================================
-- STEP 10: Create Views for Reporting
-- ============================================================================

-- View: Invoice Summary with Customer Details
CREATE VIEW view_invoice_summary AS
SELECT 
    i.invoice_id,
    i.invoice_number,
    i.invoice_date,
    c.customer_name,
    c.customer_type,
    i.subtotal,
    i.discount_amount,
    i.taxable_amount,
    i.cgst_amount,
    i.sgst_amount,
    i.igst_amount,
    i.total_tax,
    i.total_amount,
    i.amount_paid,
    i.amount_due,
    i.payment_status,
    i.invoice_status,
    u.username as created_by_name,
    i.created_at
FROM invoices i
INNER JOIN customers c ON i.customer_id = c.customer_id
LEFT JOIN users u ON i.created_by = u.user_id
ORDER BY i.invoice_date DESC, i.invoice_id DESC;

-- View: GST Summary Report
CREATE VIEW view_gst_summary AS
SELECT 
    DATE_FORMAT(invoice_date, '%Y-%m') as month,
    SUM(taxable_amount) as total_taxable,
    SUM(cgst_amount) as total_cgst,
    SUM(sgst_amount) as total_sgst,
    SUM(igst_amount) as total_igst,
    SUM(total_tax) as total_gst,
    SUM(total_amount) as total_invoice_value,
    COUNT(*) as invoice_count
FROM invoices
WHERE invoice_status = 'finalized'
GROUP BY DATE_FORMAT(invoice_date, '%Y-%m')
ORDER BY month DESC;

-- ============================================================================
-- STEP 11: Verification Queries
-- ============================================================================

-- Check all tables were created
SHOW TABLES LIKE '%company_settings%';
SHOW TABLES LIKE '%customers%';
SHOW TABLES LIKE '%invoices%';
SHOW TABLES LIKE '%invoice_items%';
SHOW TABLES LIKE '%payments%';

-- Verify company settings
SELECT * FROM company_settings;

-- Verify customers
SELECT customer_id, customer_name, customer_type, gstin, billing_state FROM customers;

-- Verify products have GST fields
SELECT product_id, product_code, product_name, hsn_code, gst_rate FROM products LIMIT 5;

-- ============================================================================
-- SETUP COMPLETE!
-- ============================================================================
-- Next Steps:
-- 1. Update company settings with your actual details
-- 2. Start creating invoices
-- 3. Generate GST reports
-- ============================================================================
