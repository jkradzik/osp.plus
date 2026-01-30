<?php

declare(strict_types=1);

namespace App\Tests\Integration;

use App\Tests\ApiTestCase;

class MemberApiTest extends ApiTestCase
{
    private string $testPesel = '12345678901';

    public function testGetMembersCollectionRequiresAuth(): void
    {
        static::createClient()->request('GET', '/api/members');

        $this->assertResponseStatusCodeSame(401);
    }

    public function testGetMembersCollection(): void
    {
        $response = $this->authenticatedRequest('GET', '/api/members');

        $this->assertResponseIsSuccessful();
        $this->assertJsonLdResponse();

        $data = $response->toArray();
        $this->assertJsonLdCollection($data);
        $this->assertGreaterThan(0, count($data['member']));
    }

    public function testGetMemberItem(): void
    {
        // First get the collection to find a member ID
        $collectionResponse = $this->authenticatedRequest('GET', '/api/members');
        $collection = $collectionResponse->toArray();
        $firstMember = $collection['member'][0];
        $memberId = $firstMember['id'];

        $response = $this->authenticatedRequest('GET', '/api/members/' . $memberId);

        $this->assertResponseIsSuccessful();
        $this->assertJsonLdResponse();

        $data = $response->toArray();
        $this->assertJsonLdItem($data);
        $this->assertEquals($memberId, $data['id']);
        $this->assertArrayHasKey('firstName', $data);
        $this->assertArrayHasKey('lastName', $data);
        $this->assertArrayHasKey('pesel', $data);
    }

    public function testGetMemberNotFound(): void
    {
        $this->authenticatedRequest('GET', '/api/members/999999');

        $this->assertResponseStatusCodeSame(404);
    }

    public function testCreateMemberSuccess(): void
    {
        $uniquePesel = '99' . str_pad((string) random_int(0, 999999999), 9, '0', STR_PAD_LEFT);

        $response = $this->authenticatedRequest('POST', '/api/members', [
            'headers' => ['Content-Type' => 'application/ld+json'],
            'json' => [
                'firstName' => 'Test',
                'lastName' => 'Testowy',
                'pesel' => $uniquePesel,
                'birthDate' => '1990-05-15',
                'joinDate' => '2020-01-01',
                'membershipStatus' => 'active',
            ],
        ]);

        $this->assertResponseStatusCodeSame(201);
        $this->assertJsonLdResponse();

        $data = $response->toArray();
        $this->assertEquals('Test', $data['firstName']);
        $this->assertEquals('Testowy', $data['lastName']);
        $this->assertEquals($uniquePesel, $data['pesel']);
    }

    public function testCreateMemberValidationErrorMissingFields(): void
    {
        $this->authenticatedRequest('POST', '/api/members', [
            'headers' => ['Content-Type' => 'application/ld+json'],
            'json' => [
                'firstName' => 'Test',
                // missing required fields
            ],
        ]);

        $this->assertResponseStatusCodeSame(422);
    }

    public function testCreateMemberValidationErrorInvalidPesel(): void
    {
        $this->authenticatedRequest('POST', '/api/members', [
            'headers' => ['Content-Type' => 'application/ld+json'],
            'json' => [
                'firstName' => 'Test',
                'lastName' => 'Testowy',
                'pesel' => '123', // too short
                'birthDate' => '1990-05-15',
                'joinDate' => '2020-01-01',
                'membershipStatus' => 'active',
            ],
        ]);

        $this->assertResponseStatusCodeSame(422);
    }

    public function testCreateMemberDuplicatePesel(): void
    {
        $uniquePesel = '88' . str_pad((string) random_int(0, 999999999), 9, '0', STR_PAD_LEFT);

        // Create first member
        $this->authenticatedRequest('POST', '/api/members', [
            'headers' => ['Content-Type' => 'application/ld+json'],
            'json' => [
                'firstName' => 'First',
                'lastName' => 'Member',
                'pesel' => $uniquePesel,
                'birthDate' => '1990-05-15',
                'joinDate' => '2020-01-01',
                'membershipStatus' => 'active',
            ],
        ]);

        $this->assertResponseStatusCodeSame(201);

        // Try to create second member with same PESEL
        $this->authenticatedRequest('POST', '/api/members', [
            'headers' => ['Content-Type' => 'application/ld+json'],
            'json' => [
                'firstName' => 'Second',
                'lastName' => 'Member',
                'pesel' => $uniquePesel,
                'birthDate' => '1985-03-20',
                'joinDate' => '2021-01-01',
                'membershipStatus' => 'active',
            ],
        ]);

        $this->assertResponseStatusCodeSame(422);
    }

