<?php

declare(strict_types=1);

namespace App\DataFixtures;

use App\Entity\Member;
use App\Entity\MembershipFee;
use App\Entity\User;
use App\Enum\FeeStatus;
use App\Enum\MembershipStatus;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class AppFixtures extends Fixture
{
    public function __construct(
        private readonly UserPasswordHasherInterface $passwordHasher,
    ) {}

    public function load(ObjectManager $manager): void
    {
        $this->loadUsers($manager);
        $this->loadMembers($manager);

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
}
