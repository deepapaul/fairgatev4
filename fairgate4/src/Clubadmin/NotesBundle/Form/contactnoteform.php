<?php

namespace Clubadmin\NotesBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;

/**
 * For create the contact note form
 */
class contactnoteform extends AbstractType
{
    public $formsize;
    /**
     * Contact note constructor
     * @param type $notesDetails
     */
    public function __construct($notesDetails)
    {
      $this->formsize=$notesDetails;
    }
    
    /**
     * Method to set custom values as globals
     * 
     * @param array $customValues array of sutom values to set
     */
    private function setCustomValues($customValues) {
        $this->formsize = $customValues['formsize'];
    } 
    
/** Build contact note form
 *
 * @param \Symfony\Component\Form\FormBuilderInterface $builder builder
 * @param array                                        $options options
 */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $this->setCustomValues($options['custom_value']);  
        $formsize=$this->formsize;
        foreach ($formsize as $notevalue) {
           if ($options['data']['type'] != 'archive') {
            $builder->add($notevalue['id'], TextareaType::class, array('data' => $notevalue['note'], 'attr' => array('required' => true, 'class' => 'auto-textarea timeline-content input-group note-border', 'data-key'=> $notevalue['id'], 'maxlength'=>"2000")));
           } else {
             $builder->add($notevalue['id'], TextareaType::class, array('data' => $notevalue['note'], 'attr' => array('required' => true, 'class' => 'auto-textarea timeline-content input-group note-border', 'data-key'=> $notevalue['id'], 'maxlength'=>"2000", 'disabled' =>'disabled', "readonly" => true)));
           }

        }
    }
/**
 * For set the default options
 *
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
        return 'contactadmin_terminologybundle_contactform';

    }


}