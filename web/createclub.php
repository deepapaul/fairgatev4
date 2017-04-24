<html>

<body>
	<link href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.6/css/bootstrap.min.css" rel="stylesheet"/>
	<form method="post">

		<table class="table table-bordered">
			<tr>
				<td width="40%">parentClubId</td>
				<td width="60%"><input  type="number" name="parentClubId" value="" size="20"/></td>
			</tr>
			<tr>
				<td>federationId</td>
				<td><input type="text" name="federationId" value=""/></td>
			</tr>
			<tr>
				<td>urlIdentifier</td>
				<td><input type="text" name="urlIdentifier" value=""/></td>
			</tr>
			<tr>
				<td>clubTitle</td>
				<td><input type="text" name="clubTitle" value=""/></td>
			</tr>
			<tr>
				<td>clubType</td>
				<td><input type="text" name="clubType" value=""/></td>
			</tr>
			<tr>
				<td>website</td>
				<td><input type="text" name="website" value=""/></td>
			</tr>
			<tr>
				<td>hasSubfederation</td>
				<td><input  type="number" min="0" max="1" name="hasSubfederation" value=""/></td>
			</tr>
			<tr>
				<td>clubMembershipAvailable <br/>(0-no, 1- yes)</td>
				<td><input type="number" min="0" max="1" name="clubMembershipAvailable" value=""/></td>
			</tr>
			<tr>
				<td>fedMembershipMandatory <br/>(0-non-mandatory, 1- mandatory)</td>
				<td><input type="number" min="0" max="1" name="fedMembershipMandatory" value=""/></td>
			</tr>
			
			<tr>
				<td>assignFedmembershipWithApplication <br/>(0-without application, 1- with application)</td>
				<td><input type="number" min="0" max="1" name="assignFedmembershipWithApplication" value=""/></td>
			</tr>
			<tr>
				<td>addExistingFedMemberClub <br/>(0 - not possible, 1- possible without application, 2 - possible with application)</td>
				<td><input type="number" min="0" max="2" name="addExistingFedMemberClub" value=""/></td>
			</tr>
			<tr>
				<td>fedAdminAccess <br/>(0-no, 1- yes)</td>
				<td><input type="number" min="0" max="1" name="fedAdminAccess" value=""/></td>
			</tr>
			<tr>
				<td></td>
				<td><input type="submit" name="create" value="Create"/></td>
			</tr>
		</table>
	</form>
</body>
</html>

<?php

if (count(array_filter($_POST)) > 1){

	$mysqli = new mysqli("192.168.0.39", "admin", "admin123", "fairgate_migrate");
	if ($mysqli->connect_errno) {
	    echo "Failed to connect to MySQL: (" . $mysqli->connect_errno . ") " . $mysqli->connect_error;
	}

	$parentClubId = $_POST['parentClubId'];
	$federationId = $_POST['federationId'];
	$urlIdentifier = $_POST['urlIdentifier'];
	$clubTitle = $_POST['clubTitle'];
	$clubType = $_POST['clubType'];
	$website = $_POST['website'];
	$hasSubfederation = $_POST['hasSubfederation'];
	$clubMembershipAvailable = $_POST['clubMembershipAvailable'];
	$fedMembershipMandatory = $_POST['fedMembershipMandatory'];
	$assignFedmembershipWithApplication = $_POST['assignFedmembershipWithApplication'];
	$addExistingFedMemberClub = $_POST['addExistingFedMemberClub'];
	$fedAdminAccess = $_POST['fedAdminAccess'];

	$q = "CALL V4createClub($parentClubId, $federationId, '$urlIdentifier', '$clubTitle', '$clubType', '$website', $hasSubfederation, $clubMembershipAvailable, $fedMembershipMandatory, $assignFedmembershipWithApplication, $addExistingFedMemberClub, $fedAdminAccess)";
	echo $q;
	if (!$mysqli->query($q)) {
	    echo "<br/>CALL failed: (" . $mysqli->errno . ") " . $mysqli->error;
	}
	$mysqli->close();
}