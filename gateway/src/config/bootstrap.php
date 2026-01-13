<?php
declare(strict_types=1);

use DI\Container;
use Gateway\Middlewares\CorsMiddleware;
use Slim\Factory\AppFactory;

$settings = require __DIR__ . '/settings.php';
$servicesDefinitions = require __DIR__ . '/services.php';

$container = new Container();

$container->set('settings', $settings);

foreach ($servicesDefinitions as $key => $factory) {
    $container->set($key, $factory);
}

AppFactory::setContainer($container);
$app = AppFactory::create();

$app->add(new CorsMiddleware());
$app->addBodyParsingMiddleware();
$app->addRoutingMiddleware();

$displayErrorDetails = $container->get('settings')['displayErrorDetails'] ?? false;
$app->addErrorMiddleware($displayErrorDetails, true, true)
    ->getDefaultErrorHandler()
    ->forceContentType('application/json');

$app = (require_once __DIR__ . '/routes.php')($app);

return $app;
