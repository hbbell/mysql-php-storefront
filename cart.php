<?php
require_once "common.php";

requireCustomerLogin();
handleLogout("login.php");

$pdo = getPDO();
$message = "";
$messageColor = "red";
$customer_id = $_SESSION["customer_id"];
$cart_id = getCustomerCartId($pdo, $customer_id);

if (isset($_POST["update_cart"])) {
    $cart_item_id = $_POST["cart_item_id"];
    $quantity = $_POST["quantity"];

    if ($quantity > 0) {
        $stmt = $pdo->prepare("UPDATE cart_item SET quantity = ? WHERE cart_item_id = ?");
        $stmt->execute([$quantity, $cart_item_id]);
    } else {
        $stmt = $pdo->prepare("DELETE FROM cart_item WHERE cart_item_id = ?");
        $stmt->execute([$cart_item_id]);
    }
}

if (isset($_POST["remove"]) && isset($_POST["product_id"])) {
    $stmt = $pdo->prepare("DELETE FROM cart_item WHERE cart_id = ? AND product_id = ?");
    $stmt->execute([$cart_id, $_POST["product_id"]]);
}


    // checkout
if (isset($_POST["checkout"])) {
    $stmt = $pdo->prepare("
        SELECT 
            ci.product_id,
            ci.quantity AS cart_qty,
            ci.price_display,
            p.name,
            p.stock AS stock_qty,
            p.price
        FROM cart_item ci
        JOIN product p ON ci.product_id = p.product_id
        WHERE ci.cart_id = ?
    ");
    $stmt->execute([$cart_id]);
    $checkout_items = $stmt->fetchAll(PDO::FETCH_ASSOC);

    if (empty($checkout_items)) {
        $message = "Your cart is empty.";
    } else {
        $errors = [];

        foreach ($checkout_items as $item) {
            if ($item["cart_qty"] > $item["stock_qty"]) {
                $errors[] = $item["name"];
            }
        }

        if (!empty($errors)) {
            $message = "Not enough stock for: " . implode(", ", $errors) . ". Please update or remove these items.";
        } else {
            try {
                $pdo->beginTransaction();

                $total = 0;
                foreach ($checkout_items as $item) {
                    $total += $item["price_display"] * $item["cart_qty"];
                }

                // create order
                $stmt = $pdo->prepare("
                    INSERT INTO orders (customer_id, order_date, total_amount)
                    VALUES (?, NOW(), ?)
                ");
                $stmt->execute([$customer_id, $total]);

                $order_id = $pdo->lastInsertId();

                // insert order items, update stock, insert history
                foreach ($checkout_items as $item) {
                    $old_stock = $item["stock_qty"];
                    $new_stock = $old_stock - $item["cart_qty"];
                    $price = $item["price"];

                    if ($new_stock < 0) {
                        throw new Exception("Insufficient stock for " . $item["name"]);
                    }

                    // insert order item
                    $stmt = $pdo->prepare("
                        INSERT INTO order_item (order_id, product_id, quantity, price_at_order)
                        VALUES (?, ?, ?, ?)
                    ");
                    $stmt->execute([
                        $order_id,
                        $item["product_id"],
                        $item["cart_qty"],
                        $item["price_display"]
                    ]);

                    // update stock
                    $stmt = $pdo->prepare("
                        UPDATE product
                        SET stock = stock - ?
                        WHERE product_id = ?
                    ");
                    $stmt->execute([
                        $item["cart_qty"],
                        $item["product_id"]
                    ]);

                    // insert stock history for customer purchase
                    $stmt = $pdo->prepare("
                        INSERT INTO product_history
                        (product_id, action, who_type, who_id, old_price, new_price, old_stock, new_stock, order_id)
                        VALUES (?, 'update', 'customer', ?, ?, ?, ?, ?, ?)
                    ");
                    $stmt->execute([
                        $item["product_id"],
                        $customer_id,
                        $price,
                        $price,
                        $old_stock,
                        $new_stock,
                        $order_id
                    ]);
                }

                // clear cart
                $stmt = $pdo->prepare("DELETE FROM cart_item WHERE cart_id = ?");
                $stmt->execute([$cart_id]);

                $pdo->commit();

                $message = "Order placed successfully! Your order number is " . $order_id;
                $messageColor = "green";
            } catch (Exception $e) {
                $pdo->rollBack();
                $message = "Checkout failed. No changes were saved.";
                $messageColor = "red";
            }
        }
    }
}


$stmt = $pdo->prepare("
    SELECT ci.cart_item_id, ci.product_id, p.name, ci.price_display, p.image_url, ci.quantity
    FROM cart_item ci
    JOIN product p ON ci.product_id = p.product_id
    WHERE ci.cart_id = ?
");
$stmt->execute([$cart_id]);
$items = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html>
<head>
<style>
body { background-color: #d4f5d0; font-family: Arial; margin: 0; padding-top: 90px; }
label { font-family: 'Segoe UI', Tahoma, sans-serif; font-size: 14px; color: #000000; font-weight: bold; }
button, input[type="submit"] { background-color: #4CAF50; color: white; padding: 5px 10px; margin: 8px; cursor: pointer; font-size: 14px; font-family: 'Segoe UI', Tahoma, sans-serif; border: none; border-radius: 6px; }
input[type="number"] { padding: 5px; border-radius: 6px; border: 1px solid #ccc; }
.product-box { border: 1px solid #ccc; background-color: white; padding: 15px; margin: 15px; width: 220px; text-align: center; display: inline-block; vertical-align: top; border-radius: 10px; }
.product-box img { width: 150px; height: 150px; object-fit: contain; }
</style>
</head>
<body>
<div style="position: absolute; top: 0; width: 100%; background-color: #ffffff; color: black; padding: 15px; text-align: center; font-size: 25px; font-family: Papyrus, fantasy; font-weight: bold;">
    Your Cart
    <div style="position: absolute; top: 15px; right: 20px; display: flex; align-items: center; gap: 10px;">
        <p style="margin: 0; font-size: 14px;">Welcome <?php echo h($_SESSION["username"]); ?></p>
        <form action="main.php" style="margin: 0;"><input type="submit" value="Main Menu"></form>
        <form method="post" action="cart.php" style="margin: 0;"><input type="submit" name="logout" value="Logout"></form>
    </div>
</div>

<div style="margin-left: 20px;"><h2>Shopping Cart</h2></div>

<?php if (!empty($message)) { ?>
    <p style="margin-left:20px; font-weight:bold; color: <?php echo h($messageColor); ?>;"><?php echo h($message); ?></p>
<?php } ?>

<?php
if (empty($items)) {
    echo '<p style="margin-left: 20px;">Your cart is empty.</p>';
} else {
    $total = 0;
    foreach ($items as $item) {
        echo '<div class="product-box">';
        echo '<img src="'.h($item["image_url"]).'" alt="'.h($item["name"]).'"><br><br>';
        echo '<strong>'.h($item["name"]).'</strong><br>';
        echo 'Price: $'.h($item["price_display"]).'<br><br>';
        $total += $item["price_display"] * $item["quantity"];

        echo '<form method="post" action="cart.php">';
        echo '<input type="hidden" name="cart_item_id" value="'.h($item["cart_item_id"]).'">';
        echo '<div style="display:flex; justify-content:center; align-items:center; gap:10px; margin-top:10px;">';
        echo '<label for="quantity_'.h($item["cart_item_id"]).'">Qty:</label>';
        echo '<input type="number" id="quantity_'.h($item["cart_item_id"]).'" name="quantity" value="'.h($item["quantity"]).'" min="1" style="width:60px; text-align:center;">';
        echo '<input type="submit" name="update_cart" value="Update">';
        echo '</div></form>';

        echo '<form method="post" action="cart.php">';
        echo '<input type="hidden" name="product_id" value="'.h($item["product_id"]).'">';
        echo '<input type="submit" name="remove" value="Remove">';
        echo '</form></div>';
    }
    echo '<h2 style="margin-left:20px;">Total: $'.h($total).'</h2>';
}
?>

<div style="position: absolute; bottom: 0; height: 35px; width: 100%; background-color: #ffffff; color: black; padding: 15px; text-align: center;"></div>

<div style="position: fixed; bottom: 15px; right: 20px; display: flex; gap: 10px;">
    <form method="post" action="cart.php" style="margin: 0;"><input type="submit" name="checkout" value="Checkout"></form>
    <form action="orders.php" style="margin: 0;"><input type="submit" value="Order History"></form>
</div>
</body>
</html>