<?php
require_once __DIR__ . '/functions.php';

$messages = [];

// Create directory for pending verifications if not exist
if (!is_dir(__DIR__ . '/pending_verifications')) {
    mkdir(__DIR__ . '/pending_verifications', 0755, true);
}

// Handle email registration
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Register
    if (isset($_POST['email'])) {
        $email = trim($_POST['email']);
        $code = generateVerificationCode();
        $file = __DIR__ . "/pending_verifications/verify_" . md5($email) . ".json";
        file_put_contents($file, json_encode(['email' => $email, 'code' => $code]));
        sendVerificationEmail($email, $code);
        $messages[] = "Verification code sent to your email.";
    }

    // Verify
    if (isset($_POST['verification_code'])) {
        $code = trim($_POST['verification_code']);
        $email = trim($_POST['email'] ?? '');
        if (verifyCode($email, $code, 'verify')) {
            registerEmail($email);
            unlink(__DIR__ . "/pending_verifications/verify_" . md5($email) . ".json");
            $messages[] = "Email verified and registered successfully!";
        } else {
            $messages[] = "Invalid verification code.";
        }
    }

    // Unsubscribe request
    if (isset($_POST['unsubscribe_email'])) {
        $email = trim($_POST['unsubscribe_email']);
        $code = generateVerificationCode();
        $file = __DIR__ . "/pending_verifications/unsubscribe_" . md5($email) . ".json";
        file_put_contents($file, json_encode(['email' => $email, 'code' => $code]));
        sendVerificationEmail($email, $code, 'unsubscribe');
        $messages[] = "Unsubscribe verification code sent to your email.";
    }

    // Unsubscribe verify
    if (isset($_POST['unsubscribe_verification_code'])) {
        $code = trim($_POST['unsubscribe_verification_code']);
        $email = trim($_POST['unsubscribe_email'] ?? '');
        if (verifyCode($email, $code, 'unsubscribe')) {
            unsubscribeEmail($email);
            unlink(__DIR__ . "/pending_verifications/unsubscribe_" . md5($email) . ".json");
            $messages[] = "Successfully unsubscribed.";
        } else {
            $messages[] = "Invalid unsubscription code.";
        }
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Email Verification</title>
</head>
<body>
    <h2>📧 Email Verification & Subscription</h2>
    <?php foreach ($messages as $msg): ?>
        <p><?= htmlspecialchars($msg) ?></p>
    <?php endforeach; ?>

    <!-- Register Form -->
    <form method="post">
        <label>Email:</label>
        <input type="email" name="email" required>
        <button id="submit-email">Submit</button>
    </form>

    <!-- Verification Code -->
    <form method="post">
        <label>Verification Code:</label>
        <input type="text" name="verification_code" maxlength="6" required>
        <input type="hidden" name="email" value="<?= htmlspecialchars($_POST['email'] ?? '') ?>">
        <button id="submit-verification">Verify</button>
    </form>

    <hr>

    <h3>🚫 Unsubscribe</h3>
    <!-- Unsubscribe Request -->
    <form method="post">
        <label>Email to Unsubscribe:</label>
        <input type="email" name="unsubscribe_email" required>
        <button id="submit-unsubscribe">Unsubscribe</button>
    </form>

    <!-- Unsubscribe Verification -->
    <form method="post">
        <label>Unsubscribe Verification Code:</label>
        <input type="text" name="unsubscribe_verification_code">
        <input type="hidden" name="unsubscribe_email" value="<?= htmlspecialchars($_POST['unsubscribe_email'] ?? '') ?>">
        <button id="verify-unsubscribe">Verify</button>
    </form>
</body>
</html>
