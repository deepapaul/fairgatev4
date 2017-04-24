<?php

/**
 *
 * (c) pit solutions <pitsolutions.ch>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * //http://symfony.com/doc/current/reference/forms/types/date.html#format
 */

namespace Common\UtilityBundle\Util;

use Symfony\Component\Intl\Intl;

class FgSettings {

    public static $SYSTEM_LANGUAGE;
    public static $CORRESPONDENCE_LANGUAGE;

    public static $PHP_DATE_FORMAT;
    public static $SYMFONYFORM_DATE_FORMAT;
    public static $JQUERY_DATE_FORMAT;
    public static $MOMENT_DATE_FORMAT;
    public static $MYSQL_DATE_FORMAT;
    public static $DEFAULT_PHP_DATE_FORMAT;

    public static $PHP_TIME_FORMAT;
    public static $SYMFONYFORM_TIME_FORMAT;
    public static $JQUERY_TIME_FORMAT;
    public static $MOMENT_TIME_FORMAT;
    public static $MYSQL_TIME_FORMAT;

    public static $PHP_DATETIME_FORMAT;
    public static $SYMFONYFORM_DATETIME_FORMAT;
    public static $JQUERY_DATETIME_FORMAT;
    public static $MOMENT_DATETIME_FORMAT;
    public static $MYSQL_DATETIME_FORMAT;

    public static $THOUSAND_SEPERATOR;
    public static $DECIMAL_MARKER;

    public static $LOCALE_DETAILS ;


    /**
     *
     * The function to set the Settings Locale in the application
     */
    public static function setLocaleSettings($settings) {

        //Query data
        self::setLocaleDetails();
        self::setCorrespondenceLanguage($settings['correspondance_lang']);
        self::setSystemLanguage($settings['system_lang']);
        self::setDateFormat($settings['date_format']);
        self::setTimeFormat($settings['time_format']);
        self::setDateTimeFormat();
        self::setThousandSeperator($settings['thousand_separator'],$settings['correspondance_lang']);
        self::setDecimalMarker($settings['decimal_marker'],$settings['correspondance_lang']);
    }

    /**
     *
     * The function to set the system language for the user
     *
     * @param String $myCorrespondenceLanguage   My correspondence language from the database
     */
    public static function setCorrespondenceLanguage($myCorrespondenceLanguage) {
        self::$CORRESPONDENCE_LANGUAGE = $myCorrespondenceLanguage;
    }

    /**
     *
     * The function to set the system language for the user
     *
     * @param String $mySystemLanguage   My system language from the database
     */
    public static function setSystemLanguage($mySystemLanguage) {
        self::$SYSTEM_LANGUAGE = $mySystemLanguage;
    }

