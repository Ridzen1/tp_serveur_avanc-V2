<?php

use DI\Container;
use Slim\Factory\AppFactory;
use Dotenv\Dotenv;

$dotenv = Dotenv::createImmutable(__DIR__);
if (file_exists(__DIR__ . '/.env')) {
    $dotenv->load();
}

$settings = require __DIR__ . '/settings.php';
$services = require __DIR__ . '/services.php';
$api = require __DIR__ . '/api.php';

$container = new Container();

$container->set('settings', $settings);

foreach ($services as $key => $factory) {
    $container->set($key, $factory);
}

foreach ($api as $key => $factory) {
    $container->set($key, $factory);
}

AppFactory::setContainer($container);
$app = AppFactory::create();

$app->addBodyParsingMiddleware();
$app->addRoutingMiddleware();

$displayErrorDetails = $container->get('settings')['displayErrorDetails'] ?? false;
$app->addErrorMiddleware($displayErrorDetails, true, true)
    ->getDefaultErrorHandler()
    ->forceContentType('application/json')
;

$app = (require_once __DIR__ . '/../src/api/routes.php')($app);

return $app;
