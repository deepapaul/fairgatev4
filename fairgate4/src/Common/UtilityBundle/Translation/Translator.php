<?php
/**
 * Integrate terminology terms in translation
 *
 * Get all terminology terms for a club
 *
 * @package    Fairgate
 * @subpackage Listener
 * @author     PIT Solutions <pitsolutions.ch>
 * @version    Fairgate V4
 */
namespace Common\UtilityBundle\Translation;

use Symfony\Bundle\FrameworkBundle\Translation\Translator as BaseTranslator;

class Translator extends BaseTranslator {
    /**
     * {@inheritdoc}
     *
     * @api
     */
    public function trans( $id, array $parameters = array(), $domain = null, $locale = null )
    {
        $terminology = $this->container->get('fairgate_terminology_service');
        $transValue = parent::trans( $id, $parameters, $domain, $locale );
        if(preg_match_all('/\{\#([a-zA-Z\.\ \-\_]+)\#\}/i', $transValue, $matches, PREG_PATTERN_ORDER)==0){
            return $transValue;
        }else{
            return str_replace(array_keys($terminology->translationTerminology),  array_values($terminology->translationTerminology),$transValue);
        }

    }
}