<?php
declare(strict_types=1);

namespace WebEngine\Plugins;

abstract class PluginBase
{
    protected string $name;
    protected string $version;
    protected string $description;
    protected array $config;
    protected bool $enabled;

    abstract public function getName(): string;
    abstract public function getVersion(): string;
    abstract public function getDescription(): string;
    abstract public function install(): bool;
    abstract public function uninstall(): bool;
    abstract public function enable(): bool;
    abstract public function disable(): bool;

    public function isEnabled(): bool
    {
        return $this->enabled;
    }

    public function getConfig(): array
    {
        return $this->config;
    }

    public function setConfig(array $config): void
    {
        $this->config = $config;
    }

    public function getConfigValue(string $key, $default = null)
    {
        return $this->config[$key] ?? $default;
    }

    public function setConfigValue(string $key, $value): void
    {
        $this->config[$key] = $value;
    }
}