    public function testPatchMemberWithMergePatchJson(): void
    {
        // First create a member to patch
        $uniquePesel = '77' . str_pad((string) random_int(0, 999999999), 9, '0', STR_PAD_LEFT);

        $createResponse = $this->authenticatedRequest('POST', '/api/members', [
            'headers' => ['Content-Type' => 'application/ld+json'],
            'json' => [
                'firstName' => 'ToUpdate',
                'lastName' => 'Member',
                'pesel' => $uniquePesel,
                'birthDate' => '1990-05-15',
                'joinDate' => '2020-01-01',
                'membershipStatus' => 'active',
            ],
        ]);

        $this->assertResponseStatusCodeSame(201);
        $createdMember = $createResponse->toArray();
        $memberId = $createdMember['id'];

        // Now patch with correct Content-Type
        $response = $this->authenticatedRequest('PATCH', '/api/members/' . $memberId, [
            'headers' => ['Content-Type' => 'application/merge-patch+json'],
            'json' => [
                'firstName' => 'Updated',
                'phone' => '123456789',
            ],
        ]);

        $this->assertResponseIsSuccessful();
        $this->assertJsonLdResponse();

        $data = $response->toArray();
        $this->assertEquals('Updated', $data['firstName']);
        $this->assertEquals('123456789', $data['phone']);
    }

    public function testPatchMemberWithWrongContentType(): void
    {
        // First get a member ID
        $collectionResponse = $this->authenticatedRequest('GET', '/api/members');
        $collection = $collectionResponse->toArray();
        $memberId = $collection['member'][0]['id'];

        // Try to patch with wrong Content-Type (application/json instead of merge-patch+json)
        $this->authenticatedRequest('PATCH', '/api/members/' . $memberId, [
            'headers' => ['Content-Type' => 'application/json'],
            'json' => [
                'firstName' => 'ShouldFail',
            ],
        ]);

        $this->assertResponseStatusCodeSame(415);
    }

    public function testPatchMemberNotFound(): void
    {
        $this->authenticatedRequest('PATCH', '/api/members/999999', [
            'headers' => ['Content-Type' => 'application/merge-patch+json'],
            'json' => [
                'firstName' => 'Updated',
            ],
        ]);

        $this->assertResponseStatusCodeSame(404);
    }

    public function testDeleteMemberAsAdmin(): void
    {
        // First create a member to delete
        $uniquePesel = '66' . str_pad((string) random_int(0, 999999999), 9, '0', STR_PAD_LEFT);

        $createResponse = $this->authenticatedRequest('POST', '/api/members', [
            'headers' => ['Content-Type' => 'application/ld+json'],
            'json' => [
                'firstName' => 'ToDelete',
                'lastName' => 'Member',
                'pesel' => $uniquePesel,
                'birthDate' => '1990-05-15',
                'joinDate' => '2020-01-01',
                'membershipStatus' => 'active',
            ],
        ]);

        $this->assertResponseStatusCodeSame(201);
        $createdMember = $createResponse->toArray();
        $memberId = $createdMember['id'];

        // Delete as admin (admin@osp.plus is default)
        $this->authenticatedRequest('DELETE', '/api/members/' . $memberId);

        $this->assertResponseStatusCodeSame(204);

        // Verify it's deleted
        $this->authenticatedRequest('GET', '/api/members/' . $memberId);
        $this->assertResponseStatusCodeSame(404);
    }

    public function testDeleteMemberAsUserForbidden(): void
    {
        // First create a member to try to delete (as admin)
        $uniquePesel = '55' . str_pad((string) random_int(0, 999999999), 9, '0', STR_PAD_LEFT);

        $createResponse = $this->authenticatedRequest('POST', '/api/members', [
            'headers' => ['Content-Type' => 'application/ld+json'],
            'json' => [
                'firstName' => 'ToTryDelete',
                'lastName' => 'Member',
                'pesel' => $uniquePesel,
                'birthDate' => '1990-05-15',
                'joinDate' => '2020-01-01',
                'membershipStatus' => 'active',
            ],
        ]);

        $this->assertResponseStatusCodeSame(201);
        $createdMember = $createResponse->toArray();
        $memberId = $createdMember['id'];

        // Try to delete as regular user
        $userToken = $this->getToken('user@osp.plus', 'user123');

        static::createClient()->request('DELETE', '/api/members/' . $memberId, [
            'headers' => [
                'Authorization' => 'Bearer ' . $userToken,
            ],
        ]);

        $this->assertResponseStatusCodeSame(403);
    }

