<?php

/**
 * This class is used for handling newsletter migration process
 *
 * @author pitsolutions.com
 */
class SubscriberLangUpdation
{

    private $conn;
    private $log;

    /**
     * Constructor for initial setting.
     *
     * @param type $conn   connection object
     */
    public function __construct($conn)
    {
        $this->conn = $conn;
        $this->log = fopen('subscriber_updation_lang_log_' . date('dHis') . '.txt', 'w');
    }

    /**
     * Function to init subscriber correspondance lang updation
     *
     * @throws exception
     */
    public function InitSubscriberLangUpdation()
    {
        try {
            $this->conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            $this->conn->beginTransaction();
            $this->writeLog("---- SUBSCRIBER CORRESPONDANCE LANGUAGE UPDATION STARTS ----");
            $this->updateCorrespondenceLangOfSubscribers();
            $this->writeLog("---- SUBSCRIBER CORRESPONDANCE LANGUAGE UPDATION ENDS ----");
            $this->conn->commit();
        } catch (Exception $ex) {
            $this->conn->rollback();
            echo "Failed: " . $ex->getMessage();
            throw $ex;
        }
    }

    /**
     * Function to get club_ids with additional subscribers
     */
    public function updateCorrespondenceLangOfSubscribers()
    {
        $statement1 = $this->conn->query("SELECT DISTINCT S.`club_id`, C.`federation_id` FROM `fg_cn_subscriber` S INNER JOIN `fg_club` C ON (S.club_id = C.id) WHERE S.`lang_updated` = 0 LIMIT 0,10;");
        $resultArr1 = $statement1->fetchAll(\PDO::FETCH_ASSOC);
        foreach ($resultArr1 as $result1) {
            $this->writeLog("---- LANGUAGE UPDATION OF " . $result1['club_id'] . " STARTS ----");
            $lang = $this->getClubDefaultLang($result1['club_id'], $result1['federation_id']);
            
            $this->updateLangOfSubscriber($result1['club_id'], $lang);
            $this->writeLog("---- LANGUAGE UPDATION OF " . $result1['club_id'] . " ENDS ----");
        }
    }

    /**
     * This function is used to get the club default language
     */
    public function getClubDefaultLang($clubId, $federationId)
    {
        $federationQry = ($federationId > 1) ? "CL.club_id = $federationId" : "CL.club_id = $clubId";
        $statement2 = $this->conn->query("SELECT CL.correspondance_lang FROM `fg_club_language` CL INNER JOIN `fg_club_language_settings` CLS ON (CLS.club_language_id = CL.id) "
            . "WHERE " . $federationQry . " AND CLS.club_id = " . $clubId . " AND CLS.is_active = 1 ORDER BY CLS.sort_order ASC, CL.id ASC LIMIT 1");
        $result2 = $statement2->fetchAll(\PDO::FETCH_COLUMN);
        $this->writeLog("---- DEFAULT LANG OF CLUB " . $result1['club_id'] . " IS " . $result2[0] . " ----");
        
        return $result2[0];
    }

    /**
     * Function to update subscriber lang and insert corresponding log entry
     */
    public function updateLangOfSubscriber($clubId, $lang)
    {
        $this->conn->exec("INSERT INTO fg_cn_subscriber_log (subscriber_id, club_id, date, kind, field, value_before, value_after, changed_by) SELECT S.id, S.club_id, S.created_at, 'data', 'correspondance_lang', '', '" . $lang . "', 1 FROM fg_cn_subscriber S WHERE S.club_id = " . $clubId . " AND S.lang_updated = 0");
        $this->writeLog("---- SUBSCRIBER LOG ENTRIES UPDATED ----");
        $this->conn->exec("UPDATE fg_cn_subscriber S SET S.correspondance_lang = '$lang', S.lang_updated = 1 WHERE S.club_id = $clubId");
        $this->writeLog("---- SUBSCRIBER CORRESPONDANCE LANGUAGE UPDATED ----");
    }

    /**
     * This function is used to log the messages
     */
    private function writeLog($msg)
    {
        fwrite($this->log, $msg);
        echo nl2br($msg);
    }
}
