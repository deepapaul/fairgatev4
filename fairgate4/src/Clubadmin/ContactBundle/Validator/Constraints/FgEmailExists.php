<?php

namespace Clubadmin\ContactBundle\Validator\Constraints;

use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\Exception\MissingOptionsException;

/**
 * FgEmailExists
 *
 * @author PITSolutions <pit@pitsolutions.com>
 */
class FgEmailExists extends Constraint
{
    public $emailExistsMessage = 'EMAIL_ALREADY_EXISTS';
    public $contactId;
    public $typeOfContact;
    public $hasFedMembership;
    public $subscriberId;
    public $from;
    public $excludeMergableContacts = false;

    /**
     * Constructor
     *
     * @param Array $options Options
     */
    public function __construct($options = null)
    {
        if (null !== $options && !is_array($options)) {
            $options = array(
                'contactId' => $options,
                'typeOfContact' => $options,
                'hasFedMembership' => $options,
                'subscriberId' => $options,
                'from' => $options,
                'excludeMergableContacts' => $options
            );
        }

        parent::__construct($options);

        if (null === $this->contactId) {
            throw new MissingOptionsException("'contactId'is a mandatory option'");
        }
    }

    /**
     * Validate function
     * @return String
     */
    public function validatedBy()
    {
        return 'email_exists';
    }

}
