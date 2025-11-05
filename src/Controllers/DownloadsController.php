<?php
declare(strict_types=1);

namespace WebEngine\Controllers;

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\Views\Twig;
use WebEngine\Database\Database;

class DownloadsController
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
        $sql = "SELECT * FROM GGC_DOWNLOADS WHERE active = 1 ORDER BY sort_order ASC";
        $downloads = $this->db->fetchAll($sql);

        return $this->view->render($response, 'downloads/index.twig', ['downloads' => $downloads]);
    }
}
