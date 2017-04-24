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
 * Controller managing the resetting of the password
 *
 * @author Thibault Duplessis <thibault.duplessis@gmail.com>
 * @author Christophe Coevoet <stof@notk.org>
 */
class ResettingControllerTest extends WebTestCase
{
    /**
     * Request reset user password: submit form and send email
     */
    public function testindexAction()
    {
        $username = $this->container->get('request')->request->get('username');
        /** @var $user UserInterface */
        $user = $this->container->get('fos_user.user_manager')->findUserByUsernameOrEmail($username);
        if (null === $user) {
            return $this->container->get('templating')->renderResponse('CommonUtilityBundle:Resetting:request.html.'.$this->getEngine(), array('invalid_username' => $username));
        }
        if ($user->isPasswordRequestNonExpired($this->container->getParameter('fos_user.resetting.token_ttl'))) {
            return $this->container->get('templating')->renderResponse('CommonUtilityBundle:Resetting:passwordAlreadyRequested.html.'.$this->getEngine());
        }
        if (null === $user->getConfirmationToken()) {
            /** @var $tokenGenerator \FOS\UserBundle\Util\TokenGeneratorInterface */
            $tokenGenerator = $this->container->get('fos_user.util.token_generator');
            $user->setConfirmationToken($tokenGenerator->generateToken());
        }

        $this->em = $this->getDoctrine()->getManager();
        $this->conn = $this->container->get('database_connection');
        $storedprocedure=$this->conn->prepare("SELECT salutationText( 1,1,'de',NULL )");
        $storedprocedure->execute();
        $results= $storedprocedure->fetchAll();
        $results=$results[0];
        foreach($results as $key=>$val){
            $salutation = $val;
        }
        $resetPasswordTemplate =  'CommonUtilityBundle:Resetting:resetPassword.html.twig';
        $subject=$this->get('translator') ->trans('Resetpassword');
        $this->container->get('session')->set(static::SESSION_EMAIL, $this->getObfuscatedEmail($user));
        $this->container->get('fos_user.mailer')->sendResettingEmailMessage($user, $resetPasswordTemplate, $this->clubUrlIdentifier, $salutation, $subject);
        $user->setPasswordRequestedAt(new \DateTime());
        $this->container->get('fos_user.user_manager')->updateUser($user);

        return new RedirectResponse($this->container->get('router')->generate('fos_user_resetting_check_email'));
    }
}
