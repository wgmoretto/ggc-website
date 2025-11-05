<?php
declare(strict_types=1);

namespace WebEngine\Controllers;

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\Views\Twig;
use WebEngine\Models\Character;
use WebEngine\Models\Guild;
use WebEngine\Database\Database;

class ProfileController
{
    private Twig $view;
    private Character $characterModel;
    private Guild $guildModel;

    public function __construct(Twig $view, Database $db)
    {
        $this->view = $view;
        $this->characterModel = new Character($db);
        $this->guildModel = new Guild($db);
    }

    public function player(Request $request, Response $response, array $args): Response
    {
        $character = $this->characterModel->find($args['name']);

        if (!$character) {
            return $response->withStatus(404);
        }

        $guild = $this->characterModel->getGuild($args['name']);

        return $this->view->render($response, 'profile/player.twig', [
            'character' => $character,
            'guild' => $guild
        ]);
    }

    public function guild(Request $request, Response $response, array $args): Response
    {
        $guild = $this->guildModel->find($args['name']);

        if (!$guild) {
            return $response->withStatus(404);
        }

        return $this->view->render($response, 'profile/guild.twig', ['guild' => $guild]);
    }
}
