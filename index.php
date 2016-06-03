<?php
	/*
  Plugin Name: multiple-marker-map-and-filter
  Description: :
  Version: 1.0

 */
require('settings-page-markers.php');
// Add the google maps api to header
add_action('wp_head', 'map_filter_header');

// Create custom taxonomy for map 


add_action('init', 'map_filter_registration_of_taxonomies');
function map_filter_registration_of_taxonomies() {
    $args = array( 
        'hierarchical' => true,
        'label' => 'Map Categories',
    );
    register_taxonomy( 'map_category', array( 'post', 'Map Category' ), $args );
}


function map_filter_header() {
    ?>
    <script type="text/javascript" src="http://maps.google.com/maps/api/js?sensor=false"></script>
    <?php
}

// generate script
function map_filter_build_map($attr) {
    $markertext = '';
    $attr = shortcode_atts(array(
        'lat' => (get_option('map_filter_initial_latitude') !='') ? sanitize_text_field(get_option('map_filter_initial_latitude')) : 0 ,
        'lon' => (get_option('map_filter_initial_longitude') !='') ? sanitize_text_field(get_option('map_filter_initial_longitude')): 0,
        'id' => 'map',
        'z' => (get_option('map_filter_zoom') !='') ? sanitize_text_field(get_option('map_filter_zoom')) : 16 ,
        'w' => (get_option('map_filter_width') !='') ? sanitize_text_field(get_option('map_filter_width')) : 500 ,
        'h' => (get_option('map_filter_height') !='') ? sanitize_text_field(get_option('map_filter_height')) : 500,
        'maptype' => (get_option('map_filter_maptype') !='') ? sanitize_text_field(get_option('map_filter_maptype')): 'ROADMAP',
        'marker' => ''
            ), $attr);
		// dropdown categories
		$front_cats = wp_dropdown_categories(array('echo' => 0, 'hide_empty' => 0, 'show_option_all'    => 'All','taxonomy'=>'map_category'));
		$front_cats = str_replace('id=', 'onchange="filterMarkers(this.value)" id=', $front_cats);
        

    $generateScript = '  '.$front_cats.'
    <div id="' . $attr['id'] . '" style="width:' . $attr['w'] . 'px;height:' . $attr['h'] . 'px;border:1px solid gray;"></div><br>
    <script type="text/javascript">
    var infowindow = null;
		var latlng = new google.maps.LatLng(' . $attr['lat'] . ', ' . $attr['lon'] . ');
		var myOptions = {
			zoom: ' . $attr['z'] . ',
			center: latlng,
			mapTypeId: google.maps.MapTypeId.' . $attr['maptype'] . '
		};
		var ' . $attr['id'] . ' = new google.maps.Map(document.getElementById("' . $attr['id'] . '"),
		myOptions);
		';

    $generateScript .=' 
	var gmarkers = [];
	var locations = [';
    if (!isset($wpdb))
        $wpdb = $GLOBALS['wpdb'];
    $markers = $wpdb->get_results("SELECT id,title, lattitude, longitude,html,icon,category,postdate FROM " . $wpdb->prefix . "multiple_map_markers_data");
    foreach ($markers as $marker) {
        if (isset($marker->icon) && @GetImageSize($marker->icon)) {
            $markertext .='[' . $marker->lattitude . ',' . $marker->longitude . ',\'' . $marker->html . '\',\'' . $marker->icon . '\',\'' . $marker->category . '\'],';
        } else {
            $markertext .='[' . $marker->lattitude . ',' . $marker->longitude . ',\'' . $marker->html . '\',null,\'' . $marker->category . '\'],';
        }
    }
    $markertext = substr($markertext, 0, strlen($markertext) - 1);
    $generateScript .=$markertext;
    $generateScript .='];';
    $generateScript .='
	 ';
    $generateScript .=' 
	 var bounds = new google.maps.LatLngBounds();
	 for (var i = 0; i < locations.length; i++) {';
    $generateScript .=' var loc = locations[i];
	 ';
    $generateScript .=' var siteLatLng = new google.maps.LatLng(loc[0], loc[1]);
   ';
    $generateScript .=' if(loc[3]!=null) { 
   ';
    $generateScript .=' var markerimage  = loc[3];
   ';
    $generateScript .=' var marker = new google.maps.Marker({
   ';
    $generateScript .=' position: siteLatLng,
   ';
    $generateScript .= ' map: ' . $attr['id'] . ',
   ';
	$generateScript .= ' category: loc[4],
   ';
    $generateScript .= ' icon: markerimage,
   ';
    $generateScript .= ' html: loc[2] });
   ';
    $generateScript .=' } else {
   ';
    $generateScript .=' var marker = new google.maps.Marker({
   ';
    $generateScript .=' position: siteLatLng,
   ';
    $generateScript .= ' map: ' . $attr['id'] . ',
   ';
	$generateScript .= ' category: loc[4],
   ';
    $generateScript .= ' html: loc[2] });
   ';
    $generateScript .=' } gmarkers.push(marker); 
   ';
    $generateScript .= ' var contentString = "Some content";';
    $generateScript .= 'google.maps.event.addListener(marker, "click", function () {
   ';
    $generateScript .= 'infowindow.setContent(this.html);
   ';
    $generateScript .= ' infowindow.open(' . $attr['id'] . ', this); 
   ';
    $generateScript .= '});
   ';
    $generateScript .= 'bounds.extend(marker.position); }
	if(locations.length == 1){
			map.setCenter( bounds.getCenter() );
			map.setZoom( 12 );
	}
	else{
		 map.fitBounds(bounds)
	}
	;
   ';
    $generateScript .=' infowindow = new google.maps.InfoWindow({
                content: "loading.."
            });
    ';
    $generateScript .= '
		filterMarkers = function (category) {
		for (i = 0; i < locations.length; i++) {
		
        marker1 = gmarkers[i];
		
        // If is same category or category not picked
        if (marker1.category == category || category.length === 0) {
            marker1.setVisible(true);
        }
        else {
            marker1.setVisible(false);
        }
		if(category == "0")
			marker1.setVisible(true);
    }
		
	if(locations.length == 1){
			map.setCenter( bounds.getCenter() );
			map.setZoom( 12 );
	}
	else{
		 map.fitBounds(bounds)
	}
}
		</script>';
    return $generateScript;
    ?>



    <?php
}

