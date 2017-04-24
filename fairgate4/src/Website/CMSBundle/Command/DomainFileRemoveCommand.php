<?php

/**
 * DomainFileRemoveCommand
 *
 * This command is used to clear the domain file that was added to a website
 *
 * @package    WebsiteCMSBundle
 * @subpackage Command
 * @author     pitsolutions.ch
 * @version    Fairgate V4
 */
namespace Website\CMSBundle\Command;

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Common\UtilityBundle\Util\FgUtility;

/**
 * Used for resenting the bounced emails - insert to spool
 *
 */
class DomainFileRemoveCommand extends ContainerAwareCommand
{

    protected function configure()
    {
        $this
            ->setName('website:cleardomainfile')
            ->setDescription('Clear domain file after 7 days');
    }

    /**
     * 
     * @param InputInterface $input
     * @param OutputInterface $output
     * 
     * @return void
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $container = $this->getContainer();
        $em = $container->get('doctrine')->getManager();
        $domainFiles = $em->getRepository('CommonUtilityBundle:FgWebSettings')->getNotEmptyDomainFiles();

        $timeLimit = time() - (7 * 24 * 60 * 60);
        $clearSettingsIdArray = array();

        foreach ($domainFiles as $file) {
            $domainFileName = FgUtility::getRootPath($container) . '/' . $file['domainVerificationFilename'];
            if (file_exists($domainFileName)) {
                $domainFileCreatedTime = filectime($domainFileName);
                if ($domainFileCreatedTime < $timeLimit) {
                    $clearSettingsIdArray[] = $file['id'];
                    unlink($domainFileName);
                }
            }
        }
        if (count($clearSettingsIdArray) > 0) {
            $em->getRepository('CommonUtilityBundle:FgWebSettings')->clearDomainFiles($clearSettingsIdArray);
        }
        $output->writeln('#########' . count($clearSettingsIdArray) . ' files cleared ####');
    }
}
