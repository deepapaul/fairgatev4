<?php

/**
 * UnsendMailDetailsCommand
 *
 * This command is used to get the detail of stucked mail details 
 *
 * @package    CommunicationBundle
 * @subpackage Command
 * @author     pitsolutions.ch
 * @version    Fairgate V4
 */

namespace Clubadmin\CommunicationBundle\Command;

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Common\UtilityBundle\Util\FgUtility;

/**
 * Used for collect stuck mail detail of club for sending alert mail.
 *
 */
class UnsendMailDetailsCommand extends ContainerAwareCommand
{
   protected function configure()
    {
        $this
            ->setName('stuckedmailalert:send')
            ->setDescription('send stucked newsletter/simplemail details') ;
    }
    protected function execute(InputInterface $input, OutputInterface $output) {
        $output->writeln('########## CRON: SENDING STUCK MAIl DETAILS ##########');        
        $container = $this->getContainer();
        $em = $container->get('doctrine')->getManager();    
        $noreplyEmail = 'noreply@fairgate.ch';
        //gather unsent newsletter/simple mail details
        $stuckMailDetails = $em->getRepository('CommonUtilityBundle:FgCnNewsletter')->stuckedNewsletterDetails('sending');
        //gather alert mail sending  mailids from settings.php
        $notificationSendMails = $container->getParameter('newsletter_notification_mails');
        $sendDate = date('d.m.Y');      
        //subject setting
        $subject ="eMail Report on jammed Newsletters for ".$sendDate;
        $baseUrl = $container->getParameter('base_url');
        if(count($stuckMailDetails) > 0) {
            foreach($notificationSendMails as $users){
                //create template
                $emailTemplateForNotification = $container->get('templating')->render('ClubadminCommunicationBundle:Newsletter:stuckMailNotificationTemplate.html.twig', array('stuckMailDetails' => $stuckMailDetails, 'salutation' => 'Hi','baseUrl' => $baseUrl));
                $this->sendStuckMailDetails($users, $emailTemplateForNotification, $subject, $noreplyEmail);  
            }
           
        }
       
       
        $output->writeln('######### CRON: MAIL SEND ####');
    }
    /**
     * Function to send stuck mail details 
     * @param type $email
     * @param type $templateContent
     */
    protected function sendStuckMailDetails($email,$templateContent,$subject,$from){
        $container = $this->getContainer();
        $mailer = $container->get('mailer');
        //mail sending area
        $message = \Swift_Message::newInstance()
                            ->setSubject($subject)
                            ->setFrom($from)
                            ->setTo($email)
                            ->setBody($templateContent,'text/html')
                            ->setPriority(3)
                            ->setCharset('utf-8');
        $mailer->send($message);
       
    }
    

}
