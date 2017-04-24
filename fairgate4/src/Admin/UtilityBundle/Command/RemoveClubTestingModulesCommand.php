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
namespace Admin\UtilityBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * This command is used to remove unbooked club modules after 30 days of testing period,
 * and clear contract start date
 *
 * @author pitsolutions.ch
 */
class RemoveClubTestingModulesCommand extends ContainerAwareCommand
{

    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this
            ->setName('club:removeTestingModules')
            ->setDescription('Remove unbooked club modules after 30 days of testing period');
    }

    /**
     * {@inheritdoc}
     *
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $output->writeln('######### CRON: REMOVE MODULES STARTS ####');
        $container = $this->getContainer();
        $adminEm = $container->get('doctrine.orm.admin_entity_manager');
        //get expired clubs
        $clubsExpired = $adminEm->getRepository('AdminUtilityBundle:FgClub')->getClubsExpired();
        $clubIdsExpired = array_column($clubsExpired, 'id');
        //clear contract start date
        $adminEm->getRepository('AdminUtilityBundle:FgClub')->clearContractStartDateOfClubs($clubIdsExpired);
        $modulesIds = $container->getParameter('modulesForTesingPeriod');
        //remove  modules of clubs
        $adminEm->getRepository('AdminUtilityBundle:FgMbClubModules')->removeModulesOfClubs($clubIdsExpired, $modulesIds);

        $output->writeln('######### CRON: REMOVE MODULES ENDS ####');
    }
}
