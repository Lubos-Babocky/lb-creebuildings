<?php

namespace LB\CreeBuildings\Import;

use LB\CreeBuildings\Service\CreeApiService,
    LB\CreeBuildings\Service\LogService,
    LB\CreeBuildings\Repository\ProjectPropertyRepository;

/**
 * Description of ProjectPropertyImporter
 *
 * @author Lubos Babocky <babocky@gmail.com>
 */
class ProjectPropertyImporter extends AbstractImporter
{

    private array $properties = [];

    public function __construct(
        CreeApiService $apiService,
        LogService $logService,
        ProjectPropertyRepository $repository
    )
    {
        parent::__construct($apiService, $logService, $repository);
    }

    protected function runImport(): void
    {
        $this->collectProperties();
        $this->upsertProperties();
    }

    private function collectProperties(): void
    {
        foreach ($this->apiService->loadProjectPropertyList() ?? [] as $propertyCategory) {
            foreach ($propertyCategory['definitions'] ?? [] as $property) {
                $this->addProperty(
                    category: $propertyCategory,
                    property: $property
                );
            }
        }
    }

    private function addProperty(
        array $category,
        array $property
    ): void
    {
        $categoryID = $category['group']['id'] ?? throw new \Exception('Property category ID is missing!');
        $propertyID = $property['id'] ?? throw new \Exception('Property ID is missing!');
        $this->properties[] = [
            'uid' => sprintf('%s-%s', $categoryID, $propertyID),
            'group_id' => $categoryID,
            'group_name' => $category['group']['displayName'] ?? 'Not set',
            'property_id' => $propertyID,
            'property_name' => $property['displayName'] ?? 'Not set',
            'tstamp' => time()
        ];
    }

    private function upsertProperties(): void
    {
        if (!empty($this->properties)) {
            $this->repository->saveMultipleRecords(records: $this->properties);
        }
    }
}
