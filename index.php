<?php
include "../base/chech.php"; 
include "../base/main.php";
session_start();
include 'alert.php';
require 'encryption_key.php';

$username = $_SESSION["username"];
$chatsFile = 'chats.json';

if (!file_exists($chatsFile)) {
    file_put_contents($chatsFile, json_encode([]));
}

$chats = json_decode(file_get_contents($chatsFile), true);
?>

<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Chat - Home</title>
        <link rel="stylesheet" href="https://house-778.theorangecow.org/base/style.css">
        <link rel="stylesheet" href="style.css">
        <link rel="preconnect" href="https://fonts.googleapis.com">
        <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
        <link href="https://fonts.googleapis.com/css2?family=Comfortaa:wght@300..700&display=swap" rel="stylesheet">
        <link rel="icon" href="https://house-778.theorangecow.org/base/icon.ico" type="image/x-icon">
        <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>
    </head>
    <body>
        <canvas class="back" id="canvas"></canvas>
        <?php include '../base/sidebar.php'; ?>
        <div class="con">
            <button class="circle-btn" onclick="openNav()">☰</button> 
            <button id="chatss" 
                onclick="toggleVisibility('chats', 'dev'); styleButton(this, '#0056b3');" 
                style="width: 49%; float: left; background-color: #0056b3;">
                Chats
            </button>
            <button id="jion" 
                onclick="toggleVisibility('dev', 'chats'); styleButton(this, '#0056b3');" 
                style="float: right; width: 49%;">
                Join or Add
            </button>
            
            
            <br><br><h1>Welcome, <?php echo htmlspecialchars($username); ?></h1>
        
            <div id="chats">
                <h3>Your Chats</h3>
                <div id="chatList">
                    <?php foreach ($chats as $chat): ?>
                        <?php if ($chat['type'] === 'public' || in_array($username, $chat['participants'])): ?>
                            <a href="send.php?chatName=<?php echo urlencode($chat['name']); ?>">
                                <?php echo htmlspecialchars($chat['name']); ?> (<?php echo htmlspecialchars($chat['type']); ?>) 
                            </a><br>
                        <?php endif; ?>
                    <?php endforeach; ?>
                </div>
            </div>
            
            
            <div id="dev" class="hidden">
                <div id="newChat">
                    <h3>Create a New Chat</h3>
                    <label>Chat Name:</label><br>
                    <input type="text" id="chatName" required><br><br>
                    <label>Type:</label><br>
                    <select id="chatType">
                        <option value="public">Public</option>
                        <option value="private">Private</option>
                    </select><br><br>
                    <button onclick="createChat()">Create Chat</button>
                </div>
                <div id="joinChat">
                    <h3>Join Private Chat</h3>
                    <label>Join Code:</label><br>
                    <input type="text" id="joinCode" required><br><br>
                    <button onclick="joinChat()">Join Chat</button>
                </div><br>
                <a href="create_api.php">Get an API</a>
            </div>
        </div>
        <script>
            function toggleVisibility(showId, hideId) {
                document.getElementById(hideId).classList.add('hidden');
                document.getElementById(showId).classList.remove('hidden');
            }
            
            function styleButton(button, color) {
                document.querySelectorAll('button').forEach(btn => {
                    btn.style.backgroundColor = '';
                });
                button.style.backgroundColor = color;
            }
            function toggleView(showId, hideId, activeBtnId, inactiveBtnId) {
                document.getElementById(showId).classList.remove('hidden');
                document.getElementById(hideId).classList.add('hidden');
                document.getElementById(activeBtnId).style.backgroundColor = '#0056b3';
                document.getElementById(inactiveBtnId).style.backgroundColor = '';
            }

            function loadChats() {
                $.get('chat.php?action=list', function(data) {
                    const chats = JSON.parse(data);
                    $('#chatList').html(chats.map(chat => 
                        `<a href="send.php?chatName=${encodeURIComponent(chat.name)}">${chat.name} (${chat.type})</a><br>`
                    ).join(''));
                });
            }

            function createChat() {
                const chatName = $('#chatName').val();
                const chatType = $('#chatType').val();
                $.post('chat.php', JSON.stringify({ action: 'create', chatName, chatType }), function(response) {
                    showAlert(response.message);
                    loadChats();
                }, 'json');
            }

            function joinChat() {
                const joinCode = $('#joinCode').val();
                $.post('chat.php', JSON.stringify({ action: 'join', joinCode }), function(response) {
                    showAlert(response.message);
                    loadChats();
                }, 'json');
            }

            $(document).ready(function() {
                loadChats();
            });
        </script>
        
    </body>
    <script src="https://theme.house-778.theorangecow.org/background.js"></script>
    <script src="https://house-778.theorangecow.org/base/main.js"></script>
    <script src="https://house-778.theorangecow.org/base/sidebar.js"></script>
</html>
