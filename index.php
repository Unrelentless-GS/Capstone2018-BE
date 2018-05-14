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
	<!-- Latest compiled and minified CSS -->
	<link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap.min.css">

	<script defer src="https://use.fontawesome.com/releases/v5.0.10/js/all.js" integrity="sha384-slN8GvtUJGnv6ca26v8EzVaR9DC58QEwsIk9q1QXdCU8Yu8ck/tL/5szYlBbqmS+" crossorigin="anonymous"></script>

	<!-- jQuery library -->
	<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.3.1/jquery.min.js"></script>

	<!-- Latest compiled JavaScript -->
	<script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/js/bootstrap.min.js"></script>
	<script src="js/spotifyJS.js?v=3"></script>
	<link href="css/style.css" rel="stylesheet">
</head>
<body>
	<div class="container">
		<img class="logo-loginpage" src="Spotify_Logo_RGB_Green.png" />
		<section class="cols-xs-12 login-content login-selector section">
			<table class="login-choice login-list">
				<div>
					<button type="button" class = "button-hostlogin">Host a party!</button>
				</div>
				<br></br>
				<div>
					<button type="button" class = "button-guestlogin">Join a party!</button>
				</div>
			</table>
		</section>
		<section class="cols-xs-12 login-content host-login section">
			<table class="login-choice host-list">
				<div>
					<form method="POST" action="jukebox.php">
						<input type="hidden" name="txtServeType" value="HTML">
						<button type="submit" class = "button-hostloginconfirm" name="btnHost" id="btnHost">Login</button>
					</form>
				</div>
			</table>
		</section>
		<section class="cols-xs-12 login-content guest-login section">
			<table class="login-choice guest-list">
				<div>
					<form method="POST" action="jukebox.php">
						<input type="hidden" name="txtServeType" value="HTML">
						<button type="submit" class = "button-hostloginconfirm" name="btnGuest" id="btnGuest">Login</button>
					</form>
				</div>
			</table>
		</section>
	</div>
</body>
</html>