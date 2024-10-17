<?php

namespace LB\CreeBuildings\Repository;

/**
 * Description of ProjectParticipantRepository
 *
 * @author Lubos Babocky <babocky@gmail.com>
 * @property string $relatedTable lb_creebuildings_project_participant
 */
class ProjectParticipantRepository extends AbstractRepository {

    public function insertAllProjectParticipants(array $insertRows): void {
        $this->insertMultipleRows(
                "INSERT INTO `{$this->tableName}` (`uid`, `participant_id`, `participant_name`, `project_id`, `is_main`, `role_key`)"
                . " VALUES(:uid, :participant_id, :participant_name, :project_id, :is_main, :role_key)"
                . " ON DUPLICATE KEY UPDATE `participant_name` = VALUES(`participant_name`), `is_main` = VALUES(`is_main`), `role_key` = VALUES(`role_key`)",
                $insertRows
        );
    }
}
