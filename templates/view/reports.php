<?php $l = $_['l'] ?? \OC::$server->getL10N('immo'); ?>
<div class="immo-view">
    <h2><?php p($l->t('Reports')); ?></h2>
    <form id="immo-report-create" class="immo-form immo-form-grid">
        <label><?php p($l->t('Property')); ?>
            <select name="propId" required data-placeholder="<?php p($l->t('Select property')); ?>"></select>
        </label>
        <label><?php p($l->t('Year')); ?>
            <input type="number" name="year" value="<?php p(date('Y')); ?>">
        </label>
        <div class="immo-form-actions">
            <button class="primary" type="submit"><?php p($l->t('Create report')); ?></button>
        </div>
    </form>
    <div id="immo-report-list"></div>
</div>
