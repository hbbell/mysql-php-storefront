<?php
require_once "common.php";

requireCustomerLogin();
handleLogout("login.php");

$pdo = getPDO();
$message = "";
$messageColor = "red";
$customer_id = $_SESSION["customer_id"];

if (isset($_POST["changepass"])) {
    $oldPassword = $_POST["oldpassword"] ?? "";
    $newPassword = $_POST["newpassword"] ?? "";

    $stmt = $pdo->prepare("SELECT * FROM customer WHERE customer_id = ? AND password = SHA2(?, 256)");
    $stmt->execute([$customer_id, $oldPassword]);
    $valid = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($valid) {
        $stmt = $pdo->prepare("UPDATE customer SET password = SHA2(?, 256) WHERE customer_id = ?");
        $stmt->execute([$newPassword, $customer_id]);
        $message = "Password updated successfully";
        $messageColor = "green";
    } else {
        $message = "Incorrect old password";
    }
}
?>
<!DOCTYPE html>
<html>
<head>
<style>
body { background-color: #d4f5d0; font-family: Arial; display: flex; justify-content: center; align-items: center; height: 100vh; margin: 0; }
label { font-family: 'Segoe UI', Tahoma, sans-serif; font-size: 14px; color: #000000; font-weight: bold; }
button, input[type="submit"] { background-color: #4CAF50; color: white; padding: 5px 12px; margin: 8px; cursor: pointer; font-size: 14px; font-family: 'Segoe UI', Tahoma, sans-serif; display: inline-block; border: none; border-radius: 6px; }
</style>
</head>
<body>
<div style="position: absolute; top: 0; width: 100%; background-color: #ffffff; color: black; padding: 15px; text-align: center; font-size: 25px; font-family: Papyrus, fantasy; font-weight: bold;">
    Welcome to the Online Tech Store
    <div style="position: absolute; top: 15px; right: 20px; display: flex; align-items: center; gap: 10px;">
        <p style="margin: 0; font-size: 14px;">Welcome <?php echo h($_SESSION["username"]); ?></p>
        <form action="cart.php" style="margin: 0;"><input type="submit" value="Cart"></form>
        <form method="post" action="changepass.php" style="margin: 0;"><input type="submit" name="logout" value="Logout"></form>
        <form action="main.php" style="margin: 0;"><input type="submit" value="Main Menu"></form>
    </div>
</div>

<?php if ($message != "") { ?>
<p style="position: fixed; top: 70px; left: 50%; transform: translateX(-50%); color: <?php echo h($messageColor); ?>; font-weight: bold; background: white; padding: 10px 20px; border: 1px solid <?php echo h($messageColor); ?>; border-radius: 8px; z-index: 9999;"><?php echo h($message); ?></p>
<?php } ?>

<form method="post" action="changepass.php">
    <label for="oldpassword">Old Password:</label>
    <input type="password" name="oldpassword" id="oldpassword"><br><br>
    <label for="newpassword">New Password:</label>
    <input type="password" name="newpassword" id="newpassword"><br><br>
    <input type="submit" name="changepass" value="Change Password">
</form>
</body>
</html>