<?php

namespace LB\CreeBuildings\Exception;

use Exception;
use LB\CreeBuildings\Service\LogService;

/**
 * Description of CriticalException
 *
 * @author Lubos Babocky <babocky@gmail.com>
 */
abstract class CriticalException extends Exception
{

    abstract protected function getErrorLogMessage(): string;

    public function __construct(string $message = "", int $code = 0, ?\Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }

    public function handle(): never
    {
        $errorMessage = $this->getErrorLogMessage();
        LogService::GetInstance()->writeErrorLog($errorMessage);
        die($errorMessage);
    }
}
