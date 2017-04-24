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
use Symfony\Component\Console\Input\InputArgument;
use Admin\UtilityBundle\Classes\SyncFgadmin;

/**
 * This command is used to update filter contacts
 *
 * @author pitsolutions.ch
 */
class DocumentCountUpdateCommand extends ContainerAwareCommand
{

    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this
            ->setName('doccount:update')
            ->addArgument('clubId', InputArgument::REQUIRED, 'club id?')
            ->addArgument('clubtype', InputArgument::REQUIRED, 'club type?')
            ->setDescription('Update document count of federation/subfederation/count');
    }

    /**
     * {@inheritdoc}
     *
     * @throws \InvalidArgumentException When the target directory does not exist or symlink cannot be used
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $container = $this->getContainer();
        $em = $container->get('doctrine.orm.admin_entity_manager');
        $clubid = $input->getArgument('clubId');
        $clubType = $input->getArgument('clubtype'); 
        $resultClubs = $em->getRepository('AdminUtilityBundle:FgClub')->getAllClub($clubid,$clubType);
        $output->writeln('######### CRON: UPDATE CLUB DOCUMENT COUNT  STARTS ####');
file_put_contents('q1.txt',$clubid) ;       
        foreach ($resultClubs as $resultClub) {
            file_put_contents('q1.txt',"###".$resultClub['clubId'],FILE_APPEND) ;   
            $clubId = $resultClub['clubId'];            
            $syncCount = new SyncFgadmin($container);
            try {
                $federationId = $clubid;
                $syncCount->syncDocumentCount($clubId,$federationId);
            } catch (\Doctrine\DBAL\DBALException $e) {
                echo  $e;
            }
        }
        $output->writeln('######### CRON: UPDATE CLUB DOCUMENT COUNT ENDS ####');
    }
}
