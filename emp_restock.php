<?php
require_once "common.php";

requireEmployeeLogin();
$pdo = getPDO();
$error = "";
$success = "";

$stmt = $pdo->query("SELECT product_id, name, stock FROM product ORDER BY name");
$products = $stmt->fetchAll(PDO::FETCH_ASSOC);

if (isset($_POST["back"])) {
    header("Location: emp_main.php");
    exit;
}

if (isset($_POST["restock"])) {
    $product_id = $_POST["product_id"] ?? "";
    $amount = $_POST["amount"] ?? "";

    if ($product_id == "" || $amount == "") {
        $error = "Please select a product and enter an amount.";
    } elseif (!is_numeric($amount) || $amount <= 0) {
        $error = "Restock amount must be a positive number.";
    } else {
        $stmt = $pdo->prepare("SELECT name, stock, price FROM product WHERE product_id = ?");
        $stmt->execute([$product_id]);
        $product = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$product) {
            $error = "Product not found.";
        } else {
            $old_stock = $product["stock"];
            $new_stock = $old_stock + $amount;
            $who_id = $_SESSION["emp_id"];

            $stmt = $pdo->prepare("UPDATE product SET stock = ? WHERE product_id = ?");
            $stmt->execute([$new_stock, $product_id]);

            $stmt = $pdo->prepare("
                INSERT INTO product_history
                (product_id, action, who_type, who_id, old_price, new_price, old_stock, new_stock, order_id)
                VALUES (?, 'update', 'employee', ?, ?, ?, ?, ?, NULL)
            ");
            $stmt->execute([$product_id, $who_id, $product["price"], $product["price"], $old_stock, $new_stock]);

            $success = $product["name"] . " restocked successfully. New stock: " . $new_stock;
            $stmt = $pdo->query("SELECT product_id, name, stock FROM product ORDER BY name");
            $products = $stmt->fetchAll(PDO::FETCH_ASSOC);
        }
    }
}
?>
<!DOCTYPE html>
<html>
<head>
<style>
body { background-color: #d4f5d0; font-family: Arial; display: flex; justify-content: center; align-items: center; height: 100vh; margin: 0; }
button, input[type="submit"] { background-color: #4CAF50; color: white; padding: 5px 12px; margin: 8px; cursor: pointer; font-size: 14px; font-family: 'Segoe UI', Tahoma, sans-serif; display: inline-block; border: none; border-radius: 6px; }
input[type="text"], input[type="password"], input[type="number"], select { padding: 6px; }
.box { background-color: white; padding: 30px; border-radius: 12px; text-align: center; min-width: 380px; box-shadow: 0 0 12px rgba(0,0,0,0.15); }
</style>
</head>
<body>
<div class="box">
    <h2>Employee Restock</h2>
    <?php if ($error != "") { ?><p style="color:red; font-weight:bold;"><?php echo h($error); ?></p><?php } ?>
    <?php if ($success != "") { ?><p style="color:green; font-weight:bold;"><?php echo h($success); ?></p><?php } ?>
    <form method="post" action="">
        <label for="product_id">Select Product:</label><br><br>
        <select name="product_id" id="product_id">
            <option value="">-- Choose a product --</option>
            <?php foreach ($products as $p) { ?>
                <option value="<?php echo h($p["product_id"]); ?>"><?php echo h($p["name"]) . " (Current stock: " . h($p["stock"]) . ")"; ?></option>
            <?php } ?>
        </select>
        <br><br>
        <label for="amount">Amount to Add:</label><br><br>
        <input type="number" name="amount" id="amount" min="1">
        <br><br>
        <input type="submit" name="restock" value="Restock Product">
        <input type="submit" name="back" value="Back">
    </form>
</div>
</body>
</html>