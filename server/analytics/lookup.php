<?php
	include "db.php";
	$macaddress = "";
	$report = "[{";

	// Create connection
	$conn = new mysqli($servername, $username, $password, $dbname);
	if ($conn->connect_error) {
		die("Connection failed: " . $conn->connect_error);
	}

	if(isset($_GET['lu_fullname'])){
		$fullName = $_GET['lu_fullname'];
		$fullName = mysqli_real_escape_string($conn, $fullName);
		$spaceLoc = strpos($fullName, " ");
		$spaceLoc = $spaceLoc + 1;
		$firstname = substr($fullName, 0, $spaceLoc - 1);
		$lastname = substr($fullName, $spaceLoc);

		$query = "SELECT `user_mac` FROM user_device WHERE `first_name` = '$firstname' AND `last_name` = '$lastname'";
		$result = $conn->query($query);
		$row = $result->fetch_assoc();
		$macaddress = $macaddress . $row["user_mac"];


	}elseif ($_GET['lu_mac']){
		$macaddress = $_GET['lu_mac'];

		$macaddress = mysqli_real_escape_string($conn, $macaddress);
	}

	$query = "Select `beacon_mac` from access_log where `user_mac` = '$macaddress'";
	$result = $conn->query($query);
	$macFormat = "";
	$seenArray = array();
	$CSV = "";
	while($row = $result->fetch_assoc()){
		if (in_array($row["beacon_mac"], $seenArray)){

		}else{
			$macFormat = $macFormat . '"mac":"' . $row["beacon_mac"] . '","nickname":"';
			$temp = $row["beacon_mac"];
			$query = "SELECT `user` from beacon_device where `beacon_mac` = '$temp'";
			$nameResult = $conn->query($query);
			$nameRow = $nameResult->fetch_assoc();
			$macFormat = $macFormat . $nameRow["user"] . '","timestamps":[';

			$temp = $row["beacon_mac"];
			$query = "Select `Timestamp` from access_log where `user_mac` = '$macaddress' and `beacon_mac` = '$temp'";
			$timeResult = $conn->query($query);
			while($row2 = $timeResult->fetch_assoc()){
				$macFormat = $macFormat . '"' . $row2['Timestamp'] . '",';
			}
			$length = strlen($macFormat);
			$macFormat = substr($macFormat,0, $length - 1);
			$macFormat = $macFormat . ']},{';
			array_push($seenArray, $row["beacon_mac"]);
		}
	}
	$length = strlen($macFormat);
	$macFormat = substr($macFormat,0, $length - 2);
	$report = $report . $macFormat;
	$report = $report . "]";
	echo $report;
?>
