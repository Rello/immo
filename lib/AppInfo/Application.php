<?php
namespace OCA\Immo\AppInfo;

use OCP\AppFramework\App;
use OCP\AppFramework\Bootstrap\IBootstrap;
use OCP\AppFramework\Bootstrap\IRegistrationContext;
use OCP\AppFramework\Bootstrap\IBootContext;
use OCA\Immo\Db\PropertyMapper;
use OCA\Immo\Db\UnitMapper;
use OCA\Immo\Db\TenantMapper;
use OCA\Immo\Db\LeaseMapper;
use OCA\Immo\Db\BookingMapper;
use OCA\Immo\Db\FileLinkMapper;
use OCA\Immo\Db\ReportMapper;
use OCA\Immo\Db\RoleMapper;
use OCA\Immo\Db\CostAllocMapper;
use OCA\Immo\Service\RoleService;
use OCA\Immo\Service\PropertyService;
use OCA\Immo\Service\UnitService;
use OCA\Immo\Service\LeaseService;
use OCA\Immo\Service\BookingService;
use OCA\Immo\Service\FileLinkService;
use OCA\Immo\Service\ReportService;
use OCA\Immo\Service\DashboardService;
use OCA\Immo\Service\AllocationService;

class Application extends App implements IBootstrap {
    public const APP_ID = 'immo';

    public function __construct(array $params = []) {
        parent::__construct(self::APP_ID, $params);
    }

    public function register(IRegistrationContext $context): void {
        // Register factories in the registration context so other parts
        // of the app (or tests) can request the services by class name.
        // The closures delegate to the instance getters above to keep
        // wiring centralized and avoid direct container assumptions.
        $context->registerService(PropertyMapper::class, function() {
            return $this->getPropertyMapper();
        });
        $context->registerService(UnitMapper::class, function() {
            return $this->getUnitMapper();
        });
        $context->registerService(TenantMapper::class, function() {
            return $this->getTenantMapper();
        });
        $context->registerService(LeaseMapper::class, function() {
            return $this->getLeaseMapper();
        });
        $context->registerService(BookingMapper::class, function() {
            return $this->getBookingMapper();
        });
        $context->registerService(FileLinkMapper::class, function() {
            return $this->getFileLinkMapper();
        });
        $context->registerService(ReportMapper::class, function() {
            return $this->getReportMapper();
        });
        $context->registerService(RoleMapper::class, function() {
            return $this->getRoleMapper();
        });

        $context->registerService(RoleService::class, function() {
            return $this->getRoleService();
        });
        $context->registerService(PropertyService::class, function() {
            return $this->getPropertyService();
        });
        $context->registerService(UnitService::class, function() {
            return $this->getUnitService();
        });
        $context->registerService(LeaseService::class, function() {
            return $this->getLeaseService();
        });
        $context->registerService(BookingService::class, function() {
            return $this->getBookingService();
        });
        $context->registerService(FileLinkService::class, function() {
            return $this->getFileLinkService();
        });
        $context->registerService(ReportService::class, function() {
            return $this->getReportService();
        });
        $context->registerService(DashboardService::class, function() {
            return $this->getDashboardService();
        });
        $context->registerService(AllocationService::class, function() {
            return $this->getAllocationService();
        });
        $context->registerService(CostAllocMapper::class, function() {
            return $this->getCostAllocMapper();
        });
    }

    public function boot(IBootContext $context): void {
    }

    // Lazy factories for mappers and services to centralize wiring.
    // These are intentionally simple and avoid depending on a specific
    // container API so they are safe to call from other parts of the app.

    private ?PropertyMapper $propertyMapper = null;
    private ?UnitMapper $unitMapper = null;
    private ?TenantMapper $tenantMapper = null;
    private ?LeaseMapper $leaseMapper = null;
    private ?BookingMapper $bookingMapper = null;
    private ?FileLinkMapper $fileLinkMapper = null;
    private ?ReportMapper $reportMapper = null;
    private ?RoleMapper $roleMapper = null;
    private ?CostAllocMapper $costAllocMapper = null;

    private ?RoleService $roleService = null;
    private ?PropertyService $propertyService = null;
    private ?UnitService $unitService = null;
    private ?LeaseService $leaseService = null;
    private ?BookingService $bookingService = null;
    private ?FileLinkService $fileLinkService = null;
    private ?ReportService $reportService = null;
    private ?DashboardService $dashboardService = null;
    private ?AllocationService $allocationService = null;

