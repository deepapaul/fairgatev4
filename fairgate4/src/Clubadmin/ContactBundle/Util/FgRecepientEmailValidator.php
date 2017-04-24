<?php

namespace Clubadmin\ContactBundle\Util;

use Symfony\Component\Validator\Constraints\Email;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\Collection;

/**
 * For handling the validation of membership log data edit via inline edit
 */
class FgRecepientEmailValidator
{
    /**
     * $container
     * @var object {container object}
     */
    private $container;
    
    /**
     * $value
     * @var string Email
     */
    private $value;

    /**
     * 
     * @param type $container
     * @param type $value
     */
    public function __construct($container, $value)
    {
        $this->value = $value;
        $this->container = $container;
    }

    /**
     * Function to validate email
     */
    public function isValidEmail()
    {
        $requiredMessage = 'REQUIRED';
        $invalidEmailMessage = 'INVALID_EMAIL';
        $updateSuccessMessage = 'RECEPIENTS_EDIT_SUCCESS';
        $translatorService = $this->container->get('translator');
        
        $valueConstraints = array();
        $valueConstraints[] = new Email(array('message' => $invalidEmailMessage));
        $valueConstraints[] = new NotBlank(array('message' => $requiredMessage));

        $data = array('value' => $this->value);
        $collectionConstraint = new Collection(array(
            'value' => $valueConstraints
        ));
        $errors = $this->container->get('validator')->validate($data, $collectionConstraint);
        
        if (count($errors) !== 0) {
            $output = array('valid' => 'false', 'msg' => $translatorService->trans($errors[0]->getMessage()));
        } else {
            $output = array('valid' => 'true', 'msg' => $translatorService->trans($updateSuccessMessage));
        }

        return $output;
    }
}
