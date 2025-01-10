<?php

namespace LB\CreeBuildings\Service;

use CurlHandle;
use LB\CreeBuildings\Exception\ApiException;
use LB\CreeBuildings\Configuration\ApiEndpointInterface;

/**
 * Description of CreeApiService
 * Executes calls to CreeAPI and returns response in associative array
 * @author Lubos Babocky <babocky@gmail.com>
 */
class CreeApiService extends AbstractService implements ApiEndpointInterface
{

    protected ConfigurationService $configurationService;
    protected CurlHandle $curlHandle;

    /**
     * Prepares class properties instead of constructor
     * @return void
     */
    protected function injectDependencies(): void
    {
        $this->configurationService = ConfigurationService::GetInstance();
        $this->prepareRequest();
    }

    /**
     * Prepare and configure cURL request
     * @return void
     */
    protected function prepareRequest(): void
    {
        $this->curlHandle = curl_init();
        if (!$this->curlHandle) {
            throw new ApiException(message: 'Failed to initialize cURL');
        }
        curl_setopt($this->curlHandle, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($this->curlHandle, CURLOPT_ENCODING, 'gzip, deflate');
        curl_setopt($this->curlHandle, CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_2_0);
        curl_setopt($this->curlHandle, CURLOPT_DNS_CACHE_TIMEOUT, 300);
        curl_setopt($this->curlHandle, CURLOPT_CONNECTTIMEOUT, 5);
        curl_setopt($this->curlHandle, CURLOPT_HTTPHEADER, [
            'Connection: keep-alive',
            $this->configurationService->getConfig('CREEAPI_HEADER_ACCEPT'),
            sprintf(
                '%s: %s',
                $this->configurationService->getConfig('CREEAPI_KEY_NAME'),
                $this->configurationService->getConfig('CREEAPI_KEY_VALUE')
            )
        ]);
    }

    /**
     * Closes curl handle at end of class lifetime
     */
    public function __destruct()
    {
        curl_close($this->curlHandle);
    }

    /**
     * Loads all partners data from CreeAPI
     * @return array
     */
    public function loadAllPartners(): array
    {
        $apiResponse = $this->executeApiCall(self::API_ENDPOINT_PARTNER_LIST);
        return $apiResponse['records'] ?? null
            ?: throw new ApiException(
                message: 'Required key "records" is missing or empty in API response!',
                apiResponse: json_encode($apiResponse),
                calledUrl: $this->convertEndpointToApiUrl(self::API_ENDPOINT_PARTNER_LIST)
            );
    }

    /**
     * Loads all project data from CreeAPI
     * @return array
     * @throws ApiException on wrong response
     */
    public function loadAllProjects(): array
    {
        $apiResponse = $this->executeApiCall(self::API_ENDPOINT_PROJECT_LIST);
        return $apiResponse['records'] ?? null
            ?: throw new ApiException(
                message: 'Required key "records" is missing or empty in API response!',
                apiResponse: json_encode($apiResponse),
                calledUrl: $this->convertEndpointToApiUrl(self::API_ENDPOINT_PROJECT_LIST)
            );
    }

    /**
     * Loads single project data
     * @param string $projectId
     * @return array
     */
    public function loadProject(
        string $projectId
    ): array
    {
        $apiEndpoint = self::API_ENDPOINT_PROJECT_DETAIL;
        $apiEndpoint['url'] .= $projectId;
        return $this->executeApiCall($apiEndpoint);
    }

    /**
     * Loads project property data
     * @return array
     */
    public function loadProjectPropertyList(): array
    {
        return $this->executeApiCall(self::API_ENDPOINT_METADATA_PROPERTIES);
    }

    /**
     * Executes cURL request to CreeAPI
     *
     * @param array $apiEndpointConfigArray Configuration array for the API endpoint.
     * @return array Decoded JSON response from the API.
     * @throws ApiException If the API response is invalid JSON or the cURL request fails.
     */
    private function executeApiCall(
        array $apiEndpointConfigArray
    ): array
    {
        $apiEndpointUrl = $this->convertEndpointToApiUrl($apiEndpointConfigArray);
        curl_setopt($this->curlHandle, CURLOPT_URL, $apiEndpointUrl);
        $responseBody = curl_exec($this->curlHandle);
        if ($responseBody === false) {
            throw new ApiException(
                    message: 'CURL request failed: ' . curl_error($this->curlHandle),
                    calledUrl: $apiEndpointUrl
                );
        }
        $jsonResponse = json_decode($responseBody, true);
        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new ApiException(
                    message: 'Invalid JSON response',
                    calledUrl: $apiEndpointUrl,
                    apiResponse: $responseBody
                );
        }
        return $jsonResponse;
    }

    /**
     * Converts
     * @param array $apiEndpoint Configuration array containing 'url' and optionally 'query'.
     * @return string The full API URL.
     */
    private function convertEndpointToApiUrl(
        array $apiEndpoint
    ): string
    {
        $url = sprintf(
            '%s/%s',
            rtrim($this->configurationService->getConfig('CREEAPI_BASE_URL'), '/'),
            trim($apiEndpoint['url'])
        );
        return empty($apiEndpoint['query'] ?? null)
            ? $url
            : sprintf('%s?%s', $url, ltrim(http_build_query($apiEndpoint['query']), '?'));
    }
}
