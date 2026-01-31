<?php

declare(strict_types=1);

namespace App\Tests;

use ApiPlatform\Symfony\Bundle\Test\ApiTestCase as BaseApiTestCase;
use ApiPlatform\Symfony\Bundle\Test\Client;
use Doctrine\ORM\Tools\SchemaTool;
use Symfony\Contracts\HttpClient\ResponseInterface;

abstract class ApiTestCase extends BaseApiTestCase
{
    protected static ?bool $alwaysBootKernel = true;

    private static ?string $adminToken = null;
    private static ?string $userToken = null;
    private static int $uniqueYearCounter = 0;

    protected function setUp(): void
    {
        parent::setUp();
    }

    /**
     * Get JWT token for given credentials (cached per test class)
     */
    protected function getToken(string $email = 'admin@osp.plus', string $password = 'admin123'): string
    {
        // Use cached tokens for common users to speed up tests
        if ($email === 'admin@osp.plus' && self::$adminToken !== null) {
            return self::$adminToken;
        }
        if ($email === 'user@osp.plus' && self::$userToken !== null) {
            return self::$userToken;
        }

        $client = static::createClient();
        $response = $client->request('POST', '/api/login_check', [
            'headers' => ['Content-Type' => 'application/json'],
            'json' => [
                'email' => $email,
                'password' => $password,
            ],
        ]);

        $this->assertResponseIsSuccessful();
        $data = $response->toArray();
        $this->assertArrayHasKey('token', $data);

        // Cache common tokens
        if ($email === 'admin@osp.plus') {
            self::$adminToken = $data['token'];
        } elseif ($email === 'user@osp.plus') {
            self::$userToken = $data['token'];
        }

        return $data['token'];
    }

    /**
     * Make authenticated request with JWT token
     */
    protected function authenticatedRequest(
        string $method,
        string $url,
        array $options = [],
        string $email = 'admin@osp.plus',
        string $password = 'admin123'
    ): ResponseInterface {
        $token = $this->getToken($email, $password);

        $options['headers'] = array_merge(
            $options['headers'] ?? [],
            ['Authorization' => 'Bearer ' . $token]
        );

        $client = static::createClient();
        return $client->request($method, $url, $options);
    }

    /**
     * Assert response is JSON-LD format
     */
    protected function assertJsonLdResponse(): void
    {
        $this->assertResponseHeaderSame('content-type', 'application/ld+json; charset=utf-8');
    }

    /**
     * Assert response contains JSON-LD collection
     */
    protected function assertJsonLdCollection(array $data): void
    {
        $this->assertArrayHasKey('@context', $data);
        $this->assertArrayHasKey('@id', $data);
        $this->assertArrayHasKey('@type', $data);
        $this->assertArrayHasKey('member', $data);
        $this->assertEquals('Collection', $data['@type']);
    }

    /**
     * Assert response contains JSON-LD item
     */
    protected function assertJsonLdItem(array $data): void
    {
        $this->assertArrayHasKey('@context', $data);
        $this->assertArrayHasKey('@id', $data);
        $this->assertArrayHasKey('@type', $data);
    }

    /**
     * Create test database schema
     */
    protected static function createSchema(): void
    {
        $kernel = static::bootKernel();
        $em = $kernel->getContainer()->get('doctrine')->getManager();

        $schemaTool = new SchemaTool($em);
        $metadata = $em->getMetadataFactory()->getAllMetadata();

        $schemaTool->dropSchema($metadata);
        $schemaTool->createSchema($metadata);
    }

    public static function tearDownAfterClass(): void
    {
        parent::tearDownAfterClass();
        // Reset tokens cache between test classes
        self::$adminToken = null;
        self::$userToken = null;
    }

    /**
     * Generate a unique year for testing (within valid 1900-2100 range).
     * Uses years 2030-2079 to avoid conflicts with fixture data (2023-2026).
     * Combines timestamp with counter to ensure uniqueness across test runs.
     */
    protected function getUniqueYear(): int
    {
        self::$uniqueYearCounter++;
        // Use microseconds + counter to generate unique year in 2030-2079 range
        $microPart = (int)(microtime(true) * 1000) % 50;
        return 2030 + (($microPart + self::$uniqueYearCounter) % 50);
    }
}
