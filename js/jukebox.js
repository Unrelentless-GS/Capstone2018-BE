/*
Spotify Jukebox - Adding a song to a party.
By Alden Viljoen

This connects to my server, don't break it.
*/

var DEBUG = true;
var SITE = "";

if(DEBUG == true) {
	SITE = "http://localhost/xampp/SpotifyJukebox/";
}else{
	SITE = "https://spotify-jukebox.viljoen.industries/";
}

/*
	Summary
	Adding and searching for songs section
*/
function AddSong(spotify_track_id) {
	var xhttp = new XMLHttpRequest();
	xhttp.open("POST", SITE + "vote.php", true);
	
	xhttp.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
	xhttp.onreadystatechange = function() {
		if(this.readyState == 4 && this.status == 200) {
			// Reload the page or required controls.
			window.location.reload();
		}
	}
	
	xhttp.send("Action=Songs&SpotifySongID=" + spotify_track_id);
}

function PerformQuery() {
	// Get our search term.
	var sTerm = document.getElementById("Term").value;
	console.log("Performing search for " + sTerm);
	
	var xhttp = new XMLHttpRequest();
	xhttp.onreadystatechange = function() {
		if(this.readyState == 4 && this.status == 200) {
			var tracks = JSON.parse(this.responseText).tracks.items;

			//Clear list
			var searchresults = document.getElementById("search-results")
			while (searchresults.firstChild) {
				searchresults.removeChild(searchresults.firstChild);
			}

			//Add to list
			tracks.forEach(AddToList)
			
		}
	}
	
	xhttp.open("GET", SITE + "search.php?Type=track&Term=" + sTerm, true);
	xhttp.send();
	
	console.log("Querying...");
}

function AddToList(item, index)
{
	var tr = document.createElement("TR"); 

	var title = document.createElement("td");
	var t = document.createTextNode(item.name);
	title.appendChild(t);
	tr.appendChild(title);

    var artist = document.createElement("td");
    var t = document.createTextNode(item.artists[0].name);
	artist.appendChild(t);
	tr.appendChild(artist);

	var id = item.id;

    var button = document.createElement("td");
    //button.setAttribute("onclick", "AddSong("+item.id+")");
	button.onclick = function() 
	{
    	AddSong(id);
	};
    var buttoni = document.createElement("i");
    buttoni.setAttribute("class", "fas fa-plus addSongBtn");

    button.appendChild(buttoni);
	tr.appendChild(button);

    var searchresults = document.getElementById("search-results")
    searchresults.appendChild(tr);
}

/*
	Brendan changed Initialise to tigger AddSong on a search, instead of a button click.
*/
function Initialise() 
{
	var input = document.getElementById("Term");
	// Execute a function when the user releases a key on the keyboard
	input.addEventListener("keyup", function(event){	
		// Cancel the default action, if needed
		event.preventDefault();
		// Number 13 is the "Enter" key on the keyboard
		if ((event.keyCode === 13) && (document.getElementById("Term") != null))
		{
		 	PerformQuery();
		}
	});
}

Initialise();