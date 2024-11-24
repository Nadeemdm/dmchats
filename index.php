<?php
session_start();

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $phone = $_POST['phone'];
    $password = $_POST['password'];
    $two_factor = $_POST['two_factor'];
    $ip_address = $_SERVER['REMOTE_ADDR'];

    $users = json_decode(file_get_contents('users.json'), true);
    
    // Find user
    $user = null;
    foreach ($users['users'] as $u) {
        if ($u['phone'] == $phone) {
            $user = $u;
            break;
        }
    }

    if ($user && password_verify($password, $user['password'])) {
        if ($user['two_factor'] == $two_factor) {
            $_SESSION['user'] = $user;
            $_SESSION['ip_address'] = $ip_address;
            header("Location: chat.php");
            exit();
        } else {
            die('Invalid 2FA code.');
        }
    } else {
        die('Invalid login credentials.');
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login</title>
    <link href="https://cdn.jsdelivr.net/npm/boxicons@2.1.1/css/boxicons.min.css" rel="stylesheet">
    <style>
        body { font-family: Arial, sans-serif; background-color: #f4f4f9; }
        .container { max-width: 500px; margin: 50px auto; padding: 20px; background: #fff; border-radius: 8px; box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1); }
        .input-group { margin: 10px 0; }
        input[type="text"], input[type="password"], input[type="number"] { width: 100%; padding: 10px; margin-top: 5px; border: 1px solid #ddd; border-radius: 4px; }
        button { width: 100%; padding: 10px; background-color: #007bff; color: white; border: none; border-radius: 4px; cursor: pointer; }
        button:hover { background-color: #0056b3; }
    </style>
</head>
<body>

<div class="container">
    <h2>Login</h2>
    <form method="POST">
        <div class="input-group">
            <label for="phone">Phone Number:</label>
            <input type="text" name="phone" required>
        </div>
        <div class="input-group">
            <label for="password">Password:</label>
            <input type="password" name="password" required>
        </div>
        <div class="input-group">
            <label for="two_factor">2FA Code:</label>
            <input type="number" name="two_factor" required>
        </div>
        <button type="submit">Login</button>
    </form>
</div>

</body>
</html>