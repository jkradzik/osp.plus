<?php

declare(strict_types=1);

namespace App\Tests\Integration;

use App\Tests\ApiTestCase;

class FinancialApiTest extends ApiTestCase
{
    public function testGetRecordsCollectionRequiresAuth(): void
    {
        static::createClient()->request('GET', '/api/financial_records');

        $this->assertResponseStatusCodeSame(401);
    }

    public function testGetRecordsCollection(): void
    {
        $response = $this->authenticatedRequest('GET', '/api/financial_records');

        $this->assertResponseIsSuccessful();
        $this->assertJsonLdResponse();

        $data = $response->toArray();
        $this->assertJsonLdCollection($data);
    }

    public function testGetRecordItem(): void
    {
        $collectionResponse = $this->authenticatedRequest('GET', '/api/financial_records');
        $collection = $collectionResponse->toArray();

        if (empty($collection['member'])) {
            $this->markTestSkipped('No financial records in database');
        }

        $firstRecord = $collection['member'][0];
        $recordId = $firstRecord['id'];

        $response = $this->authenticatedRequest('GET', '/api/financial_records/' . $recordId);

        $this->assertResponseIsSuccessful();
        $this->assertJsonLdResponse();

        $data = $response->toArray();
        $this->assertJsonLdItem($data);
        $this->assertEquals($recordId, $data['id']);
        $this->assertArrayHasKey('type', $data);
        $this->assertArrayHasKey('amount', $data);
        $this->assertArrayHasKey('description', $data);
        $this->assertArrayHasKey('recordedAt', $data);
    }

    public function testGetRecordNotFound(): void
    {
        $this->authenticatedRequest('GET', '/api/financial_records/999999');

        $this->assertResponseStatusCodeSame(404);
    }

    public function testCreateRecordSuccess(): void
    {
        // Get a category
        $categoriesResponse = $this->authenticatedRequest('GET', '/api/financial_categories?type=expense');
        $categories = $categoriesResponse->toArray();
        $categoryId = $categories['member'][0]['id'];

        $response = $this->authenticatedRequest('POST', '/api/financial_records', [
            'headers' => ['Content-Type' => 'application/ld+json'],
            'json' => [
                'type' => 'expense',
                'category' => '/api/financial_categories/' . $categoryId,
                'amount' => '150.50',
                'description' => 'Test expense record',
                'documentNumber' => 'TEST/2024/001',
                'recordedAt' => '2024-06-15',
            ],
        ]);

        $this->assertResponseStatusCodeSame(201);
        $this->assertJsonLdResponse();

        $data = $response->toArray();
        $this->assertEquals('expense', $data['type']);
        $this->assertEquals('150.50', $data['amount']);
        $this->assertEquals('Test expense record', $data['description']);
    }

    public function testCreateRecordValidationErrorMissingFields(): void
    {
        $this->authenticatedRequest('POST', '/api/financial_records', [
            'headers' => ['Content-Type' => 'application/ld+json'],
            'json' => [
                'type' => 'expense',
                // missing category, amount, description, recordedAt
            ],
        ]);

        $this->assertResponseStatusCodeSame(422);
    }

    public function testPatchRecord(): void
    {
        $collectionResponse = $this->authenticatedRequest('GET', '/api/financial_records');
        $collection = $collectionResponse->toArray();

        if (empty($collection['member'])) {
            $this->markTestSkipped('No financial records in database');
        }

        $recordId = $collection['member'][0]['id'];

        $response = $this->authenticatedRequest('PATCH', '/api/financial_records/' . $recordId, [
            'headers' => ['Content-Type' => 'application/merge-patch+json'],
            'json' => [
                'description' => 'Updated description for test',
            ],
        ]);

        $this->assertResponseIsSuccessful();

        $data = $response->toArray();
        $this->assertEquals('Updated description for test', $data['description']);
    }

    public function testFilterByType(): void
    {
        $response = $this->authenticatedRequest('GET', '/api/financial_records?type=income');

        $this->assertResponseIsSuccessful();
        $data = $response->toArray();

        foreach ($data['member'] as $record) {
            $this->assertEquals('income', $record['type']);
        }
    }

    public function testFilterByCategory(): void
    {
        // Get a category first
        $categoriesResponse = $this->authenticatedRequest('GET', '/api/financial_categories');
        $categories = $categoriesResponse->toArray();

        if (empty($categories['member'])) {
            $this->markTestSkipped('No categories in database');
        }

        $categoryId = $categories['member'][0]['id'];

        $response = $this->authenticatedRequest('GET', '/api/financial_records?category=' . $categoryId);

        $this->assertResponseIsSuccessful();
        $data = $response->toArray();

        foreach ($data['member'] as $record) {
            $this->assertStringContainsString('/api/financial_categories/' . $categoryId, $record['category']);
        }
    }

    public function testGetCategories(): void
    {
        $response = $this->authenticatedRequest('GET', '/api/financial_categories');

        $this->assertResponseIsSuccessful();
        $data = $response->toArray();

        $this->assertJsonLdCollection($data);
        $this->assertGreaterThan(0, count($data['member']));

        $firstCategory = $data['member'][0];
        $this->assertArrayHasKey('name', $firstCategory);
        $this->assertArrayHasKey('type', $firstCategory);
    }

    public function testGetCategoriesByType(): void
    {
        $response = $this->authenticatedRequest('GET', '/api/financial_categories?type=income');

        $this->assertResponseIsSuccessful();
        $data = $response->toArray();

        foreach ($data['member'] as $category) {
            $this->assertEquals('income', $category['type']);
        }
    }

    public function testFinancialSummary(): void
    {
        $response = $this->authenticatedRequest('GET', '/api/financial-summary');

        $this->assertResponseIsSuccessful();

        $data = $response->toArray();
        $this->assertArrayHasKey('totalIncome', $data);
        $this->assertArrayHasKey('totalExpense', $data);
        $this->assertArrayHasKey('balance', $data);
        $this->assertArrayHasKey('byCategory', $data);
    }

    public function testFinancialSummaryWithYear(): void
    {
        $response = $this->authenticatedRequest('GET', '/api/financial-summary?year=2025');

        $this->assertResponseIsSuccessful();

        $data = $response->toArray();
        $this->assertEquals(2025, $data['year']);
        $this->assertArrayHasKey('totalIncome', $data);
        $this->assertArrayHasKey('totalExpense', $data);
        $this->assertArrayHasKey('balance', $data);
    }

    public function testFinancialSummaryWithYearAndMonth(): void
    {
        $response = $this->authenticatedRequest('GET', '/api/financial-summary?year=2025&month=1');

        $this->assertResponseIsSuccessful();

        $data = $response->toArray();
        $this->assertEquals(2025, $data['year']);
        $this->assertEquals(1, $data['month']);
    }

    public function testRecordPaginationReturnsCorrectFields(): void
    {
        $response = $this->authenticatedRequest('GET', '/api/financial_records?page=1&itemsPerPage=5');

        $this->assertResponseIsSuccessful();
        $data = $response->toArray();

        $this->assertArrayHasKey('totalItems', $data);
        $this->assertArrayHasKey('member', $data);
        $this->assertLessThanOrEqual(5, count($data['member']));
    }
}
