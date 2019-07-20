<?php

namespace App\Tests;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Request;
use App\DataFixtures\BillingUserFixtures;
use App\Tests\AbstractTest;

class BillingUserControllerTest extends AbstractTest
{
    public function getFixtures(): array
    {
        return [BillingUserFixtures::class];
    }
    public function testRegisterNewUser()
    {
        $client = static::createClient();
        $client->request('POST', '/api/v1/register', [], [], [], json_encode(['email' => 'user@gmail.com', 'password' => '1234567']));
        $this->AssertContains('"token"', $client->getResponse()->getContent());
        $this->AssertContains('"roles":["ROLE_USER"]', $client->getResponse()->getContent());
        $this->assertSame(201, $client->getResponse()->getStatusCode());
    }
    public function testRegisterExistingUser()
    {
        $client = static::createClient();
        $client->request('POST', '/api/v1/register', [], [], [], json_encode(['email' => 'simpleUser@gmail.com', 'password' => '1234567']));
        $this->AssertContains('"code":400,"message":["Email already exists"]', $client->getResponse()->getContent());
        $this->assertSame(400, $client->getResponse()->getStatusCode());
    }
    public function testRegisterBlankData()
    {
        $client = static::createClient();
        $client->request('POST', '/api/v1/register', [], [], [], json_encode(['email' => '', 'password' => '']));
        $this->AssertContains('code":400,"message":["Enter email","Enter password"]', $client->getResponse()->getContent());
        $this->assertSame(400, $client->getResponse()->getStatusCode());
    }
    public function testRegisterInvalidData()
    {
        $client = static::createClient();
        $client->request('POST', '/api/v1/register', [], [], [], json_encode(['email' => 'userGmail.com', 'password' => '123']));
        $this->AssertContains('"code":400,"message":["Invalid email","Password must be at least 6 characters"]', $client->getResponse()->getContent());
        $this->assertSame(400, $client->getResponse()->getStatusCode());
    }
    public function testRegisterNotFound()
    {
        $client = static::createClient();
        $client->request('POST', '/api/v1/register1', [], [], [], json_encode(['email' => 'simpleUser@gmail.com', 'password' => 'passwordForSimpleUser']));
        $this->AssertContains('"message":"No route found', $client->getResponse()->getContent());
        $this->assertSame(404, $client->getResponse()->getStatusCode());
    }
    public function testRegisterInvalidJson()
    {
        $client = static::createClient();
        $client->request('POST', '/api/v1/register', [], [], [], '{"email":"simpleUser@gmail.com", "password":12345678"}');
        $this->AssertContains('"code":500', $client->getResponse()->getContent());
        $this->assertSame(500, $client->getResponse()->getStatusCode());
    }
    public function testLoginTrueUser()
    {
        $client = static::createClient();
        $client->request('POST', '/api/v1/auth', [], [], ['CONTENT_TYPE' => 'application/json'], json_encode(['username' => 'simpleUser@gmail.com', 'password' => 'passwordForSimpleUser']));
        $this->AssertContains('"token"', $client->getResponse()->getContent());
        $this->AssertContains('"roles":["ROLE_USER"]', $client->getResponse()->getContent());
    }
    public function testLoginUserWrongPassword()
    {
        $client = static::createClient();
        $client->request('POST', '/api/v1/auth', [], [], ['CONTENT_TYPE' => 'application/json'], json_encode(['username' => 'simpleUser@gmail.com', 'password' => 'passwordSimpleUser']));
        $this->AssertContains('code":401,"message":"Bad credentials"', $client->getResponse()->getContent());
    }
    public function testLoginUserWrongEmail()
    {
        $client = static::createClient();
        $client->request('POST', '/api/v1/auth', [], [], ['CONTENT_TYPE' => 'application/json'], json_encode(['username' => 'User@gmail.com', 'password' => 'passwordForSimpleUser']));
        $this->AssertContains('"code":401,"message":"Bad credentials"', $client->getResponse()->getContent());
    }
    public function testLoginUserBlankData()
    {
        $client = static::createClient();
        $client->request('POST', '/api/v1/auth', [], [], ['CONTENT_TYPE' => 'application/json'], json_encode(['username' => '', 'password' => '']));
        $this->AssertContains('code":401,"message":"Bad credentials', $client->getResponse()->getContent());
    }
    public function testLoginNotFound()
    {
        $client = static::createClient();
        $client->request('POST', '/api/v1/auth1', [], [], ['CONTENT_TYPE' => 'application/json'], json_encode(['username' => 'simpleUser@gmail.com', 'password' => 'passwordSimpleUser']));
        $this->AssertContains('"message":"No route found', $client->getResponse()->getContent());
        $this->assertSame(404, $client->getResponse()->getStatusCode());
    }
    public function testLoginInvalidJson()
    {
        $client = static::createClient();
        $client->request('POST', '/api/v1/register', [], [], [], '{"username":"sir@gmail.com","password":password"}');
        $this->AssertContains('"code":500,"message":"Could not decode JSON, syntax error - malformed JSON."', $client->getResponse()->getContent());
        $this->assertSame(500, $client->getResponse()->getStatusCode());
    }
    public function testCurrentUser()
    {
        $client = static::createClient();
        $client->request('POST', '/api/v1/auth', [], [], ['CONTENT_TYPE' => 'application/json'], json_encode(['username' => 'simpleUser@gmail.com', 'password' => 'passwordForSimpleUser']));
        $response = json_decode($client->getResponse()->getContent(), true);
        $client->request('GET', '/api/v1/users/current', [], [], ['HTTP_AUTHORIZATION' => 'Bearer '.$response['token']]);
        $this->AssertContains('"username":"simpleUser@gmail.com","roles":["ROLE_USER"],"balance":1500', $client->getResponse()->getContent());
    }
    public function testCurrentUserInvalidToken()
    {
        $client = static::createClient();
        $client->request('POST', '/api/v1/auth', [], [], ['CONTENT_TYPE' => 'application/json'], json_encode(['username' => 'simpleUser@gmail.com', 'password' => 'passwordForSimpleUser']));
        $response = json_decode($client->getResponse()->getContent(), true);
        $client->request('GET', '/api/v1/users/current', [], [], ['HTTP_AUTHORIZATION' => 'Bearer jf9834hfuf87efr']);
        $this->AssertContains('"code":401,"message":"Invalid JWT Token"', $client->getResponse()->getContent());
    }
    public function testRefreshToken()
    {
        $client = static::createClient();
        $client->request('POST', '/api/v1/auth', [], [], ['CONTENT_TYPE' => 'application/json'], json_encode(['username' => 'simpleUser@gmail.com', 'password' => 'passwordForSimpleUser']));
        $response = json_decode($client->getResponse()->getContent(), true);
        $client->request('POST', '/api/v1/token/refresh', [], [], ['CONTENT_TYPE' => 'application/json', 'HTTP_AUTHORIZATION' => 'Bearer '.$response['token']], json_encode(['refresh_token' => $response['refresh_token']]));
        $this->AssertContains('"token"', $client->getResponse()->getContent());
    }
    public function testRefreshWrongToken()
    {
        $client = static::createClient();
        $client->request('POST', '/api/v1/auth', [], [], ['CONTENT_TYPE' => 'application/json'], json_encode(['username' => 'simpleUser@gmail.com', 'password' => 'passwordForSimpleUser']));
        $response = json_decode($client->getResponse()->getContent(), true);
        $client->request('POST', '/api/v1/token/refresh', [], [], ['CONTENT_TYPE' => 'application/json', 'HTTP_AUTHORIZATION' => 'Bearer '.$response['token']], json_encode(['refresh_token' => 'gfh89383h99ut']));
        $this->AssertContains('code":401,"message":"Bad credentials', $client->getResponse()->getContent());
    }
}