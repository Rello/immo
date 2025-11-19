<?php $l = $_['l'] ?? \OC::$server->getL10N('immo'); ?>
<?php /** @var array $data */ ?>
<div class="immo-view immo-dashboard" data-year="<?php p($data['year']); ?>">
    <h2><?php p($l->t('Dashboard for {year}', ['year' => $data['year']])); ?></h2>
    <div class="immo-cards">
        <div class="card"><?php p($l->t('Properties')); ?>: <?php p($data['propCount']); ?></div>
        <div class="card"><?php p($l->t('Units')); ?>: <?php p($data['unitCount']); ?></div>
        <div class="card"><?php p($l->t('Active leases')); ?>: <?php p($data['activeLeaseCount']); ?></div>
        <div class="card"><?php p($l->t('Annual rent (cold)')); ?>: <?php p($data['annualRentSum']); ?></div>
    </div>
</div>
