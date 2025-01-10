<?php

namespace LB\CreeBuildings\Repository;

/**
 * Description of ProjectPropertyRepository
 *
 * @author Lubos Babocky <babocky@gmail.com>
 * @property string $relatedTable lb_creebuildings_project_property
 */
class ProjectPropertyRepository extends AbstractRepository
{

    public function findActualProperties(): array
    {
        return $this->createQuery()
                ->select(fields: [
                    '*'
                ])
                ->where(constraints: [
                    sprintf("FROM_UNIXTIME(`tstamp`, '%%Y-%%m-%%d') = '%s'", date('Y-m-d'))
                ])
                ->execute()
                ->fetchAll();
    }

    public function getUsedAcfFields(): array
    {
        $results = $this->createQuery()
            ->select(fields: [
                '`wp_meta_key`'
            ])
            ->where(constraints: [
                "`wp_meta_key` != ''"
            ])
            ->execute()
            ->fetchAll();
        return array_column($results, 'wp_meta_key');
    }
}
