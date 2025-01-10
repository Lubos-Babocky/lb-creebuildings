<?php

namespace LB\CreeBuildings;

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

use LB\CreeBuildings\Import\PartnerListImporter,
    LB\CreeBuildings\Import\ProjectImporter,
    LB\CreeBuildings\Import\ProjectPropertyImporter,
    LB\CreeBuildings\Import\ProjectListImporter,
    LB\CreeBuildings\Service\AbstractService,
    LB\CreeBuildings\Service\ConfigurationService,
    LB\CreeBuildings\Service\CreeApiService,
    LB\CreeBuildings\Service\LogService,
    LB\CreeBuildings\Service\DatabaseService,
    LB\CreeBuildings\Exception\CriticalException,
    LB\CreeBuildings\Repository\Partner\PartnerRepository,
    LB\CreeBuildings\Repository\ProjectRepository,
    LB\CreeBuildings\Repository\ProjectAttachmentRepository,
    LB\CreeBuildings\Repository\ProjectPropertyRepository;

/**
 * Description of MainController
 * @author Lubos Babocky <babocky@gmail.com>
 */
class Import extends AbstractService
{

    private ConfigurationService $configurationService;
    private CreeApiService $creeApiService;
    private LogService $logService;
    private DatabaseService $databaseService;
    private ProjectRepository $projectRepository;

    protected function injectDependencies(): void
    {
        $this->configurationService = ConfigurationService::GetInstance();
        $this->creeApiService = CreeApiService::GetInstance();
        $this->logService = LogService::GetInstance();
        $this->databaseService = DatabaseService::GetInstance();
        $this->projectRepository = $this->databaseService->getRepository(ProjectRepository::class);
    }

    /**
     * @return void
     */
    public function runImport(): void
    {
        //(new \LB\CreeBuildings\Utils\ImportDataCleaner())->resetProjectForImport('8LGS9Q1UM0N1S');
        try {
            $this->importPartners();
            $this->importProjectProperties();
            $this->importBaseProjectData();
            $this->importProjects();
        } catch (CriticalException $ex) {
            $ex->handle();
        } catch (\Exception $ex) {
            $this->logService->handleException($ex);
        }
    }

    private function importPartners(): void
    {
        (new PartnerListImporter(
            apiService: $this->creeApiService,
            logService: $this->logService,
            repository: $this->databaseService->getRepository(PartnerRepository::class),
        ))->run();
    }

    private function importProjectProperties(): void
    {
        (new ProjectPropertyImporter(
            apiService: $this->creeApiService,
            logService: $this->logService,
            repository: $this->databaseService->getRepository(ProjectPropertyRepository::class)
        ))->run();
    }

    /**
     * @return void
     */
    private function importBaseProjectData(): void
    {
        (new ProjectListImporter(
            apiService: $this->creeApiService,
            logService: $this->logService,
            repository: $this->databaseService->getRepository(ProjectRepository::class)
        ))->run();
    }

    /**
     * @return void
     */
    protected function importProjects(): void
    {
        (new ProjectImporter(
            configurationService: $this->configurationService,
            apiService: $this->creeApiService,
            logService: $this->logService,
            repository: $this->databaseService->getRepository(ProjectRepository::class),
            attachmentRepository: $this->databaseService->getRepository(ProjectAttachmentRepository::class)
        ))->run();
    }
}
