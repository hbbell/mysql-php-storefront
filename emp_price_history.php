<?php
require_once "common.php";

requireEmployeeLogin();
$pdo = getPDO();
$error = "";
$history = [];
$selected_product = "";

$stmt = $pdo->query("SELECT product_id, name FROM product ORDER BY name");
$products = $stmt->fetchAll(PDO::FETCH_ASSOC);

if (isset($_POST["back"])) {
    header("Location: emp_main.php");
    exit;
}

if (isset($_POST["show_history"])) {
    $selected_product = $_POST["product_id"] ?? "";
    if ($selected_product == "") {
        $error = "Please select a product.";
    } else {
        $stmt = $pdo->prepare("
            SELECT ph.history_id, ph.change_time, ph.action, ph.who_type, ph.who_id, ph.old_price, ph.new_price, p.name
            FROM product_history ph
            JOIN product p ON ph.product_id = p.product_id
            WHERE ph.product_id = ?
              AND ph.old_price IS NOT NULL
              AND ph.new_price IS NOT NULL
              AND ph.old_price <> ph.new_price
            ORDER BY ph.change_time DESC
        ");
        $stmt->execute([$selected_product]);
        $history = $stmt->fetchAll(PDO::FETCH_ASSOC);

        if (!$history) {
            $error = "No price history found for that product.";
        }
    }
}
?>
<!DOCTYPE html>
<html>
<head>
<style>
body { background-color: #d4f5d0; font-family: Arial; margin: 0; padding: 30px; }
button, input[type="submit"] { background-color: #4CAF50; color: white; padding: 5px 12px; margin: 8px; cursor: pointer; font-size: 14px; font-family: 'Segoe UI', Tahoma, sans-serif; display: inline-block; border: none; border-radius: 6px; }
select { padding: 6px; margin: 6px; }
.box { background-color: white; padding: 30px; border-radius: 12px; box-shadow: 0 0 10px rgba(0,0,0,0.15); max-width: 950px; margin: 0 auto; }
table { width: 100%; border-collapse: collapse; margin-top: 20px; }
th, td { border: 1px solid #999; padding: 10px; text-align: center; }
th { background-color: #4CAF50; color: white; }
h2 { text-align: center; }
</style>
</head>
<body>
<div class="box">
    <h2>Price History</h2>
    <?php if ($error != "") { ?><p style="color:red; font-weight:bold; text-align:center;"><?php echo h($error); ?></p><?php } ?>
    <form method="post" action="" style="text-align:center;">
        <label for="product_id">Select Product:</label><br>
        <select name="product_id" id="product_id">
            <option value="">-- Choose a product --</option>
            <?php foreach ($products as $p) { ?>
                <option value="<?php echo h($p["product_id"]); ?>" <?php if ($selected_product == $p["product_id"]) echo "selected"; ?>><?php echo h($p["name"]); ?></option>
            <?php } ?>
        </select>
        <br><br>
        <input type="submit" name="show_history" value="Show Price History">
        <input type="submit" name="back" value="Back">
    </form>

    <?php if (!empty($history)) { ?>
        <table>
            <tr><th>History ID</th><th>Product</th><th>Change Time</th><th>Action</th><th>Who Type</th><th>Who ID</th><th>Old Price</th><th>New Price</th><th>Difference</th><th>Percent Change</th></tr>
            <?php foreach ($history as $row) { ?>
                <tr>
                    <td><?php echo h($row["history_id"]); ?></td>
                    <td><?php echo h($row["name"]); ?></td>
                    <td><?php echo h($row["change_time"]); ?></td>
                    <td><?php echo h($row["action"]); ?></td>
                    <td><?php echo h($row["who_type"]); ?></td>
                    <td><?php echo h($row["who_id"]); ?></td>
                    <td>$<?php echo number_format($row["old_price"], 2); ?></td>
                    <td>$<?php echo number_format($row["new_price"], 2); ?></td>
                    <td>$<?php echo number_format($row["new_price"] - $row["old_price"], 2); ?></td>
                    <td><?php if ($row["old_price"] != 0) { $percent = (($row["new_price"] - $row["old_price"]) / $row["old_price"]) * 100; echo h(number_format($percent, 2) . "%"); } else { echo "N/A"; } ?></td>
                </tr>
            <?php } ?>
        </table>
    <?php } ?>
</div>



</body>
</html>