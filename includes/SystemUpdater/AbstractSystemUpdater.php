<?php

namespace LB\CreeBuildings\SystemUpdater;

use LB\CreeBuildings\Service\ConfigurationService;

/**
 * Description of AbstractSystemUpdater
 *
 * @author Lubos Babocky <babocky@gmail.com>
 */
abstract class AbstractSystemUpdater
{

    abstract protected function updateSystemData(): void;

    public function __construct(
        protected readonly ConfigurationService $configurationService,
        protected array $data
    )
    {

    }

    public final function update(): void
    {
        $this->updateSystemData();
    }
}
