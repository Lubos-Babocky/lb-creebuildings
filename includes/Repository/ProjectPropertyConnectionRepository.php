<?php

namespace LB\CreeBuildings\Repository;

/**
 * Description of ProjectPropertyConnectionRepository
 *
 * @author Lubos Babocky <babocky@gmail.com>
 * @property string $relatedTable lb_creebuildings_project_property_mm
 */
class ProjectPropertyConnectionRepository extends AbstractRepository {

    public function insertAllProjectPropertyConnections(array $insertRows): void {
        $this->insertMultipleRows(
                "INSERT INTO `{$this->tableName}` (`uid`, `project_id`, `property_id`, `property_value`)"
                . " VALUES(:uid, :project_id, :property_id, :property_value)"
                . " ON DUPLICATE KEY UPDATE `property_value` = VALUES(`property_value`)",
                $insertRows
        );
    }
}
