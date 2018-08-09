<?php
	/*
	A class for handling
	Written by Alden Viljoen
	
	UpdateUserVote(vote, partyid, userid, spotify_track_id)
	Parameter 'vote' can either be 1 or 0 for upvote downvote respectively.
	
	WARNING! This class is UNTESTED at 30/04/2018
	*/
	
	require_once("backend/jukeboxdb.php");
	require_once("backend/funcs.php");

	if(!class_exists("CPlaylist")) {
		class CPlaylist extends CJukeboxDB {
			function __construct() {
				parent::__construct("Playlist", "", "", array ());
			}
			
			// Adds a track to the playlist for this party
			// via a spotify track ID.
			// Parameter $session must be a session row returned by CParty::GetSessionInfo()
			// This function is intended to be accessed by an endpoint.
			private $_addSong = "
				INSERT INTO song(SongID, SongName, SongArtists, SongAlbum, SongSpotifyID, SongImageLink, SongDuration, PlaylistID)
				VALUES (NULL, :name, :artist, :album, :id, :image, :duration, :playlistid)
 			";
			public function AddSong($session, $spotify_track_id) {
				global $JUKE;
				
				$songid = $this->GetSongID($session["PartyID"], $spotify_track_id);
				if($songid !== NULL)
					return $songid; // If the song's already added, just return its existing ID.
				
				$playlist = $this->GetPartyPlaylist($session["PartyID"]);
				// Make a request to Spotify for info on this song.
				
				$json = $JUKE->GetRequest("https://api.spotify.com/v1/tracks/" . $spotify_track_id, array("Authorization: Bearer " . $session["AuthAccessToken"]));
				$obj = json_decode($json, TRUE);
				
				// Currently will only display the first Artist.
				$name 		= $obj["name"];
				$artist 	= $obj["artists"][0]["name"];
				$album 		= $obj["album"]["name"];
				$image		= $obj["album"]["images"][0]["url"];
				$duration 	= $obj["duration_ms"];
				
				$songid = $this->RunQuery_GetLastInsertID($this->_addSong,
					[
						"name"			=> $name,
						"artist"		=> $artist,
						"album"			=> $album,
						"id"			=> $spotify_track_id,
						"image"			=> $image,
						"duration"		=> (int)($duration / 1000),
						"playlistid"	=> $playlist["PlaylistID"]
					])["InsertID"];
					
				return $songid;
			}
			
			// If the party has this song added, the function returns its local ID.
			private $_findSong = "
				SELECT s.SongID
				FROM song s
				
				INNER JOIN playlist p
				ON p.PlaylistID=s.PlaylistID
				
				WHERE s.SongSpotifyID=:spotid
					AND p.PartyID=:partyid
			";
			public function GetSongID($partyid, $spotify_track_id) {
				$result = $this->RunQuery($this->_findSong,
					[
						"spotid"			=> $spotify_track_id,
						"partyid"			=> $partyid
					]);
					
				if($result === NULL || $result->rowCount() <= 0)
					return NULL;
				return $this->GetRow($result)["SongID"];
			}
			
			// Either returns an existing party playlist,
			// or creates a new one and returns it.
			public function GetPartyPlaylist($partyid) {
				$playlist = $this->RunQuery("SELECT * FROM playlist WHERE PartyID=:id", ["id"=>$partyid]);
				if($playlist !== NULL && $playlist->rowCount() > 0)
					return $this->GetRow($playlist);
				
				$this->RunQuery("
					INSERT INTO playlist(PlaylistID, CurrentlyPlaying, PartyID)
					VALUES (NULL, 'NONE', :id)
				", ["id"=>$partyid]);
				
				return $this->GetPartyPlaylist($partyid);
			}
			
			// Added AND statement in subquery, to return a single song.
			// Returns a statement containing all songs
			// and all votes for each song row.
			private $_getSongs = "
				SELECT 
					s.*,
					(
						SELECT COALESCE(SUM(v.VoteValue),0)
						FROM vote v
						
						INNER JOIN song s1
						ON s1.SongID=v.SongID
						
						INNER JOIN playlist p
						ON s1.PlaylistID=p.PlaylistID
						
						WHERE p.PartyID=:partyid
							AND s.SongID=v.SongID
					) AS VoteCount
					
				FROM song s
				INNER JOIN playlist p
				ON s.PlaylistID=p.PlaylistID
				
				WHERE p.PartyID=:id
			";
			public function GetPartySongs($partyid) {
				$result = $this->RunQuery($this->_getSongs,
					[
						"partyid"		=> $partyid,
						"id"			=> $partyid
					]);
					
				if($result === NULL || $result->rowCount() <= 0)
					return NULL;
				return $result;
			}

			// Removes all the votes for a song, prior to the song being delted. - Brendan
			private $_removeVotesForSong = "
				DELETE v.*
				FROM vote v
				WHERE v.SongID=:songid
			";
			public function RemoveVotesForSong($songid) {
				$this->RunQuery($this->_clearVote,
					[
						"songid"			=> $songid,
					]);
			}
			
			// Remove a song from the database. - Brendan
			private $_deleteSong = "
				DELETE s.*
				FROM song s
				WHERE s.SongID=:songid
			";
			public function RemoveSong($songid) {
				RemoveVotesForSong($songid);
				$this->RunQuery($this->_deleteSong,
					[
						"songid"			=> $songid,
					]);
			}
			
			private $_addVote = "
				INSERT INTO vote(VoteID, VoteValue, SongID, UserID)
				VALUES (NULL, :value, :songid, :userid)
			";
			public function UpdateUserVote($vote, $partyid, $userid, $songid) {
				// Clear user's current vote on the selected SONG, whatever it is.
				// Create a new vote instance, attach it to the song.
				
				$this->ClearUserVote($songid, $userid);
				$this->RunQuery($this->_addVote,
					[
						"value"				=> $vote,
						"songid"			=> $songid,
						"userid"			=> $userid
					]);
			}
			
			/*
			- CREDIT FOR FIX -
			Brendan greatly simplified and corrected this function.
			*/
			private $_clearVote = "
				DELETE v.*
				FROM vote v
				
				WHERE v.UserID=:userid
					AND v.SongID=:songid
			";
			public function ClearUserVote($songid, $userid) {
				$this->RunQuery($this->_clearVote,
					[
						"songid"			=> $songid,
						"userid"			=> $userid
					]);
			}

			// Get Votes For a specific User
			private $_userVotes = "
				SELECT v.VoteValue
				FROM vote v

				WHERE v.UserID=:userid
					AND v.SongID=:songid
			";
			public function GetVotesForUserForSong($userid,$songid) {
				$result = $this->RunQuery($this->_userVotes,
					[
						"userid"			=> $userid,
						"songid"			=> $songid
					]);
				
				$votestate = 0;
				while( $usersvote = $this->GetRow($result)) {
					
					if ($usersvote["VoteValue"] == 1) {
						 $votestate=1;
					}
					else if ($usersvote["VoteValue"] == -1) {
						 $votestate=-1;
					}
				}
				return $votestate;
			}

			// Gets Currently Playing Song
			private $_currentSong = "
				SELECT s.*
				FROM song s

				INNER JOIN playlist p
				ON s.PlaylistID=p.PlaylistID
				
				WHERE s.SongID=p.CurrentlyPlaying
					AND p.PartyID=:partyid
			";
			public function GetCurrentSong($partyid) {
				$result = $this->RunQuery($this->_currentSong,
					[
						"partyid"			=> $partyid,
					]);

				if($result === NULL || $result->rowCount() <= 0)
					return NULL;
				return $result;
			}
		}
	}
	
	$PLAYLIST = new CPlaylist;
?>