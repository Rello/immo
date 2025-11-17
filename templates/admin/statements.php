<?php
script('immo', 'immo-main');
?>
<div id="immo-app" data-immo-view="admin-statements">
    <h1><?php p($l->t('Statements')); ?></h1>
    <table class="grid">
        <thead>
            <tr>
                <th><?php p($l->t('Year')); ?></th>
                <th><?php p($l->t('Scope')); ?></th>
                <th><?php p($l->t('File')); ?></th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($_['statements'] as $statement): ?>
                <tr>
                    <td><?php p($statement->getYear()); ?></td>
                    <td><?php p($statement->getScopeType()); ?> #<?php p($statement->getScopeId()); ?></td>
                    <td><?php p($statement->getFilePath()); ?></td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>
