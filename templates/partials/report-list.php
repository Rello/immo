<div class="section" data-partial="<?php echo basename(__FILE__, '.php'); ?>">
    <div class="section__header">
        <h2><?php p($l->t('Reports')); ?></h2>
        <button class="primary" data-action="report-add"><?php p($l->t('New report')); ?></button>
    </div>
    <table class="immo-table" id="report-table">
        <thead>
            <tr>
                <th data-sort-key="year"><?php p($l->t('Year')); ?><span class="sort-indicator">⇅</span></th>
                <th data-sort-key="property"><?php p($l->t('Property')); ?><span class="sort-indicator">⇅</span></th>
                <th data-sort-key="type"><?php p($l->t('Type')); ?><span class="sort-indicator">⇅</span></th>
                <th data-sort-key="file"><?php p($l->t('File')); ?><span class="sort-indicator">⇅</span></th>
            </tr>
        </thead>
        <tbody>
            <tr><td colspan="4"><?php p($l->t('No reports loaded yet')); ?></td></tr>
        </tbody>
    </table>
</div>