    /**
     * The function to set my date format
     *
     * @param String $format   the format
     *
     */
    public static function setDateFormat($format) {
        switch ($format){
            //23.08.2005  dd.mm.YY
            case 'dd.mm.YY':
                $phpDateFormat = 'd.m.Y';
                $symfonyFormDateFormat = 'dd.MM.y';
                $momentDateFormat = 'DD.MM.YYYY';
                $jqueryDateFormat = 'dd.mm.yyyy';
                $mysqlDateFormat = '%d.%m.%Y';
                break;

            //23.8.2005  dd.m.YY
            case 'dd.m.YY':
                $phpDateFormat = 'j.n.Y';
                $symfonyFormDateFormat = 'd.M.y';
                $momentDateFormat = 'D.M.YYYY';
                $jqueryDateFormat = 'd.m.yyyy';
                $mysqlDateFormat = '%e.%c.%Y';
                break;

            //2005.08.23  YY.mm.dd
            case 'YY.mm.dd':
                $phpDateFormat = 'Y.m.d';
                $symfonyFormDateFormat = 'y.MM.dd';
                $momentDateFormat = 'YYYY.MM.DD';
                $jqueryDateFormat = 'yyyy.mm.dd';
                $mysqlDateFormat = '%Y.%m.%d';
                break;

            //23/08/2005  dd/mm/YY
             case 'dd/mm/YY':
                $phpDateFormat = 'd/m/Y';
                $symfonyFormDateFormat = 'dd/MM/y';
                $momentDateFormat = 'DD/MM/YYYY';
                $jqueryDateFormat = 'dd/mm/yyyy';
                $mysqlDateFormat = '%d/%m/%Y';
                break;

            //23/8/2005  dd/mm/YY
             case 'dd/m/YY':
                $phpDateFormat = 'j/n/Y';
                $symfonyFormDateFormat = 'd/M/y';
                $momentDateFormat = 'D/M/YYYY';
                $jqueryDateFormat = 'd/m/yyyy';
                $mysqlDateFormat = '%e/%c/%Y';
                break;

            //2005/08/23  YY/mm/dd
             case 'YY/mm/dd':
                $phpDateFormat = 'Y/m/d';
                $symfonyFormDateFormat = 'y/MM/dd';
                $momentDateFormat = 'YYYY/MM/DD';
                $jqueryDateFormat = 'yyyy/mm/dd';
                $mysqlDateFormat = '%Y/%m/%d';
                break;

            //2005/8/23  YY/m/dd
             case 'YY/m/dd':
                $phpDateFormat = 'Y/n/j';
                $symfonyFormDateFormat = 'y/M/d';
                $momentDateFormat = 'YYYY/M/D';
                $jqueryDateFormat = 'yyyy/m/d';
                $mysqlDateFormat = '%Y/%c/%e';
                break;

            //8/23/2005  m/dd/YY
            case 'm/dd/YY':
                $phpDateFormat = 'n/j/Y';
                $symfonyFormDateFormat = 'M/d/y';
                $momentDateFormat = 'M/D/YYYY';
                $jqueryDateFormat = 'm/d/yyyy';
                $mysqlDateFormat = '%c/%e/%Y';
                break;

            //23-08-2005  dd-mm-YY
            case 'dd-mm-YY':
                $phpDateFormat = 'd-m-Y';
                $symfonyFormDateFormat = 'd-MM-y';
                $momentDateFormat = 'DD-MM-YYYY';
                $jqueryDateFormat = 'dd-mm-yyyy';
                $mysqlDateFormat = '%d-%m-%Y';
                break;

            //23-8-2005  dd-m-YY
            case 'dd-m-YY':
                $phpDateFormat = 'j-n-Y';
                $symfonyFormDateFormat = 'd-M-y';
                $momentDateFormat = 'D-M-YYYY';
                $jqueryDateFormat = 'd-m-yyyy';
                $mysqlDateFormat = '%e-%c-%Y';
                break;

            //23-08-05  dd-mm-Y
            case 'dd-mm-Y':
                $phpDateFormat = 'd-m-y';
                $symfonyFormDateFormat = 'd-MM-yy';
                $momentDateFormat = 'DD-MM-YY';
                $jqueryDateFormat = 'dd-mm-yy';
                $mysqlDateFormat = '%d-%m-%y';
                break;

            //2005-08-23  YY-mm-dd
            case 'YY-mm-dd':
                $phpDateFormat = 'Y-m-d';
                $symfonyFormDateFormat = 'y-MM-d';
                $momentDateFormat = 'YYYY-MM-DD';
                $jqueryDateFormat = 'yyyy-mm-dd';
                $mysqlDateFormat = '%Y-%m-%d';
                break;

            default:
                $phpDateFormat = 'd/m/Y';
                $symfonyFormDateFormat = 'd/M/y';
                $momentDateFormat = 'DD/MM/YYYY';
                $jqueryDateFormat = 'd/m/yyyy';
                $mysqlDateFormat = '%d/%m/%Y';
                break;
        }

        self::$PHP_DATE_FORMAT = $phpDateFormat;
        self::$SYMFONYFORM_DATE_FORMAT = $symfonyFormDateFormat;
        self::$JQUERY_DATE_FORMAT = $jqueryDateFormat;
        self::$MOMENT_DATE_FORMAT = $momentDateFormat;
        self::$MYSQL_DATE_FORMAT = $mysqlDateFormat;
        self::$MYSQL_DATE_FORMAT = $mysqlDateFormat;
        
    }

    /**
     * The function to set my time format
     *
     * @param String $format   the format
     *
     */
    public static function setTimeFormat($format) {        
        switch ($format){
            case 'H:i':
                $phpTimeFormat = 'H:i';
                $jqueryTimeFormat = 'hh:ii';
                $momentTimeFormat = 'HH:mm';
                $mysqlTimeFormat = '%H:%i';
                break;

            case 'H.i':
                $phpTimeFormat = 'H.i';
                $jqueryTimeFormat = 'hh.ii';
                $momentTimeFormat = 'HH.mm';
                $mysqlTimeFormat = '%H.%i';
                break;

            case 'H[h] i':
                $phpTimeFormat = 'H \h i';
                $jqueryTimeFormat = "hh ## ii";
                $momentTimeFormat = 'HH [h] mm';
                $mysqlTimeFormat = '%H h %i';
                break;

            case 'h:i':
                $phpTimeFormat = 'h:i A';
                $jqueryTimeFormat = 'HH:ii P';
                $momentTimeFormat = 'hh:mm A';
                $mysqlTimeFormat = '%h:%i %p';
                break;

            default:
                $phpTimeFormat = 'H:i';
                $jqueryTimeFormat = 'hh:ii';
                $momentTimeFormat = 'HH:mm';
                $mysqlTimeFormat = '%H:%i';
                break;
        }
        self::$PHP_TIME_FORMAT = $phpTimeFormat;
        self::$JQUERY_TIME_FORMAT = $jqueryTimeFormat;
        self::$MOMENT_TIME_FORMAT = $momentTimeFormat;
        self::$MYSQL_TIME_FORMAT = $mysqlTimeFormat;
    }

