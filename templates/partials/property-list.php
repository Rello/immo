<div class="section">
    <div class="section__header">
        <h2><?php p($l->t('Properties')); ?></h2>
        <button data-action="prop-add" class="primary"><?php p($l->t('New property')); ?></button>
    </div>
    <div id="prop-table"></div>

    <div id="prop-form-modal" class="immo-modal" role="dialog" aria-modal="true" aria-labelledby="prop-form-title">
        <div class="immo-modal__dialog">
            <div class="immo-modal__header">
                <h3 id="prop-form-title"><?php p($l->t('Property')); ?></h3>
                <button type="button" class="icon-close" data-close-modal="prop-form-modal" aria-label="<?php p($l->t('Close')); ?>"></button>
            </div>
            <div class="immo-modal__body">
                <form>
                    <label><?php p($l->t('Name')); ?><input type="text" name="name" required></label>
                    <label><?php p($l->t('Street')); ?><input type="text" name="street"></label>
                    <label><?php p($l->t('ZIP')); ?><input type="text" name="zip"></label>
                    <label><?php p($l->t('City')); ?><input type="text" name="city"></label>
                    <label><?php p($l->t('Country')); ?><input type="text" name="country"></label>
                    <label><?php p($l->t('Type')); ?><input type="text" name="type"></label>
                    <label><?php p($l->t('Notes')); ?><textarea name="note"></textarea></label>
                    <div class="immo-modal__footer">
                        <button type="button" data-close-modal="prop-form-modal"><?php p($l->t('Cancel')); ?></button>
                        <button type="submit" class="primary"><?php p($l->t('Save')); ?></button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
