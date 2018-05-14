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
					<title>Spotify Jukebox</title>

					<!-- Latest compiled and minified CSS -->
					<link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap.min.css">
				
					<script src="https://use.fontawesome.com/releases/v5.0.10/js/all.js" integrity="sha384-slN8GvtUJGnv6ca26v8EzVaR9DC58QEwsIk9q1QXdCU8Yu8ck/tL/5szYlBbqmS+" crossorigin="anonymous"></script>
				
					<!-- jQuery library -->
					<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.3.1/jquery.min.js"></script>
				
					<!-- Latest compiled JavaScript -->
					<script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/js/bootstrap.min.js"></script>
					<script src="js/spotifyJS.js?v=2"></script>
					<link href="css/style.css" rel="stylesheet">
				</head>
				<body>
					<div class="container">
						<div class="row main-section">
							<div class="col-xs-12 main-row">
								<div class="row search-row">
									<div class="col-xs-2">
										<img class="logo" src="Spotify_Logo_RGB_Green.png" />
									</div>
									<div class="col-xs-10 searchbar section">
										<div class="navbar-form" role="search">
											<div class="input-group add-on">
												<input type="hidden" name="Mode" id="Mode" value="AuthorisationCode">
												<input type="hidden" name="Type" id="Type" value="track">
												<input class="form-control" placeholder="Search..." name="Term" id="Term" type="search">
											</div>
										</div>
									</div>
								</div>
								<div class="row content-row">
									<div class="col-xs-12 main-frame">
										<!-- <iframe src="vote.html" class="main-content"></iframe> -->
										<section class="cols-xs-12 playlist-content vote-selector section">
											<h1 class="content-header">Party Playlist</h1>
											<table id="vote-table" class="choice-list vote-list">
												<tbody>
													<?php
														if($songs != NULL) {
															while($song = $PLAYLIST->GetRow($songs)) {
															?>
																<tr class='song-select'>
																	<td class="song">
																		<?php
																		print($song["SongName"]);
																		?>
																	</td>
																	
																	<td class="artist">
																		<?php
																		print($song["SongArtists"]);
																		?>
																	</td>

																	<?php
																	
																	$votestate = $PLAYLIST->GetVotesForUserForSong($session["UserID"],$song["SongID"]);
																	if ($votestate == 1)
																	{
																		?> <td class="upvote active-vote"> <?php
																	}
																	else
																	{
																		?> <td class="upvote"> <?php
																	}
																	?>
																		<form id="frmVoteUp" method="POST" action="vote.php">
																			<input type="hidden" name="SongID" id="SongID" value="<?php print($song["SongID"]); ?>">
																			<input type="hidden" name="Action" id="Action" value="Voting">
																			<?php
																			if ($votestate == 1)
																			{
																				?> <input type="hidden" name="Value" id="Value" value="0"> <?php
																			}
																			else
																			{
																				?> <input type="hidden" name="Value" id="Value" value="1"> <?php
																			}
																			?>
																			<button type="submit" name="btnVoteUp" id="btnVoteUp"><i class="fas fa-arrow-up"></i></button>
																		</form>
																	</td>
				
																	<td class="voteCount">
																		<?php
																			print($song["VoteCount"]);
																		?>
																	</td>
																	
																	<?php
																	if ($votestate == -1)
																	{
																		?> <td class="downvote active-vote"> <?php
																	}
																	else
																	{
																		?> <td class="downvote"> <?php
																	}
																	?>
																		<form id="frmVoteDown" method="POST" action="vote.php">
																			<input type="hidden" name="SongID" id="SongID" value="<?php print($song["SongID"]); ?>">
																			<input type="hidden" name="Action" id="Action" value="Voting">
																			<?php
																			if ($votestate == -1)
																			{
																				?> <input type="hidden" name="Value" id="Value" value="0"> <?php
																			}
																			else
																			{
																				?> <input type="hidden" name="Value" id="Value" value="-1"> <?php
																			}
																			?>
																			<button type="submit" name="btnVoteDown" id="btnVoteDown"><i class="fas fa-arrow-down"></i></button>
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
												</tbody>
											</table>
										</section>
									</div>
								</div>
							</div>
						</div>
						<div>
						<label for="lblUniqueLink">Give this to your friends: </label>
							<label name="lblUniqueLink" id="lblUniqueLink">https://spotify-jukebox.viljoen.industries/join.php?ID=<?php print($session["PartyUniqueID"]); ?></label>
						</div>
						<div class="row">
							<div class="col-xs-12 section current-music">
								<div class="row current-row">
									<div class="col-xs-6 artwork">
										<i class="fas fa-music"></i>
									</div>
									<div class="col-xs-6 current-info">
										<div class="row">
											<div class="col-xs-12">
												Current Song<br />
											</div>
										</div>
										<div class="row">
											<div class="col-xs-12">
												Current Artist
											</div>
										</div>
									</div>
								</div>
								<div class="row progress-row">
									<div class="col-xs-1"></div>
									<div class="col-xs-10 total-progress-bar">
										<div class="music-progress"></div>
									</div>
									<div class="col-xs-1"></div>
								</div>	
							</div>
						</div>
					</div>

					<!-- Include all compiled plugins (below), or include individual files as needed -->
					<script src="js/jukebox.js?v=29"></script>
				</body>
				</html>
				<?php
			}
		}
	}
?>