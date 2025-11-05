<?php
declare(strict_types=1);

namespace WebEngine\Controllers;

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\Views\Twig;
use WebEngine\Services\PluginManager;

class PluginController
{
    private Twig $view;
    private PluginManager $pluginManager;

    public function __construct(Twig $view, PluginManager $pluginManager)
    {
        $this->view = $view;
        $this->pluginManager = $pluginManager;
    }

    public function index(Request $request, Response $response): Response
    {
        $plugins = $this->pluginManager->getAllPlugins();

        return $this->view->render($response, 'admin/plugins/index.twig', [
            'plugins' => $plugins
        ]);
    }

    public function install(Request $request, Response $response, array $args): Response
    {
        $pluginName = $args['name'];

        if ($this->pluginManager->installPlugin($pluginName)) {
            $_SESSION['success'] = 'Plugin installed successfully';
        } else {
            $_SESSION['error'] = 'Failed to install plugin';
        }

        return $response->withHeader('Location', '/admincp/plugins')->withStatus(302);
    }

    public function uninstall(Request $request, Response $response, array $args): Response
    {
        $pluginName = $args['name'];

        if ($this->pluginManager->uninstallPlugin($pluginName)) {
            $_SESSION['success'] = 'Plugin uninstalled successfully';
        } else {
            $_SESSION['error'] = 'Failed to uninstall plugin';
        }

        return $response->withHeader('Location', '/admincp/plugins')->withStatus(302);
    }

    public function enable(Request $request, Response $response, array $args): Response
    {
        $pluginName = $args['name'];

        if ($this->pluginManager->enablePlugin($pluginName)) {
            $_SESSION['success'] = 'Plugin enabled successfully';
        } else {
            $_SESSION['error'] = 'Failed to enable plugin';
        }

        return $response->withHeader('Location', '/admincp/plugins')->withStatus(302);
    }

    public function disable(Request $request, Response $response, array $args): Response
    {
        $pluginName = $args['name'];

        if ($this->pluginManager->disablePlugin($pluginName)) {
            $_SESSION['success'] = 'Plugin disabled successfully';
        } else {
            $_SESSION['error'] = 'Failed to disable plugin';
        }

        return $response->withHeader('Location', '/admincp/plugins')->withStatus(302);
    }

    public function configure(Request $request, Response $response, array $args): Response
    {
        $pluginName = $args['name'];
        $plugins = $this->pluginManager->getAllPlugins();

        if (!isset($plugins[$pluginName])) {
            return $response->withStatus(404);
        }

        return $this->view->render($response, 'admin/plugins/configure.twig', [
            'plugin_name' => $pluginName,
            'plugin' => $plugins[$pluginName]
        ]);
    }

    public function updateConfig(Request $request, Response $response, array $args): Response
    {
        $pluginName = $args['name'];
        $data = $request->getParsedBody();

        // Remove csrf_token from config
        unset($data['csrf_token']);

        if ($this->pluginManager->updatePluginConfig($pluginName, $data)) {
            $_SESSION['success'] = 'Plugin configuration updated successfully';
        } else {
            $_SESSION['error'] = 'Failed to update plugin configuration';
        }

        return $response->withHeader('Location', "/admincp/plugins/{$pluginName}/configure")->withStatus(302);
    }

    public function handleWebhook(Request $request, Response $response, array $args): Response
    {
        $pluginName = $args['name'];
        $plugin = $this->pluginManager->getPlugin($pluginName);

        if (!$plugin || !($plugin instanceof \WebEngine\Plugins\PaymentPluginInterface)) {
            return $response->withStatus(404);
        }

        $data = $request->getParsedBody() ?: [];

        try {
            $success = $plugin->handleWebhook($data);

            if ($success) {
                $response->getBody()->write('OK');
                return $response->withStatus(200);
            } else {
                return $response->withStatus(400);
            }
        } catch (\Exception $e) {
            error_log('Plugin webhook error: ' . $e->getMessage());
            return $response->withStatus(500);
        }
    }
}
