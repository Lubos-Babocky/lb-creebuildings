<?php

namespace LB\CreeBuildings\Repository\Project;

use LB\CreeBuildings\Repository\AbstractRepository;

/**
 * Description of ProjectParticipantRepository
 *
 * @author Lubos Babocky <babocky@gmail.com>
 * @property string $relatedTable lb_creebuildings_project_participant
 */
class ProjectParticipantRepository extends AbstractRepository
{

    public function findProjectParticipantsWithRoles(
        string $projectId
    ): array
    {
        return $this->pdo->query(
                "SELECT `P`.`participant_name`, `R`.`acf_id`, `P`.`subscriptions`, `P`.`badge_title`"
                . " FROM `lb_creebuildings_project_participant` AS `P`"
                . " LEFT JOIN `lb_creebuildings_project_participant_role` AS `R` ON `P`.`role_key` = `R`.`key`"
                . " WHERE `P`.`project_id` = '$projectId' AND `R`.`wp_meta_key` != ''"
            )->fetchAll();
    }
}
