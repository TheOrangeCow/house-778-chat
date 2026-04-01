<?php
if (file_exists(__DIR__ . '/.env')) {
    $lines = file(__DIR__ . '/.env', FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        if (strpos(trim($line), '#') === 0) continue;
        putenv($line);
    }
}
define('ENCRYPTION_KEY', ENCRYPTION_KEY);

function encrypt($data, $key) {
    return openssl_encrypt($data, 'aes-256-cbc', $key, 0, substr(md5($key), 0, 16));
}

function decrypt($data, $key) {
    return openssl_decrypt($data, 'aes-256-cbc', $key, 0, substr(md5($key), 0, 16));
}
?>
