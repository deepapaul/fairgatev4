<?php

namespace Common\UtilityBundle\Repository;

use Doctrine\ORM\EntityRepository;
use Common\UtilityBundle\Entity\FgDmContactSighted;

/**
 * This repository is used for document sighted conditions
 *
 * @author pitsolutions.ch
 */
class FgDmContactSightedRepository extends EntityRepository {
    
    /**
     *  Function to mark as seen sighted documents 
     * 
     * @param int $contact  contact id
     * @param int $document document id
     * @return boolean
     */

    public function documentSighted($contact, $document) {

        $contactobj = $this->_em->getRepository('CommonUtilityBundle:FgCmContact')->find($contact);
        foreach($document as $id){
            $docobj = $this->_em->getRepository('CommonUtilityBundle:FgDmDocuments')->find($id);
            $obj = new FgDmContactSighted();
            $obj->setContact($contactobj);
            $obj->setDocument($docobj);
            $this->_em->persist($obj);  
        }
        
        $this->_em->flush();
        
        return true;
    }

}
