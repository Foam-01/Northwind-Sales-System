<?php
require_once 'db_connect.php';

// ดึงข้อมูลยอดขาย
$query = "
    SELECT 
        o.i_OrderID,
        o.c_OrderDate,
        CONCAT(e.c_FirstName, ' ', e.c_LastName) as employee_name,
        c.c_CompanyName as customer_name,
        SUM(od.i_Quantity) as total_quantity,
        SUM(od.i_Quantity * p.i_Price) as total_amount
    FROM tb_orders o
    INNER JOIN tb_employees e ON o.i_EmployeeID = e.i_EmployeeID
    INNER JOIN tb_customers c ON o.i_CustomerID = c.i_CustomerID
    INNER JOIN tb_orderdetails od ON o.i_OrderID = od.i_OrderID
    INNER JOIN tb_products p ON od.i_ProductID = p.i_ProductID
    GROUP BY o.i_OrderID, o.c_OrderDate, employee_name, customer_name
    ORDER BY o.i_OrderID DESC
";

$orders = $pdo->query($query)->fetchAll();

// เตรียมข้อมูลสำหรับ Dashboard
$daily_sales = [];
$customer_sales = [];
$monthly_sales = [];

foreach ($orders as $order) {
    // Daily sales
    $date = date('Y-m-d', strtotime($order['c_OrderDate']));
    if (!isset($daily_sales[$date])) {
        $daily_sales[$date] = 0;
    }
    $daily_sales[$date] += $order['total_amount'];
    
    // Customer sales
    if (!isset($customer_sales[$order['customer_name']])) {
        $customer_sales[$order['customer_name']] = 0;
    }
    $customer_sales[$order['customer_name']] += $order['total_amount'];
    
    // Monthly sales
    $month = date('Y-m', strtotime($order['c_OrderDate']));
    if (!isset($monthly_sales[$month])) {
        $monthly_sales[$month] = 0;
    }
    $monthly_sales[$month] += $order['total_amount'];
}

// Sort and prepare data for charts
ksort($daily_sales);
arsort($customer_sales);

// Ensure we have entries for the last N months (fill missing months with 0)
$months_to_show = 6; // change to 12 if you want 12 months
for ($i = $months_to_show - 1; $i >= 0; $i--) {
    $m = date('Y-m', strtotime("-{$i} month"));
    if (!isset($monthly_sales[$m])) {
        $monthly_sales[$m] = 0;
    }
}

ksort($monthly_sales);

// Limit to top 5 customers for pie chart
$customer_sales = array_slice($customer_sales, 0, 5);

