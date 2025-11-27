<?php $l = $_['l'] ?? \OC::$server->getL10N('immo'); ?>
<div class="immo-view">
    <h2><?php p($l->t('Leases')); ?></h2>
    <div class="immo-toolbar">
        <button class="primary" id="immo-lease-add"><?php p($l->t('New lease')); ?></button>
    </div>
    <div id="immo-lease-list"></div>
</div>
