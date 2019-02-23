<?php

namespace App\Tests\Functional;

use App\DataFixtures\UserFixtures;
use Symfony\Bundle\FrameworkBundle\Client;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

abstract class BaseWebTestCase extends WebTestCase
{
    public const TEST_AUTHENTICATION_HEADER_NAME = 'test_authentication_header';

    protected function wrapItemsInArray(array $items): array
    {
        return array_map(
            function ($item): array {
                return [$item];
            },
            $items
        );
    }

    protected static function createAuthenticatedClient(string $username = UserFixtures::STUDENT_USERNAME): Client
    {
        $client = static::createClient([], [
            self::TEST_AUTHENTICATION_HEADER_NAME => $username,
        ]);

        return $client;
    }

    /**
     * @return mixed
     */
    protected function ajaxGet(Client $client, string $url)
    {
        $client->xmlHttpRequest('GET', $url);

        return json_decode($client->getResponse()->getContent(), true);
    }

    /**
     * @return mixed
     */
    protected function ajaxPost(Client $client, string $url, array $parameters = [])
    {
        $client->xmlHttpRequest('POST', $url, $parameters);

        return json_decode($client->getResponse()->getContent(), true);
    }
}