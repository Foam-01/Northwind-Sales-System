<?php
require_once 'db_connect.php';

$order_id = isset($_GET['order_id']) ? intval($_GET['order_id']) : 0;

if ($order_id == 0) {
    header("Location: sales_report.php");
    exit();
}

// ดึงข้อมูล Order Header
$stmt = $pdo->prepare("
    SELECT 
        o.i_OrderID,
        o.c_OrderDate,
        CONCAT(e.c_FirstName, ' ', e.c_LastName) as employee_name,
        c.c_CompanyName as customer_name,
        c.c_ContactName as contact_name,
        s.c_CompanyName as supplier_name
    FROM tb_orders o
    INNER JOIN tb_employees e ON o.i_EmployeeID = e.i_EmployeeID
    INNER JOIN tb_customers c ON o.i_CustomerID = c.i_CustomerID
    INNER JOIN tb_suppliers s ON o.i_SupplierID = s.i_SupplierID
    WHERE o.i_OrderID = ?
");
$stmt->execute([$order_id]);
$order_info = $stmt->fetch();

if (!$order_info) {
    header("Location: sales_report.php");
    exit();
}

// ดึงข้อมูล Order Details
$stmt = $pdo->prepare("
    SELECT 
        p.c_ProductName,
        p.i_Price,
        od.i_Quantity,
        (p.i_Price * od.i_Quantity) as total_price
    FROM tb_orderdetails od
    INNER JOIN tb_products p ON od.i_ProductID = p.i_ProductID
    WHERE od.i_OrderID = ?
");
$stmt->execute([$order_id]);
$order_details = $stmt->fetchAll();

// คำนวณยอดรวม
$grand_total_qty = 0;
$grand_total_amount = 0;
foreach ($order_details as $detail) {
    $grand_total_qty += $detail['i_Quantity'];
    $grand_total_amount += $detail['total_price'];
}
?>
<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>รายละเอียดการขาย - Northwind</title>
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
                        <a class="nav-link" href="sales.php">
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
        <!-- Order Information -->
        <div class="info-section">
            <h5><i class="bi bi-receipt-cutoff"></i> ข้อมูลการขาย</h5>
            <div class="row">
                <div class="col-md-6">
                    <div class="info-item">
                        <span class="info-label"><i class="bi bi-hash"></i> เลขที่การขาย:</span>
                        <span class="info-value"><?php echo str_pad($order_info['i_OrderID'], 6, '0', STR_PAD_LEFT); ?></span>
                    </div>
                    <div class="info-item">
                        <span class="info-label"><i class="bi bi-calendar-event"></i> วันที่ขาย:</span>
                        <span class="info-value"><?php echo date('d/m/Y', strtotime($order_info['c_OrderDate'])); ?></span>
                    </div>
                    <div class="info-item">
                        <span class="info-label"><i class="bi bi-person-badge"></i> พนักงานขาย:</span>
                        <span class="info-value"><?php echo htmlspecialchars($order_info['employee_name']); ?></span>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="info-item">
                        <span class="info-label"><i class="bi bi-building"></i> ลูกค้า:</span>
                        <span class="info-value"><?php echo htmlspecialchars($order_info['customer_name']); ?></span>
                    </div>
                    <div class="info-item">
                        <span class="info-label"><i class="bi bi-person"></i> ผู้ติดต่อ:</span>
                        <span class="info-value"><?php echo htmlspecialchars($order_info['contact_name']); ?></span>
                    </div>
                    <div class="info-item">
                        <span class="info-label"><i class="bi bi-truck"></i> บริษัทส่งสินค้า:</span>
                        <span class="info-value"><?php echo htmlspecialchars($order_info['supplier_name']); ?></span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Order Details Table -->
        <div class="card">
            <div class="card-header">
                <h4 class="mb-0"><i class="bi bi-list-check"></i> รายละเอียดสินค้า</h4>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th class="text-center" style="width: 10%;">ลำดับ</th>
                                <th style="width: 40%;">ชื่อสินค้า</th>
                                <th class="text-end" style="width: 20%;">ราคาสินค้า</th>
                                <th class="text-center" style="width: 15%;">จำนวน</th>
                                <th class="text-end" style="width: 15%;">ราคารวม</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (count($order_details) > 0): ?>
                                <?php $no = 1; foreach ($order_details as $detail): ?>
                                <tr>
                                    <td class="text-center"><?php echo $no++; ?></td>
                                    <td>
                                        <i class="bi bi-box-seam text-primary"></i>
                                        <?php echo htmlspecialchars($detail['c_ProductName']); ?>
                                    </td>
                                    <td class="text-end">
                                        ฿<?php echo number_format($detail['i_Price'], 2); ?>
                                    </td>
                                    <td class="text-center">
                                        <span class="badge bg-info">
                                            <?php echo number_format($detail['i_Quantity']); ?> ชิ้น
                                        </span>
                                    </td>
                                    <td class="text-end fw-bold text-success">
                                        ฿<?php echo number_format($detail['total_price'], 2); ?>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="5" class="text-center py-4">
                                        <i class="bi bi-inbox" style="font-size: 3rem; opacity: 0.3;"></i>
                                        <p class="text-muted mt-2">ไม่พบข้อมูลรายการสินค้า</p>
                                    </td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                        <?php if (count($order_details) > 0): ?>
                        <tfoot>
                            <tr>
                                <td colspan="3" class="text-end"><strong>รวมทั้งหมด:</strong></td>
                                <td class="text-center">
                                    <strong><?php echo number_format($grand_total_qty); ?> ชิ้น</strong>
                                </td>
                                <td class="text-end">
                                    <strong>฿<?php echo number_format($grand_total_amount, 2); ?></strong>
                                </td>
                            </tr>
                        </tfoot>
                        <?php endif; ?>
                    </table>
                </div>
            </div>
        </div>

        <!-- Summary Boxes -->
        <div class="row mt-4">
            <div class="col-md-6">
                <div class="summary-box">
                    <i class="bi bi-basket3" style="font-size: 2rem; opacity: 0.8;"></i>
                    <h4><?php echo number_format($grand_total_qty); ?> ชิ้น</h4>
                    <p>จำนวนสินค้าทั้งหมด</p>
                </div>
            </div>
            <div class="col-md-6">
                <div class="summary-box">
                    <i class="bi bi-currency-dollar" style="font-size: 2rem; opacity: 0.8;"></i>
                    <h4>฿<?php echo number_format($grand_total_amount, 2); ?></h4>
                    <p>ยอดขายรวมทั้งหมด</p>
                </div>
            </div>
        </div>

        <!-- Back Button -->
        <div class="text-center mt-4 mb-4">
            <a href="sales_report.php" class="btn btn-primary btn-lg">
                <i class="bi bi-arrow-left-circle"></i> กลับไปหน้าตรวจสอบยอดขาย
            </a>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>