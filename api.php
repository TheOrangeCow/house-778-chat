<?php

session_start();
require 'encryption_key.php';
require 'rudewords.php';
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST, GET, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization");
header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(204);
    exit;
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

function isValidApiKey($apiKey, $chatid) {
    $apiKeysFile = 'api_keys.json';
    if (!file_exists($apiKeysFile)) {
        return false;
    }
    $apiKeys = json_decode(file_get_contents($apiKeysFile), true);
    return isset($apiKeys[$apiKey]) && $apiKeys[$apiKey] === $chatid;
}


function send($chat, $messageText, $fileName, $user) {
    if ($chat['censored']) {
        $messageText = checkForRudeWords($messageText);
    }
    $messages = file_exists($fileName) ? json_decode(decrypt(file_get_contents($fileName), ENCRYPTION_KEY), true) : [];
    $newMessage = [
        'username' => $user,
        'message' => htmlspecialchars($messageText),
        'timestamp' => time()
    ];
    $messages[] = $newMessage;
    $encryptedMessages = encrypt(json_encode($messages), ENCRYPTION_KEY);
    file_put_contents($fileName, $encryptedMessages);
    return ['status' => 'success', 'message' => 'Message sent successfully.'];
}

function sendMessage($chatId, $messageText, $joinCode, $user) {
    $apiKey = $_GET['apiKey'] ?? null;
    if ($apiKey && !isValidApiKey($apiKey, $chatId)) {
        http_response_code(403);
        return ['status' => 'error', 'message' => 'Invalid API key.'];
    }

    $chats = json_decode(file_get_contents('chats.json'), true);
    foreach ($chats as $chat) {
        if ($chat['id'] === $chatId) {
            $chatName = $chat['name'];
            $fileName = 'chats/' . md5($chatName) . '.json';
            if ($chat['type'] === 'private' && $chat['joinCode'] !== $joinCode) {
                http_response_code(403);
                return ['status' => 'error', 'message' => 'Access denied.'];
            }
            return send($chat, $messageText, $fileName, $user);
        }
    }
    http_response_code(404);
    return ['status' => 'error', 'message' => 'Chat not found.'];
}

function load($fileName) {
    return file_exists($fileName) ? json_decode(decrypt(file_get_contents($fileName), ENCRYPTION_KEY), true) : [];
}

function loadMessages($chatId, $joinCode) {
    $apiKey = $_GET['apiKey'] ?? null;
    if ($apiKey && !isValidApiKey($apiKey, $chatId)) {
        http_response_code(403);
        return ['status' => 'error', 'message' => 'Invalid API key.'];
    }

    $chats = json_decode(file_get_contents('chats.json'), true);
    foreach ($chats as $chat) {
        if ($chat['id'] === $chatId) {
            $chatName = $chat['name'];
            $fileName = 'chats/' . md5($chatName) . '.json';
            if ($chat['type'] === 'private' && $chat['joinCode'] !== $joinCode) {
                http_response_code(403);
                return ['status' => 'error', 'message' => 'Access denied.'];
            }
            return load($fileName);
        }
    }
    http_response_code(404);
    return ['status' => 'error', 'message' => 'Chat not found.'];
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $rawData = file_get_contents('php://input');
    error_log("Raw input: " . $rawData);
    $data = json_decode($rawData, true);

    if (json_last_error() !== JSON_ERROR_NONE) {
        http_response_code(400);
        echo json_encode(['status' => 'error', 'message' => 'Invalid JSON.']);
        exit;
    }

    if (!isset($data['chatId'], $data['messageText'], $data['joinCode'], $data['user'])) {
        http_response_code(400);
        echo json_encode(['status' => 'error', 'message' => 'Invalid input.']);
        exit;
    }

    $response = sendMessage($data['chatId'], $data['messageText'], $data['joinCode'], $data['user']);
    echo json_encode($response);

} elseif ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $chatId = $_GET['chatId'] ?? null;
    $joinCode = $_GET['joinCode'] ?? null;
    $response = loadMessages($chatId, $joinCode);
    echo json_encode($response);
}
?>
