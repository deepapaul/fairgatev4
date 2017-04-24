<?php

namespace Clubadmin\NotesBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;

/**
 * For create the contact note form
 */
class sponsorcontactnoteform extends AbstractType
{
    public $notesData;
    /**
     * Contact note constructor
     * @param type $notesDetails
     */
    public function __construct($notesDetails)
    {
      $this->notesData=$notesDetails;
    }
    
    /**
     * Method to set custom values as globals
     * 
     * @param array $customValues array of sutom values to set
     */
    private function setCustomValues($customValues) {
        $this->notesData = $customValues['notesData'];
    } 
    
    /** Build contact note form
     *
     * @param \Symfony\Component\Form\FormBuilderInterface $builder builder
     * @param array                                        $options options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $this->setCustomValues($options['custom_value']);  
        $value = $options['custom_value']; 
        $notesData=$this->notesData;
        foreach ($notesData as $notevalue) {          
            $builder->add($notevalue['id'], TextareaType::class, array('data' => $notevalue['note'], 'attr' => array('required' => true, 'class' => 'auto-textarea timeline-content input-group note-border', 'data-key'=> $notevalue['id'], 'maxlength'=>"2000")));           
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
        return 'contactadmin_notesbundle_sponsorcontactnote';

    }


}