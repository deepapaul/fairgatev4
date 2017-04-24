<?php

/**
 * Terminology
 *
 * Get all terminology terms for a club
 *
 * @package    Fairgate
 * @subpackage Listener
 * @author     PIT Solutions <pitsolutions.ch>
 * @version    Fairgate V4
 */

namespace Clubadmin\TerminologyBundle\Service;

use Symfony\Component\HttpKernel\Event\GetResponseEvent;
use Common\UtilityBundle\Repository\Pdo\ClubPdo;

class Terminology {

    private $clubObj;
    private $clubId;
    private $defaultLang;
    private $federationId;
    private $singular;
    private $plural;
    private $clubType;
    private $clubDefaultSystemLang;
    private $clubCacheKey;
    private $cacheLifeTime;
    public $translationTerminology = array();
    private $terminologyTerms = array();
    private $container;

    /**
     * Constructor
     *
     * @param Object $container Container Object
     *
     */
    public function __construct($container) {
        $this->clubObj = $container->get('club');

        $this->container = $container;
    }

    /**
     * Trigger service on onKernelRequest
     *
     * @param GetResponseEvent $event
     */
    public function onKernelRequest(GetResponseEvent $event) {
        $this->setDefault($this->clubObj);
    }
    /**
     * Function to set default club values and terminology terms
     *
     * @param type $clubObj
     */
    public function setDefault($clubObj){
        $this->clubId = $clubObj->get("id");
        $this->defaultLang = $clubObj->get("default_lang");
        $this->federationId = $clubObj->get("federation_id");
        $this->singular = $clubObj->get("singular");
        $this->plural = $clubObj->get("plural");
        $this->clubType = $clubObj->get('type');
        $this->clubDefaultSystemLang = $clubObj->get("default_system_lang");
        $this->clubCacheKey = $clubObj->get('clubCacheKey');
        $this->cacheLifeTime = $clubObj->get('cacheLifeTime');
        $this->setClubTerminologyTerms($this->clubId, $this->defaultLang, $this->federationId);
    }

    /**
     * Get terminology value for a given term
     *
     * @param string $term Terminology term
     * @param string $type Type singular/plural
     * @param int $termClubId Clubid to get terminology value
     * @return string Value of terminology term
     */
    public function getTerminology($term, $type, $termClubId = '') {
        $defaultLang = $this->defaultLang;
        $clubType = $this->clubType;
        if ($termClubId == '') {
            $clubId = $this->clubId;
        } else {
            $clubId = $termClubId;
            if ($clubType == 'federation') {
                $federationId = $clubId;
            }
        }
        if (!isset($this->terminologyTerms[$clubId])) {
            $this->setClubTerminologyTerms($clubId, $defaultLang, $federationId);
        }
        return (isset($this->terminologyTerms[$clubId][$term . '.' . $type])) ? $this->terminologyTerms[$clubId][$term . '.' . $type] : '';
    }

    /**
     * Set all terminology terms for a club to use in translation file and other areas
     *
     * @param int $clubId Club Id
     * @param int $defaultLang Default language of club
     * @param int $federationId Federation Id
     *
     * return void
     */
    public function setClubTerminologyTerms($clubId, $defaultLang, $federationId) {
        $translationTerminologyString = $terminologyTermsString = '';
        $domainCacheKey = $this->clubObj->get('clubCacheKey');
        $translationTerminologyCacheKey = str_replace('{{cache_area}}', 'translationTerminology', $domainCacheKey) . '_' . $defaultLang . '_' . $this->clubDefaultSystemLang;
        $terminologyTermsCacheKey = str_replace('{{cache_area}}', 'terminologyTerms' , $domainCacheKey) . '_' . $defaultLang . '_' . $this->clubDefaultSystemLang;
        $federationId = $this->clubType == 'federation' ? $clubId : $federationId;
        //Check whether cached data available.
        if (($translationTerminologyString = $this->container->get('fg.cache')->fetch($translationTerminologyCacheKey)) && ($terminologyTermsString = $this->container->get('fg.cache')->fetch($terminologyTermsCacheKey))) {
            $this->translationTerminology = unserialize($translationTerminologyString);
            $this->terminologyTerms = unserialize($terminologyTermsString);
        } else {
            $format = '{#%s#}';
            $clubPdo = new ClubPdo($this->container);
            $resultTerms = $clubPdo->getTerminologyDetails($clubId, $defaultLang, $federationId, $this->clubDefaultSystemLang, $this->clubCacheKey, $this->cacheLifeTime);
            $translationTerminology = $terminologyTerms = array();
            foreach ($resultTerms as $resultTerm) {
                $translationTerminology[sprintf($format, $resultTerm['defaultSingularTerm'])] = $resultTerm['singular'];
                $translationTerminology[sprintf($format, $resultTerm['defaultSingularTerm'] . '.s')] = ($resultTerm['singular']);
                $translationTerminology[sprintf($format, $resultTerm['defaultSingularTerm'] . '.sf')] = ucfirst(strtolower($resultTerm['singular']));
                $translationTerminology[sprintf($format, $resultTerm['defaultSingularTerm'] . '.su')] = strtoupper($resultTerm['singular']);
                $translationTerminology[sprintf($format, $resultTerm['defaultSingularTerm'] . '.sl')] = strtolower($resultTerm['singular']);
                $terminologyTerms[$resultTerm['defaultSingularTerm'] . '.s'] = $resultTerm['singular'];
                if ($resultTerm['defaultPluralTerm'] != '') {
                    $translationTerminology[sprintf($format, $resultTerm['defaultPluralTerm'])] = $resultTerm['plural'];
                    $translationTerminology[sprintf($format, $resultTerm['defaultSingularTerm'] . '.p')] = ($resultTerm['plural']);
                    $translationTerminology[sprintf($format, $resultTerm['defaultSingularTerm'] . '.pf')] = ucfirst(strtolower($resultTerm['plural']));
                    $translationTerminology[sprintf($format, $resultTerm['defaultSingularTerm'] . '.pu')] = strtoupper($resultTerm['plural']);
                    $translationTerminology[sprintf($format, $resultTerm['defaultSingularTerm'] . '.pl')] = strtolower($resultTerm['plural']);
                    $terminologyTerms[$resultTerm['defaultSingularTerm'] . '.p'] = $resultTerm['plural'];
                }
            }
            $this->translationTerminology = $translationTerminology;
            $this->terminologyTerms[$clubId] = $terminologyTerms;
            $this->container->get('fg.cache')->save($translationTerminologyCacheKey, serialize($this->translationTerminology));
            $this->container->get('fg.cache')->save($terminologyTermsCacheKey, serialize($this->terminologyTerms));
        }
    }
}