add_shortcode('map_filter', 'map_filter_build_map');

// loading js files 
function map_filter_load_scripts() {
    wp_enqueue_script('slider-validation-js', plugins_url('js/validation.js', __FILE__));
    //wordpress nonce check
    if (isset($_POST['map_form_nonce_field']) && wp_verify_nonce($_POST['map_form_nonce_field'], 'map_form_action')) {
        // process form data
        map_filter_post_map_form();
    }
    //wordpress nonce check
    if (isset($_POST['map_del_nonce_field']) && wp_verify_nonce($_POST['map_del_nonce_field'], 'map_del_action')) {
        // process form data
        map_filter_delete_map();
    }
}

add_action('admin_init', 'map_filter_load_scripts');

//custom PHP function
function map_filter_in_arrayi($needle, $haystack) {
    return in_array(strtolower($needle), array_map('strtolower', $haystack));
}

function map_filter_post_map_form() {

    // Validate user role/permissions
    if (!current_user_can('manage_options')) {
        wp_die(__('You do not have sufficient permissions to access this page.'));
    }

    // extract to variables
    extract($_POST);

    if (isset($cat)) {

        $serialized_Array = $cat;
        $date = date('Y-m-d H:i:s');
        if (!isset($wpdb))
            $wpdb = $GLOBALS['wpdb'];
        $wpdb->insert($wpdb->prefix . 'multiple_map_markers_data', array('title' => sanitize_text_field($title), 'lattitude' => sanitize_text_field($lattitude), 'longitude' => sanitize_text_field($longitude), 'category' => sanitize_text_field($serialized_Array), 'html' => sanitize_text_field($infohtml), 'icon' => sanitize_text_field($icon), 'postdate' => $date), array('%s', '%s'));
    }
}

