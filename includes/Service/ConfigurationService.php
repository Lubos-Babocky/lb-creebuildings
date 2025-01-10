<?php

namespace LB\CreeBuildings\Service;

use LB\CreeBuildings\Adapter\SystemAdapterInterface,
    LB\CreeBuildings\Adapter\WordpressAdapter;

/**
 * Description of ConfigurationService
 *
 * @author Lubos Babocky <babocky@gmail.com>
 */
class ConfigurationService extends AbstractService
{

    private const CONFIG_INI_FILE = 'configuration/config.ini';
    private const API_TO_DB_MAP_FILE = 'configuration/apiToDbColumnsMap.json';

    private ?array $configuration = null;
    private ?array $apiToDbFieldsMap = null;
    private string $pluginRootFolderPath;
    private ?SystemAdapterInterface $adapter = null;

    /**
     * @throws \Exception
     */
    protected function injectDependencies(): void
    {
        $this->pluginRootFolderPath = realpath(__DIR__ . '/../../');
    }

    /**
     * @throws \Exception
     */
    public function getConfig(?string $propertyName = null): string|array
    {
        $this->configuration ??= parse_ini_file(
                $this->getConfigIniPath()
            )
            ?: throw new \Exception('Config file corupted.');

        if ($propertyName === null) {
            return $this->configuration;
        }

        return $this->configuration[$propertyName] ?? throw new \Exception(
                "Requested configuration not found [property name: $propertyName]"
            );
    }

    public function getAdapter(): SystemAdapterInterface
    {
        return $this->adapter
            ?: new WordpressAdapter(static::GetInstance());
    }

    /**
     * @param string $parserName
     * @return array
     */
    public function getApiToDatabaseColumnsMapping(
        string $parserName
    ): array
    {
        $this->apiToDbFieldsMap ??= json_decode(
                file_get_contents($this->getApiToDbMapPath()),
                true
            )
            ?: throw new \Exception('Invalid JSON format in mapping file.');

        return $this->apiToDbFieldsMap[$parserName] ?? throw new \Exception(
                sprintf(
                    '%s not configured in %s',
                    $parserName,
                    self::API_TO_DB_MAP_FILE
                )
            );
    }

    public function getPluginRootFolder(): string
    {
        return $this->pluginRootFolderPath;
    }

    private function getConfigIniPath(): string
    {
        return realpath(sprintf(
                    '%s/%s',
                    rtrim($this->getPluginRootFolder()),
                    ltrim(self::CONFIG_INI_FILE)
                ))
            ?: throw new \Exception('Config file not found.');
    }

    private function getApiToDbMapPath(): string
    {
        return realpath(sprintf(
                    '%s/%s',
                    rtrim($this->getPluginRootFolder()),
                    ltrim(self::API_TO_DB_MAP_FILE)
                ))
            ?: throw new \Exception('ApiToDB map file not found.');
    }
}
