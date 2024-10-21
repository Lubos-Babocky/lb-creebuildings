<?php

namespace LB\CreeBuildings\Controller;

/**
 * Description of AbstractController
 *
 * @author Lubos Babocky <babocky@gmail.com>
 */
class AbstractController {

    public function __construct() {
        echo '<pre>';
        var_dump(static::class);
        die(__METHOD__ . '::' . __LINE__);
    }
}
