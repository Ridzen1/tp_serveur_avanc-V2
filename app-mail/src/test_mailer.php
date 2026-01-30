<?php
require_once __DIR__ . '/../vendor/autoload.php';

use MailService\Mailer\MailerFactory;

// Optional: allow override via CLI env vars, e.g. MAILER_DRIVER=null
if (isset($argv[1]) && preg_match('/^MAILER_DRIVER=(.+)$/', $argv[1], $m)) {
    putenv("MAILER_DRIVER={$m[1]}");
}

$mailer = MailerFactory::create();

$to = getenv('TEST_MAIL_TO') ?: 'test@example.com';
$subject = 'Test mail from toubilib';
$body = "This is a test mail. Driver=" . (getenv('MAILER_DRIVER') ?: 'symfony') . "\n";

try {
    $mailer->send($to, $subject, $body);
    echo "Test mail sent (or simulated) to $to\n";
} catch (Throwable $e) {
    echo "Error sending test mail: " . $e->getMessage() . "\n";
    exit(1);
}
