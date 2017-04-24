<?php


namespace Clubadmin\ContactBundle\Validator\Constraints;

use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * FgEmailExistsValidator
 *
 * @author Ravikumar <ravikumar.p@pitsolutions.com>
 */
class FgEmailExistsValidator extends ConstraintValidator
{
    /**
     * @var Connection
     */
    public $emailExistsMessage = 'Email already Exists';

    private $container;


    /**
     * Constructor
     *
     * @param Object $container Container
     */
    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;

    }

    /**
     * Validate function
     *
     * @param Int    $value      Value
     * @param Object $constraint Constraint
     */
    public function validate($value, Constraint $constraint)

    {
        if ($value != '') {
            $conn = $this->container->get('database_connection');
            $em = $this->container->get('doctrine.orm.entity_manager');
            $club = $this->container->get('club');

            $result = $em->getRepository('CommonUtilityBundle:FgCmAttribute')->searchEmailExistAndIsMergable($this->container, $constraint->contactId, $value, $constraint->hasFedMembership, $constraint->subscriberId, $constraint->from, $constraint->excludeMergableContacts, $constraint->typeOfContact);
            if (count($result) > 0) {
                if($result[0]['clubId'] == $club->get('id')) {
                    $this->context->addViolation($constraint->emailExistsMessage);
                } else {
                    $terminologyService = $this->container->get('fairgate_terminology_service');
                    $federation=$terminologyService->getTerminology('Federation', $this->container->getParameter('singular'));
                    $emailExist=$this->container->get('translator')->trans('EMAIL_ALREADY_EXISTS_FEDERATION', array('%federation%' => $federation));
                    $this->context->addViolation($emailExist);
                }
            }
        }
    }
}
