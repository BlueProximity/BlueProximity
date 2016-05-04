<?php
	include "db.php";
	$macReg = '/^([0-9A-F]{2}[:-]{0,1}){5}[0-9A-F]{2}$/i';

	$errorArray = array();

	$conn = new mysqli($servername, $username, $password, $dbname);

	if (isset($_POST["ogusermac"])){
		$firstname = $_POST["firstname"];
		$lastname = $_POST["lastname"];
		$mac = $_POST["usermac"];
		$oldmac = $_POST["ogusermac"];
		$validFirstName = FALSE;
		$validLastName = FALSE;
		$validMac = FALSE;

		//Strips SQL Injection
		$firstname = mysqli_real_escape_string($conn, $firstname);
		$lastname = mysqli_real_escape_string($conn, $lastname);
		$mac = mysqli_real_escape_string($conn, $mac);

		if(str_word_count($firstname) == 1){
			$validFirstName = TRUE;
		}
		if(str_word_count($lastname) == 1){
			$validLastName = TRUE;
		}

		if(preg_match($macReg, $mac)){
			$validMac = TRUE;
		}

		if(strpos($mac, ":")&& strpos($mac, "-") == FALSE){
			$validMac = FALSE;
		}
		$query = "UPDATE `user_device` SET `user_mac`='$mac',`first_name`='$firstname', `last_name`= '$lastname' WHERE `user_mac` = '$oldmac'";

		if($validFirstName == FALSE){
			$errorArray['Error'] = "Invalid first name $firstname + $lastname";

		}elseif($validLastName == FALSE){
			$errorArray['Error'] = "Invalid last name";

		}elseif($validMac == FALSE){
			$errorArray['Error'] = "Invalid mac";

		}elseif ($conn->query($query) === TRUE) {
    		$errorArray['Success'] = "User succesfully modified";

		} else {
			$errorArray['Error'] = "An error occurred";
		}
	}else if(isset($_POST["ogbeaconmac"])){
		$name = $_POST["beaconname"];
		$mac = $_POST["beaconmac"];
		$oldmac = $_POST['ogbeaconmac'];
		$validName = FALSE;
		$validMac = FALSE;

		//Strips SQL Injection
		$name = mysqli_real_escape_string($conn, $name);
		$mac = mysqli_real_escape_string($conn, $mac);

		if(str_word_count($name) >= 1){
			$validName = TRUE;
		}

		if(preg_match($macReg, $mac)){
			$validMac = TRUE;
		}

		if(strpos($mac, ":")&& strpos($mac, "-") == FALSE){
			$validMac = FALSE;
		}

		$query = "UPDATE `beacon_device` SET `beacon_mac`='$mac',`user`='$name' WHERE `beacon_mac` = '$oldmac'";

		if($validName == FALSE){
			$errorArray['Error'] = "Invalid beacon name";

		}elseif($validMac == FALSE){
			$errorArray['Error'] = "Invalid beacon mac";

		} elseif ($conn->query($query) === TRUE) {
    		$errorArray['Success'] = "Beacon succesfully modified";

		} else{
			$errorArray['Error'] = "An error occurred";

		}
	}

	//view users
	$query = "SELECT * FROM `user_device` WHERE 1";
	$user_result = $conn->query($query);

	// View Devices
	$query = "SELECT * FROM `beacon_device` WHERE 1";
	$beacon_result = $conn->query($query);
	$conn->close();
?>
<html>
<script src="https://ajax.googleapis.com/ajax/libs/jquery/2.1.3/jquery.min.js"></script>
<script>
$(function(){
	$(".content").click(function(){
		$(this).hide();
		$(this).next().show();

	});
});
</script>

<head>
    <link rel="stylesheet" type="text/css" href="splash.css">
	<link rel="stylesheet" type="text/css" href="manage_users.css">
	<link rel="stylesheet" type="text/css" href="common.css">
</head>

<body>
	<div id="top_bar">
		<div class='padded_container'>
			<img id='logo' src='image/logo.png' onclick='window.location="analytics.html"'/>
		</div>
	</div>

	<div id="page_container">
		<div id="page_title">Manage Users</div>
		<div id="main_text">
			<?php
				if(isset($errorArray['Error'])){
					echo("<div id='error_message'>" . $errorArray['Error'] . "</div>");
				}else if (isset($errorArray['Success'])){
					echo("<div id='success_message'>" . $errorArray['Success'] . "</div>");
				}else{
					echo("<div id='success_message'><br>"."</div>");
				}
			?>

			<div id="table_title">
				Users:
			</div>
			<?php
				while($row = $user_result->fetch_assoc()){
					echo("<div class='content'>" . $row['first_name'] . " " . $row['last_name'] . " " . $row['user_mac'] . "</div>");
					echo("<div class='content2'><form action='manage_users.php' method='post'>" . "<input type='text' name ='firstname' value='" . $row['first_name'] . "'> <input type='text' name='lastname' value='" . $row['last_name'] . "'> <input type='text' name='usermac' value='" . $row['user_mac'] . "'> <input type='hidden' name='ogusermac' value='" . $row['user_mac'] . "'> <input type='submit' value='Submit'></form></div>");
				}
			?>
			<div style="padding: 10px"></div>
			<div id="table_title">
				Beacons:
			</div>
			<?php
				while($row = $beacon_result->fetch_assoc()){
					echo("<div class='content'>" . $row['user'] . " " . $row['beacon_mac'] . "</div>");
					echo("<div class='content2'><form action='manage_users.php' method='post'>" . "<input type='text' name='beaconname' value='". $row['user'] . "'> <input type='text' name='beaconmac'value='" . $row['beacon_mac'] . "'> <input type='hidden' name='ogbeaconmac'value='" . $row['beacon_mac'] . "'> <input type='submit' value='Submit'></form></div>");
				}
			?>
		</div>
	</div>

</body>

</html>
