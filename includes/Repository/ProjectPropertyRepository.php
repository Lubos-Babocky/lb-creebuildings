<?php

namespace LB\CreeBuildings\Repository;

/**
 * Description of ProjectPropertyRepository
 *
 * @author Lubos Babocky <babocky@gmail.com>
 * @property string $relatedTable lb_creebuildings_project_property
 */
class ProjectPropertyRepository extends AbstractRepository {

    public function getLastUpdate(): string {
        return $this->createQuery()
                        ->select(['last_update' => "MAX(FROM_UNIXTIME(`tstamp`, '%Y-%m-%d'))"])
                        ->where([])
                        ->execute()
                        ->fetchColumn();
    }

    public function insertAllBaseProjectPropertyData(array $insertRows): void {
        $this->insertMultipleRows(
                "INSERT INTO `{$this->tableName}` (`uid`, `crdate`, `tstamp`, `group_id`, `group_name`, `property_id`, `property_name`)"
                . " VALUES(:uid, UNIX_TIMESTAMP(), UNIX_TIMESTAMP(), :group_id, :group_name, :property_id, :property_name)"
                . " ON DUPLICATE KEY UPDATE `tstamp` = UNIX_TIMESTAMP(), `group_name` = VALUES(`group_name`), `property_name` = VALUES(`property_name`)",
                $insertRows);
    }

    public function findActualProperties(): array {
        return $this->createQuery()
                        ->select(['*'])
                        ->where([sprintf("FROM_UNIXTIME(`tstamp`, '%%Y-%%m-%%d') = '%s'", date('Y-m-d'))])
                        ->execute()
                        ->fetchAll();
    }
}
