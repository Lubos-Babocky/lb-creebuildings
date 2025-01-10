<?php

namespace LB\CreeBuildings\Utils;

/**
 * Description of QueryBuilder
 *
 * @author Lubos Babocky <babocky@gmail.com>
 */
class QueryBuilder {

    protected array $selectFields;
    protected array $constraints;
    protected ?int $limit;

    public function __construct(
            protected readonly \PDO $pdo,
            protected readonly string $tableName
    ) {
        
    }

    /**
     * Sets select fields, can be set as ['*'] for all results
     * @param array $fields
     * @return self
     */
    public function select(
            array $fields
    ): self {
        $this->selectFields = $fields;
        return $this;
    }

    /**
     * Sets array of constraints which are merged with AND conjuction
     * @param array $constraints
     * @return self
     */
    public function where(
            array $constraints
    ): self {
        $this->constraints = $constraints;
        return $this;
    }

    /**
     * Sets limit of results
     * @param int $limit
     * @return self
     */
    public function limit(
            int $limit
    ): self {
        $this->limit = $limit;
        return $this;
    }

    /**
     * Builds and returns query string
     * @return string
     */
    public function getQuery(): string {
        $query = sprintf("SELECT %s FROM `%s`", $this->getSelectFields(), $this->getTableName());
        if (!empty($this->constraints)) {
            $query .= sprintf(' WHERE %s', implode(' AND ', $this->constraints));
        }
        if (isset($this->limit)) {
            $query .= sprintf(' LIMIT %d', $this->limit);
        }
        return $query;
    }

    /**
     * Executes query and returns PDOStatement
     * @return \PDOStatement
     */
    public function execute(): \PDOStatement {
        return $this->pdo->query($this->getQuery());
    }

    /**
     * Executes update request and returns number of affected rows
     * @param array $set
     * @param array $where
     * @return int|bool
     */
    public function update(
            array $set,
            array $where
    ): int|bool {
        return $this->pdo->exec(sprintf("UPDATE `{$this->tableName}` SET %s WHERE %s", implode(', ', $set), implode(' AND ', $where)));
    }

    /**
     * Creates select part for query
     * @return string
     */
    private function getSelectFields(): string {
        $selectParts = [];
        foreach ($this->selectFields as $alias => $select) {
            if (is_numeric($alias)) {
                $selectParts[] = $select;
            } else {
                $selectParts[] = sprintf("%s AS `%s`", $select, $alias);
            }
        }
        return implode(', ', $selectParts);
    }

    /**
     * Returns $tableName defined by class docheader
     * example: @property string $relatedTable TABLE_NAME
     * @return string
     */
    private function getTableName(): string {
        return $this->tableName;
    }
}
