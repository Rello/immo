<?php
script('immo', 'immo-main');
?>
<div id="immo-app" data-immo-view="tenant-dashboard">
    <h1><?php p($l->t('My rentals')); ?></h1>
    <p><?php p($l->t('This area lists your active leases and latest statements.')); ?></p>
    <div data-immo-tenant-leases></div>
</div>
