<?php
declare(strict_types=1);

namespace WebEngine\Controllers;

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\Views\Twig;
use WebEngine\Models\News;
use WebEngine\Database\Database;

class NewsController
{
    private Twig $view;
    private News $newsModel;

    public function __construct(Twig $view, Database $db)
    {
        $this->view = $view;
        $this->newsModel = new News($db);
    }

    public function index(Request $request, Response $response): Response
    {
        $news = $this->newsModel->getAll(20);
        return $this->view->render($response, 'news/index.twig', ['news' => $news]);
    }

    public function show(Request $request, Response $response, array $args): Response
    {
        $article = $this->newsModel->find((int)$args['id']);

        if (!$article) {
            return $response->withStatus(404);
        }

        return $this->view->render($response, 'news/show.twig', ['article' => $article]);
    }
}
