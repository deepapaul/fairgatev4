<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of FunctionSortOrderCorrection
 *
 * @author rinu.rk
 */
class FunctionSortOrderCorrection { 

     /**
     * $em.
     *
     * @var object entitymanager object
     */
    private $conn;
    private $log;


    /**
     * Constructor for initial setting.
     *
     * @param type $conn   container
     */
    public function __construct($conn){
        $this->conn = $conn;
        $this->log = 'Function_SortOrder_Correction_'.date('dHis').'.txt';
    }
     /**
     * Function to correct function sort_order
     * @throws \Clubadmin\ContactBundle\Util\Exception
     */
    public function correctSortOrder(){
        try {
            $this->conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            $this->conn->beginTransaction();
            $this->correctNonWorkgroupFunSort();
            $this->correctFedWorkgroupFunSort();
            $this->correctLowerWorkgroupFunSort();
            $this->correctTeamRoleSort();
            $this->correctRoleSort();
            $this->conn->commit();
        } catch (Exception $ex) {
            $this->conn->rollback();
            echo "Failed: " . $ex->getMessage();
            throw $ex;
        }
    }
    /**
     * Function to correct non workgroup fun sort
     */
    private function correctNonWorkgroupFunSort(){

        $functionsQry = $this->conn->query(" SELECT GROUP_CONCAT(function_id) as functionIds, role_id, ROLE.title, CAT.title as categoryTitle, CAT.is_team as isTeamCat, CAT.is_workgroup as isWorkgroupCat, CLUB.title , CAT.club_id "
            . " FROM `fg_rm_role_function` ROLE_FUN "
            . " INNER JOIN fg_rm_role ROLE ON ROLE.id = ROLE_FUN.role_id "
            . " INNER JOIN fg_rm_category CAT ON CAT.id = ROLE.category_id "
            . " INNER JOIN fg_club CLUB ON CLUB.id = CAT.club_id  "
            . " WHERE CAT.is_workgroup != 1  GROUP BY ROLE_FUN.role_id "); //CLUB.id  = 8573
        $functions = $functionsQry->fetchAll();
        foreach ($functions as $functiondetails) { 
//            $testQuery = "select group_concat(sort_order separator ', ') as test from fg_rm_function WHERE id IN (".$functiondetails['functionIds'].") AND is_federation = 0 ORDER BY sort_order ASC";
//            $test = $this->conn->query($testQuery)->fetchAll();
//            echo "<pre>$testQuery  {$functiondetails['role_id']}<br />"; print_r($test[0]['test']);
            
            $updateQuery = "SET @a = 0; UPDATE `fg_rm_function` SET `sort_order` = @a:=@a+1  WHERE id IN (".$functiondetails['functionIds'].") "
                . " AND is_federation = 0 ORDER BY sort_order ASC, id ASC ";            
            $this->conn->exec($updateQuery);
            file_put_contents($this->log, $updateQuery."\n", FILE_APPEND);
            
//            $testQuery = "select group_concat(sort_order separator ', ') as test from fg_rm_function WHERE id IN (".$functiondetails['functionIds'].") AND is_federation = 0 ORDER BY sort_order ASC";
//            $test = $this->conn->query($testQuery)->fetchAll();
//            echo "<br />"; print_r($test[0]['test']);
//            echo "<br />==================================================<br /><br />";
        }
     
    }
    
