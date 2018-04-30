<!DOCTYPE html>
<html lang="en">
	<head>
		<meta charset="utf-8">
		<meta http-equiv="X-UA-Compatible" content="IE=edge">
		<meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
		<title>Spotify Jukebox - Join a Party</title>
		<!-- Bootstrap core CSS-->
		<link href="vendor/bootstrap/css/bootstrap.min.css" rel="stylesheet">
		<!-- Custom fonts for this template-->
		<link href="vendor/font-awesome/css/font-awesome.min.css" rel="stylesheet" type="text/css">
		<!-- Custom styles for this template-->
		<link href="css/sb-admin.css" rel="stylesheet">
	</head>

	<body class="bg-dark">
		<div class="container">
			<div class="card  mx-auto mt-5">
				<div class="card-header">Join a Party</div>
				<div class="card-body">
					<?php
						/*
						Summary
						Provides a form for both getting the guest's username AND getting a guests target room ID.
						Written by Alden Viljoen
						*/
						
						require_once("data/backend/funcs.php");
						require_once("data/party.php");

						if(!class_exists("CJoinParty")) {
							class CJoinParty {
								function __construct() {
									
								}
								
								public function GetUsername($party) {
									// We already have the party the user wants to join,
									// we just need their personal info now.
									
									?>
									<form method="POST" action="join.php">
										<input type="hidden" name="PartyID" value="<?php print($party["PartyID"]); ?>">
										
										<label for="txtNickname">Nickname: </label>
										<input type="text" name="txtNickname" id="txtNickname">
										
										<button type="submit" name="btnJoin">Join</button>
									</form>
									<?php
								}
								
								public function RequestPartyID() {
									// The user wants to join a party. Which one though?
									
									?>
									<form method="GET" action="join.php">
										<label for="ID">Party Unique ID: </label>
										<input type="text" name="ID" id="ID">
										
										<button type="submit" name="btnJoin">Join</button>
									</form>
									<?php
								}
							}
						}
					?>
				</div>
			</div>
		</div>
		
		<!-- Bootstrap core JavaScript-->
		<script src="vendor/jquery/jquery.min.js"></script>
		<script src="vendor/bootstrap/js/bootstrap.bundle.min.js"></script>
		<!-- Core plugin JavaScript-->
		<script src="vendor/jquery-easing/jquery.easing.min.js"></script>
	</body>
</html>