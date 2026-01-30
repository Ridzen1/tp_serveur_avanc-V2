<?php
namespace toubilib\infra\publishers;

use toubilib\core\application\ports\spi\EventPublisherInterface;

class NullEventPublisher implements EventPublisherInterface
{
    public function publish(string $eventType, array $payload): void
    {
        // no-op - useful for tests or when broker unavailable
        // log to stdout optionally
        echo "[NullEventPublisher] event={$eventType} payload=" . json_encode($payload) . "\n";
    }
}