function map_filter_delete_map() {
    // only if numeric values 
    // Validate user role/permissions
    if (!current_user_can('manage_options')) {
        wp_die(__('You do not have sufficient permissions to access this page.'));
    }

    if (isset($_REQUEST['deleteval']) && is_numeric($_REQUEST['deleteval'])) {
        $id = $_REQUEST['deleteval'];
        if (!isset($wpdb))
            $wpdb = $GLOBALS['wpdb'];
        $map_filter_table_name = $wpdb->prefix . 'multiple_map_markers_data';
        $wpdb->query("DELETE FROM $map_filter_table_name WHERE ID = $id ");
    }
}

function map_filter_admin_menu() {

    add_menu_page('Map Markers', 'Map Markers', 'manage_options', 'map-markers', 'map_filter_menu_plugin_options');
}

//
add_action('admin_menu', 'map_filter_admin_menu');

//

function map_filter_add_submenu_page() {
    add_submenu_page(
            'map-markers', 'New Marker', 'New Marker', 'manage_options', 'addnew_marker', 'map_filter_add_options_function'
    );
}

add_action('admin_menu', 'map_filter_add_submenu_page');

function map_filter_add_options_function() {

    // Validate user role/permissions
    if (!current_user_can('manage_options')) {
        wp_die(__('You do not have sufficient permissions to access this page.'));
    }
    ?>

    <div class="wrap">
        <h2><?php echo esc_html('Add New Marker'); ?></h2>
        <form method="post" name="map_form" id="map_form" action="" >

            <?php
            // WordPress nonce field
            wp_nonce_field('map_form_action', 'map_form_nonce_field');
            ?>
            <table class="form-table">
                <tr valign="top">
                    <th scope="row"><?php echo esc_html('Title'); ?></th>
                    <td><input required type="text" name="title" id="title" class="" value="" /></td>
                </tr>
                <tr valign="top">
                    <th scope="row"><?php echo esc_html('Lattitude'); ?></th>
                    <td><input required type="text" name="lattitude" id="lattitude" class="" value="" /></td>
                </tr>
                <tr valign="top">
                    <th scope="row"><?php echo esc_html('Longitude'); ?></th>
                    <td><input required type="text" name="longitude" id="longitude" class="" value="" /></td>
                </tr>
                <tr valign="top">
                    <th scope="row"><?php echo esc_html('InfoWindow Text'); ?></th>
                    <td><input required type="text" name="infohtml" id="infohtml" class="" value="" /></td>
                </tr>
                <tr valign="top">
                    <th scope="row"><?php echo esc_html('Map Icon URL'); ?></th>
                    <td><input required type="url" name="icon" id="icon" class="" value="" /></td>
                </tr>

                <tr valign="top">
                    <th scope="row"><?php echo esc_html('Category'); ?></th>
                    <td>
                        <?php
                        $select_cats = wp_dropdown_categories(array('echo' => 0, 'hide_empty' => 0,'taxonomy'=>'map_category','show_option_all'    => 'No Categories',));
						
					   echo $select_cats;
                        ?>
                    </td>
                </tr>

            </table>

            <?php submit_button(); ?>

        </form>

    </div>
    <?php
}

