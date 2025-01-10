<?php

namespace LB\CreeBuildings\ApiDataParser\Project;

use LB\CreeBuildings\ApiDataParser\AbstractApiDataParser,
    LB\CreeBuildings\Repository\Project\ProjectParticipantRepository,
    LB\CreeBuildings\Repository\ProjectPropertyConnectionRepository,
    LB\CreeBuildings\Service\DatabaseService;

/**
 * Description of ProjectDataParser
 *
 * @author Lubos Babocky <babocky@gmail.com>
 */
class ProjectDataParser extends AbstractApiDataParser
{

    protected function modifyDataBeforeSave(): void
    {
        $this->setLocation();
        //$this->setClient();
        $this->setProperties();
        $this->setParticipants();
        $this->databaseRecord['tstamp'] = time();
    }

    private function setLocation(): void
    {
        $this->databaseRecord['location'] = implode(', ', array_filter([
            $this->apiData['street1'] ?? '',
            trim(sprintf('%s %s', $this->apiData['city'] ?? '', $this->apiData['zip'] ?? '')),
            $this->apiData['country']['name'] ?? $this->apiData['countryIso2'] ?? ''
        ]));
    }

    /**
     * Not needed anymore, remove it!
     * @return void
     * @deprecated
     */
    private function setClient(): void
    {
        foreach ($this->apiData['participants'] ?? [] as $participant) {
            if ($participant['roleKey'] ?? '' === 'CL') {
                $this->databaseRecord['client'] = $participant['target']['displayName'] ?? '';
                break;
            }
        }
    }

    private function setProperties(): void
    {
        $insertData = [];
        $projectId = $this->getProjectId();
        foreach ($this->apiData['properties'] ?? [] as $property) {
            $groupKey = $property['groupKey'] ?? throw new \Exception('GroupKey cant be empty!');
            $propertyKey = $property['propertyKey'] ?? throw new \Exception('PropertyKey cant be empty!');
            $insertData[] = [
                ':uid' => sprintf('%s-%s-%s', $projectId, $groupKey, $propertyKey),
                ':project_id' => $projectId,
                ':property_id' => sprintf('%s-%s', $groupKey, $propertyKey),
                ':property_value' => $property['value']
            ];
        }
        DatabaseService::GetInstance()
            ->getRepository(ProjectPropertyConnectionRepository::class)
            ->insertAllProjectPropertyConnections($insertData);
    }

    private function setParticipants(): void
    {
        $insertData = [];
        $projectId = $this->getProjectId();
        foreach ($this->apiData['participants'] ?? [] as $participant) {
            if (empty($participantId = $participant['target']['id'] ?? null)) {
                continue;
            }
            $insertData[] = [
                'uid' => sprintf('%s-%s', $participantId, $projectId),
                'participant_id' => $participantId,
                'participant_name' => $participant['target']['displayName'] ?? '',
                'project_id' => $projectId,
                'is_main' => (int) $participant['isMain'] ?? 0,
                'role_key' => $participant['roleKey'] ?? '',
                'subscriptions' => count($participant['target']['subscriptions'] ?? []),
                'badge_title' => $participant['target']['subscriptions'][0]['displayName'] ?? ''
            ];
        }
        DatabaseService::GetInstance()
            ->getRepository(ProjectParticipantRepository::class)
            ->saveMultipleRecords($insertData);
    }

    /**
     * @return string
     * @throws Exception when ProjectID is not set
     */
    private function getProjectId(): string
    {
        return $this->apiData['id'] ?? null
            ?: throw new \Exception('Project id cant be null!');
    }
}
