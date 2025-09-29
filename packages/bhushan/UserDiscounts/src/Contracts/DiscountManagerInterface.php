<?php
namespace Vendor\UserDiscounts\Contracts;
use Illuminate\Contracts\Support\Arrayable;
interface DiscountManagerInterface
{
public function assign(int $discountId, int $userId): void;
public function revoke(int $discountId, int $userId): void;
public function eligibleFor(int $userId): array; // returns eligible 

public function apply(int $userId, float $amount): array; // returns
}