<?php

declare(strict_types=1);

namespace App\DataFixtures;

use App\Entity\FinancialCategory;
use App\Entity\FinancialRecord;
use App\Entity\User;
use App\Enum\FinancialType;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;

class FinancialFixtures extends Fixture implements DependentFixtureInterface
{
    public const CATEGORY_DOTACJA = 'fin-cat-dotacja';
    public const CATEGORY_SKLADKI = 'fin-cat-skladki';
    public const CATEGORY_DAROWIZNY = 'fin-cat-darowizny';
    public const CATEGORY_WYNAJEM = 'fin-cat-wynajem';
    public const CATEGORY_INNE_PRZYCHODY = 'fin-cat-inne-przychody';
    public const CATEGORY_PALIWO = 'fin-cat-paliwo';
    public const CATEGORY_PRZEGLADY = 'fin-cat-przeglady';
    public const CATEGORY_SPRZET = 'fin-cat-sprzet';
    public const CATEGORY_SZKOLENIA = 'fin-cat-szkolenia';
    public const CATEGORY_UBEZPIECZENIA = 'fin-cat-ubezpieczenia';
    public const CATEGORY_MEDIA = 'fin-cat-media';
    public const CATEGORY_INNE_KOSZTY = 'fin-cat-inne-koszty';

    public function getDependencies(): array
    {
        return [AppFixtures::class];
    }

    public function load(ObjectManager $manager): void
    {
        $this->loadCategories($manager);
        $manager->flush();

        $this->loadSampleRecords($manager);
        $manager->flush();
    }

    private function loadCategories(ObjectManager $manager): void
    {
        $incomeCategories = [
            [
                'ref' => self::CATEGORY_DOTACJA,
                'name' => 'Dotacja z gminy',
                'sortOrder' => 10,
            ],
            [
                'ref' => self::CATEGORY_SKLADKI,
                'name' => 'Składki członkowskie',
                'sortOrder' => 20,
            ],
            [
                'ref' => self::CATEGORY_DAROWIZNY,
                'name' => 'Darowizny',
                'sortOrder' => 30,
            ],
            [
                'ref' => self::CATEGORY_WYNAJEM,
                'name' => 'Wynajem remizy',
                'sortOrder' => 40,
            ],
            [
                'ref' => self::CATEGORY_INNE_PRZYCHODY,
                'name' => 'Inne przychody',
                'sortOrder' => 99,
            ],
        ];

        foreach ($incomeCategories as $data) {
            $category = new FinancialCategory();
            $category->name = $data['name'];
            $category->type = FinancialType::Income;
            $category->sortOrder = $data['sortOrder'];

            $manager->persist($category);
            $this->addReference($data['ref'], $category);
        }

        $expenseCategories = [
            [
                'ref' => self::CATEGORY_PALIWO,
                'name' => 'Paliwo',
                'sortOrder' => 10,
            ],
            [
                'ref' => self::CATEGORY_PRZEGLADY,
                'name' => 'Przeglądy i naprawy pojazdów',
                'sortOrder' => 20,
            ],
            [
                'ref' => self::CATEGORY_SPRZET,
                'name' => 'Zakup sprzętu',
                'sortOrder' => 30,
            ],
            [
                'ref' => self::CATEGORY_SZKOLENIA,
                'name' => 'Szkolenia',
                'sortOrder' => 40,
            ],
            [
                'ref' => self::CATEGORY_UBEZPIECZENIA,
                'name' => 'Ubezpieczenia',
                'sortOrder' => 50,
            ],
            [
                'ref' => self::CATEGORY_MEDIA,
                'name' => 'Media i utrzymanie remizy',
                'sortOrder' => 60,
            ],
            [
                'ref' => self::CATEGORY_INNE_KOSZTY,
                'name' => 'Inne koszty',
                'sortOrder' => 99,
            ],
        ];

        foreach ($expenseCategories as $data) {
            $category = new FinancialCategory();
            $category->name = $data['name'];
            $category->type = FinancialType::Expense;
            $category->sortOrder = $data['sortOrder'];

            $manager->persist($category);
            $this->addReference($data['ref'], $category);
        }
    }

