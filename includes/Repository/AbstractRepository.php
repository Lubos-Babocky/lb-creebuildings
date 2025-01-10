<?php

namespace LB\CreeBuildings\Repository;

use LB\CreeBuildings\Utils\QueryBuilder;

/**
 * Description of AbstractRepository
 *
 * @author Lubos Babocky <babocky@gmail.com>
 */
abstract class AbstractRepository {

    protected string $tableName;
    protected QueryBuilder $queryBuilder;
    protected array $defaultRecordStructure;

    public function __construct(
            protected readonly \PDO $pdo
    ) {
        $this->initTableName();
        $this->initQueryBuilder();
    }

    private function initTableName(): void {
        $matches = [];
        preg_match(
                pattern: '/\$([a-zA-Z_][a-zA-Z0-9_]*)\s+([a-zA-Z_][a-zA-Z0-9_]*)/',
                subject: (new \ReflectionClass(static::class))->getDocComment(),
                matches: $matches
        );
        if ($matches) {
            $this->tableName = $matches[2];
        } else {
            throw new \Exception('Project table not defined in repository');
        }
    }

    private function initQueryBuilder(): void {
        $this->queryBuilder = new QueryBuilder(
                pdo: $this->pdo,
                tableName: $this->tableName
        );
    }

    /**
     * Returns max tstamp in table
     * @return type
     */
    public function getLastUpdate() {
        return $this->createQuery()
                        ->select(['MAX(`tstamp`)'])
                        ->execute()
                        ->fetchColumn();
    }

    /**
     * Returns all rows from table
     * @param string $idField
     * @return array
     */
    public function findAllRecords(
            string $idField = 'id'
    ): array {
        $results = $this->createQuery()
                ->select(['*'])
                ->execute()
                ->fetchAll();
        return array_combine(array_column($results, $idField), $results);
    }

    /**
     * This method automatically saves record into DB
     * @param array $record
     */
    public function saveRecord(
            array $record
    ) {
        $this->validateRecordBeforeSave($record);
        try {
            $this->pdo->beginTransaction();
            $stmt = $this->pdo->prepare($this->prepareRecordDataForPdoStatement($record));
            $stmt->execute($record);
            $this->pdo->commit();
        } catch (\Exception $ex) {
            $this->pdo->rollBack();
            throw $ex;
        }
    }

    public function saveMultipleRecords(
            array $records
    ): void {
        try {
            $this->pdo->beginTransaction();
            foreach ($records as $record) {
                $this->validateRecordBeforeSave($record);
                $stmt = $this->pdo->prepare($this->prepareRecordDataForPdoStatement($record));
                $stmt->execute($record);
            }
            $this->pdo->commit();
        } catch (\Exception $ex) {
            $this->pdo->rollBack();
            throw $ex;
        }
    }

    /**
     * Returns prepared SQL for PDO and adds : to input array keys
     * @param array $record
     * @return string
     */
    private function prepareRecordDataForPdoStatement(
            array &$record
    ): string {
        $fields = [];
        $values = [];
        $updates = [];
        $preparedRecord = [];
        foreach ($record as $fieldName => $fieldValue) {
            $fields[] = sprintf('`%s`', $fieldName);
            $values[] = sprintf(':%s', $fieldName);
            $updates[] = sprintf('`%s` = VALUES(`%s`)', $fieldName, $fieldName);
            $preparedRecord[sprintf(':%s', $fieldName)] = $fieldValue ?? '';
        }
        $record = $preparedRecord;
        return sprintf(
                "INSERT INTO `{$this->tableName}` (%s) VALUES (%s) ON DUPLICATE KEY UPDATE %s",
                implode(', ', $fields),
                implode(', ', $values),
                implode(', ', $updates)
        );
    }

    protected function insertMultipleRows(
            string $query,
            array $insertRows
    ): void {
        try {
            $this->pdo->beginTransaction();
            $stmt = $this->pdo->prepare(query: $query);
            foreach ($insertRows as $row) {
                $stmt->execute($row);
            }
            $this->pdo->commit();
        } catch (\Exception $ex) {
            $this->pdo->rollBack();
            throw $ex;
        }
    }

    /**
     * Returns last inserted id
     * @param string $query
     * @param array $insertData
     * @return string|null
     * @throws \Exception
     */
    protected function insertSingleRow(
            string $query,
            array $insertData
    ): ?string {
        try {
            $this->pdo->beginTransaction();
            $stmt = $this->pdo->prepare($query);
            $stmt->execute($insertData);
            $this->pdo->commit();
            return $this->pdo->lastInsertId();
        } catch (\Exception $ex) {
            $this->pdo->rollBack();
            throw $ex;
        }
    }

    public function createQuery(): QueryBuilder {
        return $this->queryBuilder;
    }

    public function executeQuery(
            string $query
    ): int|bool {
        return $this->pdo->exec($query);
    }

    public function getTableName(): string {
        return $this->tableName;
    }

    public function getPreparedInsertArray(): array {
        return $this->defaultRecordStructure ??= $this->prepareDefaultRecordStructure();
    }

    private function validateRecordBeforeSave(
            array $record
    ): void {
        if (!empty($invalidKeys = array_diff_key($record, $this->getPreparedInsertArray()))) {
            throw new \Exception('Input array contains invalid keys: ' . implode(', ', array_keys($invalidKeys)));
        }
    }

    private function prepareDefaultRecordStructure(): array {
        $tableColumns = $this->pdo
                ->query("DESCRIBE {$this->tableName}")
                ->fetchAll(\PDO::FETCH_ASSOC);
        $preparedArray = [];
        foreach ($tableColumns as $column) {
            $preparedArray[$column['Field']] = $this->convertType(
                    value: $column['Default'],
                    type: $column['Type']
            );
        }
        return $preparedArray;
    }

    /**
     * @param mixed $value
     * @param string $type
     * @return null|int|float|bool|string
     */
    private function convertType(
            mixed $value,
            string $type
    ): null|int|float|bool|string {
        return match (true) {
            $value === null => null,
            preg_match('/int|tinyint|smallint|mediumint|bigint/i', $type) && is_numeric($value) => (int) $value,
            preg_match('/float|double|decimal|numeric/i', $type) && is_numeric($value) => (float) $value,
            preg_match('/bool|boolean/i', $type) => (bool) $value,
            default => (string) $value,
        };
    }
}
