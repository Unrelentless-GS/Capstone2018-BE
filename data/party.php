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
			Also, updates the party's playlist to point to the currently playing song.
			*/
			private $_updateCurrentlyPlaying = "
				UPDATE playlist p
				SET 
					CurrentlyPlaying = 
						(
							SELECT s.SongID
							FROM song s
							WHERE s.SongSpotifyID=:songid
							LIMIT 1
						),
					PlaybackStarted = UNIX_TIMESTAMP()
					
				WHERE p.PartyID=:partyid
			";
			public function ChangeSongForParty($partyid, $spotify_track_id) {
				global $JUKE;
				
				$row = $this->FindPartyWithID($partyid);
				$JUKE->PutRequest(
					"https://api.spotify.com/v1/me/player/play",
					array( 
						"Content-Type: application/json",
						"Accept: application/json",
						"Authorization: Bearer " . $row["AuthAccessToken"]
					),
					 json_encode(array("uris" => array("spotify:track:" . $spotify_track_id))),
					NULL,
					NULL
				);
				
				$this->RunQuery($this->_updateCurrentlyPlaying,
					[
						"partyid"		=> $partyid,
						"songid"		=> $spotify_track_id
					]);
			}

			/**----------------------------**
			
			Summary
			Locating a party row.
			*/
			private $_findPartyWithID = "SELECT a.*,
												p.*
										 FROM party p 
										 
										 INNER JOIN authentication a
										 ON a.AuthID=p.AuthID
										 
										 WHERE PartyID=:id";
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
			public function FindPartyWithHostID($spotify_user_id) {
				$result = $this->RunQuery($this->_findPartyWithHostID,
					[
						"id"			=> $spotify_user_id
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
			
			public function EndParty($party_id) {
				// Delete all votes.
				$this->RunQuery("DELETE v.* FROM vote v INNER JOIN user u ON u.UserID=v.UserID WHERE u.PartyID=:id", [ "id"	=> $party_id ]);
				
				// Delete all songs.
				$this->RunQuery("DELETE s.* FROM song s INNER JOIN playlist p ON p.PlaylistID=s.PlaylistID WHERE p.PartyID=:id", [ "id"	=> $party_id ]);
				
				// Delete playlist.
				$this->RunQuery("DELETE p.* FROM playlist p WHERE p.PlaylistID=:id", [ "id"	=> $party_id ]);
				
				// Delete all users.
				$this->RunQuery("DELETE u.* FROM user u INNER JOIN party p ON p.PartyID=u.PartyID WHERE p.PartyID=:id", [ "id"	=> $party_id ]);

				// Delete auth.
				$this->RunQuery("DELETE a.* FROM authentication a INNER JOIN party p ON p.AuthID=a.AuthID WHERE p.PartyID=:id", [ "id"	=> $party_id ]);
				
				// Delete party.
				$this->RunQuery("DELETE p.* FROM party p WHERE p.PartyID=:id", [ "id"	=> $party_id ]);
			}
			//**----------------------------**
		}
	}
	
	$PARTY = new CParty();
?>