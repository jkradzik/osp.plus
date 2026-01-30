<?php

declare(strict_types=1);

namespace App\Enum;

enum MembershipStatus: string
{
    case Active = 'active';
    case Inactive = 'inactive';
    case Honorary = 'honorary';
    case Supporting = 'supporting';
    case Youth = 'youth';
    case Removed = 'removed';
    case Deceased = 'deceased';
}
