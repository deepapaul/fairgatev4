<?php

namespace Clubadmin\CommunicationBundle\Tests\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class StatisticsControllerTest extends WebTestCase
{
    public function testNewsletter()
    {
        $client = static::createClient();

        $crawler = $client->request('GET', '/newsletter/statistics');
    }

}
