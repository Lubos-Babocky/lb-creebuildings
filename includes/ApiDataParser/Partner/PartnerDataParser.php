<?php

namespace LB\CreeBuildings\ApiDataParser\Partner;

use LB\CreeBuildings\ApiDataParser\AbstractApiDataParser;

/**
 * Description of PartnerDataParser
 *
 * @author Lubos Babocky <babocky@gmail.com>
 */
class PartnerDataParser extends AbstractApiDataParser
{

    protected function modifyDataBeforeSave(): void
    {
        $this->databaseRecord['id'] ??= $this->apiData['id'] ?? throw new \Exception('missing id, this is not possible but handle error anyway');
        $this->databaseRecord['tstamp'] = time();
    }
}
