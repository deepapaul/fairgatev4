<?php

/*
 * This file is part of the FOSUserBundle package.
 *
 * (c) FriendsOfSymfony <http://friendsofsymfony.github.com/>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Clubadmin\ContactBundle\Tests\Userrights;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

/**
 * UserrightsControllerTest for unit testing
 *
 * @author PITSolutions <pit@solutions.com>
 */
class UserrightsControllerTest extends WebTestCase
{
    protected $client;

    /**
     * testrenderIndexAction
     *
     */
    public function testrenderIndexAction()
    {
        $this->client = static::createClient();
        $crawler = $this->client->request('GET', 'userrightsettings');
        $html = $crawler->html();
        echo $html;
        $this->assertTrue($crawler->filter('html:contains("<div class="col-md-12 fg-user-div fg-common-top fg-dev-user-rights-elements">")')->count() > 0);
    }
}
