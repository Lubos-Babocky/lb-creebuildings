<?php

namespace LB\CreeBuildings\Exception;

use LB\CreeBuildings\Exception\CriticalException;

/**
 * Description of ApiException
 * The ApiException class is used for specific errors related to API interactions.
 * It contains additional information about the called URL and the API response.
 * @author Lubos Babocky <babocky@gmail.com>
 */
class ApiException extends CriticalException
{

    public function __construct(
        string $message = "",
        protected readonly string $calledUrl = "",
        protected readonly string $apiResponse = "",
        int $code = 0,
        ?\Throwable $previous = null
    )
    {
        parent::__construct($message, $code, $previous);
    }

    public function getCalledUrl(): string
    {
        return $this->calledUrl;
    }

    public function getApiResponse(): string
    {
        return $this->apiResponse;
    }

    protected function getErrorLogMessage(): string
    {
        return sprintf(
            "%s. Calling [%s] returned: %s",
            $this->getMessage(),
            $this->getCalledUrl(),
            $this->getApiResponse()
        );
    }
}
