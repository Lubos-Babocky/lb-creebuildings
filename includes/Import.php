<?php

namespace LB\CreeBuildings;

use LB\CreeBuildings\DataHandler\ProjectDataHandler,
    LB\CreeBuildings\DataHandler\ProjectImageDataHandler,
    LB\CreeBuildings\Utils\GeneralUtility,
    LB\CreeBuildings\Service\AbstractService,
    LB\CreeBuildings\Service\ConfigurationService,
    LB\CreeBuildings\Service\CreeApiService,
    LB\CreeBuildings\Service\LogService,
    LB\CreeBuildings\Service\DatabaseService,
    LB\CreeBuildings\Repository\ProjectRepository,
    LB\CreeBuildings\Repository\ProjectImageRepository,
    LB\CreeBuildings\Repository\ProjectPropertyRepository;

/**
 * Description of MainController
 * @author Lubos Babocky <babocky@gmail.com>
 */
class Import extends AbstractService {

    protected ConfigurationService $configurationService;
    protected CreeApiService $creeApiService;
    protected LogService $logService;
    protected DatabaseService $databaseService;
    protected ProjectRepository $projectRepository;
    protected ProjectPropertyRepository $projectPropertyRepository;
    protected float $executionStart;

    public function __destruct() {
        echo sprintf('<br>Import terminated after %ss', microtime(true) - $this->executionStart);
    }

    protected function injectDependencies(): void {
        $this->configurationService = ConfigurationService::GetInstance();
        $this->creeApiService = CreeApiService::GetInstance();
        $this->logService = LogService::GetInstance();
        $this->databaseService = DatabaseService::GetInstance();
        $this->projectRepository = $this->databaseService->getRepository(ProjectRepository::class);
        $this->projectPropertyRepository = $this->databaseService->getRepository(ProjectPropertyRepository::class);
        $this->executionStart = microtime(true);
    }

    public function runImport(): void {
        try {
            $this->logService->writeAccessLog('Import started');
            $this->updateProjectPropertyData();
            $this->updateBaseProjectData();
            $this->updateAllProjectData();
            $this->updateAllImagePublicUrls();
        } catch (\Exception $ex) {
            $this->logService->handleException($ex);
        }
    }

    protected function updateProjectPropertyData(): void {
        $insertData = [];
        $projectPropertiesData = $this->creeApiService->loadProjectPropertyList();
        foreach ($projectPropertiesData as $propertyCategory) {
            foreach (GeneralUtility::GetMultiArrayValue($propertyCategory, 'definitions') as $property) {
                $insertData[] = [
                    ':uid' => sprintf(
                            '%s-%s',
                            GeneralUtility::GetMultiArrayValue($propertyCategory, 'group.id'),
                            GeneralUtility::GetMultiArrayValue($property, 'id')
                    ),
                    ':group_id' => GeneralUtility::GetMultiArrayValue($propertyCategory, 'group.id'),
                    ':group_name' => GeneralUtility::GetMultiArrayValue($propertyCategory, 'group.displayName'),
                    ':property_id' => GeneralUtility::GetMultiArrayValue($property, 'id'),
                    ':property_name' => GeneralUtility::GetMultiArrayValue($property, 'displayName')
                ];
            }
        }
        $this->projectPropertyRepository->insertAllBaseProjectPropertyData($insertData);
        $this->logService->writeAccessLog('Table lb_creebuildings_project_property updated.');
    }

    protected function updateBaseProjectData(): void {
        if ($this->projectRepository->getLastUpdate() !== date('Y-m-d')) {
            $this->logService->writeAccessLog('Project base data require update');
            $insertData = [];
            foreach ($this->creeApiService->loadAllProjects() as $projectData) {
                $insertData[] = [':projectId' => $projectData['id'], ':accessType' => $projectData['accessType']];
            }
            $this->projectRepository->insertAllBaseProjectData($insertData);
            $this->logService->writeAccessLog('Project base data updated');
        } else {
            $this->logService->writeAccessLog('Project base data already updated');
        }
    }

    protected function updateAllProjectData(): void {
        if (empty($projectsToUpdate = $this->projectRepository->loadUnporcessedProjects())) {
            $this->logService->writeAccessLog('No project update required');
            return;
        }
        foreach (array_column($projectsToUpdate, 'project_id') as $projectId) {
            (new ProjectDataHandler())->processApiData($this->creeApiService->loadProject($projectId));
        }
    }

    protected function updateAllImagePublicUrls(): void {
        $unprocessedImages = $this->databaseService->getRepository(ProjectImageRepository::class)->findUnprocessedImages();
        $this->logService->writeAccessLog(sprintf('Unprocessed images found, calling api for %d of them', count($unprocessedImages)));
        foreach ($unprocessedImages as $imageData) {
            (new ProjectImageDataHandler($imageData))->processImage();
        }
    }
}
