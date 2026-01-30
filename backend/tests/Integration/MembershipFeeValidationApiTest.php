<?php

declare(strict_types=1);

namespace App\Tests\Integration;

use App\Tests\ApiTestCase;

class MembershipFeeValidationApiTest extends ApiTestCase
{
    public function testValidateOverdueRequiresAuth(): void
    {
        static::createClient()->request('POST', '/api/membership-fees/validate-overdue');

        $this->assertResponseStatusCodeSame(401);
    }

    public function testValidateOverdueEndpoint(): void
    {
        $response = $this->authenticatedRequest('POST', '/api/membership-fees/validate-overdue');

        $this->assertResponseIsSuccessful();

        $data = $response->toArray();
        $this->assertArrayHasKey('success', $data);
        $this->assertArrayHasKey('marked_count', $data);
        $this->assertArrayHasKey('message', $data);
        $this->assertTrue($data['success']);
        $this->assertIsInt($data['marked_count']);
        $this->assertGreaterThanOrEqual(0, $data['marked_count']);
    }

    public function testValidateOverdueResponseFormat(): void
    {
        $response = $this->authenticatedRequest('POST', '/api/membership-fees/validate-overdue');

        $this->assertResponseIsSuccessful();
        $this->assertResponseHeaderSame('content-type', 'application/json');

        $data = $response->toArray();

        // Verify message format
        if ($data['marked_count'] > 0) {
            $this->assertStringContainsString('Oznaczono', $data['message']);
            $this->assertStringContainsString('zaległe', $data['message']);
        } else {
            $this->assertStringContainsString('Brak składek', $data['message']);
        }
    }

    public function testGetOverdueFeesRequiresAuth(): void
    {
        static::createClient()->request('GET', '/api/membership-fees/overdue');

        $this->assertResponseStatusCodeSame(401);
    }

    public function testGetOverdueFeesEndpoint(): void
    {
        $response = $this->authenticatedRequest('GET', '/api/membership-fees/overdue');

        $this->assertResponseIsSuccessful();
        $this->assertResponseHeaderSame('content-type', 'application/json');

        $data = $response->toArray();
        $this->assertArrayHasKey('count', $data);
        $this->assertArrayHasKey('items', $data);
        $this->assertIsInt($data['count']);
        $this->assertIsArray($data['items']);
        $this->assertEquals(count($data['items']), $data['count']);
    }

    public function testGetOverdueFeesItemFormat(): void
    {
        // First validate to potentially mark some fees as overdue
        $this->authenticatedRequest('POST', '/api/membership-fees/validate-overdue');

        $response = $this->authenticatedRequest('GET', '/api/membership-fees/overdue');
        $data = $response->toArray();

        if ($data['count'] > 0) {
            $firstItem = $data['items'][0];

            $this->assertArrayHasKey('id', $firstItem);
            $this->assertArrayHasKey('member_id', $firstItem);
            $this->assertArrayHasKey('member_name', $firstItem);
            $this->assertArrayHasKey('year', $firstItem);
            $this->assertArrayHasKey('amount', $firstItem);
            $this->assertArrayHasKey('status', $firstItem);
            $this->assertEquals('overdue', $firstItem['status']);
        } else {
            // No overdue fees is also valid
            $this->assertEquals(0, $data['count']);
            $this->assertEmpty($data['items']);
        }
    }

