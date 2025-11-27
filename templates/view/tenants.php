<?php $l = $_['l'] ?? \OC::$server->getL10N('immo'); ?>
<div class="immo-view">
    <h2><?php p($l->t('Tenants')); ?></h2>
    <div class="immo-toolbar">
        <button class="primary" id="immo-tenant-add"><?php p($l->t('New tenant')); ?></button>
    </div>
    <div id="immo-tenant-list"></div>
</div>
