<?php
session_start();

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = $_POST['username'];
    $phone = $_POST['phone'];
    $password = password_hash($_POST['password'], PASSWORD_BCRYPT);
    $two_factor = $_POST['two_factor'];
    $ip_address = $_SERVER['REMOTE_ADDR'];

    // Validate 2FA code length
    if (strlen($two_factor) != 5 || !is_numeric($two_factor)) {
        die('2FA must be a 5-digit number');
    }

    $users = json_decode(file_get_contents('users.json'), true);
    
    // Check if phone number already exists
    foreach ($users['users'] as $user) {
        if ($user['phone'] == $phone) {
            die('Phone number already in use.');
        }
    }

    $new_user = [
        'username' => $username,
        'phone' => $phone,
        'password' => $password,
        'two_factor' => $two_factor,
        'ip_address' => $ip_address
    ];

    $users['users'][] = $new_user;
    file_put_contents('users.json', json_encode($users));

    echo 'Registration successful! Please login.';
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Signup</title>
    <link href="https://cdn.jsdelivr.net/npm/boxicons@2.1.1/css/boxicons.min.css" rel="stylesheet">
    <style>
        body { font-family: Arial, sans-serif; background-color: #f4f4f9; }
        .container { max-width: 500px; margin: 50px auto; padding: 20px; background: #fff; border-radius: 8px; box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1); }
        .input-group { margin: 10px 0; }
        input[type="text"], input[type="password"], input[type="number"] { width: 100%; padding: 10px; margin-top: 5px; border: 1px solid #ddd; border-radius: 4px; }
        button { width: 100%; padding: 10px; background-color: #28a745; color: white; border: none; border-radius: 4px; cursor: pointer; }
        button:hover { background-color: #218838; }
    </style>
</head>
<body>

<div class="container">
    <h2>Create an Account</h2>
    <form method="POST">
        <div class="input-group">
            <label for="username">Username:</label>
            <input type="text" name="username" required>
        </div>
        <div class="input-group">
            <label for="phone">Phone Number:</label>
            <input type="text" name="phone" required>
        </div>
        <div class="input-group">
            <label for="password">Password:</label>
            <input type="password" name="password" required>
        </div>
        <div class="input-group">
            <label for="two_factor">2FA Code (5 digits):</label>
            <input type="number" name="two_factor" required>
        </div>
        <button type="submit">Register</button>
    </form>
</div>

</body>
</html>