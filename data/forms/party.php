<?php
	/*
	Summary
	Once a user is authorised into a party, and a cookie is set (either via Host or Guest)
	this form will be served, that provides all the options.
	
	Written by Alden Viljoen
	*/
	
	require_once("data/backend/funcs.php");
	require_once("data/party.php");
	require_once("data/playlist.php");

	if(!class_exists("CPartyForm")) {
		class CPartyForm {
			function __construct() {
				
			}

			public function ServeForm($userHash) {
				global $PARTY;
				$PLAYLIST = new CPlaylist;
				
				$session = $PARTY->GetSessionInfo($userHash);
				$songs = $PLAYLIST->GetPartySongs($session["PartyID"]);
				
				if(isset($_POST["btnPlay"])) {
					$PARTY->ChangeSongForParty(
						$_POST["PartyID"],
						$_POST["SongSpotifyID"]);
				}
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
									
									<div>
										<div>
											<table width="100%" border="1">
												<tr>
													<td><strong>Name</strong></td>
													<td><strong>Artist</strong></td>
													<td><strong>Votes</strong></td>
													<td><strong>Vote</strong></td>
													<td><strong>Play</strong></td>
												</tr>
												
												<?php
													if($songs != NULL) {
														while($song = $PLAYLIST->GetRow($songs)) {
															?>
															<tr>
																<td>
																	<?php
																	print($song["SongName"]);
																	?>
																</td>
																
																<td>
																	<?php
																	print($song["SongArtists"]);
																	?>
																</td>
																
																<td>
																	<?php
																	print($song["VoteCount"]);
																	?>
																</td>
																	
																<td>
																	<form id="frmVoteUp" method="POST" action="vote.php">
																		<input type="hidden" name="SongID" id="SongID" value="<?php print($song["SongID"]); ?>">
																		<input type="hidden" name="Action" id="Action" value="Voting">
																		<input type="hidden" name="Value" id="Value" value="1">
																		
																		<button type="submit" name="btnVoteUp" id="btnVoteUp">▲</button>
																	</form>
																	
																	<form id="frmVoteDown" method="POST" action="vote.php">
																		<input type="hidden" name="SongID" id="SongID" value="<?php print($song["SongID"]); ?>">
																		<input type="hidden" name="Action" id="Action" value="Voting">
																		<input type="hidden" name="Value" id="Value" value="0">
																		
																		<button type="submit" name="btnVoteDown" id="btnVoteDown">▼</button>
																	</form>
																</td>

																<td>
																	<form action="" method="POST">
																		<input type="hidden" name="SongID" id="SongID" value="<?php print($song["SongID"]); ?>">
																		<input type="hidden" name="PartyID" id="PartyID" value="<?php print($session["PartyID"]); ?>">
																		<input type="hidden" name="SongSpotifyID" id="SongSpotifyID" value="<?php print($song["SongSpotifyID"]); ?>">
																		
																		<button type="submit" name="btnPlay">Play</button>
																	</form>
																</td>
															</tr>
														<?php
														}
													}
												?>
											</table>
										</div>
										
										<div>
											<input type="hidden" name="Mode" id="Mode" value="AuthorisationCode">
											<input type="hidden" name="Type" id="Type" value="track">
											
											<input type="text" name="Term" id="Term">
											<button name="btnAdd" id="btnAdd" type="submit">Add First Song</button>
										</div>
									</div>
								</div>
								</div>
							</div>
						</div>
						
						<!-- Bootstrap core JavaScript-->
						<script src="vendor/jquery/jquery.min.js"></script>
						<script src="vendor/bootstrap/js/bootstrap.bundle.min.js"></script>
						<!-- Core plugin JavaScript-->
						<script src="vendor/jquery-easing/jquery.easing.min.js"></script>
						<script src="js/jukebox.js?3"></script>
					</body>
				</html>
				<?php
			}
		}
	}
?>
