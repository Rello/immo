<?php $l = $_['l'] ?? \OC::$server->getL10N('immo'); ?>
<div class="immo-view">
    <h2><?php p($l->t('Tenant detail')); ?> #<?php p($_['id']); ?></h2>
    <div id="immo-tenant-detail"></div>
</div>
