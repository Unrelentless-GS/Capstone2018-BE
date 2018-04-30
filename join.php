<?php
	/*
	A script for handling guest joins.
	
	Written by Alden Viljoen
	*/
	
	require_once("data/forms/joinparty.php");
	
	require_once("data/backend/srvr_info.php");
	require_once("data/backend/network.php");
	require_once("data/backend/funcs.php");
	
	require_once("data/auth.php");
	require_once("data/party.php");
	require_once("data/user.php");
	
	if(!class_exists("CJoin")) {
		class CJoin extends CNetwork {
			function __construct() {
				parent::__construct("Join", "", "", array ());
				
				if(isset($_POST["PartyID"]) && isset($_POST["txtNickname"])) {
					$this->CompletePartyJoin();
				}elseif(isset($_GET["ID"])) {
					global $PARTY;
					$partyRow = $PARTY->FindPartyWithUniqueString($_GET["ID"]);
					
					if($partyRow === NULL){
						$this->RequestPartyID();
						return;
					}
					
					$this->AuthoriseUserIntoParty($partyRow);
				}else{ 
					$this->RequestPartyID();
				}
			}
			
			private function CompletePartyJoin() {
				global $USER;
				
				// Finalise everything. Create the userHash, set cookie and Location to jukebox.
				$partyid = $_POST["PartyID"];
				$nickname = $_POST["txtNickname"];
				
				$USER->EnterNewUser($partyid, $nickname, 0);
				
				header("Location: jukebox.php");
			}
			
			private function AuthoriseUserIntoParty($party) {
				// Apply join rules here.
				// Party full etc.
				
				$joinform = new CJoinParty();
				$joinform->GetUsername($party);
			}
			
			private function RequestPartyID() {
				$joinform = new CJoinParty();
				$joinform->RequestPartyID();
			}
		}
	}
	
	$join = new CJoin;
?>