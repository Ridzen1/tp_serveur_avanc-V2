<?php
namespace toubilib\infra\publishers;

use toubilib\core\application\ports\spi\EventPublisherInterface;
use PhpAmqpLib\Connection\AMQPStreamConnection;
use PhpAmqpLib\Message\AMQPMessage;

class RabbitMQEventPublisher implements EventPublisherInterface
{
    private string $host;
    private int $port;
    private string $user;
    private string $pass;
    private string $exchange;

    public function __construct(string $host = 'rabbitmq', int $port = 5672, string $user = 'guest', string $pass = 'guest', string $exchange = 'toubilib.events')
    {
        $this->host = getenv('RABBITMQ_HOST') ?: $host;
        $this->port = intval(getenv('RABBITMQ_PORT') ?: $port);
        $this->user = getenv('RABBITMQ_USER') ?: $user;
        $this->pass = getenv('RABBITMQ_PASS') ?: $pass;
        $this->exchange = $exchange;
    }

    public function publish(string $eventType, array $payload): void
    {
        try {
            $conn = new AMQPStreamConnection($this->host, $this->port, $this->user, $this->pass);
            $ch = $conn->channel();
            // declare exchange (idempotent)
            $ch->exchange_declare($this->exchange, 'topic', false, true, false);

            $messageBody = json_encode([
                'event_type' => $eventType,
                'timestamp' => date('Y-m-d H:i:s'),
                'data' => $payload
            ], JSON_UNESCAPED_SLASHES);

            $msg = new AMQPMessage($messageBody, ['delivery_mode' => AMQPMessage::DELIVERY_MODE_PERSISTENT]);

            // routing key -> rdv.created or rdv.cancelled
            $routingKey = str_replace('.', '', $eventType); // not ideal; prefer pass event like 'rdv.created'
            // We'll accept eventType like 'rdv.created' => use as routing key
            $routingKey = $eventType;

            $ch->basic_publish($msg, $this->exchange, $routingKey);
            $ch->close();
            $conn->close();
        } catch (\Throwable $e) {
            // In production we might log and/or rethrow; for now, echo for visibility and continue
            echo "[RabbitMQEventPublisher] Error publishing event: " . $e->getMessage() . "\n";
        }
    }
}
