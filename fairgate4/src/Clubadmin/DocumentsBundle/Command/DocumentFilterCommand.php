<?php

/**
 * DocumentFilterCommand
 *
 * This command is used to update assigned contacts of contactdocuments with filter
 *
 * @package    ClubadminDocumentsBundle
 * @subpackage Command
 * @author     pitsolutions.ch
 * @version    Fairgate V4
 */

namespace Clubadmin\DocumentsBundle\Command;

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Common\UtilityBundle\Routing\FgRoutingListener;
use Common\UtilityBundle\Repository\Pdo\DocumentPdo;

/**
 * Used for moving the bounced emails to a separate folder and update in db table
 *
 */
class DocumentFilterCommand extends ContainerAwareCommand {

    protected function configure() {
        $this
                ->setName('documentfilter:update')
                ->setDescription('Update contact documents with filter');
    }

    /**
     * Cron command to update contact assignment to document via filter
     *
     * @param \Symfony\Component\Console\Input\InputInterface $input
     * @param \Symfony\Component\Console\Output\OutputInterface $output
     */
    protected function execute(InputInterface $input, OutputInterface $output) {
        $container = $this->getContainer();
        $doctrine = $container->get('doctrine');
        $em = $doctrine->getManager();

        $resultDocuments = $em->getRepository('CommonUtilityBundle:FgDmDocuments')->getContactFilterDocuments();
        $output->writeln('######### CRON: UPDATE DOCUMENT FILTER STARTS ####');
        $clubId = 0;
        foreach ($resultDocuments as $resultDocument) {
            if ($clubId != $resultDocument['clubId']) {
                $clubId = $resultDocument['clubId'];
                $club = new FgRoutingListener($container, null, $clubId, true);
                $container->set('club', $club);
            }
            try {
                $docPdo = new DocumentPdo($container);
                $docPdo->updateDocumentFilterContacts($resultDocument['id'], $resultDocument['filter'], array(), true);
            } catch (\Doctrine\DBAL\DBALException $e) {

            }
        }
        $output->writeln('######### CRON: UPDATE DOCUMENT FILTER ENDS ####');
    }

}
