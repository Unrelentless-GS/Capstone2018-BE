<?php
	/*
	Written by Alden Viljoen
	*/
	
	require_once("data/auth.php");
?>

<!DOCTYPE html>
<html lang="en">
	<head>
		<meta charset="utf-8">
		<meta http-equiv="X-UA-Compatible" content="IE=edge">
		<meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
		<title>Spotify Jukebox</title>
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
				<div class="card-header">Spotify Jukebox</div>
				
				<?php if(isset($_COOKIE["Jukebox_Token"])): ?>
					<p>
						<?php
							require_once("authorized.php");
						?>
					</p>
				<?php elseif(isset($_GET["state"])): ?>
					<p>
						<?php
						global $AUTHENTICATION;
						
						$AUTHENTICATION->HandleUserDecision(
						
							/*
							Anonymous function handling the result of our access/refresh token request.
							In this block, we need to handle a JSON object containing the necessary new information.
							
							Access Token - The ticket!
							Expires In - A value (in seconds) indicating the life of the access token.
							Refresh Token - The ticket to refreshing an access token. Note: A new refresh token may also be returned!
							*/
							function ($response, $state) {
								if($response === FALSE) {
									/*
									The request failed, maybe some more delicate handling in the future? :)
									Like maybe 'at least its not Apple Music'
									*/
									
									print("POST request failed!");
									return;
								}

								$json = json_decode($response, TRUE);
								$accessToken 		= $json["access_token"];
								$tokenType 			= $json["token_type"];
								$permissions 		= $json["scope"];
								$expiresIn 			= $json["expires_in"];
								$refreshToken		= $json["refresh_token"];
								
								if(!isset($_COOKIE["Jukebox_Token"]) || $_COOKIE["Jukebox_Token"] != $accessToken) {
									print("Setting token");
									
									setcookie("Jukebox_Token", $accessToken);
									setcookie("Jukebox_RToken", $refreshToken);
									setcookie("Jukebox_Expiry", time() + $expiresIn);
									
									$GLOBALS["Jukebox_Token"] = $accessToken;
									$GLOBALS["Jukebox_RToken"] = $refreshToken;
								}
								
								require_once("authorized.php");
							},
							
							function ($reason) {
								print("The request failed! Reason: " . $reason);
							}
						);
						?>
					</p>
				<?php else: ?>
					<div class="card-body">
						<button name="btnAuthorize" id="btnAuthorize">Authorize</button>
					</div>
				<?php endif; ?>
			</div>
		</div>
		
		<script type="text/javascript" src="js/authorize.js"></script>
		<script type="text/javascript" src="js/search.js?13"></script>
		<!-- Bootstrap core JavaScript-->
		<script src="vendor/jquery/jquery.min.js"></script>
		<script src="vendor/bootstrap/js/bootstrap.bundle.min.js"></script>
		<!-- Core plugin JavaScript-->
		<script src="vendor/jquery-easing/jquery.easing.min.js"></script>
	</body>
</html>