    public function getPropertyMapper(): PropertyMapper {
        if ($this->propertyMapper === null) {
            $this->propertyMapper = new PropertyMapper(\OC::$server->getDatabaseConnection());
        }
        return $this->propertyMapper;
    }

    public function getUnitMapper(): UnitMapper {
        if ($this->unitMapper === null) {
            $this->unitMapper = new UnitMapper(\OC::$server->getDatabaseConnection());
        }
        return $this->unitMapper;
    }

    public function getTenantMapper(): TenantMapper {
        if ($this->tenantMapper === null) {
            $this->tenantMapper = new TenantMapper(\OC::$server->getDatabaseConnection());
        }
        return $this->tenantMapper;
    }

    public function getLeaseMapper(): LeaseMapper {
        if ($this->leaseMapper === null) {
            $this->leaseMapper = new LeaseMapper(\OC::$server->getDatabaseConnection());
        }
        return $this->leaseMapper;
    }

    public function getBookingMapper(): BookingMapper {
        if ($this->bookingMapper === null) {
            $this->bookingMapper = new BookingMapper(\OC::$server->getDatabaseConnection());
        }
        return $this->bookingMapper;
    }

    public function getFileLinkMapper(): FileLinkMapper {
        if ($this->fileLinkMapper === null) {
            $this->fileLinkMapper = new FileLinkMapper(\OC::$server->getDatabaseConnection());
        }
        return $this->fileLinkMapper;
    }

    public function getReportMapper(): ReportMapper {
        if ($this->reportMapper === null) {
            $this->reportMapper = new ReportMapper(\OC::$server->getDatabaseConnection());
        }
        return $this->reportMapper;
    }

    public function getCostAllocMapper(): CostAllocMapper {
        if ($this->costAllocMapper === null) {
            $this->costAllocMapper = new CostAllocMapper(\OC::$server->getDatabaseConnection());
        }
        return $this->costAllocMapper;
    }

    public function getRoleMapper(): RoleMapper {
        if ($this->roleMapper === null) {
            $this->roleMapper = new RoleMapper(\OC::$server->getDatabaseConnection());
        }
        return $this->roleMapper;
    }

    public function getRoleService(): RoleService {
        if ($this->roleService === null) {
            $this->roleService = new RoleService(\OC::$server->getDatabaseConnection(), \OC::$server->getGroupManager());
        }
        return $this->roleService;
    }

    public function getPropertyService(): PropertyService {
        if ($this->propertyService === null) {
            $this->propertyService = new PropertyService($this->getPropertyMapper(), $this->getRoleService(), \OC::$server->getUserSession(), \OC::$server->getTimeFactory());
        }
        return $this->propertyService;
    }

    public function getUnitService(): UnitService {
        if ($this->unitService === null) {
            $this->unitService = new UnitService($this->getUnitMapper(), $this->getPropertyMapper(), $this->getRoleService(), \OC::$server->getUserSession());
        }
        return $this->unitService;
    }

    public function getLeaseService(): LeaseService {
        if ($this->leaseService === null) {
            $this->leaseService = new LeaseService($this->getLeaseMapper(), $this->getUnitMapper(), $this->getTenantMapper(), $this->getRoleService(), \OC::$server->getUserSession());
        }
        return $this->leaseService;
    }

    public function getBookingService(): BookingService {
        if ($this->bookingService === null) {
            $this->bookingService = new BookingService($this->getBookingMapper(), $this->getPropertyMapper(), $this->getRoleService(), \OC::$server->getUserSession(), $this->getAllocationService());
        }
        return $this->bookingService;
    }

    public function getFileLinkService(): FileLinkService {
        if ($this->fileLinkService === null) {
            $this->fileLinkService = new FileLinkService($this->getFileLinkMapper(), \OC::$server->getUserSession(), \OC::$server->getUserManager());
        }
        return $this->fileLinkService;
    }

    public function getReportService(): ReportService {
        if ($this->reportService === null) {
            $this->reportService = new ReportService($this->getReportMapper(), \OC::$server->getUserSession(), \OC::$server->getGroupManager());
        }
        return $this->reportService;
    }

    public function getDashboardService(): DashboardService {
        if ($this->dashboardService === null) {
            $this->dashboardService = new DashboardService($this->getPropertyService(), $this->getUnitService(), $this->getLeaseService());
        }
        return $this->dashboardService;
    }

    public function getAllocationService(): AllocationService {
        if ($this->allocationService === null) {
            $this->allocationService = new AllocationService($this->getCostAllocMapper(), $this->getLeaseMapper());
        }
        return $this->allocationService;
    }
}
