<?php

namespace Vendor\UserDiscounts\Events;

use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;

class DiscountAssigned
{
    use Dispatchable, InteractsWithSockets;
    public $userId;
    public $discountId;
    public function __construct(int $userId, int $discountId)
    {
        $this->userId = $userId;
        $this->discountId = $discountId;
    }
}
