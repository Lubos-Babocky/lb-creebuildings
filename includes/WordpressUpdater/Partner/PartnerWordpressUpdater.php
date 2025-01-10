<?php

namespace LB\CreeBuildings\WordpressUpdater\Partner;

use LB\CreeBuildings\WordpressUpdater\AbstractWordpressUpdater,
    LB\CreeBuildings\Repository\Yith\StoreRepository,
    LB\CreeBuildings\Repository\Partner\PartnerRepository,
    LB\CreeBuildings\Utils\FileUtility,
    LB\CreeBuildings\Service\DatabaseService;

/**
 * Description of PartnerWordpressUpdater
 *
 * @author Lubos Babocky <babocky@gmail.com>
 */
class PartnerWordpressUpdater extends AbstractWordpressUpdater
{

    public function __construct(
        array $updateData,
        PartnerRepository $repository
    )
    {
        parent::__construct(updateData: $updateData, repository: $repository);
    }

    protected function updateWordpressTables(): void
    {
        $this->insertOrUpdateWordpressPost();
        $this->updatePostAttachment();
        $this->upsertYithData();
        $this->setYithPostMetaData();
        DatabaseService::GetInstance()
            ->getRepository(PartnerRepository::class)
            ->saveRecord($this->updateData);
    }

    /**
     * Inserts or updates store post, after insert it automatically updates post_id
     * @return void
     */
    private function insertOrUpdateWordpressPost(): void
    {
        $this->updateData['post_id'] = $this->configurationService->getAdapter()
            ->upsertPartnerPost(
                postId: $this->updateData['post_id'] ?? null,
                postTitle: $this->updateData['title'],
                postContent: $this->updateData['title'],
                postAuthor: $this->configurationService->getConfig('WP_PROJECT_POST_AUTHOR_ID'),
                postType: $this->configurationService->getConfig('WP_PARTNER_POST_TYPE')
            );
    }

    private function updatePostAttachment(): void
    {
        if (empty($this->updateData['avatar_api_url'])) {
            return;   //[L:] no image for update, maybe use some default logo?
        }
        if (empty($this->updateData['avatar_storage_path']) || !is_readable($this->updateData['avatar_storage_path'])) {
            $this->updateData['avatar_storage_path'] = (new FileUtility())
                ->createImageFromUrl(
                    imageName: sprintf('partner-%s', $this->updateData['id']),
                    imageUrl: $this->updateData['avatar_api_url']
                );
        }
        $this->updateData['avatar_post_id'] = $this->configurationService
            ->getAdapter()
            ->upsertPostAttachment(
                postId: $this->updateData['post_id'],
                attachmentId: $this->updateData['avatar_post_id'] ?? null,
                localFilePath: $this->updateData['avatar_storage_path'] ?? null,
            );
    }

    /**
     * Inserts or updates record in wp_yith_sl_stores_lookup table,
     * data validation happens in AbstractRepository
     * @return void
     */
    private function upsertYithData(): void
    {
        DatabaseService::GetInstance()
            ->getRepository(StoreRepository::class)
            ->saveRecord([
                'ID' => $this->updateData['post_id'],
                'name' => $this->updateData['title'],
                'content' => $this->updateData['title'],
                'map_address' => '??',
                'map_latitude' => $this->updateData['latitude'],
                'map_longitude' => $this->updateData['longitude'],
                'address1' => $this->updateData['address_street_1'],
                'address2' => $this->updateData['address_street_1'],
                'city' => $this->updateData['address_city'],
                'state' => $this->updateData['address_state'],
                'country' => $this->updateData['address_country_name'],
                'postcode' => $this->updateData['address_zip'],
                'name_link_action' => 'store-page',
                'language' => 'de-AT',
        ]);
    }

    /**
     * Creates or updates meta data for YithStore post record
     * @return void
     */
    private function setYithPostMetaData(): void
    {
        $this->configurationService
            ->getAdapter()
            ->upsertMultiplePostMetaData(
                postId: $this->updateData['post_id'],
                metaData: $this->getYithStorePostMetaDataArray()
            );
    }

    /**
     * Return array of YithStore postmeta data [$metaKey => $metaValue]
     * @return array
     */
    private function getYithStorePostMetaDataArray(): array
    {
        return [
            '_yith_sl_gmap_location' => $this->updateData['title'],
            '_yith_sl_address_line1' => $this->updateData['address_street_1'],
            '_yith_sl_address_line2' => $this->updateData['address_street_2'],
            '_yith_sl_postcode' => $this->updateData['address_zip'],
            '_yith_sl_city' => $this->updateData['address_city'],
            '_yith_sl_address_state' => $this->updateData['address_state'],
            '_yith_sl_address_country' => $this->updateData['address_country_name'],
            '_yith_sl_latitude' => $this->updateData['latitude'],
            '_yith_sl_longitude' => $this->updateData['longitude'],
            '_yith_sl_store_name_link' => 'none'
        ];
    }
}
