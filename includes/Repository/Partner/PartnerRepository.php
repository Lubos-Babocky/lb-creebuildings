<?php

namespace LB\CreeBuildings\Repository\Partner;

use LB\CreeBuildings\Repository\AbstractRepository;

/**
 * Description of PartnerRepository
 *
 * @author Lubos Babocky <babocky@gmail.com>
 * @property string $relatedTable lb_creebuildings_partner
 */
class PartnerRepository extends AbstractRepository {

    /**
     * Sets current unixtime to tstamp field
     * @param string $partnerId
     * @return void
     */
    public function updateTstamp(
            string $partnerId
    ): void {
        $this->createQuery()->update(
                set: [sprintf("`tstamp` = %d", time())],
                where: [sprintf("`id` = '%s'", $partnerId)]
        );
    }

    public function findMaxTstamp(): int {
        return (int) $this->createQuery()
                        ->select(fields: ['MAX(`tstamp`)'])
                        ->execute()
                        ->fetchColumn();
    }

    /**
     * For testing
     * @param string $partnerId
     * @return void
     * @deprecated remove after testing
     */
    public function forcePartnerForImport(
            string $partnerId
    ): void {
        $this->createQuery()
                ->update(set: ["`tstamp` = 0"], where: ['1=1']);
        $this->createQuery()
                ->update(set: ["`modified` = ''"], where: [sprintf("`id` = '%s'", $partnerId)]);
    }
}
