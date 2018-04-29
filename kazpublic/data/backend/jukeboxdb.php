<?php
	/*
	Summary
	Defines Jukebox specific functionality.
	Written by Alden Viljoen
	*/
	
	require_once("database.php");
	require_once("funcs.php");
	require_once("encryption.php");

	if(!class_exists("CJukeboxDB")) {
		class CJukeboxDB extends CDBConnection {
			
			function __construct($name) {
				date_default_timezone_set("Australia/Melbourne");
				parent::__construct($name);
				
				if(CHECKTABLES_ENABLED === "1")
					$this->CreateOurTables();
			}
			
			private function CreateOurTables() {
				if($this->DoesTableExist("authentication") === FALSE) {
					$this->RunGetQuery($this->_authenticationTable);
				}
				
				if($this->DoesTableExist("party") === FALSE) {
					$this->RunGetQuery($this->_partyTable);
				}
				
				if($this->DoesTableExist("user") === FALSE) {
					$this->RunGetQuery($this->_userTable);
				}
				
				if($this->DoesTableExist("playlist") === FALSE) {
					$this->RunGetQuery($this->_playlistTable);
				}
				
				if($this->DoesTableExist("song") === FALSE) {
					$this->RunGetQuery($this->_songTable);
				}
				
				if($this->DoesTableExist("vote") === FALSE) {
					$this->RunGetQuery($this->_voteTable);
				}
			}
			
			// The tables this class uses.
			private $_authenticationTable = "
				CREATE TABLE authentication(
					AuthID 					INT		 		AUTO_INCREMENT,
					AuthAccessToken 		VARCHAR(256) 	NOT NULL,
					AuthRefreshToken 		VARCHAR(256) 	NOT NULL,
					AuthExpires 			BIGINT 			NOT NULL,
					AuthSpotifyUserID 		INT	 			NOT NULL,
					
					PRIMARY KEY(AuthID)
				)
			";
			
			private $_partyTable = "
				CREATE TABLE party(
					PartyID 				INT 			AUTO_INCREMENT,
					PartyName 				VARCHAR(50) 	NOT NULL,
					PartyUniqueID 			VARCHAR(128) 	NOT NULL,
					AuthID					INT 			NOT NULL,
					
					PRIMARY KEY(PartyID)
				)
			";
			
			private $_userTable = "
				CREATE TABLE user(
					UserID					INT	 			AUTO_INCREMENT,
					Nickname				VARCHAR(30) 	NOT NULL,
					IsHost					INT		 		NOT NULL,
					PartyID					INT		 		NOT NULL,
					
					PRIMARY KEY(UserID)
				)
			";
			
			private $_playlistTable = "
				CREATE TABLE playlist(
					PlaylistID 				INT 			AUTO_INCREMENT,
					CurrentlyPlaying 		INT	 			NOT NULL,
					PartyID 				INT 			NOT NULL,
					
					PRIMARY KEY(PlaylistID)
				)
			";
			
			private $_songTable = "
				CREATE TABLE song(
					SongID  				INT 			AUTO_INCREMENT,
					SongName 				VARCHAR(128) 	NOT NULL,
					SongArtists 			VARCHAR(192) 	NOT NULL,
					SongAlbum 				VARCHAR(128) 	NOT NULL,
					SongSpotifyID 			INT 			NOT NULL,
					SongImageLink 			VARCHAR(192) 	NOT NULL,
					UserID 					INT 			NOT NULL,
					PlaylistID 				INT 			NOT NULL,
					
					PRIMARY KEY(SongID)
				)	
			";
			
			private $_voteTable = "
				CREATE TABLE vote(
					VoteID 					INT 			AUTO_INCREMENT,
					VoteValue				INT				NOT NULL,
					SongID 					INT				NOT NULL,
					UserID 					INT				NOT NULL,
					
					PRIMARY KEY(VoteID)
				)
			";
		}
	}
?>