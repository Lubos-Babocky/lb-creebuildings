<?php

namespace LB\CreeBuildings\ApiDataParser\Project;

use LB\CreeBuildings\ApiDataParser\AbstractApiDataParser,
    LB\CreeBuildings\Repository\ProjectAttachmentRepository;

/**
 * Description of AttachmentDataParser
 *
 * @author Lubos Babocky <babocky@gmail.com>
 */
class AttachmentDataParser extends AbstractApiDataParser {

    public function __construct(
            protected readonly string $projectId,
            array $apiData,
            ProjectAttachmentRepository $repository,
            ?array $databaseRecord = null
    ) {
        parent::__construct($apiData, $repository, $databaseRecord);
    }

    protected function modifyDataBeforeSave(): void {
        $this->databaseRecord['project_id'] = $this->projectId;
        $this->databaseRecord['source'] = 'gallery';
        $galleryImageMetaData = $this->configurationService->getAdapter()
                ->generateFileAttachmentMetaData(files: $this->apiData['files'] ?? []);
        $this->databaseRecord['internal_url'] = $galleryImageMetaData['file'];
        $this->databaseRecord['mime_type'] = $galleryImageMetaData['sizes']['large']['mime-type'] ?? 'notfound';
        $this->databaseRecord['meta_data'] = serialize($galleryImageMetaData);
        //[L:] unnecessary data:
        $this->databaseRecord['uid'] = sprintf('%s-%s', $this->projectId, $this->apiData['id']);
        $this->databaseRecord['processed'] = 8;
    }
}
