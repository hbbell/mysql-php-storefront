<?php
require_once "common.php";

$message = "";
handleLogout("main.php");
$pdo = getPDO();
$selected_category = $_POST["category"] ?? "";

if (isset($_POST["add_to_cart"]) && isset($_SESSION["username"]) && isset($_SESSION["customer_id"])) {
    $product_id = $_POST["product_id"];
    $quantity = $_POST["quantity"];
    $customer_id = $_SESSION["customer_id"];

    $cart_id = getCustomerCartId($pdo, $customer_id);

    $stmt = $pdo->prepare("SELECT quantity FROM cart_item WHERE cart_id = ? AND product_id = ?");
    $stmt->execute([$cart_id, $product_id]);
    $existing = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($existing) {
        $new_quantity = $existing["quantity"] + $quantity;
        $stmt = $pdo->prepare("UPDATE cart_item SET quantity = ? WHERE cart_id = ? AND product_id = ?");
        $stmt->execute([$new_quantity, $cart_id, $product_id]);
    } else {
        $stmt = $pdo->prepare("SELECT price FROM product WHERE product_id = ?");
        $stmt->execute([$product_id]);
        $product_row = $stmt->fetch(PDO::FETCH_ASSOC);

        $stmt = $pdo->prepare("
            INSERT INTO cart_item (cart_id, product_id, quantity, price_display)
            VALUES (?, ?, ?, ?)
        ");
        $stmt->execute([$cart_id, $product_id, $quantity, $product_row["price"]]);
    }

    $message = "Item added to cart!";
}

if ($selected_category != "") {
    $stmt = $pdo->prepare("
        SELECT p.product_id, p.name, p.price, p.image_url
        FROM product p
        JOIN category c ON p.category_id = c.category_id
        WHERE c.name = ?
    ");
    $stmt->execute([$selected_category]);
} else {
    $stmt = $pdo->prepare("SELECT product_id, name, price, image_url FROM product");
    $stmt->execute();
}
$products = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html>
<head>
<style>
body { background-color: #d4f5d0; font-family: Arial; margin: 0; padding-top: 90px; }
label { font-family: 'Segoe UI', Tahoma, sans-serif; font-size: 14px; color: #000000; font-weight: bold; }
button, input[type="submit"] { background-color: #4CAF50; color: white; padding: 5px 12px; margin: 8px; cursor: pointer; font-size: 14px; font-family: 'Segoe UI', Tahoma, sans-serif; display: inline-block; border: none; border-radius: 6px; }
.flash-message { background-color: #dff0d8; color: #2e6b2e; padding: 10px 15px; border: 1px solid #b2d8b2; border-radius: 6px; width: fit-content; margin: 20px auto; font-weight: bold; text-align: center; }
</style>
</head>
<body>
<div style="position: absolute; top: 0; width: 100%; background-color: #ffffff; color: black; padding: 15px; text-align: center; font-size: 25px; font-family: Papyrus, fantasy; font-weight: bold;">
    Welcome to the Online Tech Store
    <div style="position: absolute; top: 15px; right: 20px; display: flex; align-items: center; gap: 10px;">
        <?php if (isset($_SESSION["username"])) { ?>
            <p style="margin: 0; font-size: 14px;">Welcome <?php echo h($_SESSION["username"]); ?></p>
            <form action="cart.php" style="margin: 0;"><input type="submit" value="Cart"></form>
            <form method="post" action="main.php" style="margin: 0;"><input type="submit" name="logout" value="Logout"></form>
            <form action="changepass.php" style="margin: 0;"><input type="submit" value="Change Password"></form>
        <?php } else { ?>
            <form action="login.php" style="margin: 0;"><input type="submit" value="Login"></form>
            <form action="registration.php" style="margin: 1px;"><input type="submit" value="Register"></form>
        <?php } ?>
    </div>
</div>

<?php if ($message != "") { ?>
    <div id="flashMessage" class="flash-message"><?php echo h($message); ?></div>
<?php } ?>

<form method="post" action="main.php" style="margin-left: 20px;">
    <label for="category">Category:</label>
    <select name="category" id="category">
    <option value="">-- Select a category --</option>
    <option value="phones" <?php if ($selected_category == "phones") echo "selected"; ?>>Smart Phones</option>
    <option value="computers" <?php if ($selected_category == "computers") echo "selected"; ?>>Computer or Laptop</option>
    <option value="audio" <?php if ($selected_category == "audio") echo "selected"; ?>>Audio</option>
    <option value="accessories" <?php if ($selected_category == "accessories") echo "selected"; ?>>Accessories</option>
</select>
        
    <input type="submit" name="search" value="Search">
</form>

<?php
foreach ($products as $product) {
    echo '<div style="border:1px solid #ccc; padding:15px; margin:15px; width:220px; height:320px; display:inline-block; vertical-align:top; text-align:center; background-color:white; border-radius:10px;">';
    echo '<div style="height:180px; display:flex; align-items:center; justify-content:center; vertical-align:middle;">';
    echo '<img src="' . h($product['image_url']) . '" style="width:150px; height:150px; object-fit:contain;">';
    echo '</div>';
    echo '<strong>' . h($product["name"]) . '</strong><br>';
    echo '$' . h($product["price"]) . '<br>';

    if (isset($_SESSION["username"])) {
        echo '<form method="post" action="main.php">';
        echo '<input type="hidden" name="product_id" value="' . h($product['product_id']) . '">';
        echo '<input type="hidden" name="category" value="' . h($selected_category) . '">';
        echo '<div style="display:flex; justify-content:center; align-items:center; gap:10px;">';
        echo '<input type="submit" name="add_to_cart" value="Add to Cart">';
        echo '<select name="quantity"><option value="1">1</option><option value="2">2</option><option value="3">3</option><option value="4">4</option><option value="5">5</option></select>';
        echo '</div></form>';
    } else {
        echo '<button onclick="window.location.href=\'login.php\'">Login to Buy</button>';
    }
    echo '</div>';
}
?>

<script>
setTimeout(function() {
    var msg = document.getElementById("flashMessage");
    if (msg) {
        msg.style.transition = "opacity 0.5s";
        msg.style.opacity = "0";
        setTimeout(function() { msg.style.display = "none"; }, 500);
    }
}, 3000);
</script>
</body>
</html>