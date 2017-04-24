<?php
require_once ('FunctionSortOrderCorrection.php');
if($_GET['key']==date('Ymd')){
    set_time_limit(0);
    try {
        $dbh = new PDO('mysql:host=192.168.0.203;dbname=fairgate_migrate_new;charset=UTF8;','admin','admin123', array(PDO::ATTR_PERSISTENT=>true));
        echo "Connected <br />";
    } catch (Exception $e) {
        die("Unable to connect: " . $e->getMessage());
    }
    
    $functionCorrection =  new FunctionSortOrderCorrection($dbh);
    $functionCorrection->correctSortOrder();
    echo "Function corrected";
    
} else {
    echo "Invalid key, Please use key ".date('Ymd');
}
exit;
