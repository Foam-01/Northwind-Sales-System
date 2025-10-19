<?php
require_once 'db_connect.php';

// ดึงข้อมูล OrderID ล่าสุด
$stmt = $pdo->query("SELECT MAX(i_OrderID) as max_id FROM tb_orders");
$result = $stmt->fetch();
$next_order_id = ($result['max_id'] ?? 0) + 1;

// ดึงข้อมูลพนักงาน
$employees = $pdo->query("SELECT i_EmployeeID, c_FirstName, c_LastName FROM tb_employees ORDER BY c_FirstName")->fetchAll();

// ดึงข้อมูลลูกค้า
$customers = $pdo->query("SELECT i_CustomerID, c_CompanyName, c_ContactName FROM tb_customers ORDER BY c_CompanyName")->fetchAll();

// ดึงข้อมูล Suppliers
$suppliers = $pdo->query("SELECT i_SupplierID, c_CompanyName FROM tb_suppliers ORDER BY c_CompanyName")->fetchAll();

// ดึงข้อมูลสินค้า
$products = $pdo->query("SELECT i_ProductID, c_ProductName, i_Price FROM tb_products ORDER BY c_ProductName")->fetchAll();
?>
<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ระบบขายสินค้า - Northwind</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
    <link rel="stylesheet" href="assets/css/theme.css">
