<?php

namespace Common\UtilityBundle\Tests\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class MailTrackingControllerControllerTest extends WebTestCase
{
    public function testTrackingemail()
    {
        $client = static::createClient();

        $crawler = $client->request('GET', 'track_email');
    }

}
