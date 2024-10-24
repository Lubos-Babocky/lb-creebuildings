<?php

namespace LB\CreeBuildings\DataHandler;

use LB\CreeBuildings\Repository\ProjectPropertyConnectionRepository,
    LB\CreeBuildings\Service\ConfigurationService,
    LB\CreeBuildings\Repository\ProjectRepository,
    LB\CreeBuildings\Utils\GeneralUtility,
    LB\CreeBuildings\Service\DatabaseService;

/**
 * Description of WordpressDataHandler
 *
 * @author Lubos Babocky <babocky@gmail.com>
 */
class WordpressDataHandler extends AbstractDataHandler {

    protected ProjectRepository $projectRepository;
    protected ProjectPropertyConnectionRepository $projectPropertyConnectionRepository;
    protected \PDO $connection;
    protected ConfigurationService $configurationService;

    public function __construct() {
        $databaseService = DatabaseService::GetInstance();
        $this->connection = $databaseService->getConnection();
        $this->projectRepository = $databaseService->getRepository(ProjectRepository::class);
        $this->projectPropertyConnectionRepository = $databaseService->getRepository(ProjectPropertyConnectionRepository::class);
        $this->configurationService = ConfigurationService::GetInstance();
    }

    public function handle(): void {
        $this->updateTags();
        $this->updateOptionsOfAcfStatusField();
        $this->updateProjectPosts();
        $this->updateProjectPostMetaData();
    }

    protected function updateOptionsOfAcfStatusField(): void {
        $fieldId = $this->connection
                ->query(sprintf(
                                "SELECT `post_name` FROM `wp_posts` WHERE `post_parent` = %d AND `post_excerpt` = '%s'",
                                $this->configurationService->getConfig('ACF_GROUP_POST_ID'),
                                $this->configurationService->getConfig('ACF_PROJECT_STATUS_FIELD_NAME')
                        ))
                ->fetchColumn();
        $acfField = acf_get_field($fieldId);
        foreach ($this->projectPropertyConnectionRepository->findAvailableTypesOfUse() as $typeOfUse) {
            if (!array_key_exists($typeOfUse, $acfField['choices'])) {
                $acfField['choices'][$typeOfUse] = $typeOfUse;
            }
        }
        acf_update_field($acfField);
    }

    protected function updateProjectPosts(): void {
        foreach ($this->projectRepository->findPublicProjects() as $projectData) {
            $existingPost = get_post($projectData['post_id']);
            $postData = [
                'ID' => empty($existingPost) ? 'draft' : $existingPost->ID,
                'post_author' => $this->configurationService->getConfig('WP_PROJECT_POST_AUTHOR_ID'),
                'post_title' => htmlspecialchars($projectData['title'], ENT_QUOTES),
                'post_name' => GeneralUtility::Slugify($projectData['title']),
                'post_status' => empty($existingPost) ? 'draft' : $existingPost->post_status,
                'post_type' => $this->configurationService->getConfig('WP_PROJECT_POST_TYPE')
            ];
            $this->projectRepository->updateProjectPostID($projectData['project_id'], wp_insert_post($postData));
        }
    }

    protected function updateProjectPostMetaData(): void {
        foreach ($x = $this->projectPropertyConnectionRepository->findAllConnections() as $projectProperty) {
            update_field($projectProperty['acf_id'], $projectProperty['property_value'], $projectProperty['post_id']);
        }
    }

    protected function updateTags(): void {
        foreach ($this->projectPropertyConnectionRepository->findAvailableTypesOfUse() as $typeOfUse) {
            if(null === term_exists($typeOfUse, $this->configurationService->getConfig('WP_PROJECT_TAG_TAXONOMY'))) {
                wp_insert_term($typeOfUse, $this->configurationService->getConfig('WP_PROJECT_TAG_TAXONOMY'));
            }
        }
        foreach($this->projectPropertyConnectionRepository->findProjectCategoryRelations() as $relation) {
            wp_set_post_terms($relation['post_id'], [$relation['property_value']], $this->configurationService->getConfig('WP_PROJECT_TAG_TAXONOMY'), false);
        }
    }
}
