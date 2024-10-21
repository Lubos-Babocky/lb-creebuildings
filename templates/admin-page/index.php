<?php
/** @var \LB\CreeBuildings\Controller\AdminPageController $this */
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
                                <a href="<?= $this->buildSwithPostStatusUri($project); ?>" aria-label="<?= $project['post_status'] === 'publish' ? 'Set to draft' : 'Publish post' ?>">
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