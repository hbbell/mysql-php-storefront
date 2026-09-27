<?php
require_once "common.php";
requireEmployeeLogin();
handleLogout("employee_login.php");
?>
<!DOCTYPE html>
<html>
<head>
<style>
body {
    background-color: #d4f5d0;
    font-family: Arial;
    display: flex;
    justify-content: center;
    align-items: center;
    height: 100vh;
    margin: 0;
}

/*Changing size of buttons (big)*/
button, input[type="submit"] {
    background-color: #4CAF50;
    color: white;
    padding: 10px 10px;
    margin: 10px auto;
    border: none;
    border-radius: 8px;
    cursor: pointer;
    font-size: 16px;
    font-family: 'Segoe UI', Tahoma, sans-serif;
    display: block;
    width: 200px;
}

/*Changing size of buttons (small)*/
.small-btn {
    padding: 5px 10px;
    font-size: 12px;
    font-family: 'Segoe UI', Tahoma, sans-serif; display: inline-block;
    width: auto !important;        /* ⭐ override big width */
    display: inline-block !important;  /* ⭐ override block */
    margin: 0 !important;          /* remove extra spacing */
}
</style>
</head>

<body>

<!-- Header block with title, logout, and change password -->
<div style="
    position: absolute;
    top: 0;
    width: 100%;
    background-color: #ffffff;
    color: black;
    padding: 15px;
    text-align: center;
    font-size: 25px;
    font-family: Papyrus, fantasy;
    font-weight: bold;
">
    Welcome to the Online Tech Store

    <div style="
        position: absolute;
        top: 10px;
        right: 20px;
        display: flex;
        align-items: center;
        gap: 10px;
        font-size: 16px;
    ">
        <p style="margin: 0;">
            Welcome <?php echo h($_SESSION["employee_username"]); ?>
        </p>

        <form method="post" action="emp_main.php" style="margin: 0;">
            <input type="submit" name="logout" value="Logout" class="small-btn">
        </form>

        <form action="emp_change_password.php" style="margin: 0;">
            <input type="submit" value="Change Password" class="small-btn">
        </form>
    </div>
</div>

<!--Buttons for employee actions -->
<div style="text-align:center;">
    <form action="emp_restock.php">
        <input type="submit" value="Restock Product">
    </form>

    <form action="emp_change_price.php">
        <input type="submit" value="Change Product Price">
    </form>

    <form action="emp_stock_history.php">
        <input type="submit" value="Stock History">
    </form>

    <form action="emp_price_history.php">
        <input type="submit" value="Price History">
    </form>
</div>

</body>
</html>