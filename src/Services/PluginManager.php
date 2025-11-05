<?php
declare(strict_types=1);

namespace WebEngine\Services;

use WebEngine\Database\Database;
use WebEngine\Plugins\PluginBase;

class PluginManager
{
    private Database $db;
    private array $plugins = [];
    private array $loadedPlugins = [];

    public function __construct(Database $db)
    {
        $this->db = $db;
        $this->registerPlugins();
        $this->loadEnabledPlugins();
    }

    private function registerPlugins(): void
    {
        // Register available plugins
        $this->plugins = [
            'PayPal' => \WebEngine\Plugins\PayPal\PayPalPlugin::class,
            'Stripe' => \WebEngine\Plugins\Stripe\StripePlugin::class,
        ];
    }

    private function loadEnabledPlugins(): void
    {
        $sql = "SELECT * FROM GGC_PLUGINS WHERE enabled = 1";
        $enabledPlugins = $this->db->fetchAll($sql);

        foreach ($enabledPlugins as $pluginData) {
            $pluginName = $this->getPluginKeyByName($pluginData['name']);

            if ($pluginName && isset($this->plugins[$pluginName])) {
                $pluginClass = $this->plugins[$pluginName];
                $plugin = new $pluginClass($this->db);

                // Load configuration
                $config = json_decode($pluginData['config'], true) ?? [];
                $plugin->setConfig($config);

                $this->loadedPlugins[$pluginName] = $plugin;
            }
        }
    }

    public function getPlugin(string $name): ?PluginBase
    {
        return $this->loadedPlugins[$name] ?? null;
    }

    public function getAllPlugins(): array
    {
        $result = [];

        foreach ($this->plugins as $key => $class) {
            $plugin = new $class($this->db);

            // Check if installed and enabled
            $sql = "SELECT * FROM GGC_PLUGINS WHERE name = ?";
            $pluginData = $this->db->fetch($sql, [$plugin->getName()]);

            $result[$key] = [
                'name' => $plugin->getName(),
                'version' => $plugin->getVersion(),
                'description' => $plugin->getDescription(),
                'installed' => $pluginData !== null,
                'enabled' => $pluginData ? (bool)$pluginData['enabled'] : false,
                'config' => $pluginData ? json_decode($pluginData['config'], true) : []
            ];
        }

        return $result;
    }

    public function installPlugin(string $name): bool
    {
        if (!isset($this->plugins[$name])) {
            return false;
        }

        $pluginClass = $this->plugins[$name];
        $plugin = new $pluginClass($this->db);

        return $plugin->install();
    }

    public function uninstallPlugin(string $name): bool
    {
        if (!isset($this->plugins[$name])) {
            return false;
        }

        $pluginClass = $this->plugins[$name];
        $plugin = new $pluginClass($this->db);

        return $plugin->uninstall();
    }

    public function enablePlugin(string $name): bool
    {
        if (!isset($this->plugins[$name])) {
            return false;
        }

        $pluginClass = $this->plugins[$name];
        $plugin = new $pluginClass($this->db);

        return $plugin->enable();
    }

    public function disablePlugin(string $name): bool
    {
        if (!isset($this->plugins[$name])) {
            return false;
        }

        $pluginClass = $this->plugins[$name];
        $plugin = new $pluginClass($this->db);

        return $plugin->disable();
    }

    public function updatePluginConfig(string $name, array $config): bool
    {
        $plugin = $this->getPluginByKey($name);
        if (!$plugin) {
            return false;
        }

        $sql = "UPDATE GGC_PLUGINS SET config = ? WHERE name = ?";
        return $this->db->execute($sql, [json_encode($config), $plugin->getName()]);
    }

    public function getPaymentPlugins(): array
    {
        $paymentPlugins = [];

        foreach ($this->loadedPlugins as $key => $plugin) {
            if ($plugin instanceof \WebEngine\Plugins\PaymentPluginInterface) {
                $paymentPlugins[$key] = $plugin;
            }
        }

        return $paymentPlugins;
    }

    private function getPluginByKey(string $key): ?PluginBase
    {
        if (!isset($this->plugins[$key])) {
            return null;
        }

        $pluginClass = $this->plugins[$key];
        $plugin = new $pluginClass($this->db);

        // Load configuration
        $sql = "SELECT config FROM GGC_PLUGINS WHERE name = ?";
        $result = $this->db->fetch($sql, [$plugin->getName()]);

        if ($result) {
            $config = json_decode($result['config'], true) ?? [];
            $plugin->setConfig($config);
        }

        return $plugin;
    }

    private function getPluginKeyByName(string $name): ?string
    {
        foreach ($this->plugins as $key => $class) {
            $plugin = new $class($this->db);
            if ($plugin->getName() === $name) {
                return $key;
            }
        }

        return null;
    }
}
