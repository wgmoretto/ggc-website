<?php
declare(strict_types=1);

namespace WebEngine\Controllers;

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\Views\Twig;
use WebEngine\Models\Account;
use WebEngine\Models\Character;
use WebEngine\Models\Vote;
use WebEngine\Database\Database;

class UserCpController
{
    private Twig $view;
    private Account $accountModel;
    private Character $characterModel;
    private Vote $voteModel;

    public function __construct(Twig $view, Database $db)
    {
        $this->view = $view;
        $this->accountModel = new Account($db);
        $this->characterModel = new Character($db);
        $this->voteModel = new Vote($db);
    }

    public function index(Request $request, Response $response): Response
    {
        $username = $_SESSION['username'];
        $account = $this->accountModel->find($username);
        $characters = $this->accountModel->getCharacters($username);

        return $this->view->render($response, 'usercp/index.twig', [
            'account' => $account,
            'characters' => $characters
        ]);
    }

    public function myAccount(Request $request, Response $response): Response
    {
        $account = $this->accountModel->find($_SESSION['username']);
        return $this->view->render($response, 'usercp/myaccount.twig', ['account' => $account]);
    }

    public function myPassword(Request $request, Response $response): Response
    {
        return $this->view->render($response, 'usercp/mypassword.twig');
    }

    public function updatePassword(Request $request, Response $response): Response
    {
        $data = $request->getParsedBody();
        $username = $_SESSION['username'];

        if (!$this->accountModel->verifyPassword($username, $data['current_password'])) {
            return $this->view->render($response, 'usercp/mypassword.twig', [
                'error' => 'Current password is incorrect'
            ]);
        }

        if ($data['new_password'] !== $data['confirm_password']) {
            return $this->view->render($response, 'usercp/mypassword.twig', [
                'error' => 'Passwords do not match'
            ]);
        }

        $this->accountModel->changePassword($username, $data['new_password']);

        return $this->view->render($response, 'usercp/mypassword.twig', [
            'success' => 'Password changed successfully'
        ]);
    }

    public function addStats(Request $request, Response $response): Response
    {
        $characters = $this->accountModel->getCharacters($_SESSION['username']);
        return $this->view->render($response, 'usercp/addstats.twig', ['characters' => $characters]);
    }

    public function processAddStats(Request $request, Response $response): Response
    {
        $data = $request->getParsedBody();
        $characterName = $data['character'];
        $stats = [
            'Strength' => (int)($data['strength'] ?? 0),
            'Dexterity' => (int)($data['dexterity'] ?? 0),
            'Vitality' => (int)($data['vitality'] ?? 0),
            'Energy' => (int)($data['energy'] ?? 0)
        ];

        if ($this->characterModel->addStats($characterName, $stats)) {
            $characters = $this->accountModel->getCharacters($_SESSION['username']);
            return $this->view->render($response, 'usercp/addstats.twig', [
                'characters' => $characters,
                'success' => 'Stats added successfully'
            ]);
        }

        return $response->withStatus(400);
    }

    public function reset(Request $request, Response $response): Response
    {
        $characters = $this->accountModel->getCharacters($_SESSION['username']);
        return $this->view->render($response, 'usercp/reset.twig', ['characters' => $characters]);
    }

    public function processReset(Request $request, Response $response): Response
    {
        $data = $request->getParsedBody();
        $characterName = $data['character'];

        if ($this->characterModel->reset($characterName)) {
            return $this->view->render($response, 'usercp/reset.twig', [
                'success' => 'Character reset successfully'
            ]);
        }

        return $response->withStatus(400);
    }

    public function vote(Request $request, Response $response): Response
    {
        $sites = $this->voteModel->getSites();
        $votes = $this->voteModel->getUserVotes($_SESSION['username']);

        return $this->view->render($response, 'usercp/vote.twig', [
            'sites' => $sites,
            'votes' => $votes
        ]);
    }

    public function clearPk(Request $request, Response $response): Response
    {
        $characters = $this->accountModel->getCharacters($_SESSION['username']);
        return $this->view->render($response, 'usercp/clearpk.twig', ['characters' => $characters]);
    }

    public function processClearPk(Request $request, Response $response): Response
    {
        $data = $request->getParsedBody();
        $this->characterModel->clearPK($data['character']);
        return $response->withHeader('Location', '/usercp/clearpk')->withStatus(302);
    }

    public function unstick(Request $request, Response $response): Response
    {
        $characters = $this->accountModel->getCharacters($_SESSION['username']);
        return $this->view->render($response, 'usercp/unstick.twig', ['characters' => $characters]);
    }

    public function processUnstick(Request $request, Response $response): Response
    {
        $data = $request->getParsedBody();
        $this->characterModel->unstick($data['character']);
        return $response->withHeader('Location', '/usercp/unstick')->withStatus(302);
    }

    // Implement other UserCP methods...
    public function myEmail(Request $request, Response $response): Response
    {
        $account = $this->accountModel->find($_SESSION['username']);
        return $this->view->render($response, 'usercp/myemail.twig', ['account' => $account]);
    }

    public function updateEmail(Request $request, Response $response): Response
    {
        $data = $request->getParsedBody();
        $this->accountModel->changeEmail($_SESSION['username'], $data['email']);
        return $response->withHeader('Location', '/usercp/myemail')->withStatus(302);
    }

    public function resetStats(Request $request, Response $response): Response
    {
        $characters = $this->accountModel->getCharacters($_SESSION['username']);
        return $this->view->render($response, 'usercp/resetstats.twig', ['characters' => $characters]);
    }

    public function processResetStats(Request $request, Response $response): Response
    {
        $data = $request->getParsedBody();
        $this->characterModel->resetStats($data['character']);
        return $response->withHeader('Location', '/usercp/resetstats')->withStatus(302);
    }

    public function buyZen(Request $request, Response $response): Response
    {
        $characters = $this->accountModel->getCharacters($_SESSION['username']);
        return $this->view->render($response, 'usercp/buyzen.twig', ['characters' => $characters]);
    }

    public function processBuyZen(Request $request, Response $response): Response
    {
        // Implement buy zen logic
        return $response->withHeader('Location', '/usercp/buyzen')->withStatus(302);
    }

    public function clearSkillTree(Request $request, Response $response): Response
    {
        $characters = $this->accountModel->getCharacters($_SESSION['username']);
        return $this->view->render($response, 'usercp/clearskilltree.twig', ['characters' => $characters]);
    }

    public function processClearSkillTree(Request $request, Response $response): Response
    {
        $data = $request->getParsedBody();
        $this->characterModel->clearSkillTree($data['character']);
        return $response->withHeader('Location', '/usercp/clearskilltree')->withStatus(302);
    }

    public function updateAccount(Request $request, Response $response): Response
    {
        // Implement account update logic
        return $response->withHeader('Location', '/usercp/myaccount')->withStatus(302);
    }

    public function processVote(Request $request, Response $response): Response
    {
        $data = $request->getParsedBody();
        $siteId = (int)$data['site_id'];
        $username = $_SESSION['username'];
        $ipAddress = $_SERVER['REMOTE_ADDR'];

        if ($this->voteModel->canVote($username, $siteId)) {
            $this->voteModel->recordVote($username, $siteId, $ipAddress);
        }

        return $response->withHeader('Location', '/usercp/vote')->withStatus(302);
    }
}
