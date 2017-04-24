<?php

/**
 * FilterUpdateCommand
 *
 * This command is used to update filter contacts for filterdriven roles
 *
 * @package    CommonUtilityBundle
 * @subpackage Command
 * @author     pitsolutions.ch
 * @version    Fairgate V4
 */

namespace Common\UtilityBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Common\UtilityBundle\Routing\FgRoutingListener;

/**
 * This command is used to update filter contacts
 *
 * @author pitsolutions.ch
 */
class FilterUpdateCommand extends ContainerAwareCommand {

    /**
     * {@inheritdoc}
     */
    protected function configure() {
        $this
                ->setName('filter:update')
                ->setDescription('Update filter contacts for filterdriven roles');
    }

    /**
     * {@inheritdoc}
     *
     * @throws \InvalidArgumentException When the target directory does not exist or symlink cannot be used
     */
    protected function execute(InputInterface $input, OutputInterface $output) {
        $container = $this->getContainer();
        $doctrine = $container->get('doctrine');
        $em = $doctrine->getManager();

        $resultRoles = $em->getRepository('CommonUtilityBundle:FgRmRole')->getAllFilterRoles();
        $output->writeln('######### CRON: UPDATE FILTER STARTS ####');
        $clubId = 0;
        foreach ($resultRoles as $resultRole) {
            if ($clubId != $resultRole['clubId']) {
                $clubId = $resultRole['clubId'];
                $club = new FgRoutingListener($container, null, $clubId, true);
                $container->set('club', $club);
            }
            $club = $container->get('club');
            try {
                $em->getRepository('CommonUtilityBundle:FgRmRole')->updateFilterRoles($resultRole['roleId'], $container, 1);
            } catch (\Doctrine\DBAL\DBALException $e) {
            }
        }
        $output->writeln('######### CRON: UPDATE FILTER ENDS ####');
    }

}
