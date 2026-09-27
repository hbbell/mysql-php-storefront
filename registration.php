<?php
require_once "common.php";

$pdo = getPDO();
$error = "";

if (isset($_POST["cancel"])) {
    header("Location: login.php");
    exit;
}

if (isset($_POST["create"])) {
    $username = trim($_POST["username"]);
    $password = trim($_POST["password"]);
    $verify_password = trim($_POST["verify_password"]);
    $fname = trim($_POST["fname"]);
    $lname = trim($_POST["lname"]);
    $email = trim($_POST["email"]);
    $address = trim($_POST["address"]);

    if ($username == "" || $password == "" || $verify_password == "" || $fname == "" || $lname == "" || $email == "") {
        $error = "Please fill in all required fields.";
    } elseif ($password != $verify_password) {
        $error = "Passwords do not match.";
    } else {
        $stmt = $pdo->prepare("SELECT customer_id FROM customer WHERE username = ? OR email = ?");
        $stmt->execute([$username, $email]);

        if ($stmt->fetch()) {
            $error = "Username or email already exists.";
        } else {
            $stmt = $pdo->prepare("INSERT INTO customer (username, password, first_name, last_name, email, shipping_address) VALUES (?, SHA2(?, 256), ?, ?, ?, ?)");
            $stmt->execute([$username, $password, $fname, $lname, $email, $address]);
            header("Location: login.php");
            exit;
        }
    }
}
?>
<!DOCTYPE html>
<html>
<head>
<style>
body { background-color: #d4f5d0; font-family: Arial; display: flex; justify-content: center; align-items: center; height: 100vh; margin: 0; }
.box { background: white; padding: 30px; border-radius: 12px; box-shadow: 0 0 10px rgba(0,0,0,0.15); }
</style>
</head>
<body>
<div class="box">
<form method="post" action="">
  <h2>Register Account</h2>
  <?php if ($error != "") { ?><p style="color:red; font-weight:bold;"><?php echo h($error); ?></p><?php } ?>
  <label for="username">Username: </label><input type="text" id="username" name="username"><br><br>
  <label for="password">Password: </label><input type="password" id="password" name="password"><br><br>
  <label for="verify_password">Verify Password: </label><input type="password" id="verify_password" name="verify_password"><br><br>
  <label for="fname">First Name: </label><input type="text" id="fname" name="fname"><br><br>
  <label for="lname">Last Name: </label><input type="text" id="lname" name="lname"><br><br>
  <label for="email">Email: </label><input type="email" id="email" name="email"><br><br>
  <label for="address">Address: </label><input type="text" id="address" name="address"><br><br>
  <div style="text-align:center;"><input type="submit" name="create" value="Create Account"><input type="submit" name="cancel" value="Cancel"></div>
</form>
</div>
</body>
</html>