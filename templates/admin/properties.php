<?php
script('immo', 'immo-main');
?>
<div id="immo-app" data-immo-view="admin-properties">
    <h1><?php p($l->t('Properties')); ?></h1>
    <table class="grid">
        <thead>
            <tr>
                <th><?php p($l->t('Name')); ?></th>
                <th><?php p($l->t('Address')); ?></th>
                <th><?php p($l->t('Actions')); ?></th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($_['properties'] as $property): ?>
                <tr>
                    <td><a href="<?php p(link_to_route('immo.property.show', ['id' => $property->getId()])); ?>"><?php p($property->getName()); ?></a></td>
                    <td><?php p($property->getAddress()); ?></td>
                    <td>
                        <a class="button" href="<?php p(\OC::$server->getURLGenerator()->linkToRoute('immo.property.show', ['id' => $property->getId()])); ?>">
                            <?php p($l->t('Open')); ?>
                        </a>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>
