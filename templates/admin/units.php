<?php
script('immo', 'immo-main');
?>
<div id="immo-app" data-immo-view="admin-units">
    <h1><?php p($l->t('Units')); ?></h1>
    <table class="grid">
        <thead>
            <tr>
                <th><?php p($l->t('Label')); ?></th>
                <th><?php p($l->t('Property')); ?></th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($_['units'] as $unit): ?>
                <tr>
                    <td><?php p($unit->getLabel()); ?></td>
                    <td><?php p($unit->getPropertyId()); ?></td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>
