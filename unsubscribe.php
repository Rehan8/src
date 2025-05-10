<?php
require_once __DIR__ . '/functions.php';

if (!isset($_GET['email'])) {
    echo "<p>Missing email parameter.</p>";
    exit;
}

$email = trim($_GET['email']);

// Create the pending_verifications directory if not exists
$pendingDir = __DIR__ . '/pending_verifications';
if (!is_dir($pendingDir)) {
    mkdir($pendingDir, 0755, true);
}

$code = generateVerificationCode();
$file = $pendingDir . "/unsubscribe_" . md5($email) . ".json";

file_put_contents($file, json_encode(['email' => $email, 'code' => $code]));
sendVerificationEmail($email, $code, 'unsubscribe');

echo "<p>An unsubscribe verification code has been sent to your email. Please enter it on the website to complete the process.</p>";
?>
  