<?php
	/*
	Written by Alden Viljoen
	*/
	
	define("DEBUG", "1");
	if(DEBUG === "1") {
		define("REDIRECT_URI", "http://localhost/xampp/SpotifyJukebox/jukebox.php");
		
		define("USERNAME", "root");
		define("PASSWORD", "");
	}else{
		define("USERNAME", "-snip-");
		define("PASSWORD", "-snip-");
	
		define("REDIRECT_URI", "https://spotify-jukebox.viljoen.industries/jukebox.php");
	}
	
	define("HOST", "localhost");
	define("DATABASE", "jukebox");

	define("VERSION", "0.01");

	define("CLIENT_ID", "-snip-");
	define("CLIENT_SECRET", "-snip-");
	define("STATE", "Thisisacoolproject");

	define("CHECKTABLES_ENABLED", "1");
?>