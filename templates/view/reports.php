<?php $l = $_['l'] ?? \OC::$server->getL10N('immo'); ?>
<div class="immo-view">
    <h2><?php p($l->t('Reports')); ?></h2>
    <form id="immo-report-create">
        <label><?php p($l->t('Property ID')); ?> <input type="number" name="propId" required></label>
        <label><?php p($l->t('Year')); ?> <input type="number" name="year" value="<?php p(date('Y')); ?>"></label>
        <button class="primary" type="submit"><?php p($l->t('Create report')); ?></button>
    </form>
    <div id="immo-report-list"></div>
</div>
