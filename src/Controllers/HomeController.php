<?php
declare(strict_types=1);

namespace WebEngine\Controllers;

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\Views\Twig;
use WebEngine\Models\News;
use WebEngine\Models\Rankings;
use WebEngine\Database\Database;

class HomeController
{
    private Twig $view;
    private Database $db;

    public function __construct(Twig $view, Database $db)
    {
        $this->view = $view;
        $this->db = $db;
    }

    public function index(Request $request, Response $response): Response
    {
        $newsModel = new News($this->db);
        $rankingsModel = new Rankings($this->db);

        $data = [
            'news' => $newsModel->getRecent(5),
            'top_players' => $rankingsModel->getTopLevel(10),
            'online_count' => $this->getOnlineCount(),
            'total_accounts' => $this->getTotalAccounts(),
            'total_characters' => $this->getTotalCharacters(),
            'user' => $_SESSION['username'] ?? null
        ];

        return $this->view->render($response, 'home.twig', $data);
    }

    private function getOnlineCount(): int
    {
        $sql = "SELECT COUNT(*) as count FROM MEMB_STAT WHERE ConnectStat = 1";
        $result = $this->db->fetch($sql);
        return (int)($result['count'] ?? 0);
    }

    private function getTotalAccounts(): int
    {
        $sql = "SELECT COUNT(*) as count FROM MEMB_INFO";
        $result = $this->db->fetch($sql);
        return (int)($result['count'] ?? 0);
    }

    private function getTotalCharacters(): int
    {
        $sql = "SELECT COUNT(*) as count FROM Character";
        $result = $this->db->fetch($sql);
        return (int)($result['count'] ?? 0);
    }
}
