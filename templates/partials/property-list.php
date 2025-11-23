<div class="section">
    <h2><?php p($l->t('Properties')); ?></h2>
    <button data-action="prop-add" class="primary"><?php p($l->t('New property')); ?></button>
    <div id="prop-list" data-list="properties"></div>
    <div id="prop-form-modal" class="modal">
        <form>
            <label><?php p($l->t('Name')); ?><input type="text" name="name" required></label>
            <label><?php p($l->t('Street')); ?><input type="text" name="street"></label>
            <label><?php p($l->t('ZIP')); ?><input type="text" name="zip"></label>
            <label><?php p($l->t('City')); ?><input type="text" name="city"></label>
            <label><?php p($l->t('Country')); ?><input type="text" name="country"></label>
            <label><?php p($l->t('Type')); ?><input type="text" name="type"></label>
            <label><?php p($l->t('Notes')); ?><textarea name="note"></textarea></label>
            <button type="submit"><?php p($l->t('Save')); ?></button>
        </form>
    </div>
</div>
