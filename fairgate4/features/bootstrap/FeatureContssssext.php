<?php

use Behat\Behat\Context\ClosuredContextInterface,
    Behat\Behat\Context\TranslatedContextInterface,
    Behat\Behat\Context\BehatContext,
        Behat\Behat\Tester\Exception\PendingException;
use Behat\Gherkin\Node\PyStringNode,
    Behat\Gherkin\Node\TableNode;

use Behat\MinkExtension\Context\MinkContext;
use Behat\Mink\Driver\GoutteDriver;
use Behat\Mink\Session;
use Behat\Mink\Driver\Goutte\Client;

class FeatureContext extends MinkContext
{
    private $session;
    private $response;

    /**
     * Initializes context.
     *
     * Every scenario gets its own context instance.
     * You can also pass arbitrary arguments to the
     * context constructor through behat.yml.
     */
    public function __construct()
    {
//        $driver = new GoutteDriver();
//
//        $this->session = new Session($driver);
//
//        // start the session
//        $this->session->start();
    }
//    
////    /**
////     * @When I fill in :arg1 with :arg2 and :arg3 with :arg4
////     */
////    public function iFillInWithAndWith($arg1='_username', $arg2='superadmin', $arg3='_password', $arg4='test')
////    {
////        throw new PendingException();
////    }
////    
////    /**
////     * @Given I add :arg1 header equal to :arg2 and :arg3 header equal to :arg4
////     */
////    public function iAddHeaderEqualToAndHeaderEqualTo($arg1, $arg2, $arg3, $arg4)
////    {
////        $driver = new GoutteDriver();
////
////        $this->session = new Session($driver);
////
////        // start the session
////        $this->session->start();
////        // setting browser language:
////        $this->session->setRequestHeader($arg1, $arg2);
////        $this->session->setRequestHeader($arg3, $arg4);
////
////    }
////
////    /**
////     * @When I send a GET request to :arg1
////     */
////    public function iSendAGetRequestTo($arg1)
////    {
////        $this->response = $this->session->visit($arg1);
//////        $client = new Client(['base_url' => 'http://localhost:8083/']);
//////        $this->_client = $client;
//////        $request = $this->_client->get($arg1);
//////        $this->_response = $request;
////    }
//
//    /**
//     * @When I request :arg1
//     */
//    public function iRequest($arg1)
//    {
//        throw new PendingException();
//    }
//
//    /**
//     * @Then the :arg1 header should be :arg2
//     */
//    public function theHeaderShouldBe($arg1, $arg2)
//    {
//        throw new PendingException();
//    }
//
//    /**
//     * @Then the :arg1  header should be :arg2
//     */
//    public function theHeaderShouldBe2($arg1, $arg2)
//    {
//        throw new PendingException();
//    }

}
