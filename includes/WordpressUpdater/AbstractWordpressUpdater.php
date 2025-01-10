<?php

namespace LB\CreeBuildings\WordpressUpdater;

use LB\CreeBuildings\Service\ConfigurationService,
    LB\CreeBuildings\Repository\AbstractRepository;

/**
 * Description of AbstractWordpressUpdater
 * @author Lubos Babocky <babocky@gmail.com>
 */
abstract class AbstractWordpressUpdater {

    protected ConfigurationService $configurationService;

    public function __construct(
            protected array $updateData,
            protected readonly AbstractRepository $repository
    ) {
        $this->configurationService = ConfigurationService::GetInstance();
    }

    abstract protected function updateWordpressTables(): void;

    public function update(): void {
        $this->updateWordpressTables();
        $this->repository->saveRecord($this->updateData);
    }
}
