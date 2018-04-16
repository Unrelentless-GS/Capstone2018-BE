/*
Spotify Jukebox - Search example.
By Alden Viljoen

This connects to my server, don't break it.
*/

function PerformQuery() {
	// Get our search term.
	var sTerm = document.getElementById("txtSearch").value;
	
	var xhttp = new XMLHttpRequest();
	xhttp.onreadystatechange = function() {
		console.log("Ready State: " + this.readyState);
		console.log("Status: " + this.status);
		
		if(this.readyState == 4 && this.status == 200) {
			var tracks = JSON.parse(this.responseText).tracks.items;
			
			// Do something with the response. Update that fckn gay list.
			var list = document.getElementById("results");
			for(var i = 0; i < tracks.length; i++) {
				var result = document.createElement("div");
				result.setAttribute("class", "alert alert-success");
				result.innerHTML = "<strong>" + tracks[i].name + "</strong> by " + tracks[i].artists[0].name;
				
				list.appendChild(result);
			}
		}
	}
	
	xhttp.open("GET", "https://spotify-jukebox.viljoen.industries/search.php?Type=track&Term=" + sTerm, true);
	xhttp.send();
}

function Initialise() {
	var btnSearch = null;
	if((btnSearch = document.getElementById("btnSearch")) != null)
		btnSearch.onclick = PerformQuery;
}

Initialise();