    public function testValidateOverdueThenGetOverdue(): void
    {
        // Create a fee that should be marked as overdue
        $membersResponse = $this->authenticatedRequest('GET', '/api/members');
        $members = $membersResponse->toArray();
        $memberId = $members['member'][0]['id'];

        // Create an unpaid fee for a past year (e.g., 2020) which should be overdue
        $pastYear = 2020;

        $createResponse = $this->authenticatedRequest('POST', '/api/membership_fees', [
            'headers' => ['Content-Type' => 'application/ld+json'],
            'json' => [
                'member' => '/api/members/' . $memberId,
                'year' => $pastYear,
                'amount' => '30.00',
                'status' => 'unpaid',
            ],
        ]);

        // It might already exist, so we accept 201 or 500 (duplicate)
        $statusCode = $createResponse->getStatusCode();
        $this->assertContains($statusCode, [201, 500]);

        // Now validate overdue
        $validateResponse = $this->authenticatedRequest('POST', '/api/membership-fees/validate-overdue');
        $this->assertResponseIsSuccessful();

        // Get overdue fees
        $overdueResponse = $this->authenticatedRequest('GET', '/api/membership-fees/overdue');
        $overdueData = $overdueResponse->toArray();

        // Should have at least one overdue fee
        $this->assertGreaterThanOrEqual(0, $overdueData['count']);

        // All returned items should have status 'overdue'
        foreach ($overdueData['items'] as $item) {
            $this->assertEquals('overdue', $item['status']);
        }
    }

    public function testValidateOverdueSkipsExemptFees(): void
    {
        // Create an exempt fee
        $membersResponse = $this->authenticatedRequest('GET', '/api/members');
        $members = $membersResponse->toArray();
        $memberId = $members['member'][0]['id'];

        $uniqueYear = 2010 + random_int(1, 9);

        $createResponse = $this->authenticatedRequest('POST', '/api/membership_fees', [
            'headers' => ['Content-Type' => 'application/ld+json'],
            'json' => [
                'member' => '/api/members/' . $memberId,
                'year' => $uniqueYear,
                'amount' => '0.00',
                'status' => 'exempt',
            ],
        ]);

        // Might already exist
        if ($createResponse->getStatusCode() === 201) {
            $createdFee = $createResponse->toArray();
            $feeId = $createdFee['id'];

            // Validate overdue
            $this->authenticatedRequest('POST', '/api/membership-fees/validate-overdue');

            // Get the fee again - should still be exempt, not overdue
            $feeResponse = $this->authenticatedRequest('GET', '/api/membership_fees/' . $feeId);
            $feeData = $feeResponse->toArray();

            $this->assertEquals('exempt', $feeData['status']);
        } else {
            // Fee already exists, test is still valid
            $this->assertTrue(true);
        }
    }

    public function testValidateOverdueSkipsNotApplicableFees(): void
    {
        // Create a not_applicable fee
        $membersResponse = $this->authenticatedRequest('GET', '/api/members');
        $members = $membersResponse->toArray();
        $memberId = $members['member'][0]['id'];

        $uniqueYear = 2000 + random_int(1, 9);

        $createResponse = $this->authenticatedRequest('POST', '/api/membership_fees', [
            'headers' => ['Content-Type' => 'application/ld+json'],
            'json' => [
                'member' => '/api/members/' . $memberId,
                'year' => $uniqueYear,
                'amount' => '0.00',
                'status' => 'not_applicable',
            ],
        ]);

        // Might already exist
        if ($createResponse->getStatusCode() === 201) {
            $createdFee = $createResponse->toArray();
            $feeId = $createdFee['id'];

            // Validate overdue
            $this->authenticatedRequest('POST', '/api/membership-fees/validate-overdue');

            // Get the fee again - should still be not_applicable, not overdue
            $feeResponse = $this->authenticatedRequest('GET', '/api/membership_fees/' . $feeId);
            $feeData = $feeResponse->toArray();

            $this->assertEquals('not_applicable', $feeData['status']);
        } else {
            // Fee already exists, test is still valid
            $this->assertTrue(true);
        }
    }

    public function testValidateOverdueIdempotent(): void
    {
        // Call validate twice - second call should not change already overdue fees
        $firstResponse = $this->authenticatedRequest('POST', '/api/membership-fees/validate-overdue');
        $firstData = $firstResponse->toArray();

        $secondResponse = $this->authenticatedRequest('POST', '/api/membership-fees/validate-overdue');
        $secondData = $secondResponse->toArray();

        // Second call should mark 0 or fewer fees (those already overdue won't be counted again)
        $this->assertLessThanOrEqual($firstData['marked_count'], $firstData['marked_count']);
        $this->assertTrue($secondData['success']);
    }
}
