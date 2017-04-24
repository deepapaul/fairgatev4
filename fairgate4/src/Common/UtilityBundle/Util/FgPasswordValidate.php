<?php

namespace Common\UtilityBundle\Util;


/**
 * FgPasswordValidate
 *
 * This class is used for handling validation to make secure passwords
 *
 * @package    CommonUtilityBundle
 * @subpackage Util
 * @author     pitsolutions.ch
 * @version    Fairgate V4
 */
class FgPasswordValidate {

 
    /**
     *
     * @var object container object 
     */
    private $container;

    /**
     *
     * @var object entity manager 
     */
    private $em;

    /**
     * @param \Symfony\Component\DependencyInjection\ContainerInterface $container container
     */
    public function __construct($container)
    {
        $this->container = $container;
        $this->em = $this->container->get('doctrine')->getManager();
    }
    
    /**
     * This method is used to check desired criteria for creating secure passwords.
     * 
     * @param string $password password to be validated
     * 
     * @return boolean
     */
    public function valid($password)
    {
        $messageArray = array();
        $statusArray = array();
        if(strlen($password) < 8 || strlen($password) > 25) {
            $statusArray[] = false;
            $messageArray[] = 'Password length should be between 8-25';
        }
        if (!$this->checkAllCharsExist($password)) {
            $statusArray[] = false;
            $messageArray[] = 'Contains at least 1 lower case letter and 1 upper case letter (all UTF-8), at least 1 number and at least 1 special character (punctuation)';
        }
        if ($this->checkIdenticalChars($password)) {
            $statusArray[] = false;
            $messageArray[] = 'Not more than 2 identical characters in a row (e.g., 111 not allowed)';
        } 
        if ($this->checkSequenceAlphabets($password)) {
            $statusArray[] = false;
            $messageArray[] = 'Not any sequence of the English alphabet (above 3 letters)';
        } 
        if ($this->checkWorstPassword($password)) {
            $statusArray[] = false;
            $messageArray[] = 'Not matching any of the worst-password-db';
        }
        
        if (in_array(false, $statusArray)) {
            $status = false;
        } else{
            $status = true;
        }
        
        return array('status'=>$status, 'message'=>$messageArray);
    }

    /**
     * This method is used to check whether the the password contains more than 2 identical characters.
     * Not more than 2 identical characters in a row (e.g., 111 not allowed)
     * 
     * @param string $str Password string to be checked
     * 
     * @return boolean
     */
    function checkIdenticalChars($str) {
        $re = '/^(.)*(([\S+])\3\3)(.)*$/';
        if (preg_match($re, $str)) {
            return true;
        }
        return false;
    }

    /**
     * This method is used to check whether the password contains at least 1 lower case letter and 1 upper case letter (all UTF-8), at least 1 number and at least 1 special character (punctuation).
     * 
     * @param string $str Password string to be checked
     * 
     * @return boolean
     */
    function checkAllCharsExist($str)
    {
        $re = '/(?=.*?[\p{Ll}])(?=.*?[\p{Lu}])(?=.*[0-9])(?=.*[!@#$%^&*()_+~{}:?><;.,])/i';
        if (preg_match($re, $str)) {
            return true;
        }
        return false;
    }

    /**
     * This method is used to check whether the sequence of alphabets or numbers(3 or more chars) are existing in password string; 
     * 
     * @param string $str Password to be checked
     * 
     * @return boolean
     */
    function checkSequenceAlphabets($str) {
        $re = '/(abc|bcd|cde|def|efg|fgh|ghi|hij|ijk|jkl|klm|lmn|mno|nop|opq|pqr|qrs|rst|stu|tuv|uvw|vwx|wxy|xyz|012|123|234|345|456|567|678|789)+/i';
        if (preg_match($re, $str)) {
            return true;
        }
        return false;
    }
    
    /**
     * This method is used to check whether the password exists in worst-password list.
     * Worst passwords are saved manually in db.  
     * 
     * @param string $str Password to be checked
     * 
     * @return boolean
     */
    function checkWorstPassword($str) {
        return false;
    }
    

}
