<?php

namespace LB\CreeBuildings\Service;

/**
 * Description of CreeApiService
 * @author Lubos Babocky <babocky@gmail.com>
 */
class CreeApiService extends AbstractService {

    protected ConfigurationService $configurationService;

    protected function injectDependencies(): void {
        $this->configurationService = ConfigurationService::GetInstance();
    }

    public function loadAllProjects(): array {
        $result = json_decode($this->executeApiCall('Workspaces/find?searchExpression=types%%3Aconstruction&page=1&pageSize=1000&debugOutput=false'), true);
        array_key_exists('records', $result) && !empty($result['records']) ?: throw new \Exception("No results returned from API");
        return $result['records'];
    }

    public function loadProject(string $projectId): array {
        $result = json_decode($this->executeApiCall(sprintf('Workspaces/%s', $projectId)), true);
        return $result;
    }

    public function loadProjectPropertyList(): array {
        return json_decode($this->executeApiCall('Metadata/find/properties'), true);
    }

    public function loadSearches(): array {
        return json_decode($this->executeApiCall('Searches/find/mine?maxRecords=100'), true);
    }

    public function loadImage($imageApiUrl): array {
        return json_decode($this->executeApiCall(sprintf('%s&redirect=false', $imageApiUrl), true), true);
    }

    private function executeApiCall(string $urlPart, bool $absoluteUrl = false): string {
        $apiUrl = $absoluteUrl ? $urlPart : sprintf('%s/%s', rtrim($this->configurationService->getConfig('CREEAPI_BASE_URL'), '/'), ltrim($urlPart, '/'));
        try {
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $apiUrl);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_HTTPHEADER, [
                $this->configurationService->getConfig('CREEAPI_HEADER_ACCEPT'),
                sprintf(
                        '%s: %s',
                        $this->configurationService->getConfig('CREEAPI_KEY_NAME'),
                        $this->configurationService->getConfig('CREEAPI_KEY_VALUE')
                )
            ]);
            return curl_exec($ch) ?: throw new \Exception(sprintf('API response is empty for [%s]', $apiUrl));
        } finally {
            curl_close($ch);
        }
    }
}
