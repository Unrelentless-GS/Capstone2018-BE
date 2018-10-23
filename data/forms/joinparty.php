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
		<link href="css/style.css" rel="stylesheet">
	</head>

	<body>
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
						<section class="cols-xs-12 login-content guest-login section">
							<img class="logo-loginpage" src="iconAndName.png" />
							<table class="login-choice guest-list">
								<div class="formwrapper">
									<form method="POST" action="join.php">
										<input type="hidden" name="PartyID" value="<?php print($party["PartyID"]); ?>">
										<input type="text" name="txtNickname" id="txtNickname" placeholder="Your nickname.."> <br>
									 	<input type="submit" value="Join">
									</form>
								</div>
							</table>
						</section>
						<?php
					}
					
					public function RequestPartyID($incorrectID) {
						// The user wants to join a party. Which one though?
						
						?>
						<section class="cols-xs-12 login-content guest-login section">
							<img class="logo-loginpage" src="iconAndName.png" />
							<table class="login-choice guest-list">
								<?php
									if ($incorrectID)
									{
										?><p id=incorrectID>Incorrect Party ID</p><?php
									} 
								?>
								<div class="formwrapper">
									<form method="GET" action="join.php">
										<input type="text" name="ID" id="ID" placeholder="Party Unique ID"><br>
									 	<input type="submit" name="btnJoin" value="Join">
									</form>
								</div>
							</table>
						</section>
						<?php
					}
				}
			}
		?>

		<!-- Bootstrap core JavaScript-->
		<script src="vendor/jquery/jquery.min.js"></script>
		<script src="vendor/bootstrap/js/bootstrap.bundle.min.js"></script>
		<!-- Core plugin JavaScript-->
		<script src="vendor/jquery-easing/jquery.easing.min.js"></script>
	</body>
</html>