<?php
script('immoapp', 'app');
style('immoapp', 'style');
?>
<div id="immoapp" class="immoapp-layout">
    <div class="immoapp-sidebar">
        <h2><?php p($l->t('Immo navigation')); ?></h2>
        <ul>
            <li data-role="manager"><a href="#/dashboard"><?php p($l->t('Dashboard')); ?></a></li>
            <li data-role="manager"><a href="#/properties"><?php p($l->t('Properties')); ?></a></li>
            <li data-role="manager"><a href="#/units"><?php p($l->t('Units')); ?></a></li>
            <li data-role="manager"><a href="#/tenants"><?php p($l->t('Tenants')); ?></a></li>
            <li data-role="manager"><a href="#/tenancies"><?php p($l->t('Tenancies')); ?></a></li>
            <li data-role="manager"><a href="#/transactions"><?php p($l->t('Transactions')); ?></a></li>
            <li data-role="manager"><a href="#/accounting"><?php p($l->t('Accounting')); ?></a></li>
            <li data-role="tenant"><a href="#/my-tenancies"><?php p($l->t('My tenancies')); ?></a></li>
            <li data-role="tenant"><a href="#/my-reports"><?php p($l->t('My reports')); ?></a></li>
        </ul>
    </div>
    <div class="immoapp-content" id="immoapp-content">
        <div class="section">
            <h2><?php p($l->t('Loading Immo app…')); ?></h2>
            <div class="icon-loading"></div>
        </div>
    </div>
</div>
