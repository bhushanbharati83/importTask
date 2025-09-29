<?php

namespace Vendor\UserDiscounts\Managers;

use Vendor\UserDiscounts\Contracts\DiscountManagerInterface;
use Vendor\UserDiscounts\Models\Discount;
use Vendor\UserDiscounts\Models\UserDiscount;
use Vendor\UserDiscounts\Models\DiscountAudit;
use Vendor\UserDiscounts\Events\DiscountAssigned;
use Vendor\UserDiscounts\Events\DiscountRevoked;
use Vendor\UserDiscounts\Events\DiscountApplied;

class DiscountManager implements DiscountManagerInterface
{
    protected $config;
    public function __construct(array $config = [])
    {
        $this->config = $config;
    }
    public function assign(int $discountId, int $userId): void
    {
        $discount = Discount::findOrFail($discountId);
        UserDiscount::firstOrCreate([
            'user_id' => $userId,
            'discount_id' => $discountId,
        ]);
        DiscountAudit::create(['user_id' => $userId, 'discount_id' => $discountId, 'action' => 'assigned']);
        event(new DiscountAssigned($userId, $discountId));
    }

    public function eligibleFor(int $userId): array
    {
        $now = now();
        $query = Discount::where('active', true)
            ->where(function ($q) use ($now) {
                $q->whereNull('starts_at')->orWhere('starts_at', '<=', $now);
            })
            ->where(function ($q) use ($now) {
                $q->whereNull('ends_at')->orWhere('ends_at', '>=', $now);
            })
            ->whereIn('id', function ($q) use ($userId) {
                $q->select('discount_id')->from('user_discounts')->where('user_id', $userId);
            });
        $discounts = $query->get()->filter->isActive()->values();
        return $discounts->toArray();
    }
    /**
     * Apply discounts in deterministic order and return final amount and applied discounts details.
     */
    public function apply(int $userId, float $amount): array
    {
        $original = $amount;
        $applied = [];
        // load eligible discount models
        $eligible = Discount::where('active', true)
            ->where(function ($q) {
                $q->whereNull('starts_at')->orWhere('starts_at', '<=', now());
            })
            ->where(function ($q) {
                $q->whereNull('ends_at')->orWhere('ends_at', '>=', now());
            })
            ->whereIn('id', function ($q) use ($userId) {
                $q->select('discount_id')->from('user_discounts')->where('user_id', $userId);
            })
            ->get();
        // deterministic ordering: type per config stacking_order, then priority desc, then id asc
        $order = $this->config['stacking_order'] ?? ['percentage', 'fixed'];
        $eligible = $eligible->sortBy(function ($d) use ($order) {
            $typeIdx = array_search($d->type, $order);
            if ($typeIdx === false) $typeIdx = 999;
            // negative priority so higher priority first. then id to keep deterministic
            return sprintf(
                '%03d-%05d-%010d',
                $typeIdx,
                - ($d->priority ?? 0),
                $d->id
            );
        })->values();
        // compute cap: capped percentage remaining
        $remainingPercentageCap = $this->config['max_percentage_cap'] ?? 100;
        $totalPercentageApplied = 0.0;
        foreach ($eligible as $d) {
            // per-user usage cap enforcement
            $userDiscount = UserDiscount::firstOrCreate([
                'user_id' => $userId,
                'discount_id' => $d->id
            ]);
            if (
                $d->usage_cap_per_user !== null && $userDiscount->usage_count >=
                $d->usage_cap_per_user
            ) {
                continue; // skip exhausted
            }
            if (! $d->isActive()) continue;
            $appliedAmount = 0.0;
            if ($d->type === 'percentage') {
                // enforce max percentage cap across all percentage discounts
                $allowedRemaining = max(0, $remainingPercentageCap -
                    $totalPercentageApplied);
                $takePercent = min($d->value, $allowedRemaining);
                if ($takePercent <= 0) continue;
                $appliedAmount = ($takePercent / 100.0) * $amount;
                $totalPercentageApplied += $takePercent;
            } else { // fixed
                $appliedAmount = min($d->value, $amount);
            }
            // rounding
            $decimals = $this->config['rounding']['decimals'] ?? 2;
            $mode = $this->config['rounding']['mode'] ?? PHP_ROUND_HALF_UP;
            $appliedAmount = round($appliedAmount, $decimals, $mode);
            // idempotency: if appliedAmount is 0, skip
            if ($appliedAmount <= 0) continue;
            $amount -= $appliedAmount;
            $amount = round($amount, $decimals, $mode);
            // record usage & audit
            $userDiscount->increment('usage_count');
            DiscountAudit::create(['user_id' => $userId, 'discount_id' => $d->id, 'action' => 'applied', 'meta' => ['applied' => $appliedAmount, 'remaining'
            => $amount]]);
            $applied[] = [
                'discount_id' => $d->id,
                'type' => $d->type,
                'value' => $d->value,
                'applied_amount' => $appliedAmount,
            ];
            // event
            event(new DiscountApplied($userId, $original, $amount, $applied));
            // stop if amount is zero
            if ($amount <= 0) break;
        }
        return ['amount' => $amount, 'original' => $original, 'applied' =>
        $applied];
    }
}
