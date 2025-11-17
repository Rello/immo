<?php
script('immo', 'immo-main');
?>
<div id="immo-app" data-immo-view="admin-statement-wizard">
    <h1><?php p($l->t('Statement wizard')); ?></h1>
    <form data-immo-statement-wizard>
        <label>
            <?php p($l->t('Scope type')); ?>
            <select name="scopeType">
                <option value="property"><?php p($l->t('Property')); ?></option>
                <option value="unit"><?php p($l->t('Unit')); ?></option>
                <option value="tenant"><?php p($l->t('Tenant')); ?></option>
            </select>
        </label>
        <label>
            <?php p($l->t('Scope ID')); ?>
            <input type="number" name="scopeId" required>
        </label>
        <label>
            <?php p($l->t('Year')); ?>
            <input type="number" name="year" value="<?php p(date('Y')); ?>" required>
        </label>
        <button type="submit"><?php p($l->t('Generate')); ?></button>
    </form>
    <div data-immo-statement-result></div>
</div>
