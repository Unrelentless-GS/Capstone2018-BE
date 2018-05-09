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
				INSERT INTO song(SongID, SongName, SongArtists, SongAlbum, SongSpotifyID, SongImageLink, PlaylistID)
				VALUES (NULL, :name, :artist, :album, :id, :image, :playlistid)
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
				
				$songid = $this->RunQuery_GetLastInsertID($this->_addSong,
					[
						"name"			=> $name,
						"artist"		=> $artist,
						"album"			=> $album,
						"id"			=> $spotify_track_id,
						"image"			=> $image,
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
			
			// Removes a track from the playlist for this party.
			// This doesn't require a userid, since it'll be the Jukebox system removing songs,
			// after they've been played.
			private $_deleteSong = "
				DELETE s.*, v.*
				FROM song s
				
				INNER JOIN vote v
				ON s.SongID=v.SongID
				
				INNER JOIN playlist p
				ON p.PlaylistID=s.PlaylistID
				
				WHERE s.SongSpotifyID=:track_id
					AND p.PartyID=:partyid
			";
			public function RemoveSong($partyid, $spotify_track_uri) {
				$this->RunQuery($this->_deleteSong,
					[
						"track_id"			=> $spotify_track_uri,
						"partyid"			=> $partyid
					]);
			}
			
			private $_addVote = "
				INSERT INTO vote(VoteID, VoteValue, SongID, UserID)
				VALUES (NULL, :value, :songid, :userid)
			";
			public function UpdateUserVote($vote, $partyid, $userid, $songid) {
				// Clear user's current vote on the selected SONG, whatever it is.
				// Create a new vote instance, attach it to the Playlist for this partyid.
				
				$this->ClearUserVote($partyid, $songid, $userid);
				$this->RunQuery($this->_addVote,
					[
						"value"				=> $vote,
						"songid"			=> $songid,
						"userid"			=> $userid
					]);
			}
			
			private $_clearVote = "
				DELETE v.*
				FROM vote v
				
				INNER JOIN party p
				ON p.PartyID=:partyid
				
				INNER JOIN playlist pl
				ON pl.PartyID=p.PartyID
				
				INNER JOIN song s
				ON s.PlaylistID=pl.PlaylistID
				
				WHERE p.PartyID=:partyid1
					AND v.UserID=:userid
					AND s.SongID=:songid
			";
			public function ClearUserVote($partyid, $songid, $userid) {
				$this->RunQuery($this->_clearVote,
					[
						"partyid"			=> $partyid,
						"partyid1"			=> $partyid,
						"userid"			=> $userid,
						"songid"			=> $songid
					]);
			}
		}
	}
	
	$PLAYLIST = new CPlaylist;
?>