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
			
			/*
			-- Kaz --
			Web API reference.
			https://beta.developer.spotify.com/documentation/web-api/reference/player/start-a-users-playback/
			*/
			
			public function ChangeSongForParty($partyid, $spotify_track_uri) {
				global $JUKE;
				
				// You can get the Access token by querying for the party row using partyid.
				// Use JUKE for options in performing POST requests from PHP.
				// See backend/funcs.php for function definition(s)
				// See auth.php for an example of how to perform a POST request.
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
			
			private $_findPartyWithID = "SELECT * FROM party WHERE PartyUniqueID=:id";
			public function FindPartyWithUniqueString($unique) {
				$result = $this->RunQuery($this->_findPartyWithID,
					[
						"id"			=> $unique
					]);
				
				if($result === NULL || $result->rowCount() <= 0)
					return NULL;
				
				return $this->GetRow($result);
			}
			//**----------------------------**
			
			/**----------------------------**
			
			Summary
			Managing a party. Such as starting, ending etc
			*/
			private $_createParty = "
				INSERT INTO party(PartyID, PartyName, PartyUniqueID, AuthID)
				VALUES (NULL, $name, $uniqueid, $authid)
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
			
			public function EndParty($party_id) {
				// TODO
				// This will destroy a party, including the row, all users, its playlist, all songs and all votes.
			}
			//**----------------------------**
		}
	}
?>