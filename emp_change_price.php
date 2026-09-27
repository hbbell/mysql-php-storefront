<?php
require_once "common.php";

requireEmployeeLogin();
$pdo = getPDO();
$error = "";
$success = "";
$emp_id = $_SESSION["emp_id"];

$stmt = $pdo->query("SELECT product_id, name, price, stock FROM product ORDER BY name");
$products = $stmt->fetchAll(PDO::FETCH_ASSOC);

if (isset($_POST["back"])) {
    header("Location: emp_main.php");
    exit;
}

/* Changing product price */
if (isset($_POST["change_price"])) {
    $product_id = $_POST["product_id"] ?? "";
    $new_price = $_POST["new_price"] ?? "";

    /* Makes sure products new price is valid */
    if ($product_id == "" || $new_price == "") {
        $error = "Please select a product and enter a new price.";
    } elseif (!is_numeric($new_price) || $new_price < 0) {
        $error = "Price must be a number that is 0 or greater.";
    } else {
        $stmt = $pdo->prepare("SELECT product_id, name, price, stock FROM product WHERE product_id = ?");
        $stmt->execute([$product_id]);
        $product = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$product) {
            $error = "Product not found.";
        } else {
            $old_price = $product["price"];
            $stock = $product["stock"];
            $name = $product["name"];

            if ((float)$old_price == (float)$new_price) {
                $error = "The new price is the same as the current price.";
            } else {
                $stmt = $pdo->prepare("UPDATE product SET price = ? WHERE product_id = ?");
                $stmt->execute([$new_price, $product_id]);

                $stmt = $pdo->prepare("
                    INSERT INTO product_history
                    (product_id, action, who_type, who_id, old_price, new_price, old_stock, new_stock, order_id)
                    VALUES (?, 'update', 'employee', ?, ?, ?, ?, ?, NULL)
                ");
                $stmt->execute([$product_id, $emp_id, $old_price, $new_price, $stock, $stock]);

                $success = $name . " price updated successfully from $" . number_format($old_price, 2) . " to $" . number_format((float)$new_price, 2);
                $stmt = $pdo->query("SELECT product_id, name, price, stock FROM product ORDER BY name");
                $products = $stmt->fetchAll(PDO::FETCH_ASSOC);
            }
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
input[type="number"], select { padding: 6px; margin: 6px; }
.box { background-color: white; padding: 30px; border-radius: 12px; box-shadow: 0 0 10px rgba(0,0,0,0.15); text-align: center; min-width: 420px; }
</style>
</head>
<body>
<div class="box">
    <h2>Change Product Price</h2>
    <?php if ($error != "") { ?><p style="color:red; font-weight:bold;"><?php echo h($error); ?></p><?php } ?>
    <?php if ($success != "") { ?><p style="color:green; font-weight:bold;"><?php echo h($success); ?></p><?php } ?>
    <form method="post" action="">
        <label for="product_id">Select Product:</label><br>
        <select name="product_id" id="product_id">
            <option value="">-- Choose a product --</option>
            <?php foreach ($products as $p) { ?>
                <option value="<?php echo h($p["product_id"]); ?>"><?php echo h($p["name"]) . " (Current Price: $" . number_format($p["price"], 2) . ", Stock: " . h($p["stock"]) . ")"; ?></option>
            <?php } ?>
        </select>
        <br><br>
        <label for="new_price">New Price:</label><br>
        <input type="number" name="new_price" id="new_price" min="0" step="0.01">
        <br><br>
        <input type="submit" name="change_price" value="Update Price">
        <input type="submit" name="back" value="Back">
    </form>
</div>



</body>
</html>