<?php
declare(strict_types=1);

namespace WebEngine\Controllers;

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\Views\Twig;
use WebEngine\Models\Account;
use WebEngine\Models\Character;
use WebEngine\Models\News;
use WebEngine\Models\Donation;
use WebEngine\Services\CacheService;
use WebEngine\Database\Database;

class AdminController
{
    private Twig $view;
    private Database $db;
    private Account $accountModel;
    private Character $characterModel;
    private News $newsModel;
    private Donation $donationModel;
    private CacheService $cache;

    public function __construct(Twig $view, Database $db, CacheService $cache)
    {
        $this->view = $view;
        $this->db = $db;
        $this->accountModel = new Account($db);
        $this->characterModel = new Character($db);
        $this->newsModel = new News($db);
        $this->donationModel = new Donation($db);
        $this->cache = $cache;
    }

    public function dashboard(Request $request, Response $response): Response
    {
        $data = [
            'total_accounts' => $this->getTotalAccounts(),
            'total_characters' => $this->getTotalCharacters(),
            'online_count' => $this->getOnlineCount(),
            'recent_registrations' => $this->accountModel->getRecentRegistrations(7)
        ];

        return $this->view->render($response, 'admin/dashboard.twig', $data);
    }

    public function accounts(Request $request, Response $response): Response
    {
        $accounts = $this->accountModel->getAllAccounts(100);
        return $this->view->render($response, 'admin/accounts.twig', ['accounts' => $accounts]);
    }

    public function accountInfo(Request $request, Response $response, array $args): Response
    {
        $account = $this->accountModel->find($args['id']);
        $characters = $this->accountModel->getCharacters($args['id']);

        return $this->view->render($response, 'admin/account-info.twig', [
            'account' => $account,
            'characters' => $characters
        ]);
    }

    public function editAccount(Request $request, Response $response, array $args): Response
    {
        $data = $request->getParsedBody();
        $this->accountModel->update($args['id'], $data);
        return $response->withHeader('Location', "/admincp/account/{$args['id']}")->withStatus(302);
    }

    public function banAccount(Request $request, Response $response, array $args): Response
    {
        $data = $request->getParsedBody();
        $this->accountModel->ban($args['id'], $data['reason'], (int)($data['duration'] ?? 0));
        return $response->withHeader('Location', "/admincp/account/{$args['id']}")->withStatus(302);
    }

    public function unbanAccount(Request $request, Response $response, array $args): Response
    {
        $this->accountModel->unban($args['id']);
        return $response->withHeader('Location', "/admincp/account/{$args['id']}")->withStatus(302);
    }

    public function manageNews(Request $request, Response $response): Response
    {
        $news = $this->newsModel->getAll(50);
        return $this->view->render($response, 'admin/news/manage.twig', ['news' => $news]);
    }

    public function addNews(Request $request, Response $response): Response
    {
        return $this->view->render($response, 'admin/news/add.twig');
    }

    public function saveNews(Request $request, Response $response): Response
    {
        $data = $request->getParsedBody();
        $data['author'] = $_SESSION['username'];
        $this->newsModel->create($data);
        return $response->withHeader('Location', '/admincp/news')->withStatus(302);
    }

    public function editNews(Request $request, Response $response, array $args): Response
    {
        $news = $this->newsModel->find((int)$args['id']);
        return $this->view->render($response, 'admin/news/edit.twig', ['news' => $news]);
    }

    public function updateNews(Request $request, Response $response, array $args): Response
    {
        $data = $request->getParsedBody();
        $this->newsModel->update((int)$args['id'], $data);
        return $response->withHeader('Location', '/admincp/news')->withStatus(302);
    }

    public function deleteNews(Request $request, Response $response, array $args): Response
    {
        $this->newsModel->delete((int)$args['id']);
        return $response->withHeader('Location', '/admincp/news')->withStatus(302);
    }

    public function characterInfo(Request $request, Response $response, array $args): Response
    {
        $character = $this->characterModel->find($args['name']);
        return $this->view->render($response, 'admin/character-info.twig', ['character' => $character]);
    }

