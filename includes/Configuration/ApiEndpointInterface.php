<?php

namespace LB\CreeBuildings\Configuration;

/**
 * Description of ApiEndpointInterface
 * @author Lubos Babocky <babocky@gmail.com>
 */
interface ApiEndpointInterface
{

    const API_ENDPOINT_PARTNER_LIST = [
        'url' => 'Companies/find',
        'query' => [
            'staticFilter' => 'iscreepartner:yes',
            'sortBy' => 'Alphabetical',
            'page' => 1,
            'pageSize' => 100,
            'debugOutput' => 'false'
        ]
    ];
    const API_ENDPOINT_PROJECT_LIST = [
        'url' => 'Workspaces/find',
        'query' => [
            'searchExpression' => 'access:public types:construction',
            'page' => 1,
            'pageSize' => 100,
            'debugOutput' => 'false'
        ]
    ];
    const API_ENDPOINT_PROJECT_DETAIL = [
        'url' => 'Workspaces/'
    ];
    const API_ENDPOINT_METADATA_PROPERTIES = [
        'url' => 'Metadata/find/properties'
    ];
}
