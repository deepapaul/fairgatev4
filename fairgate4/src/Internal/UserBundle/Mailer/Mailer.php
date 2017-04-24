<?php

/*
 * This file is overrided from FOSUserBundle package.
 *
 * (c) FriendsOfSymfony <http://friendsofsymfony.github.com/>
 *
 */

namespace Internal\UserBundle\Mailer;

use Symfony\Bundle\FrameworkBundle\Templating\EngineInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use FOS\UserBundle\Model\UserInterface;
use FOS\UserBundle\Mailer\MailerInterface;
use FOS\UserBundle\Mailer\Mailer as BaseMailer;
use Common\UtilityBundle\Util\FgUtility;

/**
 * @author Thibault Duplessis <thibault.duplessis@gmail.com>
 */
class Mailer extends BaseMailer implements MailerInterface
{
    protected $mailer;
    protected $router;
    protected $templating;
    protected $parameters;

    public function __construct(\Swift_Mailer $mailer, UrlGeneratorInterface  $router, EngineInterface $templating )
    {

        $this->mailer = $mailer;
        $this->router = $router;
        $this->templating = $templating;
        $this->parameters = $parameters;
    }

    /**
     * {@inheritdoc}
     */
    public function sendConfirmationEmailMessage(UserInterface $user)
    {
        parent::sendConfirmationEmailMessage($user);
    }

    /**
     * Method to send email on restting password
     *
     * @param Object UserInterface $user    UserInterface Object
     */
    public function sendResettingEmailMessage(UserInterface $user)
    {
        $resetPasswordRouting = $this->parameters['resetPasswordRouting'];
        $resetPasswordTemplate = $this->parameters['resetPasswordTemplate'];
        $clubTitle = $this->parameters['clubTitle'];
        $salutation = $this->parameters['salutation'];
        $clubLogoUrl = $this->parameters['clubLogoUrl'];        
        $url = FgUtility::generateUrl($this->parameters['container'], '', $resetPasswordRouting, array('token' => $user->getConfirmationToken())) ; 
        $rendered = $this->templating->render($resetPasswordTemplate, array(
            'user' => $user,
            'confirmationUrl' => $url,
            'club' => $clubTitle,
            'salutation' => $salutation,
            'logoURL' =>$clubLogoUrl
        ));
        $this->sendEmailMessage($rendered, 'noreply@fairgate.ch', $user->getEmail());
    }

    /**
     * @param string $renderedTemplate
     * @param string $fromEmail
     * @param string $toEmail
     */
    protected function sendEmailMessage($renderedTemplate, $fromEmail, $toEmail)
    {        
        $subject = $this->parameters['subject'];
        $body = $renderedTemplate;

        $message = \Swift_Message::newInstance()
            ->setSubject($subject)
            ->setFrom($fromEmail)
            ->setTo($toEmail)
            ->setContentType("text/html")
            ->setBody($body);

        $this->mailer->send($message);
    }
    
    /**
     * Method to set parameter values
     * 
     * @param array $parameters parameter values to set
     */
    public function setParams($parameters){ 
        $this->parameters = $parameters;
    }
    
}
