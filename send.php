<?php

ini_set('display_errors', '1');
ini_set('display_startup_errors', '1');
error_reporting(E_ALL);
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

if ($chatid === null) {
    echo "Chat not found.";
}
?>
<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Chat - <?php echo htmlspecialchars($chatName);?></title>
        <link rel="stylesheet" href="style.css">
        <link rel="stylesheet" href="https://house-778.theorangecow.org/base/style.css">
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
            <h1>Chat: <?php echo htmlspecialchars($chatName);?></h1>
            <p><?php echo htmlspecialchars($chatid);?></p>
            <a href="index.php">Home</a><br>
            <div id="chatBox"></div><br><br>
            <div id="send">
                <input type="text" id="message">
                <button id="submit" onclick="sendMessage()">Send</button>
            </div>
            <br><a href="property.php?chatName=<?php echo htmlspecialchars($chatName);?>"><?php echo htmlspecialchars($chatName);?> Properties</a>
        </div>
        <script>
            function loadMessages() {
                $.get(`chat.php?action=load&chatName=<?php echo $chatName; ?>`, function(data) {
                    try {
                        const messages = typeof data === 'string' ? JSON.parse(data) : data;
                        if (Array.isArray(messages)) {
                            $('#chatBox').html(messages.map(msg => `<p class ="message"><strong>${msg.username}</strong></a>: ${msg.message}</p>`).join(''));
                        } else {
                            $('#chatBox').html('<p>No messages found or access denied.</p>');
                        }
                    } catch (e) {
                        console.error("Error parsing JSON response:", e);
                        $('#chatBox').html('<p>Error loading messages. Please try again.</p>');
                    }
                });
            }
    
            function sendMessage() {
                const message = $('#message').val();
                $.post('chat.php', JSON.stringify({
                    action: 'send',
                    chatName: '<?php echo $chatName; ?>',
                    message: message
                }), function(response) {
                    $('#message').val('');
                    console.log("Response from server:", response);
                    loadMessages();
                }).fail(function(jqXHR, textStatus, errorThrown) {
                    console.error("Message send failed:", textStatus, errorThrown);
                    alert("Failed to send message. Please try again.");
                });
            }
    
            loadMessages();
            setInterval(loadMessages, 2000);
        </script>
    
        <?php
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            ob_start();
            include 'settings.php';
            $output = ob_get_clean();
            echo '<div id="errorOutput">' . nl2br(htmlspecialchars($output)) . '</div>';
        }
        ?>
    </body>
    <script src="https://theme.house-778.theorangecow.org/background.js"></script>
    <script src="https://house-778.theorangecow.org/base/main.js"></script>
    <script src="https://house-778.theorangecow.org/base/sidebar.js"></script>
</html>
