<?php $l = $_['l'] ?? \OC::$server->getL10N('immo'); ?>
<div class="immo-view">
    <h2><?php p($l->t('Reports')); ?></h2>
    <div class="immo-toolbar">
        <button class="primary" id="immo-report-add"><?php p($l->t('Create report')); ?></button>
    </div>
    <div id="immo-report-list"></div>
</div>
