<?php
/**
 * Script de test pour l'envoi de messages à RabbitMQ
 * 
 * Exécution: docker-compose exec api.rdv php test/test-rabbitmq.php
 */

require_once __DIR__ . '/../vendor/autoload.php';

use PhpAmqpLib\Connection\AMQPStreamConnection;
use PhpAmqpLib\Message\AMQPMessage;

echo "=== Test d'envoi de message à RabbitMQ ===\n\n";

try {
    // Connexion à RabbitMQ
    echo "1. Connexion à RabbitMQ...\n";
    $connection = new AMQPStreamConnection(
        'rabbitmq',  
        5672,
        'guest',
        'guest'
    );
    $channel = $connection->channel();
    echo "Connecté\n\n";

    // Déclaration de l'exchange 
    echo "2. Déclaration de l'exchange 'toubilib.events' (type: topic)...\n";
    $channel->exchange_declare(
        'toubilib.events', 
        'topic',
        false,
        true,
        false
    );
    echo "Exchange déclaré\n\n";

    // Déclaration de la queue
    echo "3. Déclaration de la queue 'mail.notifications'...\n";
    $channel->queue_declare(
        'mail.notifications',
        false,
        true,
        false,
        false
    );
    echo "Queue déclarée\n\n";

    // Binding queue -> exchange
    echo "4. Binding queue 'mail.notifications' à l'exchange avec routing key 'rdv.*'...\n";
    $channel->queue_bind(
        'mail.notifications',
        'toubilib.events',
        'rdv.*'
    );
    echo "Binding créé\n\n";

    // Préparation du message de test
    $testMessage = [
        'event_type' => 'rdv.created',
        'timestamp' => date('Y-m-d H:i:s'),
        'data' => [
            'rdv_id' => 'test-' . uniqid(),
            'praticien_email' => 'dr.test@example.com',
            'patient_email' => 'patient.test@example.com',
            'date_rdv' => '2026-02-15 14:30:00',
            'motif' => 'Consultation test'
        ]
    ];

    $messageBody = json_encode($testMessage, JSON_PRETTY_PRINT);
    
    echo "5. Envoi du message de test:\n";
    echo "   Message: " . $messageBody . "\n\n";

    $msg = new AMQPMessage(
        $messageBody,
        ['delivery_mode' => AMQPMessage::DELIVERY_MODE_PERSISTENT]
    );

    $channel->basic_publish(
        $msg,
        'toubilib.events',
        'rdv.created'
    );
    
    echo "Message envoyé avec routing key 'rdv.created'\n\n";

    $channel->close();
    $connection->close();

    echo "Test réussi !\n";
    echo "\nVérifie dans l'interface RabbitMQ (http://localhost:15672):\n";
    echo "  - Exchange: toubilib.events\n";
    echo "  - Queue: mail.notifications (devrait avoir 1 message)\n";
    echo "  - Bindings: rdv.* entre exchange et queue\n";

} catch (Exception $e) {
    echo "Erreur: " . $e->getMessage() . "\n";
    echo "   Trace: " . $e->getTraceAsString() . "\n";
    exit(1);
}
