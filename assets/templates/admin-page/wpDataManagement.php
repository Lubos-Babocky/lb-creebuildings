<?php
/** @var \LB\CreeBuildings\Controller\AdminPageController $this */
isset($this) || die;
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
                        <th class="text-left">PostID</th>
                        <th class="text-left">Title</th>
                        <th class="text-left">Created</th>
                        <th class="text-left">Updated</th>
                        <th class="text-left">Attachments</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($this->getArgument('projectPosts') as $post): ?>
                        <tr>
                            <td><?= $post['ID'] ?></td>
                            <td><?= $post['post_title'] ?></td>
                            <td><?= $post['post_date'] ?></td>
                            <td><?= $post['post_modified'] ?></td>
                            <td>
                                <?php if($post['attachments'] > 0): ?>
                                    <?= $post['attachments'] ?>
                                    <a href="<?= $this->buildActionUri('wpDataDeleteAttachments', ['postId' => $post['ID']]) ?>">Delete attachments</a>
                                <?php else: ?>
                                    no attachments
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>