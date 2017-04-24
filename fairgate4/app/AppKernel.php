<?php

use Symfony\Component\HttpKernel\Kernel;
use Symfony\Component\Config\Loader\LoaderInterface;

class AppKernel extends Kernel
{
    public function registerBundles()
    {
        $bundles = array(
            new Symfony\Bundle\FrameworkBundle\FrameworkBundle(),
            new Symfony\Bundle\SecurityBundle\SecurityBundle(),
            new Symfony\Bundle\TwigBundle\TwigBundle(),
            new Symfony\Bundle\MonologBundle\MonologBundle(),
            new Symfony\Bundle\SwiftmailerBundle\SwiftmailerBundle(),
            new Symfony\Bundle\AsseticBundle\AsseticBundle(),
            new Doctrine\Bundle\DoctrineBundle\DoctrineBundle(),
            new Sensio\Bundle\FrameworkExtraBundle\SensioFrameworkExtraBundle(),
            new Clubadmin\ContactBundle\ClubadminContactBundle(),
            new Common\UtilityBundle\CommonUtilityBundle(),
            new FOS\UserBundle\FOSUserBundle(),
            new Clubadmin\TerminologyBundle\TerminologyBundle(),
            new Clubadmin\NotesBundle\NotesBundle(),
            new Clubadmin\ClubBundle\ClubadminClubBundle(),
            new Clubadmin\CommunicationBundle\ClubadminCommunicationBundle(),
            new Clubadmin\DocumentsBundle\ClubadminDocumentsBundle(),
            new Clubadmin\SponsorBundle\ClubadminSponsorBundle(),
            new Knp\Bundle\SnappyBundle\KnpSnappyBundle(),
            new Internal\ProfileBundle\InternalProfileBundle(),
            new Internal\UserBundle\InternalUserBundle(),
            new Clubadmin\GeneralBundle\ClubadminGeneralBundle(),
            new Internal\GeneralBundle\InternalGeneralBundle(),
            new Internal\MessageBundle\InternalMessageBundle(),
            new Internal\TeamBundle\InternalTeamBundle(),
            new Internal\CalendarBundle\InternalCalendarBundle(),
            new Internal\GalleryBundle\InternalGalleryBundle(),
            new Common\FileServeBundle\CommonFileServeBundle(),
            new Common\FilemanagerBundle\CommonFilemanagerBundle(),
            new Common\HelpBundle\CommonHelpBundle(),
            new Internal\ArticleBundle\InternalArticleBundle(),
            new Website\CMSBundle\WebsiteCMSBundle(),
            new JMS\SerializerBundle\JMSSerializerBundle(),
            new FOS\RestBundle\FOSRestBundle(),
            new Nelmio\ApiDocBundle\NelmioApiDocBundle(),
            new FairgateApiBundle\FairgateApiBundle(),
            new Admin\UtilityBundle\AdminUtilityBundle(),
        );

        if (in_array($this->getEnvironment(), array('dev', 'test'))) {
            $bundles[] = new Symfony\Bundle\WebProfilerBundle\WebProfilerBundle();
            $bundles[] = new Sensio\Bundle\DistributionBundle\SensioDistributionBundle();
            $bundles[] = new Sensio\Bundle\GeneratorBundle\SensioGeneratorBundle();
        }

        return $bundles;
    }
    public function getRootDir()
    {
        return __DIR__;
    }

    public function getCacheDir()
    {
        return dirname(__DIR__).'/var/cache/'.$this->getEnvironment();
    }

    public function getLogDir()
    {
        return dirname(__DIR__).'/var/logs';
    }

    public function registerContainerConfiguration(LoaderInterface $loader)
    {
            $loader->load(__DIR__.'/config/config_'.$this->getEnvironment().'.yml');
    }
}
