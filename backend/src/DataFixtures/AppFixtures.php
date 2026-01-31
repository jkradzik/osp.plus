<?php

declare(strict_types=1);

namespace App\DataFixtures;

use App\Entity\Decoration;
use App\Entity\DecorationDictionary;
use App\Entity\EquipmentDictionary;
use App\Entity\Member;
use App\Entity\MembershipFee;
use App\Entity\PersonalEquipment;
use App\Entity\User;
use App\Enum\FeeStatus;
use App\Enum\MembershipStatus;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class AppFixtures extends Fixture implements DependentFixtureInterface
{
    public function __construct(
        private readonly UserPasswordHasherInterface $passwordHasher,
    ) {}

    public function getDependencies(): array
    {
        return [DictionaryFixtures::class];
    }

    public function load(ObjectManager $manager): void
    {
        $this->loadUsers($manager);
        $this->loadMembers($manager);
        $this->loadDecorations($manager);
        $this->loadEquipment($manager);

        $manager->flush();
    }

    private function loadUsers(ObjectManager $manager): void
    {
        $admin = new User();
        $admin->email = 'admin@osp.plus';
        $admin->password = $this->passwordHasher->hashPassword($admin, 'admin123');
        $admin->roles = ['ROLE_ADMIN'];

        $manager->persist($admin);

        $user = new User();
        $user->email = 'user@osp.plus';
        $user->password = $this->passwordHasher->hashPassword($user, 'user123');
        $user->roles = ['ROLE_USER'];

        $manager->persist($user);
    }

    private function loadMembers(ObjectManager $manager): void
    {
        $membersData = [
            [
                'firstName' => 'Jan',
                'lastName' => 'Kowalski',
                'pesel' => '85010112345',
                'email' => 'jan.kowalski@example.com',
                'phone' => '500100200',
                'birthDate' => '1985-01-01',
                'joinDate' => '2010-05-15',
                'status' => MembershipStatus::Active,
                'boardPosition' => 'Prezes',
                'fees' => [
                    ['year' => 2023, 'amount' => '50.00', 'status' => FeeStatus::Paid, 'paidAt' => '2023-02-15'],
                    ['year' => 2024, 'amount' => '50.00', 'status' => FeeStatus::Paid, 'paidAt' => '2024-03-10'],
                    ['year' => 2025, 'amount' => '50.00', 'status' => FeeStatus::Paid, 'paidAt' => '2025-01-20'],
                    ['year' => 2026, 'amount' => '50.00', 'status' => FeeStatus::Unpaid],
                ],
            ],
            [
                'firstName' => 'Anna',
                'lastName' => 'Nowak',
                'pesel' => '90050567890',
                'email' => 'anna.nowak@example.com',
                'phone' => '600200300',
                'birthDate' => '1990-05-05',
                'joinDate' => '2015-03-20',
                'status' => MembershipStatus::Active,
                'fees' => [
                    ['year' => 2023, 'amount' => '50.00', 'status' => FeeStatus::Paid, 'paidAt' => '2023-03-01'],
                    ['year' => 2024, 'amount' => '50.00', 'status' => FeeStatus::Overdue],
                    ['year' => 2025, 'amount' => '50.00', 'status' => FeeStatus::Unpaid],
                    ['year' => 2026, 'amount' => '50.00', 'status' => FeeStatus::Unpaid],
                ],
            ],
            [
                'firstName' => 'Piotr',
                'lastName' => 'Wiśniewski',
                'pesel' => '78121298765',
                'email' => 'piotr.wisniewski@example.com',
                'birthDate' => '1978-12-12',
                'joinDate' => '2005-01-10',
                'status' => MembershipStatus::Honorary,
                'fees' => [
                    ['year' => 2024, 'amount' => '0.00', 'status' => FeeStatus::Exempt],
                    ['year' => 2025, 'amount' => '0.00', 'status' => FeeStatus::Exempt],
                    ['year' => 2026, 'amount' => '0.00', 'status' => FeeStatus::Exempt],
                ],
            ],
            [
                'firstName' => 'Maria',
                'lastName' => 'Zielińska',
                'pesel' => '95030354321',
                'phone' => '700300400',
                'birthDate' => '1995-03-03',
                'joinDate' => '2020-09-01',
                'status' => MembershipStatus::Active,
                'boardPosition' => 'Skarbnik',
                'fees' => [
                    ['year' => 2023, 'amount' => '50.00', 'status' => FeeStatus::Paid, 'paidAt' => '2023-01-15'],
                    ['year' => 2024, 'amount' => '50.00', 'status' => FeeStatus::Paid, 'paidAt' => '2024-02-28'],
                    ['year' => 2025, 'amount' => '50.00', 'status' => FeeStatus::Unpaid],
                    ['year' => 2026, 'amount' => '50.00', 'status' => FeeStatus::Unpaid],
                ],
            ],
            [
                'firstName' => 'Tomasz',
                'lastName' => 'Lewandowski',
                'pesel' => '00081011111',
                'email' => 'tomasz.lewandowski@example.com',
                'birthDate' => '2000-08-10',
                'joinDate' => '2022-06-01',
                'status' => MembershipStatus::Youth,
                'fees' => [
                    ['year' => 2024, 'amount' => '25.00', 'status' => FeeStatus::NotApplicable],
                    ['year' => 2025, 'amount' => '25.00', 'status' => FeeStatus::NotApplicable],
                    ['year' => 2026, 'amount' => '25.00', 'status' => FeeStatus::NotApplicable],
                ],
            ],
        ];

        foreach ($membersData as $data) {
            $member = new Member();
            $member->firstName = $data['firstName'];
            $member->lastName = $data['lastName'];
            $member->pesel = $data['pesel'];
            $member->email = $data['email'] ?? null;
            $member->phone = $data['phone'] ?? null;
            $member->address = $data['address'] ?? null;
            $member->birthDate = new \DateTime($data['birthDate']);
            $member->joinDate = new \DateTime($data['joinDate']);
            $member->membershipStatus = $data['status'];
            $member->boardPosition = $data['boardPosition'] ?? null;

            $manager->persist($member);
            $this->addReference('member-' . $data['pesel'], $member);

            foreach ($data['fees'] as $feeData) {
                $fee = new MembershipFee();
                $fee->member = $member;
                $fee->year = $feeData['year'];
                $fee->amount = $feeData['amount'];
                $fee->status = $feeData['status'];
                $fee->paidAt = isset($feeData['paidAt']) ? new \DateTime($feeData['paidAt']) : null;

                $manager->persist($fee);
            }
        }
    }

    private function loadDecorations(ObjectManager $manager): void
    {
        // Jan Kowalski (20+ lat stażu) - kilka odznaczeń
        $janKowalski = $this->getReference('member-85010112345', Member::class);

        $decoration1 = new Decoration();
        $decoration1->member = $janKowalski;
        $decoration1->type = $this->getReference(DictionaryFixtures::DECORATION_WYSLUGA_10, DecorationDictionary::class);
        $decoration1->awardedAt = new \DateTime('2020-05-15');
        $decoration1->awardedBy = 'Zarząd Oddziału Powiatowego ZOSP RP';
        $manager->persist($decoration1);

        $decoration2 = new Decoration();
        $decoration2->member = $janKowalski;
        $decoration2->type = $this->getReference(DictionaryFixtures::DECORATION_MEDAL_BRAZOWY, DecorationDictionary::class);
        $decoration2->awardedAt = new \DateTime('2022-05-04');
        $decoration2->awardedBy = 'Zarząd Główny ZOSP RP';
        $decoration2->certificateNumber = 'MZP/B/2022/1234';
        $manager->persist($decoration2);

        // Piotr Wiśniewski (20+ lat stażu, honorowy) - więcej odznaczeń
        $piotrWisniewski = $this->getReference('member-78121298765', Member::class);

        $decoration3 = new Decoration();
        $decoration3->member = $piotrWisniewski;
        $decoration3->type = $this->getReference(DictionaryFixtures::DECORATION_WYSLUGA_20, DecorationDictionary::class);
        $decoration3->awardedAt = new \DateTime('2025-01-10');
        $decoration3->awardedBy = 'Zarząd Oddziału Powiatowego ZOSP RP';
        $manager->persist($decoration3);

        $decoration4 = new Decoration();
        $decoration4->member = $piotrWisniewski;
        $decoration4->type = $this->getReference(DictionaryFixtures::DECORATION_MEDAL_SREBRNY, DecorationDictionary::class);
        $decoration4->awardedAt = new \DateTime('2020-05-04');
        $decoration4->certificateNumber = 'MZP/S/2020/567';
        $manager->persist($decoration4);

        $decoration5 = new Decoration();
        $decoration5->member = $piotrWisniewski;
        $decoration5->type = $this->getReference(DictionaryFixtures::DECORATION_ZLOTY_ZNAK, DecorationDictionary::class);
        $decoration5->awardedAt = new \DateTime('2023-09-15');
        $decoration5->awardedBy = 'Zarząd Główny ZOSP RP';
        $manager->persist($decoration5);

        // Anna Nowak - jedno odznaczenie
        $annaNowak = $this->getReference('member-90050567890', Member::class);

        $decoration6 = new Decoration();
        $decoration6->member = $annaNowak;
        $decoration6->type = $this->getReference(DictionaryFixtures::DECORATION_STRAZAK_WZOROWY, DecorationDictionary::class);
        $decoration6->awardedAt = new \DateTime('2018-05-04');
        $manager->persist($decoration6);
    }

    private function loadEquipment(ObjectManager $manager): void
    {
        // Jan Kowalski - pełne wyposażenie
        $janKowalski = $this->getReference('member-85010112345', Member::class);

        $eq1 = new PersonalEquipment();
        $eq1->member = $janKowalski;
        $eq1->type = $this->getReference(DictionaryFixtures::EQUIPMENT_KURTKA, EquipmentDictionary::class);
        $eq1->size = 'L';
        $eq1->issuedAt = new \DateTime('2020-03-15');
        $manager->persist($eq1);

        $eq2 = new PersonalEquipment();
        $eq2->member = $janKowalski;
        $eq2->type = $this->getReference(DictionaryFixtures::EQUIPMENT_SPODNIE, EquipmentDictionary::class);
        $eq2->size = 'L';
        $eq2->issuedAt = new \DateTime('2020-03-15');
        $manager->persist($eq2);

        $eq3 = new PersonalEquipment();
        $eq3->member = $janKowalski;
        $eq3->type = $this->getReference(DictionaryFixtures::EQUIPMENT_HELM, EquipmentDictionary::class);
        $eq3->size = 'M';
        $eq3->serialNumber = 'HLM-2020-001';
        $eq3->issuedAt = new \DateTime('2020-03-15');
        $manager->persist($eq3);

        $eq4 = new PersonalEquipment();
        $eq4->member = $janKowalski;
        $eq4->type = $this->getReference(DictionaryFixtures::EQUIPMENT_BUTY, EquipmentDictionary::class);
        $eq4->size = '43';
        $eq4->issuedAt = new \DateTime('2020-03-15');
        $manager->persist($eq4);

        // Anna Nowak - częściowe wyposażenie
        $annaNowak = $this->getReference('member-90050567890', Member::class);

        $eq5 = new PersonalEquipment();
        $eq5->member = $annaNowak;
        $eq5->type = $this->getReference(DictionaryFixtures::EQUIPMENT_KURTKA, EquipmentDictionary::class);
        $eq5->size = 'S';
        $eq5->issuedAt = new \DateTime('2021-06-10');
        $manager->persist($eq5);

        $eq6 = new PersonalEquipment();
        $eq6->member = $annaNowak;
        $eq6->type = $this->getReference(DictionaryFixtures::EQUIPMENT_SPODNIE, EquipmentDictionary::class);
        $eq6->size = 'S';
        $eq6->issuedAt = new \DateTime('2021-06-10');
        $manager->persist($eq6);

        $eq7 = new PersonalEquipment();
        $eq7->member = $annaNowak;
        $eq7->type = $this->getReference(DictionaryFixtures::EQUIPMENT_HELM, EquipmentDictionary::class);
        $eq7->size = 'S';
        $eq7->serialNumber = 'HLM-2021-015';
        $eq7->issuedAt = new \DateTime('2021-06-10');
        $manager->persist($eq7);

        // Maria Zielińska - podstawowe wyposażenie
        $mariaZielinska = $this->getReference('member-95030354321', Member::class);

        $eq8 = new PersonalEquipment();
        $eq8->member = $mariaZielinska;
        $eq8->type = $this->getReference(DictionaryFixtures::EQUIPMENT_KURTKA, EquipmentDictionary::class);
        $eq8->size = 'M';
        $eq8->issuedAt = new \DateTime('2022-01-20');
        $manager->persist($eq8);

        $eq9 = new PersonalEquipment();
        $eq9->member = $mariaZielinska;
        $eq9->type = $this->getReference(DictionaryFixtures::EQUIPMENT_REKAWICE, EquipmentDictionary::class);
        $eq9->size = 'M';
        $eq9->issuedAt = new \DateTime('2022-01-20');
        $manager->persist($eq9);

        $eq10 = new PersonalEquipment();
        $eq10->member = $mariaZielinska;
        $eq10->type = $this->getReference(DictionaryFixtures::EQUIPMENT_LATARKA, EquipmentDictionary::class);
        $eq10->serialNumber = 'LAT-2022-008';
        $eq10->issuedAt = new \DateTime('2022-01-20');
        $manager->persist($eq10);
    }
}
