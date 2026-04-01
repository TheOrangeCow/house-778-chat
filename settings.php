<?php
session_start();
require 'encryption_key.php';

$chatsFile = 'chats.json';
if (!file_exists($chatsFile)) {
    file_put_contents($chatsFile, json_encode([]));
}

$username = $_SESSION["username"];
$chatName = $_POST['chatName'];
$action = $_POST['action'] ?? null;

switch ($action) {
    case 'deleteChat':
        echo deleteChat($chatName);
        break;
    case 'renameChat':
        $newChatName = $_POST['newChatName'] ?? null;
        echo renameChat($chatName, $newChatName);
        break;
    case 'deleteAllMessages':
        echo deleteAllMessages($chatName);
        break;
    case 'deleteMessage':
        $messageId = $_POST['messageId'] ?? null;
        echo deleteMessage($chatName, $messageId);
        break;
    case 'transferAdmin':
        $newAdmin = $_POST['newAdmin'] ?? null;
        echo transferAdmin($chatName, $newAdmin);
        break;
    case 'regenerateJoinCode':
        echo regenerateJoinCode($chatName);
        break;
    case 'leaveChat':
        echo leaveChat($chatName);
        break;
    case 'censored':
        echo censored($chatName);
        break;
    default:
        echo 'Invalid action.';
        break;
}

function deleteChat($chatName) {
    global $chatsFile, $username;
    $chats = json_decode(file_get_contents($chatsFile), true);

    foreach ($chats as $key => $chat) {
        if ($chat['name'] === $chatName) {
            if ($chat['admin'] === $username) {
                deleteAllMessages($chatName);
                unset($chats[$key]);
                file_put_contents($chatsFile, json_encode(array_values($chats)));
                return 'Chat deleted successfully.';
            } else {
                return 'Only the admin can delete this chat.';
            }
        }
    }

    return 'Chat not found.';
}

function renameChat($chatName, $newChatName) {
    global $chatsFile, $username;
    $chats = json_decode(file_get_contents($chatsFile), true);

    foreach ($chats as $chat) {
        if ($chat['name'] === $newChatName) {
            return 'A chat with this new name already exists.';
        }
    }

    foreach ($chats as &$chat) {
        if ($chat['name'] === $chatName) {
            if ($chat['admin'] === $username) {
                $chat['name'] = $newChatName;
                file_put_contents($chatsFile, json_encode($chats));
                return 'Chat renamed successfully.';
            } else {
                return 'Only the admin can rename this chat.';
            }
        }
    }

    return 'Chat not found.';
}
function deleteAllMessages($chatName) {
    global $username;
    $fileName = 'chats/' . md5($chatName) . '.json';
    $chats = json_decode(file_get_contents('chats.json'), true);

    foreach ($chats as $chat) {
        if ($chat['name'] === $chatName) {
            if ($chat['admin'] === $username) {
                if (file_exists($fileName)) {
                    unlink($fileName);
                    return 'Chat messages deleted successfully.';
                } else {
                    return 'Chat messages not found.';
                }
            } else {
                return 'Only the admin can delete the chat file.';
            }
        }
    }

    return 'Chat not found.';
}


function deleteMessage($chatName, $messageId) {
    global $username;
    $fileName = 'chats/' . md5($chatName) . '.json';
    $chats = json_decode(file_get_contents('chats.json'), true);

    foreach ($chats as $chat) {
        if ($chat['name'] === $chatName) {
            if ($chat['admin'] === $username) {
                $messages = json_decode(decrypt(file_get_contents($fileName), ENCRYPTION_KEY), true);
                if (isset($messages[$messageId])) {
                    unset($messages[$messageId]);
                    $encryptedMessages = encrypt(json_encode(array_values($messages)), ENCRYPTION_KEY);
                    file_put_contents($fileName, $encryptedMessages);
                    return 'Message deleted successfully.';
                }
                return 'Message not found.';
            } else {
                return 'Only the admin can delete messages.';
            }
        }
    }

    return 'Chat not found.';
}

function transferAdmin($chatName, $newAdmin) {
    global $chatsFile, $username;
    $chats = json_decode(file_get_contents($chatsFile), true);

    foreach ($chats as &$chat) {
        if ($chat['name'] === $chatName) {
            if ($chat['admin'] === $username) {
                if ($chat['type'] === 'private'){
                    if (in_array($newAdmin, $chat['participants'])) {
                         $chat['admin'] = $newAdmin;
                    }else{
                        return 'New admin must be a participant of the chat.';
                    }
                }else{
                    $chat['admin'] = $newAdmin;
                }
            
                file_put_contents($chatsFile, json_encode($chats));
                return "Admin transferred to $newAdmin.";
                
               
            } else {
                return 'Only the admin can transfer admin rights.';
            }
        }
    }

    return 'Chat not found.';
}

function regenerateJoinCode($chatName) {
    global $chatsFile, $username;
    $chats = json_decode(file_get_contents($chatsFile), true);

    foreach ($chats as &$chat) {
        if ($chat['name'] === $chatName) {
            if ($chat['admin'] === $username && $chat['type'] === 'private') {
                $chat['joinCode'] = rand(1000, 9999);
                file_put_contents($chatsFile, json_encode($chats));
                return 'Join code regenerated.';
            } else {
                return 'Only the admin of a private chat can regenerate the join code.';
            }
        }
    }

    return 'Chat not found.';
}

function leaveChat($chatName) {
    global $chatsFile, $username;
    $chats = json_decode(file_get_contents($chatsFile), true);

    foreach ($chats as &$chat) {
        if ($chat['name'] === $chatName) {
            if ($chat['admin'] === $username) {
                return 'Admin cannot leave without transferring admin rights.';
            }

            if (($key = array_search($username, $chat['participants'])) !== false) {
                unset($chat['participants'][$key]);
                file_put_contents($chatsFile, json_encode($chats));
                return 'Left the chat successfully.';
            }
            return 'You are not a participant of this chat.';
        }
    }

    return 'Chat not found.';
}



function censored($chatName) {
    global $chatsFile, $username;
    $chats = json_decode(file_get_contents($chatsFile), true);

    foreach ($chats as &$chat) {
        if ($chat['name'] === $chatName) {
            if ($chat['admin'] === $username) {
                if (isset($chat['censored'])) {
                    $chat['censored'] = !$chat['censored'];
                    file_put_contents($chatsFile, json_encode(array_values($chats)));
                    return 'Censorship status updated successfully.';
                } else {
                    return 'Censorship status field not found in this chat.';
                }
            } else {
                return 'Only the admin can change the censorship status of this chat.';
            }
        }
    }

    return 'Chat not found.';
}

?>