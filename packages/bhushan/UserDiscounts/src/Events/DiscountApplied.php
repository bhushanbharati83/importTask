<?php

namespace Vendor\UserDiscounts\Events;

use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;

class DiscountApplied
{
    use Dispatchable, InteractsWithSockets;
    public $userId;
    public $originalAmount;
    public $finalAmount;
    public $applied;
    public function __construct(int $userId, float $originalAmount, float $finalAmount, array $applied)
    {
        $this->userId = $userId;
        $this->originalAmount = $originalAmount;
        $this->finalAmount = $finalAmount;
        $this->applied = $applied;
    }
}
