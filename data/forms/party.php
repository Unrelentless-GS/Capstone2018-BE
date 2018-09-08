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
					<script src="js/spotifyJS.js?v=15"></script>
					<link href="css/style.css?v=7" rel="stylesheet">
				</head>
				<body>
					<input type="hidden" class="party-id" value="<?php print($session["PartyID"]); ?>">
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
									<h1 class="party-header"><?php print($this->ReturnHostname($hostname)); ?> Playlist</h1>
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
													<tbody id="vote-table-attach-point">
														<!--Currently Playing Song-->
														<?php
														$currentSong = $PLAYLIST->GetCurrentSong($session["PartyID"]);
														if ($currentSong != null)
														{
															?>
															<tr class='currently-playing'>
																<td class="currentSongName">
																</td>
																
																<td class="currentArtistName">
																</td>

																<td>
																</td>

																<td class="currentSongValue voteCount">
																</td>

																<td>
																</td>
															</tr>
															<?php
														}
														else
														{
															?>
															<tr class='currently-playing display-hide'>
																<td class="currentSongName">
																</td>
																
																<td class="currentArtistName">
																</td>

																<td>
																</td>

																<td class="currentSongValue voteCount">
																</td>

																<td>
																</td>
															</tr>
															<?php
														}
														
														if($songs != NULL) {
															while($song = $PLAYLIST->GetRow($songs)) {

															if ($song["SongID"] == $currentSong["SongID"])
															{
																continue;
															}

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
																</tr>
															<?php
															}
														}
														else
														{
															?>
															<tr class="noSongsErrorMessage"><td>The playlist is empty, try adding some songs using the search bar.</td><td class="focusSearchBar"><button>Search for Songs</button></td></tr>
															<?php
														}
														?>
													</tbody>
												</table>
											</div>
										</section>
									</div>
								</div>
							</div>
							<div>
								<div class="col-xs-12 section current-music">
									<!--Display current song info, filled in by jukebox.js
									Shows different things depending on if the user is a guest or the host - Brendan-->
									<div class="row current-row">
										<?php
										if ($session["IsHost"] == 1)
										{
											?>
											<div class="col-xs-6 artwork" id="artworkparent">
												<div></div>
											</div>
											<div class="col-xs-6 current-info">
												<div class="row">
													<div class="col-xs-12 currentSongName">
													</div>
												</div>
												<div class="row">
													<div class="col-xs-12 currentArtistName darkerArtistName">
													</div>
												</div>
											</div>
											<div class="player">
												<div class="playpause">
													<form action="player.php" method="POST">
														<input type="hidden" name="PartyID" id="PartyID" value="<?php print($session["PartyID"]); ?>">
														<?php 
															if ($currentSong == null)
															{
																?>
																<button type="submit" name="btnPlayPause">Start Party</button>
																<?php
															}
															else
															{
																?>
																<button class="playButton" type="submit" name="btnPlayPause">Pause</button>
																<?php
															}
														?>
														<!--<button type="submit" name="btnPlayPause">Play/Pause</button>-->
													</form>
												</div>
											</div>
											<div class="chooseDevice">
												<!-- Trigger/Open The Modal -->
												<button id="chooseDeviceModalBtn">Choose Device</button>
											</div>
											<div class="disbandParty">
												<div class="disbandParty2">
													<form action="index.php" method="POST">
														<input type="hidden" name="PartyID" id="PartyID" value="<?php print($session["PartyID"]); ?>">
														<button type="submit" name="endParty">Disband Party</button>
													</form>
												</div>
											</div>
											<?php
										}
										else
										{
											?>
											<div class="col-xs-6 guestArtwork" id="artworkparent">
												<div></div>
											</div>
											<div class="col-xs-6 guest-current-info">
												<div class="row">
													<div class="col-xs-12 currentSongName">
													</div>
												</div>
												<div class="row">
													<div class="col-xs-12 currentArtistName darkerArtistName">
													</div>
												</div>
											</div>
											<div class="guestLeaveParty">
												<div class="leaveParty2">
													<form action="index.php" method="POST">
														<input type="hidden" name="PartyID" id="PartyID" value="<?php print($session["PartyID"]); ?>">
														<input type="hidden" name="UserID" id="UserID" value="<?php print($session["UserID"]); ?>">
														<button type="submit" name="leaveParty">Leave Party</button>
													</form>
												</div>
											</div>
											<?php
										}
										?>
									</div>
								</div>
							</div>
						</div>
						<!-- The Modal -->
						<div id="chooseDeviceModal">
							<!-- Modal content -->
							<div id="modal-content">
							  <!--<span class="exit">Close</span>-->
							</div>
						</div>
					</div>
					<!-- jQuery (necessary for Bootstrap's JavaScript plugins) -->
					<script src="https://ajax.googleapis.com/ajax/libs/jquery/1.12.4/jquery.min.js"></script>
					<!-- Include all compiled plugins (below), or include individual files as needed -->
					<script src="js/jukebox.js?v=23"></script>
				</body>
				</html>
				<?php
			}
			
			private function ReturnHostname($raw_hostname) {
				if(substr($raw_hostname, strlen($raw_hostname) - 1, 1) === "s") {
					return $raw_hostname . "'";
				}else{
					return $raw_hostname . "'s";
				}
			}
		}
	}
?>