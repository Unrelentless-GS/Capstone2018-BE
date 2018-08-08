<?php
	/*
	Summary
	The script which will coordinate which song to play next for each party. This script has been cron'd with the following command (on live server.)
	* /1 * * * *;timeout 60s php /home/anubis/www/jukebox_viljoen_industries/public/data/cron/checkparties.php > /home/anubis/www/jukebox_viljoen_industries/cron.log 2>&1
	
	The script will run every minute, limited to a minute.
	The script itself will run a while loop constantly checking all parties for CurrentlyPlaying songs that have a duration of 2 or less seconds.
	
	If this is the case, the calculated 'next in line' song is decided and sent to 'party' for change.
	
	Written by Alden Viljoen
	*/
	
	set_include_path("/home/anubis/www/jukebox_viljoen_industries/public/data/");
	
	require_once("backend/jukeboxdb.php");
	require_once("party.php");
	require_once("playlist.php");
	
	if(!class_exists("CSongLoader")) {
		class CSongLoader extends CJukeboxDB {
			function __construct() {
				parent::__construct("SongLoader");
				
				while(true) {
					$this->CheckAllParties();
					sleep(2);
				}
			}
			
			private $_getAllFinishingSongs = "
				SELECT  p.*,
						p1.*,
						s.*
				FROM party p

				INNER JOIN playlist p1
				ON p1.PartyID=p.PartyID

				INNER JOIN song s
				ON p1.CurrentlyPlaying=s.SongID
				
				WHERE ((p1.PlaybackStarted + s.SongDuration) - UNIX_TIMESTAMP() <= 2)
			";
			private function CheckAllParties() {
				error_log("Checking");
				$results = $this->RunGetQuery($this->_getAllFinishingSongs);
				
				if($results === NULL || $results->rowCount() <= 0)
					return;
				
				while($row = $this->GetRow($results)) {
					$this->UpdateSong($row);
				}
			}
			
			// Determine the song with the next highest votes and play it.
			// Don't play the same one.
			// In fact, maybe remove the old one.
			private $_getNextHighestVoted = "
				SELECT s.SongID,
						s.SongSpotifyID,
						(
							SELECT SUM(v.VoteValue)
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
				$PLAYLIST->RemoveSong($currentSongID, $currentSongSpotifyID);
				
				// Play the next song.
				$nextSongRow = $this->GetRow($nextSong);
				$PARTY->ChangeSongForParty($row["PartyID"], $nextSongRow["SongSpotifyID"]);
			}
		}
	}
	$loader = new CSongLoader;
?> <?php
	/*
	Summary
	The script which will coordinate which song to play next for each party. This script has been cron'd with the following command (on live server.)
	* /1 * * * *;timeout 60s php /home/anubis/www/jukebox_viljoen_industries/public/data/cron/checkparties.php > /home/anubis/www/jukebox_viljoen_industries/cron.log 2>&1
	
	The script will run every minute, limited to a minute.
	The script itself will run a while loop constantly checking all parties for CurrentlyPlaying songs that have a duration of 2 or less seconds.
	
	If this is the case, the calculated 'next in line' song is decided and sent to 'party' for change.
	
	Written by Alden Viljoen
	*/
	
	set_include_path("/home/anubis/www/jukebox_viljoen_industries/public/data/");
	
	require_once("backend/jukeboxdb.php");
	require_once("party.php");
	require_once("playlist.php");
	
	if(!class_exists("CSongLoader")) {
		class CSongLoader extends CJukeboxDB {
			function __construct() {
				parent::__construct("SongLoader");
				
				while(true) {
					$this->CheckAllParties();
					sleep(2);
				}
			}
			
			private $_getAllFinishingSongs = "
				SELECT  p.*,
						p1.*,
						s.*
				FROM party p

				INNER JOIN playlist p1
				ON p1.PartyID=p.PartyID

				INNER JOIN song s
				ON p1.CurrentlyPlaying=s.SongID
				
				WHERE ((p1.PlaybackStarted + s.SongDuration) - UNIX_TIMESTAMP() <= 2)
			";
			private function CheckAllParties() {
				error_log("Checking");
				$results = $this->RunGetQuery($this->_getAllFinishingSongs);
				
				if($results === NULL || $results->rowCount() <= 0)
					return;
				
				while($row = $this->GetRow($results)) {
					$this->UpdateSong($row);
				}
			}
			
			// Determine the song with the next highest votes and play it.
			// Don't play the same one.
			// In fact, maybe remove the old one.
			private $_getNextHighestVoted = "
				SELECT s.SongID,
						s.SongSpotifyID,
						(
							SELECT SUM(v.VoteValue)
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
				$PLAYLIST->RemoveSong($currentSongID, $currentSongSpotifyID);
				
				// Play the next song.
				$nextSongRow = $this->GetRow($nextSong);
				$PARTY->ChangeSongForParty($row["PartyID"], $nextSongRow["SongSpotifyID"]);
			}
		}
	}
	$loader = new CSongLoader;
?>