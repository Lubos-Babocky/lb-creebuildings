<?php

namespace LB\CreeBuildings\Import;

use LB\CreeBuildings\ApiDataParser\Partner\PartnerDataParser,
    LB\CreeBuildings\Repository\Partner\PartnerRepository,
    LB\CreeBuildings\Service\CreeApiService,
    LB\CreeBuildings\Service\LogService,
    LB\CreeBuildings\SystemUpdater\PartnerSystemUpdater,
    LB\CreeBuildings\Service\ConfigurationService;

/**
 * Description of PartnerListImporter
 *
 * @author Lubos Babocky <babocky@gmail.com>
 */
class PartnerListImporter extends AbstractImporter
{

    private array $apiData;
    private array $storedPartnerRecords;

    public function __construct(
        CreeApiService $apiService,
        LogService $logService,
        PartnerRepository $repository
    )
    {
        parent::__construct($apiService, $logService, $repository);
    }

    protected function runImport(): void
    {
        $this->prepareData();
        $this->runUpdate();
    }

    private function prepareData(): void
    {
        $this->apiData = $this->apiService->loadAllPartners() ?? [];
        $this->storedPartnerRecords = $this->repository->findAllRecords();
    }

    private function runUpdate(): void
    {
        foreach ($this->apiData as $partnerApiData) {
            $partnerApiData['id'] ?? throw new \Exception('ID in ApiData can not be missing or empty!');
            if (!$this->partnerNeedsUpdate(currentPartnerApiData: $partnerApiData)) {
                $this->repository->updateTstamp(partnerId: $partnerApiData['id']);
                continue;
            }
            $partnerImporter = new PartnerDataParser(
                apiData: $partnerApiData,
                repository: $this->repository,
                databaseRecord: $this->storedPartnerRecords[$partnerApiData['id']] ?? null
            );
            (new PartnerSystemUpdater(
                configurationService: ConfigurationService::GetInstance(),
                data: $partnerImporter->importData()->getAsArray()
            ))->update();
        }
    }

    /**
     * @param array $currentPartnerApiData
     * @return bool
     */
    private function partnerNeedsUpdate(
        array $currentPartnerApiData,
    ): bool
    {
        if (empty($dbRecord = $this->storedPartnerRecords[$currentPartnerApiData['id']] ?? null)) {
            return true;
        }
        return match (true) {
            empty($dbRecord) => true,
            !array_key_exists('modifiedAt', $currentPartnerApiData) => true,
            !array_key_exists('modified', $dbRecord) => true,
            $dbRecord['modified'] !== $currentPartnerApiData['modifiedAt'] => true,
            default => false
        };
    }
}
