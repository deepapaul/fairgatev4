<?php

namespace Common\UtilityBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * FgFieldCategoryType
 *
 * This FgFieldCategoryType was created for handling form
 *
 * @package    CommonUtilityBundle
 * @subpackage Form
 */
class FgClubDataCategory extends AbstractType
{

    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public $submittedData;
    public $editData;
    public $containerParameters;
    public $pageType;

    /**
     * Constructor
     *
     * @param array $submittedData       Submitted value
     * @param array $editData            Existing values
     * @param array $containerParameters Container params
     */
    public function __construct($submittedData, $editData, $containerParameters, $pageType="")
    {
        $this->submittedData = $submittedData;
        $this->editData = $editData;
        $this->containerParameters = $containerParameters;
        $this->pageType = $pageType;
    }
    
    /**
     * Method to set custom values as globals
     * 
     * @param array $customValues array of sutom values to set
     */
    private function setCustomValues($customValues) {
        $this->submittedData = $customValues['submittedData'];        
        $this->editData = $customValues['editData'];
        $this->containerParameters = $customValues['containerParameters'];
        $this->pageType = $customValues['pageType'];
        $this->clubObj = $customValues['club'];
    }

    /**
     * Area to build form
     *
     * @param FormBuilderInterface $builder Builder interface
     * @param array                $options Options
     */
    public function buildForm(FormBuilderInterface $builder,array $options)
    {
        $this->setCustomValues($options['custom_value']);   
        $attr=array('data-catId' => '1');
        if ($this->submittedData) {
            $attr = $this->submittedData['same_invoice_address']=='1' ? '1':'0';
        } elseif ($this->editData){
            $attr= ($this->editData['correspondence_id']==$this->editData['billing_id']) ? '1': '0';
        }
        $builder->add('system', FgClubDataField::class, array('label' => 'System', 'attr' => array('data-catId' => '0'), 'custom_value' => array('submittedData' => $this->submittedData, 'editData' => $this->editData, 'containerParameters' => $this->containerParameters, 'pageType' => $this->pageType, 'club' => $this->clubObj)));
        $builder->add('Correspondence', FgClubDataField::class, array('label' => 'CL_CORRESPONDENCE_TITLE', 'attr' =>array('data-catId' => '1','data-same'=>$attr ), 'custom_value' => array('submittedData' => $this->submittedData, 'editData' => $this->editData, 'containerParameters' => $this->containerParameters, 'club' => $this->clubObj) ));
        $builder->add('Invoice', FgClubDataField::class, array('label' => 'CL_INVOICE_TITLE', 'attr' => array('data-catId' => '2','data-same'=>$attr), 'custom_value' => array('submittedData' => $this->submittedData, 'editData' => $this->editData, 'containerParameters' => $this->containerParameters, 'club' => $this->clubObj)));
        $builder->add('Notification', FgClubDataField::class, array('label' => 'CL_SIGNATURE_TITLE', 'attr' => array('data-catId' => '3','data-same'=>$attr), 'custom_value' => array('submittedData' => $this->submittedData, 'editData' => $this->editData, 'containerParameters' => $this->containerParameters, 'club' => $this->clubObj)));
        if ($this->editData[fedIcon_Visibility]) {
            $builder->add('Federation', FgClubDataField::class, array('label' => 'CL_FEDERATION_LOGO_TITLE', 'attr' => array('data-catId' => '4','data-same'=>$attr), 'custom_value' => array('submittedData' => $this->submittedData, 'editData' => $this->editData, 'containerParameters' => $this->containerParameters, 'club' => $this->clubObj)));
        }
    }
    
    /**
     * @param OptionsResolver $resolver resolver
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'custom_value' => null,
        ));
    }

    /**
     * @return string
     */
    public function getBlockPrefix()
    {
        return 'fg_club_category';
    }

}
