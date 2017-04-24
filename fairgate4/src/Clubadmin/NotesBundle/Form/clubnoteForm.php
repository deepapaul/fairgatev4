<?php

namespace Clubadmin\NotesBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;

/**
 * For create the club note form
 */
class clubnoteForm extends AbstractType
{

    private $formsize ;
    /**
     * Note form constructor
     * @param type $notesDetails notedetails
     */
    public function __construct($notesDetails)
    {
      $this->formsize = $notesDetails;
    }
    
    /**
     * Method to set custom values as globals
     * 
     * @param array $customValues array of sutom values to set
     */
    private function setCustomValues($customValues) {
        $this->formsize = $customValues['formsize'];
    } 
    
    /**
     * Build note form
     * @param \Symfony\Component\Form\FormBuilderInterface $builder form builder
     * @param array                                        $options form options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $this->setCustomValues($options['custom_value']);  
        $formsize  = $this->formsize;
        foreach ($formsize as $notevalue) {
            $builder->add($notevalue['id'], TextareaType::class, array('data' => $notevalue['note'], 'attr' => array('required' => true, 'class' => 'auto-textarea timeline-content input-group note-border', 'data-key'=> $notevalue['id'], 'maxlength'=>"2000")));
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
        return 'common_utilitybundle_fgclubnotes';

    }


}
