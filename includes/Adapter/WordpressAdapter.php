<?php

namespace LB\CreeBuildings\Adapter;

use LB\CreeBuildings\Service\ConfigurationService,
    LB\CreeBuildings\Utils\GeneralUtility;

/**
 * Description of WordpressAdapter
 *
 * @author Lubos Babocky <babocky@gmail.com>
 */
class WordpressAdapter implements SystemAdapterInterface
{

    private const PROJECT_GALLERY_IMAGE_VIRTUAL_FOLDER = 'project-images';
    private const IMAGE_SIZES = [
        'PreviewXs' => 'thumbnail',
        'PreviewSm' => 'medium',
        'PreviewMd' => 'medium_large',
        'PreviewLg' => 'large'
    ];
    private const IMAGE_META_DEFAULT_ARRAY = [
        'aperture' => 0,
        'credit' => '',
        'camera' => '',
        'caption' => '',
        'created_timestamp' => '0',
        'copyright' => '',
        'focal_length' => '0',
        'iso' => '0',
        'shutter_speed' => '0',
        'title' => '',
        'orientation' => '1',
        'keywords' => []
    ];

    public function __construct(
        protected readonly ConfigurationService $configurationService
    )
    {

    }

    /**
     * Insert or update project post
     * @param string $projectTitle
     * @param int|null $postId
     * @return int PostID
     */
    public function upsertProjectPost(
        string $projectTitle,
        int $postTagId = 0,
        ?int $postId = null
    ): int
    {
        if (empty($postId)) {
            $postId = wp_insert_post([
                'post_title' => htmlspecialchars($projectTitle, ENT_QUOTES),
                'post_content' => $projectTitle,
                'post_status' => $this->configurationService->getConfig('WP_PROJECT_POST_STATUS'),
                'post_author' => $this->configurationService->getConfig('WP_PROJECT_POST_AUTHOR_ID'),
                'post_type' => $this->configurationService->getConfig('WP_PROJECT_POST_TYPE')
            ]);
        } else {
            wp_update_post([
                'ID' => $postId,
                'post_title' => htmlspecialchars($projectTitle, ENT_QUOTES),
                'post_content' => $projectTitle
            ]);
        }
        wp_set_object_terms(
            object_id: $postId,
            terms: (int) $this->configurationService->getConfig('WP_PROJECT_TERM_ID'),
            taxonomy: $this->configurationService->getConfig('WP_PROJECT_TERM_TAXONOMY')
        );
        if (!empty($postTagId)) {
            wp_set_object_terms(
                object_id: $postId,
                terms: $postTagId,
                taxonomy: 'post_tag'
            );
        }
        return $postId;
    }

    public function upsertPartnerPost(
        string $partnerTitle,
        ?int $postId = null
    ): int
    {
        return empty($postId)
            ? wp_insert_post(postarr: [
                'post_title' => htmlspecialchars($partnerTitle, ENT_QUOTES),
                'post_content' => $partnerTitle,
                'post_status' => $this->configurationService->getConfig('WP_PARTNER_POST_STATUS'),
                'post_author' => $this->configurationService->getConfig('WP_PARTNER_POST_AUTHOR_ID'),
                'post_type' => $this->configurationService->getConfig('WP_PARTNER_POST_TYPE')
            ])
            : wp_update_post(postarr: [
                'ID' => $postId,
                'post_title' => htmlspecialchars($partnerTitle, ENT_QUOTES),
                'post_content' => $partnerTitle
        ]);
    }

    /**
     * Create or update post attachment
     * @param int $postId
     * @param int|null $attachmentId
     * @param string|null $localFilePath
     * @return int
     */
    public function upsertPostAttachment(
        int $postId,
        ?int $attachmentId = null,
        ?string $localFilePath = null
    ): int
    {
        $filetype = wp_check_filetype(filename: basename(path: $localFilePath));
        $attachment = [
            'post_mime_type' => $filetype['type'],
            'post_title' => sanitize_file_name(basename($localFilePath)),
            'post_content' => '',
            'post_status' => 'inherit'
        ];
        if (empty($attachmentId)) {
            $attachmentId = wp_insert_attachment(args: $attachment, file: $localFilePath, parent_post_id: $postId);
        } else {
            wp_update_post(postarr: [
                'ID' => $attachmentId,
                'post_mime_type' => $filetype['type'],
                'post_title' => sanitize_file_name(filename: basename(path: $localFilePath)),
            ]);
        }
        update_post_meta(post_id: $attachmentId, meta_key: '_wp_attached_file', meta_value: $localFilePath);
        set_post_thumbnail($postId, $attachmentId);
        return $attachmentId;
    }

    public function upsertProjectBackground(
        int $postId,
        array $metaData,
        string $mimeType,
        ?int $attachmentId = null,
        ?string $localFilePath = null
    ): int
    {
        if (empty($attachmentId) || get_post($attachmentId) === null) {
            $attachmentId = $this->insertAttachment(
                postId: $postId,
                mimeType: $mimeType,
                localFilePath: $localFilePath
            );
        }
        wp_update_attachment_metadata(attachment_id: $attachmentId, data: $metaData);
        update_post_meta(post_id: $postId, meta_key: 'hero_image', meta_value: $attachmentId);
        update_post_meta(post_id: $attachmentId, meta_key: '_wp_attached_file', meta_value: $localFilePath);
        set_post_thumbnail(post: $postId, thumbnail_id: $attachmentId);
        return $attachmentId;
    }

