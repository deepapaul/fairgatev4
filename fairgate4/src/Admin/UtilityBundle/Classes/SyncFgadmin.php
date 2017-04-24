<?php namespace Admin\UtilityBundle\Classes;

use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\Process\Process;
use Symfony\Component\Process\Exception\ProcessFailedException;
use Common\UtilityBundle\Repository\Pdo\ClubPdo;
use Common\UtilityBundle\Util\FgSettings;

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Used to update fg_club and fg_cm_contact table to admin and default database 
 *
 * @author jinesh.m
 */
class SyncFgadmin
{

    /**
     *
     * @var object container object 
     */
    private $container;

    /**
     *
     * @var object entity manager 
     */
    private $em;

    /**
     * Club admin entity manager
     * @var type 
     */
    private $adminEntityManager;

    /**
     * Club Id
     * @var type 
     */
    private $club;

    /**
     * @param \Symfony\Component\DependencyInjection\ContainerInterface $container container
     */
    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
        $this->em = $this->container->get('doctrine')->getManager();
        $this->adminEntityManager = $this->container->get("fg.admin.connection")->getAdminEntityManager();
        $this->club = $this->container->get('club');
    }

    /**
     * To synchronize club note count to club admin db
     * 
     * @param int $clubId club id 
     * @param int $createdClub created club id 
     */
    public function syncClubNoteCount($clubId = null,$createdClub = null)
    {
        $clubPdo = new ClubPdo($this->container);
        $clubNoteCount = $clubPdo->getClubNoteCount($clubId,$createdClub);
        $clubType = $this->club->get('type');
        $this->adminEntityManager->getRepository('AdminUtilityBundle:FgClub')->syncClubdata('notecount', $clubId, $clubNoteCount[0]['notecount'], $this->adminEntityManager,$clubType);
    }

    /**
     * To synchronize club document count to club admin db
     */
    public function syncDocumentCount($clubId = null, $federationId = null)
    {

        $clubPdo = new ClubPdo($this->container);
        $id = ($clubId == null) ? $this->club->get('id') : $clubId;
        $fedId = ($federationId == null) ? $this->club->get('federation_id') : $federationId;

        $clubDocumentCount = $clubPdo->getClubDocumentCount($id, $fedId);
        $this->adminEntityManager->getRepository('AdminUtilityBundle:FgClub')->syncClubdata('documentcount', $id, $clubDocumentCount[0]['doccount'], $this->adminEntityManager);
    }

    /**
     * To synchronize club fed member count to club admin db
     */
    Public function syncFedMemberCount()
    {
        $federationId = $this->getFederationId($this->club->get("type"));
        $clubPdo = new ClubPdo($this->container);
        $fedmemberCount = $clubPdo->getFedCount($this->club->get('id'), $federationId);
        $this->adminEntityManager->getRepository('AdminUtilityBundle:FgClub')->syncClubdata('fedmembercount', $this->club->get('id'), $fedmemberCount[0]['fedcount'], $this->adminEntityManager);
        //To update fed member count on federation level
        if ($this->club->get("type") == 'federation_club' || $this->club->get("type") == 'sub_federation_club') {
            $fedmemberCount = $clubPdo->getFedCount($federationId, 0);
            $this->adminEntityManager->getRepository('AdminUtilityBundle:FgClub')->syncClubdata('fedmembercount', $federationId, $fedmemberCount[0]['fedcount'], $this->adminEntityManager);
        }
        //To update fed member count on subfederation level
        if ($this->club->get("type") == 'sub_federation_club') {
            $subfederationId = $this->club->get("sub_federation_id");
            $fedmemberCount = $clubPdo->getFedCount($subfederationId, $federationId);
            $this->adminEntityManager->getRepository('AdminUtilityBundle:FgClub')->syncClubdata('fedmembercount', $subfederationId, $fedmemberCount[0]['fedcount'], $this->adminEntityManager);
        }
    }

    /**
     * To synchronize last updated date of club contact to club admin db
     */
    public function syncLastUpdated()
    {
        $federationId = $this->getFederationId($this->club->get("type"));
        $mysqlDateFormat = FgSettings::getMysqlDateFormat();
        $clubPdo = new ClubPdo($this->container);
        $lastContactUpdate = $clubPdo->getLastContactUpdatedDate($this->club->get('id'), $federationId, $mysqlDateFormat);
        $this->adminEntityManager->getRepository('AdminUtilityBundle:FgClub')->syncClubdata('lastcontactupdated', $this->club->get('id'), $lastContactUpdate[0]['updatedate'], $this->adminEntityManager);
    }

    /**
     * For get the federation id from the club type
     * @param type $clubtype
     *
     * @return type
     */
    private function getFederationId($clubtype)
    {

        return $clubtype == 'federation' ? $this->club->get("id") : $this->club->get("federation_id");
    }

    /**
     * Document count update process
     * @param type $clubId current club id 
     * @param type $clubType current club type
     */
    public function documentcountUpdateProcess($clubId, $clubType)
    {
        $process = new Process("php ../fairgate4/bin/console doccount:update $clubId $clubType");

        $process->start();
    }
}
