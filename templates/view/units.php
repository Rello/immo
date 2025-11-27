<?php $l = $_['l'] ?? \OC::$server->getL10N('immo'); ?>
<div class="immo-view">
    <h2><?php p($l->t('Units')); ?></h2>
    <div class="immo-toolbar">
        <button class="primary" id="immo-unit-add"><?php p($l->t('New unit')); ?></button>
    </div>
    <div id="immo-unit-list"></div>
</div>
