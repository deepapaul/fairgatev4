<?php
error_reporting(0);
$url = $_SERVER['REQUEST_URI'];

$urlarray = explode('/', $url);
$filename = $urlarray[5]; //always in 5th
$type = $urlarray[7];
$clubId = $urlarray[9];
// $documentRoot = 'D:/xampp/htdocs/FairGate/web/';   // Local path
$documentRoot =  '/var/www/faigate_v4/web/';
if ($type == 'contact') {
    $storeFolder = $documentRoot.'uploads/' . $clubId . '/contact/' . $filename;
}
//$size = filesize($storeFolder);
$result = array();
$result['name'] = $filename;
$result['size'] = $size;

echo json_encode($result);
?>