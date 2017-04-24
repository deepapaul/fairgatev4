<?php
$fileName = $_POST['filename'];
$option = $_POST['option'];
//$folderLocation = '/var/www/html/fairgate_nfs/';
$folderLocation = '/opt/tempfolder/';
try{
	if(!empty($fileName) && !empty($option)){
		$command = 'scan "'. $folderLocation . $fileName . '" ' . $option;
		$result = shell_exec( $command );
	} else {
		$result = '[ERROR]';
	}
} catch(Exception $e){
	$result = '[EXCEPTION]';
}
echo $result;
?>