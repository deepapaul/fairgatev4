<?php

/**
 * NewsletterSchedulerCommand
 *
 * This command is used to insert spool entries for scheduled newsletters
 *
 * @package    CommonUtilityBundle
 * @subpackage Command
 * @author     pitsolutions.ch
 * @version    Fairgate V4
 */

namespace Clubadmin\CommunicationBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputOption ;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Common\UtilityBundle\Routing\FgRoutingListener;

/**
 * This command is used to spool scheduled newsletter contacts
 *
 * @author pitsolutions.ch
 */
class NewsletterSchedulerCommand extends ContainerAwareCommand
{
    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this
            ->setName('newsletter:insert_to_spool')
            ->setDescription('Insert scheduled newslettres receivers to fg_mail_message table')
            ->addOption('time-limit', null, InputOption::VALUE_REQUIRED);
    }

    /**
     * {@inheritdoc}
     *
     * @throws \InvalidArgumentException When the target directory does not exist or symlink cannot be used
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        echo "CRON -STARTS";
        $timeLimit = $input->getOption('time-limit');
        $startTime = time();
        $container = $this->getContainer();
        $doctrine  = $container->get('doctrine');
        $em = $doctrine->getManager();

        //update send newsletters status
        $em->getRepository('CommonUtilityBundle:FgCnNewsletter')->updateStatusOfSendCompletedNewsletters();
        $scheduledNewsletters = $em->getRepository('CommonUtilityBundle:FgCnNewsletter')->getScheduledNewsletters();
        $output->writeln('########## CRON: INSERT TO SPOOL TABLE STARTS ##########');
        $clubId = 0;
        foreach ($scheduledNewsletters as $scheduledNewsletter) { 
            $currentTime = time();
            if (($currentTime - $startTime) >= $timeLimit) {
                break;
            }
            if ($clubId != $scheduledNewsletter['clubId']){
                $clubId = $scheduledNewsletter['clubId'];
                $club = new FgRoutingListener($container, null, $clubId, true);
                $container->set('club', $club);
            }
            $club = $container->get('club');
            echo $club->get('club_team_id');
            $fgSendNewsletterObj = new \Clubadmin\CommunicationBundle\Util\FgSendNewsletters($container, $scheduledNewsletter);
            $fgSendNewsletterObj->updateNewsletterStatusAndContent();
            $fgSendNewsletterObj->insertNewsletterContactsToSpool();
            $output->writeln('ClubId = '. $club->get('id').'******NewsletterId = '.$scheduledNewsletter['id']);
        }
        $output->writeln('########## CRON: INSERT TO SPOOL TABLE ENDS ##########');
    }
}

