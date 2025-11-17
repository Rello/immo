<?php
script('immo', 'immo-main');
?>
<div id="immo-app" data-immo-view="admin-leases">
    <h1><?php p($l->t('Leases')); ?></h1>
    <table class="grid">
        <thead>
            <tr>
                <th><?php p($l->t('Unit')); ?></th>
                <th><?php p($l->t('Tenant')); ?></th>
                <th><?php p($l->t('Start')); ?></th>
                <th><?php p($l->t('End')); ?></th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($_['leases'] as $lease): ?>
                <tr>
                    <td><?php p($lease->getUnitId()); ?></td>
                    <td><?php p($lease->getTenantId()); ?></td>
                    <td><?php p($lease->getStartDate()->format('Y-m-d')); ?></td>
                    <td><?php p($lease->getEndDate() ? $lease->getEndDate()->format('Y-m-d') : '–'); ?></td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>
