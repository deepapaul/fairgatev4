<?php

/**
 * SyncAdminDBCommand
 *
 * This command is used to update the data in the admin DB
 *
 * @package    CommonUtilityBundle
 * @subpackage Command
 * @author     pitsolutions.ch
 * @version    Fairgate V4
 */

namespace Common\UtilityBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Output\OutputInterface;
use Common\UtilityBundle\Util\FgClubSyncDataToAdmin;

/**
 * This command is used to update filter contacts
 *
 * @author pitsolutions.ch
 */
class SyncClubDataCommand extends ContainerAwareCommand {

    /**
     * {@inheritdoc}
     */
    protected function configure() {
        $this
                ->setName('syncclubdata:common')
                ->addArgument('arguments', InputArgument::IS_ARRAY, array())
                ->setDescription('The common command to update the admin data');
    }

    /**
     * {@inheritdoc}
     *
     * @throws \InvalidArgumentException When the target directory does not exist or symlink cannot be used
     */
    protected function execute(InputInterface $input, OutputInterface $output) {
        $container = $this->getContainer();
        $inputParameters = $input->getArgument('arguments');
        $functionName = $inputParameters[0];
        $functionParameters = array_slice($inputParameters, 1);
        $syncObject = new FgClubSyncDataToAdmin($container);
        if(method_exists ( $syncObject , $functionName )){
            call_user_func_array(array($syncObject, $functionName), $functionParameters);
        }
        
    }

}
