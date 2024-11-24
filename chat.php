<?php
session_start();

// Check if the user is logged in
if (!isset($_SESSION['user'])) {
    header("Location: login.php");
    exit();
}

$user = $_SESSION['user'];
$users = json_decode(file_get_contents('users.json'), true);
$chats = json_decode(file_get_contents('chats.json'), true);

// Function to update user profile
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['update_profile'])) {
    $new_username = $_POST['username'];
    $new_phone = $_POST['phone'];
    $new_picture = $_FILES['profile_picture']['name'];
    move_uploaded_file($_FILES['profile_picture']['tmp_name'], "profile_pics/$new_picture");

    // Update user data in users.json
    foreach ($users['users'] as &$u) {
        if ($u['phone'] == $user['phone']) {
            $u['username'] = $new_username;
            $u['phone'] = $new_phone;
            $u['profile_picture'] = $new_picture;
        }
    }
    file_put_contents('users.json', json_encode($users));

    $_SESSION['user'] = $u; // Update session
    header("Location: chat.php");
    exit();
}

// Message sending logic
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['message']) && isset($_POST['receiver_phone'])) {
    $new_message = $_POST['message'];
    $receiver_phone = $_POST['receiver_phone'];

    // Encrypt the message
    $encrypted_message = openssl_encrypt($new_message, 'aes-128-cbc', 'encryptionkey', 0, '1234567890123456');

    // Add the message to the chat array
    $new_chat = [
        'sender' => $user['phone'],
        'receiver' => $receiver_phone,
        'message' => $encrypted_message,
        'timestamp' => time()
    ];
    $chats['chats'][] = $new_chat;

    // Save the updated chat data to the JSON file
    file_put_contents('chats.json', json_encode($chats));

    header("Location: chat.php");
    exit();
}

// Message searching logic
$search_results = [];
if (isset($_POST['search'])) {
    $search_term = $_POST['search_term'];
    foreach ($chats['chats'] as $chat) {
        if (strpos($chat['message'], $search_term) !== false) {
            $search_results[] = $chat;
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Chat</title>
    <link href="https://cdn.jsdelivr.net/npm/boxicons@2.1.1/css/boxicons.min.css" rel="stylesheet">
    <style>
        body { font-family: Arial, sans-serif; background-color: #f4f4f9; }
        .chat-box { max-width: 800px; margin: 50px auto; padding: 20px; background: #fff; border-radius: 8px; box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1); }
        .chat { margin-bottom: 20px; }
        .chat .sender { font-weight: bold; color: #007bff; }
        .chat .message { padding: 10px; background-color: #f1f1f1; border-radius: 4px; }
        .input-group { margin: 10px 0; }
        input[type="text"], textarea { width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 4px; }
        button { width: 100%; padding: 10px; background-color: #28a745; color: white; border: none; border-radius: 4px; cursor: pointer; }
        button:hover { background-color: #218838; }
        .message-actions { display: flex; justify-content: space-between; }
        .profile-box { text-align: center; margin-bottom: 30px; }
        .profile-box img { border-radius: 50%; width: 100px; height: 100px; object-fit: cover; }
    </style>
</head>
<body>

<div class="chat-box">
    <h2>Private Chat</h2>

    <!-- Profile Update Form -->
    <div class="profile-box">
        <img src="profile_pics/<?= $user['profile_picture'] ?>" alt="Profile Picture">
        <p>Username: <?= $user['username'] ?></p>
        <p>Phone: <?= $user['phone'] ?></p>
        <button onclick="document.getElementById('profile-modal').style.display='block'">Edit Profile</button>
    </div>

    <!-- Chat Search Form -->
    <form method="POST">
        <div class="input-group">
            <input type="text" name="search_term" placeholder="Search messages..." value="<?= isset($search_term) ? $search_term : '' ?>" required>
            <button type="submit" name="search">Search</button>
        </div>
    </form>

    <!-- Display Search Results -->
    <?php if (isset($search_term)): ?>
        <h4>Search Results for "<?= $search_term ?>"</h4>
        <?php if (count($search_results) > 0): ?>
            <?php foreach ($search_results as $chat): ?>
                <div class="chat">
                    <p class="sender"><?= $chat['sender'] == $user['phone'] ? 'You' : $chat['sender'] ?>:</p>
                    <p class="message"><?= openssl_decrypt($chat['message'], 'aes-128-cbc', 'encryptionkey', 0, '1234567890123456') ?></p>
                </div>
            <?php endforeach; ?>
        <?php else: ?>
            <p>No messages found.</p>
        <?php endif; ?>
    <?php endif; ?>

    <!-- Display All Chats -->
    <?php foreach ($chats['chats'] as $chat): ?>
        <?php if ($chat['sender'] == $user['phone'] || $chat['receiver'] == $user['phone']): ?>
            <div class="chat">
                <p class="sender"><?= $chat['sender'] == $user['phone'] ? 'You' : $chat['sender'] ?>:</p>
                <p class="message"><?= openssl_decrypt($chat['message'], 'aes-128-cbc', 'encryptionkey', 0, '1234567890123456') ?></p>
            </div>
        <?php endif; ?>
    <?php endforeach; ?>

    <!-- Send Message Form -->
    <form method="POST">
        <div class="input-group">
            <input type="text" name="receiver_phone" placeholder="Receiver's Phone" required>
        </div>
        <div class="input-group">
            <textarea name="message" placeholder="Type your message" required></textarea>
        </div>
        <button type="submit">Send Message</button>
    </form>
</div>

<!-- Profile Update Modal -->
<div id="profile-modal" style="display:none;">
    <div class="modal-content">
        <h3>Edit Profile</h3>
        <form method="POST" enctype="multipart/form-data">
            <div class="input-group">
                <label for="username">Username:</label>
                <input type="text" name="username" value="<?= $user['username'] ?>" required>
            </div>
            <div class="input-group">
                <label for="phone">Phone:</label>
                <input type="text" name="phone" value="<?= $user['phone'] ?>" required>
            </div>
            <div class="input-group">
                <label for="profile_picture">Profile Picture:</label>
                <input type="file" name="profile_picture">
            </div>
            <button type="submit" name="update_profile">Update Profile</button>
        </form>
    </div>
</div>

</body>
</html>
