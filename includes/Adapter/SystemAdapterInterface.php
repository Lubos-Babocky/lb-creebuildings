<?php

namespace LB\CreeBuildings\Adapter;

/**
 * System adapter interface
 * defines all methods needed for inserting API data into host system
 * @author Ľuboš Babocký <babocky@gmail.com>
 */
interface SystemAdapterInterface
{

    public function upsertProjectPost(
        string $projectTitle,
        int $postTagId = 0,
        ?int $postId = null
    ): int;

    public function upsertPartnerPost(
        string $partnerTitle,
        ?int $postId = null
    ): int;

    public function upsertPostAttachment(
        int $postId,
        ?int $attachmentId = null,
        ?string $localFilePath = null
    ): int;

    public function upsertProjectBackground(
        int $postId,
        array $metaData,
        string $mimeType,
        ?int $attachmentId = null,
        ?string $localFilePath = null
    ): int;

    public function upsertProjectGalleryImage(
        int $postId,
        array $metaData,
        string $mimeType,
        ?int $attachmentId = null,
        ?string $localFilePath = null
    ): int;

    public function insertAttachment(
        int $postId,
        string $mimeType,
        string $localFilePath
    ): int;

    public function upsertMultiplePostMetaData(
        int $postId,
        array $metaData
    ): void;

    public function removePostAndAttachements(
        int $postId
    ): void;

    public function upsertAcfData(
        int $postId,
        string $acfId,
        mixed $value
    ): void;

    public function clearAcfData(
        int $postId,
        array $acfIds
    ): void;

    public function upsertPostMeta(
        int $postId,
        string $metaKey,
        mixed $metaValue
    ): void;

    public function generateFileAttachmentMetaData(
        array $files
    ): array;
}