    /**
     * Function to correct workgroup fun sort (fed and standard clubs)
     */
    private function correctFedWorkgroupFunSort(){

        $functionsQry = $this->conn->query(" SELECT GROUP_CONCAT(function_id) as functionIds, role_id, ROLE.title, CAT.title as categoryTitle, CAT.is_team as isTeamCat, CAT.is_workgroup as isWorkgroupCat, CLUB.title , CAT.club_id "
            . " FROM `fg_rm_role_function` ROLE_FUN "
            . " INNER JOIN fg_rm_role ROLE ON ROLE.id = ROLE_FUN.role_id "
            . " INNER JOIN fg_rm_category CAT ON CAT.id = ROLE.category_id "
            . " INNER JOIN fg_club CLUB ON CLUB.id = CAT.club_id  "
            . " WHERE CAT.is_workgroup = 1  AND CLUB.club_type IN ('federation', 'standard_club') GROUP BY ROLE_FUN.role_id "); //CLUB.id  = 8573
        $functions = $functionsQry->fetchAll();
        foreach ($functions as $functiondetails) { 
//            $testQuery = "select group_concat(sort_order separator ', ') as test from fg_rm_function WHERE id IN (".$functiondetails['functionIds'].") AND is_federation = 0 ORDER BY sort_order ASC";
//            $test = $this->conn->query($testQuery)->fetchAll();
//            echo "<pre>$testQuery  {$functiondetails['role_id']}<br />"; print_r($test[0]['test']);
            
            //non club executive board functions
            $updateQuery = "SET @a = 0;  
            UPDATE `fg_rm_function` SET `sort_order` = @a:=@a+1  WHERE id IN (".$functiondetails['functionIds'].") AND is_federation = 0 "
                . " ORDER BY sort_order ASC, id ASC ";
            $this->conn->exec($updateQuery);
            file_put_contents($this->log, $updateQuery."\n", FILE_APPEND);
            
//            $testQuery = "select group_concat(sort_order separator ', ') as test from fg_rm_function WHERE id IN (".$functiondetails['functionIds'].") AND is_federation = 0 ORDER BY sort_order ASC";
//            $test = $this->conn->query($testQuery)->fetchAll();
//            echo "<br />"; print_r($test[0]['test']);
//            echo "<br />==================================================<br /><br />";
            
//            $testQuery = "select group_concat(sort_order separator ', ') as test from fg_rm_function WHERE id IN (".$functiondetails['functionIds'].") AND is_federation = 1 ORDER BY sort_order ASC";
//            $test = $this->conn->query($testQuery)->fetchAll();
//            echo "<pre>$testQuery  {$functiondetails['role_id']}<br />"; print_r($test[0]['test']);
            
            //club executive board functions
            $updateQuery = "SET @a = 0;  
            UPDATE `fg_rm_function` SET `sort_order` = @a:=@a+1  WHERE id IN (".$functiondetails['functionIds'].") AND is_federation = 1 "
                . " ORDER BY sort_order ASC, id ASC ";
            $this->conn->exec($updateQuery);
            file_put_contents($this->log, $updateQuery."\n", FILE_APPEND);
//            $testQuery = "select group_concat(sort_order separator ', ') as test from fg_rm_function WHERE id IN (".$functiondetails['functionIds'].") AND is_federation = 1 ORDER BY sort_order ASC";
//            $test = $this->conn->query($testQuery)->fetchAll();
//            echo "<br />"; print_r($test[0]['test']);
//            echo "<br />==================================================<br /><br />";
            
        }
     
    }
    
    /**
     * Function to correct workgroup fun sort (sub fed and club levels)
     */
    private function correctLowerWorkgroupFunSort(){

        $functionsQry = $this->conn->query(" SELECT GROUP_CONCAT(function_id) as functionIds, CLUB.federation_id, role_id, ROLE.title, CAT.title as categoryTitle, CAT.is_team as isTeamCat, CAT.is_workgroup as isWorkgroupCat, CLUB.title , CAT.club_id "
            . " FROM `fg_rm_role_function` ROLE_FUN "
            . " INNER JOIN fg_rm_role ROLE ON ROLE.id = ROLE_FUN.role_id "
            . " INNER JOIN fg_rm_category CAT ON CAT.id = ROLE.category_id "
            . " INNER JOIN fg_club CLUB ON CLUB.id = CAT.club_id  "
            . " WHERE CAT.is_workgroup = 1  AND CLUB.club_type IN ('sub_federation_club', 'federation_club', 'sub_federation') GROUP BY ROLE_FUN.role_id "); //CLUB.id  = 8573
        $functions = $functionsQry->fetchAll();;
        foreach ($functions as $functiondetails) { 
            $countQuery = "select COUNT(Distinct FUN.id) as count from fg_rm_function FUN "
                . " INNER JOIN fg_rm_category CAT ON CAT.id = FUN.category_id "
                . " WHERE FUN.is_federation = 1 AND CAT.club_id = ".$functiondetails['federation_id'];
            $count = $this->conn->query($countQuery)->fetchAll();
            
//            $testQuery = "select group_concat(sort_order separator ', ') as test from fg_rm_function WHERE id IN (".$functiondetails['functionIds'].") AND is_federation = 0 ORDER BY sort_order ASC";
//            $test = $this->conn->query($testQuery)->fetchAll();
//            echo "<pre>$testQuery  {$functiondetails['role_id']}<br /> start--".$count[0]['count']."<br />"; print_r($test[0]['test']);
            
            //non club executive board functions
            $updateQuery = "SET @a = ".$count[0]['count'].";  
            UPDATE `fg_rm_function` SET `sort_order` = @a:=@a+1  WHERE id IN (".$functiondetails['functionIds'].") AND is_federation = 0 "
                . " ORDER BY sort_order ASC, id ASC ";
            $this->conn->exec($updateQuery);
            file_put_contents($this->log, $updateQuery."\n", FILE_APPEND);
            
//            $testQuery = "select group_concat(sort_order separator ', ') as test from fg_rm_function WHERE id IN (".$functiondetails['functionIds'].") AND is_federation = 0 ORDER BY sort_order ASC";
//            $test = $this->conn->query($testQuery)->fetchAll();
//            echo "<br />"; print_r($test[0]['test']);
//            echo "<br />==================================================<br /><br />";
            
        }
     
    }
    
