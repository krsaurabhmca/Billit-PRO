# 📦 Inventory Management System

A comprehensive inventory management application built with **core PHP** and **mysqli procedural functions**, featuring modern UI design, complete CRUD operations, and robust stock management capabilities.

## ✨ Features

### 🔐 Authentication & User Management
- Secure login/logout system with password hashing (bcrypt)
- Role-based access control (Admin, Manager, Staff)
- User registration (Admin only)
- Session management with timeout

### 📊 Dashboard
- Real-time inventory statistics
- Low stock alerts
- Recent transaction overview
- Category-wise product distribution
- Total inventory value calculation

### 📦 Product Management
- Complete CRUD operations (Create, Read, Update, Delete)
- Product search and filtering
- Category and supplier assignment
- Stock level tracking with reorder alerts
- Multiple unit of measure support

### 🏷️ Category Management
- Add, edit, and delete categories
- Product count per category
- Inline editing functionality

### 🏢 Supplier Management
- Comprehensive supplier information
- Contact details management
- Product association tracking

### 📥📤 Stock Management
- **Stock In**: Add inventory with purchase details
- **Stock Out**: Remove inventory with sales tracking
- Real-time stock availability validation
- Transaction history with filtering
- Reference number tracking (PO/SO/Invoice)
- Automatic stock quantity updates

### 📈 Reporting
- Stock transaction history
- Filter by product, type, and date range
- Summary statistics (total value, transactions)

## 🛠️ Technology Stack

- **Backend**: PHP 7.4+ (Procedural)
- **Database**: MySQL/MariaDB with mysqli_* functions
- **Frontend**: HTML5, CSS3, JavaScript (Vanilla)
- **Design**: Custom responsive CSS with modern gradients
- **Server**: XAMPP/WAMP/LAMP

## 📋 Requirements

- PHP 7.4 or higher
- MySQL 5.7 or higher / MariaDB 10.3 or higher
- Apache Web Server
- XAMPP/WAMP (for local development)

## 🚀 Installation Guide

### Step 1: Clone/Download Files
```bash
# Place all files in your XAMPP htdocs directory
C:\xampp\htdocs\billit\
```

### Step 2: Database Setup

1. **Start XAMPP** and ensure MySQL service is running