// display map list 
function map_filter_menu_plugin_options() {
    $cat_string = '';
    if (!isset($wpdb))
        $wpdb = $GLOBALS['wpdb'];
// Validate user role/permissions
    if (!current_user_can('manage_options')) {
        wp_die(__('You do not have sufficient permissions to access this page.'));
    }
    ?>
    <div class="wrap">
        <h1><?php echo esc_html('Map Markers'); ?>
            <a class="page-title-action" href="<?php echo admin_url(); ?>admin.php?page=addnew_marker"><?php echo esc_html('Add New'); ?></a>
        </h1>
    </div>
    <table class="wp-list-table widefat fixed striped pages">
        <thead>
            <tr >
                <th class="manage-column column-author" id="author" scope="col"><?php echo esc_html('Title'); ?></th>
                <th class="manage-column column-author" id="author" scope="col"><?php echo esc_html('Lattitude'); ?></th>
                <th class="manage-column column-author" id="author" scope="col"><?php echo esc_html('Longitude'); ?></th>
                <th class="manage-column column-author" id="author" scope="col"><?php echo esc_html('Icon'); ?></th>
                <th class="manage-column column-author" id="author" scope="col"><?php echo esc_html('Description'); ?></th>
                <th class="manage-column column-author" id="author" scope="col"><?php echo esc_html('Date Posted'); ?></th>
                <th class="manage-column column-author" id="author" scope="col"><?php echo esc_html('Category'); ?></th>
                <th class="manage-column column-author" id="author" scope="col"><?php echo esc_html('Action'); ?></th>
            </tr>
        </thead> 
        <?php
        $all_maps = $wpdb->get_results("SELECT id,title, lattitude, longitude,html,icon,category,postdate FROM " . $wpdb->prefix . "multiple_map_markers_data");

        foreach ($all_maps as $map) {
            $cat_string = '';
            ?>	
            <tr class="row-title">
                <th><?php echo esc_html($map->title); ?></th>
                <th><?php echo esc_html($map->lattitude); ?></th>
                <th><?php echo esc_html($map->longitude); ?></th>
                <th><?php echo esc_html($map->icon); ?></th>
                <th><?php echo esc_html($map->html); ?></th>
                <th><?php echo esc_html($map->postdate); ?></th>
				<?php 
			if($map->category != 0 ){
				$terms = get_term($map->category,'map_category');
				if(isset($terms) && !empty($terms)){ 
					?>
						<th><?php echo esc_html($terms->name); ?></th>
				<?php 	}}
				
				else{
					echo "<th>No Category Assigned</th>";
				}
						?> 
				
				<th>
            <form action="" id="delfrm<?php echo $map->id; ?>" name="delfrm<?php echo $map->id; ?>" method="post">
                <?php
                // WordPress nonce field
                wp_nonce_field('map_del_action', 'map_del_nonce_field');
                ?>
                <a href="javascript:;"onclick="javascript:confirm('Do you really want to delete') ? validate(event, <?php echo $map->id; ?>) : 0"  /><?php echo esc_html('Delete'); ?> </a>
                <input type="hidden" name="deleteval" id="deleteval" value="<?php echo esc_html($map->id); ?>" />
            </form>
        </th>

        <tr>
        <?php }
        ?>

    </tr>

    <tbody id="the-list">

    </tbody>
    </table>
    <?php
}

/* Plugin Activation Hook
 * 
 */

function map_filter_plugin_options_install() {
    if (!isset($wpdb))
        $wpdb = $GLOBALS['wpdb'];
    $map_filter_table_name = $wpdb->prefix . 'multiple_map_markers_data';

    if ($wpdb->get_var("show tables like '$map_filter_table_name'") != $map_filter_table_name) {
        $sql = "CREATE TABLE " . $map_filter_table_name . " (
		id INT NOT NULL AUTO_INCREMENT,
		lattitude double NOT NULL,
		title text,
		longitude double NOT NULL,
		html text,
		icon text,
		category text,
		postdate datetime , 
		PRIMARY KEY (id)
		);";

        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
        dbDelta($sql);
    }
}

register_activation_hook(__FILE__, 'map_filter_plugin_options_install');

// Plugin deactivation hook
function map_filter_hook_uninstall() {
    if (!isset($wpdb))
     $wpdb = $GLOBALS['wpdb'];
    $map_filter_table_name = $wpdb->prefix . 'multiple_map_markers_data';
    $wpdb->query("DROP TABLE IF EXISTS $map_filter_table_name");
}

register_uninstall_hook(__FILE__, 'map_filter_hook_uninstall');
?>