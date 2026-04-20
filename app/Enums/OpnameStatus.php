<?php

namespace App\Enums;

enum OpnameStatus: string
{
    case PENDING = 'pending';

    case APPROVED = 'approved';

    case REJECTED = 'rejected';
}
