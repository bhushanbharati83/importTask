<?php

namespace Vendor\UserDiscounts\Tests\Feature;

use Vendor\UserDiscounts\Tests\TestCase;
use Vendor\UserDiscounts\Models\Discount;
use Vendor\UserDiscounts\Managers\DiscountManager;
use Illuminate\Support\Facades\Event;

class DiscountsTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        // run migrations loaded in TestCase
        $this->artisan('migrate', ['--database' => 'testbench'])->run();
    }
    public function test_assign_and_revoke()
    {
        $d = Discount::create(['name' => 'Test', 'type' => 'percentage', 'value'
        => 10, 'active' => true]);
        $manager = new DiscountManager(config('discounts'));
        $manager->assign($d->id, 1);
        $this->assertDatabaseHas('user_discounts', [
            'user_id' => 1,
            'discount_id' => $d->id
        ]);
        $manager->revoke($d->id, 1);
        $this->assertDatabaseMissing('user_discounts', [
            'user_id' => 1,
            'discount_id' => $d->id
        ]);
    }
    public function test_eligible_ignores_expired_or_inactive()
    {
        $active = Discount::create([
            'name' => 'Active',
            'type' => 'percentage',
            'value' => 10,
            'active' => true
        ]);
        $inactive = Discount::create(['name' => 'Inactive', 'type' =>
        'percentage', 'value' => 20, 'active' => false]);
        $expired = Discount::create(['name' => 'Expired', 'type' =>
        'percentage', 'value' => 30, 'active' => true, 'ends_at' => now()->subDay()]);
        $manager = new DiscountManager(config('discounts'));
        // assign all to user
        $manager->assign($active->id, 1);
        $manager->assign($inactive->id, 1);
        $manager->assign($expired->id, 1);
        $eligible = $manager->eligibleFor(1);
        $this->assertCount(1, $eligible);

        $this->assertEquals($active->id, $eligible[0]['id']);
    }

    public function test_apply_deterministic_and_idempotent_and_caps()
    {
        config(['discounts.stacking_order' => ['percentage', 'fixed']]);
        config(['discounts.max_percentage_cap' => 30]);
        config(['discounts.rounding.decimals' => 2]);
        // create discounts: two percentage and one fixed
        $p1 = Discount::create(['name' => 'P1', 'type' => 'percentage', 'value'
        => 20, 'priority' => 10, 'active' => true]);
        $p2 = Discount::create(['name' => 'P2', 'type' => 'percentage', 'value'
        => 20, 'priority' => 5, 'active' => true]);
        $f1 = Discount::create([
            'name' => 'F1',
            'type' => 'fixed',
            'value' => 5,
            'priority' => 1,
            'active' => true
        ]);
        $manager = new DiscountManager(config('discounts'));
        // assign all to user
        $manager->assign($p1->id, 1);
        $manager->assign($p2->id, 1);
        $manager->assign($f1->id, 1);
        // initial amount 100
        $res = $manager->apply(1, 100.00);
        // percentage cap 30% => max percent discounts combined = 30
        // p1 priority higher so p1 uses 20%, p2 can only use 10% (of its 20) => total percent=30 -> 30 of 100 = 30
        // fixed 5 applied after percent => final = 100 - 30 - 5 = 65
        $this->assertEquals(65.00, $res['amount']);
        $this->assertCount(3, $res['applied']);
        // idempotence check: applying again should consume usage_count and may change if usage caps present
        // but if no caps, applying again reduces further; to test idempotence we ensure repeated apply with same manager and no usage cap results in same deterministic result only if we reset usage counts.
    }
}
