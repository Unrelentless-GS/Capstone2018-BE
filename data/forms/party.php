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

				//if(isset($_POST["btnPlay"])) {
				//	$PARTY->ChangeSongForParty(
				//		$_POST["PartyID"],
				//		$_POST["SongSpotifyID"]);
				//}

				?>
				<!DOCTYPE html>
				<html lang="en" data-ng-app="spotifyApp">
				<head>
					<meta charset="utf-8"/>
					<title>Spotify Jukebox</title>
					<!--Bootstrap-->
					<!-- Latest compiled and minified CSS -->
					<link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap.min.css">
				
					<script src="https://use.fontawesome.com/releases/v5.0.10/js/all.js" integrity="sha384-slN8GvtUJGnv6ca26v8EzVaR9DC58QEwsIk9q1QXdCU8Yu8ck/tL/5szYlBbqmS+" crossorigin="anonymous"				></script>
				
					<!-- jQuery library -->
					<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.3.1/jquery.min.js"></script>
				
					<!-- Latest compiled JavaScript -->
					<script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/js/bootstrap.min.js"></script>
					<script src="js/spotifyJS.js?v=2"></script>
					<link href="css/style.css?v=2" rel="stylesheet">
				</head>
				<body>
					<div class="container">
						<div class="row main-section">
							<div class="col-xs-12 main-row">
								<div class="row search-row">
									<div class="col-xs-2 logo-containter">
										<img class="logo" src="Spotify_Logo_RGB_Green.png" />
									</div>
									<div class="col-xs-10 searchbar section">
										<input type="hidden" name="Mode" id="Mode" value="AuthorisationCode">
										<input type="hidden" name="Type" id="Type" value="track">
										<input class="form-control" placeholder="Search..." name="Term" id="Term" type="search">
									</div>
								</div>
								<div class="row results-row display-hide">
									<div class="col-xs-2">
									</div>
									<div class="col-xs-10 search-results">
										<div class="table-responsive" >	
											<table id="search-table" class="table search-list">
												<tbody id="search-results">
												</tbody>
											</table>
										</div>
									</div>
								</div>
								<!--Displays party name and room code, two options for room Code-->
								<div class="header-row">
									<?php $hostname = $PARTY->GetHostNickname($session["PartyID"]); ?>
									<h1 class="party-header"><?php print($hostname)?>'s Playlist</h1>
									<h1 class="join-header">Join : <?php print($session["PartyUniqueID"]); ?></h1>
									<!--<h3 class="join-header">spotify-jukebox.viljoen.industries/join.php?ID=<?php print($session["PartyUniqueID"]); ?></h3>-->
								</div>
								<div class="row content-row">
									<div class="col-xs-12 main-frame">
										<section class="cols-xs-12 playlist-content vote-selector section">
											<div class="table-responsive">	
												<table id="vote-table" class="table choice-list vote-list">
													<thead>
														<tr>
															<th>Track</th>
															<th>Artist</th>
															<th></th>
															<th class="voteHeader">Vote</th>
															<th></th>
														</tr>
													</thead>
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
				
																		<!--<td>
																			<form action="" method="POST">
																				<input type="hidden" name="SongID" id="SongID" value="<?php print($song["SongID"]); ?>">
																				<input type="hidden" name="PartyID" id="PartyID" value="<?php print($session["PartyID"]); ?>">
																				<input type="hidden" name="SongSpotifyID" id="SongSpotifyID" value="<?php print($song["SongSpotifyID"]); ?>">
																				
																				<button type="submit" name="btnPlay">Play</button>
																			</form>
																		</td>-->
				
																	</tr>
																<?php
																}
															}
														?>
													</tbody>
												</table>
											</div>
										</section>
									</div>
								</div>
							</div>
						</div>
						<div class="row">
							<div class="col-xs-12 section current-music">
								<div>
									<form action="player.php" method="POST">
										<input type="hidden" name="PartyID" id="PartyID" value="<?php print($session["PartyID"]); ?>">
										<button type="submit" name="btnPlayPause">Play/Pause</button>
									</form>
								</div>
								<?php
									$current = $PLAYLIST->GetCurrentSong($session["PartyID"]);
									// If there is no current song, don't try to call current song
									// If there is a current song, display the current song
									// - Brendan
									if ($current != NULL)
									{
										while($csong = $PLAYLIST->GetRow($current)) 
										{
											?>
											<div class="row current-row">
												<div class="col-xs-6 artwork">
													<?php
														//Display song image
														$url = $csong["SongImageLink"];
														$allow = ['gif', 'jpg', 'png'];  // allowed extensions
														$img = file_get_contents($url);
														$url_info = pathinfo($url);
														
														// if allowed extension
														//if(in_array($url_info['extension'], $allow)) {
														  // Format the image SRC:  data:{mime};base64,{img_data_base64};
														  $src = 'data:image/jpg; base64,'. base64_encode($img);
														
														  // add the base64 image into a <img> to display it
														  $re = '<img src="'. $src .'" height="45" width="45"/>';
														//}
														//else $re = 'Invalid extension: '. $url_info['extension'];
														
														echo $re;  // output $re data 
													?>
												</div>
												<div class="col-xs-6 current-info">
													<div class="row">
														<div class="col-xs-12">
															<?php
																//Display song name
																print($csong["SongName"]);
															?>
															<br />
														</div>
													</div>
													<div class="row">
														<div class="col-xs-12">
															<?php
																//Display artist name
																print($csong["SongArtists"]);
															?>
														</div>
													</div>
												</div>
											</div>
											<?php	
										}
									}
								?>
							</div>
						</div>
					</div>
					<!-- jQuery (necessary for Bootstrap's JavaScript plugins) -->
					<script src="https://ajax.googleapis.com/ajax/libs/jquery/1.12.4/jquery.min.js"></script>
					<!-- Include all compiled plugins (below), or include individual files as needed -->
					<script src="js/jukebox.js?v=18"></script>
				</body>
				</html>
				<?php
			}
		}
	}
?>