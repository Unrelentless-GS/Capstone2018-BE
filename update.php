<?php
	/*
	Spotify Jukebox
	Written by Alden Viljoen

	Functionality for updating specifically mobile data.
	Such items served;
	- Playlist (songs and votes)
	- Currently playing track
	- Handle pause/play from the host.
	*/
	
	require_once("data/backend/network.php");
	
	require_once("data/auth.php");
	require_once("data/party.php");
	require_once("data/user.php");
	require_once("data/playlist.php");

	if(!class_exists("CUpdate")) {
		class CUpdate extends CNetwork {
			function __construct() {
				parent::__construct("Update", "", "", array ());
				
				if(!$this->IsSessionValid()) {
					error_log("[WARNING] Mobile user has an invalid session. (userhash:" . $_POST["JukeboxCookie"] . ")");
					return;
				}
				
				if(!$this->IsClientMobile()) {
					error_log("[WARNING] User attempting communication is NOT mobile.");
					return;
				}
				
				if(!isset($_POST["Operation"])) {
					error_log("[WARNING] Mobile user communicating with no intent.");
					return;
				}
				
				switch($_POST["Operation"]) {
					case "UpdatePlaylist":
						$this->DropPlaylist();
						break;
						
					case "CurrentlyPlaying":
						$this->DropPlaybackInfo();
						break;
						
					case "AddSong":
						$this->AddSong();
						break;
						
					case "EndParty":
						$this->EndParty();
						break;
						
					case "LeaveParty":
						$this->LeaveParty();
						break;
				}
			}
			
			private function EndParty() {
				global $PARTY;
				
				if($this->_NET_SESSION["IsHost"] !== 1) {
					error_log("[WARNING-SECURITY-BREACH] Non-host attempted to end party!");
					$this->DropFault("NotAuthorised");
					
					return;
				}
				
				$PARTY->EndParty($this->_NET_SESSION["PartyID"]);
				$this->DropNetMessage(array( "Status"	=>	"Success"));
			}
			
			private function LeaveParty() {
				global $PARTY;
				
				$PARTY->LeaveParty($this->_NET_SESSION["PartyID"], 
					$this->_NET_SESSION["UserID"]);
				$this->DropNetMessage(array( "Status"	=>	"Success"));
			}
			
			private function AddSong() {
				global $PLAYLIST;
				
				if(!isset($_POST["SongSpotifyID"])) {
					$this->DropFault("NoSongSpotifyIDGiven");
					return;
				}
				
				$songid = $PLAYLIST->AddSong($this->_NET_SESSION, $_POST["SongSpotifyID"]);
				$this->DropNetMessage(array( "Status" => "Success", "SongID" => $songid ));
			}
			
			private function DropPlaylist() {
				global $PLAYLIST;
				$songs = $PLAYLIST->GetPartySongsWithUserVote($this->_NET_SESSION["PartyID"], $this->_NET_SESSION["UserID"]);
				
				// songs is a resource, fetch assoc array.
				if($songs !== NULL)
					$songArray = $this->GetAllResults($songs);
				else
					$songArray = "NoSongsAdded";
				
				// can safely drop this.
				// this can be processed exactly like in the party form, only with JSON on the mobile device.
				$this->DropNetMessage($songArray);
			}
			
			private function DropPlaybackInfo() {
				global $PARTY;
				
				$json = $PARTY->GetCurrentPlaybackInfo($this->_NET_SESSION["PartyID"]);
				if($json === NULL OR $json === "") {
					$this->DropFault("UnableToFindParty");
					return;
				}
				
				$this->DropNetMessage($json);
			}
		}
	}
	
	$update = new CUpdate;
?>
