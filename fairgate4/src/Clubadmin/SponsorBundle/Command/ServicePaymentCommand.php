<?php

/**
 * ServicePaymentCommand
 *
 * This command is used to add payment plan of regular payment for the comming year
 *
 * @package    ClubadminSponsorBundle
 * @subpackage Command
 * @author     pitsolutions.ch
 * @version    Fairgate V4
 */

namespace Clubadmin\SponsorBundle\Command;

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Common\UtilityBundle\Routing\FgRoutingListener;

/**
 * Used for moving the bounced emails to a separate folder and update in db table
 *
 */
class ServicePaymentCommand extends ContainerAwareCommand {

    protected function configure() {
        $this
                ->setName('servicePaymentPlan:update')
                ->setDescription('Update service assignment payment plan');
    }

    /**
     * Cron command to update service assignment payment plan of regular assignments
     *
     * @param \Symfony\Component\Console\Input\InputInterface $input
     * @param \Symfony\Component\Console\Output\OutputInterface $output
     */
    protected function execute(InputInterface $input, OutputInterface $output) {
        $container = $this->getContainer();
        $doctrine = $container->get('doctrine');
        $em = $doctrine->getManager();

        $resultBookings = $em->getRepository('CommonUtilityBundle:FgSmBookings')->getBookingsForPaymentUpdate();
        $output->writeln('######### CRON: UPDATE PAYMENT PLAN STARTS ####');
        foreach ($resultBookings as $resultBooking) {
            try {
                $em->getRepository('CommonUtilityBundle:FgSmBookings')->updatePaymentForNextYear($resultBooking);
            } catch (\Doctrine\DBAL\DBALException $e) {

            }
        }
        $output->writeln('######### CRON: UPDATE PAYMENT PLAN ENDS ####');
    }

}
