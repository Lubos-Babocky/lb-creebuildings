<?php

namespace LB\CreeBuildings\Utils;

/**
 * Description of ImportDataCleaner
 *
 * @author Lubos Babocky <babocky@gmail.com>
 */
class ImportDataCleaner
{

    public function resetProjectForImport(
        string $projectId
    ): void
    {
        \LB\CreeBuildings\Service\DatabaseService::GetInstance()
            ->getRepository(\LB\CreeBuildings\Repository\ProjectRepository::class)
            ->createQuery()
            ->update(
                set: ["`modified` = ''"],
                where: [sprintf("`project_id` = '%s'", $projectId)]
            );
    }
}
