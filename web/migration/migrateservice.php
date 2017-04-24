<?php
//require_once ('ContactMigration.php');
//require_once ('AssignmentMigration.php');
require_once ('ServiceMigration.php');
if($_GET['key']==date('Ymd')){
    set_time_limit(0);
    try {
        $dbh = new PDO('mysql:host=192.168.0.39;port=3306;dbname=fairgatelive_backup;charset=UTF8;','admin','admin123', array(PDO::ATTR_PERSISTENT=>true));
  //      $dbh = new PDO('mysql:host=192.168.0.39;port=3306;dbname=fairgate_fedv2_qa;charset=UTF8;','admin','admin123', array(PDO::ATTR_PERSISTENT=>true));
        echo "Connected <br />";
    } catch (Exception $e) {
        die("Unable to connect: " . $e->getMessage());
    }
    $migrationservice =  new ServiceMigration($dbh);
    $migrationservice->InitServiceMigration();
    echo "Service Migration Success";
} else {
    echo "Invalid key, Please use key ".date('Ymd');

}
exit;
