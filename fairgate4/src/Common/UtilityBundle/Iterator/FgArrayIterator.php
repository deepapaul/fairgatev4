<?php

namespace Common\UtilityBundle\Iterator;

/**
 * FgArrayIterator
 *
 * This FgArrayIterator was created for handling Iterator
 *
 * @package    CommonUtilityBundle
 * @subpackage Iterator
 * @author     pitsolutions.ch
 * @version    Release:1
 */
class FgArrayIterator extends \RecursiveIteratorIterator {

    public $key;
    public $current;
    public $next;
    public $iterator;
    public $filterType = '';
    public $result = array();
    public $temp = array();
    public $level1 = '';
    public $level2 = '';
    public $level3 = '';
    public $level4 = '';
    public $translator;
    public $clubLanguages;
    public $correspondancelang;
    public $gender;
    public $salutation;
    public $nationality1;
    public $nationality2;
    public $correspondaceLand;
    public $invoiceLand; 
    public $genderTrans;
    public $salutationTrans;
    public $countryList;
    public $executiveBrdId;
    public $executiveBrdTitle;
    private $level1Count = 0;
    private $level2Count = 0;
    private $level3Count = 0;
    private $level4Count = 0;

    /**
     * Constructor
     *
     * @param array $iterator Iterator
     */
    public function __construct(\Traversable $iterator) {
        $this->iterator = $iterator;
        parent::__construct($iterator);
    }

    /**
     * rewind
     *
     * @return Array
     */
    public function rewind() {
        return parent::rewind();
    }

    /**
     * valid
     *
     * @return Array
     */
    public function valid() {
        return parent::valid();
    }

    /**
     * key
     *
     * @return Array
     */
    public function key() {
        $this->key = parent::key();
        switch ($this->filterType) {
            case 'role':
                $this->groupRoles();
                break;
            case 'contactFields':
                $this->groupContactFields();
                break;
        }

        return $this->key;
    }

    /**
     * current
     *
     * @return Array
     */
    public function current() {
        $this->current = parent::current();

        return $this->current;
    }

    /**
     * next
     *
     * @return Array
     */
    public function next() {
        $this->next = parent::next();

        return $this->next;
    }

    /**
     * endChildren
     *
     * @return Array
     */
    public function endChildren() {
        $this->level1 = '';
        $this->level2 = '';
        $this->level3 = '';
        $this->level4 = '';

        return parent::endChildren();
    }

    /**
     * Get the iterated - processed result
     *
     * @return Array
     */
    public function getResult() {
        return $this->result;
    }

    /*
     * Process contact field sql result as required for filter
     *
     * @return Array
     */

    private function groupContactFields() {
        $defaultSelect = 'select2';
        switch ($this->key) {
            case 'id':
                $this->temp = array('id' => $this->current);
                break;
            case 'fieldValue';
                switch ($this->temp['id']) {
                    case $this->correspondancelang:
                        $this->temp['input'] = $this->clubLanguages;
                        break;
                    case $this->nationality1:
                        $this->temp['input'] = $this->countryList;
                        $defaultSelect = 'select-search';
                        break;
                    case $this->nationality2;
                        $this->temp['input'] = $this->countryList;
                        $defaultSelect = 'select-search';
                        break;
                    case $this->correspondaceLand;
                        $this->temp['input'] = $this->countryList;
                        $defaultSelect = 'select-search';
                        break;
                    case $this->invoiceLand; 
                        $this->temp['input'] = $this->countryList;
                        $defaultSelect = 'select-search';
                        break;
                    case $this->gender:
                        $this->temp['input'] = $this->genderTrans;
                        break;
                    case $this->salutation:
                        $this->temp['input'] = $this->salutationTrans;
                        break;
                    default:
                        if (($this->temp['type'] == 'select' || $this->temp['type'] == 'checkbox' || $this->temp['type'] == 'radio') && $this->current != '') {
                            $values = explode(';', $this->current);
                            $input = array();
                            foreach ($values as $value) {
                                $input[] = array('id' => $value, 'title' => $value);
                            }
                            $this->temp['input'] = $input;
                        }
                        break;
                }
                switch ($this->temp['type']) {
                    case 'select':
                        $this->temp['type'] = 'select';
                        $this->temp['data-edit-type'] = $defaultSelect;
                        break;
                    case 'checkbox':
                        $this->temp['type'] = 'select';
                        $this->temp['data-edit-type'] = 'select-multiple';
                        break;
                    case 'radio':
                        $this->temp['type'] = 'select';
                        $this->temp['data-edit-type'] = 'select2';
                        break;
                    case 'multiline':
                        $this->temp['type'] = 'text';
                        $this->temp['data-edit-type'] = 'textarea';
                        break;
                    case 'singleline':
                        $this->temp['type'] = 'text';
                        $this->temp['data-edit-type'] = 'text';
                        break;
                    case 'email':
                        $this->temp['type'] = 'text';
                        $this->temp['data-edit-type'] = 'text';
                        break;
                    case 'url':
                        $this->temp['type'] = 'text';
                        $this->temp['data-edit-type'] = 'text';
                        $this->temp['data-type'] = 'url';
                        break;
                    case 'login email':
                        $this->temp['type'] = 'text';
                        $this->temp['data-edit-type'] = 'text';
                        break;
                    case 'date':
                        $this->temp['data-edit-type'] = 'date';
                        break;
                    case 'number':
                        $this->temp['data-edit-type'] = 'number';
                        break;
                }
                break;
            case 'title':
                $this->temp['title'] = $this->current;
                break;
            case 'type':
                $this->temp['type'] = $this->current;
                break;
            case 'selectgroup':
                $this->temp['selectgroup'] = $this->current;
                break;
            case 'shortName':
                $this->temp['shortName'] = $this->current;
                break;
            case 'catId':
                $this->temp['catId'] = $this->current;
                break;
            case 'sort';
                if ($this->temp['type'] == 'imageupload' || $this->temp['type'] == 'fileupload') {
                    $this->temp['show_filter']=0;
                }
                
                $this->result[] = $this->temp;
                break;
            default:
                break;
        }
    }

