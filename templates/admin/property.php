<?php
script('immo', 'immo-main');
?>
<div id="immo-app" data-immo-view="admin-property">
    <h1><?php p($l->t('Property %s', [$_['property']->getName()])); ?></h1>
    <section>
        <h2><?php p($l->t('Units')); ?></h2>
        <table class="grid">
            <thead>
                <tr>
                    <th><?php p($l->t('Label')); ?></th>
                    <th><?php p($l->t('Area (sqm)')); ?></th>
                    <th><?php p($l->t('Type')); ?></th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($_['units'] as $unit): ?>
                    <tr>
                        <td><?php p($unit->getLabel()); ?></td>
                        <td><?php p($unit->getAreaSqm()); ?></td>
                        <td><?php p($unit->getType()); ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </section>
</div>
