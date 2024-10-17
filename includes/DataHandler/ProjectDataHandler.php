<?php

namespace LB\CreeBuildings\DataHandler;

use LB\CreeBuildings\Utils\GeneralUtility,
    LB\CreeBuildings\Service\DatabaseService,
    LB\CreeBuildings\Repository\ProjectRepository,
    LB\CreeBuildings\Repository\ProjectImageRepository,
    LB\CreeBuildings\Repository\ProjectParticipantRepository,
    LB\CreeBuildings\Repository\ProjectPropertyConnectionRepository;

/**
 * Description of ProjectDataHandler
 *
 * @author Lubos Babocky <babocky@gmail.com>
 */
class ProjectDataHandler extends AbstractDataHandler {

    protected array $apiData;
    protected array $data;
    protected ProjectRepository $projectRepository;
    protected ProjectImageRepository $projectImageRepository;
    protected ProjectParticipantRepository $projectParticipantRepository;
    protected ProjectPropertyConnectionRepository $projectPropertyConnectionRepository;

    public function __construct() {
        $databaseService = DatabaseService::GetInstance();
        $this->projectRepository = $databaseService->getRepository(ProjectRepository::class);
        $this->projectImageRepository = $databaseService->getRepository(ProjectImageRepository::class);
        $this->projectParticipantRepository = $databaseService->getRepository(ProjectParticipantRepository::class);
        $this->projectPropertyConnectionRepository = $databaseService->getRepository(ProjectPropertyConnectionRepository::class);
    }

    public function processApiData(array $apiData): void {
        $this->apiData = $apiData;
        $this->data = $this->projectRepository->loadProjectData(GeneralUtility::GetMultiArrayValue($apiData, 'id'));
        $this->assignBaseData();
        $this->assignLocation();
        $this->assignClient();
        $this->assignProperties();
        $this->assignParticipants();
        $this->assignImages();
        $this->projectRepository->updateProjectSetProcessed(GeneralUtility::GetMultiArrayValue($apiData, 'id'));
    }

    protected function assignBaseData(): void {
        $this->data = [
            'tstamp' => time(),
            'project_id' => GeneralUtility::GetMultiArrayValue($this->apiData, 'id'),
            'title' => GeneralUtility::GetMultiArrayValue($this->apiData, 'displayName'),
            'access_type' => GeneralUtility::GetMultiArrayValue($this->apiData, 'accessType'),
            'sub_title' => GeneralUtility::GetMultiArrayValue($this->apiData, 'subTitle'),
            'latitude' => GeneralUtility::GetMultiArrayValue($this->apiData, 'lat'),
            'longitude' => GeneralUtility::GetMultiArrayValue($this->apiData, 'lon')
        ];
    }

    protected function assignLocation(): void {
        $locationParts = [];
        $locationParts[] = GeneralUtility::GetMultiArrayValue($this->apiData, 'street1');
        $locationParts[] = trim(sprintf(
                        '%s %s',
                        (string) GeneralUtility::GetMultiArrayValue($this->apiData, 'city'),
                        (string) GeneralUtility::GetMultiArrayValue($this->apiData, 'zip'),
                ));
        $locationParts[] = GeneralUtility::GetMultiArrayValue($this->apiData, 'country.name') ?: GeneralUtility::GetMultiArrayValue($this->apiData, 'countryIso2') ?: '';
        $this->data['location'] = implode(', ', array_filter($locationParts));
    }

    protected function assignClient(): void {
        $participants = GeneralUtility::GetMultiArrayValue($this->apiData, 'participants') ?: [];
        foreach ($participants as $participant) {
            if (GeneralUtility::GetMultiArrayValue($participant, 'roleKey') === 'CL') {
                $this->data['client'] = GeneralUtility::GetMultiArrayValue($participant, 'target.displayName');
                break;
            }
        }
    }

    protected function assignProperties(): void {
        $insertData = [];
        foreach (GeneralUtility::GetMultiArrayValue($this->apiData, 'properties') as $property) {
            $insertData[] = [
                ':uid' => sprintf(
                        '%s-%s-%s',
                        GeneralUtility::GetMultiArrayValue($this->apiData, 'id'),
                        GeneralUtility::GetMultiArrayValue($property, 'groupKey'),
                        GeneralUtility::GetMultiArrayValue($property, 'propertyKey')
                ),
                ':project_id' => GeneralUtility::GetMultiArrayValue($this->apiData, 'id'),
                ':property_id' => sprintf(
                        '%s-%s',
                        GeneralUtility::GetMultiArrayValue($property, 'groupKey'),
                        GeneralUtility::GetMultiArrayValue($property, 'propertyKey')
                ),
                ':property_value' => GeneralUtility::GetMultiArrayValue($property, 'value')
            ];
            $this->projectPropertyConnectionRepository->insertAllProjectPropertyConnections($insertData);
        }
    }

