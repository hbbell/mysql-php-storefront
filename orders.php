<?php
require_once "common.php";

requireCustomerLogin();
$pdo = getPDO();
$customer_id = $_SESSION["customer_id"];
$selected_order_id = $_GET["order_id"] ?? null;

$stmt = $pdo->prepare("SELECT order_id, order_date, total_amount FROM orders WHERE customer_id = ? ORDER BY order_date DESC");
$stmt->execute([$customer_id]);
$orders = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html>
<head>
<style>
body { background-color: #d4f5d0; font-family: Arial; margin: 0; padding-top: 90px; }
button, input[type="submit"] { background-color: #4CAF50; color: white; padding: 5px 10px; margin: 8px; cursor: pointer; font-size: 14px; font-family: 'Segoe UI', Tahoma, sans-serif; border: none; border-radius: 6px; }
.order-box { border: 1px solid #ccc; background-color: white; padding: 15px; margin: 20px; border-radius: 10px; }
.order-items { margin-top: 10px; padding-left: 20px; }
.order-items table { border-collapse: collapse; width: 100%; margin-top: 10px; }
.order-items th, .order-items td { border: 1px solid #ccc; padding: 8px; text-align: center; background-color: white; }
.order-items th { background-color: #f2f2f2; }
</style>
</head>
<body>
<div style="position: absolute; top: 0; width: 100%; background-color: #ffffff; color: black; padding: 15px; text-align: center; font-size: 25px; font-family: Papyrus, fantasy; font-weight: bold;">
    Your Orders
    <div style="position: absolute; top: 15px; right: 20px; display: flex; align-items: center; gap: 10px;">
        <p style="margin: 0; font-size: 14px;">Welcome <?php echo h($_SESSION["username"]); ?></p>
        <form action="main.php" style="margin: 0;"><input type="submit" value="Main Menu"></form>
        <form action="cart.php" style="margin: 0;"><input type="submit" value="Cart"></form>
    </div>
</div>

<div style="margin-left: 20px;"><h2>Order History</h2></div>

<?php
if (empty($orders)) {
    echo '<p style="margin-left:20px;">No orders found.</p>';
} else {
    foreach ($orders as $order) {
        echo '<div class="order-box">';
        echo '<strong>Order Number:</strong> ' . h($order["order_id"]) . '<br>';
        echo '<strong>Order Time:</strong> ' . h($order["order_date"]) . '<br>';
        echo '<strong>Total:</strong> $' . h($order["total_amount"]) . '<br>';
        echo '<form method="get" action="orders.php" style="margin-top:10px;">';
        echo '<input type="hidden" name="order_id" value="'.h($order["order_id"]).'">';
        echo '<input type="submit" value="View Items">';
        echo '</form>';

        if ($selected_order_id == $order["order_id"]) {
            $stmt = $pdo->prepare("SELECT oi.product_id, p.name AS product_name, oi.price_at_order, oi.quantity FROM order_item oi JOIN product p ON oi.product_id = p.product_id WHERE oi.order_id = ?");
            $stmt->execute([$order["order_id"]]);
            $items = $stmt->fetchAll(PDO::FETCH_ASSOC);

            echo '<div class="order-items"><table><tr><th>Product ID</th><th>Product Name</th><th>Price</th><th>Quantity</th></tr>';
            foreach ($items as $item) {
                echo '<tr><td>' . h($item["product_id"]) . '</td><td>' . h($item["product_name"]) . '</td><td>$' . h($item["price_at_order"]) . '</td><td>' . h($item["quantity"]) . '</td></tr>';
            }
            echo '</table></div>';
        }
        echo '</div>';
    }
}
?>
</body>
</html>