<?php

namespace LB\CreeBuildings\ApiDataParser;

use LB\CreeBuildings\Repository\AbstractRepository,
    LB\CreeBuildings\Utils\GeneralUtility,
    LB\CreeBuildings\ApiDataImporter\ImportResult,
    LB\CreeBuildings\Service\ConfigurationService;

/**
 * Description of AbstractApiDataParser
 * Process API data and map it to the database record.
 * @author Lubos Babocky <babocky@gmail.com>
 */
abstract class AbstractApiDataParser {

    protected ConfigurationService $configurationService;
    protected array $fieldMap;

    public function __construct(
            protected readonly array $apiData,
            protected readonly AbstractRepository $repository,
            protected ?array $databaseRecord = null
    ) {
        $this->configurationService = ConfigurationService::GetInstance();
        $this->fieldMap = $this->configurationService->getApiToDatabaseColumnsMapping(parserName: static::class);
    }

    /**
     * Use data specific rules before database record is saved
     */
    protected abstract function modifyDataBeforeSave(): void;

    /**
     * import data from API to DB
     * @return static
     */
    public final function importData(): ParserResult {
        $this->databaseRecord ??= $this->repository->getPreparedInsertArray();
        foreach ($this->fieldMap as $dbFieldName => $apiFieldPath) {
            $this->databaseRecord[$dbFieldName] = GeneralUtility::GetMultiArrayValue(
                    inputArray: $this->apiData,
                    keys: $apiFieldPath,
                    defaultValue: $this->databaseRecord[$dbFieldName]);
        }
        $this->modifyDataBeforeSave();
        $this->repository->saveRecord(record: $this->databaseRecord);
        return new ParserResult($this->databaseRecord);
    }
}
