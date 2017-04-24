<?php

/**
 * FgCmsArticleContainer
 */
namespace Website\CMSBundle\Util;

/**
 * FgCmsArticleContainer - The wrapper class to handle change in article element container, and articles per row settings 
 * on page container resize 
 *
 * @package         Website
 * @subpackage      CMS
 * @author          pitsolutions.ch
 * @version         Fairgate V4
 */
class FgCmsArticleContainer
{

    /**
     * The constructor function
     *
     * @param object $container container:\Symfony\Component\DependencyInjection\ContainerInterface
     */
    public function __construct($container)
    {
        $this->container = $container;
        $this->club = $this->container->get('club');
        $this->contact = $this->container->get('contact');
        $this->em = $this->container->get('doctrine')->getManager();
    }

    /**
     * This function is used to resize the article element on container update
     * 
     * @param int $containerId The container id
     */
    public function adjustArticleElementOnContainerResize($containerId)
    {
        $articleDetails = $this->em->getRepository('CommonUtilityBundle:FgCmsPageContainerColumn')->getArticleElementsInAContainer($this->club->get('id'), $containerId);
        foreach ($articleDetails as $articleDetail) {
            $pageContainerSize = $articleDetail['pageContainerSize'];
            $articlesPerRow = $articleDetail['articlesPerRow'];
            $optimalArticleContainerSize = intdiv($pageContainerSize, $articlesPerRow);
            $maxArticleContainerSize = ($optimalArticleContainerSize > 0) ? $optimalArticleContainerSize : 1;

            if ($articleDetail['articlesPerRow'] !== 1 && ($articlesPerRow > $pageContainerSize )) {
                $newArticlesPerRow = $this->getArticlesPerRowForModifiedContainer($pageContainerSize, $maxArticleContainerSize, $articlesPerRow);
                $this->updateArticlesPerRow($articleDetail['elementId'], $newArticlesPerRow);
            }
        }
    }

    /**
     * This function is used to get the article per row for the modified container
     * 
     * @param int $pageContainerSize        The page container size
     * @param int $maxArticleContainerSize The maximum article container size
     * @param int $articlesPerRow          The articles per row
     * 
     * @return int $articlesPerRow The final articles per row settings
     */
    private function getArticlesPerRowForModifiedContainer($pageContainerSize, $maxArticleContainerSize, $articlesPerRow)
    {
        do {
            $articlesPerRow--;
            $newArticleContainerSize = intdiv($pageContainerSize, $articlesPerRow);
        } while (($newArticleContainerSize > $maxArticleContainerSize) && ($articlesPerRow > 1));

        return $articlesPerRow;
    }
    /* This function is used to update the articles per row 
     * 
     * @param int $elementId         The article element id
     * @param int $newArticlesPerRow The new articles per row
     */

    private function updateArticlesPerRow($elementId, $newArticlesPerRow)
    {
        $this->em->getRepository('CommonUtilityBundle:FgCmsPageContentElement')->updateArticlesPerRow($elementId, $newArticlesPerRow);
    }
    /**
     * This function is used to adjust article element on sidebar include and exclude action
     * 
     * @param array $containerIdsArr The container array
     */
    public function adjustArticleElementOnSidebarIncludeExclude($containerIdsArr)
    {
        foreach ($containerIdsArr as $containerId) {
            $this->adjustArticleElementOnContainerResize($containerId);
        }
    }
}
