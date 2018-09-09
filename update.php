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
				}
			}
			
			private function DropPlaylist() {
				global $PLAYLIST;
				$songs = $PLAYLIST->GetPartySongs($this->_NET_SESSION["PartyID"]);
				
				// songs is a resource, fetch assoc array.
				$songArray = $this->GetAllResults($songs);
				
				// can safely drop this.
				// this can be processed exactly like in the party form, only with JSON on the mobile device.
				$this->DropNetMessage($songArray);
			}
			
			private function DropPlaybackInfo() {
				// TODO: Drop playback info. What's currently playing; track name, artist, album, preview image, elapsed and song duration.
			}
		}
	}
	
	$update = new CUpdate;
?>