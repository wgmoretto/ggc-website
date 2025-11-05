<?php
declare(strict_types=1);

use DI\Container;
use Slim\Factory\AppFactory;
use Slim\Views\Twig;
use Slim\Views\TwigMiddleware;

require __DIR__ . '/../vendor/autoload.php';

// Load environment variables
$dotenv = Dotenv\Dotenv::createImmutable(__DIR__ . '/..');
$dotenv->load();

// Create Container
$container = new Container();
AppFactory::setContainer($container);

// Create App
$app = AppFactory::create();

// Add Routing Middleware
$app->addRoutingMiddleware();

// Add Error Middleware
$errorMiddleware = $app->addErrorMiddleware(
    $_ENV['APP_DEBUG'] === 'true',
    true,
    true
);

// Session Middleware
$app->add(new \WebEngine\Middleware\SessionMiddleware());

// CSRF Protection
$app->add(new \WebEngine\Middleware\CsrfMiddleware($container));

// Configure Container
require __DIR__ . '/../config/container.php';

// Load Routes
require __DIR__ . '/../config/routes.php';

$app->run();
