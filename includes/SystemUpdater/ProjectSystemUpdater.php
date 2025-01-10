<?php

namespace LB\CreeBuildings\SystemUpdater;

use LB\CreeBuildings\Service\DatabaseService,
    LB\CreeBuildings\Repository\ProjectRepository,
    LB\CreeBuildings\Repository\ProjectAttachmentRepository,
    LB\CreeBuildings\Repository\ProjectPropertyRepository,
    LB\CreeBuildings\Repository\ProjectPropertyConnectionRepository,
    LB\CreeBuildings\Repository\Project\ProjectParticipantRepository;

/**
 * Description of ProjectSystemUpdater
 * @author Lubos Babocky <babocky@gmail.com>
 */
class ProjectSystemUpdater extends AbstractSystemUpdater
{

    private string $projectId;
    private int $projectPostId;

    protected function updateSystemData(): void
    {
        $this->projectId = $this->data['project_id'] ?? throw new \Exception('Project id can\'t be empty!');

        $postTagId = $this->getProjectPropertyConnectionRepository()
            ->getProjectPostTagId(
                projectId: $this->projectId,
                propertyId: $this->configurationService->getConfig('WP_PROJECT_TAG_PROPERTY_ID')
            );
        $this->upsertPostAndSetItsId(
            postTitle: $this->data['title']
                ?: $this->projectId,
            postTagId: $postTagId,
            postId: $this->data['post_id']
                ?: null
        );
        $this->getProjectRepository()
            ->updateProjectPostID(
                projectId: $this->projectId,
                postId: $this->projectPostId
            );
        $this->configurationService->getAdapter()
            ->clearAcfData(
                postId: $this->projectPostId,
                acfIds: $this->getProjectPropertyRepository()->getUsedAcfFields()
            );
        $this->upsertBaseAttributes();
        $this->upsertProjectProperties();
        $this->upsertProjectParticipants();
        $this->upsertPostBackgroundImage();
        $this->upsertPostGalleryImages();
    }

    private function upsertPostAndSetItsId(
        string $postTitle,
        string $postTagId,
        ?int $postId = null
    ): void
    {
        $this->projectPostId = $this->configurationService
            ->getAdapter()
            ->upsertProjectPost(
                postId: $postId,
                postTagId: $postTagId,
                projectTitle: $postTitle
            );
    }

    private function upsertBaseAttributes(): void
    {
        $this->setAcfField(
            acfId: 'subtitel',
            value: $this->data['subtitle']
        );
        $this->setAcfField(
            acfId: 'location',
            value: $this->data['location']
        );
    }

    private function upsertProjectProperties(): void
    {
        $this->configurationService->getAdapter()->clearAcfData($this->projectPostId, []);

        $projectProperties = $this->getProjectPropertyConnectionRepository()
            ->findAllProjectProperties($this->projectId);
        foreach ($projectProperties as $projectProperty) {
            $this->setAcfField(
                acfId: $projectProperty['acf_id'],
                value: $projectProperty['property_value']
            );
        }
    }

    private function upsertProjectParticipants(): void
    {
        $projectParticipants = $this->getProjectParticipantRepository()
            ->findProjectParticipantsWithRoles(projectId: $this->projectId);
        foreach ($projectParticipants as $participant) {
            $this->setAcfField(
                acfId: $participant['acf_id'],
                value: ($participant['subscriptions'] > 0)
                    ? sprintf('<span class="%s" title="%s">%s</span>', $this->configurationService->getConfig('WP_PROJECT_PARTNER_BADGE_CLASS'), $participant['badge_title'], $participant['participant_name'])
                    : $participant['participant_name']
            );
        }
    }

    private function upsertPostBackgroundImage(): void
    {
        $projectBackground = $this->getProjectAttachmentRepository()
            ->findProjectBackground($this->projectId);
        $projectBackgroundAttachmentPostId = $this->configurationService
            ->getAdapter()
            ->upsertProjectBackground(
                postId: $this->projectPostId,
                metaData: unserialize($projectBackground['meta_data']),
                mimeType: $projectBackground['mime_type'],
                attachmentId: $projectBackground['attachment_post_id'],
                localFilePath: $projectBackground['internal_url']
            );
        $this->getProjectAttachmentRepository()
            ->updateAttachmentPostId(
                attachmentId: $projectBackground['file_id'],
                attachmentPostId: $projectBackgroundAttachmentPostId
            );
    }

    private function upsertPostGalleryImages(): void
    {
        $projectGalleryImagePostIdList = [];
        $projectGalleryImages = $this->getProjectAttachmentRepository()
            ->findProjectGalleryImages(projectId: $this->projectId);
        foreach ($projectGalleryImages as $projectGalleryImage) {
            $projectGalleryImagePostId = $this->configurationService
                ->getAdapter()
                ->upsertProjectGalleryImage(
                    postId: $this->projectPostId,
                    metaData: unserialize($projectGalleryImage['meta_data']),
                    mimeType: $projectGalleryImage['mime_type'],
                    attachmentId: $projectGalleryImage['attachment_post_id'],
                    localFilePath: $projectGalleryImage['internal_url']
                );
            $this->getProjectAttachmentRepository()
                ->updateAttachmentPostId(
                    attachmentId: $projectGalleryImage['file_id'],
                    attachmentPostId: $projectGalleryImagePostId
                );
            $projectGalleryImagePostIdList[] = $projectGalleryImagePostId;
        }
        $this->setAcfField(
            acfId: 'gallery',
            value: $projectGalleryImagePostIdList
        );
    }

    private function setAcfField(
        string $acfId,
        mixed $value
    ): void
    {
        $this->configurationService
            ->getAdapter()
            ->upsertAcfData(
                postId: $this->projectPostId,
                acfId: $acfId,
                value: $value
            );
    }

    private function getProjectPropertyConnectionRepository(): ProjectPropertyConnectionRepository
    {
        return DatabaseService::GetInstance()
                ->getRepository(ProjectPropertyConnectionRepository::class);
    }

    private function getProjectPropertyRepository(): ProjectPropertyRepository
    {
        return DatabaseService::GetInstance()
                ->getRepository(ProjectPropertyRepository::class);
    }

    private function getProjectAttachmentRepository(): ProjectAttachmentRepository
    {
        return DatabaseService::GetInstance()
                ->getRepository(ProjectAttachmentRepository::class);
    }

    private function getProjectRepository(): ProjectRepository
    {
        return DatabaseService::GetInstance()
                ->getRepository(ProjectRepository::class);
    }

    private function getProjectParticipantRepository(): ProjectParticipantRepository
    {
        return DatabaseService::GetInstance()
                ->getRepository(ProjectParticipantRepository::class);
    }
}
