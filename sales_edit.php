<?php
require_once 'db_connect.php';

$order_id = $_GET['order_id'] ?? 0;

// ดึงข้อมูลคำสั่งซื้อ
$stmt = $pdo->prepare("SELECT * FROM tb_orders WHERE i_OrderID = ?");
$stmt->execute([$order_id]);
$order = $stmt->fetch();

if (!$order) {
  die("ไม่พบคำสั่งซื้อที่เลือก");
}

// ดึงข้อมูลรายการสินค้าในคำสั่งซื้อนี้
$stmt2 = $pdo->prepare("
  SELECT od.i_OrderDetailID, od.i_ProductID, od.i_Quantity, p.c_ProductName, p.i_Price
  FROM tb_orderdetails od
  JOIN tb_products p ON od.i_ProductID = p.i_ProductID
  WHERE od.i_OrderID = ?
");
$stmt2->execute([$order_id]);
$orderDetails = $stmt2->fetchAll();

// ดึงข้อมูล dropdown
$employees = $pdo->query("SELECT i_EmployeeID, c_FirstName, c_LastName FROM tb_employees")->fetchAll();
$customers = $pdo->query("SELECT i_CustomerID, c_CompanyName FROM tb_customers")->fetchAll();
$suppliers = $pdo->query("SELECT i_SupplierID, c_CompanyName FROM tb_suppliers")->fetchAll();
$products = $pdo->query("SELECT i_ProductID, c_ProductName, i_Price FROM tb_products")->fetchAll();
?>

<!DOCTYPE html>
<html lang="th">
<head>
<meta charset="UTF-8">
<title>แก้ไขคำสั่งซื้อ</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
<link rel="stylesheet" href="assets/css/theme.css">
</head>
<body>
<div class="container py-4">
  <div class="card shadow p-4">
    <h3 class="mb-4"><i class="bi bi-pencil-square"></i> แก้ไขคำสั่งซื้อ #<?= $order['i_OrderID'] ?></h3>

    <form method="POST" action="save_edit.php">
      <input type="hidden" name="order_id" value="<?= $order['i_OrderID'] ?>">

      <div class="row mb-3">
        <div class="col-md-4">
          <label>พนักงานขาย</label>
          <select class="form-select" name="employee_id" required>
            <?php foreach($employees as $e): ?>
              <option value="<?= $e['i_EmployeeID'] ?>" <?= $order['i_EmployeeID']==$e['i_EmployeeID']?'selected':'' ?>>
                <?= $e['c_FirstName'].' '.$e['c_LastName'] ?>
              </option>
            <?php endforeach; ?>
          </select>
        </div>
        <div class="col-md-4">
          <label>ลูกค้า</label>
          <select class="form-select" name="customer_id" required>
            <?php foreach($customers as $c): ?>
              <option value="<?= $c['i_CustomerID'] ?>" <?= $order['i_CustomerID']==$c['i_CustomerID']?'selected':'' ?>>
                <?= $c['c_CompanyName'] ?>
              </option>
            <?php endforeach; ?>
          </select>
        </div>
        <div class="col-md-4">
          <label>บริษัทขนส่ง</label>
          <select class="form-select" name="supplier_id" required>
            <?php foreach($suppliers as $s): ?>
              <option value="<?= $s['i_SupplierID'] ?>" <?= $order['i_SupplierID']==$s['i_SupplierID']?'selected':'' ?>>
                <?= $s['c_CompanyName'] ?>
              </option>
            <?php endforeach; ?>
          </select>
        </div>
      </div>

      <hr>
      <h5>รายการสินค้า</h5>
      <div id="productContainer">
        <?php foreach($orderDetails as $index => $d): ?>
        <div class="row mb-3 product-row">
          <div class="col-md-6">
            <select class="form-select" name="products[<?= $index ?>][product_id]" required>
              <?php foreach($products as $p): ?>
                <option value="<?= $p['i_ProductID'] ?>" <?= $d['i_ProductID']==$p['i_ProductID']?'selected':'' ?>>
                  <?= $p['c_ProductName'] ?> (฿<?= number_format($p['i_Price'],2) ?>)
                </option>
              <?php endforeach; ?>
            </select>
          </div>
          <div class="col-md-3">
            <input type="number" class="form-control" name="products[<?= $index ?>][quantity]" 
                   value="<?= $d['i_Quantity'] ?>" min="1" required>
          </div>
          <div class="col-md-3">
            <button type="button" class="btn btn-danger remove-row">ลบ</button>
          </div>
        </div>
        <?php endforeach; ?>
      </div>

      <button type="button" class="btn btn-success mb-3" id="addRow">+ เพิ่มสินค้า</button>
      <br>
      <button type="submit" class="btn btn-primary">บันทึกการแก้ไข</button>
      <a href="sales_report.php" class="btn btn-secondary">ยกเลิก</a>
    </form>
  </div>
</div>

<script>
document.getElementById('addRow').addEventListener('click', () => {
  const container = document.getElementById('productContainer');
  const clone = container.firstElementChild.cloneNode(true);
  clone.querySelectorAll('input,select').forEach(el => el.value = '');
  container.appendChild(clone);
  clone.querySelector('.remove-row').addEventListener('click', ()=>clone.remove());
});
document.querySelectorAll('.remove-row').forEach(btn => {
  btn.addEventListener('click', e => e.target.closest('.product-row').remove());
});
</script>
</body>
</html>
