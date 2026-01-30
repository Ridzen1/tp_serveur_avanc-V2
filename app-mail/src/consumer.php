<?php
require_once __DIR__ . '/../vendor/autoload.php';

use PhpAmqpLib\Connection\AMQPStreamConnection;
use PhpAmqpLib\Message\AMQPMessage;
use MailService\Mailer\SymfonyMailerAdapter;

echo "=== Mail consumer for 'mail.notifications' queue ===\n";

// Configure connection
$rabbitHost = getenv('RABBITMQ_HOST') ?: 'rabbitmq';
$rabbitPort = getenv('RABBITMQ_PORT') ?: 5672;
$rabbitUser = getenv('RABBITMQ_USER') ?: 'guest';
$rabbitPass = getenv('RABBITMQ_PASS') ?: 'guest';

$connection = new AMQPStreamConnection($rabbitHost, $rabbitPort, $rabbitUser, $rabbitPass);
$channel = $connection->channel();

// Ensure exchange/queue exist (idempotent)
$exchange = 'toubilib.events';
$queue = 'mail.notifications';
$routingKeyPattern = 'rdv.*';

$channel->exchange_declare($exchange, 'topic', false, true, false);
$channel->queue_declare($queue, false, true, false, false);
$channel->queue_bind($queue, $exchange, 'rdv.*');

$channel->basic_qos(null, 1, null);

$mailer = new SymfonyMailerAdapter();

$callback = function (AMQPMessage $msg) use ($mailer) {
    echo "\n[" . date('Y-m-d H:i:s') . "] Received message\n";
    $body = $msg->getBody();
    echo "Payload: " . $body . "\n";

    $payload = json_decode($body, true);
    if (!$payload) {
        echo "Invalid JSON, ack and skip\n";
        $msg->ack();
        return;
    }

    $eventType = $payload['event_type'] ?? 'unknown';
    $data = $payload['data'] ?? [];

    $subject = "Notification: $eventType";
    $text = "Événement: $eventType\nDonnées: " . json_encode($data, JSON_PRETTY_PRINT);

    // Send to praticien if present
    if (!empty($data['praticien_email'])) {
        try {
            $mailer->send($data['praticien_email'], $subject, $text);
            echo "Sent mail to praticien: {$data['praticien_email']}\n";
        } catch (\Throwable $e) {
            echo "Error sending to praticien: " . $e->getMessage() . "\n";
        }
    }

    // Send to patient if present
    if (!empty($data['patient_email'])) {
        try {
            $mailer->send($data['patient_email'], $subject, $text);
            echo "Sent mail to patient: {$data['patient_email']}\n";
        } catch (\Throwable $e) {
            echo "Error sending to patient: " . $e->getMessage() . "\n";
        }
    }

    // ack message
    $msg->ack();
};

$channel->basic_consume($queue, '', false, false, false, false, $callback);

echo "Waiting for messages on queue '$queue'...\nPress CTRL+C to exit.\n";

while ($channel->is_consuming()) {
    try {
        $channel->wait();
    } catch (\Exception $e) {
        echo "Consumer error: " . $e->getMessage() . "\n";
        break;
    }
}

$channel->close();
$connection->close();
