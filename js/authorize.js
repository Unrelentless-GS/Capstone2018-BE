/*
Spotify Jukebox - Authorization example.
By Alden Viljoen

This connects to my server, don't break it.
*/

// My personal Spotify app's client ID.
var CLIENT_ID = "19ad9a26512a4c729f357d826130ffad";

// The redirect URL, the user will be sent here upon making an authorization decision.
var REDIRECT_URL = encodeURIComponent("https://spotify-jukebox.viljoen.industries/index.php");

// Assurance state value, this will be more complex when rolled out.
var STATE = "This is a cool project!";

// The specific permissions we're going to request from the user.
var SCOPES = encodeURIComponent("user-modify-playback-state user-read-playback-state playlist-read-private user-library-read");

function AuthorizeJukebox() {
	var request = "https://accounts.spotify.com/authorize/?client_id=" + 
			CLIENT_ID + "&response_type=code&redirect_uri=" + 
			REDIRECT_URL + "&scope=" + 
			SCOPES + "&state=" + 
			STATE;
			
	// Done assembling our request, we can make a query now.
	window.location.replace(request);
}

function Initialise() {
	var btnAuthorize = null;
	if((btnAuthorize = document.getElementById("btnAuthorize")) != null)
		btnAuthorize.onclick = AuthorizeJukebox;
}

Initialise();