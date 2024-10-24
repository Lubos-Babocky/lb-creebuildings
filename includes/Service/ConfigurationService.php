<?php

namespace LB\CreeBuildings\Service;

/**
 * Description of ConfigurationService
 *
 * @author Lubos Babocky <babocky@gmail.com>
 */
class ConfigurationService extends AbstractService {

    private const CONFIG_INI_FILE = 'config.ini';

    private array $configuration;
    private string $pluginRootFolderPath;

    /**
     * @throws \Exception
     */
    protected function injectDependencies(): void {
        $this->pluginRootFolderPath = realpath(__DIR__ . '/../../');
        $configIniPath = realpath(sprintf('%s/%s', rtrim($this->getPluginRootFolder()), ltrim(self::CONFIG_INI_FILE))) ?: throw new \Exception('Config file not found.');
        $this->configuration = parse_ini_file($configIniPath) ?: throw new \Exception('Config file corupted.');
    }

    /**
     * @throws \Exception
     */
    public function getConfig(?string $propertyName = null): string|array {
        if($propertyName === null) {
            return $this->configuration;
        }
        if (array_key_exists($propertyName, $this->configuration)) {
            return $this->configuration[$propertyName];
        } else {
            throw new \Exception("Requested configuration not found [property name: $propertyName]");
        }
    }

    public function getPluginRootFolder(): string {
        return $this->pluginRootFolderPath;
    }
}
