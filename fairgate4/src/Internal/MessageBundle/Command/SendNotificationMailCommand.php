<?php

/**
 * SendNotificationMailCommand
 *
 * This command is used to send notification mail from fg_notification spool
 *
 * @package    InternalMessageBundle
 * @subpackage Command
 * @author     pitsolutions.ch
 * @version    Fairgate V4
 */
namespace Internal\MessageBundle\Command;

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;

/**
 * Used for resenting the bounced emails - insert to spool
 *
 */
class SendNotificationMailCommand extends ContainerAwareCommand
{

    /**
     * This function is used for configuring massage    
     *
     * @return Template
     */
    protected function configure()
    {
        $this
            ->setName('notificationmail:send')
            ->setDescription('Send notification mails');
    }

    /**
     * Converastion page
     *
     * @param Object $input Input data
     * @param Object $output Output data
     *
     * @return Template
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $output->writeln('######### CRON: SENT NOTIFICATION MAIL STARTS ####');
        $container = $this->getContainer();
        $mailer = $container->get('mailer');
        $doctrine = $container->get('doctrine');
        $em = $doctrine->getManager();
        $mailerObjects = $em->getRepository('CommonUtilityBundle:FgNotificationSpool')->getSpoolEntries();
        foreach ($mailerObjects as $mail) {
            try {
                $message = unserialize(stream_get_contents($mail->getTemplateContent()));
                $numSent = $mailer->send($message);
            } catch (Exception $e) {
                $output->writeln('\nError :: ' . $e);
            }
            $em->getRepository('CommonUtilityBundle:FgNotificationSpool')->deleteFromSpool($mail->getId());
        }
        $output->writeln('######### CRON: SENT NOTIFICATION MAIL ENDS ####');
    }
}
