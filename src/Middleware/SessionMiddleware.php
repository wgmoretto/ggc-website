<?php
declare(strict_types=1);

namespace WebEngine\Middleware;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

class SessionMiddleware implements MiddlewareInterface
{
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_name($_ENV['SESSION_NAME'] ?? 'WebEngine_Slim');
            session_start([
                'cookie_httponly' => true,
                'cookie_secure' => $_ENV['APP_ENV'] === 'production',
                'cookie_samesite' => 'Lax',
                'gc_maxlifetime' => (int)($_ENV['SESSION_LIFETIME'] ?? 7200)
            ]);
        }

        return $handler->handle($request);
    }
}
