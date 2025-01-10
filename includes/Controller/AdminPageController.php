<?php

namespace LB\CreeBuildings\Controller;

use LB\CreeBuildings\Repository\ProjectRepository,
    LB\CreeBuildings\Repository\ProjectAttachmentRepository,
    LB\CreeBuildings\Service\ConfigurationService,
    LB\CreeBuildings\Service\DatabaseService;

/**
 * Description of AdminPageController
 *
 * @author Lubos Babocky <babocky@gmail.com>
 */
class AdminPageController extends AbstractController {

    public ProjectRepository $projectRepository;
    public ProjectAttachmentRepository $projectAttachmentRepository;

    protected function injectDependencies(): void {
        $databaseService = DatabaseService::GetInstance();
        $this->projectRepository = $databaseService->getRepository(ProjectRepository::class);
        $this->projectAttachmentRepository = $databaseService->getRepository(ProjectAttachmentRepository::class);
    }

    /**
     * @menuTitle Overview
     */
    public function indexAction(): void {
        
    }

    /**
     * @menuTitle WP Data management
     */
    public function wpDataManagementAction(): void {
        /** @var \wpdb $wpdb */
        global $wpdb;
        $query = "SELECT"
                . " `WP`.`ID`, `WP`.`post_title`, `WP`.`post_author`, `WP`.`post_date`, `WP`.`post_modified`,"
                . " (SELECT COUNT(0) FROM `wp_posts` WHERE `post_parent` = `WP`.`ID` AND `post_author` = 4 AND `post_type` = 'attachment') AS `attachments`"
                . " FROM `wp_posts` AS `WP`"
                . " WHERE `WP`.`ID` IN(SELECT `post_id` FROM `lb_creebuildings_project`)"
                . " ORDER BY `post_title` ASC";
        $this->setArgument('projectPosts', $wpdb->get_results($query, ARRAY_A));
    }

    /**
     * @global \wpdb $wpdb
     */
    public function wpDataDeleteAttachmentsAction(): void {
        global $wpdb;
        $attachments = $wpdb->get_results(sprintf("SELECT `ID` FROM `wp_posts` WHERE `post_parent` = %d AND `post_author` = 4 AND `post_type` = 'Attachment'", $this->getArgument('postId')), ARRAY_A);
        foreach (array_column($attachments, 'ID') as $attachmentID) {
            wp_delete_attachment($attachmentID, true);
            $this->projectAttachmentRepository->removePostAttachmentConnection($attachmentID);
        }
        $this->redirect('wpDataManagement');
    }

    /**
     * @menuTitle Settings
     */
    public function settingsAction(): void {
        $this->setArgument('settings', ConfigurationService::GetInstance()->getConfig());
    }

    public function switchPostStatusAction(): void {
        $this->projectRepository->updateProjectPostStatus($this->getArgument('project_id'), $this->getArgument('post_status', 'draft'));
        $this->redirect('index');
    }

    public function buildSwithPostStatusUri(array $project): string {
        return $this->buildActionUri(
                        'switchPostStatus',
                        [
                            'project_id' => $project['project_id'],
                            'post_status' => $project['post_status'] === 'publish' ? 'draft' : 'publish'
                        ]
                );
    }
}