    /**
     * The function to set my date-time format
     *
     *
     */
    public static function setDateTimeFormat() {

        self::$PHP_DATETIME_FORMAT = self::$PHP_DATE_FORMAT.' '.self::$PHP_TIME_FORMAT;
        self::$JQUERY_DATETIME_FORMAT = self::$JQUERY_DATE_FORMAT.' '.self::$JQUERY_TIME_FORMAT;
        self::$MOMENT_DATETIME_FORMAT = self::$MOMENT_DATE_FORMAT.' '.self::$MOMENT_TIME_FORMAT;
        self::$MYSQL_DATETIME_FORMAT = self::$MYSQL_DATE_FORMAT.' '.self::$MYSQL_TIME_FORMAT;
    }

    /**
     * The function to set my thousand seperator
     *
     *
     */
    public static function setThousandSeperator($seperator = ',', $locale = 'de') {
         switch ($seperator){
            case 'default':
                $localeDetails = self::$LOCALE_DETAILS;
                self::$THOUSAND_SEPERATOR = $localeDetails[$locale][2];
                break;
            case 'space':
                if (version_compare(PHP_VERSION, '5.4.0') == -1) {
                    self::$THOUSAND_SEPERATOR = '`';
                } else {
                    self::$THOUSAND_SEPERATOR = '&#8239;';
                }
                break;
            case 'apostrophe':
                if (version_compare(PHP_VERSION, '5.4.0') == -1) {
                    self::$THOUSAND_SEPERATOR = '`';
                } else {
                    self::$THOUSAND_SEPERATOR = '&#8217;';
                }
                break;
            case 'dot':
                self::$THOUSAND_SEPERATOR = '.';
                break;
            case 'comma':
                self::$THOUSAND_SEPERATOR = ',';
                break;
            case 'none':
                self::$THOUSAND_SEPERATOR = '';
                break;
            default:
                self::$THOUSAND_SEPERATOR = ',';
                break;
        }
    }

    /**
     * The function to set my decimal marker
     *
     *
     */
    public static function setDecimalMarker($decimalMarker = '.', $locale = 'de') {
        switch ($decimalMarker){
            case 'default':
                $localeDetails = self::$LOCALE_DETAILS;
                self::$DECIMAL_MARKER = $localeDetails[$locale][3];
                break;
            case 'dot':
                self::$DECIMAL_MARKER = '.';
                break;
            case 'comma':
                self::$DECIMAL_MARKER = ',';
                break;
            default:
                self::$DECIMAL_MARKER = '.';
                break;
        }
    }

    /**
     * The function to set all locale details
     * 0 => Locale
     * 1 => Locale Name
     * 2 => Thousand Seperator
     * 3 => Decimal marker
     *
     */
    public static function setLocaleDetails() {
        self::$LOCALE_DETAILS = array(
            'sq' => array('sq_AL','Albanian','.',','),
            'bs' => array('sr_BA','Bosnian',',','.'),
            'bg' => array('bg_BG','Bulgarian','',','),
            'hr' => array('hr_HR','Croatian','',','),
            'cs' => array('cs_CZ','Czech',' ',','),
            'da' => array('da_DK','Danish','.',','),
            'nl' => array('nl_NL','Dutch','',','),
            'en' => array('en_GB','English',',','.'),
            'fr' => array('fr_FR','French','',','),
            'de' => array('de_AT','German','',','),
            'el' => array('el_GR','Greek','',','),
            'it' => array('it_IT','Italian','',','),
            'mk' => array('mk_MK','Macedonian',' ',','),
            'no' => array('no_NO','Norwegian','.',','),
            'pl' => array('pl_PL','Polish','',','),
            'pt' => array('pt_PT','Portuguese','',','),
            'rm' => array('rm_CH','Romansh',',','.'),
            'sr' => array('sr_RS','Serbian','','.'),
            'sk' => array('sk_SK','Slovak',' ',','),
            'sl' => array('sl_SI','Slovenian','',','),
            'es' => array('es_ES','Spanish','',','),
            'sv' => array('sv_SE','Swedish',' ',','),
            'tr' => array('tr_TR','Turkish','.',','),
            );
    }
    /**
     * The function to get all locale details
     * 0 => Locale, 1 => Locale Name, 2 => Thousand Seperator, 3 => Decimal marker
     * @return array 
     */
    public static function getLocaleDetails() {
        return self::$LOCALE_DETAILS;
    }

