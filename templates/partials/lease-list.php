<div class="section" data-partial="<?php echo basename(__FILE__, '.php'); ?>">
    <div class="section__header">
        <h2><?php p($l->t('Leases')); ?></h2>
        <button class="primary" data-action="lease-add"><?php p($l->t('New lease')); ?></button>
    </div>
    <table class="immo-table" id="lease-table">
        <thead>
            <tr>
                <th data-sort-key="tenant"><?php p($l->t('Tenant')); ?><span class="sort-indicator">⇅</span></th>
                <th data-sort-key="unit"><?php p($l->t('Unit')); ?><span class="sort-indicator">⇅</span></th>
                <th data-sort-key="period"><?php p($l->t('Period')); ?><span class="sort-indicator">⇅</span></th>
                <th data-sort-key="status"><?php p($l->t('Status')); ?><span class="sort-indicator">⇅</span></th>
            </tr>
        </thead>
        <tbody>
            <tr><td colspan="4"><?php p($l->t('No leases loaded yet')); ?></td></tr>
        </tbody>
    </table>
</div>
