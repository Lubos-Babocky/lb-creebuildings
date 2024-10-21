<?php

namespace LB\CreeBuildings\Controller;

use LB\CreeBuildings\Repository\ProjectRepository,
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

    public function indexAction(): void {
        
    }

    public function switchPostStatusAction(): void {
        $this->projectRepository->updateProjectPostStatus($this->getArgument('project_id'), $this->getArgument('post_status', 'draft'));
        $this->redirectToAction('index');
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
