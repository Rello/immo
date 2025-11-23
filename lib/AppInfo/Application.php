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

class Application extends App implements IBootstrap {
    public const APP_ID = 'immo';

    public function __construct() {
        parent::__construct(self::APP_ID);
    }

    public function register(IRegistrationContext $context): void {
        $context->registerService(PropertyService::class, function ($c) {
            return $c->get(PropertyService::class);
        });
        $context->registerService(UnitService::class, function ($c) {
            return $c->get(UnitService::class);
        });
        $context->registerService(TenantService::class, function ($c) {
            return $c->get(TenantService::class);
        });
        $context->registerService(LeaseService::class, function ($c) {
            return $c->get(LeaseService::class);
        });
        $context->registerService(BookingService::class, function ($c) {
            return $c->get(BookingService::class);
        });
        $context->registerService(ReportService::class, function ($c) {
            return $c->get(ReportService::class);
        });
        $context->registerService(FileLinkService::class, function ($c) {
            return $c->get(FileLinkService::class);
        });
        $context->registerService(DashboardService::class, function ($c) {
            return $c->get(DashboardService::class);
        });
        $context->registerService(RoleService::class, function ($c) {
            return $c->get(RoleService::class);
        });
        $context->registerService(FilesystemService::class, function ($c) {
            return $c->get(FilesystemService::class);
        });
    }

    public function boot(IBootContext $context): void {
    }
}
