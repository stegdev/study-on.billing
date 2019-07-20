<?php

namespace App\Tests;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Request;
use App\DataFixtures\BillingUserFixtures;
use App\Tests\AbstractTest;

class TransactionTest extends AbstractTest
{
    public function getFixtures(): array
    {
        return [BillingUserFixtures::class];
    }

    public function regNewUser($client)
    {
        $client->request('POST', '/api/v1/register', [], [], [], json_encode(['email' => 'user@gmail.com', 'password' => '1234567']));
        $response = json_decode($client->getResponse()->getContent(), true);
        return $response['token'];
    }

    public function authUser($client)
    {
        $client->request('POST', '/api/v1/auth', [], [], ['CONTENT_TYPE' => 'application/json'], json_encode(['username' => 'aaa@mail.ru', 'password' => '123456']));
        $response = json_decode($client->getResponse()->getContent(), true);
        return $response['token'];
    }

    public function testGetFirstDeposit()
    {
        $client = static::createClient();
        $client->request('GET', '/api/v1/transactions', [], [], ['HTTP_AUTHORIZATION' => 'Bearer '. $this->regNewUser($client)]);
        $this->AssertContains('"type":"deposit","amount":1000.0', $client->getResponse()->getContent());
    }

    public function testFilterWrongType()
    {
        $client = static::createClient();
        $token = $this->authUser($client);
        $client->request('GET', '/api/v1/transactions?type=paymenttt', [], [], ['HTTP_AUTHORIZATION' => 'Bearer '. $token]);
        $this->AssertContains('"code":400,"message":"Type must be payment or deposit"', $client->getResponse()->getContent());
    }

    public function testFilterWrongCourse()
    {
        $client = static::createClient();
        $token = $this->authUser($client);
        $client->request('GET', '/api/v1/transactions?course_code=build-from-scratch', [], [], ['HTTP_AUTHORIZATION' => 'Bearer '. $token]);
        $this->AssertContains('"code":404,"message":"No course found"', $client->getResponse()->getContent());
    }
}