<?php
	/*
	Summary
	A class for handling party specific functions.
	
	Written by Alden Viljoen
	*/
	
	require_once("backend/jukeboxdb.php");
	
	if(!class_exists("CParty")) {
		class CParty extends CJukeboxDB {
			function __construct() {
				parent::__construct("Party");
			}
			
			private $_insertParty = "
				INSERT INTO party(PartyID, PartyName, PartyUniqueID, AuthID)
				VALUES (NULL, :name, :id, :authid)
			";
			public function CreateParty($authid, $partyName, $uniqueString) {
				return $this->RunQuery_GetLastInsertID($this->_insertParty,
					[
						"name"			=> $partyName,
						"id"			=> $uniqueString,
						"authid"		=> $authid
					])["InsertID"];
			}
			
			/*
			Start a user's playback, given a spotify track URI and party ID.
			Example track URI: spotify:track:2u9HkCJUIfofPGMyiEBh7C
			*/
			public function ChangeSongForParty($partyid, $spotify_track_uri) {
				global $JUKE;
				
				$row = $this->FindPartyWithID($partyid);
				$JUKE->PostRequest(
					"https://api.spotify.com/v1/me/player/play",
					"Authorization:  " . $row["AuthID"],
					array(
						"uris"			=> json_encode(array($spotify_track_uri))
					), 
					NULL
				);
			}

			/**----------------------------**
			
			Summary
			Locating a party row.
			*/
			private $_findPartyWithID = "SELECT * FROM party WHERE PartyID=:id";
			public function FindPartyWithID($id) {
				$result = $this->RunQuery($this->_findPartyWithID,
					[
						"id"			=> $id
					]);
				
				if($result === NULL || $result->rowCount() <= 0)
					return NULL;
				
				return $this->GetRow($result);
			}
			
			private $_findPartyWithUniqueID = "SELECT * FROM party WHERE PartyUniqueID=:id";
			public function FindPartyWithUniqueString($unique) {
				$result = $this->RunQuery($this->_findPartyWithUniqueID,
					[
						"id"			=> $unique
					]);
				
				if($result === NULL || $result->rowCount() <= 0)
					return NULL;
				
				return $this->GetRow($result);
			}
			
			private $_findPartyWithHostID =  "
				SELECT p.* 
				FROM party p
				INNER JOIN authentication a
				ON p.AuthID=a.AuthID
				WHERE a.AuthSpotifyUserID=:id";
			public function FindPartyWithHostID($userid) {
				$result = $this->RunQuery($this->_findPartyWithHostID,
					[
						"id"			=> $userid
					]);
				
				if($result === NULL || $result->rowCount() <= 0)
					return NULL;
				
				return $this->GetRow($result);
			}
			//**----------------------------**
			
			public function GenerateUniqueString($partyName, $nickname, $time) {
				$hash = NULL;
				
				while(true) {
					$rand = random_bytes(10);
					$hash = hash("adler32", $partyName . $nickname . $time . $rand);
					
					if($this->FindPartyWithUniqueString($hash) !== NULL){
						continue;
					}else{
						break;
					}
				}
				return $hash;
			}
			
			/**----------------------------**
			
			Summary
			Managing a party. Such as starting, ending etc
			*/
			private $_createParty = "
				INSERT INTO party(PartyID, PartyName, PartyUniqueID, AuthID)
				VALUES (NULL, :name, :uniqueid, :authid)
			";
			public function StartParty($party_name, $party_unique_id, $authid) {
				// Inserts a party row and returns the newly created Party ID.
				$id = $this->RunQuery($this->_createParty,
					[
						"name"			=> $party_name,
						"uniqueid"		=> $party_unique_id,
						"authid"		=> $authid
					])["InsertID"];
					
				return $id;
			}
			
			// Adds a track to the playlist for this party.
			// Takes a party ID and track uri.
			public function AddSong($partyid, $spotify_track_uri) {
				
			}
			
			// Removes a track from the playlist for this party.
			public function RemoveSong($partyid, $spotify_track_uri) {
				
			}
			
			public function EndParty($party_id) {
				// TODO
				// This will destroy a party, including the row, all users, its playlist, all songs and all votes.
			}
			//**----------------------------**
		}
	}
	
	$PARTY = new CParty();
?>