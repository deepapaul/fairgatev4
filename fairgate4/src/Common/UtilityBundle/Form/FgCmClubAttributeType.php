<?php

namespace Common\UtilityBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\Constraints\Collection;
use Symfony\Component\Validator\Mapping\ClassMetadata;
use Symfony\Component\Validator\Constraints\Email;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\Url;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\Valid;
use Symfony\Component\Validator\Constraints\False;
use Symfony\Component\Validator\Constraints\Choice;
use Symfony\Component\Validator\Constraints\Image;
use Symfony\Component\Validator\Constraints\Regex;
use Clubadmin\ContactBundle\Validator\Constraints\FgEmailExists;

/**
 * FgCmClubAttributeType
 *
 * This FgCmClubAttributeType was created for handling form
 *
 * @package    CommonUtilityBundle
 * @subpackage Form
 * @author     pitsolutions.ch
 * @version    Fairgate V4
 */
class FgCmClubAttributeType extends AbstractType
{
    /**
     * Area to build form
     *
     * @param FormBuilderInterface $builder Builder interface
     * @param array                $options Options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('name', 'text', array(
                    'constraints' => array(
                        new NotBlank(array('message' => 'Required')),
                        new Regex(array('pattern' => '/^[A-Za-z0-9]*$/', 'match' => true, 'message' => 'Invalid'))),
                    'required' => true,
                    'label' => 'FgCmClubAttributeType.name',
                    'attr' => array('class' => 'form-control')
                ))
                ->add('address', 'textarea', array(
                    'constraints' => array(
                        new Length(array('max' => 140, 'maxMessage' => 'This value should have 140 characters or less'))),
                    'label' => 'Address',
                    'attr' => array('class' => 'form-control')
                ))
                ->add('email', 'email', array(
                    'constraints' => array(
                        new NotBlank(array('message' => 'Required')),
                        new Email(array('message' => 'Invalid email address')),
                        new FgEmailExists(array('contactId' => false))
                    ),
                    'attr' => array(
                        'class' => 'form-control txtfield'
                    ),
                    'required' => true,
                    'label' => 'Email'))
                ->add('gender', 'choice', array(
                    'choices' => array('male' => 'Male', 'female' => 'Female'),
                    'label' => 'Gender',
                    'placeholder' => '-Select gender-',
                    'attr' => array(
                        'class' => 'form-control'
                    )
                ))
                ->add('news', 'checkbox', array(
                    'label' => 'Newsletter',
                    'constraints' => array(
                        new NotBlank(array('message' => 'Required'))),
                    'required' => true,
                    'attr' => array(
                        'class' => 'form-control'
                    )
                ))
                ->add('nationality', 'country', array(
                    'constraints' => array(new NotBlank(array('message' => 'Required'))),
                    'label' => 'Nationality',
                    'placeholder' => 'Select country',
                    'required' => true,
                    'attr' => array(
                        'class' => 'form-control'
                    )
                ))
                ->add('pincode', 'text', array(
                    'constraints' => array(
                        new NotBlank(array('message' => 'Required')),
                        new Regex(array('pattern' => '/^[0-9]*$/', 'match' => true, 'message' => 'Invalid'))),
                    'required' => true,
                    'label' => 'Pincode',
                    'attr' => array('class' => 'form-control')
                ))
                ->add('url', 'text', array(
                    'constraints' => array(
                        new NotBlank(array('message' => 'Required')),
                        new Url(array('message' => 'Invalid'))),
                    'required' => true,
                    'label' => 'Site url',
                    'attr' => array('class' => 'form-control')
                ))
                ->add('language', 'language', array(
                    'label' => 'Language',
                    'placeholder' => '-Select language-',
                    'multiple' => 'true',
                    'attr' => array(
                        'class' => 'form-control'
                    )
                ))
                ->add('contact', 'text', array(
                    'constraints' => array(
                        new NotBlank(array('message' => 'Required'))),
                    'required' => true,
                    'label' => 'Contact',
                    'attr' => array('class' => 'form-control typeahead')
        ));
    }

    /**
     * @param OptionsResolver $resolver
     */
    public function configureOptions(OptionsResolver $resolver)
    {
    }

    /**
     * @return string
     */
    public function getBlockPrefix()
    {
        return 'common_utilitybundle_fgcmclubattribute';
    }

}
