<?php

namespace LB\CreeBuildings\Repository\Project;

use LB\CreeBuildings\Repository\AbstractRepository;

/**
 * Description of BackgroundImageRepository
 *
 * @author Lubos Babocky <babocky@gmail.com>
 * @property string $relatedTable lb_creebuildings_project_background
 */
class BackgroundImageRepository extends AbstractRepository {

    public function findByUid(string $uid): bool|array {
        return $this->createQuery()
                        ->select(['*'])
                        ->where([sprintf("`uid` = '%s'", $uid)])
                        ->execute()
                        ->fetch();
    }
}
