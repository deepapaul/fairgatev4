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
 * This command is used to Update the club status to 'INVALID' and remove the club url_identifier 
 * and unset password of main contact if the account is not confirmed by the user within 7 days
 *
 * @author pitsolutions.ch
 */
class MakeClubInactiveCommand extends ContainerAwareCommand
{

    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this
            ->setName('club:updateStatus')
            ->setDescription('Update club status if the account is not confirmed');
    }

    /**
     * {@inheritdoc}
     *
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $output->writeln('######### CRON: UPDATE CLUB STATUS STARTS ####');
        $container = $this->getContainer();
        $adminEm = $container->get('doctrine.orm.admin_entity_manager');
        $nonConfirmedClubs = $adminEm->getRepository('AdminUtilityBundle:FgClub')->getNonConfirmedClubs();   
        //update status and url_identifiers in admin db
        $adminEm->getRepository('AdminUtilityBundle:FgClub')->makeNonConfirmedClubsInactive();        
        $em = $container->get('doctrine')->getManager();
        //unset url identifiers in fairgatedb
        $em->getRepository('CommonUtilityBundle:FgClub')->unsetUrlIdentifiersOfClubs($nonConfirmedClubs); 
        //unset password of main contact ids
        $em->getRepository('CommonUtilityBundle:SfGuardUser')->unsetPasswordOfMainContacts($nonConfirmedClubs); 
                
        $output->writeln('######### CRON: UPDATE CLUB STATUS ENDS ####');
    }
}
