<?php

namespace LB\CreeBuildings\Repository;

/**
 * Description of ProjectAttachmentRepository
 *
 * @author Lubos Babocky <babocky@gmail.com>
 * @property string $relatedTable lb_creebuildings_project_attachment
 */
class ProjectAttachmentRepository extends AbstractRepository {

    public function findProjectBackground(
            string $projectId
    ): array {
        return $this->createQuery()
                        ->select(['*'])
                        ->where(constraints: [
                            sprintf("`project_id` = '%s'", $projectId),
                            "`source` = 'background'"
                        ])
                        ->execute()
                        ->fetch() ?: [];
    }

    public function findProjectGalleryImages(
            string $projectId
    ): array {
        return $this->createQuery()
                        ->select(['*'])
                        ->where(constraints: [
                            sprintf("`project_id` = '%s'", $projectId),
                            "`source` = 'gallery'"
                        ])
                        ->execute()
                        ->fetchAll();
    }

    public function deleteByProjectId(
            string $projectId
    ): void {
        $this->pdo->exec(sprintf("DELETE FROM `{$this->tableName}` WHERE `project_id` = '%s'", $projectId));
    }

    public function findUnprocessedVideoAttachments(): bool|array {
        $results = $this->createQuery()
                ->select(['*'])
                ->where([
                    "`mime_type` LIKE 'video/%'",
                    "`processed` = 0"
                ])
                ->execute()
                ->fetchAll();
        return $results ?: [];
    }

    public function findById(string $id): bool|array {
        return $this->createQuery()
                        ->select(['*'])
                        ->where([sprintf("`file_id` = '%s'", $id)])
                        ->execute()
                        ->fetch();
    }

    public function findByUid(string $uid): bool|array {
        return $this->createQuery()
                        ->select(['*'])
                        ->where([sprintf("`uid` = '%s'", $uid)])
                        ->execute()
                        ->fetch();
    }

    public function insertOrUpdateImageRecord(
            array $insertData
    ): ?string {
        $query = "INSERT INTO `{$this->tableName}` (`uid`, `modified`, `project_id`, `file_id`, `source`, `api_url`, `internal_url`, `mime_type`, `meta_data`)"
                . " VALUES(:uid, :modified, :project_id, :file_id, :source, :api_url, :internal_url, :mime_type, :meta_data)"
                . " ON DUPLICATE KEY UPDATE"
                . " `modified` = VALUES(`modified`),"
                . " `api_url` = VALUES(`api_url`),"
                . " `internal_url` = VALUES(`internal_url`),"
                . " `mime_type` = VALUES(`mime_type`),"
                . " `meta_data` = VALUES(`meta_data`),"
                . " `processed` = IF(`modified` <> VALUES(`modified`), 0, `processed`)";
        return $this->insertSingleRow($query, $insertData);
    }

    public function findAllProjectGalleryImages(): array {
        $query = "SELECT `I`.*, `P`.`post_id`"
                . " FROM  `{$this->tableName}` AS `I`"
                . " LEFT JOIN `lb_creebuildings_project` AS `P` ON `I`.`project_id` = `P`.`project_id`"
                . " WHERE `I`.`processed` = 0 AND `mime_type` LIKE 'image%' AND `source` = 'gallery'";
        return $this->pdo
                        ->query($query)
                        ->fetchAll();
    }

    public function findAllProjectBackgrounds(): array {
        $query = "SELECT `I`.*, `P`.`post_id`"
                . " FROM  `{$this->tableName}` AS `I`"
                . " LEFT JOIN `lb_creebuildings_project` AS `P` ON `I`.`project_id` = `P`.`project_id`"
                . " WHERE `I`.`processed` = 0 AND `mime_type` LIKE 'image%' AND `source` = 'background'";
        return $this->pdo
                        ->query($query)
                        ->fetchAll();
    }

    public function findAllPublicProjectsAndTheirGalleryAttachments(): array {
        $query = "SELECT `P`.`post_id`, GROUP_CONCAT(`A`.`attachment_post_id` SEPARATOR ',') AS `attachments`"
                . " FROM `{$this->tableName}` AS `A`"
                . " LEFT JOIN `lb_creebuildings_project` AS `P` ON `A`.`project_id` = `P`.`project_id`"
                . " GROUP BY `P`.`post_id`";
        return $this->pdo
                        ->query($query)
                        ->fetchAll();
    }

    public function updateAttachmentPostId(
            string $attachmentId,
            int $attachmentPostId
    ): int|bool {
        return $this->executeQuery(sprintf(
                                "UPDATE `{$this->tableName}` SET `attachment_post_id` = %d WHERE `file_id` = '%s'",
                                $attachmentPostId,
                                $attachmentId
                        ));
    }

    /**
     * Sets attachment record to processed
     * @param string $attachmentUid
     * @return bool
     */
    public function setAttachmentToProcessed(
            string $attachmentUid
    ): int|bool {
        return $this->executeQuery(sprintf("UPDATE `{$this->tableName}` SET `processed` = 1 WHERE `uid` = '%s'", $attachmentUid));
    }

    public function removePostAttachmentConnection(
            int $attachmentPostId
    ): int|bool {
        return $this->executeQuery(sprintf("UPDATE `{$this->tableName}` SET `attachment_post_id` = 0 WHERE `attachment_post_id` = '%d'", $attachmentPostId));
    }
}
