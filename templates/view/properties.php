<?php $l = $_['l'] ?? \OC::$server->getL10N('immo'); ?>
<div class="immo-view">
    <h2><?php p($l->t('Properties')); ?></h2>
    <button class="primary" id="immo-prop-add"><?php p($l->t('New property')); ?></button>
    <div id="immo-prop-list"></div>
</div>