    public function testDeleteMemberNotFound(): void
    {
        $this->authenticatedRequest('DELETE', '/api/members/999999');

        $this->assertResponseStatusCodeSame(404);
    }

    public function testMemberHasFullNameProperty(): void
    {
        $collectionResponse = $this->authenticatedRequest('GET', '/api/members');
        $collection = $collectionResponse->toArray();
        $memberId = $collection['member'][0]['id'];

        $response = $this->authenticatedRequest('GET', '/api/members/' . $memberId);
        $data = $response->toArray();

        $this->assertArrayHasKey('fullName', $data);
        $this->assertNotEmpty($data['fullName']);
    }

    // ===== FILTER TESTS =====

    public function testFilterByLastName(): void
    {
        $response = $this->authenticatedRequest('GET', '/api/members?lastName=Kowalski');

        $this->assertResponseIsSuccessful();
        $data = $response->toArray();

        // All returned members should have "Kowalski" in lastName
        foreach ($data['member'] as $member) {
            $this->assertStringContainsStringIgnoringCase('Kowalski', $member['lastName']);
        }
    }

    public function testFilterByMembershipStatus(): void
    {
        $response = $this->authenticatedRequest('GET', '/api/members?membershipStatus=honorary');

        $this->assertResponseIsSuccessful();
        $data = $response->toArray();

        // All returned members should have status honorary
        foreach ($data['member'] as $member) {
            $this->assertEquals('honorary', $member['membershipStatus']);
        }
    }

    public function testFilterByMultipleParams(): void
    {
        $response = $this->authenticatedRequest('GET', '/api/members?membershipStatus=active');

        $this->assertResponseIsSuccessful();
        $data = $response->toArray();

        foreach ($data['member'] as $member) {
            $this->assertEquals('active', $member['membershipStatus']);
        }
    }

    public function testPaginationReturnsCorrectFields(): void
    {
        $response = $this->authenticatedRequest('GET', '/api/members?page=1&itemsPerPage=2');

        $this->assertResponseIsSuccessful();
        $data = $response->toArray();

        $this->assertArrayHasKey('totalItems', $data);
        $this->assertArrayHasKey('member', $data);
        $this->assertLessThanOrEqual(2, count($data['member']));
    }

    public function testPaginationSecondPage(): void
    {
        // First get total count
        $firstResponse = $this->authenticatedRequest('GET', '/api/members?itemsPerPage=2');
        $firstData = $firstResponse->toArray();

        if ($firstData['totalItems'] <= 2) {
            $this->markTestSkipped('Not enough members for pagination test');
        }

        // Get second page
        $secondResponse = $this->authenticatedRequest('GET', '/api/members?page=2&itemsPerPage=2');
        $secondData = $secondResponse->toArray();

        $this->assertResponseIsSuccessful();
        $this->assertNotEmpty($secondData['member']);

        // Members on page 2 should be different from page 1
        $firstIds = array_map(fn($m) => $m['id'], $firstData['member']);
        $secondIds = array_map(fn($m) => $m['id'], $secondData['member']);

        $this->assertEmpty(array_intersect($firstIds, $secondIds));
    }

    public function testOrderByLastName(): void
    {
        $response = $this->authenticatedRequest('GET', '/api/members?order[lastName]=asc');

        $this->assertResponseIsSuccessful();
        $data = $response->toArray();

        if (count($data['member']) > 1) {
            $lastNames = array_map(fn($m) => $m['lastName'], $data['member']);
            $sorted = $lastNames;
            sort($sorted, SORT_STRING | SORT_FLAG_CASE);
            $this->assertEquals($sorted, $lastNames);
        }
    }

    public function testFilterReturnsEmptyForNoMatch(): void
    {
        $response = $this->authenticatedRequest('GET', '/api/members?lastName=ZZZZNONEXISTENT');

        $this->assertResponseIsSuccessful();
        $data = $response->toArray();

        $this->assertEquals(0, $data['totalItems']);
        $this->assertEmpty($data['member']);
    }
}
