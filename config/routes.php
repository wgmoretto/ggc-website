<?php
declare(strict_types=1);

use WebEngine\Controllers\HomeController;
use WebEngine\Controllers\AuthController;
use WebEngine\Controllers\NewsController;
use WebEngine\Controllers\RankingsController;
use WebEngine\Controllers\ProfileController;
use WebEngine\Controllers\UserCpController;
use WebEngine\Controllers\DonationController;
use WebEngine\Controllers\DownloadsController;
use WebEngine\Controllers\InfoController;
use WebEngine\Controllers\CastleSiegeController;
use WebEngine\Controllers\AdminController;
use WebEngine\Controllers\ApiController;
use WebEngine\Controllers\PluginController;
use WebEngine\Middleware\AuthMiddleware;
use WebEngine\Middleware\AdminMiddleware;

// Public Routes
$app->get('/', [HomeController::class, 'index'])->setName('home');
$app->get('/home', [HomeController::class, 'index']);

// Authentication
$app->get('/login', [AuthController::class, 'showLogin'])->setName('login');
$app->post('/login', [AuthController::class, 'login']);
$app->get('/register', [AuthController::class, 'showRegister'])->setName('register');
$app->post('/register', [AuthController::class, 'register']);
$app->get('/logout', [AuthController::class, 'logout'])->setName('logout');
$app->get('/forgotpassword', [AuthController::class, 'showForgotPassword'])->setName('forgotpassword');
$app->post('/forgotpassword', [AuthController::class, 'forgotPassword']);
$app->get('/verifyemail/{token}', [AuthController::class, 'verifyEmail'])->setName('verifyemail');

// News
$app->get('/news', [NewsController::class, 'index'])->setName('news');
$app->get('/news/{id}', [NewsController::class, 'show'])->setName('news.show');

// Rankings
$app->get('/rankings', [RankingsController::class, 'index'])->setName('rankings');
$app->get('/rankings/level', [RankingsController::class, 'level'])->setName('rankings.level');
$app->get('/rankings/resets', [RankingsController::class, 'resets'])->setName('rankings.resets');
$app->get('/rankings/grandresets', [RankingsController::class, 'grandResets'])->setName('rankings.grandresets');
$app->get('/rankings/guilds', [RankingsController::class, 'guilds'])->setName('rankings.guilds');
$app->get('/rankings/killers', [RankingsController::class, 'killers'])->setName('rankings.killers');
$app->get('/rankings/online', [RankingsController::class, 'online'])->setName('rankings.online');
$app->get('/rankings/votes', [RankingsController::class, 'votes'])->setName('rankings.votes');
$app->get('/rankings/gens', [RankingsController::class, 'gens'])->setName('rankings.gens');
$app->get('/rankings/master', [RankingsController::class, 'master'])->setName('rankings.master');

// Profiles
$app->get('/profile/player/{name}', [ProfileController::class, 'player'])->setName('profile.player');
$app->get('/profile/guild/{name}', [ProfileController::class, 'guild'])->setName('profile.guild');

// Downloads
$app->get('/downloads', [DownloadsController::class, 'index'])->setName('downloads');

// Server Info
$app->get('/info', [InfoController::class, 'index'])->setName('info');
$app->get('/castlesiege', [CastleSiegeController::class, 'index'])->setName('castlesiege');

// Static Pages
$app->get('/privacy', [InfoController::class, 'privacy'])->setName('privacy');
$app->get('/tos', [InfoController::class, 'tos'])->setName('tos');
$app->get('/refunds', [InfoController::class, 'refunds'])->setName('refunds');
$app->get('/contact', [InfoController::class, 'contact'])->setName('contact');
$app->post('/contact', [InfoController::class, 'sendContact']);

// Donation/Shop
$app->get('/donation', [DonationController::class, 'index'])->setName('donation');
$app->post('/donation/process', [DonationController::class, 'process']);

// User Control Panel (Protected Routes)
$app->group('/usercp', function ($group) {
    $group->get('', [UserCpController::class, 'index'])->setName('usercp');
    $group->get('/myaccount', [UserCpController::class, 'myAccount'])->setName('usercp.myaccount');
    $group->post('/myaccount', [UserCpController::class, 'updateAccount']);
    $group->get('/myemail', [UserCpController::class, 'myEmail'])->setName('usercp.myemail');
    $group->post('/myemail', [UserCpController::class, 'updateEmail']);
    $group->get('/mypassword', [UserCpController::class, 'myPassword'])->setName('usercp.mypassword');
    $group->post('/mypassword', [UserCpController::class, 'updatePassword']);
    $group->get('/addstats', [UserCpController::class, 'addStats'])->setName('usercp.addstats');
    $group->post('/addstats', [UserCpController::class, 'processAddStats']);
    $group->get('/reset', [UserCpController::class, 'reset'])->setName('usercp.reset');
    $group->post('/reset', [UserCpController::class, 'processReset']);
    $group->get('/resetstats', [UserCpController::class, 'resetStats'])->setName('usercp.resetstats');
    $group->post('/resetstats', [UserCpController::class, 'processResetStats']);
    $group->get('/buyzen', [UserCpController::class, 'buyZen'])->setName('usercp.buyzen');
    $group->post('/buyzen', [UserCpController::class, 'processBuyZen']);
    $group->get('/clearpk', [UserCpController::class, 'clearPk'])->setName('usercp.clearpk');
    $group->post('/clearpk', [UserCpController::class, 'processClearPk']);
    $group->get('/clearskilltree', [UserCpController::class, 'clearSkillTree'])->setName('usercp.clearskilltree');
    $group->post('/clearskilltree', [UserCpController::class, 'processClearSkillTree']);
    $group->get('/unstick', [UserCpController::class, 'unstick'])->setName('usercp.unstick');
    $group->post('/unstick', [UserCpController::class, 'processUnstick']);
    $group->get('/vote', [UserCpController::class, 'vote'])->setName('usercp.vote');
    $group->post('/vote', [UserCpController::class, 'processVote']);
})->add(new AuthMiddleware($container));

