<?php
declare(strict_types=1);

namespace WebEngine\Middleware;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Psr\Container\ContainerInterface;
use Slim\Psr7\Response;

class CsrfMiddleware implements MiddlewareInterface
{
    private ContainerInterface $container;

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        // Generate CSRF token if not exists
        if (!isset($_SESSION['csrf_token'])) {
            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        }

        // Verify CSRF token for POST, PUT, DELETE requests
        if (in_array($request->getMethod(), ['POST', 'PUT', 'DELETE'])) {
            $body = $request->getParsedBody();
            $token = $body['csrf_token'] ?? '';

            if (!hash_equals($_SESSION['csrf_token'], $token)) {
                $response = new Response();
                $response->getBody()->write('CSRF token validation failed.');
                return $response->withStatus(403);
            }
        }

        return $handler->handle($request);
    }

    public static function getToken(): string
    {
        return $_SESSION['csrf_token'] ?? '';
    }
}
