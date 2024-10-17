<?php

namespace LB\CreeBuildings\DataHandler;

use LB\CreeBuildings\Service\CreeApiService,
    LB\CreeBuildings\Repository\ProjectImageRepository,
    LB\CreeBuildings\Service\DatabaseService,
    LB\CreeBuildings\Utils\GeneralUtility;

/**
 * Description of ProjectImageDataHandler
 *
 * @author Lubos Babocky <babocky@gmail.com>
 */
class ProjectImageDataHandler extends AbstractDataHandler {

    protected CreeApiService $creeApiService;
    protected ProjectImageRepository $projectImageRepository;

    public function __construct(
            protected readonly array $imageData
    ) {
        $this->creeApiService = CreeApiService::GetInstance();
        $this->projectImageRepository = DatabaseService::GetInstance()
                ->getRepository(ProjectImageRepository::class);
    }

    public function processImage(): void {
        $apiResponse = $this->creeApiService
                ->loadImage(GeneralUtility::GetMultiArrayValue($this->imageData, 'api_url'));
        $imageUri = urldecode(GeneralUtility::GetMultiArrayValue($apiResponse, 'uri', ''));
        $this->projectImageRepository
                ->updatePublicUrl(GeneralUtility::GetMultiArrayValue($this->imageData, 'uid'), $imageUri);
    }
}
