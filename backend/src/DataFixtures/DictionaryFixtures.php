<?php

declare(strict_types=1);

namespace App\DataFixtures;

use App\Entity\DecorationDictionary;
use App\Entity\EquipmentDictionary;
use App\Enum\DecorationCategory;
use App\Enum\EquipmentCategory;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

class DictionaryFixtures extends Fixture
{
    public const DECORATION_STRAZAK_WZOROWY = 'decoration-strazak-wzorowy';
    public const DECORATION_MEDAL_BRAZOWY = 'decoration-medal-brazowy';
    public const DECORATION_MEDAL_SREBRNY = 'decoration-medal-srebrny';
    public const DECORATION_MEDAL_ZLOTY = 'decoration-medal-zloty';
    public const DECORATION_WYSLUGA_5 = 'decoration-wysluga-5';
    public const DECORATION_WYSLUGA_10 = 'decoration-wysluga-10';
    public const DECORATION_WYSLUGA_15 = 'decoration-wysluga-15';
    public const DECORATION_WYSLUGA_20 = 'decoration-wysluga-20';
    public const DECORATION_WYSLUGA_25 = 'decoration-wysluga-25';
    public const DECORATION_WYSLUGA_30 = 'decoration-wysluga-30';
    public const DECORATION_ZLOTY_ZNAK = 'decoration-zloty-znak';

    public const EQUIPMENT_KURTKA = 'equipment-kurtka';
    public const EQUIPMENT_SPODNIE = 'equipment-spodnie';
    public const EQUIPMENT_HELM = 'equipment-helm';
    public const EQUIPMENT_BUTY = 'equipment-buty';
    public const EQUIPMENT_REKAWICE = 'equipment-rekawice';
    public const EQUIPMENT_KOMINIARKA = 'equipment-kominiarka';
    public const EQUIPMENT_LATARKA = 'equipment-latarka';

    public function load(ObjectManager $manager): void
    {
        $this->loadDecorationDictionary($manager);
        $this->loadEquipmentDictionary($manager);

        $manager->flush();
    }

    private function loadDecorationDictionary(ObjectManager $manager): void
    {
        $decorations = [
            [
                'ref' => self::DECORATION_STRAZAK_WZOROWY,
                'name' => 'Odznaka "Strażak Wzorowy"',
                'category' => DecorationCategory::Osp,
                'requiredYears' => 3,
                'sortOrder' => 10,
            ],
            [
                'ref' => self::DECORATION_MEDAL_BRAZOWY,
                'name' => 'Medal "Za Zasługi dla Pożarnictwa" - brązowy',
                'category' => DecorationCategory::Osp,
                'requiredYears' => 10,
                'sortOrder' => 20,
            ],
            [
                'ref' => self::DECORATION_MEDAL_SREBRNY,
                'name' => 'Medal "Za Zasługi dla Pożarnictwa" - srebrny',
                'category' => DecorationCategory::Osp,
                'requiredYears' => 15,
                'sortOrder' => 21,
            ],
            [
                'ref' => self::DECORATION_MEDAL_ZLOTY,
                'name' => 'Medal "Za Zasługi dla Pożarnictwa" - złoty',
                'category' => DecorationCategory::Osp,
                'requiredYears' => 20,
                'sortOrder' => 22,
            ],
            [
                'ref' => self::DECORATION_WYSLUGA_5,
                'name' => 'Odznaka "Za wysługę lat" - 5 lat',
                'category' => DecorationCategory::Osp,
                'requiredYears' => 5,
                'sortOrder' => 30,
            ],
            [
                'ref' => self::DECORATION_WYSLUGA_10,
                'name' => 'Odznaka "Za wysługę lat" - 10 lat',
                'category' => DecorationCategory::Osp,
                'requiredYears' => 10,
                'sortOrder' => 31,
            ],
            [
                'ref' => self::DECORATION_WYSLUGA_15,
                'name' => 'Odznaka "Za wysługę lat" - 15 lat',
                'category' => DecorationCategory::Osp,
                'requiredYears' => 15,
                'sortOrder' => 32,
            ],
            [
                'ref' => self::DECORATION_WYSLUGA_20,
                'name' => 'Odznaka "Za wysługę lat" - 20 lat',
                'category' => DecorationCategory::Osp,
                'requiredYears' => 20,
                'sortOrder' => 33,
            ],
            [
                'ref' => self::DECORATION_WYSLUGA_25,
                'name' => 'Odznaka "Za wysługę lat" - 25 lat',
                'category' => DecorationCategory::Osp,
                'requiredYears' => 25,
                'sortOrder' => 34,
            ],
            [
                'ref' => self::DECORATION_WYSLUGA_30,
                'name' => 'Odznaka "Za wysługę lat" - 30 lat',
                'category' => DecorationCategory::Osp,
                'requiredYears' => 30,
                'sortOrder' => 35,
            ],
            [
                'ref' => self::DECORATION_ZLOTY_ZNAK,
                'name' => 'Złoty Znak Związku OSP RP',
                'category' => DecorationCategory::Osp,
                'requiredYears' => 25,
                'sortOrder' => 40,
            ],
        ];

        foreach ($decorations as $data) {
            $decoration = new DecorationDictionary();
            $decoration->name = $data['name'];
            $decoration->category = $data['category'];
            $decoration->requiredYears = $data['requiredYears'];
            $decoration->sortOrder = $data['sortOrder'];

            $manager->persist($decoration);
            $this->addReference($data['ref'], $decoration);
        }
    }

    private function loadEquipmentDictionary(ObjectManager $manager): void
    {
        $equipment = [
            [
                'ref' => self::EQUIPMENT_KURTKA,
                'name' => 'Ubranie specjalne - kurtka',
                'category' => EquipmentCategory::Clothing,
                'hasSizes' => true,
            ],
            [
                'ref' => self::EQUIPMENT_SPODNIE,
                'name' => 'Ubranie specjalne - spodnie',
                'category' => EquipmentCategory::Clothing,
                'hasSizes' => true,
            ],
            [
                'ref' => self::EQUIPMENT_HELM,
                'name' => 'Hełm strażacki',
                'category' => EquipmentCategory::Protective,
                'hasSizes' => true,
            ],
            [
                'ref' => self::EQUIPMENT_BUTY,
                'name' => 'Buty specjalne',
                'category' => EquipmentCategory::Protective,
                'hasSizes' => true,
            ],
            [
                'ref' => self::EQUIPMENT_REKAWICE,
                'name' => 'Rękawice',
                'category' => EquipmentCategory::Protective,
                'hasSizes' => true,
            ],
            [
                'ref' => self::EQUIPMENT_KOMINIARKA,
                'name' => 'Kominiarka',
                'category' => EquipmentCategory::Protective,
                'hasSizes' => false,
            ],
            [
                'ref' => self::EQUIPMENT_LATARKA,
                'name' => 'Latarka',
                'category' => EquipmentCategory::Other,
                'hasSizes' => false,
            ],
        ];

        foreach ($equipment as $data) {
            $item = new EquipmentDictionary();
            $item->name = $data['name'];
            $item->category = $data['category'];
            $item->hasSizes = $data['hasSizes'];

            $manager->persist($item);
            $this->addReference($data['ref'], $item);
        }
    }
}