    protected function assignParticipants(): void {
        $insertData = [];
        foreach (GeneralUtility::GetMultiArrayValue($this->apiData, 'participants') as $participant) {
            if (empty(GeneralUtility::GetMultiArrayValue($participant, 'target.id'))) {
                continue;
            }
            $insertData[] = [
                ':uid' => sprintf(
                        '%s-%s',
                        GeneralUtility::GetMultiArrayValue($participant, 'target.id'),
                        GeneralUtility::GetMultiArrayValue($this->apiData, 'id')
                ),
                ':participant_id' => GeneralUtility::GetMultiArrayValue($participant, 'target.id'),
                ':participant_name' => GeneralUtility::GetMultiArrayValue($participant, 'target.displayName'),
                ':project_id' => GeneralUtility::GetMultiArrayValue($this->apiData, 'id'),
                ':is_main' => (int) GeneralUtility::GetMultiArrayValue($participant, 'isMain'),
                ':role_key' => GeneralUtility::GetMultiArrayValue($participant, 'roleKey'),
            ];
        }
        $this->projectParticipantRepository->insertAllProjectParticipants($insertData);
    }

    protected function assignImages(): void {
        $insertData = [];
        if (!empty($backgroungImage = GeneralUtility::GetMultiArrayValue($this->apiData, 'background'))) {
            $this->attachBackgroundImagesToInsertData($insertData, $backgroungImage);
            //$insertData[] = $this->getImageInsertData($backgroungImage, 'background');
        }
        foreach (GeneralUtility::GetMultiArrayValue($this->apiData, 'attachments', []) as $attachmentImage) {
            $this->attachGalleryImagesToInsertData($insertData, $attachmentImage);
            //$insertData[] = $this->getImageInsertData($attachmentImage, 'attachments');
        }
        $this->projectImageRepository->insertAllProjectImages($insertData);
    }

    protected function getImageInsertData(array $img, string $sourceType): array {
        return [
            ':uid' => sprintf(
                    '%s-%s',
                    GeneralUtility::GetMultiArrayValue($this->apiData, 'id', ''),
                    GeneralUtility::GetMultiArrayValue($img, 'id', '')
            ),
            ':title' => GeneralUtility::GetMultiArrayValue($img, 'displayName', ''),
            ':file_name' => GeneralUtility::GetMultiArrayValue($img, 'filename', ''),
            ':project_id' => GeneralUtility::GetMultiArrayValue($this->apiData, 'id', ''),
            ':image_id' => GeneralUtility::GetMultiArrayValue($img, 'id', ''),
            ':url' => GeneralUtility::GetMultiArrayValue($img, 'attachmentUri', ''),
            ':source_type' => $sourceType
        ];
    }

    /**
     * @param array $insertData
     * @param array $imageData
     * @param string $sourceType
     * @return void
     */
    protected function attachBackgroundImagesToInsertData(array &$insertData, array $imageData): void {
        $srcSet = sprintf('%s 0w,%s', GeneralUtility::GetMultiArrayValue($imageData, 'attachmentUri', ''), GeneralUtility::GetMultiArrayValue($imageData, 'srcSet', ''));
        foreach (GeneralUtility::TrimExplode($srcSet, ',') as $imageSet) {
            list($apiUrl, $width) = GeneralUtility::TrimExplode($imageSet, ' ');
            $apiUrlData = GeneralUtility::ExtractUrlParts($apiUrl);
            $projectId = GeneralUtility::GetMultiArrayValue($this->apiData, 'id', '');
            $imageId = GeneralUtility::GetMultiArrayValue($imageData, 'id', '');
            $fileType = GeneralUtility::GetMultiArrayValue($apiUrlData, 'query.fileType', '');
            $insertData[] = [
                ':uid' => sprintf('%s-%s-%s', $projectId, $imageId, $fileType),
                ':project_id' => $projectId,
                ':image_id' => $imageId,
                ':type_id' => '',
                ':source_type' => 'background',
                ':file_type' => $fileType,
                ':api_url' => $apiUrl,
                ':width' => (int) GeneralUtility::TrimExplode($width, 'w')[0],
                ':height' => 0,
            ];
        }
    }

    protected function attachGalleryImagesToInsertData(array &$insertData, array $imageData): void {
        foreach (GeneralUtility::GetMultiArrayValue($imageData, 'files', []) as $file) {
            $projectId = GeneralUtility::GetMultiArrayValue($this->apiData, 'id', '');
            $imageId = GeneralUtility::GetMultiArrayValue($imageData, 'id', '');
            $typeId = GeneralUtility::GetMultiArrayValue($file, 'id', '');
            $insertData[] = [
                ':uid' => sprintf('%s-%s-%s', $projectId, $imageId, $typeId),
                ':project_id' => $projectId,
                ':image_id' => $imageId,
                ':type_id' => $typeId,
                ':source_type' => 'gallery',
                ':file_type' => GeneralUtility::GetMultiArrayValue($file, 'type', ''),
                ':api_url' => GeneralUtility::GetMultiArrayValue($file, 'resourceUri', ''),
                ':width' => GeneralUtility::GetMultiArrayValue($file, 'dimensions.width', 0),
                ':height' => GeneralUtility::GetMultiArrayValue($file, 'dimensions.height', 0),
            ];
        }
    }
}
