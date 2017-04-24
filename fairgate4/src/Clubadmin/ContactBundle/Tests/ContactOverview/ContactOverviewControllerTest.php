<?php

/*
 * This file is part of the FOSUserBundle package.
 *
 * (c) FriendsOfSymfony <http://friendsofsymfony.github.com/>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Clubadmin\ContactBundle\Tests\ContactOverview;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

/**
 * contact overview controller test case
 */
class ContactOverviewControllerTest extends WebTestCase
{
    protected $client;
    /**
     * overviewcontentAction
     */
    public function testrenderOverviewContentAction()
    {
        $this->client = static::createClient();
        $crawler = $this->client->request('GET', 'contactoverview');
        $html = $crawler->html();
        echo $html;
        $this->assertTrue($crawler->filter('html:contains("<div class="col-md-6 leftDisplay">")')->count() > 0);

    }
}
