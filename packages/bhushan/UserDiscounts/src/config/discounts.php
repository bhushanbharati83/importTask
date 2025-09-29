<?php
return [
// stacking order: order of types applied. If types equal, priority then id 

'stacking_order' => [
'percentage',
'fixed'
],
    // global cap on total percent discount (0-100). e.g. 50 means at most 50%
    'max_percentage_cap' => 50,
    // rounding precision for money after applying discounts
    'rounding' => [
        'decimals' => 2,
        'mode' => PHP_ROUND_HALF_UP,
    ],
];