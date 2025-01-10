<?php
/** @var \LB\CreeBuildings\Controller\AdminPageController $this */
isset($this) || die;
?>
<div class="container-fluid ml-4">
    <div class="row">
        <div class="col">
            <h1><?= esc_html(get_admin_page_title()); ?></h1>
        </div>
    </div>
    <div class="row">
        <div class="col-6">
            <form>
                <?php foreach ($this->getArgument('settings') as $attrName => $attrValue): ?>
                    <div class="row form-group align-items-center">
                        <div class="col-4">
                            <label for="<?= strtolower($attrName) ?>" class="form-label"><?= $attrName ?></label>
                        </div>
                        <div class="col-8">
                            <?php if($attrName === 'DB_PASSWORD'): ?>
                                <input type="password" id="<?= strtolower($attrName) ?>" class="form-control" value="**********" />
                            <?php else: ?>
                                <input type="text" id="<?= strtolower($attrName) ?>" class="form-control" value="<?= $attrValue ?>" />
                            <?php endif; ?>
                        </div>
                    </div>
                <?php endforeach; ?>
                <div class="row form-group">
                    <div class="col-10">
                    </div>
                    <div class="col-2">
                        <input type="submit" class="form-submit" value="Update settings" />
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>