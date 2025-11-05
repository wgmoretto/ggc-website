<?php
declare(strict_types=1);

namespace WebEngine\Controllers;

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use WebEngine\Models\CastleSiege;
use WebEngine\Database\Database;

class ApiController
{
    private Database $db;
    private CastleSiege $castleSiegeModel;

    public function __construct(Database $db)
    {
        $this->db = $db;
        $this->castleSiegeModel = new CastleSiege($db);
    }

    public function serverTime(Request $request, Response $response): Response
    {
        $data = [
            'server_time' => date('Y-m-d H:i:s'),
            'timezone' => $_ENV['SERVER_TIMEZONE'] ?? 'UTC'
        ];

        $response->getBody()->write(json_encode($data));
        return $response->withHeader('Content-Type', 'application/json');
    }

    public function events(Request $request, Response $response): Response
    {
        // Return events schedule
        $events = [
            ['name' => 'Blood Castle', 'time' => '00:00'],
            ['name' => 'Devil Square', 'time' => '02:00'],
            ['name' => 'Castle Siege', 'time' => '20:00']
        ];

        $response->getBody()->write(json_encode($events));
        return $response->withHeader('Content-Type', 'application/json');
    }

    public function castleSiege(Request $request, Response $response): Response
    {
        $owner = $this->castleSiegeModel->getOwner();
        $registeredGuilds = $this->castleSiegeModel->getRegisteredGuilds();

        $data = [
            'owner' => $owner,
            'registered_guilds' => $registeredGuilds
        ];

        $response->getBody()->write(json_encode($data));
        return $response->withHeader('Content-Type', 'application/json');
    }

    public function guildMark(Request $request, Response $response, array $args): Response
    {
        // Return guild mark image
        return $response->withStatus(200);
    }

    public function version(Request $request, Response $response): Response
    {
        $data = ['version' => '2.0.0', 'name' => 'WebEngine Slim'];
        $response->getBody()->write(json_encode($data));
        return $response->withHeader('Content-Type', 'application/json');
    }

    public function cron(Request $request, Response $response): Response
    {
        // Execute cron jobs
        $response->getBody()->write(json_encode(['status' => 'success']));
        return $response->withHeader('Content-Type', 'application/json');
    }

    public function paypalIpn(Request $request, Response $response): Response
    {
        // Handle PayPal IPN
        $response->getBody()->write(json_encode(['status' => 'received']));
        return $response->withHeader('Content-Type', 'application/json');
    }
}
