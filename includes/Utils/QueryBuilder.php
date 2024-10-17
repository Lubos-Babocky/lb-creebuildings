<?php

namespace LB\CreeBuildings\Utils;

/**
 * Description of QueryBuilder
 *
 * @author Lubos Babocky <babocky@gmail.com>
 */
class QueryBuilder {

    protected \PDO $pdo;
    protected array $selectFields;
    protected string $tableName;
    protected array $constraints;
    protected ?int $limit;

    public function __construct(\PDO $pdo, string $tableName) {
        $this->pdo = $pdo;
        $this->tableName = $tableName;
    }

    public function select(array $fields): self {
        $this->selectFields = $fields;
        return $this;
    }

    public function where(array $constraints): self {
        $this->constraints = $constraints;
        return $this;
    }

    public function limit(int $limit): self {
        $this->limit = $limit;
        return $this;
    }

    public function getQuery(): string {
        $query = sprintf("SELECT %s FROM `%s`", $this->getSelectFields(), $this->getTableName());
        if(!empty($this->constraints)) {
            $query .= sprintf(' WHERE %s', implode(' AND ', $this->constraints));
        }
        if(isset($this->limit)) {
            $query .= sprintf(' LIMIT %d', $this->limit);
        }
        return $query;
    }

    public function execute(): \PDOStatement {
        return $this->pdo->query($this->getQuery());
    }

    public function update(array $set, array $where) {
        return $this->pdo->exec(sprintf("UPDATE `{$this->tableName}` SET %s WHERE %s", implode(', ', $set), implode(' AND ', $where)));
    }

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

    private function getTableName(): string {
        return $this->tableName;
    }
}
