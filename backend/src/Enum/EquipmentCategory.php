<?php

declare(strict_types=1);

namespace App\Enum;

enum EquipmentCategory: string
{
    case Clothing = 'clothing';
    case Protective = 'protective';
    case Other = 'other';
}
