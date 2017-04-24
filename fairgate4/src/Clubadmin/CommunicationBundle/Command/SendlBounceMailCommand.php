<?php
/**
 * SendlBounceMailCommand
 *
 * This command is used to insert spool entries for scheduled newsletters
 *
 * @package    CommonUtilityBundle
 * @subpackage Command
 * @author     pitsolutions.ch
 * @version    Fairgate V4
 */

namespace Clubadmin\CommunicationBundle\Command;

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;

/**
 * Used for resenting the bounced emails - insert to spool
 *
 */
class SendlBounceMailCommand extends ContainerAwareCommand  {

    protected function configure()
    {
        $this
            ->setName('bouncemail:send')
            ->setDescription('Send bounce mails') ;
    }


    protected function execute(InputInterface $input, OutputInterface $output) {
        $output->writeln('######### CRON: RESENT BOUNCE MAIL STARTS ####');
        $container = $this->getContainer();
        $doctrine  = $container->get('doctrine');
        $em = $doctrine->getManager();
        $resultRoles = $em->getRepository('CommonUtilityBundle:FgCnNewsletterReceiverLog')->resentBounceMails();
        $output->writeln('######### CRON: RESENT BOUNCE MAIL ENDS ####');
    }
}
