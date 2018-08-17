<?php
	/*
	Written by Alden Viljoen
	*/
	
	require_once("data/auth.php");
	require_once("data/party.php");

	//Ends party
	if(isset($_POST["endParty"])) {
		$PARTY->EndParty($_POST["PartyID"]);
	}

	//Removes user from party
	if(isset($_POST["leaveParty"])) {
		$PARTY->LeaveParty($_POST["PartyID"],$_POST["UserID"]);
	}
?>

<!DOCTYPE html>
<html lang="en">
<head>
	<meta charset="utf-8">
	<meta http-equiv="X-UA-Compatible" content="IE=edge">
	<meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
	<title>Spotify Jukebox</title>
	<!-- Latest compiled and minified CSS -->
	<link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap.min.css">

	<script defer src="https://use.fontawesome.com/releases/v5.0.10/js/all.js" integrity="sha384-slN8GvtUJGnv6ca26v8EzVaR9DC58QEwsIk9q1QXdCU8Yu8ck/tL/5szYlBbqmS+" crossorigin="anonymous"></script>

	<!-- jQuery library -->
	<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.3.1/jquery.min.js"></script>

	<!-- Latest compiled JavaScript -->
	<script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/js/bootstrap.min.js"></script>
	<link href="css/style.css" rel="stylesheet">
</head>
<body>
	<div class="container">
		<section class="cols-xs-12 login-content login-selector section">
			<img class="logo-loginpage" src="Spotify_Logo_RGB_Green.png" />
			<table class="login-choice login-list">
				<div>
					<form method="POST" action="jukebox.php">
						<input type="hidden" name="txtServeType" value="HTML">
						<button type="submit" class = "button-hostlogin" name="btnHost" id="btnHost">Host a party!</button>
					</form>
				</div>
				<br></br>
				<div>
					<form method="POST" action="jukebox.php">
						<input type="hidden" name="txtServeType" value="HTML">
						<button type="submit" class = "button-guestlogin" name="btnGuest" id="btnGuest">Join a party!</button>
					</form>
				</div>
			</table>
		</section>
	</div>
</body>
</html>