<script type="text/javascript">
    var infowindow = null;
		var latlng = new google.maps.LatLng(0, 0);
		var myOptions = {
			zoom: 1,
			center: latlng,
			mapTypeId: google.maps.MapTypeId.ROADMAP
		};
		var map = new google.maps.Map(document.getElementById("map"),
		myOptions);
		 var sites = [];
	  
	 var bounds = new google.maps.LatLngBounds();
	 for (var i = 0; i < sites.length; i++) { var site = sites[i];
	  var siteLatLng = new google.maps.LatLng(site[0], site[1]);
    if(site[3]!=null) { 
    var markerimage  = site[3];
    var marker = new google.maps.Marker({
    position: siteLatLng,
    map: map,
    icon: markerimage,
    html: site[2] });
    } else {
    var marker = new google.maps.Marker({
    position: siteLatLng,
    map: map,
    html: site[2] });
    }
    var contentString = "Some content";google.maps.event.addListener(marker, "click", function () {
   infowindow.setContent(this.html);
    infowindow.open(map, this); 
   });
   bounds.extend(marker.position); } map.fitBounds(bounds);
    infowindow = new google.maps.InfoWindow({
                content: "loading..."
            });
    	</script>