    /**
     *
     * The function to get my correspondence language
     *
     * @return String $CORRESPONDENCE_LANGUAGE
     */
    public static function getCorrespondenceLanguage() {
        return self::$CORRESPONDENCE_LANGUAGE;
    }

    /**
     *
     * The function to get my system language
     *
     * @return String $SYSTEM_LANGUAGE
     */
    public static function getSystemLanguage() {
        return self::$SYSTEM_LANGUAGE;
    }

    /**
     *
     * The function to get my php date format
     *
     * @return String $PHP_DATE_FORMAT
     */
    public static function getPhpDateFormat() {
        return self::$PHP_DATE_FORMAT;
    }


    /**
     *
     * The function to get my php date format
     *
     * @return String $SYMFONYFORM_DATE_FORMAT
     */
    public static function getSymfonyDateFormat() {
        return self::$SYMFONYFORM_DATE_FORMAT;
    }


    /**
     *
     * The function to get my moment date format
     *
     * @return String $MOMENT_DATE_FORMAT
     */
    public static function getMomentDateFormat() {
        return self::$MOMENT_DATE_FORMAT;
    }

    /**
     *
     * The function to get my jquery date format
     *
     * @return String $JQUERY_DATE_FORMAT
     */
    public static function getJqueryDateFormat() {
        return self::$JQUERY_DATE_FORMAT;
    }

    /**
     *
     * The function to get my mysql date format
     *
     * @return String $MYSQL_DATE_FORMAT
     */
    public static function getMysqlDateFormat() {
        return self::$MYSQL_DATE_FORMAT;
    }

    /**
     *
     * The function to get my php datetime format
     *
     * @return String $PHP_DATETIME_FORMAT
     */
    public static function getPhpDateTimeFormat() {
        return self::$PHP_DATETIME_FORMAT;
    }

    /**
     *
     * The function to get my jquery datetime format
     *
     * @return String $JQUERY_DATETIME_FORMAT
     */
    public static function getJqueryDateTimeFormat() {
        return self::$JQUERY_DATETIME_FORMAT;
    }

    /**
     *
     * The function to get my moment datetime format
     *
     * @return String $MOMENT_DATETIME_FORMAT
     */
    public static function getMomentDateTimeFormat() {
        return self::$MOMENT_DATETIME_FORMAT;
    }

    /**
     *
     * The function to get my mysql datetime format
     *
     * @return String $MYSQL_DATETIME_FORMAT
     */
    public static function getMysqlDateTimeFormat() {
        return self::$MYSQL_DATETIME_FORMAT;
    }

    /**
     *
     * The function to get my php time format
     *
     * @return String $PHP_TIME_FORMAT
     */
    public static function getPhpTimeFormat() {
        return self::$PHP_TIME_FORMAT;
    }

    /**
     *
     * The function to get my jquery time format
     *
     * @return String $JQUERY_TIME_FORMAT
     */
    public static function getJqueryTimeFormat() {
        return self::$JQUERY_TIME_FORMAT;
    }

    /**
     *
     * The function to get my moment time format
     *
     * @return String $MOMENT_TIME_FORMAT
     */
    public static function getMomentTimeFormat() {
        return self::$MOMENT_TIME_FORMAT;
    }

    /**
     *
     * The function to get my mysql time format
     *
     * @return String $MYSQL_TIME_FORMAT
     */
    public static function getMysqlTimeFormat() {
        return self::$MYSQL_TIME_FORMAT;
    }

    /**
     *
     * The function to get my thousand seperator
     *
     * @return String $THOUSAND_SEPERATOR
     */
    public static function getThousandSeperator() {
        return self::$THOUSAND_SEPERATOR;
    }

    /**
     *
     * The function to get my decimal marker
     *
     * @return String $DECIMAL_MARKER
     */
    public static function getDecimalMarker() {
        return self::$DECIMAL_MARKER;
    }
    
}
