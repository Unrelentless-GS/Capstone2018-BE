<?php
	/*
	An endpoint for handling player commands.
	
	Written by Brendan based off designs by Alden Viljoen
	*/
	
	require_once("data/backend/srvr_info.php");
	require_once("data/backend/network.php");
	require_once("data/backend/funcs.php");
	
	require_once("data/auth.php");
	require_once("data/party.php");
	require_once("data/user.php");
	require_once("data/playlist.php");
	
	if(!class_exists("CPlayer")) {
		class CPlayer extends CNetwork {
			function __construct() {
				parent::__construct("Player", "", "", array ( "Action" ));
				
				if(!$this->SessionRequired()) {
					print("No session");
					return;
				}

				if(isset($_POST["PartyID"])) 
				{
					$partyid = $_POST["PartyID"];
					//If no active spotify device, return to jukebox and open the devicechoice modal
					$noActiveDevice = $this->checkActiveSpotifyDevice($partyid);
					if ($noActiveDevice)
					{
						header("Location: jukebox.php?choosedevice");
						return;
					}
	
					$this->TogglePause($partyid);
					header("Location: jukebox.php");
				}
				else if(isset($_GET["PartyID"])) 
				{
					$partyid = $_GET["PartyID"];
					if(isset($_GET["TP"]))
					{
						$this->TogglePause($partyid);
					}
					else
					{
						$this->spotifyCurrentlyPlaying($partyid);
					}
				}
			}

			/**----------------------------**
			Returns all connected devices
			*/
			private function GetDevices($partyid) {
				global $JUKE;
			
				global $PARTY;
				$row = $PARTY->FindPartyWithID($partyid);
				
				$result = $JUKE->GetRequest(
					"https://api.spotify.com/v1/me/player/devices",
					array( 
						"Content-Type: application/json",
						"Accept: application/json",
						"Authorization: Bearer " . $row["AuthAccessToken"]
					),
					NULL,
					NULL,
					NULL
				);
				return $result;
			}
			
			/**----------------------------**
			Returns true if there are no active spotify devices
			*/
			private function checkActiveSpotifyDevice($partyid) {
				$results = $this->GetDevices($partyid);
				$resultsObj = json_decode($results, TRUE);
				$noActiveDeviceFound = TRUE;
				print($results);
			
				foreach ($resultsObj["devices"] as $dev) 
				{
					if ($dev["is_active"] == 1)
					{
						$noActiveDeviceFound = FALSE;
					}
				}
			
				return $noActiveDeviceFound;
			}

			/**----------------------------**
			Returns true or false to the question: Is spotify currently playing?
			*/
			private function spotifyCurrentlyPlaying($partyid) {
				global $PARTY;
				$row = $PARTY->FindPartyWithID($partyid);
				$playing = $this->GetPlayer($row["AuthAccessToken"]);

				//Checks if party has started
				global $PLAYLIST;
				$currentSong = $PLAYLIST->GetCurrentSong($partyid);
				if ($currentSong == null)
				{
					print(-1);
				}
				else
				{
					if(isset($playing["is_playing"]))
					{
						if ($playing["is_playing"]==1)
						{
							print(1);
						}
						else 
						{
							print(0);
						}
					}
				}
			}
			
			/**----------------------------**
			Returns all player information
			*/
			private function GetPlayer($AuthAccessToken) {
				global $JUKE;
				
				$result = $JUKE->GetRequest(
					"https://api.spotify.com/v1/me/player?market=AU",
					array( 
						"Content-Type: application/json",
						"Accept: application/json",
						"Authorization: Bearer " . $AuthAccessToken
					),
					NULL,
					NULL,
					NULL
				);
				$result = json_decode($result, true);
				return $result;
			}

			/**----------------------------**
			Toggle's Pause
			*/
			private function TogglePause($partyid) {
				global $PARTY;
				$row = $PARTY->FindPartyWithID($partyid);
				$playing = $this->GetPlayer($row["AuthAccessToken"]);

				global $PLAYLIST;
				$current = $PLAYLIST->GetCurrentSong($partyid);
				//If a song is currently playing or currently paused, toggle it
				//If no song is currently playing , play the top song
				if ($current === NULL)
				{
					$_getHighestVoted = "
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
						ORDER BY Value DESC
						LIMIT 1
					";

					global $PARTY;
					$nextSong = $this->RunQuery($_getHighestVoted,
						[
							"partyid"			=> $row["PartyID"],
						]);
						
					if($nextSong === NULL || $nextSong->rowCount() <= 0) 
					{
						//If no songs in playlist, display an error
						header("Location: jukebox.php?needtoaddsongs");
						exit;
					}

					// Play the top song.
					$nextSongRow = $this->GetRow($nextSong);
					$PARTY->ChangeSongForParty($partyid, $nextSongRow["SongSpotifyID"]);
				}
				else
				{
					global $JUKE;
					if(isset($playing["is_playing"]))
					{
						if ($playing["is_playing"] == 1)
						{
							$JUKE->PutRequest
							(
								"https://api.spotify.com/v1/me/player/pause",
								array( 
									"Content-Type: application/json",
									"Accept: application/json",
									"Authorization: Bearer " . $row["AuthAccessToken"]
								),
								NULL,
								NULL,
								NULL
							);
						}
						else
						{
							$JUKE->PutRequest
							(
								"https://api.spotify.com/v1/me/player/play",
								array( 
									"Content-Type: application/json",
									"Accept: application/json",
									"Authorization: Bearer " . $row["AuthAccessToken"]
								),
								NULL,
								NULL,
								NULL
							);
						}
					}
				}
			}
		}
	}
	
	$player = new CPlayer;
?>