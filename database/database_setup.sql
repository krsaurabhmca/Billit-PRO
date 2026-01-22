-- ============================================================================
-- INVENTORY MANAGEMENT SYSTEM - DATABASE SETUP SCRIPT
-- ============================================================================
-- Description: Complete database schema for inventory management application
-- Author: Inventory Management System
-- Date: 2026-01-23
-- Database: MySQL/MariaDB
-- ============================================================================

-- STEP 1: Create Database
-- ============================================================================
-- Drop database if exists (WARNING: This will delete all existing data)
DROP DATABASE IF EXISTS inventory_management;

-- Create new database with UTF-8 encoding
CREATE DATABASE inventory_management 
CHARACTER SET utf8mb4 
COLLATE utf8mb4_unicode_ci;

-- Select the database for use
USE inventory_management;

-- ============================================================================
-- STEP 2: Create Tables
-- ============================================================================

-- ----------------------------------------------------------------------------
-- Table: users
-- Purpose: Store user authentication and profile information
-- ----------------------------------------------------------------------------
CREATE TABLE users (
    user_id INT(11) NOT NULL AUTO_INCREMENT,
    username VARCHAR(50) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    full_name VARCHAR(100) NOT NULL,
    email VARCHAR(100) NOT NULL,
    role ENUM('admin', 'manager', 'staff') NOT NULL DEFAULT 'staff',
    status ENUM('active', 'inactive') NOT NULL DEFAULT 'active',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (user_id),
    INDEX idx_username (username),
    INDEX idx_status (status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ----------------------------------------------------------------------------
-- Table: categories
-- Purpose: Store product categories for organization
-- ----------------------------------------------------------------------------
CREATE TABLE categories (
    category_id INT(11) NOT NULL AUTO_INCREMENT,
    category_name VARCHAR(100) NOT NULL,
    description TEXT,
    status ENUM('active', 'inactive') NOT NULL DEFAULT 'active',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (category_id),
    INDEX idx_category_name (category_name),
    INDEX idx_status (status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ----------------------------------------------------------------------------
-- Table: suppliers
-- Purpose: Store supplier/vendor information
-- ----------------------------------------------------------------------------
CREATE TABLE suppliers (
    supplier_id INT(11) NOT NULL AUTO_INCREMENT,
    supplier_name VARCHAR(100) NOT NULL,
    contact_person VARCHAR(100),
    email VARCHAR(100),
    phone VARCHAR(20),
    address TEXT,
    city VARCHAR(50),
    country VARCHAR(50),
    status ENUM('active', 'inactive') NOT NULL DEFAULT 'active',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (supplier_id),
    INDEX idx_supplier_name (supplier_name),
    INDEX idx_status (status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ----------------------------------------------------------------------------
-- Table: products
-- Purpose: Store product/inventory item information
-- ----------------------------------------------------------------------------
CREATE TABLE products (
    product_id INT(11) NOT NULL AUTO_INCREMENT,
    product_code VARCHAR(50) NOT NULL UNIQUE,
    product_name VARCHAR(200) NOT NULL,
    description TEXT,
    category_id INT(11) NOT NULL,
    supplier_id INT(11),
    unit_price DECIMAL(10, 2) NOT NULL DEFAULT 0.00,
    quantity_in_stock INT(11) NOT NULL DEFAULT 0,
    reorder_level INT(11) NOT NULL DEFAULT 10,
    unit_of_measure VARCHAR(20) DEFAULT 'pcs',
    status ENUM('active', 'inactive', 'discontinued') NOT NULL DEFAULT 'active',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (product_id),
    UNIQUE KEY unique_product_code (product_code),
    INDEX idx_product_name (product_name),
    INDEX idx_category (category_id),
    INDEX idx_supplier (supplier_id),
    INDEX idx_status (status),
    FOREIGN KEY (category_id) REFERENCES categories(category_id) ON DELETE RESTRICT ON UPDATE CASCADE,
    FOREIGN KEY (supplier_id) REFERENCES suppliers(supplier_id) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ----------------------------------------------------------------------------
-- Table: stock_transactions
-- Purpose: Track all stock movements (in/out) with full audit trail
-- ----------------------------------------------------------------------------
CREATE TABLE stock_transactions (
    transaction_id INT(11) NOT NULL AUTO_INCREMENT,
    product_id INT(11) NOT NULL,
    transaction_type ENUM('stock_in', 'stock_out', 'adjustment') NOT NULL,
    quantity INT(11) NOT NULL,
    unit_price DECIMAL(10, 2),
    total_amount DECIMAL(10, 2),
    reference_number VARCHAR(50),
    notes TEXT,
    transaction_date DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    created_by INT(11),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (transaction_id),
    INDEX idx_product (product_id),
    INDEX idx_transaction_type (transaction_type),
    INDEX idx_transaction_date (transaction_date),
    INDEX idx_created_by (created_by),
    FOREIGN KEY (product_id) REFERENCES products(product_id) ON DELETE RESTRICT ON UPDATE CASCADE,
    FOREIGN KEY (created_by) REFERENCES users(user_id) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================================
-- STEP 3: Insert Sample Data
-- ============================================================================

-- ----------------------------------------------------------------------------
-- Insert Default Admin User
-- Password: admin123 (hashed using PHP password_hash with bcrypt)
-- ----------------------------------------------------------------------------
INSERT INTO users (username, password, full_name, email, role, status) VALUES
('admin', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'System Administrator', 'admin@inventory.com', 'admin', 'active'),
('manager', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Store Manager', 'manager@inventory.com', 'manager', 'active'),
('staff', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Staff Member', 'staff@inventory.com', 'staff', 'active');

-- ----------------------------------------------------------------------------
-- Insert Sample Categories
-- ----------------------------------------------------------------------------
INSERT INTO categories (category_name, description, status) VALUES
('Electronics', 'Electronic devices and accessories', 'active'),
('Office Supplies', 'Office stationery and supplies', 'active'),
('Furniture', 'Office and home furniture', 'active'),
('Hardware', 'Hardware tools and equipment', 'active'),
('Software', 'Software licenses and applications', 'active');

-- ----------------------------------------------------------------------------
-- Insert Sample Suppliers
-- ----------------------------------------------------------------------------
INSERT INTO suppliers (supplier_name, contact_person, email, phone, address, city, country, status) VALUES
('Tech Solutions Ltd', 'John Smith', 'john@techsolutions.com', '+1-555-0101', '123 Tech Street', 'New York', 'USA', 'active'),
('Office Depot Inc', 'Sarah Johnson', 'sarah@officedepot.com', '+1-555-0102', '456 Supply Avenue', 'Chicago', 'USA', 'active'),
('Global Hardware Co', 'Mike Wilson', 'mike@globalhardware.com', '+1-555-0103', '789 Hardware Road', 'Los Angeles', 'USA', 'active'),
('Premium Furniture', 'Emily Brown', 'emily@premiumfurniture.com', '+1-555-0104', '321 Furniture Lane', 'Boston', 'USA', 'active');

-- ----------------------------------------------------------------------------
-- Insert Sample Products
-- ----------------------------------------------------------------------------
INSERT INTO products (product_code, product_name, description, category_id, supplier_id, unit_price, quantity_in_stock, reorder_level, unit_of_measure, status) VALUES
('ELEC001', 'Wireless Mouse', 'Ergonomic wireless mouse with USB receiver', 1, 1, 25.99, 150, 20, 'pcs', 'active'),
('ELEC002', 'USB Keyboard', 'Standard USB keyboard with numeric keypad', 1, 1, 35.50, 80, 15, 'pcs', 'active'),
('ELEC003', 'LED Monitor 24"', '24-inch Full HD LED monitor', 1, 1, 199.99, 45, 10, 'pcs', 'active'),
('OFF001', 'A4 Paper Ream', '500 sheets white A4 paper', 2, 2, 5.99, 200, 50, 'ream', 'active'),
('OFF002', 'Ballpoint Pen Blue', 'Blue ink ballpoint pen', 2, 2, 0.50, 500, 100, 'pcs', 'active'),
('OFF003', 'Stapler Standard', 'Standard office stapler', 2, 2, 8.99, 75, 15, 'pcs', 'active'),
('FURN001', 'Office Chair', 'Ergonomic office chair with adjustable height', 3, 4, 149.99, 30, 5, 'pcs', 'active'),
('FURN002', 'Desk Lamp', 'LED desk lamp with adjustable arm', 3, 4, 29.99, 60, 10, 'pcs', 'active'),
('HARD001', 'Screwdriver Set', '10-piece screwdriver set', 4, 3, 19.99, 40, 8, 'set', 'active'),
('HARD002', 'Hammer', 'Claw hammer with rubber grip', 4, 3, 12.50, 35, 10, 'pcs', 'active');

-- ----------------------------------------------------------------------------
-- Insert Sample Stock Transactions
-- ----------------------------------------------------------------------------
INSERT INTO stock_transactions (product_id, transaction_type, quantity, unit_price, total_amount, reference_number, notes, transaction_date, created_by) VALUES
(1, 'stock_in', 100, 25.99, 2599.00, 'PO-2026-001', 'Initial stock purchase', '2026-01-15 10:00:00', 1),
(1, 'stock_in', 50, 25.99, 1299.50, 'PO-2026-002', 'Restock order', '2026-01-20 14:30:00', 1),
(2, 'stock_in', 80, 35.50, 2840.00, 'PO-2026-003', 'Initial stock purchase', '2026-01-15 10:00:00', 1),
(3, 'stock_in', 50, 199.99, 9999.50, 'PO-2026-004', 'Initial stock purchase', '2026-01-16 11:00:00', 1),
(3, 'stock_out', 5, 199.99, 999.95, 'SO-2026-001', 'Sales order', '2026-01-22 09:15:00', 2),
(4, 'stock_in', 200, 5.99, 1198.00, 'PO-2026-005', 'Bulk paper order', '2026-01-17 13:00:00', 1),
(5, 'stock_in', 500, 0.50, 250.00, 'PO-2026-006', 'Pen bulk order', '2026-01-18 15:00:00', 1);

-- ============================================================================
-- STEP 4: Create Views (Optional - for reporting)
-- ============================================================================

-- ----------------------------------------------------------------------------
-- View: Low Stock Products
-- Purpose: Quick view of products below reorder level
-- ----------------------------------------------------------------------------
CREATE VIEW view_low_stock_products AS
SELECT 
    p.product_id,
    p.product_code,
    p.product_name,
    c.category_name,
    p.quantity_in_stock,
    p.reorder_level,
    (p.reorder_level - p.quantity_in_stock) AS shortage_quantity,
    p.unit_price
FROM products p
INNER JOIN categories c ON p.category_id = c.category_id
WHERE p.quantity_in_stock <= p.reorder_level
AND p.status = 'active'
ORDER BY shortage_quantity DESC;

-- ----------------------------------------------------------------------------
-- View: Product Inventory Summary
-- Purpose: Complete product information with category and supplier
-- ----------------------------------------------------------------------------
CREATE VIEW view_product_inventory AS
SELECT 
    p.product_id,
    p.product_code,
    p.product_name,
    p.description,
    c.category_name,
    s.supplier_name,
    p.unit_price,
    p.quantity_in_stock,
    p.reorder_level,
    p.unit_of_measure,
    (p.quantity_in_stock * p.unit_price) AS total_value,
    p.status,
    p.created_at,
    p.updated_at
FROM products p
INNER JOIN categories c ON p.category_id = c.category_id
LEFT JOIN suppliers s ON p.supplier_id = s.supplier_id
ORDER BY p.product_name;

-- ============================================================================
-- STEP 5: Verification Queries
-- ============================================================================

-- Check all tables were created successfully
SHOW TABLES;

-- Verify user data
SELECT user_id, username, full_name, role, status FROM users;

-- Verify categories
SELECT category_id, category_name, status FROM categories;

-- Verify suppliers
SELECT supplier_id, supplier_name, contact_person, status FROM suppliers;

-- Verify products
SELECT product_id, product_code, product_name, quantity_in_stock FROM products;

-- Verify stock transactions
SELECT transaction_id, product_id, transaction_type, quantity, transaction_date FROM stock_transactions;

-- Check low stock products
SELECT * FROM view_low_stock_products;

-- ============================================================================
-- SETUP COMPLETE!
-- ============================================================================
-- Next Steps:
-- 1. Update config/config.php with your database credentials
-- 2. Access the application through your web browser
-- 3. Login with username: admin, password: admin123
-- 4. Change the default password after first login
-- ============================================================================
