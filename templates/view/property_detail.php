<?php $l = $_['l'] ?? \OC::$server->getL10N('immo'); ?>
<div class="immo-view">
    <h2><?php p($l->t('Property detail')); ?> #<?php p($_['id']); ?></h2>
    <div id="immo-prop-detail"></div>
    <div id="immo-prop-units"></div>
    <div id="immo-prop-reports"></div>
</div>
