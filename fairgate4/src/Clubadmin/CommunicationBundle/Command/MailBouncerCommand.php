<?php
/**
 * MailBouncerCommand
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
use Common\UtilityBundle\Util\MailBouncer;

/**
 * Used for moving the bounced emails to a separate folder and update in db table
 *
 */
class MailBouncerCommand extends ContainerAwareCommand  {

    protected function configure()
    {
        $this
            ->setName('bouncemail:update')
            ->setDescription('Keep bounced mails in folder') ;
    }


    protected function execute(InputInterface $input, OutputInterface $output) {
        $output->writeln('######### CRON: GET BOUNCE MAIL STARTS ####');
        $container = $this->getContainer();
        $mailObj = new MailBouncer($container);        
        $folder = $mailObj->folder;
        $mailObj->inbox();
        if($folder === "inbox") {
            $mailObj->spam();
        }
        $output->writeln('######### CRON: GET BOUNCE MAIL ENDS ####');
    }
}
