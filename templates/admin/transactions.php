<?php
script('immo', 'immo-main');
?>
<div id="immo-app" data-immo-view="admin-transactions">
    <h1><?php p($l->t('Transactions')); ?></h1>
    <form method="get" data-immo-filter>
        <label>
            <?php p($l->t('Year')); ?>
            <input type="number" name="year" value="<?php p($_['year'] ?? date('Y')); ?>">
        </label>
        <button type="submit"><?php p($l->t('Filter')); ?></button>
    </form>
    <table class="grid" data-immo-table>
        <thead>
            <tr>
                <th><?php p($l->t('Date')); ?></th>
                <th><?php p($l->t('Type')); ?></th>
                <th><?php p($l->t('Amount')); ?></th>
                <th><?php p($l->t('Category')); ?></th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($_['transactions'] as $transaction): ?>
                <tr>
                    <td><?php p($transaction->getDate()->format('Y-m-d')); ?></td>
                    <td><?php p($transaction->getType()); ?></td>
                    <td><?php p(number_format($transaction->getAmount(), 2)); ?></td>
                    <td><?php p($transaction->getCategory()); ?></td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>
