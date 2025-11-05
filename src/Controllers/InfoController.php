<?php
declare(strict_types=1);

namespace WebEngine\Controllers;

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\Views\Twig;
use WebEngine\Services\EmailService;

class InfoController
{
    private Twig $view;
    private EmailService $email;

    public function __construct(Twig $view, EmailService $email)
    {
        $this->view = $view;
        $this->email = $email;
    }

    public function index(Request $request, Response $response): Response
    {
        return $this->view->render($response, 'info/index.twig');
    }

    public function privacy(Request $request, Response $response): Response
    {
        return $this->view->render($response, 'info/privacy.twig');
    }

    public function tos(Request $request, Response $response): Response
    {
        return $this->view->render($response, 'info/tos.twig');
    }

    public function refunds(Request $request, Response $response): Response
    {
        return $this->view->render($response, 'info/refunds.twig');
    }

    public function contact(Request $request, Response $response): Response
    {
        return $this->view->render($response, 'info/contact.twig');
    }

    public function sendContact(Request $request, Response $response): Response
    {
        $data = $request->getParsedBody();
        $this->email->sendContactFormEmail($data['name'], $data['email'], $data['message']);

        return $this->view->render($response, 'info/contact.twig', [
            'success' => 'Message sent successfully'
        ]);
    }
}
