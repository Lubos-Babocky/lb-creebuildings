<?php

namespace LB\CreeBuildings\Repository;

/**
 * Description of ProjectImageRepository
 *
 * @author Lubos Babocky <babocky@gmail.com>
 * @property string $relatedTable lb_creebuildings_project_image_new
 */
class ProjectImageRepository extends AbstractRepository {

    public function insertAllProjectImages(array $insertRows): void {
        $this->insertMultipleRows(
                "INSERT INTO `{$this->tableName}`"
                . " (`uid`, `project_id`, `image_id`, `type_id`, `source_type`, `file_type`, `api_url`, `public_url`, `width`, `height`, `image_post_id`, `processed`)"
                . " VALUES(:uid, :project_id, :image_id, :type_id, :source_type, :file_type, :api_url, '', :width, :height, 0, 0)"
                . " ON DUPLICATE KEY UPDATE"
                . " `public_url` = IF(VALUES(`api_url`) <> `api_url`, '', `public_url`), "
                . " `width` = VALUES(`width`), "
                . " `height` = VALUES(`height`), "
                . " `processed` = IF(VALUES(`api_url`) <> `api_url`, 0, `processed`)",
                $insertRows
        );
    }

    public function findUnprocessedImages($limit = 50): array {
        return $this->queryBuilder
                        ->select(['*'])
                        ->where(["`processed` = 0"])
                        ->limit($limit)
                        ->execute()
                        ->fetchAll();
    }

    public function updatePublicUrl(string $imageUid, string $publicUrl): int {
        return $this->queryBuilder
                        ->update([sprintf("`public_url` = '%s'", $publicUrl), "`processed` = 1"], [sprintf("`uid` = '%s'", $imageUid)]);
    }
}
