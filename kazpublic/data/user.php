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
		}
	}
	
	$USER = new CUser();
?>