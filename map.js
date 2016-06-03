(function($) {

/*
*  new_map
*
*  This function will render a Google Map onto the selected jQuery element
*
*  @type	function
*  @date	8/11/2013
*  @since	4.3.0
*
*  @param	$el (jQuery element)
*  @return	n/a
*/

function new_map( $el ) {
	
	// var
          var infoWindows = [];
	var $markers = $el.find('.marker');
	 var isDraggable = $(window).width() > 767 ? true : false; // If document (your website) is wider than 480px, isDraggable = true, else isDraggable = false
        var isScroll = $(window).width() > 767 ? true : false; // If document (your website) is wider than 480px, isDraggable = true, else isDraggable = false
    
	// vars
	var args = {
		zoom		: 3,
		center		: new google.maps.LatLng(0, 0),
                draggable       : isDraggable,
                scrollwheel    : true,
		mapTypeId	: google.maps.MapTypeId.ROADMAP
	};
	
	
	// create map	        	
	var map = new google.maps.Map( $el[0], args);
	
	
	// add a markers reference
	map.markers = [];
	
	
	// add markers
	$markers.each(function(){
		
    	add_marker( $(this), map ,infoWindows );
		
	});
	
	
	// center map
	center_map( map );
	
	
	// return
	return map;
	
}

/*
*  add_marker
*
*  This function will add a marker to the selected Google Map
*
*  @type	function
*  @date	8/11/2013
*  @since	4.3.0
*
*  @param	$marker (jQuery element)
*  @param	map (Google Map object)
*  @return	n/a
*/

function add_marker( $marker, map, infoWindows ) {

	// var
	var latlng = new google.maps.LatLng( $marker.attr('data-lat'), $marker.attr('data-lng') );

	// create marker
	var marker = new google.maps.Marker({
		position	: latlng,
                 icon : map_icon_path+'/jobmapicon.jpg',
		map			: map,
                draggable : false,
               
	});

	// add to array
	map.markers.push( marker );

	// if marker contains HTML, add it to an infoWindow
	if( $marker.html() )
	{
		// create info window
		var infowindow = new google.maps.InfoWindow({
			content		: $marker.html()
		});
                        if (infowindow) {
                         infowindow.close();
                        }
		// show info window when marker is clicked
		google.maps.event.addListener(marker, 'click', function() {
                     map.setZoom(13);
                    jQuery('.map-marker-window').css('display','block');
			for (var k=0;k<infoWindows.length;k++) {
                           infoWindows[k].close();
                   }
                        if (infowindow) {
                         infowindow.close();
                        }
                        
                        infowindow.open(map,marker);
                       
                        jQuery(".gm-style-iw").next("div").hide()
                        jQuery(".gm-style-iw").prev("div").css({'width':'530px!important','overflow':'hidden'});
                            });
	}
        google.maps.event.addListener(map, 'click', function() {
    if(infowindow){
       infowindow.close();
    }
});
         infoWindows.push(infowindow); 
        return marker;

}

/*
*  center_map
*
*  This function will center the map, showing all markers attached to this map
*
*  @type	function
*  @date	8/11/2013
*  @since	4.3.0
*
*  @param	map (Google Map object)
*  @return	n/a
*/

function center_map( map ) {

	// vars
	var bounds = new google.maps.LatLngBounds();

	// loop through all markers and create bounds
	$.each( map.markers, function( i, marker ){

		var latlng = new google.maps.LatLng( marker.position.lat(), marker.position.lng() );

		bounds.extend( latlng );

	});

	// only 1 marker?
	if( map.markers.length == 1 )
	{
		// set center of map
	    map.setCenter( bounds.getCenter() );
	    map.setZoom( 16 );
	}
	else
	{
		// fit to bounds
		map.fitBounds( bounds );
	}

}

/*
*  document ready
*
*  This function will render each map when the document is ready (page has loaded)
*
*  @type	function
*  @date	8/11/2013
*  @since	5.0.0
*
*  @param	n/a
*  @return	n/a
*/
// global var
var map = null;

$(document).ready(function(){

	$('.acf-map').each(function(){

		// create map
		map = new_map( $(this) );

	});

});

})(jQuery);