    private function loadSampleRecords(ObjectManager $manager): void
    {
        // Get admin user for createdBy
        $adminUser = $manager->getRepository(User::class)->findOneBy(['email' => 'admin@osp.plus']);

        $records = [
            // Przychody 2025
            [
                'type' => FinancialType::Income,
                'category' => self::CATEGORY_DOTACJA,
                'amount' => '15000.00',
                'description' => 'Dotacja z budżetu gminy na 2025 rok',
                'documentNumber' => 'DOT/2025/001',
                'recordedAt' => '2025-01-15',
            ],
            [
                'type' => FinancialType::Income,
                'category' => self::CATEGORY_SKLADKI,
                'amount' => '2500.00',
                'description' => 'Składki członkowskie za 2025 rok',
                'recordedAt' => '2025-02-28',
            ],
            [
                'type' => FinancialType::Income,
                'category' => self::CATEGORY_DAROWIZNY,
                'amount' => '1000.00',
                'description' => 'Darowizna od lokalnej firmy',
                'recordedAt' => '2025-03-10',
            ],
            // Koszty 2025
            [
                'type' => FinancialType::Expense,
                'category' => self::CATEGORY_PALIWO,
                'amount' => '1200.00',
                'description' => 'Paliwo do wozów bojowych - styczeń',
                'documentNumber' => 'FV/2025/0012',
                'recordedAt' => '2025-01-31',
            ],
            [
                'type' => FinancialType::Expense,
                'category' => self::CATEGORY_PALIWO,
                'amount' => '800.00',
                'description' => 'Paliwo do wozów bojowych - luty',
                'documentNumber' => 'FV/2025/0045',
                'recordedAt' => '2025-02-28',
            ],
            [
                'type' => FinancialType::Expense,
                'category' => self::CATEGORY_MEDIA,
                'amount' => '450.00',
                'description' => 'Prąd i woda - I kwartał',
                'documentNumber' => 'FV/2025/0078',
                'recordedAt' => '2025-03-15',
            ],
            [
                'type' => FinancialType::Expense,
                'category' => self::CATEGORY_UBEZPIECZENIA,
                'amount' => '3500.00',
                'description' => 'Ubezpieczenie pojazdów na 2025',
                'documentNumber' => 'POL/2025/1234',
                'recordedAt' => '2025-01-10',
            ],
            [
                'type' => FinancialType::Expense,
                'category' => self::CATEGORY_SZKOLENIA,
                'amount' => '600.00',
                'description' => 'Szkolenie podstawowe dla nowych członków',
                'recordedAt' => '2025-02-20',
            ],
            // Przychody 2026
            [
                'type' => FinancialType::Income,
                'category' => self::CATEGORY_DOTACJA,
                'amount' => '18000.00',
                'description' => 'Dotacja z budżetu gminy na 2026 rok',
                'documentNumber' => 'DOT/2026/001',
                'recordedAt' => '2026-01-10',
            ],
            [
                'type' => FinancialType::Income,
                'category' => self::CATEGORY_WYNAJEM,
                'amount' => '500.00',
                'description' => 'Wynajem sali na imprezę okolicznościową',
                'recordedAt' => '2026-01-20',
            ],
            // Koszty 2026
            [
                'type' => FinancialType::Expense,
                'category' => self::CATEGORY_PALIWO,
                'amount' => '950.00',
                'description' => 'Paliwo do wozów bojowych - styczeń',
                'documentNumber' => 'FV/2026/0008',
                'recordedAt' => '2026-01-25',
            ],
        ];

        foreach ($records as $data) {
            $record = new FinancialRecord();
            $record->type = $data['type'];
            $record->category = $this->getReference($data['category'], FinancialCategory::class);
            $record->amount = $data['amount'];
            $record->description = $data['description'];
            $record->documentNumber = $data['documentNumber'] ?? null;
            $record->recordedAt = new \DateTime($data['recordedAt']);
            $record->createdBy = $adminUser;

            $manager->persist($record);
        }
    }
}
