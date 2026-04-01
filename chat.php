<?php
session_start();
include "../base/main.php";
require 'encryption_key.php';
require 'rudewords.php';

$chatsFile = 'chats.json';
if (!file_exists($chatsFile)) {
    file_put_contents($chatsFile, json_encode([]));
}

$action = $_GET['action'] ?? json_decode(file_get_contents('php://input'), true)['action'];

$username = $_SESSION["username"];

switch ($action) {
    case 'list':
        echo json_encode(listChats());
        break;

    case 'create':
        $input = json_decode(file_get_contents('php://input'), true);
        $chatName = $input['chatName'] ?? null;
        $chatType = $input['chatType'] ?? null;

        if (!$chatName || !$chatType) {
            echo json_encode(['status' => 'error', 'message' => 'Chat name and type are required.']);
            exit;
        }
        if (stripos($_SESSION['username'], "Guest") === true) {
            echo json_encode(['status' => 'error', 'message' => 'You have to have an account to make an chat']);
            exit;
        }

        $chats = json_decode(file_get_contents($chatsFile), true);
        foreach ($chats as $chat) {
            if ($chat['name'] === $chatName) {
                echo json_encode(['status' => 'error', 'message' => 'Chat already exists.']);
                exit;
            }
        }
        $id = "chat_" . date("YmdHis");
        $joinCode = ($chatType === 'private') ? rand(1000, 9999) : null;
        $newChat = [
            'id' => $id,
            'name' => $chatName,
            'admin' => $username,
            'censored'=> true,
            'type' => $chatType,
            'joinCode' => $joinCode,
            'participants' => ($chatType === 'private' ? [$username] : [])
        ];
        $chats[] = $newChat;
        file_put_contents($chatsFile, json_encode($chats));


        if ($chatType === 'private') {
            $initialMessage = "Private chat created. Join code: $joinCode";
            sendMessage($chatName, $initialMessage);
        }

        echo json_encode(['status' => 'success', 'message' => $chatType === 'private' ? "Private chat created with code: $joinCode" : "Chat created"]);
        break;

    case 'join':
        $input = json_decode(file_get_contents('php://input'), true);
        $joinCode = $input['joinCode'] ?? null;

        if (!$joinCode) {
            echo json_encode(['status' => 'error', 'message' => 'Join code is required.']);
            exit;
        }
        if (stripos($_SESSION['username'], "Guest") === true) {
            echo json_encode(['status' => 'error', 'message' => 'You have to have an account to join an privet chat']);
            exit;
        }


        $chats = json_decode(file_get_contents($chatsFile), true);
        foreach ($chats as &$chat) {
            if ($chat['type'] === 'private' && $chat['joinCode'] == $joinCode) {
                if (!in_array($username, $chat['participants'])) {
                    $chat['participants'][] = $username;
                    file_put_contents($chatsFile, json_encode($chats));
                }
                echo json_encode(['status' => 'success', 'message' => "Joined {$chat['name']}"]);
                return;
            }
        }
        echo json_encode(['status' => 'error', 'message' => 'Incorrect join code or chat not found']);
        break;

    case 'load':
        $chatName = $_GET['chatName'] ?? null;
        if (!$chatName) {
            echo json_encode(['status' => 'error', 'message' => 'Chat name is required.']);
            exit;
        }
        echo json_encode(loadMessages($chatName));
        break;

    case 'send':
        $input = json_decode(file_get_contents('php://input'), true);
        if (!isset($input['chatName']) || !isset($input['message'])) {
            echo json_encode(['status' => 'error', 'message' => 'Invalid input data.']);
            exit;
        }
        
        echo json_encode(sendMessage($input['chatName'], $input['message']));
        break;

    default:
        echo json_encode(['status' => 'error', 'message' => 'Invalid action.']);
        break;
}

function listChats() {
    global $username, $chatsFile;
    $chats = json_decode(file_get_contents($chatsFile), true);
    $filteredChats = [];

    foreach ($chats as $chat) {
        if ($chat['type'] === 'public' || (in_array($username, $chat['participants']))) {
            $filteredChats[] = $chat;
        }
    }

    return $filteredChats;
}

function loadMessages($chatName) {
    global $username;
    $fileName = 'chats/' . md5($chatName) . '.json';
    $chats = json_decode(file_get_contents('chats.json'), true);
    
    foreach ($chats as $chat) {
        if ($chat['name'] === $chatName) {
            if ($chat['type'] === 'private' && !in_array($username, $chat['participants'])) {
                return ['status' => 'error', 'message' => 'Access denied.'];
            }
            return file_exists($fileName) ? json_decode(decrypt(file_get_contents($fileName), ENCRYPTION_KEY), true) : [];
        }
    }

    return ['status' => 'error', 'message' => 'Chat not found'];
}

function sendMessage($chatName, $messageText) {
    global $username;
    $fileName = 'chats/' . md5($chatName) . '.json';
    $chats = json_decode(file_get_contents('chats.json'), true);
    
    foreach ($chats as $chat) {
        if ($chat['name'] === $chatName) {
            if ($chat['type'] === 'private' && !in_array($username, $chat['participants'])) {
                return ['status' => 'error', 'message' => 'Access denied.'];
            }
            if ($chat['censored'] == true){
                $messageText = checkForRudeWords($messageText);
            }
            $messages = file_exists($fileName) ? json_decode(decrypt(file_get_contents($fileName), ENCRYPTION_KEY), true) : [];
            $newMessage = [
                'username' => $_SESSION['username'],
                'message' => htmlspecialchars($messageText),
                'timestamp' => time()
            ];
            $messages[] = $newMessage;
            $encryptedMessages = encrypt(json_encode($messages), ENCRYPTION_KEY);
            file_put_contents($fileName, $encryptedMessages);

            return ['status' => 'success', 'message' => 'Message sent successfully.'];
        }
    }
    
    return ['status' => 'error', 'message' => 'Chat not found.'];
}

function checkForRudeWords($message) {
    $rudeWords = rudewords();
    foreach ($rudeWords as $word) {
        if (preg_match("/\b$word\b/i", $message)) {
            return "This message contains inappropriate language.";
        }
    }
    return htmlspecialchars($message);
}
