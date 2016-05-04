<?php
	include "db.php";

	// Create connection
	$conn = new mysqli($servername, $username, $password, $dbname);
	if ($conn->connect_error) {
		die("Connection failed: " . $conn->connect_error);
	}

	$u_mac = $_GET['source'];
	$b_mac = $_GET['beacon'];
	$validUser = FALSE;
	$validBeacon = FALSE;

	$u_mac = mysqli_real_escape_string($conn, $u_mac);
	$b_mac = mysqli_real_escape_string($conn, $b_mac);


	if (isset($_GET['source']) && isset($_GET['beacon'])){
		$query = "SELECT * FROM `user_device` WHERE `user_mac` = '$u_mac'";
		$query2 = "SELECT * FROM `beacon_device` WHERE `beacon_mac` = '$b_mac'";
		$result = $conn->query($query);
		$result2 = $conn->query($query2);
		$row = $result->fetch_assoc();
		$row2 = $result2->fetch_assoc();

		if ($row != NULL){
			$validUser = TRUE;
		}

		if ($row2 != NULL){
			$validBeacon = TRUE;
		}
	}


	if ($validUser == TRUE && $validBeacon == TRUE) {
		$conn->query("INSERT INTO `access_log`(`beacon_mac`, `user_mac`) VALUES ('$b_mac','$u_mac')");
	}
?>
