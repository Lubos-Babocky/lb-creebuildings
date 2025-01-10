<?php

namespace LB\CreeBuildings\SystemUpdater;

use LB\CreeBuildings\Service\ConfigurationService,
    LB\CreeBuildings\SystemUpdater\AbstractSystemUpdater,
    LB\CreeBuildings\Repository\Yith\StoreRepository,
    LB\CreeBuildings\Repository\Partner\PartnerRepository,
    LB\CreeBuildings\Utils\FileUtility,
    LB\CreeBuildings\Service\DatabaseService;

/**
 * Description of PartnerSystemUpdater
 *
 * @author Lubos Babocky <babocky@gmail.com>
 */
class PartnerSystemUpdater extends AbstractSystemUpdater
{

    private PartnerRepository $repository;

    public function __construct(
        ConfigurationService $configurationService,
        array $data
    )
    {
        $this->repository = DatabaseService::GetInstance()
            ->getRepository(PartnerRepository::class);
        parent::__construct($configurationService, $data);
    }

    protected function updateSystemData(): void
    {
        $this->insertOrUpdateWordpressPost();
        $this->updatePostAttachment();
        $this->upsertYithData();
        $this->setYithPostMetaData();
        DatabaseService::GetInstance()
            ->getRepository(PartnerRepository::class)
            ->saveRecord($this->data);
    }

    /**
     * Inserts or updates store post, after insert it automatically updates post_id
     * @return void
     */
    private function insertOrUpdateWordpressPost(): void
    {
        $this->data['post_id'] = $this->configurationService->getAdapter()
            ->upsertPartnerPost(
                partnerTitle: $this->data['title'],
                postId: $this->data['post_id'] ?? null
            );
    }

    private function updatePostAttachment(): void
    {
        if (empty($this->data['avatar_api_url'])) {
            $this->data['avatar_api_url'] = $this->configurationService->getConfig('WP_PARTNER_DEFAULT_LOGO_URL');
        }
        if (empty($this->data['avatar_storage_path']) || !is_readable($this->data['avatar_storage_path'])) {
            $this->data['avatar_storage_path'] = (new FileUtility())
                ->createImageFromUrl(
                    imageName: sprintf('partner-%s', $this->data['id']),
                    imageUrl: $this->data['avatar_api_url']
                );
        }
        $this->data['avatar_post_id'] = $this->configurationService
            ->getAdapter()
            ->upsertPostAttachment(
                postId: $this->data['post_id'],
                attachmentId: $this->data['avatar_post_id'] ?? null,
                localFilePath: $this->data['avatar_storage_path'] ?? null,
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
                'ID' => $this->data['post_id'],
                'name' => $this->data['title'],
                'content' => $this->data['title'],
                'map_address' => '??',
                'map_latitude' => $this->data['latitude'],
                'map_longitude' => $this->data['longitude'],
                'address1' => $this->data['address_street_1'],
                'address2' => $this->data['address_street_1'],
                'city' => $this->data['address_city'],
                'state' => $this->data['address_state'],
                'country' => $this->data['address_country_name'],
                'postcode' => $this->data['address_zip'],
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
                postId: $this->data['post_id'],
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
            '_yith_sl_gmap_location' => $this->data['title'],
            '_yith_sl_address_line1' => $this->data['address_street_1'],
            '_yith_sl_address_line2' => $this->data['address_street_2'],
            '_yith_sl_postcode' => $this->data['address_zip'],
            '_yith_sl_city' => $this->data['address_city'],
            '_yith_sl_address_state' => $this->data['address_state'],
            '_yith_sl_address_country' => $this->data['address_country_name'],
            '_yith_sl_latitude' => $this->data['latitude'],
            '_yith_sl_longitude' => $this->data['longitude'],
            '_yith_sl_store_name_link' => 'none'
        ];
    }
}
