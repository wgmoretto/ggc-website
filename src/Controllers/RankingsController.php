<?php
declare(strict_types=1);

namespace WebEngine\Controllers;

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\Views\Twig;
use WebEngine\Models\Rankings;
use WebEngine\Database\Database;
use WebEngine\Services\CacheService;

class RankingsController
{
    private Twig $view;
    private Rankings $rankingsModel;
    private CacheService $cache;

    public function __construct(Twig $view, Database $db, CacheService $cache)
    {
        $this->view = $view;
        $this->rankingsModel = new Rankings($db);
        $this->cache = $cache;
    }

    public function index(Request $request, Response $response): Response
    {
        return $this->view->render($response, 'rankings/index.twig');
    }

    public function level(Request $request, Response $response): Response
    {
        $rankings = $this->cache->remember('rankings_level', 300, function() {
            return $this->rankingsModel->getTopLevel(100);
        });

        return $this->view->render($response, 'rankings/level.twig', ['rankings' => $rankings]);
    }

    public function resets(Request $request, Response $response): Response
    {
        $rankings = $this->cache->remember('rankings_resets', 300, function() {
            return $this->rankingsModel->getTopResets(100);
        });

        return $this->view->render($response, 'rankings/resets.twig', ['rankings' => $rankings]);
    }

    public function grandResets(Request $request, Response $response): Response
    {
        $rankings = $this->cache->remember('rankings_grandresets', 300, function() {
            return $this->rankingsModel->getTopGrandResets(100);
        });

        return $this->view->render($response, 'rankings/grandresets.twig', ['rankings' => $rankings]);
    }

    public function guilds(Request $request, Response $response): Response
    {
        $rankings = $this->cache->remember('rankings_guilds', 300, function() {
            return $this->rankingsModel->getTopGuilds(50);
        });

        return $this->view->render($response, 'rankings/guilds.twig', ['rankings' => $rankings]);
    }

    public function killers(Request $request, Response $response): Response
    {
        $rankings = $this->cache->remember('rankings_killers', 300, function() {
            return $this->rankingsModel->getTopKillers(100);
        });

        return $this->view->render($response, 'rankings/killers.twig', ['rankings' => $rankings]);
    }

    public function online(Request $request, Response $response): Response
    {
        $rankings = $this->rankingsModel->getOnlinePlayers(100);
        return $this->view->render($response, 'rankings/online.twig', ['rankings' => $rankings]);
    }

    public function votes(Request $request, Response $response): Response
    {
        $rankings = $this->cache->remember('rankings_votes', 300, function() {
            return $this->rankingsModel->getTopVoters(100);
        });

        return $this->view->render($response, 'rankings/votes.twig', ['rankings' => $rankings]);
    }

    public function gens(Request $request, Response $response): Response
    {
        $rankings = $this->cache->remember('rankings_gens', 300, function() {
            return $this->rankingsModel->getTopGens(100);
        });

        return $this->view->render($response, 'rankings/gens.twig', ['rankings' => $rankings]);
    }

    public function master(Request $request, Response $response): Response
    {
        $rankings = $this->cache->remember('rankings_master', 300, function() {
            return $this->rankingsModel->getTopMasterLevel(100);
        });

        return $this->view->render($response, 'rankings/master.twig', ['rankings' => $rankings]);
    }
}
