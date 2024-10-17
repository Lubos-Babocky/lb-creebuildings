<?php

namespace LB\CreeBuildings\Utils;

/**
 * Description of QueryResult
 *
 * @author Lubos Babocky <babocky@gmail.com>
 */
class QueryResult {

    public function __construct(
            protected readonly \PDOStatement $statement
    ) {
        
    }
}
