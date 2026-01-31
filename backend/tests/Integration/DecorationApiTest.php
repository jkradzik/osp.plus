<?php

declare(strict_types=1);

namespace App\Tests\Integration;

use App\Tests\ApiTestCase;

class DecorationApiTest extends ApiTestCase
{
    public function testGetDecorationsCollectionRequiresAuth(): void
    {
        static::createClient()->request('GET', '/api/decorations');

        $this->assertResponseStatusCodeSame(401);
    }

    public function testGetDecorationsCollection(): void
    {
        $response = $this->authenticatedRequest('GET', '/api/decorations');

        $this->assertResponseIsSuccessful();
        $this->assertJsonLdResponse();

        $data = $response->toArray();
        $this->assertJsonLdCollection($data);
    }

    public function testGetDecorationItem(): void
    {
        $collectionResponse = $this->authenticatedRequest('GET', '/api/decorations');
        $collection = $collectionResponse->toArray();

        if (empty($collection['member'])) {
            $this->markTestSkipped('No decorations in database');
        }

        $firstDecoration = $collection['member'][0];
        $decorationId = $firstDecoration['id'];

        $response = $this->authenticatedRequest('GET', '/api/decorations/' . $decorationId);

        $this->assertResponseIsSuccessful();
        $this->assertJsonLdResponse();

        $data = $response->toArray();
        $this->assertJsonLdItem($data);
        $this->assertEquals($decorationId, $data['id']);
        $this->assertArrayHasKey('awardedAt', $data);
        $this->assertArrayHasKey('member', $data);
        $this->assertArrayHasKey('type', $data);
    }

    public function testGetDecorationNotFound(): void
    {
        $this->authenticatedRequest('GET', '/api/decorations/999999');

        $this->assertResponseStatusCodeSame(404);
    }

    public function testCreateDecorationSuccess(): void
    {
        // Get a member
        $membersResponse = $this->authenticatedRequest('GET', '/api/members');
        $members = $membersResponse->toArray();
        $memberId = $members['member'][0]['id'];

        // Get a decoration type
        $typesResponse = $this->authenticatedRequest('GET', '/api/decoration_dictionaries');
        $types = $typesResponse->toArray();
        $typeId = $types['member'][0]['id'];

        $response = $this->authenticatedRequest('POST', '/api/decorations', [
            'headers' => ['Content-Type' => 'application/ld+json'],
            'json' => [
                'member' => '/api/members/' . $memberId,
                'type' => '/api/decoration_dictionaries/' . $typeId,
                'awardedAt' => '2024-05-04',
                'awardedBy' => 'Test Organization',
                'certificateNumber' => 'TEST/2024/001',
            ],
        ]);

        $this->assertResponseStatusCodeSame(201);
        $this->assertJsonLdResponse();

        $data = $response->toArray();
        $this->assertEquals('2024-05-04', substr($data['awardedAt'], 0, 10));
        $this->assertEquals('Test Organization', $data['awardedBy']);
    }

    public function testCreateDecorationValidationErrorMissingFields(): void
    {
        $this->authenticatedRequest('POST', '/api/decorations', [
            'headers' => ['Content-Type' => 'application/ld+json'],
            'json' => [
                'awardedAt' => '2024-05-04',
                // missing member and type
            ],
        ]);

        $this->assertResponseStatusCodeSame(422);
    }

    public function testPatchDecoration(): void
    {
        // Get a decoration
        $collectionResponse = $this->authenticatedRequest('GET', '/api/decorations');
        $collection = $collectionResponse->toArray();

        if (empty($collection['member'])) {
            $this->markTestSkipped('No decorations in database');
        }

        $decorationId = $collection['member'][0]['id'];

        $response = $this->authenticatedRequest('PATCH', '/api/decorations/' . $decorationId, [
            'headers' => ['Content-Type' => 'application/merge-patch+json'],
            'json' => [
                'notes' => 'Updated notes for test',
            ],
        ]);

        $this->assertResponseIsSuccessful();

        $data = $response->toArray();
        $this->assertEquals('Updated notes for test', $data['notes']);
    }

    public function testDecorationHasMemberRelation(): void
    {
        $collectionResponse = $this->authenticatedRequest('GET', '/api/decorations');
        $collection = $collectionResponse->toArray();

        if (empty($collection['member'])) {
            $this->markTestSkipped('No decorations in database');
        }

        $decorationId = $collection['member'][0]['id'];

        $response = $this->authenticatedRequest('GET', '/api/decorations/' . $decorationId);
        $data = $response->toArray();

        $this->assertArrayHasKey('member', $data);
        $this->assertStringContainsString('/api/members/', $data['member']);
    }

    public function testFilterByMember(): void
    {
        // Get a member that has decorations
        $membersResponse = $this->authenticatedRequest('GET', '/api/members');
        $members = $membersResponse->toArray();
        $memberId = $members['member'][0]['id'];

        $response = $this->authenticatedRequest('GET', '/api/decorations?member=' . $memberId);

        $this->assertResponseIsSuccessful();
        $data = $response->toArray();

        foreach ($data['member'] as $decoration) {
            $this->assertStringContainsString('/api/members/' . $memberId, $decoration['member']);
        }
    }

    public function testGetDecorationDictionaries(): void
    {
        $response = $this->authenticatedRequest('GET', '/api/decoration_dictionaries');

        $this->assertResponseIsSuccessful();
        $data = $response->toArray();

        $this->assertJsonLdCollection($data);
        $this->assertGreaterThan(0, count($data['member']));

        $firstType = $data['member'][0];
        $this->assertArrayHasKey('name', $firstType);
        $this->assertArrayHasKey('category', $firstType);
    }
}