2. **Open phpMyAdmin** (http://localhost/phpmyadmin)

3. **Import Database**:
   - Click on "Import" tab
   - Choose file: `database/database_setup.sql`
   - Click "Go" to execute
   
   **OR** run the SQL script manually:
   - Open the SQL tab in phpMyAdmin
   - Copy and paste the contents of `database/database_setup.sql`
   - Click "Go"

4. **Verify Database Creation**:
   - Database name: `inventory_management`
   - Tables: users, categories, suppliers, products, stock_transactions
   - Sample data should be populated

### Step 3: Configure Database Connection

Open `config/config.php` and verify/update these settings:

```php
define('DB_HOST', 'localhost');        // Database host
define('DB_USER', 'root');             // Database username
define('DB_PASS', '');                 // Database password (empty for XAMPP)
define('DB_NAME', 'inventory_management'); // Database name
```

### Step 4: Access the Application

1. Open your web browser
2. Navigate to: `http://localhost/billit/`
3. You will be redirected to the login page

### Step 5: Login

Use these default credentials:

**Admin Account:**
- Username: `admin`
- Password: `admin123`

**Manager Account:**
- Username: `manager`
- Password: `admin123`

**Staff Account:**
- Username: `staff`
- Password: `admin123`

> ⚠️ **Important**: Change default passwords after first login!

## 📁 Project Structure

```
billit/
├── assets/
│   └── css/
│       └── style.css              # Main stylesheet
├── categories/
│   ├── categories.php             # Category management
│   └── delete_category.php        # Delete category
├── config/
│   └── config.php                 # Database configuration
├── database/
│   └── database_setup.sql         # Database schema & sample data
├── includes/
│   ├── functions.php              # Common utility functions
│   ├── header.php                 # Common header
│   └── footer.php                 # Common footer
├── products/
│   ├── products.php               # Product listing
│   ├── add_product.php            # Add product
│   ├── edit_product.php           # Edit product
│   └── delete_product.php         # Delete product
├── stock/
│   ├── stock_in.php               # Add stock
│   ├── stock_out.php              # Remove stock
│   └── stock_history.php          # Transaction history
├── suppliers/
│   ├── suppliers.php              # Supplier management
│   └── delete_supplier.php        # Delete supplier
├── index.php                      # Dashboard
├── login.php                      # Login page
├── logout.php                     # Logout handler
├── register.php                   # User registration (Admin)
└── README.md                      # This file
```

## 🎯 Usage Guide

### Adding Products

1. Navigate to **Products** → **Add New Product**
2. Fill in product details:
   - Product Code (unique identifier)
   - Product Name
   - Description
   - Category (required)
   - Supplier (optional)
   - Unit Price
   - Initial Stock Quantity
   - Reorder Level
   - Unit of Measure
   - Status
3. Click **Add Product**

### Managing Stock

**Stock In (Adding Inventory):**
1. Go to **Stock** → **Stock In**
2. Select product from dropdown
3. Enter quantity to add
4. Enter unit price (auto-filled from product)
5. Add reference number (PO/Invoice)
6. Add notes if needed
7. Click **Add Stock**

**Stock Out (Removing Inventory):**
1. Go to **Stock** → **Stock Out**
2. Select product from dropdown
3. Enter quantity to remove (validates against available stock)
4. Enter unit price
5. Add reference number (SO/Invoice)
6. Add notes if needed
7. Click **Remove Stock**

### Viewing Reports

1. Navigate to **Stock** → **Transaction History**
2. Use filters to narrow down results:
   - Filter by product
   - Filter by transaction type (In/Out)
   - Filter by date range
3. View summary statistics at the top
4. Export or print reports as needed

## 🔒 Security Features

- **Password Hashing**: All passwords stored using PHP's `password_hash()` with bcrypt
- **SQL Injection Prevention**: All inputs sanitized using `mysqli_real_escape_string()`
- **XSS Protection**: All outputs escaped using `htmlspecialchars()`
- **Session Security**: Secure session management with timeout
- **Role-Based Access**: Different permissions for Admin, Manager, and Staff
- **Input Validation**: Server-side validation for all forms

## 📝 Database Schema

### Tables Overview

1. **users**: User authentication and profiles
2. **categories**: Product categories
3. **suppliers**: Supplier information
4. **products**: Product inventory details
5. **stock_transactions**: Stock movement history

### Key Relationships

- Products → Categories (Many-to-One)
- Products → Suppliers (Many-to-One, optional)
- Stock Transactions → Products (Many-to-One)
- Stock Transactions → Users (Many-to-One)

## 🎨 Design Features

- **Modern UI**: Clean, professional interface with gradient accents
- **Responsive Design**: Mobile-friendly layout
- **Color-Coded Badges**: Visual status indicators
- **Interactive Elements**: Hover effects and smooth transitions
- **Alert System**: Success/error message notifications
- **Statistics Cards**: Visual dashboard metrics

## 🐛 Troubleshooting

### Database Connection Error

**Problem**: "Database Connection Failed" error

**Solutions**:
1. Verify XAMPP MySQL service is running
2. Check database credentials in `config/config.php`
3. Ensure database `inventory_management` exists
4. Run `database_setup.sql` to create database

### Login Not Working

**Problem**: Cannot login with default credentials

**Solutions**:
1. Verify database has sample user data
2. Check if `users` table has records
3. Re-run the database setup script
4. Clear browser cache and cookies

### Stock Not Updating

**Problem**: Stock quantity not changing after Stock In/Out

**Solutions**:
1. Check for JavaScript errors in browser console
2. Verify database transaction support (InnoDB engine)
3. Check PHP error logs for mysqli errors

## 📚 Code Documentation

All PHP files include comprehensive inline comments following these standards:

- **File Headers**: Purpose, author, date
- **Function Documentation**: Parameters, return values, descriptions
- **Section Comments**: Clear separation of code blocks
- **Inline Comments**: Explanation of complex logic

## 🔄 Future Enhancements

Potential features for future versions:

- [ ] PDF report generation
- [ ] Excel export functionality
- [ ] Barcode scanning support
- [ ] Email notifications for low stock
- [ ] Multi-location inventory
- [ ] Purchase order management
- [ ] Sales analytics dashboard
- [ ] API for mobile app integration

## 👨‍💻 Development Notes

### Coding Standards

- **Procedural PHP**: No OOP, pure procedural approach
- **mysqli Functions**: All database operations use mysqli_* functions
- **Naming Conventions**: Snake_case for variables, PascalCase for files
- **Indentation**: 4 spaces
- **Comments**: Extensive documentation throughout

### Best Practices Implemented

✅ Separation of concerns (config, includes, modules)  
✅ DRY principle (reusable functions)  
✅ Input validation and sanitization  
✅ Error handling and logging  
✅ Database transactions for critical operations  
✅ Consistent code formatting  
✅ Security-first approach  

## 📄 License

This project is open-source and available for educational and commercial use.

## 🤝 Support

For issues, questions, or contributions:

1. Check the troubleshooting section
2. Review code comments for implementation details
3. Verify database setup is correct
4. Check PHP and MySQL error logs

## 📞 Contact

For additional support or customization requests, please refer to the code documentation and inline comments throughout the application.

---

**Version**: 1.0.0  
**Last Updated**: January 23, 2026  
**Built with**: ❤️ and PHP

---

## 🎉 Quick Start Summary

1. ✅ Import `database/database_setup.sql` into MySQL
2. ✅ Configure `config/config.php` with database credentials
3. ✅ Access `http://localhost/billit/`
4. ✅ Login with username: `admin`, password: `admin123`
5. ✅ Start managing your inventory!

**Happy Inventory Management! 📦**
