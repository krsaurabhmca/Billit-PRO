# 🧾 Billing & GST System - Quick Setup Guide

## ✅ What's Been Created

### 1. Database Extension
**File**: `database/billing_extension.sql`

**Tables Created**:
- `company_settings` - Company GST details
- `customers` - Customer management (B2B/B2C)
- `invoices` - Invoice headers with GST
- `invoice_items` - Invoice line items
- `payments` - Payment tracking

**To Import**:
```sql
-- In phpMyAdmin, run:
database/billing_extension.sql
```

### 2. GST Functions Added
**File**: `includes/functions.php`

**New Functions**:
- `calculate_gst()` - Auto CGST+SGST or IGST
- `generate_invoice_number()` - Auto invoice numbering
- `get_indian_states()` - State dropdown
- `validate_gstin()` - GSTIN validation
- `number_to_words()` - Amount in words

### 3. Configuration Updated
**File**: `config/config.php`

Added GST constants for company state and invoice settings.

---

## 📋 Remaining Files to Create

Due to the extensive nature of the billing system, here are the key files that need to be created:

### Customer Management (Priority 1)
1. `customers/customers.php` - Customer listing & management
2. `customers/add_customer.php` - Add customer form
3. `customers/edit_customer.php` - Edit customer
4. `customers/delete_customer.php` - Delete customer

### Invoice Management (Priority 1)
1. `invoices/invoices.php` - Invoice listing
2. `invoices/create_invoice.php` - Create invoice with GST
3. `invoices/view_invoice.php` - View/Print invoice
4. `invoices/edit_invoice.php` - Edit draft invoice
5. `invoices/delete_invoice.php` - Cancel invoice

### Payment & Reports (Priority 2)
1. `invoices/add_payment.php` - Record payment
2. `invoices/payment_history.php` - Payment history
3. `reports/gst_report.php` - GST summary
4. `reports/sales_report.php` - Sales report

### Settings (Priority 3)
1. `settings/company_settings.php` - Company GST setup

---

## 🚀 Quick Implementation Steps

### Step 1: Import Database
```bash
1. Open phpMyAdmin
2. Select 'inventory_management' database
3. Click 'Import' tab
4. Choose file: database/billing_extension.sql
5. Click 'Go'
```

### Step 2: Update Navigation
Add to `includes/header.php` navigation menu:
```php
<li class="nav-item">
    <a href="<?php echo BASE_URL; ?>customers/customers.php" class="nav-link">
        <span class="nav-icon">👥</span>
        Customers
    </a>
</li>

<li class="nav-item">
    <a href="<?php echo BASE_URL; ?>invoices/invoices.php" class="nav-link">
        <span class="nav-icon">🧾</span>
        Invoices
    </a>
</li>

<li class="nav-item dropdown">
    <a href="#" class="nav-link">
        <span class="nav-icon">📊</span>
        Reports
        <span class="dropdown-arrow">▼</span>
    </a>
    <ul class="dropdown-menu">
        <li><a href="<?php echo BASE_URL; ?>reports/gst_report.php">GST Report</a></li>
        <li><a href="<?php echo BASE_URL; ?>reports/sales_report.php">Sales Report</a></li>
    </ul>
</li>
```

### Step 3: Create Customer Management
The customer management module will allow you to:
- Add B2B customers with GSTIN
- Add B2C customers without GSTIN
- Track customer state for GST calculation
- Manage billing addresses

### Step 4: Create Invoice System
The invoice system will:
- Auto-generate invoice numbers (INV0001, INV0002...)
- Calculate CGST+SGST for same state
- Calculate IGST for different state
- Support discounts
- Track payments
- Generate PDF invoices

---

## 💡 GST Calculation Logic

### Intra-State (Same State)
```
Customer State: Maharashtra (27)
Company State: Maharashtra (27)
Result: CGST 9% + SGST 9% = 18%
```

### Inter-State (Different State)
```
Customer State: Karnataka (29)
Company State: Maharashtra (27)
Result: IGST 18%
```

### Example Calculation
```
Product: Wireless Mouse
Unit Price: ₹1,000
Quantity: 10
Subtotal: ₹10,000

Discount (10%): ₹1,000
Taxable Amount: ₹9,000

GST @ 18%:
- CGST @ 9%: ₹810
- SGST @ 9%: ₹810
Total Tax: ₹1,620

Total Amount: ₹10,620
```

---

## 📊 Sample Data Included

### Customers
1. **ABC Enterprises** (B2B, Maharashtra) - GSTIN: 27BBBBB1111B1Z5
2. **XYZ Trading** (B2B, Karnataka) - GSTIN: 29CCCCC2222C1Z5
3. **Walk-in Customer** (B2C)
4. **Retail Customer** (B2C, Mumbai)

### Products Updated
All products now have:
- HSN codes (e.g., 8471 for electronics)
- GST rates (12% or 18%)

---

## 🔧 Next Steps

### Option 1: Complete Implementation
I can create all remaining files for the billing system:
- Customer CRUD pages
- Invoice creation with real-time GST calculation
- Payment tracking
- PDF invoice generation
- GST reports

### Option 2: Phased Approach
1. **Phase 1**: Customer management + Basic invoice creation
2. **Phase 2**: Payment tracking + Invoice viewing
3. **Phase 3**: Reports + PDF generation

### Option 3: Custom Requirements
Let me know if you need:
- Specific invoice format
- Additional GST features
- Custom reports
- Integration with existing modules

---

## 📝 Important Notes

1. **Company Settings**: Update company details in `company_settings` table
2. **State Codes**: Use 2-digit state codes (27=Maharashtra, 29=Karnataka, etc.)
3. **GSTIN Format**: 15 characters (e.g., 27AAAAA0000A1Z5)
4. **Invoice Numbering**: Starts from INV0001, auto-increments
5. **Stock Integration**: Invoices will auto-deduct stock

---

## 🎯 Ready to Continue?

The foundation is complete! The GST calculation engine is ready and tested.

**What would you like me to create next?**
1. Customer management pages
2. Invoice creation system
3. Complete all billing files at once
4. Something specific

Just let me know and I'll continue building!
