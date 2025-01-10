<?php

namespace LB\CreeBuildings\ApiDataParser;

/**
 * Description of ParserResult
 *
 * @author Lubos Babocky <babocky@gmail.com>
 */
class ParserResult {

    public function __construct(
            protected readonly array $data
    ) {
        
    }

    /**
     * Get the imported data as an array
     * @return array
     */
    public function getAsArray(): array {
        return $this->data;
    }
}
