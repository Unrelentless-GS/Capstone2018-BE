<?php
	/*
	A script for handling guest joins.
	
	Written by Alden Viljoen
	*/
	
	require_once("data/backend/srvr_info.php");
	require_once("data/backend/network.php");
	require_once("data/backend/funcs.php");
	
	require_once("data/auth.php");
	require_once("data/party.php");
	require_once("data/user.php");
	require_once("data/playlist.php");
	
	if(!class_exists("CJoin")) {
		class CJoin extends CNetwork {
			function __construct() {
				parent::__construct("Join", "", "", array ());
				
				if(isset($_POST["PartyID"]) && isset($_POST["txtNickname"])) {
					$this->CompletePartyJoin();
				}elseif(isset($_GET["ID"])) {
					global $PARTY;
					$partyRow = $PARTY->FindPartyWithUniqueString(strtoupper($_GET["ID"]));
					
					if($partyRow === NULL){
						$this->RequestPartyID();
						return;
					}
					
					$this->AuthoriseUserIntoParty($partyRow);
				}elseif($this->IsClientMobile() && isset($_POST["Nickname"]) && isset($_POST["PartyCode"])) {		// <-- Authorising a mobile client.
					$this->AuthoriseMobileIntoParty();
				}elseif($this->IsClientMobile() && isset($_POST["PartyCode"])) {
					// The user is checking whether the party exists. This is just for smoother client facing.
					$this->DoesPartyExist();
				}else{ 
					$this->RequestPartyID();
				}
			}
			
			private function CompletePartyJoin() {
				global $USER;
				
				// Finalise everything. Create the userHash, set cookie and Location to jukebox.
				$partyid 	= $_POST["PartyID"];
				$nickname 	= $_POST["txtNickname"];
				
				// TODO: Test if the user exists.
				// If the user does, return its existing userhash - like how the Host system works,
				// because right now, there'll just be an endless number of a single user created if the user uses multiple devices, closes browsers etc.
				$userhash = $USER->EnterNewUser($partyid, $nickname, 0);
				
				if(!$this->IsClientMobile())
					header("Location: jukebox.php");
				else
					return $userhash;
			}
			
			private function AuthoriseUserIntoParty($party) {
				// Apply join rules here.
				// Party full etc.
				require_once("data/forms/joinparty.php");
				
				$joinform = new CJoinParty();
				$joinform->GetUsername($party);
			}
			
			private function RequestPartyID() {
				require_once("data/forms/joinparty.php");
				
				$joinform = new CJoinParty();
				$joinform->RequestPartyID();
			}
			
			private function AuthoriseMobileIntoParty() {
				global $PLAYLIST;
				global $PARTY;
				
				$party = $PARTY->FindPartyWithUniqueString(strtoupper($_POST["PartyCode"]));
				
				if($party === NULL){
					$this->DropNetMessage(array( "JukeboxFault" => "NoSuchParty" ));
					return;
				}
				
				$_POST["txtNickname"]	= $_POST["Nickname"];
				$_POST["PartyID"]		= $party["PartyID"];
				
				// Now collect data about the party.
				$songs = $PLAYLIST->GetPartySongs($party["PartyID"]);
				$songArray = $this->GetAllResults($songs);
				$hostName = $PARTY->GetHostNickname($party["PartyID"]);
				
				$userhash = $this->CompletePartyJoin();
				
				// TODO: Insert currently playing here.
				$this->DropNetMessage(array( "UserHash" 	=> $userhash,
											 "Songs" 		=> $songArray,
											 "HostName"		=> $hostName
									));
			}
			
			private function DoesPartyExist() {
				global $PLAYLIST;
				global $PARTY;
				
				$party = $PARTY->FindPartyWithUniqueString(strtoupper($_POST["PartyCode"]));
				
				if($party === NULL){
					$this->DropNetMessage(array( "JukeboxFault" => "NoSuchParty" ));
					return;
				}
				$hostName = $PARTY->GetHostNickname($party["PartyID"]);
				
				$this->DropNetMessage(array( "Status" => "Success", "HostName" => $hostName ));	
			}
		}
	}
	
	$join = new CJoin;
?>