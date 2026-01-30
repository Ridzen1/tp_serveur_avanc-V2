<?php
namespace toubilib\core\application\ports\spi;

interface EventPublisherInterface
{
    /**
     * Publish an event with a type and payload
     * @param string $eventType
     * @param array $payload
     * @return void
     */
    public function publish(string $eventType, array $payload): void;
}