    /**
     * Function to sort team roles
     */
    private function correctTeamRoleSort() {
        $roleQry = $this->conn->query(" SELECT GROUP_CONCAT(ROLE.id) as roleIds, TEAMCAT.title, CLUB.url_identifier   "
            . " FROM `fg_rm_role` ROLE "
            . " INNER JOIN fg_team_category TEAMCAT ON TEAMCAT.id = ROLE.team_category_id "
            . " INNER JOIN fg_club CLUB ON CLUB.id = TEAMCAT.club_id  "
            . " WHERE ROLE.team_category_id IS NOT NULL GROUP BY TEAMCAT.id "); //CLUB.id  = 8573
        $roles = $roleQry->fetchAll();        
        foreach ($roles as $rolesdetails) { 
//            $testQuery = "select group_concat(sort_order separator ', ') as test, group_concat(id separator ', ') as testids  from fg_rm_role WHERE id IN (".$rolesdetails['roleIds'].") AND team_category_id IS NOT NULL ORDER BY sort_order ASC";
//            $test = $this->conn->query($testQuery)->fetchAll();
//            echo "<pre> {$rolesdetails['title']}- {$rolesdetails['url_identifier']}<br />"; print_r($test[0]['test']);
            
            $updateQuery = "SET @a = 0; UPDATE `fg_rm_role` SET `sort_order` = @a:=@a+1  WHERE id IN (".$rolesdetails['roleIds'].") "
                . " AND team_category_id IS NOT NULL  ORDER BY sort_order ASC, id ASC ";            
            $this->conn->exec($updateQuery);
            file_put_contents($this->log, $updateQuery."\n", FILE_APPEND);
            
//            $testQuery = "select group_concat(sort_order separator ', ') as test, group_concat(id separator ', ') as testids  from fg_rm_role WHERE id IN (".$rolesdetails['roleIds'].") AND team_category_id IS NOT NULL ORDER BY sort_order ASC";
//            $test = $this->conn->query($testQuery)->fetchAll();
//            echo "<br />"; print_r($test[0]['test']);
//            echo "<br />==================================================<br /><br />";
        }
    }
    
    /**
     * Function to sort all roles other than teams
     */
    private function correctRoleSort() {
        $roleQry = $this->conn->query(" SELECT GROUP_CONCAT(ROLE.id) as roleIds, CAT.title, CLUB.url_identifier   "
            . " FROM `fg_rm_role` ROLE "
            . " INNER JOIN fg_rm_category CAT ON CAT.id = ROLE.category_id "
            . " INNER JOIN fg_club CLUB ON CLUB.id = CAT.club_id  "
            . " WHERE ROLE.team_category_id IS NULL GROUP BY CAT.id "); //CLUB.id  = 8573
        $roles = $roleQry->fetchAll();        
        foreach ($roles as $rolesdetails) { 
//            $testQuery = "select group_concat(sort_order separator ', ') as test from fg_rm_role WHERE id IN (".$rolesdetails['roleIds'].") AND team_category_id IS NULL ORDER BY sort_order ASC";
//            $test = $this->conn->query($testQuery)->fetchAll();
//            echo "<pre> {$rolesdetails['title']}- {$rolesdetails['url_identifier']}<br />"; print_r($test[0]['test']);
            
            $updateQuery = "SET @a = 0; UPDATE `fg_rm_role` SET `sort_order` = @a:=@a+1  WHERE id IN (".$rolesdetails['roleIds'].") "
                . " AND team_category_id IS NULL  ORDER BY sort_order ASC, id ASC ";            
            $this->conn->exec($updateQuery);
            file_put_contents($this->log, $updateQuery."\n", FILE_APPEND);
            
//            $testQuery = "select group_concat(sort_order separator ', ') as test from fg_rm_role WHERE id IN (".$rolesdetails['roleIds'].") AND team_category_id IS NULL ORDER BY sort_order ASC";
//            $test = $this->conn->query($testQuery)->fetchAll();
//            echo "<br />"; print_r($test[0]['test']);
//            echo "<br />==================================================<br /><br />";
        }
    }
    
}
