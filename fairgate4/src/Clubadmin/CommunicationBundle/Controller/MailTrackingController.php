<?php

/**
 * MailTrackingController
 *
 * This controller used for tracking emails using pixel images
 *
 * @package    ClubadminCommunicationBundle
 * @subpackage Controller
 * @author     pitsolutions.ch
 * @version    Fairgate V4
 */
namespace Clubadmin\CommunicationBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Common\UtilityBundle\Util\TransparentPixelResponseClass;
use Clubadmin\Util\Contactlist;
use Symfony\Component\HttpFoundation\JsonResponse;
use Common\UtilityBundle\Util\FgClubSyncDataToAdmin;

class MailTrackingController extends Controller
{

    /**
     * Club Default System Language
     * @var string
     */
    public $clubDefaultSystemLang;

    /**
     * This function is used to preexecute commonly used services, to reduce the number of requests
     *
     *  @return response
     */
    public function preExecute()
    {
        $club = $this->get('club');
        $clubId = $club->get('id');
        if ($clubId == 0) {
            throw $this->createNotFoundException('Not a club!');
        } else {
            $this->clubDefaultSystemLang = $club->get('default_system_lang');
        }
        $this->container->get('translator')->setLocale($this->clubDefaultSystemLang);
    }

    /**
     * This function is used to change status of emails with respective ids and return a 1px transparent image
     *
     * @param type $request request parameter
     *
     * @return image
     */
    public function trackingEmailAction(Request $request)
    {
        $id = $request->query->get('id');
        if (null !== $id) {
            $em = $this->getDoctrine()->getManager();
            $newsletter = $em->getRepository('CommonUtilityBundle:FgCnNewsletterReceiverLog')->find($id);
            if (!$newsletter) {

            } else {
                $newsletter->setOpenedAt(new \DateTime());
                $em->flush();
            }
        }

        return new TransparentPixelResponseClass();
    }

    /**
     * Function for showing the unsubscription page
     *
     * @param $encodings encoded parameter
     *
     * @return Template
     */
    public function unSubscriptionAction($encodings)
    {
        /* $encodings may be encoded format like "type=contact&id=507281" or "type=subscriber&id=557"; */
        $container = $this->container;
        $em = $this->getDoctrine()->getManager();
        $decodedValues = $this->decodeUrl($encodings);
        $type = $decodedValues['type'];
        $contact = $decodedValues['contact'];
        $decodedKey = $decodedValues['key'];
        $secretKey = $container->getParameter('secret');
        $club = $this->get('club');
        $clubTitle = $club->get('title');
        $clubId = $club->get('id');
        $clubType = $club->get('type');
        if ($secretKey !== $decodedKey) {
            throw $this->createNotFoundException($clubTitle . ' has no access to this page');
        }
        if ($type == 'contact') {
            $email = $container->getParameter('system_field_primaryemail');
            $contactlistClass = new Contactlist($container, $contact, $this->get('club'), 'allVisible');
            $contactlistClass->setColumns(array("contactname", "`$email` as email"));
            $contactlistClass->setFrom();
            $contactlistClass->setCondition();
            $contactlistClass->addCondition("fg_cm_contact.id = $contact ");
            $query = $contactlistClass->getResult();
            $result = $em->getRepository('CommonUtilityBundle:FgCmMembership')->getContactList($query);
            $isSubscribed = $em->getRepository('CommonUtilityBundle:FgCmContact')->isContactSubscribed($contact);
            if ($result && $isSubscribed == 1) {
                $name = $result[0]['contactname'] . " (" . $result[0]['email'] . ")";
                $email = $result[0]['email'];
            }
        } else if ($type == 'subscriber') {
            $subscriberDetail = $em->getRepository('CommonUtilityBundle:FgCnSubscriber')->getSubscriberName($contact);
            $name = $subscriberDetail['name'];
            $email = $subscriberDetail['email'];
        }

        return $this->render('ClubadminCommunicationBundle:Newsletter:unsubscription.html.twig', array('clubName' => $clubTitle, 'name' => $name, 'email' => $email, 'encodings' => $encodings));
    }

    /**
     * Function for decoding type and id from the encoded url
     *
     * @param $encodings encoded parameter
     *
     * @return array of type and contactid
     */
    private function decodeUrl($encodings)
    {
        /* $encodings may be encoded format like "type=contact&id=507281" or "type=subscriber&id=557"; */
        $decodedUrl = base64_decode($encodings);
        $urlArray = explode("&", $decodedUrl);
        foreach ($urlArray as $detail) {
            $value = explode("=", $detail);
            if ($value[0] == 'type') {
                $type = $value[1];
            }
            if ($value[0] == 'id') {
                $contact = $value[1];
            }
            if ($value[0] == 'key') {
                $key = $value[1];
            }
        }

        return array('type' => $type, 'contact' => $contact, 'key' => $key);
    }

    /**
     * Function for newsletter un subscription
     *
     * @param $encodings encoded parameter
     *
     * @return type jsonresponse
     */
    public function unsubscribeContactAction($encodings)
    {
        $decodedValues = $this->decodeUrl($encodings);
        $type = $decodedValues['type'];
        $contact = $decodedValues['contact'];
        $em = $this->getDoctrine()->getManager();
        $club = $this->get('club');
        $clubId = $club->get('id');
        /* If type is 'Contact' update 'is_subscribed' field to 0
          If type is 'Subscriber' delete subscriber from subscriber table
         */
        if ($type == 'contact') {
            $contactId = $em->getRepository('CommonUtilityBundle:FgCmContact')->find($contact);
            $fedContact = $contactId->getFedContact()->getId();
            $currentContact = $em->getRepository('CommonUtilityBundle:FgCmContact')->findOneBy(array('fedContact' => $fedContact, 'club' => $clubId));
            $em->getRepository('CommonUtilityBundle:FgCmContact')->unsubscribeContact($currentContact->getId(), $clubId);
        } else if ($type == 'subscriber') {
            $em->getRepository('CommonUtilityBundle:FgCnSubscriber')->deleteSubscribers(array($contact), $clubId);
        }

        $subscriberSyncObject = new FgClubSyncDataToAdmin($this->container);
		$subscriberSyncObject->updateSubscriberCount($clubId);
        
        return new JsonResponse(array('success' => 'success',));
    }
}
