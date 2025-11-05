<?php
declare(strict_types=1);

namespace WebEngine\Middleware;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Psr\Container\ContainerInterface;
use Slim\Psr7\Response;

class AdminMiddleware implements MiddlewareInterface
{
    private ContainerInterface $container;

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        if (!isset($_SESSION['is_admin']) || $_SESSION['is_admin'] !== true) {
            $response = new Response();
            $response->getBody()->write('Access denied. Admin privileges required.');
            return $response->withStatus(403);
        }

        return $handler->handle($request);
    }
}
