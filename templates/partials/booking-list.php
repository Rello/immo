<div class="section" data-partial="<?php echo basename(__FILE__, '.php'); ?>">
    <div class="section__header">
        <h2><?php p($l->t('Bookings')); ?></h2>
        <button class="primary" data-action="booking-add"><?php p($l->t('New booking')); ?></button>
    </div>
    <table class="immo-table" id="booking-table">
        <thead>
            <tr>
                <th data-sort-key="date"><?php p($l->t('Date')); ?><span class="sort-indicator">⇅</span></th>
                <th data-sort-key="type"><?php p($l->t('Type')); ?><span class="sort-indicator">⇅</span></th>
                <th data-sort-key="property"><?php p($l->t('Property')); ?><span class="sort-indicator">⇅</span></th>
                <th data-sort-key="amount"><?php p($l->t('Amount')); ?><span class="sort-indicator">⇅</span></th>
            </tr>
        </thead>
        <tbody>
            <tr><td colspan="4"><?php p($l->t('No bookings loaded yet')); ?></td></tr>
        </tbody>
    </table>
</div>
