<?php

declare(strict_types=1);

namespace App\Enum;

enum DecorationCategory: string
{
    case Osp = 'osp';
    case State = 'state';
    case Other = 'other';
}
