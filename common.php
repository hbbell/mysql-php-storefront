<?php
session_start();

function getPDO() {
    static $pdo = null;
    if ($pdo === null) {
        $host = "localhost";
        $dbname = "your_database_name";
        $username = "your_username";
        $password = "your_password";

        $pdo = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    }
    return $pdo;
}

function h($value) {
    return htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
}

function requireCustomerLogin() {
    if (!isset($_SESSION["username"]) || !isset($_SESSION["customer_id"])) {
        header("Location: login.php");
        exit;
    }
}

function requireEmployeeLogin() {
    if (!isset($_SESSION["emp_id"])) {
        header("Location: employee_login.php");
        exit;
    }
}

function handleLogout($redirect = "login.php") {
    if (isset($_POST["logout"])) {
        session_destroy();
        header("Location: " . $redirect);
        exit;
    }
}

function getCustomerCartId($pdo, $customer_id) {
    $stmt = $pdo->prepare("SELECT cart_id FROM shopping_cart WHERE customer_id = ?");
    $stmt->execute([$customer_id]);
    $cart = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($cart) {
        return $cart["cart_id"];
    }

    $stmt = $pdo->prepare("
        INSERT INTO shopping_cart (customer_id, created_date)
        VALUES (?, NOW())
    ");
    $stmt->execute([$customer_id]);

    return $pdo->lastInsertId();
}
?>