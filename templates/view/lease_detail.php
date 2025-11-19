<?php $l = $_['l'] ?? \OC::$server->getL10N('immo'); ?>
<div class="immo-view">
    <h2><?php p($l->t('Lease detail')); ?> #<?php p($_['id']); ?></h2>
    <div id="immo-lease-detail"></div>
</div>
