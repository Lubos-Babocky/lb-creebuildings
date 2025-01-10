<?php

namespace LB\CreeBuildings\Core;

use LB\CreeBuildings\Repository\AbstractRepository,
    LB\CreeBuildings\Service\ConfigurationService,
    LB\CreeBuildings\Service\CreeApiService,
    LB\CreeBuildings\Service\DatabaseService,
    LB\CreeBuildings\Service\LogService;

/**
 * Description of ComponentContainer
 *
 * @author Lubos Babocky <babocky@gmail.com>
 */
class ComponentContainer {

    private static ?ServiceContainer $instance = null;
    private ?ConfigurationService $configurationService = null;
    private ?CreeApiService $apiService = null;
    private ?DatabaseService $databaseService = null;
    private ?LogService $logService = null;
    private array $repositories = [];

    private function __construct() {
        
    }

    private function __clone() {
        throw new \Exception("Cannot clone a singleton.");
    }

    public final function __sleep() {
        throw new \Exception("Cannot serialize a singleton.");
    }

    public final function __wakeup() {
        throw new \Exception("Cannot unserialize a singleton.");
    }

    public static function GetInstance(): static {
        return self::$instance ??= new ServiceContainer();
    }

    public function getConfigurationService(): ConfigurationService {
        return $this->configurationService ??= ConfigurationService::GetInstance();
    }

    public function getApiService(): CreeApiService {
        return $this->apiService ??= CreeApiService::GetInstance();
    }

    public function getDatabaseService(): DatabaseService {
        return $this->databaseService ??= DatabaseService::GetInstance();
    }

    public function getLogService(): LogService {
        return $this->logService ??= LogService::GetInstance();
    }

    public function getRepository(string $repositoryClassName): AbstractRepository {
        if(!array_key_exists($repositoryClassName, $this->repositories)) {
            $this->repositories[$repositoryClassName] = $this->getDatabaseService()->getRepository($repositoryClassName);
        }
        return $this->repositories[$repositoryClassName];
    }
}
