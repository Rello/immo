<?php
script('immo', 'immo-main');
?>
<div id="immo-app" data-immo-view="admin-transaction">
    <h1><?php p($l->t('Transaction #%s', [$_['transaction']->getId()])); ?></h1>
    <dl>
        <dt><?php p($l->t('Date')); ?></dt>
        <dd><?php p($_['transaction']->getDate()->format('Y-m-d')); ?></dd>
        <dt><?php p($l->t('Amount')); ?></dt>
        <dd><?php p(number_format($_['transaction']->getAmount(), 2)); ?></dd>
    </dl>
</div>
