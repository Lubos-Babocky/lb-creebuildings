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

    public function updateProjectSetProcessed(string $projectId, bool $processed = true): bool {
        return $this->createQuery()
                ->update([sprintf('`processed` = %d', $processed ? 1 : 0)], [sprintf("`project_id` = '%s'", $projectId)]);
    }
}
