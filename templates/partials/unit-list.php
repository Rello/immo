<div class="section" data-partial="<?php echo basename(__FILE__, '.php'); ?>">
    <div class="section__header">
        <h2><?php p($l->t('Units')); ?></h2>
        <button class="primary" data-action="unit-add"><?php p($l->t('New unit')); ?></button>
    </div>
    <table class="immo-table" id="unit-table">
        <thead>
            <tr>
                <th data-sort-key="name"><?php p($l->t('Name')); ?><span class="sort-indicator">⇅</span></th>
                <th data-sort-key="property"><?php p($l->t('Property')); ?><span class="sort-indicator">⇅</span></th>
                <th data-sort-key="status"><?php p($l->t('Status')); ?><span class="sort-indicator">⇅</span></th>
                <th data-sort-key="size"><?php p($l->t('Size')); ?><span class="sort-indicator">⇅</span></th>
            </tr>
        </thead>
        <tbody>
            <tr><td colspan="4"><?php p($l->t('No units loaded yet')); ?></td></tr>
        </tbody>
    </table>
</div>
