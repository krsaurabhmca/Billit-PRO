<?php
/**
 * ============================================================================
 * BULK IMPORT PAGE
 * ============================================================================
 * Purpose: Import data from CSV files
 * Author: Billit Pro
 * Date: 2026-01-23
 * ============================================================================
 */

// Include header
require_once '../includes/header.php';

// Check for messages
$success_msg = '';
$error_msg = '';

if (isset($_SESSION['import_success'])) {
    $success_msg = $_SESSION['import_success'];
    unset($_SESSION['import_success']);
}

if (isset($_SESSION['import_error'])) {
    $error_msg = $_SESSION['import_error'];
    unset($_SESSION['import_error']);
}
?>

<!-- Page Header -->
<div class="page-header">
    <div>
        <h2 class="page-title">
            <span class="page-icon">📥</span>
            Bulk Import
        </h2>
        <p class="page-description">Import multiple records at once using CSV files.</p>
    </div>
    <div class="page-actions">
        <a href="../index.php" class="btn btn-secondary">
            <span class="btn-icon">←</span> Back to Dashboard
        </a>
    </div>
</div>

<!-- Messages -->
<?php if ($success_msg): ?>
<div class="alert alert-success">
    <span class="alert-icon">✓</span>
    <span class="alert-message"><?php echo $success_msg; ?></span>
</div>
<?php endif; ?>

<?php if ($error_msg): ?>
<div class="alert alert-error">
    <span class="alert-icon">✕</span>
    <span class="alert-message"><?php echo $error_msg; ?></span>
</div>
<?php endif; ?>

