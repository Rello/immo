<?php
script('immo', 'immo-main');
?>
<div id="immo-app" data-immo-view="admin-dashboard" data-initial-year="<?php p($_['initialYear']); ?>">
    <h1><?php p($l->t('Immo Dashboard')); ?></h1>
    <div class="immo-dashboard-grid">
        <div class="immo-card" data-immo-metric="rent">
            <h3><?php p($l->t('Rent income')); ?></h3>
            <span class="immo-metric-value">–</span>
        </div>
        <div class="immo-card" data-immo-metric="expenses">
            <h3><?php p($l->t('Expenses')); ?></h3>
            <span class="immo-metric-value">–</span>
        </div>
        <div class="immo-card" data-immo-metric="vacancy">
            <h3><?php p($l->t('Vacancy rate')); ?></h3>
            <span class="immo-metric-value">–</span>
        </div>
    </div>
</div>
