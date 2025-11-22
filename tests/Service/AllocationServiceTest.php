<?php
declare(strict_types=1);

use OCA\Immo\Service\AllocationService;
use OCA\Immo\Db\Lease;
use OCA\Immo\Db\CostAlloc;
use PHPUnit\Framework\TestCase;

class AllocationServiceTest extends TestCase {
    public function testAllocateEvenSplitAcrossTwoHalfYearLeases() {
        $lease1 = new Lease();
        $lease1->setId(1);
        $lease1->setStart('2025-01-01');
        $lease1->setEnd('2025-06-30');

        $lease2 = new Lease();
        $lease2->setId(2);
        $lease2->setStart('2025-07-01');
        $lease2->setEnd('2025-12-31');

        $leaseMapper = $this->createMock(OCA\Immo\Db\LeaseMapper::class);
        $leaseMapper->method('findByOwner')->willReturn([$lease1, $lease2]);

        $allocMapper = $this->createMock(OCA\Immo\Db\CostAllocMapper::class);
        // return the created entity as-is
        $allocMapper->method('create')->willReturnCallback(function($a) { return $a; });

        $svc = new AllocationService($allocMapper, $leaseMapper);
        $created = $svc->allocateAnnualTransaction(10, 5, 2025, '1200.00', 'owner');

        // Two leases * 6 months each = 12 allocations
        $this->assertCount(12, $created);
        // check one sample allocation amount (should be 100.00 per month)
        /** @var CostAlloc $sample */
        $sample = $created[0];
        $this->assertEquals('100.00', $sample->getAmt());
    }
}
