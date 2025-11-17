<?php
script('immo', 'immo-main');
?>
<div id="immo-app" data-immo-view="admin-unit">
    <h1><?php p($l->t('Unit %s', [$_['unit']->getLabel()])); ?></h1>
    <dl>
        <dt><?php p($l->t('Property ID')); ?></dt>
        <dd><?php p($_['unit']->getPropertyId()); ?></dd>
        <dt><?php p($l->t('Area (sqm)')); ?></dt>
        <dd><?php p($_['unit']->getAreaSqm()); ?></dd>
        <dt><?php p($l->t('Type')); ?></dt>
        <dd><?php p($_['unit']->getType()); ?></dd>
    </dl>
</div>
