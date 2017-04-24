<?php

namespace Common\UtilityBundle\Extensions;

use Symfony\Component\HttpKernel\Event\GetResponseEvent;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\Locale\Locale;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\HttpKernel;
use \Twig_Filter_Function;
use \Twig_Filter_Method;

/**
 * FgTwig used for Twig extensions
 *
 * @author pit solutions <pitsolutions.ch>
 */
class FgTwig extends \Twig_Extension
{

    public $request;
    protected $fgTerminologyService;

    /**
     * This function is a constructor
     * @param Object $fgTerminologyService fgTerminologyService
     *
     * @return HTML
     */
    public function __construct($fgTerminologyService)
    {
        $this->fgTerminologyService = $fgTerminologyService;
    }

    /**
     * This function is used to render the default template
     * @param Object $event GetResponseEvent
     *
     * @return HTML
     */
    public function onKernelRequest(GetResponseEvent $event)
    {
        if ($event->getRequestType() === HttpKernel::MASTER_REQUEST) {
            $this->request = $event->getRequest();
        }
    }

    /**
     * This function is used to get the controller name
     *
     * @return String
     */
    public function getControllerName()
    {
        $controllerName = $this->request->get('_controller');
        if (!empty($controllerName)) {
            $pattern = "#Controller\\\([a-zA-Z]*)Controller#";
            if (!preg_match($pattern, $this->request->get('_controller'), $matches)) {

                return false;
            }
            $controllerName = $matches[1];
        }

        return strtolower($controllerName);
    }

    /**
    * This function is used to get the action name
    *
    * @return String
    */
    public function getActionName()
    {
        $actionName = $this->request->get('_route');

        return strtolower($actionName);
    }

    /**
     * This function is used to get the terminology term
     * @param int $term     Term
     * @param int $termType Term type
     * @param int $caseType Case
     *
     * @return tring
     */
    public function getTerminolgyName($term, $termType, $caseType = 'N')
    {
        $fgTerminologyService = $this->fgTerminologyService;
        $fgTerminologyTerm = $fgTerminologyService->getTerminology($term, $termType);
        if ($caseType != 'N') {
            $fgTerminologyTerm = $this->caseChange($fgTerminologyTerm, $caseType);
        }

        return $fgTerminologyTerm;
    }

    /**
     * This function is used to change name
     * @param int $term Term
     * @param int $type Type
     *
     * @return String
     */
    public function caseChange($term, $type)
    {
        switch ($type) {
            case 'UCW':
                $term = ucwords($term);
                break;
            case 'UCF':
                $term = ucfirst($term);
                break;
            case 'C':
                $term = strtoupper($term);
                break;
        }

        return $term;
    }
    /**
     * This function is used to check file exists
     * @param int $file File name
     *
     * @return file
    */
    public function fileExists($file)
    {
        return file_exists($file);
    }
    /**
     * This function is used to check in array
     *
     * @param string $needle
     * @param array  $haystack
     *
     * @return file
    */
    public function inArray($needle, $haystack)
    {
        return in_array($needle, $haystack);
    }
    /**
    * This function is used to get name
    *
    * @return name
    */
    public function getName()
    {
        return 'fairgate';
    }
    /**
     * This function to decode json data into array
     *
     * @return array
     */
    public function jsonDecode($json)
    {
        return json_decode($json);
    }
    
    /**
     * Method to return color string with required opacity (always consider current opacity is 1)
     * 
     * @param string $cssColor          in format rgba(a, b, c, d)
     * @param int    $opacityPercentage opacity percenteage
     * 
     * @return string color string
     */
    public function calculateOpacity($cssColor, $opacityPercentage = 100) {
        preg_match('#\((.*?)\)#', $cssColor, $match);
        $attributes = explode(',', $match[1]);
        $opacity = ($opacityPercentage/100);       
        
        return ($cssColor) ? "rgba({$attributes[0]}, {$attributes[1]}, {$attributes[2]}, $opacity)" : '';
    }
    
    /**
     * Method to return color string with required opacity (always consider current opacity of color (4th parameter - d))
     * 
     * @param string $cssColor          in format rgba(a, b, c, d)
     * @param int    $opacityPercentage opacity percenteage
     * 
     * @return string color string
     */
    public function changeOpacity($cssColor, $opacityPercentage = 100) {
        preg_match('#\((.*?)\)#', $cssColor, $match);
        $attributes = explode(',', $match[1]);
        $opacity = ($opacityPercentage/100) * $attributes[3];       
            
        return ($cssColor) ? "rgba({$attributes[0]}, {$attributes[1]}, {$attributes[2]}, $opacity)" : '';
    }
    
    /**
     * This function to format date(replace first space with ',')
     *
     * @param string $dateString 
     * 
     * @return formatted date
     */
    public function formatDate($dateString)
    {
        return preg_replace('/ /', ', ', $dateString, 1);
    }
    
}

