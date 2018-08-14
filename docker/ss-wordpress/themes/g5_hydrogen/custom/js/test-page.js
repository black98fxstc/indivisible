	var latitude, longitude;

	var componentForm = {
	    street_number : 'short_name',
	    route : 'long_name',
	    locality : 'long_name',
	    administrative_area_level_1 : 'short_name',
	    postal_code : 'short_name'
	};

	var autocomplete;
	function initAutocomplete() {
	    // Create the autocomplete object, restricting the search to geographical
	    // location types.
	   let element = document.getElementById('autocomplete');
	    autocomplete = new google.maps.places.Autocomplete(
	    /** @type {!HTMLInputElement} */
	    	element, {
		types : [ 'geocode' ],
		componentRestrictions : {
		    country : 'us'
	    }});

	    // When the user selects an address from the dropdown, populate the address
	    // fields in the form.
	    autocomplete.addListener('place_changed', fillInAddress);
	}

	var place;
	function fillInAddress() {
	    // Get the place details from the autocomplete object.
	    place = autocomplete.getPlace();

	    for ( var component in componentForm) {
		document.getElementById(component).value = '';
		document.getElementById(component).disabled = false;
	    }

	    // Get each component of the address from the place details
	    // and fill the corresponding field on the form.
	    for (var i = 0; i < place.address_components.length; i++) {
			var addressType = place.address_components[i].types[0];
			if (componentForm[addressType]) {
			    var val = place.address_components[i][componentForm[addressType]];
			    document.getElementById(addressType).value = val;
			}
		}
	    document.getElementById('google-find-me').disabled = false;	}

	function googleFindMe() {

	    latitude = place.geometry.location.lat();
	    longitude = place.geometry.location.lng();
	    document.getElementById("latitude").value = latitude;
	    document.getElementById("longitude").value = longitude;
	    document.getElementById("round-em-up").disabled = false;
	}
	
	function censusFindMe () {
		var output = document.getElementById("out");
	    var xmlhttp = new XMLHttpRequest();
	    var url = "https://geocoding.geo.census.gov/geocoder/locations/address?format=json&benchmark=Public_AR_Current" 
		    + "&street=" + document.getElementById("census-street-address").value 
		    + "&city=" + document.getElementById("census-city").value
		    + "&state=" + document.getElementById("census-state").value
		    + "&zip=" + document.getElementById("zip-code").value;

	    xmlhttp.onreadystatechange = function() {
	        if (this.readyState == 4 && this.status == 200) {
	            output.innerHTML = this.responseText;
			    document.getElementById("round-em-up").disabled = false;
	        }
	    };

	    xmlhttp.open("GET", url, true);
	    xmlhttp.send();
	}
	
	function browserFindMe () {
		var output = document.getElementById("out");

		if (!navigator.geolocation)
		{
			output.innerHTML = "<p>Geolocation is not supported by your browser</p>";
			return;
		}

		function success(position)
		{
			latitude = position.coords.latitude;
			longitude = position.coords.longitude;
		    document.getElementById("latitude").value = latitude;
		    document.getElementById("longitude").value = longitude;
		    document.getElementById("round-em-up").disabled = false;
		}

		function error()
		{
			output.innerHTML = "Unable to retrieve your location";
		}

		output.innerHTML = "<p>Locating…</p>";

		navigator.geolocation.getCurrentPosition(success, error);
	    
	}

	function getLowerDox (leg_id) {
		var xmlhttp = new XMLHttpRequest();
	    var url = "https://api.state-strong.org/open-states/api/v1/legislators/" + leg_id + "/";

	    xmlhttp.onreadystatechange = function() {
			if (this.readyState == 4 && this.status == 200) {
			    let dox = JSON.parse(this.responseText);
	
			    jQuery("#lower-party").text(dox.party);
			    jQuery("#lower-phone").text(dox.offices[0]['phone']);
			    jQuery("#lower-email").text(dox.email);
			    jQuery("#lower-address").text(dox.offices[0]['address']);
			    jQuery("#lower-photograph").empty();
			    jQuery("#lower-photograph").append(jQuery("<img>", { src : dox.photo_url }));
			    jQuery("#lower-website").empty();
			    if (dox.url)
			    	jQuery("#lower-website").append(jQuery("<a>", { href: dox.url }).text("Website"));
			    jQuery("#lower-actions").empty();
		    	jQuery("#lower-actions").append(jQuery("<a>", { href: "index.php/legislator/" + dox.leg_id.toLowerCase() }).text("Actions"));
		    };
	    }

	    xmlhttp.open("GET", url, true);
	    xmlhttp.send();
	}

	function getUpperDox (leg_id) {
		var xmlhttp = new XMLHttpRequest();
	    var url = "https://api.state-strong.org/open-states/api/v1/legislators/" + leg_id + "/";

	    xmlhttp.onreadystatechange = function() {
			if (this.readyState == 4 && this.status == 200) {
			    let dox = JSON.parse(this.responseText);
	
			    jQuery("#upper-party").text(dox.party);
			    jQuery("#upper-phone").text(dox.offices[0]['phone']);
			    jQuery("#upper-email").text(dox.email);
			    jQuery("#upper-address").text(dox.offices[0]['address']);
			    jQuery("#upper-photograph").empty();
			    jQuery("#upper-photograph").append(jQuery("<img>", { src : dox.photo_url }));
			    jQuery("#upper-website").empty();
			    if (dox.url)
			    	jQuery("#upper-website").append(jQuery("<a>", { href: dox.url }).text ("Website"));
			    jQuery("#upper-actions").empty();
		    	jQuery("#upper-actions").append(jQuery("<a>", { href: "index.php/legislator/" + dox.leg_id.toLowerCase() }).text("Actions"));
		    };
	    }

	    xmlhttp.open("GET", url, true);
	    xmlhttp.send();
	}

	function getLowerBoundary(boundary_id) {
		var xmlhttp = new XMLHttpRequest();
	    var url = "https://api.state-strong.org/open-states/api/v1/districts/boundary/" + boundary_id + "/";

	    xmlhttp.onreadystatechange = function() {
			if (this.readyState == 4 && this.status == 200) {
			    let boundary = JSON.parse(this.responseText);
	
				if (boundary)
				    jQuery("#lower-boundary").text("Have boundary");
		    };
	    }

	    xmlhttp.open("GET", url, true);
	    xmlhttp.send();
	}

	function getUpperBoundary(boundary_id) {
		var xmlhttp = new XMLHttpRequest();
	    var url = "https://api.state-strong.org/open-states/api/v1/districts/boundary/" + boundary_id + "/";

	    xmlhttp.onreadystatechange = function() {
			if (this.readyState == 4 && this.status == 200) {
			    let boundary = JSON.parse(this.responseText);
				if (boundary)
				    jQuery("#upper-boundary").text("Have boundary");
		    };
	    }

	    xmlhttp.open("GET", url, true);
	    xmlhttp.send();
	}

	function roundEmUp() {
	    var output = document.getElementById("out");
	    output.innerHTML = "";

	    var img = new Image();
	    img.src = "https://maps.googleapis.com/maps/api/staticmap?center="
		    + latitude + "," + longitude
		    + "&zoom=13&size=300x300&sensor=false"
		    + "&key=" + "AIzaSyD6-CGpCzt5hhveIVcYUNp3auafwiqmK-4";

	    output.appendChild(img);

	    var doxem = document.getElementById("dox");
	    var xmlhttp = new XMLHttpRequest();
	    var url = "https://api.state-strong.org/civic-key/front-page" + "?lat=" + latitude
		    + "&lng=" + longitude;

	    xmlhttp.onreadystatechange = function() {
			if (this.readyState == 4 && this.status == 200) {
			    var response = JSON.parse(this.responseText);
	
			    jQuery("#state-name").text(response.state);
			    jQuery("#upper-house").text(response.state_upper_district);
			    jQuery("#lower-house").text(response.state_lower_district);
			    jQuery("#upper-legislator").text(
				    response.state_upper_legislators[0].full_name);
			    jQuery("#lower-legislator").text(
				    response.state_lower_legislators[0].full_name);
	
			    getLowerDox(response.state_lower_legislators[0].leg_id);
			    getUpperDox(response.state_upper_legislators[0].leg_id);
			    getLowerBoundary(response.state_lower_boundary);
			    getUpperBoundary(response.state_upper_boundary);
	    	};
	    }

	    xmlhttp.open("GET", url, true);
	    xmlhttp.send();
	}

	jQuery(document).ready(function() {

	    jQuery(".google-find-me").on('click', googleFindMe);
	    jQuery(".census-find-me").on('click', censusFindMe);
	    jQuery(".browser-find-me").on('click', browserFindMe);
	    jQuery("#round-em-up").on('click', roundEmUp);

	    initAutocomplete();
	});
