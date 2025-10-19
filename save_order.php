<?php
require_once 'db_connect.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $pdo->beginTransaction();
        
        $order_id = $_POST['order_id'];
        $employee_id = $_POST['employee_id'];
        $customer_id = $_POST['customer_id'];
        $supplier_id = $_POST['supplier_id'];
        $order_date = $_POST['order_date'];
        
        // บันทึกข้อมูลใน tb_orders
        $stmt = $pdo->prepare("
            INSERT INTO tb_orders (i_OrderID, i_EmployeeID, i_CustomerID, i_SupplierID, c_OrderDate) 
            VALUES (?, ?, ?, ?, ?)
        ");
        $stmt->execute([$order_id, $employee_id, $customer_id, $supplier_id, $order_date]);
        
        // บันทึกข้อมูลใน tb_orderdetails
        if (isset($_POST['products']) && is_array($_POST['products'])) {
            $stmt = $pdo->prepare("
                INSERT INTO tb_orderdetails (i_OrderID, i_ProductID, i_Quantity) 
                VALUES (?, ?, ?)
            ");
            
            foreach ($_POST['products'] as $product) {
                if (!empty($product['product_id']) && !empty($product['quantity'])) {
                    $stmt->execute([
                        $order_id,
                        $product['product_id'],
                        $product['quantity']
                    ]);
                }
            }
        }
        
        $pdo->commit();
        
        // Redirect พร้อม success message
        header("Location: sales_report.php?success=1");
        exit();
        
    } catch (Exception $e) {
        $pdo->rollBack();
        die("Error: " . $e->getMessage());
    }
} else {
    header("Location: sales.php");
    exit();
}
?>