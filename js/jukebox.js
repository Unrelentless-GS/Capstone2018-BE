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

//GetVoteCountForSongID
function GetVoteCount(item,songID)
{
	//If is song ID, return vote value
	if (songID == item.SongID)
	{
		return item.VoteCount;
	}
	return null;
}

//Updates votes - Brendan
function UpdateVotes() 
{
	var xhttp = new XMLHttpRequest();
	xhttp.onreadystatechange = function()
	{
		if(this.readyState == 4 && this.status == 200) 
		{
			var songData = JSON.parse(this.responseText);
			//alert(songData[0].SongID);
			//alert(songData[0].VoteCount);

			jQuery('.song-select').each(
			function(index) 
			{
				var songID = jQuery(this).find('input[name="SongID"]').val()
				for (var i = songData.length - 1; i >= 0; i--) 
				{
					var newVoteCount = GetVoteCount(songData[i],songID);
					if (newVoteCount != null)
					{
						jQuery(this).find('.voteCount').text(newVoteCount);
						return;
					}
				}
				jQuery(this).remove()
			});
			sortTable();
		}
	}
	var partyID = jQuery('.party-id').val();
	xhttp.open("GET", SITE + "vote.php?Action=Updates&PartyID=" + partyID, true);
	xhttp.send();
	
	console.log("Updating Votes...");
}

//Timer for UpdateVotes
setInterval(function(){UpdateVotes();}, 3000);


//Sorts songs
function sortTable()
{
	//Written by Sam
	//Modified to work properly by Brendan
	var table, rows, switching, i, x, y, shouldSwitch;
	table = document.getElementById("vote-table");
	switching = true;
	while (switching) 
	{
		switching = false;
		rows = table.getElementsByTagName("TR");
		for (var i = 2, row1; row1 = table.rows[i]; i++) 
		{
			shouldSwitch = false;
			x = rows[i - 1].getElementsByTagName("TD")[3];
			y = rows[i].getElementsByTagName("TD")[3];
			if (parseInt(x.innerHTML) < parseInt(y.innerHTML)) 
		  	{
				shouldSwitch= true;
				break;
			}
		}
		if (shouldSwitch) 
		{
			rows[i].parentNode.insertBefore(rows[i], rows[i-1]);
			switching = true;
		}
	}
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

			//Clears previous results before displaying new one's. - Brendan
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

function Initialise() 
{
	var input = document.getElementById("Term");
	// Execute a function when the user releases a key on the keyboard
	var timer;
	input.addEventListener("keyup", function(event)
	{	
		// Cancel the default action, if needed
		event.preventDefault();
		// Reset timer
		clearTimeout(timer);
		// If term is not empty
		if(jQuery('.form-control').val().length > 0){
			//If key == enter, search immediately
			if (event.keyCode === 13)
			{
				PerformQuery()
			}
			else
			{
				//Else Set timer for 2 seconds
    			timer = setTimeout(function (event){
        			PerformQuery()
   				}, 2000);
			}
		}
	});
}

Initialise();