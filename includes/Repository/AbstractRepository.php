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

    public function __construct(
            protected readonly \PDO $pdo
    ) {
        $this->initTableName();
        $this->initQueryBuilder();
    }

    private function initTableName(): void {
        $refletion = new \ReflectionClass(static::class);
        $pattern = '/\$([a-zA-Z_][a-zA-Z0-9_]*)\s+([a-zA-Z_][a-zA-Z0-9_]*)/';
        $matches = [];
        preg_match($pattern, $refletion->getDocComment(), $matches);
        if ($matches) {
            $this->tableName = $matches[2];
        } else {
            throw new \Exception('Project table not defined in repository');
        }
    }
   
    private function initQueryBuilder(): void {
        $this->queryBuilder = new QueryBuilder($this->pdo, $this->tableName);
    }

    protected function insertMultipleRows($query, $insertRows): void {
        try {
            $this->pdo->beginTransaction();
            $stmt = $this->pdo->prepare($query);
            foreach ($insertRows as $row) {
                $stmt->execute($row);
            }
            $this->pdo->commit();
        } catch (\Exception $ex) {
            $this->pdo->rollBack();
            throw $ex;
        }
    }

    public function createQuery(): QueryBuilder {
        return $this->queryBuilder;
    }
}