    public function editCharacter(Request $request, Response $response, array $args): Response
    {
        $data = $request->getParsedBody();
        $this->characterModel->update($args['name'], $data);
        return $response->withHeader('Location', "/admincp/character/{$args['name']}")->withStatus(302);
    }

    public function settings(Request $request, Response $response): Response
    {
        return $this->view->render($response, 'admin/settings.twig');
    }

    public function updateSettings(Request $request, Response $response): Response
    {
        // Update settings logic
        return $response->withHeader('Location', '/admincp/settings')->withStatus(302);
    }

    public function creditsConfig(Request $request, Response $response): Response
    {
        $packages = $this->donationModel->getPackages();
        return $this->view->render($response, 'admin/credits.twig', ['packages' => $packages]);
    }

    public function updateCredits(Request $request, Response $response): Response
    {
        // Update credits config logic
        return $response->withHeader('Location', '/admincp/credits')->withStatus(302);
    }

    public function cacheManager(Request $request, Response $response): Response
    {
        return $this->view->render($response, 'admin/cache.twig');
    }

    public function clearCache(Request $request, Response $response): Response
    {
        $this->cache->clear();
        return $response->withHeader('Location', '/admincp/cache')->withStatus(302);
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

    private function getOnlineCount(): int
    {
        $sql = "SELECT COUNT(*) as count FROM MEMB_STAT WHERE ConnectStat = 1";
        $result = $this->db->fetch($sql);
        return (int)($result['count'] ?? 0);
    }

    // Implement other admin methods...
    public function castleSiegeConfig(Request $request, Response $response): Response
    {
        return $this->view->render($response, 'admin/castlesiege.twig');
    }

    public function updateCastleSiege(Request $request, Response $response): Response
    {
        return $response->withHeader('Location', '/admincp/castlesiege/config')->withStatus(302);
    }

    public function latestBans(Request $request, Response $response): Response
    {
        $sql = "SELECT TOP (50) * FROM GGC_BANS ORDER BY banned_at DESC";
        $bans = $this->db->fetchAll($sql);
        return $this->view->render($response, 'admin/monitoring/bans.twig', ['bans' => $bans]);
    }

    public function newRegistrations(Request $request, Response $response): Response
    {
        $registrations = $this->accountModel->getRecentRegistrations(30);
        return $this->view->render($response, 'admin/monitoring/registrations.twig', ['registrations' => $registrations]);
    }

    public function onlineAccounts(Request $request, Response $response): Response
    {
        $sql = "SELECT m.*, ms.ConnectStat FROM MEMB_INFO m
                INNER JOIN MEMB_STAT ms ON m.memb___id = ms.memb___id
                WHERE ms.ConnectStat = 1";
        $accounts = $this->db->fetchAll($sql);
        return $this->view->render($response, 'admin/monitoring/online.twig', ['accounts' => $accounts]);
    }

    public function paypalTransactions(Request $request, Response $response): Response
    {
        $transactions = $this->donationModel->getAllTransactions(100);
        return $this->view->render($response, 'admin/monitoring/paypal.twig', ['transactions' => $transactions]);
    }

    public function cronManager(Request $request, Response $response): Response
    {
        $sql = "SELECT * FROM GGC_CRON ORDER BY last_run DESC";
        $crons = $this->db->fetchAll($sql);
        return $this->view->render($response, 'admin/cron.twig', ['crons' => $crons]);
    }

    public function blockedIps(Request $request, Response $response): Response
    {
        $sql = "SELECT * FROM GGC_BLOCKED_IP ORDER BY created_at DESC";
        $ips = $this->db->fetchAll($sql);
        return $this->view->render($response, 'admin/blocked-ips.twig', ['ips' => $ips]);
    }

    public function addBlockedIp(Request $request, Response $response): Response
    {
        $data = $request->getParsedBody();
        $sql = "INSERT INTO GGC_BLOCKED_IP (ip_address, reason, created_at) VALUES (?, ?, GETDATE())";
        $this->db->execute($sql, [$data['ip_address'], $data['reason']]);
        return $response->withHeader('Location', '/admincp/blocked-ips')->withStatus(302);
    }
}
