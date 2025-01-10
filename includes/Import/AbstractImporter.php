<?php

namespace LB\CreeBuildings\Import;

use LB\CreeBuildings\Service\CreeApiService,
    LB\CreeBuildings\Service\LogService,
    LB\CreeBuildings\Repository\AbstractRepository;

/**
 * Description of AbstractImporter
 *
 * @author Lubos Babocky <babocky@gmail.com>
 */
abstract class AbstractImporter
{

    private float $importStart;

    abstract protected function runImport(): void;

    public function __construct(
        protected readonly CreeApiService $apiService,
        protected readonly LogService $logService,
        protected readonly AbstractRepository $repository
    )
    {
        $this->importStart = microtime(true);
    }

    public final function run(): void
    {
        try {
            if ($this->needsUpdate()) {
                $this->logService->writeAccessLog(sprintf("%s started.", static::class));
                $this->runImport();
                $this->logService->writeAccessLog(sprintf("%s ended in %s", static::class, microtime(true) - $this->importStart));
                $this->afterImport();
            }
        } catch (\Exception $ex) {
            $this->logService->handleException($ex);
        }
    }

    /**
     * Returns true if max tstamp of demanded table is not from today
     * @return bool
     */
    protected function needsUpdate(): bool
    {
        return date(format: 'Y-m-d', timestamp: $this->repository->getLastUpdate()) !== date(format: 'Y-m-d');
    }

    protected function afterImport(): void
    {
        die(static::class);
    }
}
