<?php
require_once "common.php";

requireEmployeeLogin();
$pdo = getPDO();

if (isset($_POST["change"])) {
    $newPassword = $_POST["newpassword"] ?? "";
    $stmt = $pdo->prepare("UPDATE employee SET password = SHA2(?, 256), force_password_reset = false WHERE emp_id = ?");
    $stmt->execute([$newPassword, $_SESSION["emp_id"]]);
    header("Location: emp_main.php");
    exit;
}
?>
<!DOCTYPE html>
<html>
<head>
<style>
body { background-color: #d4f5d0; display: flex; justify-content: center; align-items: center; height: 100vh; margin: 0; font-family: Arial; }
button, input[type="submit"] { background-color: #4CAF50; color: white; padding: 10px 10px; margin: 10px auto; border: none; border-radius: 8px; cursor: pointer; font-size: 16px; font-family: 'Segoe UI', Tahoma, sans-serif; display: block; width: 200px; }
.container { display: flex; flex-direction: column; align-items: center; text-align: center; }
</style>
</head>
<body>
<div class="container">
    <h2> Employee Password Reset</h2>
    <form method="post" action="emp_change_password.php">
        <label>New Password:</label><br>
        <input type="password" name="newpassword"><br><br>
        <input type="submit" name="change" value="Change Password">
    </form>
</div>
</body>
</html>