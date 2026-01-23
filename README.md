# 📦 Billit Pro - Advanced Inventory & Billing System

**Billit Pro** is a modern, feature-rich web application designed for comprehensive inventory management and professional invoicing. Built with PHP and MySQL, it offers enterprise-grade features like Batch (Expiry) and Serial (IMEI) tracking, making it perfect for electronics shops, pharmacies, and retail businesses.

![Version](https://img.shields.io/badge/version-6.0-blue.svg) ![License](https://img.shields.io/badge/license-MIT-green.svg) ![PHP](https://img.shields.io/badge/php-8.0%2B-777BB4.svg)

---

## 🚀 Key Features

### 📊 Dashboard & Analytics
- **Live Overview**: Real-time stats for Monthly Sales, Stock Value, and Pending Payments.
- **Stock Alerts**: Instant notifications for low-stock items.
- **Visual Charts**: Interactive graphs for sales trends.

### 📦 Advanced Inventory Management
- **Product Tracking**:
  - **Batch Tracking**: Manage Expiry Dates and Manufacturing info (FIFO logic).
  - **Serial Tracking**: Unique identification (IMEI/Serial No) for electronics.
- **Bulk Import**: Upload hundreds of products, categories, or suppliers via CSV.
- **Categories & Suppliers**: Organize stock efficiently.

### 🧾 Professional Invoicing
- **GST Compliant**: Automatic CGST/SGST/IGST calculation based on State codes.
- **Dynamic Theming**: Custom brand colors and company logo on invoices.
- **Tracking Integration**: Select specific Serials or Batches directly during sale.
- **Draft & Finalize**: Create quotes (Draft) or confirm sales (Finalize) which deducts stock.
- **PDF Export**: Beautiful, printable A4 invoice format.

### 📥 Purchase Management
- **Stock In**: Record purchases with tracking details (Enter new Expiry Dates or Serial Numbers).
- **Supplier Database**: Manage vendor details and history.

### 📈 Reports & Insights
- **Sales Report**: Detailed breakdown of revenue.
- **Stock Report**: Current inventory status and valuation.
- **GST Report**: Tax collected summary for filing.
- **Profit & Loss**: Gross profit estimation.
- **Access Logs**: Security audit trail of user activities.

### ⚙️ Settings & Administration
- **Company Branding**: Upload Logo, set Address, and choose **Theme Color**.
- **User Roles**: Admin, Manager, and Staff permission levels.
- **Email Config**: SMTP settings for system notifications.
- **Backup**: Database management tools.

### 🎨 Modern UI/UX
- **Glassmorphism Design**: Sleek, modern interface.
- **Responsive**: Works perfectly on Desktop and Tablets.
- **Help Center**: Integrated documentation for users.

---

## 🛠️ Installation

1. **Clone the Repository**
   ```bash
   git clone https://github.com/krsaurabhmca/Billit-PRO.git
   cd Billit-PRO
   ```

2. **Database Setup**
   - Create a MySQL database (e.g., `billit_db`).
   - Import the `database/db_schema.sql` file.
   - Import `database/billing_extension.sql` and `database/batch_serial_extension.sql` for full features.

3. **Configuration**
   - Open `config/config.php`.
   - Update database credentials:
     ```php
     define('DB_HOST', 'localhost');
     define('DB_USER', 'root');
     define('DB_PASS', '');
     define('DB_NAME', 'billit_db');
     ```

4. **Run**
   - Host the files on a PHP server (XAMPP/WAMP/Apache).
   - Navigate to `http://localhost/billit` in your browser.

5. **Default Login**
   - **Username**: `admin`
   - **Password**: `admin123`

---

## 📁 Project Structure

```
billit/
├── assets/             # CSS, JS, Images, Uploads
├── config/             # Database connection
├── database/           # SQL Import files
├── includes/           # Header, Footer, Functions
├── invoices/           # Invoice creation & Management
├── products/           # Inventory management
├── reports/            # Analytics modules
├── settings/           # Company & System settings
└── ...
```

## 🤝 Support
For documentation, click the **Help (❓)** icon in the application header.

---
*Developed with ❤️ by Billit Pro Team.*
