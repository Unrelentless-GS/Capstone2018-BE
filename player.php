<?php
	/*
	An endpoint for handling player commands.
	
	Written by Brendan based of designs by Alden Viljoen
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
				$partyid = $_POST["PartyID"];
				$this->TogglePause($partyid);
				header("Location: jukebox.php");
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
						//If no songs in playlist, Play this song 
						//(which will then loop till something is added to the playlist)
						$PARTY->ChangeSongForParty($partyid, '0dYN5MqKzCfdpDb1bgvdsm');
						return;
					}

					// Play the top song.
					$nextSongRow = $this->GetRow($nextSong);
					$PARTY->ChangeSongForParty($partyid, $nextSongRow["SongSpotifyID"]);
				}
				else
				{
					global $JUKE;
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
	
	$player = new CPlayer;
?>