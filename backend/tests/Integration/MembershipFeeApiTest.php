<?php

declare(strict_types=1);

namespace App\Tests\Integration;

use App\Tests\ApiTestCase;

class MembershipFeeApiTest extends ApiTestCase
{
    public function testGetFeesCollectionRequiresAuth(): void
    {
        static::createClient()->request('GET', '/api/membership_fees');

        $this->assertResponseStatusCodeSame(401);
    }

    public function testGetFeesCollection(): void
    {
        $response = $this->authenticatedRequest('GET', '/api/membership_fees');

        $this->assertResponseIsSuccessful();
        $this->assertJsonLdResponse();

        $data = $response->toArray();
        $this->assertJsonLdCollection($data);
    }

    public function testGetFeeItem(): void
    {
        // First get the collection to find a fee ID
        $collectionResponse = $this->authenticatedRequest('GET', '/api/membership_fees');
        $collection = $collectionResponse->toArray();

        if (empty($collection['member'])) {
            $this->markTestSkipped('No fees in database');
        }

        $firstFee = $collection['member'][0];
        $feeId = $firstFee['id'];

        $response = $this->authenticatedRequest('GET', '/api/membership_fees/' . $feeId);

        $this->assertResponseIsSuccessful();
        $this->assertJsonLdResponse();

        $data = $response->toArray();
        $this->assertJsonLdItem($data);
        $this->assertEquals($feeId, $data['id']);
        $this->assertArrayHasKey('year', $data);
        $this->assertArrayHasKey('amount', $data);
        $this->assertArrayHasKey('status', $data);
    }

    public function testGetFeeNotFound(): void
    {
        $this->authenticatedRequest('GET', '/api/membership_fees/999999');

        $this->assertResponseStatusCodeSame(404);
    }

    public function testCreateFeeSuccess(): void
    {
        // First get a member to link the fee to
        $membersResponse = $this->authenticatedRequest('GET', '/api/members');
        $members = $membersResponse->toArray();
        $memberId = $members['member'][0]['id'];

        // Use a year that likely doesn't exist for this member (within 1900-2100 validation range)
        $uniqueYear = 2050 + random_int(0, 49);

        $response = $this->authenticatedRequest('POST', '/api/membership_fees', [
            'headers' => ['Content-Type' => 'application/ld+json'],
            'json' => [
                'member' => '/api/members/' . $memberId,
                'year' => $uniqueYear,
                'amount' => '50.00',
                'status' => 'unpaid',
            ],
        ]);

        $this->assertResponseStatusCodeSame(201);
        $this->assertJsonLdResponse();

        $data = $response->toArray();
        $this->assertEquals($uniqueYear, $data['year']);
        $this->assertEquals('50.00', $data['amount']);
        $this->assertEquals('unpaid', $data['status']);
    }

    public function testCreateFeeValidationErrorMissingFields(): void
    {
        $this->authenticatedRequest('POST', '/api/membership_fees', [
            'headers' => ['Content-Type' => 'application/ld+json'],
            'json' => [
                'year' => 2024,
                // missing member and amount
            ],
        ]);

        $this->assertResponseStatusCodeSame(422);
    }

    public function testCreateFeeValidationErrorInvalidYear(): void
    {
        // First get a member
        $membersResponse = $this->authenticatedRequest('GET', '/api/members');
        $members = $membersResponse->toArray();
        $memberId = $members['member'][0]['id'];

        $this->authenticatedRequest('POST', '/api/membership_fees', [
            'headers' => ['Content-Type' => 'application/ld+json'],
            'json' => [
                'member' => '/api/members/' . $memberId,
                'year' => 1800, // invalid - out of range
                'amount' => '50.00',
                'status' => 'unpaid',
            ],
        ]);

        $this->assertResponseStatusCodeSame(422);
    }

    public function testCreateFeeDuplicateMemberYear(): void
    {
        // First get a member
        $membersResponse = $this->authenticatedRequest('GET', '/api/members');
        $members = $membersResponse->toArray();
        $memberId = $members['member'][0]['id'];

        $uniqueYear = 2060 + random_int(0, 9);

        // Create first fee
        $this->authenticatedRequest('POST', '/api/membership_fees', [
            'headers' => ['Content-Type' => 'application/ld+json'],
            'json' => [
                'member' => '/api/members/' . $memberId,
                'year' => $uniqueYear,
                'amount' => '50.00',
                'status' => 'unpaid',
            ],
        ]);

        $this->assertResponseStatusCodeSame(201);

        // Try to create second fee for same member and year
        $this->authenticatedRequest('POST', '/api/membership_fees', [
            'headers' => ['Content-Type' => 'application/ld+json'],
            'json' => [
                'member' => '/api/members/' . $memberId,
                'year' => $uniqueYear,
                'amount' => '60.00',
                'status' => 'unpaid',
            ],
        ]);

        // Should fail due to unique constraint
        $this->assertResponseStatusCodeSame(500); // Doctrine throws exception for unique constraint violation
    }

