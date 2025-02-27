<?php

namespace LB\CreeBuildings\Repository;

/**
 * Description of ProjectPropertyConnectionRepository
 *
 * @author Lubos Babocky <babocky@gmail.com>
 * @property string $relatedTable lb_creebuildings_project_property_mm
 */
class ProjectPropertyConnectionRepository extends AbstractRepository
{

    public function findAll(): array
    {
        return $this->createQuery()->select(['*'])->execute()->fetchAll();
    }

    public function findAllProjectProperties(
        string $projectId
    ): array
    {
        return $this->pdo->query(
                "SELECT `MM`.`property_value`, `PP`.`acf_id`"
                . " FROM `lb_creebuildings_project_property_mm` AS `MM`"
                . " LEFT JOIN `lb_creebuildings_project_property` AS `PP` ON `MM`.`property_id` = `PP`.`uid`"
                . " WHERE `MM`.`project_id` = '$projectId' AND `PP`.`acf_id` != ''"
            )->fetchAll();
    }

    public function findAllConnections(): array
    {
        return $this->pdo->query(
                "SELECT `P`.`post_id`, `PP`.`acf_id`, `MM`.`property_value`"
                . " FROM `lb_creebuildings_project_property_mm` AS `MM`"
                . " LEFT JOIN `lb_creebuildings_project_property` AS `PP` ON `MM`.`property_id` = `PP`.`uid`"
                . " LEFT JOIN `lb_creebuildings_project` AS `P` ON `MM`.`project_id` = `P`.`project_id`"
                . " WHERE `PP`.`acf_id` != ''"
            )->fetchAll();
    }

    public function insertAllProjectPropertyConnections(array $insertRows): void
    {
        $this->insertMultipleRows(
            "INSERT INTO `{$this->tableName}` (`uid`, `project_id`, `property_id`, `property_value`)"
            . " VALUES(:uid, :project_id, :property_id, :property_value)"
            . " ON DUPLICATE KEY UPDATE `property_value` = VALUES(`property_value`)",
            $insertRows
        );
    }

    public function findAvailableTypesOfUse(): array
    {
        $avaliableTypesOfUse = $this->pdo
            ->query("SELECT DISTINCT property_value FROM lb_creebuildings_project_property_mm WHERE property_id = 'general-TypeOfUse'")
            ->fetchAll();
        return array_column($avaliableTypesOfUse, 'property_value');
    }

    public function findProjectCategoryRelations(): array
    {
        return $this->pdo->query(
                "SELECT `P`.`post_id`, `M`.`property_value`"
                . " FROM `lb_creebuildings_project_property_mm` AS `M`"
                . " LEFT JOIN `lb_creebuildings_project` AS `P` ON `M`.`project_id` = `P`.`project_id`"
                . " WHERE `M`.`property_id` = 'general-TypeOfUse'"
            )->fetchAll();
    }

    public function getProjectPostTagId(
        string $projectId,
        string $propertyId
    ): int
    {
        $tagName = $this->createQuery()
            ->select(fields: [
                '`property_value`'
            ])
            ->where(constraints: [
                sprintf("`project_id` = '%s'", $projectId),
                sprintf("`property_id` = '%s'", $propertyId)
            ])
            ->execute()
            ->fetchColumn();
        if (empty($tagName)) {
            return 0;
        }
        $tagId = $this->pdo
            ->query(sprintf("SELECT `term_id` FROM `wp_terms` WHERE `name` = '%s'", $tagName))
            ->fetchColumn();
        return $tagId
            ?: 0;
    }
}
