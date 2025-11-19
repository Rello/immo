<?php
namespace OCA\Immo\AppInfo;

use OCA\Immo\Service\BookingService;
use OCA\Immo\Service\DashboardService;
use OCA\Immo\Service\FileLinkService;
use OCA\Immo\Service\FilesystemService;
use OCA\Immo\Service\LeaseService;
use OCA\Immo\Service\PropertyService;
use OCA\Immo\Service\ReportService;
use OCA\Immo\Service\RoleService;
use OCA\Immo\Service\TenantService;
use OCA\Immo\Service\UnitService;
use OCP\AppFramework\App;
use OCP\AppFramework\Bootstrap\IBootstrap;
use OCP\AppFramework\Bootstrap\IRegistrationContext;
use OCP\AppFramework\Bootstrap\IBootContext;
use OCP\ILogger;
use OCP\L10N\IFactory as L10NFactory;
use OCP\IDBConnection;
use OCP\Files\IRootFolder;
use OCP\IGroupManager;
use OCP\IUserSession;

class Application extends App implements IBootstrap {
    public const APP_ID = 'immo';

    public function __construct(array $params = []) {
        parent::__construct(self::APP_ID, $params);
    }

    public function register(IRegistrationContext $context): void {
        $context->registerService(RoleService::class, function ($c) {
            return new RoleService(
                $c->get(IDBConnection::class),
                $c->get(IGroupManager::class)
            );
        });

        $context->registerService('OCA\\Immo\\Db\\PropertyMapper', fn($c) => new \OCA\Immo\Db\PropertyMapper(
            $c->get(IDBConnection::class)
        ));
        $context->registerService('OCA\\Immo\\Db\\UnitMapper', fn($c) => new \OCA\Immo\Db\UnitMapper(
            $c->get(IDBConnection::class)
        ));
        $context->registerService('OCA\\Immo\\Db\\TenantMapper', fn($c) => new \OCA\Immo\Db\TenantMapper(
            $c->get(IDBConnection::class)
        ));
        $context->registerService('OCA\\Immo\\Db\\LeaseMapper', fn($c) => new \OCA\Immo\Db\LeaseMapper(
            $c->get(IDBConnection::class)
        ));
        $context->registerService('OCA\\Immo\\Db\\BookingMapper', fn($c) => new \OCA\Immo\Db\BookingMapper(
            $c->get(IDBConnection::class)
        ));
        $context->registerService('OCA\\Immo\\Db\\FileLinkMapper', fn($c) => new \OCA\Immo\Db\FileLinkMapper(
            $c->get(IDBConnection::class)
        ));
        $context->registerService('OCA\\Immo\\Db\\ReportMapper', fn($c) => new \OCA\Immo\Db\ReportMapper(
            $c->get(IDBConnection::class)
        ));
        $context->registerService('OCA\\Immo\\Db\\RoleMapper', fn($c) => new \OCA\Immo\Db\RoleMapper(
            $c->get(IDBConnection::class)
        ));

        $context->registerService(PropertyService::class, fn($c) => new PropertyService(
            $c->get('OCA\\Immo\\Db\\PropertyMapper'),
            $c->get(RoleService::class),
            $c->get(IUserSession::class)
        ));

        $context->registerService(UnitService::class, fn($c) => new UnitService(
            $c->get('OCA\\Immo\\Db\\UnitMapper'),
            $c->get('OCA\\Immo\\Db\\PropertyMapper'),
            $c->get(RoleService::class),
            $c->get(IUserSession::class)
        ));

        $context->registerService(TenantService::class, fn($c) => new TenantService(
            $c->get('OCA\\Immo\\Db\\TenantMapper'),
            $c->get(RoleService::class),
            $c->get(IUserSession::class)
        ));

        $context->registerService(LeaseService::class, fn($c) => new LeaseService(
            $c->get('OCA\\Immo\\Db\\LeaseMapper'),
            $c->get('OCA\\Immo\\Db\\UnitMapper'),
            $c->get('OCA\\Immo\\Db\\TenantMapper'),
            $c->get(RoleService::class),
            $c->get(IUserSession::class)
        ));

        $context->registerService(BookingService::class, fn($c) => new BookingService(
            $c->get('OCA\\Immo\\Db\\BookingMapper'),
            $c->get('OCA\\Immo\\Db\\PropertyMapper'),
            $c->get(RoleService::class),
            $c->get(IUserSession::class)
        ));

        $context->registerService(FilesystemService::class, fn($c) => new FilesystemService(
            $c->get(IRootFolder::class),
            $c->get(IUserSession::class)
        ));

        $context->registerService(ReportService::class, fn($c) => new ReportService(
            $c->get('OCA\\Immo\\Db\\ReportMapper'),
            $c->get(PropertyService::class),
            $c->get(BookingService::class),
            $c->get(LeaseService::class),
            $c->get(FilesystemService::class),
            $c->get(L10NFactory::class),
            $c->get(IUserSession::class),
            $c->get(RoleService::class)
        ));

        $context->registerService(FileLinkService::class, fn($c) => new FileLinkService(
            $c->get('OCA\\Immo\\Db\\FileLinkMapper'),
            $c->get(RoleService::class),
            $c->get(PropertyService::class),
            $c->get(UnitService::class),
            $c->get(TenantService::class),
            $c->get(LeaseService::class),
            $c->get(BookingService::class),
            $c->get(ReportService::class),
            $c->get(IRootFolder::class),
            $c->get(IUserSession::class)
        ));

        $context->registerService(DashboardService::class, fn($c) => new DashboardService(
            $c->get(PropertyService::class),
            $c->get(UnitService::class),
            $c->get(LeaseService::class)
        ));
    }

    public function boot(IBootContext $context): void {
    }
}
