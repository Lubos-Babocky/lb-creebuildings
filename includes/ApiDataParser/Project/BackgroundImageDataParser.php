<?php

namespace LB\CreeBuildings\ApiDataParser\Project;

use LB\CreeBuildings\ApiDataParser\AbstractApiDataParser,
    LB\CreeBuildings\Repository\ProjectAttachmentRepository,
    LB\CreeBuildings\Utils\GeneralUtility;

/**
 * Description of BackgroundImageDataParser
 *
 * @author Lubos Babocky <babocky@gmail.com>
 */
class BackgroundImageDataParser extends AbstractApiDataParser
{

    public function __construct(
        protected readonly string $projectId,
        array $apiData,
        ProjectAttachmentRepository $repository,
        ?array $databaseRecord = null
    )
    {
        parent::__construct($apiData, $repository, $databaseRecord);
    }

    protected function modifyDataBeforeSave(): void
    {
        $this->databaseRecord['project_id'] = $this->projectId;
        $this->databaseRecord['source'] = 'background';
        $backgroundImageMetaData = $this->configurationService->getAdapter()
            ->generateFileAttachmentMetaData(files: $this->getPreparedBackgroundFiles());
        $this->databaseRecord['internal_url'] = $backgroundImageMetaData['file'];
        $this->databaseRecord['mime_type'] = $backgroundImageMetaData['sizes']['large']['mime-type'] ?? 'notfound';
        $this->databaseRecord['meta_data'] = serialize($backgroundImageMetaData);
    }

    private function getPreparedBackgroundFiles(): array
    {
        $rawSrcSet = ($this->apiData['srcSet']
            ?: null) ?? throw new \Exception('Src set cant be empty!');
        $files = [];
        foreach (GeneralUtility::TrimExplode($rawSrcSet, ',') as $imageSizeVariant) {
            list($url, $width) = GeneralUtility::TrimExplode($imageSizeVariant, ' ');
            $urlParts = GeneralUtility::ExtractUrlParts($url);
            if (!empty($fileType = $urlParts['query']['fileType'] ?? null)) {
                $files[] = $this->getFileInfo($url);
            }
        }
        return $files;
    }

    private function getFileInfo(
        string $filePath
    ): array
    {
        $fileContent = file_get_contents($filePath);
        $fileInfo = getimagesizefromstring($fileContent);
        return [
            'type' => GeneralUtility::ExtractUrlParts($filePath)['query']['fileType'] ?? 'PreviewLg',
            'mimeType' => $fileInfo['mime'],
            'fileSize' => strlen($fileContent),
            'resourceUri' => $filePath,
            'dimensions' => [
                'width' => $fileInfo[0],
                'height' => $fileInfo[1]
            ]
        ];
    }
}
