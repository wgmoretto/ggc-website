<?php
declare(strict_types=1);

namespace WebEngine\Controllers;

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\Views\Twig;
use WebEngine\Models\Donation;
use WebEngine\Database\Database;

class DonationController
{
    private Twig $view;
    private Donation $donationModel;

    public function __construct(Twig $view, Database $db)
    {
        $this->view = $view;
        $this->donationModel = new Donation($db);
    }

    public function index(Request $request, Response $response): Response
    {
        $packages = $this->donationModel->getPackages();
        return $this->view->render($response, 'donation/index.twig', ['packages' => $packages]);
    }

    public function process(Request $request, Response $response): Response
    {
        $data = $request->getParsedBody();
        $packageId = (int)$data['package_id'];
        $package = $this->donationModel->getPackage($packageId);

        if (!$package) {
            return $response->withStatus(404);
        }

        // Process PayPal payment
        // Return PayPal redirect URL
        return $response->withStatus(200);
    }
}