<!-- Import Cards Grid -->
<div class="dashboard-grid">
    
    <!-- Categories Import -->
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">🏷️ Import Categories</h3>
        </div>
        <div class="card-body">
            <p class="text-muted mb-20 text-sm">Upload a CSV file to add multiple categories at once.</p>
            
            <div class="import-steps mb-20">
                <div class="step">
                    <span class="step-number">1</span>
                    <div class="step-content">
                        <strong>Download Template</strong>
                        <a href="../assets/templates/category_template.csv" download class="btn-link text-sm">
                            category_template.csv
                        </a>
                    </div>
                </div>
                <div class="step">
                    <span class="step-number">2</span>
                    <div class="step-content">
                        <strong>Fill Data</strong>
                        <span class="text-sm text-muted">Add your categories to the CSV file.</span>
                    </div>
                </div>
                <div class="step">
                    <span class="step-number">3</span>
                    <div class="step-content">
                        <strong>Upload</strong>
                        <form action="process_import.php" method="POST" enctype="multipart/form-data" class="mt-10">
                            <input type="hidden" name="type" value="categories">
                            <div class="form-group mb-10">
                                <input type="file" name="file" accept=".csv" required class="form-control-file">
                            </div>
                            <button type="submit" class="btn btn-primary btn-sm w-100">
                                <span class="btn-icon">⬆️</span> Import Categories
                            </button>
                        </form>
                    </div>
                </div>
            </div>
            
            <div class="instruction-box">
                <h5 class="instruction-title">Instructions:</h5>
                <ul class="instruction-list">
                    <li>Required fields: <strong>category_name</strong></li>
                    <li>Status values: <strong>active, inactive</strong></li>
                    <li>Existing category names will be skipped.</li>
                </ul>
            </div>
        </div>
    </div>

    <!-- Products Import -->
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">📦 Import Products</h3>
        </div>
        <div class="card-body">
            <p class="text-muted mb-20 text-sm">Upload a CSV file to add multiple products at once.</p>
            
            <div class="import-steps mb-20">
                <div class="step">
                    <span class="step-number">1</span>
                    <div class="step-content">
                        <strong>Download Template</strong>
                        <a href="../assets/templates/product_template.csv" download class="btn-link text-sm">
                            product_template.csv
                        </a>
                    </div>
                </div>
                <div class="step">
                    <span class="step-number">2</span>
                    <div class="step-content">
                        <strong>Fill Data</strong>
                        <span class="text-sm text-muted">Add your products. Ensure category names match existing ones.</span>
                    </div>
                </div>
                <div class="step">
                    <span class="step-number">3</span>
                    <div class="step-content">
                        <strong>Upload</strong>
                        <form action="process_import.php" method="POST" enctype="multipart/form-data" class="mt-10">
                            <input type="hidden" name="type" value="products">
                            <div class="form-group mb-10">
                                <input type="file" name="file" accept=".csv" required class="form-control-file">
                            </div>
                            <button type="submit" class="btn btn-primary btn-sm w-100">
                                <span class="btn-icon">⬆️</span> Import Products
                            </button>
                        </form>
                    </div>
                </div>
            </div>
            
            <div class="instruction-box">
                <h5 class="instruction-title">Instructions:</h5>
                <ul class="instruction-list">
                    <li>Required fields: <strong>product_code, product_name, category_name</strong></li>
                    <li>Product Code must be unique.</li>
                    <li>Category Name must exist in the system (or it will create a new one).</li>
                    <li>Supplier Name must match exactly (optional).</li>
                </ul>
            </div>
        </div>
    </div>

    <!-- Customers Import -->
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">👥 Import Customers</h3>
        </div>
        <div class="card-body">
            <p class="text-muted mb-20 text-sm">Bulk add customer details.</p>
            
            <div class="import-steps mb-20">
                <div class="step">
                    <span class="step-number">1</span>
                    <div class="step-content">
                        <strong>Download Template</strong>
                        <a href="../assets/templates/customer_template.csv" download class="btn-link text-sm">
                            customer_template.csv
                        </a>
                    </div>
                </div>
                <div class="step">
                    <span class="step-number">2</span>
                    <div class="step-content">
                        <strong>Fill Data</strong>
                        <span class="text-sm text-muted">Add customer details.</span>
                    </div>
                </div>
                <div class="step">
                    <span class="step-number">3</span>
                    <div class="step-content">
                        <strong>Upload</strong>
                        <form action="process_import.php" method="POST" enctype="multipart/form-data" class="mt-10">
                            <input type="hidden" name="type" value="customers">
                            <div class="form-group mb-10">
                                <input type="file" name="file" accept=".csv" required class="form-control-file">
                            </div>
                            <button type="submit" class="btn btn-primary btn-sm w-100">
                                <span class="btn-icon">⬆️</span> Import Customers
                            </button>
                        </form>
                    </div>
                </div>
            </div>
            
            <div class="instruction-box">
                <h5 class="instruction-title">Instructions:</h5>
                <ul class="instruction-list">
                    <li>Required fields: <strong>customer_name</strong></li>
                    <li>Customer Type: <strong>B2B</strong> or <strong>B2C</strong></li>
                    <li>Duplicate emails/phones will store as new records unless strict checking is enabled.</li>
                </ul>
            </div>
        </div>
    </div>

    <!-- Suppliers Import -->
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">🏢 Import Suppliers</h3>
        </div>
        <div class="card-body">
            <p class="text-muted mb-20 text-sm">Bulk add supplier details.</p>
            
            <div class="import-steps mb-20">
                <div class="step">
                    <span class="step-number">1</span>
                    <div class="step-content">
                        <strong>Download Template</strong>
                        <a href="../assets/templates/supplier_template.csv" download class="btn-link text-sm">
                            supplier_template.csv
                        </a>
                    </div>
                </div>
                <div class="step">
                    <span class="step-number">2</span>
                    <div class="step-content">
                        <strong>Fill Data</strong>
                        <span class="text-sm text-muted">Add supplier details.</span>
                    </div>
                </div>
                <div class="step">
                    <span class="step-number">3</span>
                    <div class="step-content">
                        <strong>Upload</strong>
                        <form action="process_import.php" method="POST" enctype="multipart/form-data" class="mt-10">
                            <input type="hidden" name="type" value="suppliers">
                            <div class="form-group mb-10">
                                <input type="file" name="file" accept=".csv" required class="form-control-file">
                            </div>
                            <button type="submit" class="btn btn-primary btn-sm w-100">
                                <span class="btn-icon">⬆️</span> Import Suppliers
                            </button>
                        </form>
                    </div>
                </div>
            </div>
            
            <div class="instruction-box">
                <h5 class="instruction-title">Instructions:</h5>
                <ul class="instruction-list">
                    <li>Required fields: <strong>supplier_name</strong></li>
                    <li>Status values: <strong>active, inactive</strong></li>
                </ul>
            </div>
        </div>
    </div>
</div>

<style>
.import-steps {
    display: flex;
    flex-direction: column;
    gap: 15px;
    border-left: 2px solid var(--gray-200);
    padding-left: 20px;
    margin-left: 10px;
}

.step {
    position: relative;
    display: flex;
    gap: 15px;
}

.step-number {
    position: absolute;
    left: -29px;
    top: 0;
    width: 20px;
    height: 20px;
    background: var(--primary-color);
    color: white;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 11px;
    font-weight: bold;
}

.step-content {
    flex: 1;
    display: flex;
    flex-direction: column;
    gap: 2px;
}

.form-control-file {
    font-size: 13px;
    width: 100%;
    padding: 8px;
    background: var(--gray-50);
    border: 1px solid var(--gray-200);
    border-radius: 4px;
}

.instruction-box {
    background: var(--gray-50);
    padding: 15px;
    border-radius: 6px;
    border: 1px dashed var(--gray-300);
}

.instruction-title {
    font-size: 13px;
    font-weight: 600;
    margin-bottom: 8px;
    color: var(--gray-700);
}

.instruction-list {
    margin: 0;
    padding-left: 15px;
    font-size: 12px;
    color: var(--gray-600);
}

.instruction-list li {
    margin-bottom: 4px;
}
</style>

<?php
require_once '../includes/footer.php';
?>
