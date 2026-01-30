<?php

declare(strict_types=1);

namespace App\Tests\Integration;

use App\Tests\ApiTestCase;

class AuthenticationTest extends ApiTestCase
{
    public function testLoginSuccess(): void
    {
        $response = static::createClient()->request('POST', '/api/login_check', [
            'headers' => ['Content-Type' => 'application/json'],
            'json' => [
                'email' => 'admin@osp.plus',
                'password' => 'admin123',
            ],
        ]);

        $this->assertResponseIsSuccessful();
        $this->assertResponseStatusCodeSame(200);

        $data = $response->toArray();
        $this->assertArrayHasKey('token', $data);
        $this->assertNotEmpty($data['token']);
    }

    public function testLoginWithUserRole(): void
    {
        $response = static::createClient()->request('POST', '/api/login_check', [
            'headers' => ['Content-Type' => 'application/json'],
            'json' => [
                'email' => 'user@osp.plus',
                'password' => 'user123',
            ],
        ]);

        $this->assertResponseIsSuccessful();
        $data = $response->toArray();
        $this->assertArrayHasKey('token', $data);
    }

    public function testLoginInvalidPassword(): void
    {
        static::createClient()->request('POST', '/api/login_check', [
            'headers' => ['Content-Type' => 'application/json'],
            'json' => [
                'email' => 'admin@osp.plus',
                'password' => 'wrong_password',
            ],
        ]);

        $this->assertResponseStatusCodeSame(401);
    }

    public function testLoginInvalidEmail(): void
    {
        static::createClient()->request('POST', '/api/login_check', [
            'headers' => ['Content-Type' => 'application/json'],
            'json' => [
                'email' => 'nonexistent@osp.plus',
                'password' => 'admin123',
            ],
        ]);

        $this->assertResponseStatusCodeSame(401);
    }

    public function testLoginMissingEmail(): void
    {
        static::createClient()->request('POST', '/api/login_check', [
            'headers' => ['Content-Type' => 'application/json'],
            'json' => [
                'password' => 'admin123',
            ],
        ]);

        $this->assertResponseStatusCodeSame(400);
    }

    public function testLoginMissingPassword(): void
    {
        static::createClient()->request('POST', '/api/login_check', [
            'headers' => ['Content-Type' => 'application/json'],
            'json' => [
                'email' => 'admin@osp.plus',
            ],
        ]);

        $this->assertResponseStatusCodeSame(400);
    }

    public function testApiRequiresAuthenticationMembers(): void
    {
        static::createClient()->request('GET', '/api/members');

        $this->assertResponseStatusCodeSame(401);
    }

    public function testApiRequiresAuthenticationFees(): void
    {
        static::createClient()->request('GET', '/api/membership_fees');

        $this->assertResponseStatusCodeSame(401);
    }

    public function testApiDocsIsPublic(): void
    {
        $response = static::createClient()->request('GET', '/api/docs', [
            'headers' => ['Accept' => 'application/ld+json'],
        ]);

        $this->assertResponseIsSuccessful();
    }

    public function testAuthenticatedAccessToMembers(): void
    {
        $token = $this->getToken();

        static::createClient()->request('GET', '/api/members', [
            'headers' => [
                'Authorization' => 'Bearer ' . $token,
            ],
        ]);

        $this->assertResponseIsSuccessful();
    }

    public function testInvalidTokenRejected(): void
    {
        static::createClient()->request('GET', '/api/members', [
            'headers' => [
                'Authorization' => 'Bearer invalid_token_here',
            ],
        ]);

        $this->assertResponseStatusCodeSame(401);
    }
}
