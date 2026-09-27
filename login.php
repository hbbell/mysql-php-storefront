<?php
session_start();

$pdo = new PDO("mysql:host=classdb.it.mtu.edu;dbname=hbbell", "hbbell", "Nydsok-xehja9-tywveg");

$error = "";

if (isset($_POST["login"])) {
    $username = $_POST["username"];
    $password = $_POST["password"];

    $stmt = $pdo->prepare("
        SELECT * FROM customer 
        WHERE username = ? AND password = SHA2(?, 256)
    ");
    $stmt->execute([$username, $password]);

    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($user) {
        $_SESSION["username"] = $username;
        $_SESSION["customer_id"] = $user["customer_id"];
        header("Location: main.php");
        exit;
    } else {
        $error = "Invalid username or password";
    }
}
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

label {
    font-family: 'Segoe UI', Tahoma, sans-serif;
    font-size: 14px;
    color: #000000;
    font-weight: bold;
    display: block;
    width: 100%;
    text-align: left;
    margin-bottom: 3px;
}

input[type="text"],
input[type="password"] {
    background-color: #ffffff;
    display: block;
    margin: 0 auto;
}

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
</style>
</head>

<body>

<?php if ($error != "") { ?>
    <p style="
        position: fixed;
        top: 10px;
        left: 50%;
        transform: translateX(-50%);
        color: red;
        font-weight: bold;
        background: white;
        padding: 10px 20px;
        border: 1px solid red;
        border-radius: 8px;
        z-index: 9999;
    ">
        <?php echo $error; ?>
    </p>
<?php } ?>

<div style="text-align:center;">
    <h2>Customer Login</h2>

    

    <form method="post" action="login.php">
        <label for="username">Username:</label>
        <input type="text" name="username" id="username"><br>

        <label for="password">Password:</label>
        <input type="password" name="password" id="password"><br><br>

        <input type="submit" name="login" value="Login">
    </form>
</div>

<form action="registration.php" style="position: absolute; bottom: 30px; text-align: center;">
    <p style="color: red;">Need an account?</p>
    <input type="submit" name="register" value="Register Here!">
</form>


<form action="employee_login.php" style="
    position: fixed;
    bottom: 20px;
    left: 20px;
    margin: 0;
">
    <input type="submit" value="Employee Login">
</form>
</body>
</html>