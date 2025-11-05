<?php
declare(strict_types=1);

use DI\Container;
use Slim\Factory\AppFactory;
use Slim\Views\Twig;
use Slim\Views\TwigMiddleware;

require __DIR__ . '/../vendor/autoload.php';

// Check if .env file exists, if not redirect to installer
$envPath = __DIR__ . '/../.env';
$requestUri = $_SERVER['REQUEST_URI'] ?? '';

if (!file_exists($envPath)) {
    // Avoid redirect loop - don't redirect if already in install path
    if (strpos($requestUri, '/install') === false) {
        // Check if installer exists
        if (file_exists(__DIR__ . '/install/index.php')) {
            // Redirect to installer
            header('Location: /install/');
            exit;
        }
    } else {
        // We're in install path but index.php was called, exit gracefully
        exit;
    }

    // If no installer and not in install path, show error
    if (strpos($requestUri, '/install') === false) {
        die('
        <html>
        <head>
            <title>Configuration Required</title>
            <style>
                body { font-family: Arial, sans-serif; background: #f5f5f5; padding: 50px; }
                .container { max-width: 600px; margin: 0 auto; background: white; padding: 30px; border-radius: 8px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); }
                h1 { color: #e74c3c; }
                code { background: #f8f9fa; padding: 2px 6px; border-radius: 3px; }
            </style>
        </head>
        <body>
            <div class="container">
                <h1>⚠️ Configuration Required</h1>
                <p>The <code>.env</code> file is missing. Please create it from <code>.env.example</code>:</p>
                <pre>cp .env.example .env</pre>
                <p>Then edit the <code>.env</code> file with your database credentials and settings.</p>
            </div>
        </body>
        </html>
        ');
    }
}

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
