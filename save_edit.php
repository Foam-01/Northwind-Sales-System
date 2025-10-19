<?php
require_once 'db_connect.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  try {
    $pdo->beginTransaction();

    $order_id = $_POST['order_id'];
    $employee_id = $_POST['employee_id'];
    $customer_id = $_POST['customer_id'];
    $supplier_id = $_POST['supplier_id'];

    // อัปเดตข้อมูลหลักใน tb_orders
    $stmt = $pdo->prepare("
      UPDATE tb_orders 
      SET i_EmployeeID=?, i_CustomerID=?, i_SupplierID=? 
      WHERE i_OrderID=?
    ");
    $stmt->execute([$employee_id, $customer_id, $supplier_id, $order_id]);

    // ลบรายละเอียดเก่า
    $pdo->prepare("DELETE FROM tb_orderdetails WHERE i_OrderID=?")->execute([$order_id]);

    // เพิ่มรายละเอียดใหม่
    $stmt2 = $pdo->prepare("
      INSERT INTO tb_orderdetails (i_OrderID, i_ProductID, i_Quantity)
      VALUES (?, ?, ?)
    ");
    foreach ($_POST['products'] as $p) {
      if (!empty($p['product_id']) && !empty($p['quantity'])) {
        $stmt2->execute([$order_id, $p['product_id'], $p['quantity']]);
      }
    }

    $pdo->commit();
    header("Location: sales_report.php?edit_success=1");
  } catch (Exception $e) {
    $pdo->rollBack();
    die("เกิดข้อผิดพลาด: " . $e->getMessage());
  }
}
?>