// คำนวณยอดรวม
$grand_total_qty = 0;
$grand_total_amount = 0;
foreach ($orders as $order) {
    $grand_total_qty += $order['total_quantity'];
    $grand_total_amount += $order['total_amount'];
}
?>
<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ตรวจสอบยอดขาย - Northwind</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
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
                        <a class="nav-link active" href="sales_report.php">
                            <i class="bi bi-graph-up"></i> ตรวจสอบยอดขาย
                        </a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <div class="container main-container">
        <?php if (isset($_GET['success'])): ?>
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="bi bi-check-circle-fill"></i> <strong>สำเร็จ!</strong> บันทึกรายการขายเรียบร้อยแล้ว
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
        <?php endif; ?>

        <!-- Dashboard Charts -->
        <div class="row mb-4">
            <div class="col-md-8 mb-4">
                <div class="card h-100">
                    <div class="card-header">
                        <h5 class="mb-0"><i class="bi bi-bar-chart"></i> ยอดขายรายวัน</h5>
                    </div>
                    <div class="card-body">
                        <canvas id="dailySalesChart"></canvas>
                    </div>
                </div>
            </div>
            <div class="col-md-4 mb-4">
                <div class="card h-100">
                    <div class="card-header">
                        <h5 class="mb-0"><i class="bi bi-pie-chart"></i> สัดส่วนยอดขายตามลูกค้า</h5>
                    </div>
                    <div class="card-body">
                        <canvas id="customerSalesChart"></canvas>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="row mb-4">
            <div class="col-12">
                <div class="card">
                    <div class="card-header">
                        <h5 class="mb-0"><i class="bi bi-graph-up"></i> แนวโน้มยอดขายรายเดือน</h5>
                    </div>
                    <div class="card-body">
                        <canvas id="monthlySalesChart"></canvas>
                    </div>
                </div>
            </div>
        </div>

        <script>
        // Daily Sales Bar Chart
        new Chart(document.getElementById('dailySalesChart'), {
            type: 'bar',
            data: {
                labels: <?php echo json_encode(array_map(function($date) {
                    return date('d/m/Y', strtotime($date));
                }, array_keys($daily_sales))); ?>,
                datasets: [{
                    label: 'ยอดขายรายวัน (บาท)',
                    data: <?php echo json_encode(array_values($daily_sales)); ?>,
                    backgroundColor: '#3498db',
                    borderWidth: 1
                }]
            },
            options: {
                responsive: true,
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            callback: function(value) {
                                return '฿' + value.toLocaleString();
                            }
                        }
                    }
                }
            }
        });

        // Customer Sales Pie Chart
        new Chart(document.getElementById('customerSalesChart'), {
            type: 'pie',
            data: {
                labels: <?php echo json_encode(array_keys($customer_sales)); ?>,
                datasets: [{
                    data: <?php echo json_encode(array_values($customer_sales)); ?>,
                    backgroundColor: ['#3498db', '#2ecc71', '#f1c40f', '#e74c3c', '#9b59b6']
                }]
            },
            options: {
                responsive: true,
                plugins: {
                    legend: {
                        position: 'bottom'
                    }
                }
            }
        });

        // Monthly Sales Line Chart (safely handle empty data)
        (function(){
            var monthlyLabels = <?php echo json_encode(array_map(function($month) {
                return date('m/Y', strtotime($month . '-01'));
            }, array_keys($monthly_sales))); ?>;
            var monthlyData = <?php echo json_encode(array_values($monthly_sales)); ?>;

            var canvas = document.getElementById('monthlySalesChart');
            if (!monthlyLabels || monthlyLabels.length === 0) {
                // replace canvas parent with friendly message
                var parent = canvas.parentElement;
                parent.innerHTML = '<div class="text-center py-4 text-muted">ไม่พบข้อมูลยอดขายรายเดือน</div>';
                return;
            }

            new Chart(canvas, {
                type: 'line',
                data: {
                    labels: monthlyLabels,
                    datasets: [{
                        label: 'ยอดขายรายเดือน (บาท)',
                        data: monthlyData,
                        borderColor: '#2ecc71',
                        tension: 0.3,
                        fill: false
                    }]
                },
                options: {
                    responsive: true,
                    scales: {
                        y: {
                            beginAtZero: true,
                            ticks: {
                                callback: function(value) {
                                    return '฿' + value.toLocaleString();
                                }
                            }
                        }
                    }
                }
            });
        })();
        </script>

        <div class="row mb-4">
            <div class="col-md-6">
                <div class="stats-card">
                    <i class="bi bi-basket3" style="font-size: 2rem; opacity: 0.8;"></i>
                    <h3><?php echo number_format($grand_total_qty); ?></h3>
                    <p>จำนวนสินค้าทั้งหมด</p>
                </div>
            </div>
            <div class="col-md-6">
                <div class="stats-card">
                    <i class="bi bi-currency-dollar" style="font-size: 2rem; opacity: 0.8;"></i>
                    <h3>฿<?php echo number_format($grand_total_amount, 2); ?></h3>
                    <p>ยอดขายรวมทั้งหมด</p>
                </div>
            </div>
        </div>

        <div class="card">
            <div class="card-header">
                <div class="row align-items-center">
                    <div class="col">
                        <h4 class="mb-0"><i class="bi bi-clipboard-data"></i> สรุปรายการขายสินค้า</h4>
                    </div>
                    <div class="col-auto">
                        <a href="sales.php" class="btn btn-light">
                            <i class="bi bi-plus-circle"></i> เพิ่มรายการขาย
                        </a>
                    </div>
                </div>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
    <table class="table table-hover align-middle text-center">
        <thead>
            <tr>
                <th>ลำดับ</th>
                <th>เลขที่การขาย</th>
                <th>วันที่ขาย</th>
                <th>พนักงานขาย</th>
                <th>ลูกค้า</th>
                <th>จำนวนสินค้า</th>
                <th>ราคารวม (บาท)</th>
                <th>การจัดการ</th>
            </tr>
        </thead>
        <tbody>
            <?php if (!empty($orders)): ?>
                <?php $no = 1; ?>
                <?php foreach ($orders as $order): ?>
                    <tr>
                        <td><?php echo $no++; ?></td>
                        <td>
                            <span class="badge bg-primary">
                                <?php echo str_pad($order['i_OrderID'], 6, '0', STR_PAD_LEFT); ?>
                            </span>
                        </td>
                        <td><?php echo date('d/m/Y', strtotime($order['c_OrderDate'])); ?></td>
                        <td><?php echo htmlspecialchars($order['employee_name']); ?></td>
                        <td><?php echo htmlspecialchars($order['customer_name']); ?></td>
                        <td><span class="badge bg-info"><?php echo number_format($order['total_quantity']); ?> ชิ้น</span></td>
                        <td class="text-success fw-bold">
                            ฿<?php echo number_format($order['total_amount'], 2); ?>
                        </td>
                        <td>
                            <a href="sales_detail.php?order_id=<?php echo $order['i_OrderID']; ?>" 
                               class="btn btn-info btn-sm">
                                <i class="bi bi-eye"></i> ดู
                            </a>
                            <a href="sales_edit.php?order_id=<?php echo $order['i_OrderID']; ?>" 
                               class="btn btn-warning btn-sm text-white">
                                <i class="bi bi-pencil-square"></i> แก้ไข
                            </a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            <?php else: ?>
                <tr>
                    <td colspan="8" class="py-5 text-muted">
                        <i class="bi bi-inbox" style="font-size:2.5rem;opacity:0.3;"></i><br>
                        ไม่พบข้อมูลการขาย
                    </td>
                </tr>
            <?php endif; ?>
        </tbody>

        <?php if (!empty($orders)): ?>
        <tfoot>
            <tr style="background:linear-gradient(135deg,#f39c12,#e67e22);color:white;">
                <td colspan="5" class="text-end fw-bold">รวมทั้งหมด:</td>
                <td class="fw-bold"><?php echo number_format($grand_total_qty); ?> ชิ้น</td>
                <td class="fw-bold">฿<?php echo number_format($grand_total_amount, 2); ?></td>
                <td></td>
            </tr>
        </tfoot>
        <?php endif; ?>
    </table>
</div>

  
</td>