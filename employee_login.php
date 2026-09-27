<?php
require_once "common.php";

$pdo = getPDO();
$error = "";

if (isset($_POST["login"])) {
    $username = $_POST["username"] ?? "";
    $password = $_POST["password"] ?? "";

    $stmt = $pdo->prepare("SELECT * FROM employee WHERE username = ? AND password = SHA2(?, 256)");
    $stmt->execute([$username, $password]);
    $employee = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($employee) {
        $_SESSION["emp_id"] = $employee["emp_id"];
        $_SESSION["employee_username"] = $employee["username"];
        if ($employee["force_password_reset"]) {
            header("Location: emp_change_password.php");
        } else {
            header("Location: emp_main.php");
        }
        exit;
    } else {
        $error = "Invalid employee login";
    }
}
?>
<!DOCTYPE html>
<html>
<head>
<style>
body { background-color: #d4f5d0; font-family: Arial; display: flex; justify-content: center; align-items: center; height: 100vh; margin: 0; }
button, input[type="submit"] { background-color: #4CAF50; color: white; padding: 5px 12px; margin: 8px; cursor: pointer; font-size: 14px; font-family: 'Segoe UI', Tahoma, sans-serif; display: inline-block; border: none; border-radius: 6px; }
input[type="text"], input[type="password"] { padding: 6px; }
</style>
</head>
<body>
<div style="text-align:center;">
    <h2>Employee Login</h2>
    <?php if ($error != "") { ?><p style="color:red; font-weight:bold;"><?php echo h($error); ?></p><?php } ?>
    <form method="post" action="employee_login.php">
        <label>Username:</label><input type="text" name="username"><br><br>
        <label>Password:</label><input type="password" name="password"><br><br>
        <div style="display:flex; justify-content:center; gap:10px;">
            <input type="submit" name="login" value="Login">
            <button type="button" onclick="window.location.href='main.php'">Cancel</button>
        </div>
    </form>
</div>
</body>
</html>