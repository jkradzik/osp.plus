<?php

declare(strict_types=1);

namespace App\Enum;

enum FeeStatus: string
{
    case Unpaid = 'unpaid';
    case Paid = 'paid';
    case Overdue = 'overdue';
    case Exempt = 'exempt';
    case NotApplicable = 'not_applicable';
}