    public function upsertProjectGalleryImage(
        int $postId,
        array $metaData,
        string $mimeType,
        ?int $attachmentId = null,
        ?string $localFilePath = null
    ): int
    {
        if (empty($attachmentId) || get_post($attachmentId) === null) {
            $attachmentId = $this->insertAttachment(
                postId: $postId,
                mimeType: $mimeType,
                localFilePath: $localFilePath
            );
        }
        wp_update_attachment_metadata(attachment_id: $attachmentId, data: $metaData);
        update_post_meta(post_id: $attachmentId, meta_key: '_wp_attached_file', meta_value: $localFilePath);
        return $attachmentId;
    }

    public function insertAttachment(
        int $postId,
        string $mimeType,
        string $localFilePath
    ): int
    {
        return wp_insert_attachment(
            args: [
                'post_mime_type' => $mimeType,
                'post_title' => sanitize_file_name(basename($localFilePath)),
                'post_content' => '',
                'post_status' => 'inherit'
            ],
            file: $localFilePath,
            parent_post_id: $postId
        );
    }

    /**
     * Create or update meta data from $metaData array [metaKey => metaValue]
     * @param int $postId
     * @param array $metaData
     * @return void
     */
    public function upsertMultiplePostMetaData(
        int $postId,
        array $metaData
    ): void
    {
        foreach ($metaData as $metaKey => $metaValue) {
            update_post_meta($postId, $metaKey, $metaValue);
        }
    }

    public function removePostAndAttachements(
        int $postId
    ): void
    {
        global $wpdb;
        $postAttachments = $wpdb->get_results(sprintf(
                "SELECT `ID` FROM `wp_posts` WHERE `post_parent` = %d AND `post_author` = %d AND `post_type` = 'Attachment'",
                $postId,
                $this->configurationService->getConfig('WP_PROJECT_POST_AUTHOR_ID')
            ), ARRAY_A);
        foreach ($postAttachments as $attachment) {
            wp_delete_attachment($attachment['ID'], true);
        }
        wp_delete_post($postId, true);
    }

    public function upsertAcfData(
        int $postId,
        string $acfId,
        mixed $value
    ): void
    {
        update_field($acfId, $value, $postId);
    }

    public function clearAcfData(
        int $postId,
        array $acfIds
    ): void
    {
        if (empty($postId)) {
            return;
        }
        foreach ($acfIds as $acfField) {
            delete_field(
                selector: $acfField,
                post_id: $postId
            );
        }
    }

    public function upsertPostMeta(
        int $postId,
        string $metaKey,
        mixed $metaValue
    ): void
    {
        update_post_meta(
            post_id: $postId,
            meta_key: $metaKey,
            meta_value: $metaValue
        );
    }

    public function generateFileAttachmentMetaData(
        array $files
    ): array
    {
        $sizeVariants = $this->getImageSizeVariants(files: $files);
        if (empty($largestFile = GeneralUtility::GetMultiArrayValue($sizeVariants, 'large', null))) {
            //[L:] if empty, attachment is probably not a image
            return $this->handleNonImageAttachment(files: $files);
        } else {
            $sizeVariants['1536x1536'] = $largestFile;
            $sizeVariants['2048x2048'] = $largestFile;
            return [
                'width' => $largestFile['width'],
                'height' => $largestFile['height'],
                'file' => $this->buildRelativeImagePathForWpUploadFolder(fileName: $largestFile['file']),
                'filesize' => $largestFile['filesize'],
                'sizes' => $sizeVariants,
                'image_meta' => self::IMAGE_META_DEFAULT_ARRAY
            ];
        }
    }

    private function getImageSizeVariants(
        array $files
    ): array
    {
        $sizeVariants = [];
        foreach ($files as $file) {
            $type = $file['type'] ?? throw new \Exception('Image type can not be empty!');
            if (array_key_exists($type, self::IMAGE_SIZES)) {
                $sizeVariants[self::IMAGE_SIZES[$type]] = $this->handleImageSizeVariant(
                    resourceUri: $file['resourceUri'] ?? '',
                    mimeType: $file['mimeType'] ?? '',
                    fileSize: $file['fileSize'] ?? 0,
                    width: $file['dimensions']['width'] ?? 0,
                    height: $file['dimensions']['height'] ?? 0
                );
            }
        }
        return $sizeVariants;
    }

    private function handleImageSizeVariant(
        string $resourceUri,
        string $mimeType,
        int $fileSize,
        int $width,
        int $height
    ): array
    {
        return [
            'file' => $this->buildImageNameForPostMetaData(
                resourceUri: $resourceUri,
                mimeType: $mimeType,
                width: $width,
                height: $height
            ),
            'width' => $width,
            'height' => $height,
            'mime-type' => $mimeType,
            'filesize' => $fileSize
        ];
    }

    private function buildImageNameForPostMetaData(
        string $resourceUri,
        string $mimeType,
        int $width,
        int $height
    ): string
    {
        $explodedResourceUrl = explode('/', $resourceUri);
        $explodedMimeType = explode('/', $mimeType);
        return sprintf(
            '%s-%dx%d.%s',
            str_replace('?fileType=', '-', end($explodedResourceUrl)),
            $width,
            $height,
            end($explodedMimeType)
        );
    }

    private function buildRelativeImagePathForWpUploadFolder(
        string $fileName
    ): string
    {
        $fileUrlParts = explode('-', $fileName);
        $lastParts = explode('.', array_pop($fileUrlParts));
        return sprintf('%s/%s.%s', self::PROJECT_GALLERY_IMAGE_VIRTUAL_FOLDER, implode('-', $fileUrlParts), end($lastParts));
    }

    private function handleNonImageAttachment(
        array $files
    ): array
    {
        if (1 !== count($files)) {
            throw new \Exception(sprintf('File process instructions not set! [%s::%s]', __METHOD__, __LINE__));
        } else {
            return [
                'filesize' => $files[0]['fileSize']
            ];
        }
    }
}
