<?php

namespace LB\CreeBuildings\Repository;

/**
 * Description of ProjectRepository
 *
 * @author Lubos Babocky <babocky@gmail.com>
 * @property string $relatedTable lb_creebuildings_project
 */
class ProjectRepository extends AbstractRepository {

    public function loadProjectData(string $projectId): array {
        $results = $this->createQuery()
                ->select(['*'])
                ->where([sprintf("`project_id` = '%s'", $projectId)])
                ->execute()
                ->fetchAll();
        return match (count($results)) {
            0 => throw new \Exception('Project not found'),
            1 => $results[0],
            default => throw new \Exception('More projects found for same projectId, this should not be possible!')
        };
    }

    public function getLastUpdate(): string {
        return $this->createQuery()
                        ->select(['last_update' => "MAX(FROM_UNIXTIME(`tstamp`, '%Y-%m-%d'))"])
                        ->where([])
                        ->execute()
                        ->fetchColumn();
    }

    public function insertAllBaseProjectData(array $insertRows): void {
        $this->insertMultipleRows(
                "INSERT INTO `{$this->tableName}` (`project_id`, `access_type`, `crdate`, `tstamp`) "
                . "VALUES(:projectId, :accessType, UNIX_TIMESTAMP(), UNIX_TIMESTAMP()) "
                . "ON DUPLICATE KEY UPDATE `processed` = 0, `tstamp` = UNIX_TIMESTAMP()",
                $insertRows
        );
    }

    public function loadUnporcessedProjects(): array {
        return $this->createQuery()
                        ->select(['project_id'])
                        ->where(['`processed` = 0', "`access_type` = 'Public'"])
                        ->execute()
                        ->fetchAll();
    }

    public function findPublicProjects(): array {
        return $this->createQuery()
                        ->select(['*'])
                        ->where(["`access_type` = 'Public'"])
                        ->execute()
                        ->fetchAll();
    }

    public function updateProjectSetProcessed(string $projectId, bool $processed = true): bool {
        return $this->createQuery()
                        ->update([sprintf('`processed` = %d', $processed ? 1 : 0)], [sprintf("`project_id` = '%s'", $projectId)]);
    }

    public function findProjectsDataForAdminPage(): array {
        return $this->pdo
                        ->query(
                                "SELECT `P`.`title`, `P`.`project_id`, `P`.`post_id`, `P`.`tstamp`, `P`.`type_of_use`, `P`.`project_stage`, `WP`.`post_status`, (SELECT COUNT(DISTINCT `image_id`) FROM `lb_creebuildings_project_image_new` WHERE `project_id` = `P`.`project_id`) AS `images`"
                                . " FROM `lb_creebuildings_project` AS `P`"
                                . " LEFT JOIN `wp_posts` AS `WP` ON `P`.`post_id` = `WP`.`ID`"
                                . " WHERE `P`.`access_type` = 'Public'"
                                . " ORDER BY `P`.`title` ASC"
                        )
                        ->fetchAll();
    }

    public function updateProjectPostStatus(string $projectId, string $postStatus): int {
        return $this->pdo->exec(
                        sprintf(
                                "UPDATE `wp_posts` SET `post_status` = '%s' WHERE `ID` = (SELECT `post_id` FROM `{$this->tableName}` WHERE `project_id` = '%s')",
                                ($postStatus === 'publish') ? 'publish' : 'draft',
                                $projectId
                        )
                );
    }

    public function updateProjectPostID(string $projectId, int $postId): int {
        return $this->pdo->exec(sprintf("UPDATE `{$this->tableName}` SET `post_id` = %d WHERE `post_id` = '%s'", $postId, $projectId));
    }
}
