<?php

namespace App\Enums;

enum StockAdjustmentStatus: string
{
    case PENDING = 'pending';

    case APPROVED = 'approved';

    case REJECTED = 'rejected';
}
