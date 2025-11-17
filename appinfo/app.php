<?php
/** @var \OCP\AppFramework\App $app */
$app = \OC::$server->query(\OCA\Immo\AppInfo\Application::class);

$container = $app->getContainer();
$navigationManager = $container->getServer()->getNavigationManager();
$navigationManager->add(function () use ($container) {
    return [
        'id' => 'immo',
        'order' => 10,
        'href' => $container->getURLGenerator()->linkToRoute('immo.dashboard.index'),
        'icon' => $container->getURLGenerator()->imagePath('immo', 'app.svg'),
        'name' => $container->getL10N('immo')->t('Immo'),
    ];
});
