<?php

function generateVerificationCode() {
    return str_pad(random_int(0, 999999), 6, '0', STR_PAD_LEFT);
}

function registerEmail($email) {
    $file = __DIR__ . '/registered_emails.txt';
    $emails = file($file, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    if (!in_array($email, $emails)) {
        file_put_contents($file, $email . PHP_EOL, FILE_APPEND);
    }
}

function unsubscribeEmail($email) {
    $file = __DIR__ . '/registered_emails.txt';
    $emails = file($file, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    $emails = array_filter($emails, fn($e) => trim($e) !== trim($email));
    file_put_contents($file, implode(PHP_EOL, $emails) . PHP_EOL);
}

function sendVerificationEmail($email, $code, $purpose = 'verify') {
    $subject = $purpose === 'unsubscribe' ? "Confirm Unsubscription" : "Your Verification Code";
    $body = $purpose === 'unsubscribe'
        ? "<p>To confirm unsubscription, use this code: <strong>$code</strong></p>"
        : "<p>Your verification code is: <strong>$code</strong></p>";

    $headers = "From: no-reply@example.com\r\n";
    $headers .= "Content-Type: text/html\r\n";

    mail($email, $subject, $body, $headers);
}

function verifyCode($email, $code, $purpose = 'verify') {
    $file = __DIR__ . "/pending_verifications/{$purpose}_" . md5($email) . ".json";
    if (!file_exists($file)) return false;

    $data = json_decode(file_get_contents($file), true);
    return $data['code'] === $code;
}

function fetchGitHubTimeline() {
    $context = stream_context_create(['http' => ['user_agent' => 'PHP']]);
    $data = @file_get_contents('https://www.github.com/timeline', false, $context);
    return $data ?: '[]';
}

function formatGitHubData($data) {
    $html = "<h2>GitHub Timeline Updates</h2><table border='1'><tr><th>Event</th><th>User</th></tr>";
    $json = json_decode($data, true);

    if (is_array($json)) {
        foreach ($json as $item) {
            $event = htmlspecialchars($item['type'] ?? 'Unknown');
            $user = htmlspecialchars($item['actor']['login'] ?? 'Unknown');
            $html .= "<tr><td>$event</td><td>$user</td></tr>";
        }
    }

    $html .= "</table>";
    return $html;
}

function sendGitHubUpdatesToSubscribers() {
    $file = __DIR__ . '/registered_emails.txt';
    $emails = file($file, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    $data = fetchGitHubTimeline();
    $formatted = formatGitHubData($data);

    foreach ($emails as $email) {
        $unsubscribe_url = "http://yourdomain.com/src/unsubscribe.php?email=" . urlencode($email);
        $message = $formatted . "<p><a href=\"$unsubscribe_url\" id=\"unsubscribe-button\">Unsubscribe</a></p>";

        $headers = "From: no-reply@example.com\r\n";
        $headers .= "Content-Type: text/html\r\n";

        mail($email, "Latest GitHub Updates", $message, $headers);
    }
}
