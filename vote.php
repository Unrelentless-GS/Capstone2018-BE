<?php
	/*
	An endpoint for handling upvote/downvote commands.
	Also handles song adding.
	
	Written by Alden Viljoen
	
	For this class, remember to also submit an 'action' parameter with the POST request.
	WARNING! This class is UNTESTED at 30/04/2018
	*/
	
	require_once("data/backend/srvr_info.php");
	require_once("data/backend/network.php");
	require_once("data/backend/funcs.php");
	
	require_once("data/auth.php");
	require_once("data/party.php");
	require_once("data/user.php");
	require_once("data/playlist.php");
	
	if(!class_exists("CVote")) {
		class CVote extends CNetwork {
			function __construct() {
				parent::__construct("Vote", "", "", array ( "Action" ));
				
				if(!$this->SessionRequired()) {
					print("No session");
					return;
				}
				
				if(!isset($_POST["Action"]))
					return;
				$action = $_POST["Action"];
				switch($action) {
					case "Voting":
						$this->HandleVoting();
						break;
						
					case "Songs":
						$this->HandlePlaylist();
						break;
				}
			}
			
			private function HandlePlaylist() {
				global $PLAYLIST;
				
				// The user wishes to add a song to the playlist.
				if(isset($_POST["SpotifySongID"])) {
					$spotify_id = $_POST["SpotifySongID"];
					$songid = $PLAYLIST->AddSong($this->_NET_SESSION, $spotify_id);
					
					// Do something here, like tell the user to update their songlist.
				}
			}
			
			private function HandleVoting() {
				if(isset($_POST["SongID"]) && isset($_POST["Action"])) {
					$songid = $_POST["SongID"];
					if($_POST["Action"] === "1") {
						$this->Upvote($songid);
					}elseif($_POST["Action"] === "0") {
						$this->Downvote($songid);
					}
				}
			}
			
			private function Upvote($songid) {
				global $PLAYLIST;
				$PLAYLIST->UpdateUserVote(
					1,
					$_NET_SESSION["PartyID"],
					$_NET_SESSION["UserID"],
					$songid
				);
				
				// Some more upvote-related functionality here.
			}
			
			private function Downvote($songid) {
				global $PLAYLIST;
				$PLAYLIST->UpdateUserVote(
					0,
					$_NET_SESSION["PartyID"],
					$_NET_SESSION["UserID"],
					$songid
				);
				
				// Some more downvote-related functionality here.
			}
		}
	}
	
	$vote = new CVote;
?>