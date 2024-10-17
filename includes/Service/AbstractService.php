<?php

namespace LB\CreeBuildings\Service;

/**
 * Description of AbstractService
 *
 * @author Lubos Babocky <babocky@gmail.com>
 */
abstract class AbstractService {

    private static array $instances = [];

    abstract protected function injectDependencies(): void;

    private function __construct() {
        
    }

    private function __clone() {
        throw new \Exception("Cannot clone a singleton.");
    }

    public final function __sleep() {
        throw new \Exception("Cannot serialize a singleton.");
    }

    public final function __wakeup() {
        throw new \Exception("Cannot unserialize a singleton.");
    }

    public static function GetInstance(): static {
        if (!array_key_exists(static::class, static::$instances)) {
            static::$instances[static::class] = new static();
            static::$instances[static::class]->injectDependencies();
        }
        return static::$instances[static::class];
    }
}