// Admin Control Panel (Protected Routes)
$app->group('/admincp', function ($group) {
    $group->get('', [AdminController::class, 'dashboard'])->setName('admin.dashboard');

    // Account Management
    $group->get('/accounts', [AdminController::class, 'accounts'])->setName('admin.accounts');
    $group->get('/account/{id}', [AdminController::class, 'accountInfo'])->setName('admin.account.info');
    $group->post('/account/{id}/edit', [AdminController::class, 'editAccount']);
    $group->post('/account/{id}/ban', [AdminController::class, 'banAccount']);
    $group->post('/account/{id}/unban', [AdminController::class, 'unbanAccount']);

    // Character Management
    $group->get('/character/{name}', [AdminController::class, 'characterInfo'])->setName('admin.character.info');
    $group->post('/character/{name}/edit', [AdminController::class, 'editCharacter']);

    // News Management
    $group->get('/news', [AdminController::class, 'manageNews'])->setName('admin.news');
    $group->get('/news/add', [AdminController::class, 'addNews'])->setName('admin.news.add');
    $group->post('/news/add', [AdminController::class, 'saveNews']);
    $group->get('/news/{id}/edit', [AdminController::class, 'editNews'])->setName('admin.news.edit');
    $group->post('/news/{id}/edit', [AdminController::class, 'updateNews']);
    $group->post('/news/{id}/delete', [AdminController::class, 'deleteNews']);

    // Configuration
    $group->get('/settings', [AdminController::class, 'settings'])->setName('admin.settings');
    $group->post('/settings', [AdminController::class, 'updateSettings']);
    $group->get('/credits', [AdminController::class, 'creditsConfig'])->setName('admin.credits');
    $group->post('/credits', [AdminController::class, 'updateCredits']);
    $group->get('/castlesiege/config', [AdminController::class, 'castleSiegeConfig'])->setName('admin.castlesiege');
    $group->post('/castlesiege/config', [AdminController::class, 'updateCastleSiege']);

    // Monitoring
    $group->get('/monitoring/bans', [AdminController::class, 'latestBans'])->setName('admin.monitoring.bans');
    $group->get('/monitoring/registrations', [AdminController::class, 'newRegistrations'])->setName('admin.monitoring.registrations');
    $group->get('/monitoring/online', [AdminController::class, 'onlineAccounts'])->setName('admin.monitoring.online');
    $group->get('/monitoring/paypal', [AdminController::class, 'paypalTransactions'])->setName('admin.monitoring.paypal');

    // System
    $group->get('/cache', [AdminController::class, 'cacheManager'])->setName('admin.cache');
    $group->post('/cache/clear', [AdminController::class, 'clearCache']);
    $group->get('/cron', [AdminController::class, 'cronManager'])->setName('admin.cron');
    $group->get('/blocked-ips', [AdminController::class, 'blockedIps'])->setName('admin.blocked.ips');
    $group->post('/blocked-ips/add', [AdminController::class, 'addBlockedIp']);

    // Plugins
    $group->get('/plugins', [PluginController::class, 'index'])->setName('admin.plugins');
    $group->post('/plugins/{name}/install', [PluginController::class, 'install'])->setName('admin.plugins.install');
    $group->post('/plugins/{name}/uninstall', [PluginController::class, 'uninstall'])->setName('admin.plugins.uninstall');
    $group->post('/plugins/{name}/enable', [PluginController::class, 'enable'])->setName('admin.plugins.enable');
    $group->post('/plugins/{name}/disable', [PluginController::class, 'disable'])->setName('admin.plugins.disable');
    $group->get('/plugins/{name}/configure', [PluginController::class, 'configure'])->setName('admin.plugins.configure');
    $group->post('/plugins/{name}/configure', [PluginController::class, 'updateConfig']);

})->add(new AdminMiddleware($container))->add(new AuthMiddleware($container));

// API Routes
$app->group('/api', function ($group) {
    $group->get('/servertime', [ApiController::class, 'serverTime']);
    $group->get('/events', [ApiController::class, 'events']);
    $group->get('/castlesiege', [ApiController::class, 'castleSiege']);
    $group->get('/guildmark/{guild}', [ApiController::class, 'guildMark']);
    $group->get('/version', [ApiController::class, 'version']);
    $group->post('/cron', [ApiController::class, 'cron']);
    $group->post('/paypal/ipn', [ApiController::class, 'paypalIpn']);

    // Plugin Webhooks
    $group->post('/plugins/{name}/webhook', [PluginController::class, 'handleWebhook']);
});
