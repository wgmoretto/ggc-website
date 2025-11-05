<?php
declare(strict_types=1);

use Slim\Views\Twig;
use Psr\Container\ContainerInterface;
use WebEngine\Database\Database;
use WebEngine\Services\AuthService;
use WebEngine\Services\CacheService;
use WebEngine\Services\EmailService;
use WebEngine\Services\PluginManager;
use Monolog\Logger;
use Monolog\Handler\StreamHandler;

// Twig
$container->set('view', function (ContainerInterface $c) {
    $twig = Twig::create(__DIR__ . '/../templates', [
        'cache' => $_ENV['APP_DEBUG'] === 'true' ? false : __DIR__ . '/../storage/cache/twig',
        'debug' => $_ENV['APP_DEBUG'] === 'true'
    ]);

    // Add globals
    $environment = $twig->getEnvironment();
    $environment->addGlobal('app_name', $_ENV['APP_NAME']);
    $environment->addGlobal('app_url', $_ENV['APP_URL']);
    $environment->addGlobal('server_name', $_ENV['SERVER_NAME']);
    $environment->addGlobal('user', $_SESSION['username'] ?? null);
    $environment->addGlobal('session', $_SESSION ?? []);

    // Add CSRF token function
    $environment->addFunction(new \Twig\TwigFunction('csrf_token', function () {
        return $_SESSION['csrf_token'] ?? '';
    }));

    // Add env function
    $environment->addFunction(new \Twig\TwigFunction('env', function ($key, $default = null) {
        return $_ENV[$key] ?? $default;
    }));

    return $twig;
});

// Database
$container->set(Database::class, function (ContainerInterface $c) {
    return new Database([
        'driver' => $_ENV['DB_CONNECTION'],
        'host' => $_ENV['DB_HOST'],
        'port' => $_ENV['DB_PORT'],
        'database' => $_ENV['DB_DATABASE'],
        'username' => $_ENV['DB_USERNAME'],
        'password' => $_ENV['DB_PASSWORD'],
    ]);
});

// Logger
$container->set(Logger::class, function (ContainerInterface $c) {
    $logger = new Logger('webengine');
    $logger->pushHandler(new StreamHandler(__DIR__ . '/../storage/logs/app.log', Logger::DEBUG));
    return $logger;
});

// Cache Service
$container->set(CacheService::class, function (ContainerInterface $c) {
    return new CacheService($_ENV['CACHE_PATH']);
});

// Auth Service
$container->set(AuthService::class, function (ContainerInterface $c) {
    return new AuthService($c->get(Database::class));
});

// Email Service
$container->set(EmailService::class, function (ContainerInterface $c) {
    return new EmailService([
        'host' => $_ENV['MAIL_HOST'],
        'port' => (int)$_ENV['MAIL_PORT'],
        'username' => $_ENV['MAIL_USERNAME'],
        'password' => $_ENV['MAIL_PASSWORD'],
        'encryption' => $_ENV['MAIL_ENCRYPTION'],
        'from_address' => $_ENV['MAIL_FROM_ADDRESS'],
        'from_name' => $_ENV['MAIL_FROM_NAME'],
    ]);
});

// Plugin Manager
$container->set(PluginManager::class, function (ContainerInterface $c) {
    return new PluginManager($c->get(Database::class));
});
