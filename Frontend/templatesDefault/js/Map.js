function Map(latitude, longtitude){
	this.latitude = latitude;
	this.longtitude = longtitude;
	
	this.init();
}

Map.prototype = {
	
	directionsDisplay : null,
	directionsService : null,
	map : null,
	self : null,
	
	init : function(){
		
		self = this;
		
		directionsService = new google.maps.DirectionsService()
		directionsDisplay = new google.maps.DirectionsRenderer();

	  // Create a map object, and include the MapTypeId to add
	  // to the map type control.
	   var mapOptions = {
		scrollwheel: true,
		zoom: 13,
		center: new google.maps.LatLng(this.latitude, this.longtitude),
		disableDefaultUI: false,
		mapTypeControlOptions: {
		mapTypeIds: [google.maps.MapTypeId.ROADMAP, 'map_style']
		},
		zoomControl:true,
		zoomControlOptions: {
		position    : google.maps.ControlPosition.LEFT_CENTER,
					style       : google.maps.ZoomControlStyle.SMALL
		}
	  };

		map = new google.maps.Map(document.getElementById('gMap'),
		mapOptions);

		directionsDisplay.setMap(map);
		directionsDisplay.setPanel($("#mapPannel").get(0));

		var myLatlng = new google.maps.LatLng(this.latitude, this.longtitude);

		var marker = new google.maps.Marker({
			 position: myLatlng,
			 map: map
		 });

	   $('#calcRoute').click(function(e){
		   e.preventDefault();
		   self.calcRoute();
	   });
	},
	
	calcRoute : function() {
  
		var start = $("#from").val();
		var end = new google.maps.LatLng(this.latitude, this.longtitude);
		var request = {
		  origin:start,
		  destination:end,
		  travelMode: google.maps.TravelMode.DRIVING
		};
		directionsService.route(request, function(result, status) {
		  if (status == google.maps.DirectionsStatus.OK) {
			directionsDisplay.setDirections(result);
			$("#mapPannel").show();
		  }
		});
	}
};