    /*
     * Process roles sql result as required for filter
     *
     * @return Array
     */

    private function groupRoles() {
        switch ($this->key) {
            case 'groupTitle':
                if (!isset($this->result["$this->current"])) {

                    $this->result["$this->current"] = array();
                    $this->result["$this->current"]['title'] = $this->current;
                    $this->result["$this->current"]['entry'] = array();
                }
                $this->level1 = $this->current;
                break;
            case 'categoryId':
                if ($this->current != '') {
                    $this->level2 = 't' . $this->current;
                    if (!isset($this->result["$this->level1"]['entry'][$this->level2])) {
                        $this->result["$this->level1"]['entry'][$this->level2] = array();
                        $this->result["$this->level1"]['entry'][$this->level2]['id'] = $this->current;
                        $this->result["$this->level1"]['entry'][$this->level2]['type'] = 'select';
                    }
                }
                break;
            case 'categoryTitle';
                if (!isset($this->result["$this->level1"]['entry']["$this->level2"]['title'])) {
                    $this->result["$this->level1"]['entry']["$this->level2"]['title'] = $this->current;
                }
                //echo '<pre>';print_r($this->result);exit;
                break;
            case 'roleId';
                if ($this->current != '') {
                    $this->level3 = 't' . $this->current;
                    if (!isset($this->result["$this->level1"]['entry']["$this->level2"]['input'])) {
                        $this->result["$this->level1"]['entry']["$this->level2"]['input'] = array();
                    }
                    if (!isset($this->result["$this->level1"]['entry']["$this->level2"]['input']["$this->level3"])) {
                        $this->result["$this->level1"]['entry']["$this->level2"]['input']["$this->level3"] = array('id' => $this->current, 'type' => 'select');
                    }
                }
                break;
            case 'roleTitle';
                if ($this->current != '') {
                    if (!isset($this->result["$this->level1"]['entry']["$this->level2"]['input']["$this->level3"]['title'])) {
                        if ($this->level3 == 't' . $this->executiveBrdId) {
                            $roleTitle = $this->executiveBrdTitle;
                        } else {
                            $roleTitle = $this->current;
                        }

                        $this->result["$this->level1"]['entry']["$this->level2"]['input']["$this->level3"]['title'] = $roleTitle;
                    }
                }
                break;
            case 'functionId';
                if ($this->current != '') {
                    $this->level4 = 't' . $this->current;
                    if (!isset($this->result["$this->level1"]['entry']["$this->level2"]['input']["$this->level3"]['input'])) {
                        $this->result["$this->level1"]['entry']["$this->level2"]['input']["$this->level3"]['input'] = array();
                    }
                    if (!isset($this->result["$this->level1"]['entry']["$this->level2"]['input']["$this->level3"]['input']["$this->level4"])) {
                        $this->result["$this->level1"]['entry']["$this->level2"]['input']["$this->level3"]['input']["$this->level4"] = array('id' => $this->current);
                    }
                }
                break;
            case 'functionTitle';
                if ($this->current != '') {
                    if (!isset($this->result["$this->level1"]['entry']["$this->level2"]['input']["$this->level3"]['input'][$this->level4]['title'])) {
                        $this->result["$this->level1"]['entry']["$this->level2"]['input']["$this->level3"]['input'][$this->level4]['title'] = $this->current;
                    }
                }
                break;
        }
    }

}
