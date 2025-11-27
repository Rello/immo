<?php $l = $_['l'] ?? \OC::$server->getL10N('immo'); ?>
<div class="immo-view">
    <h2><?php p($l->t('Bookings')); ?></h2>
    <div class="immo-toolbar">
        <button class="primary" id="immo-booking-add"><?php p($l->t('New booking')); ?></button>
    </div>
    <div id="immo-booking-list"></div>
</div>
