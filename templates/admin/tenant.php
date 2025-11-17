<?php
script('immo', 'immo-main');
?>
<div id="immo-app" data-immo-view="admin-tenant">
    <h1><?php p($l->t('Tenant %s', [$_['tenant']->getName()])); ?></h1>
    <p><?php p($l->t('Linked user: %s', [$_['tenant']->getNcUserId() ?: $l->t('none')])); ?></p>
</div>
