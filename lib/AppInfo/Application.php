<?php
namespace OCA\Immo\AppInfo;

use OCP\AppFramework\App;
use OCP\AppFramework\IAppContainer;
use OCP\AppFramework\Bootstrap\IBootstrap;
use OCP\AppFramework\Bootstrap\IBootContext;
use OCP\AppFramework\Bootstrap\IRegistrationContext;
use OCP\ILogger;

class Application extends App implements IBootstrap {
    public const APP_ID = 'immo';

    public function __construct(array $urlParams = []) {
        parent::__construct(self::APP_ID, $urlParams);
    }

    public function register(IRegistrationContext $context): void {
        // Register services via service.xml or Container? for now rely on default auto wiring
    }

    public function boot(IBootContext $context): void {
        $container = $context->getAppContainer();
        $container->registerService('Logger', function (IAppContainer $c) {
            return $c->getServer()->getLogger();
        });
    }
}
