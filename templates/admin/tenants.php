<?php
script('immo', 'immo-main');
?>
<div id="immo-app" data-immo-view="admin-tenants">
    <h1><?php p($l->t('Tenants')); ?></h1>
    <table class="grid">
        <thead>
            <tr>
                <th><?php p($l->t('Name')); ?></th>
                <th><?php p($l->t('Linked user')); ?></th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($_['tenants'] as $tenant): ?>
                <tr>
                    <td><?php p($tenant->getName()); ?></td>
                    <td><?php p($tenant->getNcUserId() ?: '–'); ?></td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>
