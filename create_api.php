




<?php
include "../base/chech.php"; 
include "../base/main.php";
session_start();

$apiKeysFile = 'api_keys.json';

$apiKeys = json_decode(file_get_contents($apiKeysFile), true);
if (!is_array($apiKeys)) {
    $apiKeys = [];
}

function generateApiKey() {
    return uniqid('key_', true) . '_' . date('YmdHis');
}

function addApiKey($key, $chatId) {
    global $apiKeys, $apiKeysFile;
    if (!isset($apiKeys[$key])) {
        $apiKeys[$key] = $chatId;
        file_put_contents($apiKeysFile, json_encode($apiKeys, JSON_PRETTY_PRINT));
        return $key;
    }
    return false;
}

$newApiKey = null;
$resolt = "";
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $chatId = $_POST['chatId'];
    
    $newApiKey = addApiKey(generateApiKey(), $chatId);
    
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Get API</title>
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
        <h1>API Key</h1>
        <form method="POST">
            <label for="chatId">Enter Chat ID:</label>
            <input type="text" id="chatId" name="chatId" required placeholder="Your Chat ID">
            <p>It will only work with this chat.</p>
            <button type="submit">Generate API Key</button>
            <?php echo $resolt. "<br>";?>
        </form>
        
        <?php if ($newApiKey): ?>
            <h2>Your New API Key:</h2>
            <p><?php echo htmlspecialchars($newApiKey); ?></p>
        <?php endif; ?>
        <br><a href ="index.php">Home</a>
        <br><p>Use this code to connect with our servers. If you make changes, publish it to w4 schools.</p>
            <div class="code">
                <button class="copy-button" onclick="copyCode(this)">Copy Code</button>
                <pre>
<pre>
<code>&lt;!DOCTYPE html&gt;
&lt;html lang="en"&gt;
&lt;head&gt;
    &lt;meta charset="UTF-8"&gt;
    &lt;meta name="viewport" content="width=device-width, initial-scale=1.0"&gt;
    &lt;title&gt;Chat Application&lt;/title&gt;
    &lt;style&gt;
        #messages {
            border: 1px solid #ccc;
            padding: 10px;
            height: 300px;
            overflow-y: auto;
            margin-bottom: 10px;
        }
        form {
            display: flex;
            gap: 10px;
        }
    &lt;/style&gt;
&lt;/head&gt;
&lt;body&gt;
    &lt;h1&gt;Chat Application&lt;/h1&gt;
    &lt;div id="messages"&gt;&lt;/div&gt;

    &lt;form id="messageForm"&gt;
        &lt;input type="text" id="messageText" placeholder="Type your message" required&gt;
        &lt;input type="hidden" id="chatId" value="Your_chat_id"&gt;
        &lt;input type="hidden" id="joinCode" value="Join_code_only_for_privet_chat_for_public_chats_put_none"&gt;
        &lt;input type="hidden" id="apiKey" value="Your_api_key"&gt;
        &lt;input type="hidden" id="user" value="Your_username"&gt;
        &lt;button type="submit"&gt;Send Message&lt;/button&gt;
    &lt;/form&gt;

    &lt;script&gt;
        document.getElementById('messageForm').addEventListener('submit', function(event) {
            event.preventDefault();

            const messageText = document.getElementById('messageText').value;
            const chatId = document.getElementById('chatId').value;
            const joinCode = document.getElementById('joinCode').value;
            const apiKey = document.getElementById('apiKey').value;
            const user = document.getElementById('user').value;

            fetch('https://chat.house-778.theorangecow.org/api.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({
                    chatId: chatId,
                    messageText: messageText,
                    joinCode: joinCode,
                    apiKey: apiKey,
                    user: user
                })
            })
            .then(response =&gt; response.json())
            .then(data =&gt; {
                if (data.status === 'success') {
                    appendMessage(`You: ${messageText}`);
                    fetchMessages(chatId, joinCode, apiKey);
                } else {
                    console.log(data.message);
                }
            })
            .catch(() =&gt; console.log('An error occurred while sending the message.'));

            document.getElementById('messageText').value = '';
        });

        function appendMessage(message) {
            const messagesDiv = document.getElementById('messages');
            messagesDiv.innerHTML += `&lt;div&gt;${message}&lt;/div&gt;`;
            messagesDiv.scrollTop = messagesDiv.scrollHeight;
        }

        function fetchMessages(chatId, joinCode, apiKey) {
            fetch(`https://chat.house-778.theorangecow.org/api.php?chatId=${chatId}&joinCode=${joinCode}&apiKey=${apiKey}`)
                .then(response =&gt; response.json())
                .then(data =&gt; {
                    const messagesDiv = document.getElementById('messages');
                    messagesDiv.innerHTML = '';
                    if (Array.isArray(data)) {
                        data.forEach(message =&gt; {
                            appendMessage(`${message.username}: ${message.message}`);
                        });
                    } else {
                        console.log(data.message);
                    }
                })
                .catch(() =&gt; console.log('An error occurred while loading messages.'));
        }

        window.onload = setInterval(load, 100);
        function load() {
            const chatId = document.getElementById('chatId').value;
            const joinCode = document.getElementById('joinCode').value;
            const apiKey = document.getElementById('apiKey').value;
            fetchMessages(chatId, joinCode, apiKey);
        };
    &lt;/script&gt;
&lt;/body&gt;
&lt;/html&gt;
</code>
</pre>
</div>
        
            
            

        </div>
    </body>
    <script>
        function copyCode(button) {
            const codeDiv = button.parentElement;
            const code = codeDiv.querySelector('code').innerText;
            navigator.clipboard.writeText(code).then(() => {
                button.textContent = 'Copied!';
                setTimeout(() => {
                    button.textContent = 'Copy Code';
                }, 2000);
            }).catch(err => {
                console.error('Failed to copy code: ', err);
            });
        }
            </script>
    </div>
</body>
    <script src="https://theme.house-778.theorangecow.org/background.js"></script>
    <script src="https://house-778.theorangecow.org/base/main.js"></script>
    <script src="https://house-778.theorangecow.org/base/sidebar.js"></script>
</html>
</html>

