<?php
	/*
	Summary
	The script which will coordinate which song to play next for each party. This script has been cron'd with the following command (on live server.)
	* /1 * * * *;timeout 60s php /home/anubis/www/jukebox_viljoen_industries/public/data/cron/checkparties.php > /home/anubis/www/jukebox_viljoen_industries/cron.log 2>&1
	
	The script will run every minute, limited to a minute.
	The script itself will run a while loop constantly checking all parties for CurrentlyPlaying songs that have a duration of 2 or less seconds.
	
	If this is the case, the calculated 'next in line' song is decided and sent to 'party' for change.
	
	Written by Alden Viljoen
	
	* Update 18/08/2018 *
	Problem: Song duration statically calculcated using saved time of song start and song duration.
	This causes problems when the host scrolls through the song on another device.
	
	Solution:
	Dynamically request the current progress of a playing song for Spotify.
	*/
	
	// Set to true for the script to run just once whenever its called.
	define("DEBUG_CRON", FALSE);
	
	set_include_path("/home/anubis/www/jukebox_viljoen_industries/public/data/");
	
	require_once("backend/funcs.php");
	require_once("backend/jukeboxdb.php");
	require_once("party.php");
	require_once("playlist.php");
	
	if(!class_exists("CSongLoader")) {
		class CSongLoader extends CJukeboxDB {
			private $_started = 0;
			
			function __construct() {
				parent::__construct("SongLoader");
				$this->_started = time();
				
				while(true) {
					$this->CheckAllParties();
					
					if(DEBUG_CRON === TRUE)
						exit();
					
					if($this->_started + 60 < time()){
						exit();
					}
					sleep(2);
				}
			}
			
			private $_getAllPlayingSongs = "
				SELECT  p.*,
						p1.*,
						s.*,
						a.*
				FROM party p

				INNER JOIN playlist p1
				ON p1.PartyID=p.PartyID

				INNER JOIN song s
				ON p1.CurrentlyPlaying=s.SongID
				
				INNER JOIN authentication a
				ON a.AuthID=p.AuthID
			";
			private function CheckAllParties() {
				$results = $this->RunGetQuery($this->_getAllPlayingSongs);
				
				if($results === NULL || $results->rowCount() <= 0)
					return;
				
				while($row = $this->GetRow($results)) {
					$this->CheckSongEnding($row);
				}
			}
			
			/* Summary:
			Makes a request to Spotify API to request the current status of the host's playback.
			Checks that the song playing is the song stored in the database as the one playing.
			Checks that the song is ending within 2 seconds. If so, play the next one, otherwise do nothing.
			*/
			private function CheckSongEnding($row) {
				global $JUKE;
				
				$json = $JUKE->GetRequest("https://api.spotify.com/v1/me/player/currently-playing?market=AU",
					array("Authorization: Bearer " . $row["AuthAccessToken"]));
				if($json == FALSE || $json == NULL) {
					print("Failed to discover any info for song; " . $row["SongID"] . " in party " . $row["PartyID"] . "\n");
					return;
				}

				$obj = json_decode($json, TRUE);

				//Detects error objects sent by spotify - Brendan
				if(isset($obj["error"]))
				{
					if ($obj["error"]["message"] == "The access token expired")
					{
						print("Error in Party " . $row["PartyID"] . " | Status: " . $obj["error"]["status"] . " | Message: " . $obj["error"]["message"] . "\n");
						print("Deleting Party " . $row["PartyID"] . " as no-one has even looked at it in the last hour" . "\n");
						global $PARTY;
						$PARTY->EndParty($row["PartyID"]);
						return;
					}
					else
					{
						print("Error in Party " . $row["PartyID"] . " | Status: " . $obj["error"]["status"] . " | Message: " . $obj["error"]["message"] . "\n");
						return;
					}
				}


				
				$id = $obj["item"]["id"];
				$is_playing = $obj["is_playing"];
				
				if($is_playing != TRUE) {
					print("Song in party " . $row["PartyID"] . " is currently not playing." . "\n");
					return;
				}
				
				//SongID Mismatch - Add other song to party.
				if($id !== $row["SongSpotifyID"]) {
					print("ID Mismatch for song; " . $row["SongID"] . " in party " . $row["PartyID"] . " (id mismatch)\n" . $id . " vs " . $row["SongSpotifyID"] . "\n");
					print("Replacing Currently Playing with new song in party  " . $row["PartyID"] . "\n");

					//Get Authentication
					$_getSession = "
						SELECT 
							a.AuthAccessToken, a.AuthRefreshToken, a.AuthExpires,
							p.*,
							u.*
						FROM user u
						
						INNER JOIN party p
						ON p.PartyID=u.PartyID
						
						INNER JOIN authentication a
						ON a.AuthID=p.AuthID

						WHERE u.PartyID=:partyid
					";
					$result = $this->RunQuery($_getSession,
						[
							"partyid"		=> $row["PartyID"]
						]);
						
					if($result === NULL || $result->rowCount() <= 0)
						return;
					
					$result = $this->GetRow($result);

					//Add the new song to the database
					global $PLAYLIST;
					$newSongSpotifyId = $id;
					$newSongSongId = $PLAYLIST->AddSong($result, $newSongSpotifyId);

					global $PARTY;
					//Song is already playing, so it only changes the song for the database.
					$PARTY->ChangeSongForDatabase($row["PartyID"],$newSongSpotifyId);

					//Delete old song from the playlist, they obviously didn't want to listen to it
					$PLAYLIST->RemoveSongBySpotifyID($row["SongSpotifyID"]);

					return;
				}
				
				$duration = $obj["item"]["duration_ms"] / 1000;
				$elapsed = $obj["progress_ms"] / 1000;
				
				// Uncomment for diagnosis.
				//print("Song is " . $duration . " seconds long, " . $elapsed . " seconds through." . "\n");
				
				if($duration - $elapsed <= 3) {
					//print("Song is ready to be changed." . "\n");
					// Song is ready to be changed.
					$this->UpdateSong($row);
				}
			}
			
			// Determine the song with the next highest votes and play it.
			// Don't play the same one.
			// In fact, maybe remove the old one.
			// FIXED to remove NULL results - Brendan
			private $_getNextHighestVoted = "
				SELECT s.SongID,
						s.SongSpotifyID,
						(
							SELECT COALESCE(SUM(v.VoteValue),0) As Val
							FROM vote v
							WHERE v.SongID=s.SongID
						) AS Value
				FROM song s

				INNER JOIN playlist p
				ON s.PlaylistID=p.PlaylistID

				WHERE p.PartyID=:partyid
					AND s.SongID<>:current_song
				ORDER BY Value DESC
				LIMIT 1
			";
			private function UpdateSong($row) {
				global $PARTY;
				global $PLAYLIST;
				
				$currentSongID = $row["SongID"];
				$currentSongSpotifyID = $row["SongSpotifyID"];

				$nextSong = $this->RunQuery($this->_getNextHighestVoted,
					[
						"partyid"			=> $row["PartyID"],
						"current_song"		=> $currentSongID
					]);
					
				if($nextSong === NULL || $nextSong->rowCount() <= 0) {
					// Replay the current song.
					$PARTY->ChangeSongForParty($row["PartyID"], $currentSongSpotifyID);
					
					return;
				}
							
				// Fine to remove the current song.
				$PLAYLIST->RemoveSong($currentSongID);
				
				// Play the next song.
				$nextSongRow = $this->GetRow($nextSong);
				$PARTY->ChangeSongForParty($row["PartyID"], $nextSongRow["SongSpotifyID"]);
			}
		}
	}
	$loader = new CSongLoader;
?> 