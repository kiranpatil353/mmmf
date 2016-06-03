<script type="text/javascript">
    var infowindow = null;
		var latlng = new google.maps.LatLng(48.220162, 16.287525);
		var myOptions = {
			zoom: 12,
			center: latlng,
			mapTypeId: google.maps.MapTypeId.ROADMAP
		};
		var MultipleMarkerMapDemo = new google.maps.Map(document.getElementById("MultipleMarkerMapDemo"),
		myOptions);
		 var sites = [[48.193054,16.261282,'Bar','http://google-maps-icons.googlecode.com/files/cocktail.png'],[48.220162,16.287525,'point-of-view','http://google-maps-icons.googlecode.com/files/beautiful.png'],[48.240162,16.227525,'point-of-view','http://google-maps-icons.googlecode.com/files/beautiful.png']];
		 
	  for (var i = 0; i < sites.length; i++) { var site = sites[i];
	  var siteLatLng = new google.maps.LatLng(site[0], site[1]);
    if(site[3]!=null) { 
    var markerimage  = site[3];
    var marker = new google.maps.Marker({
    position: siteLatLng,
    map: MultipleMarkerMapDemo,
    icon: markerimage,
    html: site[2] });
    } else {
    var marker = new google.maps.Marker({
    position: siteLatLng,
    map: MultipleMarkerMapDemo,
    html: site[2] });
    }
    var contentString = "Some content";google.maps.event.addListener(marker, "click", function () {
   infowindow.setContent(this.html);
    infowindow.open(MultipleMarkerMapDemo, this);
   });
   }
    infowindow = new google.maps.InfoWindow({
                content: "loading..."
            });
    </script>