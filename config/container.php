<?php
declare(strict_types=1);

use Slim\Views\Twig;
use Twig\Loader\FilesystemLoader;
use Twig\Loader\LoaderInterface;
use Psr\Container\ContainerInterface;
use WebEngine\Database\Database;
use WebEngine\Services\AuthService;
use WebEngine\Services\CacheService;
use WebEngine\Services\EmailService;
use WebEngine\Services\PluginManager;
use Monolog\Logger;
use Monolog\Handler\StreamHandler;

// Twig Loader
$container->set(LoaderInterface::class, function (ContainerInterface $c) {
    return new FilesystemLoader(__DIR__ . '/../templates');
});

// Twig (both as class and as 'view' alias)
$container->set(Twig::class, function (ContainerInterface $c) {
    $twig = Twig::create(__DIR__ . '/../templates', [
        'cache' => $_ENV['APP_DEBUG'] === 'true' ? false : __DIR__ . '/../storage/cache/twig',
        'debug' => $_ENV['APP_DEBUG'] === 'true'
    ]);

    // Add globals
    $environment = $twig->getEnvironment();
    $environment->addGlobal('app_name', $_ENV['APP_NAME'] ?? 'WebEngine CMS');
    $environment->addGlobal('app_url', $_ENV['APP_URL'] ?? 'http://localhost');
    $environment->addGlobal('server_name', $_ENV['SERVER_NAME'] ?? 'My MU Server');
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

// Alias for backward compatibility
$container->set('view', function (ContainerInterface $c) {
    return $c->get(Twig::class);
});

// Database
$container->set(Database::class, function (ContainerInterface $c) {
    return new Database([
        'driver' => $_ENV['DB_CONNECTION'] ?? 'sqlsrv',
        'host' => $_ENV['DB_HOST'] ?? 'localhost',
        'port' => $_ENV['DB_PORT'] ?? '1433',
        'database' => $_ENV['DB_DATABASE'] ?? 'MuOnline',
        'username' => $_ENV['DB_USERNAME'] ?? 'sa',
        'password' => $_ENV['DB_PASSWORD'] ?? '',
    ]);
});

// Alias for Database (both class and WebEngine\Database\Database)
$container->set(\WebEngine\Database\Database::class, function (ContainerInterface $c) {
    return $c->get(Database::class);
});

// Logger
$container->set(Logger::class, function (ContainerInterface $c) {
    $logger = new Logger('webengine');
    $logPath = __DIR__ . '/../storage/logs';

    // Criar diretório de logs se não existir
    if (!is_dir($logPath)) {
        @mkdir($logPath, 0775, true);
    }

    $logger->pushHandler(new StreamHandler($logPath . '/app.log', Logger::DEBUG));
    return $logger;
});

// Cache Service
$container->set(CacheService::class, function (ContainerInterface $c) {
    $cachePath = $_ENV['CACHE_PATH'] ?? __DIR__ . '/../storage/cache';

    // Criar diretório de cache se não existir
    if (!is_dir($cachePath)) {
        @mkdir($cachePath, 0775, true);
    }

    return new CacheService($cachePath);
});

// Auth Service
$container->set(AuthService::class, function (ContainerInterface $c) {
    return new AuthService($c->get(Database::class));
});

// Email Service
$container->set(EmailService::class, function (ContainerInterface $c) {
    return new EmailService([
        'host' => $_ENV['MAIL_HOST'] ?? 'smtp.gmail.com',
        'port' => (int)($_ENV['MAIL_PORT'] ?? 587),
        'username' => $_ENV['MAIL_USERNAME'] ?? '',
        'password' => $_ENV['MAIL_PASSWORD'] ?? '',
        'encryption' => $_ENV['MAIL_ENCRYPTION'] ?? 'tls',
        'from_address' => $_ENV['MAIL_FROM_ADDRESS'] ?? 'noreply@muserver.com',
        'from_name' => $_ENV['MAIL_FROM_NAME'] ?? $_ENV['APP_NAME'] ?? 'WebEngine CMS',
    ]);
});

// Plugin Manager
$container->set(PluginManager::class, function (ContainerInterface $c) {
    return new PluginManager($c->get(Database::class));
});
