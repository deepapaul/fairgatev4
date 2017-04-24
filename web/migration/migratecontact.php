<?php
ini_set('error_reporting', E_ALL);
//require_once ('ClubDataMigration.php');
//require_once ('ContactMigration.php');
//require_once ('membershipRelatedMigration.php');
//require_once ('AssignmentMigration.php');
//require_once ('ContactConnectionMigration.php');
//require_once ('DocumentMigration.php');
//require_once ('NotesMigration.php');
//require_once ('ContactBookmarksMigration.php');
//require_once ('FederationIconMigration.php');
//require_once ('CalendarMigration.php');
//require_once ('MessageMigration.php');
//require_once ('ContactMiscMigration.php');
//require_once ('FilemanagerMigration.php');
//require_once ('GalleryMigration.php');
//require_once ('ForumMigration.php');
//require_once('NewletterMigration.php');
//require_once ('ServiceMigration.php');
//require_once ('ProfilepicMigration.php');
//require_once ('PortraitMigration.php');
//require_once ('subscriberUpdates.php');
require_once("DocumentCountMigration.php");

if ($_GET['key'] == date('Ymd')) {
    set_time_limit(0);
    try {
        echo "1";
//        $dbh = new PDO('mysql:host=192.168.0.203;port=3306;dbname=fairgate_migrate_new;charset=UTF8;', 'admin', 'admin123', array(PDO::ATTR_PERSISTENT => true));
//        echo "Connected1<br/>";
//        $dbh1= new PDO('mysql:host=192.168.0.203;port=3306;dbname=fairgate_admin;charset=UTF8;', 'admin', 'admin123', array(PDO::ATTR_PERSISTENT => true));
//        echo "Connected2<br/>";
        $dbh = mysqli_connect('192.168.0.203', 'admin', 'admin123','fairgate_admin_ui');
        $dbh1 = mysqli_connect('192.168.0.203', 'admin', 'admin123','fairgate_migrate_new');
         echo "Connected1<br/>";
       
    $documentCountMigration =  new DocumentCountMigration($dbh, $dbh1);
    $documentCountMigration->InitDocumentCountMigration();
        
        
    } catch (Exception $e) {
        die("Unable to connect: " . $e->getMessage());
    }

    echo "fgfgfgfg";
   

    echo "ProfilePic/company logo/contact filed migration Success";

   // $subscriberUpdation =  new SubscriberLangUpdation($dbh);
   // $subscriberUpdation->InitSubscriberLangUpdation();
    echo "-- Correspondence lang of subsciber updated -- ";
} else {
    echo "Invalid key, Please use key " . date('Ymd');
}
exit;
