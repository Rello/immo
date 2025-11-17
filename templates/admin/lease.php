<?php
script('immo', 'immo-main');
?>
<div id="immo-app" data-immo-view="admin-lease">
    <h1><?php p($l->t('Lease #%s', [$_['lease']->getId()])); ?></h1>
    <dl>
        <dt><?php p($l->t('Unit')); ?></dt>
        <dd><?php p($_['lease']->getUnitId()); ?></dd>
        <dt><?php p($l->t('Tenant')); ?></dt>
        <dd><?php p($_['lease']->getTenantId()); ?></dd>
        <dt><?php p($l->t('Base rent')); ?></dt>
        <dd><?php p(number_format($_['lease']->getBaseRent(), 2)); ?></dd>
    </dl>
</div>
