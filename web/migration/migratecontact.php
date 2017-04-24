<?php

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
require_once ('PortraitMigration.php');
//require_once ('subscriberUpdates.php');

if ($_GET['key'] == date('Ymd')) {
    set_time_limit(0);
    try {
        $dbh = new PDO('mysql:host=192.168.0.203;port=3306;dbname=fairgate_migrate;charset=UTF8;', 'admin', 'admin123', array(PDO::ATTR_PERSISTENT => true));
        echo "Connected<br/>";
    } catch (Exception $e) {
        die("Unable to connect: " . $e->getMessage());
    }
/////////////////////////////////////////////////////////// SWETHA'S SCRIPT /////////////////////////////////////////////////////////////////////////////
//    $clubDataMigration = new ClubDataMigration($dbh);
//    $clubDataMigration->initClubDataMigration();
//    echo "Club data migrated successfully";
//    
////////////////////////////////////////////////////////////////////PRIYESH'S SCRIPT/////////////////////////////////////////////////////////////////////////
//    for($step=1;$step<15;$step++){
//        $migration =  new ContactMigration($dbh);
//        $migration->InitMigration($step);
//    }
//    echo "Contact Success";
//    
////////////////////////////////////////////////////////////////////NEETHU"S SCRIPT/////////////////////////////////////////////////////////////////////////
//    $membershipMigration = new membershipRelatedMigration($dbh);
//    $membershipMigration->initMigration();
//    echo "Membership Success";
//
///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

//    for($step=1;$step<3;$step++){
//        $migrationAssignment =  new AssignmentMigration($dbh);
//        $migrationAssignment->InitAssignmentMigration($step);
//    }
//    echo "Assignment Success";
//
//    $contactConnectionMigration = new ContactConnectionMigration($dbh);
//    $contactConnectionMigration->initContactConnectionMigration();
//    echo "Connection Success";
//
//    $documentMigration = new DocumentMigration($dbh);
//    $documentMigration->InitDocumentMigration();
//    echo "Documents was migrated successfully";
//
//    $notesmigration =  new NotesMigration($dbh);
//    $notesmigration->InitNotesMigration();
//    echo "Notes Success";
//
//    $contactBookmarksMigration =  new ContactBookmarksMigration($dbh);
//    $contactBookmarksMigration->InitContactBookmarkMigration();
//    echo "Bookmarks Success";
//
//    $migrationfederationicon =  new FederationIconMigration($dbh);
//    $migrationfederationicon->InitFederationIconMigration();
//    echo "FederationIcon Success";
//
//    $migrationCalendar =  new CalendarMigration($dbh);
//    $migrationCalendar->InitCalendarMigration();
//    echo "Calendar Success";
//
//    $migrationFilemamager =  new FilemanagerMigration($dbh);
//    $migrationFilemamager->InitFilemanagerMigration();
//    echo "<br/>Filemanager Success";
//
//    $migrationMessage =  new MessageMigration($dbh);
//    $migrationMessage->InitMessageMigration();
//    echo "Message Migration Success";
//
//    $contactMiscMigration =  new ContactMiscMigration($dbh);
//    $contactMiscMigration->InitContactMiscMigration();
//    echo "Contact Migration Success in miscellaneous areas";
//
//    $migrationGallery =  new GalleryMigration($dbh);
//    $migrationGallery->InitGalleryMigration();
//    echo "Gallery Migration Success";
//
//    $migrationservice =  new ServiceMigration($dbh);
//    $migrationservice->InitServiceMigration();
//    echo "Service Migration Success";
//
//    $newletter = new  NewsletterMigration($dbh);
//    $newletter->InitNewsletterMigration();
//    echo "SUCCESS";
//
//    $migrationForum =  new ForumMigration($dbh);
//    $migrationForum->InitForumMigration();
//    echo "Forum Migration Success";
    
//    $profilepicMigration =  new ProfilepicMigration($dbh);
//    $profilepicMigration->InitProfilePicMigration();
//    echo "ProfilePic/company logo/contact filed migration Success";
    
    $portraitMigration =  new PortraitMigration($dbh);
    $portraitMigration->InitPortraitMigration();
    $portraitMigration->InitProfilePicMigration();
    echo "ProfilePic/company logo/contact filed migration Success";

   // $subscriberUpdation =  new SubscriberLangUpdation($dbh);
   // $subscriberUpdation->InitSubscriberLangUpdation();
    echo "-- Correspondence lang of subsciber updated -- ";
} else {
    echo "Invalid key, Please use key " . date('Ymd');
}
exit;
