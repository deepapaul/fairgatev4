<?php

/**
 * Manage file changes on each theme
 */
namespace Website\CMSBundle\Util;

/**
 * Manage file changes on each theme
 *
 * @author     	pitsolutions.ch
 */
class FgTheme
{
    /**
     * Constructor of FgCmsThemeContainerDetails class.
     *
     * @param ContainerInterface $container
     */
    public function __construct($container)
    {
        $this->container = $container;
        $this->club = $this->container->get('club');
        $currentThemeId = $this->club->get('publicConfig')['theme'];
        $this->themeChangedFiles = $this->container->getParameter('themeFiles')['themeChangedFiles']['theme'.$currentThemeId];
    }
    
    /**
     * Method to render view page
     * 
     * @param string $pageType    (articleSpecialPage)
     */
    public function getViewPage($pageType) {
        if(in_array($pageType, array_keys($this->themeChangedFiles))) {
            $returnView = $this->themeChangedFiles["$pageType"];   
        } else {
            //default pages in theme01
            $this->themeDefaultFiles = $this->container->getParameter('themeFiles')['themeChangedFiles']['theme1'];
            
            $returnView = $this->themeDefaultFiles["$pageType"];
        }
        
        return  $returnView; 
    }
}
