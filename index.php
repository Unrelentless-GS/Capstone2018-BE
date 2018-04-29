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
				<div class="card-body">
					<form method="POST" action="jukebox.php">
						<input type="hidden" name="txtServeType" value="HTML">
						<button type="submit" name="btnHost" id="btnHost">Host</button>
					</form>
					
					<br>
					
					<form method="POST" action="jukebox.php">
						<input type="hidden" name="txtServeType" value="HTML">
						<button type="submit" name="btnGuest" id="btnGuest">Guest</button>
					</form>
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
