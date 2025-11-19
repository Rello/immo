<?php $l = $_['l'] ?? \OC::$server->getL10N('immo'); ?>
<div class="immo-view">
    <h2><?php p($l->t('Unit detail')); ?> #<?php p($_['id']); ?></h2>
    <div id="immo-unit-detail"></div>
</div>
