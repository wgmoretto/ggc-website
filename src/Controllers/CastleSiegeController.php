<?php
declare(strict_types=1);

namespace WebEngine\Controllers;

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\Views\Twig;
use WebEngine\Models\CastleSiege;
use WebEngine\Database\Database;

class CastleSiegeController
{
    private Twig $view;
    private CastleSiege $castleSiegeModel;

    public function __construct(Twig $view, Database $db)
    {
        $this->view = $view;
        $this->castleSiegeModel = new CastleSiege($db);
    }

    public function index(Request $request, Response $response): Response
    {
        $owner = $this->castleSiegeModel->getOwner();
        $registeredGuilds = $this->castleSiegeModel->getRegisteredGuilds();
        $schedule = $this->castleSiegeModel->getSchedule();

        return $this->view->render($response, 'castlesiege/index.twig', [
            'owner' => $owner,
            'registered_guilds' => $registeredGuilds,
            'schedule' => $schedule
        ]);
    }
}
