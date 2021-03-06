jQuery(document).ready(function() {
	var autocomplete;

	let element = document.getElementById('indv_autocomplete');
	autocomplete = new google.maps.places.Autocomplete(
		/** @type {!HTMLInputElement} */
		element, {
			types : [ 'geocode' ],
			componentRestrictions : {
				country : 'us'
			}
		});

	autocomplete.addListener('place_changed',
		function () {
			let place = autocomplete.getPlace();
			let latitude = place.geometry.location.lat();
			let longitude = place.geometry.location.lng();
			document.getElementById("indv_latitude").value = latitude;
			document.getElementById("indv_longitude").value = longitude;
		});

	jQuery( "#indv_legislator_name" ).autocomplete({
		source: "/index.php?rest_route=/indv/v1/politicians/autocomplete"
      });

	var output = document.getElementById("indv_output");
	jQuery("#indv_search").on('click', function (event) { 
		var keyword = document.getElementById("indv_keyword").value;
		var title = document.getElementById("indv_legislator_name").value;
		if (keyword != '' || title != '')
			return;
		
		var latitude = document.getElementById("indv_latitude").value;
		var longitude = document.getElementById("indv_longitude").value;
		if (latitude == '' || longitude == '')  {
			if (!navigator.geolocation)
				output.innerHTML = "Geolocation is not supported by your browser";
			else {
				navigator.geolocation.getCurrentPosition(
					function (position) { //success
					    latitude = position.coords.latitude;
					    longitude = position.coords.longitude;
		
					    document.getElementById("indv_latitude").value = latitude;
					    document.getElementById("indv_longitude").value = longitude;
						output.innerHTML = "<p>Successfully determined your location</p>";
						
						jQuery("#indv_search").submit();
					},
		
					function (error) { //failure
						switch(error.code) {
							case error.PERMISSION_DENIED:
							output.innerHTML = "User denied the request for Geolocation."
							break;
							case error.POSITION_UNAVAILABLE:
							output.innerHTML = "Location information is unavailable."
							break;
							case error.TIMEOUT:
							output.innerHTML = "The request to get user location timed out."
							break;
							case error.UNKNOWN_ERROR:
							output.innerHTML = "An unknown error occurred."
							break;
						}
					} );
			}
			event.preventDefault();
		}
	});
});
