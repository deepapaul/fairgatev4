<?php

/**
 * ResizeCommand
 *
 * This command is calculate the dimensions to which the image should be resized
 *
 * @package    GeneralBundle
 * @subpackage Command
 * @author     pitsolutions.ch
 * @version    Fairgate V4
 */
namespace Internal\GalleryBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputOption ;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Common\UtilityBundle\Util\FgUtility;


/**
 * This command is used to spool scheduled newsletter contacts
 *
 * @author pitsolutions.ch
 */
class ResizeCommand extends ContainerAwareCommand
{
    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this
            ->setName('gallery:resize')
            ->setDescription('Get the dimensions to which the image needed to be resized')
            ->addOption('file', null, InputOption::VALUE_REQUIRED)
            ->addOption('maxHeight', null, InputOption::VALUE_REQUIRED)
            ->addOption('maxWidth', null, InputOption::VALUE_REQUIRED);
    }

    
    /**
     * {@inheritdoc}
     *
     * @throws \InvalidArgumentException When the target directory does not exist or symlink cannot be used
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $file = $input->getOption('file');
        $maxHeight = $input->getOption('maxHeight');
        $maxWidth = $input->getOption('maxWidth');
        
        try{
            $newDimensions = FgUtility::getResizeDimension($file, $maxWidth, $maxHeight);
        } catch (Exception $e){
           $newDimensions  = array(0,0); 
        }
        $output->writeln(implode('x', $newDimensions));
    }
}

