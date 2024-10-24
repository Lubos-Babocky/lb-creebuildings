<?php

namespace LB\CreeBuildings\Controller;

use LB\CreeBuildings\Repository\ProjectRepository,
    LB\CreeBuildings\Service\ConfigurationService,
    LB\CreeBuildings\Service\DatabaseService;

/**
 * Description of AdminPageController
 *
 * @author Lubos Babocky <babocky@gmail.com>
 */
class AdminPageController extends AbstractController {

    public ProjectRepository $projectRepository;

    protected function injectDependencies(): void {
        $this->projectRepository = DatabaseService::GetInstance()->getRepository(ProjectRepository::class);
    }

    /**
     * @menuTitle Overview
     */
    public function indexAction(): void {
        
    }

    /**
     * @menuTitle Settings
     */
    public function settingsAction(): void {
        $this->setArgument('settings', ConfigurationService::GetInstance()->getConfig());
    }

    public function switchPostStatusAction(): void {
        $this->projectRepository->updateProjectPostStatus($this->getArgument('project_id'), $this->getArgument('post_status', 'draft'));
        $this->redirect('index');
    }

    public function buildSwithPostStatusUri(array $project): string {
        return $this->buildActionUri(
                        'switchPostStatus',
                        [
                            'project_id' => $project['project_id'],
                            'post_status' => $project['post_status'] === 'publish' ? 'draft' : 'publish'
                        ]
                );
    }
}