    public function testPatchFeeStatusToPaid(): void
    {
        // First get a member
        $membersResponse = $this->authenticatedRequest('GET', '/api/members');
        $members = $membersResponse->toArray();
        $memberId = $members['member'][0]['id'];

        // Create a fee to patch
        $uniqueYear = 2070 + random_int(0, 9);

        $createResponse = $this->authenticatedRequest('POST', '/api/membership_fees', [
            'headers' => ['Content-Type' => 'application/ld+json'],
            'json' => [
                'member' => '/api/members/' . $memberId,
                'year' => $uniqueYear,
                'amount' => '50.00',
                'status' => 'unpaid',
            ],
        ]);

        $this->assertResponseStatusCodeSame(201);
        $createdFee = $createResponse->toArray();
        $feeId = $createdFee['id'];

        // Patch status to paid
        $response = $this->authenticatedRequest('PATCH', '/api/membership_fees/' . $feeId, [
            'headers' => ['Content-Type' => 'application/merge-patch+json'],
            'json' => [
                'status' => 'paid',
                'paidAt' => '2024-03-15',
            ],
        ]);

        $this->assertResponseIsSuccessful();

        $data = $response->toArray();
        $this->assertEquals('paid', $data['status']);
        $this->assertNotNull($data['paidAt']);
    }

    public function testPatchFeeWithWrongContentType(): void
    {
        // Get a fee
        $feesResponse = $this->authenticatedRequest('GET', '/api/membership_fees');
        $fees = $feesResponse->toArray();

        if (empty($fees['member'])) {
            $this->markTestSkipped('No fees in database');
        }

        $feeId = $fees['member'][0]['id'];

        // Try to patch with wrong Content-Type
        $this->authenticatedRequest('PATCH', '/api/membership_fees/' . $feeId, [
            'headers' => ['Content-Type' => 'application/json'],
            'json' => [
                'status' => 'paid',
            ],
        ]);

        $this->assertResponseStatusCodeSame(415);
    }

    public function testPatchFeeNotFound(): void
    {
        $this->authenticatedRequest('PATCH', '/api/membership_fees/999999', [
            'headers' => ['Content-Type' => 'application/merge-patch+json'],
            'json' => [
                'status' => 'paid',
            ],
        ]);

        $this->assertResponseStatusCodeSame(404);
    }

    public function testFeeHasMemberRelation(): void
    {
        $feesResponse = $this->authenticatedRequest('GET', '/api/membership_fees');
        $fees = $feesResponse->toArray();

        if (empty($fees['member'])) {
            $this->markTestSkipped('No fees in database');
        }

        $feeId = $fees['member'][0]['id'];

        $response = $this->authenticatedRequest('GET', '/api/membership_fees/' . $feeId);
        $data = $response->toArray();

        $this->assertArrayHasKey('member', $data);
        // Member should be an IRI string
        $this->assertStringContainsString('/api/members/', $data['member']);
    }

    // ===== FILTER TESTS =====

    public function testFilterByYear(): void
    {
        $response = $this->authenticatedRequest('GET', '/api/membership_fees?year=2024');

        $this->assertResponseIsSuccessful();
        $data = $response->toArray();

        foreach ($data['member'] as $fee) {
            $this->assertEquals(2024, $fee['year']);
        }
    }

    public function testFilterByStatus(): void
    {
        $response = $this->authenticatedRequest('GET', '/api/membership_fees?status=paid');

        $this->assertResponseIsSuccessful();
        $data = $response->toArray();

        foreach ($data['member'] as $fee) {
            $this->assertEquals('paid', $fee['status']);
        }
    }

    public function testFilterByYearAndStatus(): void
    {
        $response = $this->authenticatedRequest('GET', '/api/membership_fees?year=2025&status=unpaid');

        $this->assertResponseIsSuccessful();
        $data = $response->toArray();

        foreach ($data['member'] as $fee) {
            $this->assertEquals(2025, $fee['year']);
            $this->assertEquals('unpaid', $fee['status']);
        }
    }

    public function testFeePaginationReturnsCorrectFields(): void
    {
        $response = $this->authenticatedRequest('GET', '/api/membership_fees?page=1&itemsPerPage=5');

        $this->assertResponseIsSuccessful();
        $data = $response->toArray();

        $this->assertArrayHasKey('totalItems', $data);
        $this->assertArrayHasKey('member', $data);
        $this->assertLessThanOrEqual(5, count($data['member']));
    }

    public function testFeeOrderByYear(): void
    {
        $response = $this->authenticatedRequest('GET', '/api/membership_fees?order[year]=desc');

        $this->assertResponseIsSuccessful();
        $data = $response->toArray();

        if (count($data['member']) > 1) {
            $years = array_map(fn($f) => $f['year'], $data['member']);
            $sorted = $years;
            rsort($sorted);
            $this->assertEquals($sorted, $years);
        }
    }

    public function testFilterReturnsEmptyForNonexistentYear(): void
    {
        $response = $this->authenticatedRequest('GET', '/api/membership_fees?year=1900');

        $this->assertResponseIsSuccessful();
        $data = $response->toArray();

        $this->assertEquals(0, $data['totalItems']);
        $this->assertEmpty($data['member']);
    }
}
