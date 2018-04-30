<?php
	/*
	Summary
	Once a user is authorised into a party, and a cookie is set (either via Host or Guest)
	this form will be served, that provides all the options.
	
	Written by Alden Viljoen
	*/
	
	require_once("data/backend/funcs.php");
	require_once("data/party.php");

	if(!class_exists("CPartyForm")) {
		class CPartyForm {
			function __construct() {
				
			}

			public function ServeForm($userHash) {
				global $PARTY;
				$session = $PARTY->GetSessionInfo($userHash);
				
				?>
				<!DOCTYPE html>
				<html lang="en">
					<head>
						<meta charset="utf-8">
						<meta http-equiv="X-UA-Compatible" content="IE=edge">
						<meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
						<title>Spotify Jukebox - <?php print($session["PartyName"]); ?></title>
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
								<div class="card-header">Party Options</div>
								<div class="card-body">
									<label for="lblUniqueLink">Give this to your friends: </label>
									<label name="lblUniqueLink" id="lblUniqueLink">https://spotify-jukebox.viljoen.industries/join.php?ID=<?php print($session["PartyUniqueID"]); ?></label>
									
									<br>
									
									
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
				<?php
			}
		}
	}
?>
