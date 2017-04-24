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
use Symfony\Component\Console\Input\InputArgument;
use Common\UtilityBundle\Repository\Pdo\ContactPdo;

/**
 * This command is used to update filter contacts
 *
 * @author pitsolutions.ch
 */
class RemoveArchivedContactFromTablesCommand extends ContainerAwareCommand
{

    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this
            ->setName('archivedcontact:remove')
            ->addArgument('contacts', InputArgument::IS_ARRAY, 'contacts?')
            ->setDescription('Remove archived contact from fg_club_bookmarks and fg_club_table setting table');
    }

    /**
     * {@inheritdoc}
     *
     * @throws \InvalidArgumentException When the target directory does not exist or symlink cannot be used
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $container = $this->getContainer();
        $adminEntityManger = $container->get('doctrine.orm.admin_entity_manager');
        $conn = $container->get('fg.admin.connection')->getAdminConnection();
        $contacts = $input->getArgument('contacts');
        $ids = '';
        if (count($contacts) > 0) {
            $ids .= implode(',', $contacts);
        }
        $contactObj = new ContactPdo($container);
        $output->writeln('######### CRON: REMOVE ARCHIVED CONTACTS  STARTS ####');
        $contactDetails = $contactObj->getContactIdDetails($ids);
        $deletedIds = array();
        foreach ($contactDetails as $contactDetail) {
            $deletedIds[] = $contactDetail['id'];
            if ($contactDetail['subfed_contact_id'] != '' && $contactDetail['subfed_contact_id']!=$contactDetail['id']) {
                $deletedIds[] = $contactDetail['subfed_contact_id'];
            }
            if ($contactDetail['fed_contact_id'] != '' && $contactDetail['fed_contact_id']!=$contactDetail['id']) {
                $deletedIds[] = $contactDetail['fed_contact_id'];
            }
        }
        $allIds = implode(',',$deletedIds);
        file_put_contents('q1.txt','d.m.Y##'. $allIds."$$$$$$",FILE_APPEND);
        try {
            
           $conn->executeQuery("DELETE FROM `fg_club_bookmarks` WHERE `contact_id` IN($allIds)");
           $conn->executeQuery("DELETE FROM `fg_club_table_settings` WHERE `contact_id` IN($allIds)");
        } catch (\Doctrine\DBAL\DBALException $e) {
            echo $e;
        }
       

        $output->writeln('######### CRON: REMOVE ARCHIVED CONTACTS ENDS ####');
    }
}
