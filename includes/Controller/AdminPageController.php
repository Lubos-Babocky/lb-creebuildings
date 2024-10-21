<?php

namespace LB\CreeBuildings\Controller;

use LB\CreeBuildings\Service\AbstractService;

/**
 * Description of AdminPageController
 *
 * @author Lubos Babocky <babocky@gmail.com>
 */
class AdminPageController extends AbstractController {

    public function __construct(
            protected readonly \LB\CreeBuildings\Repository\ProjectRepository $projectRepository
    ) {

    }

    protected function injectDependencies(): void {
        
    }

    public function indexAction(): void {
        
    }
}
