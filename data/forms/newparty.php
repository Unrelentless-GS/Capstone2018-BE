<?php
	/*
	Summary
	Provides a form ready to be served, for gathering information about a user's party.
	This is where the name, settings and unique string will be decided and set.
	
	From here, a party instance can be created!
	Written by Alden Viljoen
	*/
	
	require_once("data/backend/funcs.php");
	require_once("data/party.php");

	if(!class_exists("CCreateParty")) {
		class CCreateParty {
			function __construct() {
				
			}
			
			/*
			Summary
			Serves a form requesting more information about a party.
			From here, the party is created.
			
			TODO: Get Host's nickname from here too.
			*/
			public function ServeForm($accessToken, $expiresIn, $refreshToken) {
				$id = $this->GetUserID($accessToken);
				
				?>
				<!DOCTYPE html>
				<html lang="en">
					<head>
						<meta charset="utf-8">
						<meta http-equiv="X-UA-Compatible" content="IE=edge">
						<meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
						<title>Spotify Jukebox - Start Hosting</title>
						<!-- Bootstrap core CSS-->
						<link href="vendor/bootstrap/css/bootstrap.min.css" rel="stylesheet">
						<!-- Custom fonts for this template-->
						<link href="vendor/font-awesome/css/font-awesome.min.css" rel="stylesheet" type="text/css">
						<!-- Custom styles for this template-->
						<link href="css/sb-admin.css" rel="stylesheet">
						<link href="css/style.css" rel="stylesheet">
					</head>

					<body>	
						<section class="cols-xs-12 login-content host-login section">
							<img class="logo-loginpage" src="iconAndName.png" />
							<table class="login-choice host-list">
								<div class="formwrapper">
									<?php
										//They're blocked from creating a party if they aren't using a 
										// Spotify Premium Account
										if ($id == null)
										{
											?>
											<h4>Spotify Premium is required to Host a party.</h4>
											<form method="POST" action="index.php">
												<input type="submit" value="Return Home">
											</form>
											<?php
										}
										else
										{
											?>
											<form method="POST" action="jukebox.php">
												<input type="hidden" name="txtAccessToken" value="<?php print($accessToken); ?>">
												<input type="hidden" name="txtExpiresIn" value="<?php print($expiresIn); ?>">
												<input type="hidden" name="txtRefreshToken" value="<?php print($refreshToken); ?>">
												<input type="hidden" name="txtUserID" value="<?php print($id); ?>">
												<input type="hidden" name="txtFinishCreatingParty" value="">
												
												<input type="text" name="txtNickname" id="txtNickname" placeholder="Your nickname.."> <br>
												<input type="submit" value="Create">
											</form>
											<?php
										}
									?>
								</div>
							</table>
						</section>
						
						<!-- Bootstrap core JavaScript-->
						<script src="vendor/jquery/jquery.min.js"></script>
						<script src="vendor/bootstrap/js/bootstrap.bundle.min.js"></script>
						<!-- Core plugin JavaScript-->
						<script src="vendor/jquery-easing/jquery.easing.min.js"></script>
					</body>
				</html>
				<?php
			}
			
			/*
			Summary
			Requests the user's ID from Spotify Web API.
			This is to properly enter the user into an ongoing party, should one be existing.
			Also verifies the user has a premium spotify account.
			*/
			private function GetUserID($accessToken) {
				global $JUKE;
				
				$result = $JUKE->GetRequest(
					"https://api.spotify.com/v1/me",
					
					array(
						"Accept: application/json",
						"Content-Type: application/json",
						"Authorization: Bearer " . $accessToken
					)
				);
				
				$obj = json_decode($result, TRUE);
				if ($obj["product"] == "premium")
				{
					return $obj["id"];
				}
				else
				{
					return null;
				}
			}
		}
	}
?>
