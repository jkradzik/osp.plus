<?php

declare(strict_types=1);

namespace App\Tests\Integration;

use App\Tests\ApiTestCase;

class PersonalEquipmentApiTest extends ApiTestCase
{
    public function testGetEquipmentCollectionRequiresAuth(): void
    {
        static::createClient()->request('GET', '/api/personal_equipments');

        $this->assertResponseStatusCodeSame(401);
    }

    public function testGetEquipmentCollection(): void
    {
        $response = $this->authenticatedRequest('GET', '/api/personal_equipments');

        $this->assertResponseIsSuccessful();
        $this->assertJsonLdResponse();

        $data = $response->toArray();
        $this->assertJsonLdCollection($data);
    }

    public function testGetEquipmentItem(): void
    {
        $collectionResponse = $this->authenticatedRequest('GET', '/api/personal_equipments');
        $collection = $collectionResponse->toArray();

        if (empty($collection['member'])) {
            $this->markTestSkipped('No equipment in database');
        }

        $firstEquipment = $collection['member'][0];
        $equipmentId = $firstEquipment['id'];

        $response = $this->authenticatedRequest('GET', '/api/personal_equipments/' . $equipmentId);

        $this->assertResponseIsSuccessful();
        $this->assertJsonLdResponse();

        $data = $response->toArray();
        $this->assertJsonLdItem($data);
        $this->assertEquals($equipmentId, $data['id']);
        $this->assertArrayHasKey('issuedAt', $data);
        $this->assertArrayHasKey('member', $data);
        $this->assertArrayHasKey('type', $data);
    }

    public function testGetEquipmentNotFound(): void
    {
        $this->authenticatedRequest('GET', '/api/personal_equipments/999999');

        $this->assertResponseStatusCodeSame(404);
    }

    public function testCreateEquipmentSuccess(): void
    {
        // Get a member
        $membersResponse = $this->authenticatedRequest('GET', '/api/members');
        $members = $membersResponse->toArray();
        $memberId = $members['member'][0]['id'];

        // Get an equipment type
        $typesResponse = $this->authenticatedRequest('GET', '/api/equipment_dictionaries');
        $types = $typesResponse->toArray();
        $typeId = $types['member'][0]['id'];

        $response = $this->authenticatedRequest('POST', '/api/personal_equipments', [
            'headers' => ['Content-Type' => 'application/ld+json'],
            'json' => [
                'member' => '/api/members/' . $memberId,
                'type' => '/api/equipment_dictionaries/' . $typeId,
                'issuedAt' => '2024-01-15',
                'size' => 'XL',
                'serialNumber' => 'TEST-2024-001',
            ],
        ]);

        $this->assertResponseStatusCodeSame(201);
        $this->assertJsonLdResponse();

        $data = $response->toArray();
        $this->assertEquals('2024-01-15', substr($data['issuedAt'], 0, 10));
        $this->assertEquals('XL', $data['size']);
        $this->assertEquals('TEST-2024-001', $data['serialNumber']);
    }

    public function testCreateEquipmentValidationErrorMissingFields(): void
    {
        $this->authenticatedRequest('POST', '/api/personal_equipments', [
            'headers' => ['Content-Type' => 'application/ld+json'],
            'json' => [
                'issuedAt' => '2024-01-15',
                // missing member and type
            ],
        ]);

        $this->assertResponseStatusCodeSame(422);
    }

    public function testPatchEquipment(): void
    {
        // Get equipment
        $collectionResponse = $this->authenticatedRequest('GET', '/api/personal_equipments');
        $collection = $collectionResponse->toArray();

        if (empty($collection['member'])) {
            $this->markTestSkipped('No equipment in database');
        }

        $equipmentId = $collection['member'][0]['id'];

        $response = $this->authenticatedRequest('PATCH', '/api/personal_equipments/' . $equipmentId, [
            'headers' => ['Content-Type' => 'application/merge-patch+json'],
            'json' => [
                'notes' => 'Updated equipment notes',
            ],
        ]);

        $this->assertResponseIsSuccessful();

        $data = $response->toArray();
        $this->assertEquals('Updated equipment notes', $data['notes']);
    }

    public function testEquipmentHasMemberRelation(): void
    {
        $collectionResponse = $this->authenticatedRequest('GET', '/api/personal_equipments');
        $collection = $collectionResponse->toArray();

        if (empty($collection['member'])) {
            $this->markTestSkipped('No equipment in database');
        }

        $equipmentId = $collection['member'][0]['id'];

        $response = $this->authenticatedRequest('GET', '/api/personal_equipments/' . $equipmentId);
        $data = $response->toArray();

        $this->assertArrayHasKey('member', $data);
        $this->assertStringContainsString('/api/members/', $data['member']);
    }

    public function testFilterByMember(): void
    {
        // Get a member
        $membersResponse = $this->authenticatedRequest('GET', '/api/members');
        $members = $membersResponse->toArray();
        $memberId = $members['member'][0]['id'];

        $response = $this->authenticatedRequest('GET', '/api/personal_equipments?member=' . $memberId);

        $this->assertResponseIsSuccessful();
        $data = $response->toArray();

        foreach ($data['member'] as $equipment) {
            $this->assertStringContainsString('/api/members/' . $memberId, $equipment['member']);
        }
    }

    public function testFilterBySize(): void
    {
        $response = $this->authenticatedRequest('GET', '/api/personal_equipments?size=L');

        $this->assertResponseIsSuccessful();
        $data = $response->toArray();

        foreach ($data['member'] as $equipment) {
            $this->assertStringContainsString('L', $equipment['size']);
        }
    }

    public function testGetEquipmentDictionaries(): void
    {
        $response = $this->authenticatedRequest('GET', '/api/equipment_dictionaries');

        $this->assertResponseIsSuccessful();
        $data = $response->toArray();

        $this->assertJsonLdCollection($data);
        $this->assertGreaterThan(0, count($data['member']));

        $firstType = $data['member'][0];
        $this->assertArrayHasKey('name', $firstType);
        $this->assertArrayHasKey('category', $firstType);
        $this->assertArrayHasKey('hasSizes', $firstType);
    }
}
