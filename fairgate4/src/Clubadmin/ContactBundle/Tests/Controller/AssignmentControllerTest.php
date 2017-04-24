<?php

/*
 * This file is part of the FOSUserBundle package.
 *
 * (c) FriendsOfSymfony <http://friendsofsymfony.github.com/>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Clubadmin\ContactBundle\Tests\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
/**
 * assignment controller test case
 */
class AssignmentControllerTest extends WebTestCase
{
    protected $client;
    /**
     * function to test the assignment for a contact are rendered
     */
    public function testrenderAssignmentContentAction()
    {
        $this->client = static::createClient();
        $crawler = $this->client->request('GET', 'contactoverview/435595');
        $html = $crawler->html();
        echo $html;
        $this->assertTrue($crawler->filter('html:contains("<div class="col-md-6 fg-common-top">")')->count() > 0);
    }
    /**
     * function to check the action for update contact assignment works
     */
    public function testUpdateContactAssignmentsAction()
    {
        $client = static::createClient();
        $crawler = $client->request('POST', '/Assignment/UpdateContactAssignments');
        $this->assertEquals(1, $crawler->filter('.fairgatedirty')->count());
    }
}
