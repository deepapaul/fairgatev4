<?php

namespace Clubadmin\CommunicationBundle\Tests\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class MailTrackingControllerTest extends WebTestCase
{
    public function testTrackingemail()
    {
        $client = static::createClient();

        $crawler = $client->request('GET', 'track.gif');
    }

}
