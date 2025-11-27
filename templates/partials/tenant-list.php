<div class="section" data-partial="<?php echo basename(__FILE__, '.php'); ?>">
    <div class="section__header">
        <h2><?php p($l->t('Tenants')); ?></h2>
        <button class="primary" data-action="tenant-add"><?php p($l->t('New tenant')); ?></button>
    </div>
    <table class="immo-table" id="tenant-table">
        <thead>
            <tr>
                <th data-sort-key="name"><?php p($l->t('Name')); ?><span class="sort-indicator">⇅</span></th>
                <th data-sort-key="email"><?php p($l->t('Email')); ?><span class="sort-indicator">⇅</span></th>
                <th data-sort-key="phone"><?php p($l->t('Phone')); ?><span class="sort-indicator">⇅</span></th>
                <th data-sort-key="city"><?php p($l->t('City')); ?><span class="sort-indicator">⇅</span></th>
            </tr>
        </thead>
        <tbody>
            <tr><td colspan="4"><?php p($l->t('No tenants loaded yet')); ?></td></tr>
        </tbody>
    </table>
</div>
