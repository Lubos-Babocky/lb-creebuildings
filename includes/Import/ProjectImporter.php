<?php

namespace LB\CreeBuildings\Import;

use LB\CreeBuildings\Service\CreeApiService,
    LB\CreeBuildings\Service\LogService,
    LB\CreeBuildings\Repository\ProjectRepository,
    LB\CreeBuildings\Repository\ProjectAttachmentRepository,
    LB\CreeBuildings\Service\ConfigurationService,
    LB\CreeBuildings\SystemUpdater\ProjectSystemUpdater,
    LB\CreeBuildings\ApiDataParser\Project\ProjectDataParser,
    LB\CreeBuildings\ApiDataParser\Project\AttachmentDataParser,
    LB\CreeBuildings\ApiDataParser\Project\BackgroundImageDataParser;

/**
 * Description of ProjectImporter
 *
 * @author Lubos Babocky <babocky@gmail.com>
 */
class ProjectImporter extends AbstractImporter
{

    private array $outdatedProjects = [];

    public function __construct(
        protected ConfigurationService $configurationService,
        protected ProjectAttachmentRepository $attachmentRepository,
        CreeApiService $apiService,
        LogService $logService,
        ProjectRepository $repository
    )
    {
        parent::__construct($apiService, $logService, $repository);
    }

    protected function runImport(): void
    {
        foreach ($this->outdatedProjects as $projectRecord) {
            $projectId = $projectRecord['project_id'];
            $projectApiData = $this->apiService->loadProject(projectId: $projectId) ?? null;
            if (empty($projectApiData) || strtolower($projectApiData['accessType'] ?? '') !== 'public') {
                $this->removeProject($projectRecord['post_id'], $projectId);
            } elseif ($projectRecord['modified'] !== $projectApiData['modifiedAt'] ?? 'NOT_SET') {
                $updatedProjectData = $this->upsertProject(
                    projectApiData: $projectApiData,
                    databaseRecord: $projectRecord
                );
                $this->upsertProjectBackgroundImage(
                    projectId: $projectId,
                    backgroundApiData: $projectApiData['background'] ?? []
                );
                $this->upsertProjectAttachments(
                    projectId: $projectId,
                    attachments: $projectApiData['attachments'] ?? []
                );
                (new ProjectSystemUpdater(
                    configurationService: $this->configurationService,
                    data: $updatedProjectData
                ))->update();
            }
            //die("Project {$projectId} updated");    //[L:] Import only one project!
        }
    }

    private function removeProject(
        int $postId,
        string $projectId
    ): void
    {
        $this->configurationService->getAdapter()->removePostAndAttachements($postId);
        $this->attachmentRepository->deleteByProjectId($projectId);
        $this->repository->deleteRecord($projectId);
    }

    private function upsertProject(
        array $projectApiData,
        array $databaseRecord
    ): array
    {
        return (new ProjectDataParser(
                    apiData: $projectApiData,
                    repository: $this->repository,
                    databaseRecord: $databaseRecord
                ))
                ->importData()
                ->getAsArray();
    }

    /**
     * Update background image data if needed
     * @param string $projectId
     * @param array $backgroundApiData
     * @return void
     */
    private function upsertProjectBackgroundImage(
        string $projectId,
        array $backgroundApiData
    ): void
    {
        $backgroundId = $backgroundApiData['id'] ?? throw new \Exception("ID can't be empty");
        $backgroundDbRecord = $this->attachmentRepository->findById(id: $backgroundId)
            ?: null;
        if (empty($backgroundDbRecord) || $backgroundDbRecord['modified'] !== $backgroundApiData['modifiedAt'] ?? '') {
            (new BackgroundImageDataParser(
                projectId: $projectId,
                apiData: $backgroundApiData,
                repository: $this->attachmentRepository,
                databaseRecord: $backgroundDbRecord
            ))->importData();
        }
    }

    /**
     * Update projects attachments if needed
     * @param string $projectId
     * @param array $attachments
     * @return void
     */
    private function upsertProjectAttachments(
        string $projectId,
        array $attachments
    ): void
    {
        foreach ($attachments as $attachment) {
            $attachmentId = $attachment['id'] ?? throw new \Exception("ID can't be empty");
            $attachmentRecord = $this->attachmentRepository->findById(id: $attachmentId)
                ?: null;
            if (empty($attachmentRecord) || $attachmentRecord['modified'] !== $attachment['modifiedAt'] ?? '') {
                (new AttachmentDataParser(
                    projectId: $projectId,
                    apiData: $attachment,
                    repository: $this->attachmentRepository,
                    databaseRecord: $attachmentRecord
                ))->importData();
            }
        }
    }

    protected function needsUpdate(): bool
    {
        return !empty($this->outdatedProjects = $this->repository->findOutdatedProjects());
    }
}
