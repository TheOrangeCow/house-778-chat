<?php
include "../base/chech.php"; 
include "../base/main.php";
session_start(); 
require 'encryption_key.php';
include 'alert.php';

$username = $_SESSION["username"];
$chatName = $_GET['chatName'];
$chats = json_decode(file_get_contents('chats.json'), true);

$chatid = null;
$chatowner = null;

foreach ($chats as $chat) {
    if ($chat['name'] === $chatName) {
        $chatid = $chat['id'];
        $chatowner = $chat['admin'];
        break;
    }
}


?>

<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Chat - Properties for <?php echo htmlspecialchars($chatName); ?></title>
        <link rel="stylesheet" href="https://house-778.theorangecow.org/base/style.css">
        <link rel="stylesheet" href="style.css">
        <link rel="preconnect" href="https://fonts.googleapis.com">
        <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
        <link href="https://fonts.googleapis.com/css2?family=Comfortaa:wght@300..700&display=swap" rel="stylesheet">
        <link rel="icon" href="https://house-778.theorangecow.org/base/icon.ico" type="image/x-icon">
    </head>
    <body>
        <canvas class="back" id="canvas"></canvas>
        <?php include '../base/sidebar.php'; ?>
        <div class="con">
            <button class="circle-btn" onclick="openNav()">☰</button> 
            <?php 
            if ($chatid === null) { ?>
                <h1>Chat not found</h1>
            <?php } else { ?>
                <h1>Chat - Properties for <?php echo htmlspecialchars($chatName); ?></h1>
                <form id="chatManagementForm">
                    <br>
                    <input type="hidden" name="chatName" id="chatName" value="<?php echo htmlspecialchars($chatName); ?>">
                    <label for="action">Action</label><br>
                    <select name="action" id="action" required>
                        <option value="deleteChat">Delete Chat</option>
                        <option value="renameChat">Rename Chat</option>
                        <option value="deleteAllMessages">Delete All Messages</option>
                        <option value="deleteMessage">Delete Message</option>
                        <option value="transferAdmin">Transfer Admin Rights</option>
                        <option value="regenerateJoinCode">Regenerate Join Code</option>
                        <option value="leaveChat">Leave Chat</option>
                        <option value="censored">Toggle Censored</option>
                    </select>
                
                    <div id="additionalFields">
                        <div id="newAdminField" style="display: none;">
                            <label for="newAdmin">Transfer Admin Rights To:</label>
                            <input type="text" name="newAdmin" id="newAdmin">
                        </div>
                        <div id="newChatNameField" style="display: none;">
                            <label for="newChatName">New Chat Name:</label>
                            <input type="text" name="newChatName" id="newChatName">
                        </div>
                        <div id="messageIdField" style="display: none;">
                            <label for="messageId">Message ID:</label>
                            <input type="text" name="messageId" id="messageId">
                        </div>
                    </div>
                
                    <input type="submit" value="Submit"><br>
                </form>
            <?php } ?>
            <a href="index.php">Home</a>
        </div>
        <script>
            document.getElementById('chatManagementForm').addEventListener('submit', function(event) {
                event.preventDefault();
                const formData = new FormData(this);
                fetch('settings.php', {
                    method: 'POST',
                    body: formData
                })
                .then(response => response.text())
                .then(data => {
                    showAlert(data);
                })
                .catch(error => {
                    console.error('Error:', error);
                });
            });
        
            document.getElementById('action').addEventListener('change', function() {
                const value = this.value;
                document.getElementById('newAdminField').style.display = (value === 'transferAdmin') ? 'block' : 'none';
                document.getElementById('newChatNameField').style.display = (value === 'renameChat') ? 'block' : 'none';
                document.getElementById('messageIdField').style.display = (value === 'deleteMessage') ? 'block' : 'none';
            });
        </script>
    </body>
    <script src="https://theme.house-778.theorangecow.org/background.js"></script>
    <script src="https://house-778.theorangecow.org/base/main.js"></script>
    <script src="https://house-778.theorangecow.org/base/sidebar.js"></script>
</html>