</head>
<body>
    <!-- Navigation Bar -->
    <nav class="navbar navbar-expand-lg navbar-light">
        <div class="container-fluid">
            <a class="navbar-brand fw-bold" href="#">
                <i class="bi bi-cart-check-fill"></i> Northwind Sales System
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item">
                        <a class="nav-link active" href="sales.php">
                            <i class="bi bi-bag-plus"></i> การขายสินค้า
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="sales_report.php">
                            <i class="bi bi-graph-up"></i> ตรวจสอบยอดขาย
                        </a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <div class="container main-container">
        <div class="card">
            <div class="card-header">
                <h4 class="mb-0"><i class="bi bi-cart-plus"></i> บันทึกการขายสินค้า</h4>
            </div>
            <div class="card-body p-4">
                <div class="info-badge">
                    <div class="row">
                        <div class="col-md-6">
                            <h5><i class="bi bi-receipt"></i> เลขที่การขาย: <strong><?php echo str_pad($next_order_id, 6, '0', STR_PAD_LEFT); ?></strong></h5>
                        </div>
                        <div class="col-md-6 text-end">
                            <h5><i class="bi bi-calendar-event"></i> วันที่: <strong><?php echo date('d/m/Y'); ?></strong></h5>
                        </div>
                    </div>
                </div>

                <form id="salesForm" method="POST" action="save_order.php">
                    <input type="hidden" name="order_id" value="<?php echo $next_order_id; ?>">
                    <input type="hidden" name="order_date" value="<?php echo date('Y-m-d'); ?>">

                    <div class="row mb-4">
                        <div class="col-md-4">
                            <label class="form-label required">พนักงานขาย</label>
                            <select class="form-select" name="employee_id" required>
                                <option value="">-- เลือกพนักงาน --</option>
                                <?php foreach($employees as $emp): ?>
                                    <option value="<?php echo $emp['i_EmployeeID']; ?>">
                                        <?php echo $emp['c_FirstName'] . ' ' . $emp['c_LastName']; ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label required">ลูกค้า</label>
                            <select class="form-select" name="customer_id" required>
                                <option value="">-- เลือกลูกค้า --</option>
                                <?php foreach($customers as $cust): ?>
                                    <option value="<?php echo $cust['i_CustomerID']; ?>">
                                        <?php echo $cust['c_CompanyName']; ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label required">บริษัทส่งสินค้า</label>
                            <select class="form-select" name="supplier_id" required>
                                <option value="">-- เลือกบริษัท --</option>
                                <?php foreach($suppliers as $sup): ?>
                                    <option value="<?php echo $sup['i_SupplierID']; ?>">
                                        <?php echo $sup['c_CompanyName']; ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>

                    <hr class="my-4">
                    
                    <h5 class="mb-3"><i class="bi bi-basket"></i> รายการสินค้า</h5>
                    
                    <div id="productContainer">
                        <div class="product-row" data-row="1">
                            <div class="row align-items-center">
                                <div class="col-md-1">
                                    <strong>#1</strong>
                                </div>
                                <div class="col-md-7">
                                    <label class="form-label">สินค้า</label>
                                    <select class="form-select product-select" name="products[1][product_id]" data-row="1">
                                        <option value="">-- เลือกสินค้า --</option>
                                        <?php foreach($products as $prod): ?>
                                            <option value="<?php echo $prod['i_ProductID']; ?>" 
                                                    data-price="<?php echo $prod['i_Price']; ?>">
                                                <?php echo $prod['c_ProductName']; ?> 
                                                (฿<?php echo number_format($prod['i_Price'], 2); ?>)
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label">จำนวน</label>
                                    <input type="number" class="form-control" name="products[1][quantity]" 
                                           min="1" placeholder="0">
                                </div>
                                <div class="col-md-1">
                                    <label class="form-label d-block">&nbsp;</label>
                                    <button type="button" class="btn btn-danger btn-sm remove-row" 
                                            style="display:none;">
                                        <i class="bi bi-trash"></i>
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>

                    <button type="button" class="btn btn-success mb-4" id="addProductBtn">
                        <i class="bi bi-plus-circle"></i> เพิ่มสินค้า
                    </button>

                    <div class="text-center mt-4">
                        <button type="button" class="btn btn-primary btn-lg" id="submitBtn">
                            <i class="bi bi-check-circle"></i> บันทึกการขาย
                        </button>
                        <a href="sales_report.php" class="btn btn-secondary btn-lg">
                            <i class="bi bi-x-circle"></i> ยกเลิก
                        </a>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Confirmation Modal -->
    <div class="modal fade" id="confirmModal" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title"><i class="bi bi-question-circle"></i> ยืนยันการบันทึก</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <p class="mb-0">คุณต้องการบันทึกรายการขายนี้ใช่หรือไม่?</p>
                    <div id="orderSummary" class="mt-3"></div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">ยกเลิก</button>
                    <button type="button" class="btn btn-primary" id="confirmSubmit">ยืนยัน</button>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        let productCount = 1;
        const maxProducts = 10;

        document.getElementById('addProductBtn').addEventListener('click', function() {
            if (productCount >= maxProducts) {
                alert('สามารถเพิ่มสินค้าได้สูงสุด 10 รายการ');
                return;
            }
            
            productCount++;
            const container = document.getElementById('productContainer');
            const newRow = document.querySelector('.product-row').cloneNode(true);
            
            newRow.setAttribute('data-row', productCount);
            newRow.querySelector('strong').textContent = '#' + productCount;
            newRow.querySelector('.product-select').setAttribute('data-row', productCount);
            newRow.querySelector('.product-select').name = `products[${productCount}][product_id]`;
            newRow.querySelector('.product-select').value = '';
            newRow.querySelector('input[type="number"]').name = `products[${productCount}][quantity]`;
            newRow.querySelector('input[type="number"]').value = '';
            newRow.querySelector('.remove-row').style.display = 'inline-block';
            
            container.appendChild(newRow);
            
            newRow.querySelector('.remove-row').addEventListener('click', function() {
                newRow.remove();
                productCount--;
            });
        });

        document.getElementById('submitBtn').addEventListener('click', function() {
            const form = document.getElementById('salesForm');
            
            if (!form.checkValidity()) {
                form.reportValidity();
                return;
            }

            let hasProduct = false;
            document.querySelectorAll('.product-select').forEach(select => {
                if (select.value !== '') hasProduct = true;
            });

            if (!hasProduct) {
                alert('กรุณาเลือกสินค้าอย่างน้อย 1 รายการ');
                return;
            }

            const modal = new bootstrap.Modal(document.getElementById('confirmModal'));
            modal.show();
        });

        document.getElementById('confirmSubmit').addEventListener('click', function() {
            document.getElementById('salesForm').submit();
        });
    </script>
</body>
</html>