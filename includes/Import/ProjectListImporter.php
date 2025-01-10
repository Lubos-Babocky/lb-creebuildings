<?php

namespace LB\CreeBuildings\Import;

use LB\CreeBuildings\Service\CreeApiService,
    LB\CreeBuildings\Service\LogService,
    LB\CreeBuildings\Repository\ProjectRepository;

/**
 * Description of ProjectListImporter
 *
 * @author Lubos Babocky <babocky@gmail.com>
 */
class ProjectListImporter extends AbstractImporter
{

    private array $storedProjects = [];

    public function __construct(
        CreeApiService $apiService,
        LogService $logService,
        ProjectRepository $repository
    )
    {
        parent::__construct($apiService, $logService, $repository);
    }

    protected function runImport(): void
    {
        $projectsForUpdate = [];
        $this->storedProjects = $this->repository->findAllRecords('project_id');
        foreach ($this->apiService->loadAllProjects() as $project) {
            $projectId = ($project['id'] ?? null)
                ?: throw new \Exception("Project id can't be empty!");
            $projectsForUpdate[$projectId] = [
                'project_id' => $projectId,
                'tstamp' => time()
            ];
            if ($this->isModified(projectId: $projectId, modifiedAt: $project['modifiedAt'] ?? '')) {
                $projectsForUpdate[$projectId]['modified'] = '';
            }
        }
        $this->repository->saveMultipleRecords($projectsForUpdate);
    }

    protected function isModified(
        string $projectId,
        string $modifiedAt
    ): bool
    {
        return !array_key_exists($projectId, $this->storedProjects) || $modifiedAt !== $this->storedProjects[$projectId]['modified'];
    }
}
