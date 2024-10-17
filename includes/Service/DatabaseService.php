<?php

namespace LB\CreeBuildings\Service;

use LB\CreeBuildings\Repository\AbstractRepository;

/**
 * Description of DatabaseService
 *
 * Lubos Babocky <babocky@gmail.com>
 */
class DatabaseService extends AbstractService {

    protected ConfigurationService $configurationService;
    protected \PDO $connection;

    protected function injectDependencies(): void {
        $this->configurationService = ConfigurationService::GetInstance();
        $this->prepareConnection();
    }

    private function prepareConnection(): void {
        $this->connection = new \PDO(
                $this->configurationService->getConfig('DB_DSN'),
                $this->configurationService->getConfig('DB_USER'),
                $this->configurationService->getConfig('DB_PASSWORD'),
                [\PDO::ATTR_ERRMODE => \PDO::ERRMODE_EXCEPTION, \PDO::ATTR_DEFAULT_FETCH_MODE => \PDO::FETCH_ASSOC, \PDO::ATTR_EMULATE_PREPARES => false]
        );
    }

    public function getConnection(): \PDO {
        return $this->connection;
    }

    public function getRepository(string $repositoryClass): AbstractRepository {
        return new $repositoryClass($this->connection);
    }
}
