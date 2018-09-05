<?php
	/*
	Spotify Jukebox
	Written by Alden Viljoen

	User specific functionality
	*/
	
	require_once("backend/jukeboxdb.php");
	
	if(!class_exists("CUser")) {
		class CUser extends CJukeboxDB {
			function __construct() {
				parent::__construct("User");
			}
			
			/*private $_updateUserHash = "
				UPDATE user u
				SET u.UserHash=:hash, 
					u.Nickname=:nick
				
				WHERE u.PartyID=:partyid
					AND u.IsHost=1
			";*/
			
			// Function has been modified (for now) to return already-assigned user hash.
			// This is to ensure that the host can be logged in across any number of devices.
			// A.V.
			private $_getCurrentUserHash = "
				SELECT u.UserHash
				FROM user u
				WHERE u.PartyID=:partyid
					AND u.IsHost=1
			";
			public function UpdateHostUserHash($partyid, $nickname) {
				/*$userHash = md5($partyid . random_bytes(15) . $nickname);
				
				setcookie("JukeboxCookie", $userHash);
				$this->RunQuery($this->_updateUserHash,	
					[
						"nick"			=> $nickname,
						"partyid"		=> $partyid,
						"hash"			=> $userHash
					]);*/
					
				$result = $this->RunQuery($this->_getCurrentUserHash,
					[
						"partyid"		=> $partyid
					]);
					
				$userHash = $this->GetRow($result)["UserHash"];
				setcookie("JukeboxCookie", $userHash);
				
				return $userHash;
			}
			
			public function EnterNewUser($partyid, $nickname, $ishost) {
				$userHash = md5($partyid . random_bytes(15) . $nickname);
				setcookie("JukeboxCookie", $userHash);
				
				// Now, finally, we can create a new user. and serve the existing info.
				$this->CreateUser($nickname, $ishost, $userHash, $partyid);
				
				return $userHash;
			}
			
			private $_insertUser = "
				INSERT INTO user(UserID, Nickname, IsHost, UserHash, PartyID)
				VALUES (NULL, :nick, :host, :hash, :partyid)
			";
			public function CreateUser($nickname, $ishost, $userhash, $partyid) {
				return $this->RunQuery_GetLastInsertID($this->_insertUser,
					[
						"nick"			=> $nickname,
						"host"			=> $ishost,
						"hash"			=> $userhash,
						"partyid"		=> $partyid
					])["InsertID"];
			}
			
			private $_findUserWithHash = "
				SELECT *
				FROM user u
				WHERE u.UserHash=:hash
			";
			public function FindUserWithUserHash($userHash) {
				$result = $this->RunQuery($this->_findUserWithHash,
					[
						"hash"			=> $userHash
					]);
					
				if($result === NULL || $result->rowCount() <= 0)
					return NULL;
				
				return $this->GetRow($result);
			}
			
			/*
			Summary
			Requests the user's ID from Spotify Web API.
			This is to properly enter the user into an ongoing party, should one be existing.
			*/
			public function GetUserID($accessToken) {
				global $JUKE;
				
				$result = $JUKE->GetRequest(
					"https://api.spotify.com/v1/me",
					
					array(
						"Accept: application/json",
						"Content-Type: application/json",
						"Authorization: Bearer " . $accessToken
					)
				);
				
				$obj = json_decode($result, TRUE);
				return $obj["id"];
			}
		}
	}
	
	$USER = new CUser();
?>