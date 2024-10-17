<?php

namespace LB\CreeBuildings\Repository;

/**
 * Description of ProjectImageCopyRepository
 *
 * @author Lubos Babocky <babocky@gmail.com>
 * @property string $relatedTable lb_creebuildings_project_image_copy
 */
class ProjectImageCopyRepository extends AbstractRepository {

    public function insertAllProjectImages(array $insertRows): void {
        $this->insertMultipleRows(
                "INSERT INTO `{$this->tableName}` (`uid`, `crdate`, `tstamp`, `title`, `file_name`, `project_id`, `image_id`, `url`, `source_type`)"
                . " VALUES(:uid, UNIX_TIMESTAMP(), UNIX_TIMESTAMP(), :title, :file_name, :project_id, :image_id, :url, :source_type)"
                . " ON DUPLICATE KEY UPDATE"
                . " `processed` = IF(VALUES(`url`) <> `url` OR VALUES(`file_name`) <> `file_name`, 0, `processed`),"
                . " `tstamp` = UNIX_TIMESTAMP(),"
                . " `title` = VALUES(`title`),"
                . " `file_name` = VALUES(`file_name`),"
                . " `url` = VALUES(`url`),"
                . " `source_type` = VALUES(`source_type`)",
                $insertRows
        );
    }
}
