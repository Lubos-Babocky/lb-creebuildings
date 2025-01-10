<?php
/** @var \LB\CreeBuildings\Controller\AdminPageController $this */

use LB\CreeBuildings\Service\DatabaseService,
    LB\CreeBuildings\Repository\ProjectRepository;

/** @var ProjectRepository $projectRepository */
$projectRepository = DatabaseService::GetInstance()->getRepository(ProjectRepository::class);

$switchPostStatus = function ($projectId, $status) {
    return add_query_arg(
    [
        'page' => 'lb-creebuildings',
        'action' => 'switch-post-status',
        'project_id' => $projectId,
        'new_status' => $status
    ],
    admin_url('admin.php')
    );
};

if (!empty($action = filter_input(INPUT_GET, 'action'))) {
    try {
        switch ($action) {
            case 'switch-post-status':
                $projectRepository->updateProjectPostStatus(filter_input(INPUT_GET, 'project_id') ?: 0, filter_input(INPUT_GET, 'new_status') ?: 'draft');
                break;
            case 'show-project-images':
                
                
                break;
        }
    } catch (\Exception $ex) {
        echo '<pre>';
        var_dump($ex);
        die(__METHOD__ . '::' . __LINE__);
    }
}
?>

<div class="container-fluid">
    <div class="row">
        <div class="col">
            <h1><?= esc_html(get_admin_page_title()); ?></h1>
        </div>
    </div>
    <div class="row">
        <div class="col">
            <table class="table table-sm">
                <thead>
                    <tr>
                        <th class="text-left">#</th>
                        <th class="text-left">ID</th>
                        <th class="text-left">Title</th>
                        <th class="text-left">Updated</th>
                        <th class="text-left">Type of use</th>
                        <th class="text-left">Stage</th>
                        <th class="text-left">Images</th>
                        <th class="text-left">Post ID</th>
                        <th class="text-left">Post status</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($projectRepository->findProjectsDataForAdminPage() as $i => $project): ?>
                        <tr>
                            <td><?= $i + 1 ?></td>
                            <td><?= $project['project_id'] ?></td>
                            <td><?= $project['title'] ?></td>
                            <td><?= (date('Y-m-d', $project['tstamp']) === date('Y-m-d')) ? 'today' : date('d.m.y', $project['tstamp']) ?></td>
                            <td><?= $project['type_of_use'] ?></td>
                            <td><?= $project['project_stage'] ?></td>
                            <td><?= $project['images'] ?></td>
                            <td><?= $project['post_id'] ?></td>
                            <td><?= $project['post_status'] ?></td>
                            <td>

                                
                                
                                <a href="<?= $switchPostStatus($project['project_id'], $project['post_status'] === 'publish' ? 'draft' : 'publish') ?>">
                                    <span title="<?= $project['post_status'] === 'publish' ? 'Set to draft' : 'Publish post' ?>" class="btn icon-eye-<?= $project['post_status'] === 'publish' ? 'hide' : 'show' ?>"></span>
                                </a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>