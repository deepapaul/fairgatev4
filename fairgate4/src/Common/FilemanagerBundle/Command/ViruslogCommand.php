<?php

/**
 * VirualosCommand
 *
 * This command is used to sent virus log notofication
 *
 * @package    CommonUtilityBundle
 * @subpackage Command
 * @author     pitsolutions.ch
 * @version    Fairgate V4
 */

namespace Common\FilemanagerBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputOption ;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;


/**
 * This command is used to spool scheduled newsletter contacts
 *
 * @author pitsolutions.ch
 */
class ViruslogCommand extends ContainerAwareCommand
{
    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this
            ->setName('viruslog:send_notification')
            ->setDescription('Send daily virus log notifications')
            ->addOption('cron-instance', null, InputOption::VALUE_REQUIRED)
            ->addOption('message-limit', null, InputOption::VALUE_REQUIRED)
            ->addOption('time-limit', null, InputOption::VALUE_REQUIRED);
    }

    /**
     * {@inheritdoc}
     *
     * @throws \InvalidArgumentException When the target directory does not exist or symlink cannot be used
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $output->writeln('########## CRON: SENDING VIRUS LOG NOTIFICATION ##########');
        
        $container = $this->getContainer();
        //$container = $this;
        $translator = $container->get('translator');
        $translator->setLocale('de');
        $em = $container->get('doctrine')->getManager();
        $conn = $container->get('database_connection');
        
        $noreplyEmail = 'noreply@fairgate.ch';
        
        $dateObj = new \DateTime;
        $today = $dateObj->sub(new \DateInterval('P1D'))->format('Y-m-d');
        $filterArray['startDate'] = $today .' 00:00:00';
        $filterArray['endDate'] = $today .' 23:59:59';
        $filterArray['responseStatus'] = array('unsafe','exception','not_responding');
        $logList = $em->getRepository('CommonUtilityBundle:FgFileManagerViruscheckLog')->getVirusLogs($filterArray);
        $logDetailArray = array();
        
        if(count($logList) > 0 || 1){
            foreach($logList as $log){
               $logDetailArray[$log['responseStatus']]['club'][$log['clubId']]['clubname'] = $log['title'];
               $logDetailArray[$log['responseStatus']]['club'][$log['clubId']]['contact'][$log['contactId']] = $log['contact'];
            }
            
            $clubDefaultLanguage = 'de';
            $contactDefaultLanguage = 'de';
            $emailField = 3;
            $superAdmins = $em->getRepository('CommonUtilityBundle:SfGuardUser')->getSuperAdminForNotification($conn, $clubDefaultLanguage, $contactDefaultLanguage, $emailField);

            foreach($superAdmins as $users){
                $emailTemplateForNotification = $container->get('templating')->render('CommonFilemanagerBundle:FileManager:notificationMail.html.twig', array('logDetailArray' => $logDetailArray, 'salutation' => $users['salutationText']));
                $em->getRepository('CommonUtilityBundle:FgNotificationSpool')->addNotificationEntries($users['email'], $emailTemplateForNotification, $translator->trans('NOTIFICATION_VIRUSLOG_MAIL_TITLE'), $noreplyEmail, 'VIRUSLOG');
            }
        }
        
        $output->writeln('########## CRON: SENDING VIRUS LOG NOTIFICATION ENDS ##########');
    }
}

