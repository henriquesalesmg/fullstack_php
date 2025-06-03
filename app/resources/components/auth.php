<?php
function generateCsrfToken() {
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

function verifyCsrfToken($token) {
    if (isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token)) {
        return true;
    }
    return false;
}

function redirectWithMessage($url, $message, $type = 'error') {
    $param = $type === 'error' ? 'error' : 'success';
    header("Location: " . $url . "?" . $param . "=" . urlencode($message));
    exit;
}
?>