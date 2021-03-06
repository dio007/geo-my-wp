<?php
// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Get an option
 *
 * Get a specific gmw option from database.
 *
 * Thanks to pippin williamson for this awesome function
 *
 * @since 2.6.1
 * @return mixed
 */
function gmw_get_option( $group = '', $key ='', $default = false ) {

	$gmw_options = GMW()->options;
	
	$value = ! empty( $gmw_options[$group][$key] ) ? $gmw_options[$group][$key] : $default;
	$value = apply_filters( 'gmw_get_option', $value, $group, $key, $default );
	
	return apply_filters( 'gmw_get_option_'.$group.$key, $value, $group, $key, $default );
}

/**
 * Get group of options from database
 *
 * @since 2.6.1
 * @return mixed
 */
function gmw_get_options_group( $group = 'gmw_options' ) {
	
	$gmw_options = GMW()->options;
	
	if ( empty( $group ) || $group == 'gmw_options' ) {
		return $gmw_options;
	}

	if ( ! empty( $gmw_options[$group] ) ) {
		return $gmw_options[$group];
	}

	return false;
}

function gmw_get_object_blog_id( $object = '' ) {
	if ( $object != '' && isset( GMW()->locations_blogs[$object] ) && absint( GMW()->locations_blogs[$object] ) ) {
		return GMW()->locations_blogs[$object];
	}
	return false;
}

/**
 * Get blog ID
 * 
 * @return [type] [description]
 */
function gmw_get_blog_id( $object = '' ) {
 	
 	if ( is_multisite() ) {
		
		if ( $object != '' ) {
			
			$loc_blog = gmw_get_object_blog_id( $object );

			if ( $loc_blog != false ) {
				return $loc_blog;
			}
		}

		global $blog_id;

		return $blog_id;
	
	} else {			
		return 1;
	}
}



/**
 * Get specific form data
 * 
 * @param  boolean $id form ID
 * @return array      Form data
 */
function gmw_get_form( $id = false ) {
	return GMW_Forms_Helper::get_form( $id );
}

/**
 * Get specific form data
 * 
 * @return array GEo my WP forms data
 */
function gmw_get_forms() {
	return GMW_Forms_Helper::get_forms();
}

/**
 * Get addons ( extensions ) data
 * 
 * @return array  data of all loaded addons
 */
function gmw_get_object_types() {
	return GMW()->object_types;
}

/**
 * Check if add-on is active
 * 
 * @param  string $addon slug/name of the addon
 * 
 * @return boolean true/false
 */
function gmw_is_addon_active( $addon = '' ) {
	if ( ! empty( GMW()->addons_status[$addon] ) && GMW()->addons_status[$addon] == 'active' && ! isset( $_POST['gmw_premium_license'] ) ) {
		return true;
	} else {
		return false;
	}
}

/**
 * Get addon ( extensions ) status
 * 
 * @return array  data of all loaded addons
 */
function gmw_get_addon_status( $addon = false ) {
	return ! empty( GMW()->addons_status[$addon] ) ? GMW()->addons_status[$addon] : 'inactive';
}

/**
 * Get addons ( extensions ) status
 * 
 * @return array  data of all loaded addons
 */
function gmw_get_addons_status() {
	return GMW()->addons_status;
}

/**
 * Get addons ( extensions ) data
 * 
 * @return array  data of all loaded addons
 */
function gmw_get_addons_data( $get_licenses = false ) {
	return ( IS_ADMIN && $get_licenses ) ? array_merge_recursive( GMW()->addons, GMW()->licenses ) : GMW()->addons;
}

/**
 * Get addon ( extension ) data
 * 
 * @param  string $addon slug/name of the addon to pull its data
 * @param  string $var   specific data value
 * 
 * @return array  add-on's data
 */
function gmw_get_addon_data( $addon = '', $var = '', $get_license_data = false ) {

	if ( ! empty( GMW()->addons[$addon] ) )  {

		$addon = GMW()->addons[$addon];
		
		if ( IS_ADMIN && $get_license_data && ( $licenses = GMW()->licenses[$addon] ) !== FALSE )  {
			$addon = array_merge( $addons, $licenses );
		}

		if ( $var != '' ) {
			if ( isset( $addon[$var] ) ) {
				return $addon[$var];
			} else {
				return false;
			}
		} else {
			return $addon;
		}
	} else {
		return false;
	}
}

/**
 * Get addon ( extension ) data
 * 
 * @param  string $addon slug/name of the addon to pull its data
 * @param  string $var   specific data value
 * 
 * @return array  add-on's data
 */
function gmw_get_addon_license_data( $addon = '', $var = '' ) {
	
	if ( ! empty( GMW()->licenses[$addon] ) )  {
		
		$licenses = GMW()->licenses;

		if ( $var != '' ) {
			if ( isset( $licenses[$addon][$var] ) ) {
				return $licenses[$addon][$var];
			} else {
				return false;
			}
		} else {
			return $licenses[$addon];
		}
	} else {
		return false;
	}
}

function gmw_get_object_type( $object = false ) {
	return ( $object && isset( GMW()->object_types[$object] ) ) ? GMW()->object_types[$object] : false;
}

/**
 * Update addon status
 * 
 * @param  [type] $addon [description]
 * @return [type]        [description]
 */
function gmw_update_addon_status( $addon = false, $status = 'active', $details = false ) {

	if ( empty( $addon ) || ! in_array( $status, array( 'active', 'inactive', 'disabled' ) ) ) {
		return;
	}

	// get addons data from database
	$addons_status = get_option( 'gmw_addons_status' );

	if ( empty( $addons_status ) ) {
		$addons_status = array();
	}

	// update addon data
	$addons_status[$addon] = $status;

	// save new data in database
	update_option( 'gmw_addons_status', $addons_status );

	// update status in global
	GMW()->addons_status = $addons_status;
	GMW()->addons[$addon]['status'] = $status;
	GMW()->addons[$addon]['status_details'] = $details;

	update_option( 'gmw_addons_data', GMW()->addons );

	return GMW()->addons[$addon];
}

/**
 * Update addon data
 * 
 * @param  array  $addon [description]
 * @return [type]        [description]
 */
function gmw_update_addon_data( $addon = array() ) {

	if ( empty( $addon ) ) {
		return;
	}

	$addons_data = get_option( 'gmw_addons_data' );

	if ( empty( $addons_data ) ) {
		$addons_data = array();
	}

	$addons_data[$addon['slug']] = $addon;

	update_option( 'gmw_addons_data', $addons_data );

	GMW()->addons = $addons_data;
}

/**
 * Get URL prefix
 * 
 * @return [type] [description]
 */
function gmw_get_url_prefix() {
	return GMW()->url_prefix;
}

/**
 * Get the user current location
 * 
 * @return OBJECT of the user location
 */
function gmw_get_user_current_location() {
	return GMW_Helper::get_user_current_location();
}

	/**
	 * Get the user current coords
	 * 
	 * @return ARRAY of the user current coordinates
	 */
	function gmw_get_user_current_coords() {
		
		$ul = GMW_Helper::get_user_current_location();
		
		if ( $ul == false) {
			return false;
		}

		return array( 'lat' => $ul->lat, 'lng' => $ul->lng );
	}

	/**
	 * Get the user current address
	 * 
	 * @return ARRAY of the user current address
	 */
	function gmw_get_user_current_address() {
		
		$ul = GMW_Helper::get_user_current_location();
		
		if ( $ul == false) {
			return false;
		}

		return $ul->formatted_address;
	}

/**
 * Processes all GMW actions sent via POST and GET by looking for the 'gmw_action'
 * request and running do_action() to call the function
 *
 * @since 2.5
 * @return void
 */
function gmw_process_actions() {

	if ( isset( $_POST['gmw_action'] ) ) {
		do_action( 'gmw_' . $_POST['gmw_action'], $_POST );
	}

	if ( isset( $_GET['gmw_action'] ) ) {
		do_action( 'gmw_' . $_GET['gmw_action'], $_GET );
	}
}

if ( IS_ADMIN ) {
	add_action( 'admin_init', 'gmw_process_actions' );
} else {
	add_action( 'init', 'gmw_process_actions' );
}

/**
 * GMW Function - Covert object to array
 * 
 * @since  2.5
 * @param  object
 * @return Array/multidimensional array
 */
function gmw_object_to_array( $data ) {
	
	if ( is_array( $data ) || is_object( $data ) ) {

		$result = array();

		foreach ( $data as $key => $value ) {
			$result[ $key ] = gmw_object_to_array( $value );
		}

		return $result;
	}
	
	return $data;
}

/**
 * Sort array by priority. For Settings and forms pages.
 * @param  [type] $a [description]
 * @param  [type] $b [description]
 * @return [type]    [description]
 */
function gmw_sort_by_priority( $a, $b ) {

    $a['priority'] = ( ! empty( $a['priority'] ) ) ? $a['priority'] : 99;
    $b['priority'] = ( ! empty( $b['priority'] ) ) ? $b['priority'] : 99;
    
    if ( $a['priority'] == $b['priority'] ) {
        return 0;
    }

    return $a['priority'] - $b['priority'];
}

/**
 * Convert object to array
 * 
 * @param  object $object 
 * @param  [type] $output ARRAY_A || ARRAY_N
 * 
 * @return array
 */
function gmw_to_array( $object, $output = ARRAY_A ) {

	if ( $output == ARRAY_A ) {
        return ( array ) $object;
	} elseif ( $output == ARRAY_N ) {
        return array_values( ( array ) $object );
	} else {
		return $object;
	}
}

/**
 * Bulild a unit array 
 * @param  srring $unit imperial/metric
 * @return array        array
 */
function gmw_get_units_array( $units = 'imperial' ) {

	if ( $units == "imperial" ) {
		return array( 'radius' => 3959, 'name' => "mi", 'long_name' => 'miles', 'map_units' => "ptm", 'units' => 'imperial' );
	} else {
		return array( 'radius' => 6371, 'name' => "km", 'long_name' => 'kilometers', 'map_units' => 'ptk', 'units' => "metric" );
	}
}

/**
 * For older PHP version that does not have the array_replace_recursive function
 */
if ( ! function_exists( 'array_replace_recursive' ) ) {
	
	function array_replace_recursive($base, $replacements) {
	    
	    foreach (array_slice(func_get_args(), 1) as $replacements) {
			$bref_stack = array(&$base);
			$head_stack = array($replacements);

			do {
				end($bref_stack);

				$bref = &$bref_stack[key($bref_stack)];
				$head = array_pop($head_stack);

				unset($bref_stack[key($bref_stack)]);

				foreach (array_keys($head) as $key) {
					if (isset($bref[$key]) && is_array($bref[$key]) && is_array($head[$key])) {
						$bref_stack[] = &$bref[$key];
						$head_stack[] = $head[$key];
					} else {
						$bref[$key] = $head[$key];
					}
				}
			} while(count($head_stack));
		}

		return $base;
	}
}

/**
 * Calculate the distance between two points
 * 
 * @param  [type] $start_lat latitude of start point
 * @param  [type] $start_lng longitude of start point
 * @param  [type] $end_lat   latitude of end point
 * @param  [type] $end_lng   longitude of end point
 * @param  string $units     m for miles k for kilometers
 * 
 * @return [type]            [description]
 */
function gmw_calculate_distance( $start_lat, $start_lng, $end_lat, $end_lng, $units="m" ) {

	$theta 	  = $start_lng - $end_lng;
	$distance = sin( deg2rad( $start_lat ) ) * sin( deg2rad( $end_lat ) ) +  cos( deg2rad( $start_lat ) ) * cos( deg2rad( $end_lat ) ) * cos( deg2rad( $theta ) );
	$distance = acos( $distance );
	$distance = rad2deg( $distance );
	$miles 	  = $distance * 60 * 1.1515;

	if ( $units == "k" ) {
		$distance = ( $miles * 1.609344 );
	} else {
		$distance = ( $miles * 0.8684 );
	}

	return round( $distance, 2 );
}

/**
 * Get labels
 * 
 * most of the labels of the forms are set below.
 * it makes it easier to manage and it is now possible to modify a single or multiple
 * labels using the filter provided instead of using the translation files.
 *
 * You can create a custom function in the functions.php file of your theme and hook it using the filter gmw_shortcode_set_labels.
 * You should check for the $form['ID'] in your custom function to make sure the function apply only for the required forms.
 * 
 * @since 2.5
 */
function gmw_get_labels( $form = array() ) {

	$labels = array(
		'search_form'		=> array(
			'search_site'		=> __( 'Search Site', 'GMW' ),
			'radius_within'		=> __( 'Within',   'GMW' ),
			'kilometers'		=> __( 'Kilometers',      'GMW' ),
			'miles'				=> __( 'Miles', 'GMW' ),
			'submit'			=> __( 'Submit', 'GMW' ),
			'get_my_location' 	=> __( 'Get my current location','GMW'),
			'show_options'		=> __( 'Advanced options', 'GMW' ),
			'select_groups'		=> __( 'Select Groups', 'GMW' ),
			'no_groups'			=> __( 'No Groups', 'GMW' ),
			'all_groups'		=> __( 'All Groups', 'GMW' )
		),
		'pagination'		=> array(
			'prev'  => __( 'Prev', 	'GMW' ),
			'next'  => __( 'Next', 	'GMW' ),
		),
		'search_results'	=> array(
			'results_message' => array(
				'count_message'  => __( 'Showing {results_count} out of {total_results} results', 'GMW' ),
				'radius_message' => __( ' within {radius} {units} from {address}', 'GMW' ),
			),
			'pt_results_message' 	  => array(
				'showing'	=> __( 'Showing {results_count} out of {total_results} results', 'GMW' ),
				'within'	=> __( 'within {radius} {units} from {address}', 'GMW' ),
			),
			'fl_results_message' => array(
				'showing'	=> __( 'Viewing %s out of %s members', 'GMW' ),
				'within'	=> __( 'within %s from %s', 'GMW' ),
			),
			'gl_results_message' => array(
				'showing' 	=> __( 'Viewing %s out of %s groups', 'GMW' ),
				'within'	=> __( 'within %s from %s', 'GMW' ),
			),
			'ug_results_message' => array(
				'showing'	=> __( 'Viewing %s out of %s users', 'GMW' ),
				'within'	=> __( 'within %s from %s', 'GMW' ),
			),
			'distance'          => __( 'Distance: ', 'GMW' ),
			'driving_distance'	=> __( 'Driving distance:', 'GMW' ),
			'address'           => __( 'Address: ',  'GMW' ),
			'formatted_address' => __( 'Address: ',  'GMW' ),
			'directions'        => __( 'Get directions', 'GMW' ),
			'your_location'     => __( 'Your Location ', 'GMW' ),
			'not_avaliable'		=> __( 'N/A', 'GMW' ),
			'read_more'			=> __( 'Read more',	'GMW' ),
			'contact_info'		=> array(
				'phone'	  		=> __( 'Phone: ', 'GMW' ),
				'fax'	  		=> __( 'Fax: ', 'GMW' ),
				'email'	  		=> __( 'Email: ', 'GMW' ),
				'website' 		=> __( 'website: ', 'GMW' ),
				'na'	  		=> __( 'N/A', 'GMW' ),
				'contact_info'	=> __( 'Contact Information','GMW' ),
			),
			'opening_hours'			=> __( 'Opening Hours' ),
			'member_info'			=> __( 'Member Information', 'GMW' ),
			'google_map_directions' => __( 'Show directions on Google Map', 'GMW' ),
			'active_since'			=> __( 'active %s', 'GMW' ),
			'per_page'				=> __( 'per page', 'GMW' ),
		),
		'results_message' 	=> array(
				'showing'
		),
		'info_window'		=> array(
			'address'  			 => __( 'Address: ', 'GMW' ),
			'directions'         => __( 'Get Directions', 'GMW' ),
			'formatted_address'  => __( 'Formatted Address: ', 'GMW' ),
			'distance' 			 => __( 'Distance: ', 'GMW' ),
			'phone'	   			 => __( 'Phone: ', 'GMW' ),
			'fax'	   			 => __( 'Fax: ', 'GMW' ),
			'email'	   			 => __( 'Email: ', 'GMW' ),
			'website'  			 => __( 'website: ', 'GMW' ),
			'na'	   			 => __( 'N/A', 'GMW' ),
			'your_location'		 => __( 'Your Location ', 'GMW' ),
			'contact_info'		 => __( 'Contact Information','GMW' ),
			'read_more'			 => __( 'Read more', 'GMW' ),
			'member_info'	     => __( 'Member Information', 'GMW' )
		)
	);
	
	//modify the labels
	$labels = apply_filters( 'gmw_set_labels', $labels, $form );

	if ( ! empty( $form['ID'] ) ) {
		$labels = apply_filters( "gmw_set_labels_{$form['ID']}", $labels, $form );
	}

	return $labels;
}

/**
 * get template file and its stylesheet
 *
 * @since 3.0
 * 
 * @param  string $addon         the slug of the add-on which the tmeplate file belongs to.
 * @param  string $folder_name   folder name ( search-forms, search-results... ).
 * @param  string $template_name tempalte name
 * 
 * @return 
 */
function gmw_get_template( $addon = 'posts_locator', $template_type = 'search-forms', $iw_type = 'popup', $template_name = 'default', $base_path = '' ) {
	return GMW_Helper::get_template( $addon, $template_type, $iw_type, $template_name, $base_path );
}
	
	/**
	 * Get search form template
	 * @param  string $addon         [description]
	 * @param  string $template_name [description]
	 * @param  string $base_path     [description]
	 * @return [type]                [description]
	 */
	function gmw_get_search_form_template( $addon = 'posts_locator', $template_name = 'default', $base_addon = '', $include = false ) {
		$args = array(
			'base_addon'       => $base_addon,
			'addon' 	       => $addon, 
			'folder_name'      => 'search-forms', 
			'template_name'    => $template_name,
			'include_template' => $include
		);
		return GMW_Helper::get_template( $args );
	}

	/**
	 * Get search results template
	 * @param  string $addon         [description]
	 * @param  string $template_name [description]
	 * @param  string $base_path     [description]
	 * @return [type]                [description]
	 */
	function gmw_get_search_results_template( $addon = 'posts_locator', $template_name = 'default', $base_addon = '', $include = false ) {
		$args = array(
			'base_addon'       => $base_addon,
			'addon' 	       => $addon, 
			'folder_name'      => 'search-results', 
			'template_name'    => $template_name,
			'include_template' => $include
		);
		return GMW_Helper::get_template( $args );
	}

	/**
	 * Get info-window template
	 * @param  string $addon         [description]
	 * @param  string $template_name [description]
	 * @param  string $base_path     [description]
	 * @return [type]                [description]
	 */
	function gmw_get_info_window_template( $addon = 'posts_locator', $iw_type = 'popup', $template_name = 'default', $base_addon = '', $include = false ) {
		$args = array(
			'base_addon'       => $base_addon,
			'addon' 	       => $addon, 
			'folder_name'      => 'info-window', 
			'iw_type'		   => $iw_type,
			'template_name'    => $template_name,
			'include_template' => $include
		);
		return GMW_Helper::get_template( $args );
	}

/**
 * Element toggle button
 *
 * Will usually be used with Popup info-window
 * 
 * @param  array  $args [description]
 * @return [type]       [description]
 */
function gmw_get_element_toggle_button( $args = array() ) {

	$defaults = array( 
		'id' 		   => 0,
		'show_icon'    => 'gmw-icon-arrow-down',
		'hide_icon'    => 'gmw-icon-arrow-up',
		'target'	   => '#gmw-popup-info-window',
		'animation'    => 'height',
		'open_length'  => '100%',
		'close_length' => '35px',
		'duration'     => '100',
	);

	$args = wp_parse_args( $args, $defaults );
	$args = apply_filters( 'gmw_element_toggle_button_args', $args );

	return '<span class="gmw-element-toggle-button '.esc_attr( $args['hide_icon'] ).' visible" data-target="'.esc_attr( $args['target'] ).'" data-show_icon="'.esc_attr( $args['show_icon'] ).'" data-hide_icon="'.esc_attr( $args['hide_icon'] ).'" data-animation="'.esc_attr( $args['animation'] ).'" data-open_length="'.esc_attr( $args['open_length'] ).'" data-close_length="'.esc_attr( $args['close_length'] ).'" data-duration="'.esc_attr( $args['data_duration'] ).'"></span>';
}
	
	/**
	 * Display toggle button in info window
	 * @param  array  $args [description]
	 * @return [type]       [description]
	 */
	function gmw_element_toggle_button( $args = array() ) {
		echo gmw_get_element_toggle_button( $args );
	}

	/**
	 * Toggle button for left side info-window
	 * @return [type] [description]
	 */
	function gmw_left_window_toggle_button() { 
	    gmw_element_toggle_button( array( 
	        'animation'    => 'width', 
	        'open_length'  => '100%',
	        'close_length' => '30px', 
	        'hide_icon'    => 'gmw-icon-arrow-left', 
	        'show_icon'    => 'gmw-icon-arrow-right' 
	    ) ); 
	}

/**
 * Close button for info window
 * 
 * @param unknown_type $post
 * @param unknown_type $gmw
 */
function gmw_get_element_close_button( $icon = 'gmw-icon-cancel-circled' ) {
	return	'<span class="iw-close-button '.esc_attr( $icon ).'"></span>';
}
	function gmw_element_close_button( $icon = 'gmw-icon-cancel-circled' ) {
		echo gmw_get_element_close_button( $icon );
	}

/**
 * Info window dragging element
 * 
 * @param unknown_type $post
 * @param unknown_type $gmw
 */
function gmw_get_element_dragging_handle( $args = array() ) {

	$defaults = array( 
		'icon'   	  => 'gmw-icon-menu',
		'target' 	  => 'gmw-popup-info-window',
		'containment' => 'window'
	);
	
	$args = wp_parse_args( $args, $defaults );
	$args = apply_filters( 'gmw_draggable_button_args', $args );

	return	'<span class="gmw-draggable '.esc_attr( $args['icon'] ).'" data-draggable="'.esc_attr( $args['target'] ).'" data-containment="'.esc_attr( $args['containment'] ).'"></span>';
}
	function gmw_element_dragging_handle( $args = array() ) {
		echo gmw_get_element_dragging_handle( $args );
	}

/**
 * Create new map element
 *
 * Pass the arguments to display a map. Each element created is pushed into the global map elements.
 * The global map elements pass to the map.js file. The map.js loop through the map elements
 * and generates each map based on the arguments passed to the function.
 *
 * More information about google maps API can be found here - https://developers.google.com/maps/documentation/javascript/reference#MapOptions
 */
function gmw_new_map_element( $map_args = array(), $map_options = array(), $locations = array(), $user_position = array(), $form = array() ) {
	return GMW_Maps_API::get_map_args( $map_args, $map_options, $locations, $user_position, $form );
}

/**
 * Get map element 
 * 
 * @param  array  $map_args      [description]
 * @param  array  $map_options   [description]
 * @param  array  $locations     [description]
 * @param  array  $user_position [description]
 * @param  array  $form          [description]
 * @return [type]                [description]
 */
function gmw_get_map( $map_args = array(), $map_options = array(), $locations = array(), $user_position = array(), $form = array() ) {
	return GMW_Maps_API::get_map( $map_args, $map_options, $locations, $user_position, $form );	
}

/**
 * Get map element
 * 
 * @param  array  $args [description]
 * @return [type]       [description]
 */
function gmw_get_map_element( $args = array() ) {
	return GMW_Maps_API::get_map_element( $args );
}

/**
 * Get directions system form
 * 
 * @param  array  $args [description]
 * @return [type]       [description]
 */
function gmw_get_directions_form( $args = array() ) {
	return GMW_Maps_API::get_directions_form( $args );
}

/**
 * Get directions system panel
 * 
 * @param unknown_type $info
 * @param unknown_type $gmw
 */
function gmw_get_directions_panel( $id = 0 ) {
	return GMW_Maps_API::get_directions_panel( $id );
}
	
/**
 * Get directions system
 * 
 * @param  array  $args [description]
 * @return [type]       [description]
 */
function gmw_get_directions_system( $args = array() ) {
	return GMW_Maps_API::get_directions_system( $args );
}

/**
 * Enqueue search form/results stylesheet earlier in the <HEAD> tag
 *
 * By default, since GEO my WP uses shortcodes to display its forms, search forms and search results stylesheet loads outside the <head> tag.
 * This can cause the search forms / results look out of styling for a short moment on page load. As well it can cause HTML validation error.
 * 
 * You can use this function to overcome this issue. Pass an array of the form id and the pages which you want to load the stylesheet early in the head.
 * 
 * @param  array array( 
 *         		'form_id' 	  => the id of the form to load its stylesheets,
 *           	'pages'        => array of pages ID where you'd like to load the form's stylesheets. Empty array to load on every page,
 *            	'folders_name' => array of the folders name to load early. Right now the function supports search-forms and search-results.
 *         );
 *         
 * @return void 
 */
function gmw_enqueue_form_styles( $args = array( 'form_id' => 0, 'pages' => array(), 'folders_name' => array( 'search-forms', 'search-results' ) ) ) {

	$page_id = get_the_ID();
		
	$form = gmw_get_form( $args['form_id'] );	

	// abort if form doesnt exist
	if ( empty( $form ) ) {
		return;
	}

	if ( empty( $args['pages'] ) || ( is_array( $args['pages'] ) && in_array( $page_id, $args['pages'] ) ) ) {
		
		// get the addon slug
		$addon_data = gmw_get_addon_data( $form['slug'] );

		if ( in_array( 'search-forms', $args['folders_name'] ) ) {

			$template = $form['search_form']['form_template'];

			// Get custom template and css from child/theme folder
			if ( strpos( $template, 'custom_' ) !== false ) {

				$template     	   = str_replace( 'custom_', '', $template );
				$stylesheet_handle = "gmw-{$addon_data['prefix']}-search-forms-custom-{$template}";
				$stylesheet_uri	   = get_stylesheet_directory_uri(). "/geo-my-wp/{$addon_data['templates_folder']}/search-forms/{$template}/css/style.css";
		
			// load template files from plugin's folder
			} else {
				$stylesheet_handle = "gmw-{$addon_data['prefix']}-search-forms-{$template}";
				$stylesheet_uri    = $addon_data['plugin_url']."/templates/search-forms/{$template}/css/style.css";
			}

			if ( ! wp_style_is( $stylesheet_handle, 'enqueued' ) ) {
				wp_enqueue_style( $stylesheet_handle, $stylesheet_uri, array(), GMW_VERSION );
			}
		}
		
		if ( in_array( 'search-results', $args['folders_name'] ) ) {

			$template = $form['search_results']['results_template'];

			// Get custom template and css from child/theme folder
			if ( strpos( $template, 'custom_' ) !== false ) {

				$template     	   = str_replace( 'custom_', '', $template );
				$stylesheet_handle = "gmw-{$addon_data['prefix']}-search-results-custom-{$template}";
				$stylesheet_uri	   = get_stylesheet_directory_uri(). "/geo-my-wp/{$addon_data['templates_folder']}/search-results/{$template}/css/style.css";
		
			// load template files from plugin's folder
			} else {
				$stylesheet_handle = "gmw-{$addon_data['prefix']}-search-results-{$template}";
				$stylesheet_uri    = $addon_data['plugin_url']."/templates/search-results/{$template}/css/style.css";
			}

			if ( ! wp_style_is( $stylesheet_handle, 'enqueued' ) ) {
				wp_enqueue_style( $stylesheet_handle, $stylesheet_uri, array(), GMW_VERSION );
			}
		}
	}	
}

/**
 * Info window content
 *
 * Generate the information that will be displayed in the info-window that opens when clicking on a map marker.
 *
 * The information can be modifyed via the filter below
 * 
 * @return [type]    [description]
 */
function gmw_get_info_window_content( $location, $args = array(), $gmw = array() ) {
	return GMW_Maps_API::get_info_window_content( $location, $args, $gmw );
}

/**
 * Array of countries that can be used for select dropdown.
 * 
 * @return array of countries
 */
function gmw_get_countries_list_array( $first = false ) {

	$countries = array(
		'0' => '',
		'AF' => 'Afghanistan',
		'AX' => 'Aland Islands',
		'AL' => 'Albania',
		'DZ' => 'Algeria',
		'AS' => 'American Samoa',
		'AD' => 'Andorra',
		'AO' => 'Angola',
		'AI' => 'Anguilla',
		'AQ' => 'Antarctica',
		'AG' => 'Antigua And Barbuda',
		'AR' => 'Argentina',
		'AM' => 'Armenia',
		'AW' => 'Aruba',
		'AU' => 'Australia',
		'AT' => 'Austria',
		'AZ' => 'Azerbaijan',
		'BS' => 'Bahamas',
		'BH' => 'Bahrain',
		'BD' => 'Bangladesh',
		'BB' => 'Barbados',
		'BY' => 'Belarus',
		'BE' => 'Belgium',
		'BZ' => 'Belize',
		'BJ' => 'Benin',
		'BM' => 'Bermuda',
		'BT' => 'Bhutan',
		'BO' => 'Bolivia',
		'BA' => 'Bosnia And Herzegovina',
		'BW' => 'Botswana',
		'BV' => 'Bouvet Island',
		'BR' => 'Brazil',
		'IO' => 'British Indian Ocean Territory',
		'BN' => 'Brunei Darussalam',
		'BG' => 'Bulgaria',
		'BF' => 'Burkina Faso',
		'BI' => 'Burundi',
		'KH' => 'Cambodia',
		'CM' => 'Cameroon',
		'CA' => 'Canada',
		'CV' => 'Cape Verde',
		'KY' => 'Cayman Islands',
		'CF' => 'Central African Republic',
		'TD' => 'Chad',
		'CL' => 'Chile',
		'CN' => 'China',
		'CX' => 'Christmas Island',
		'CC' => 'Cocos (Keeling) Islands',
		'CO' => 'Colombia',
		'KM' => 'Comoros',
		'CG' => 'Congo',
		'CD' => 'Congo, Democratic Republic',
		'CK' => 'Cook Islands',
		'CR' => 'Costa Rica',
		'CI' => 'Cote D\'Ivoire',
		'HR' => 'Croatia',
		'CU' => 'Cuba',
		'CY' => 'Cyprus',
		'CZ' => 'Czech Republic',
		'DK' => 'Denmark',
		'DJ' => 'Djibouti',
		'DM' => 'Dominica',
		'DO' => 'Dominican Republic',
		'EC' => 'Ecuador',
		'EG' => 'Egypt',
		'SV' => 'El Salvador',
		'GQ' => 'Equatorial Guinea',
		'ER' => 'Eritrea',
		'EE' => 'Estonia',
		'ET' => 'Ethiopia',
		'FK' => 'Falkland Islands (Malvinas)',
		'FO' => 'Faroe Islands',
		'FJ' => 'Fiji',
		'FI' => 'Finland',
		'FR' => 'France',
		'GF' => 'French Guiana',
		'PF' => 'French Polynesia',
		'TF' => 'French Southern Territories',
		'GA' => 'Gabon',
		'GM' => 'Gambia',
		'GE' => 'Georgia',
		'DE' => 'Germany',
		'GH' => 'Ghana',
		'GI' => 'Gibraltar',
		'GR' => 'Greece',
		'GL' => 'Greenland',
		'GD' => 'Grenada',
		'GP' => 'Guadeloupe',
		'GU' => 'Guam',
		'GT' => 'Guatemala',
		'GG' => 'Guernsey',
		'GN' => 'Guinea',
		'GW' => 'Guinea-Bissau',
		'GY' => 'Guyana',
		'HT' => 'Haiti',
		'HM' => 'Heard Island & Mcdonald Islands',
		'VA' => 'Holy See (Vatican City State)',
		'HN' => 'Honduras',
		'HK' => 'Hong Kong',
		'HU' => 'Hungary',
		'IS' => 'Iceland',
		'IN' => 'India',
		'ID' => 'Indonesia',
		'IR' => 'Iran, Islamic Republic Of',
		'IQ' => 'Iraq',
		'IE' => 'Ireland',
		'IM' => 'Isle Of Man',
		'IL' => 'Israel',
		'IT' => 'Italy',
		'JM' => 'Jamaica',
		'JP' => 'Japan',
		'JE' => 'Jersey',
		'JO' => 'Jordan',
		'KZ' => 'Kazakhstan',
		'KE' => 'Kenya',
		'KI' => 'Kiribati',
		'KR' => 'Korea',
		'KW' => 'Kuwait',
		'KG' => 'Kyrgyzstan',
		'LA' => 'Lao People\'s Democratic Republic',
		'LV' => 'Latvia',
		'LB' => 'Lebanon',
		'LS' => 'Lesotho',
		'LR' => 'Liberia',
		'LY' => 'Libyan Arab Jamahiriya',
		'LI' => 'Liechtenstein',
		'LT' => 'Lithuania',
		'LU' => 'Luxembourg',
		'MO' => 'Macao',
		'MK' => 'Macedonia',
		'MG' => 'Madagascar',
		'MW' => 'Malawi',
		'MY' => 'Malaysia',
		'MV' => 'Maldives',
		'ML' => 'Mali',
		'MT' => 'Malta',
		'MH' => 'Marshall Islands',
		'MQ' => 'Martinique',
		'MR' => 'Mauritania',
		'MU' => 'Mauritius',
		'YT' => 'Mayotte',
		'MX' => 'Mexico',
		'FM' => 'Micronesia, Federated States Of',
		'MD' => 'Moldova',
		'MC' => 'Monaco',
		'MN' => 'Mongolia',
		'ME' => 'Montenegro',
		'MS' => 'Montserrat',
		'MA' => 'Morocco',
		'MZ' => 'Mozambique',
		'MM' => 'Myanmar',
		'NA' => 'Namibia',
		'NR' => 'Nauru',
		'NP' => 'Nepal',
		'NL' => 'Netherlands',
		'AN' => 'Netherlands Antilles',
		'NC' => 'New Caledonia',
		'NZ' => 'New Zealand',
		'NI' => 'Nicaragua',
		'NE' => 'Niger',
		'NG' => 'Nigeria',
		'NU' => 'Niue',
		'NF' => 'Norfolk Island',
		'MP' => 'Northern Mariana Islands',
		'NO' => 'Norway',
		'OM' => 'Oman',
		'PK' => 'Pakistan',
		'PW' => 'Palau',
		'PS' => 'Palestinian Territory, Occupied',
		'PA' => 'Panama',
		'PG' => 'Papua New Guinea',
		'PY' => 'Paraguay',
		'PE' => 'Peru',
		'PH' => 'Philippines',
		'PN' => 'Pitcairn',
		'PL' => 'Poland',
		'PT' => 'Portugal',
		'PR' => 'Puerto Rico',
		'QA' => 'Qatar',
		'RE' => 'Reunion',
		'RO' => 'Romania',
		'RU' => 'Russian Federation',
		'RW' => 'Rwanda',
		'BL' => 'Saint Barthelemy',
		'SH' => 'Saint Helena',
		'KN' => 'Saint Kitts And Nevis',
		'LC' => 'Saint Lucia',
		'MF' => 'Saint Martin',
		'PM' => 'Saint Pierre And Miquelon',
		'VC' => 'Saint Vincent And Grenadines',
		'WS' => 'Samoa',
		'SM' => 'San Marino',
		'ST' => 'Sao Tome And Principe',
		'SA' => 'Saudi Arabia',
		'SN' => 'Senegal',
		'RS' => 'Serbia',
		'SC' => 'Seychelles',
		'SL' => 'Sierra Leone',
		'SG' => 'Singapore',
		'SK' => 'Slovakia',
		'SI' => 'Slovenia',
		'SB' => 'Solomon Islands',
		'SO' => 'Somalia',
		'ZA' => 'South Africa',
		'GS' => 'South Georgia And Sandwich Isl.',
		'ES' => 'Spain',
		'LK' => 'Sri Lanka',
		'SD' => 'Sudan',
		'SR' => 'Suriname',
		'SJ' => 'Svalbard And Jan Mayen',
		'SZ' => 'Swaziland',
		'SE' => 'Sweden',
		'CH' => 'Switzerland',
		'SY' => 'Syrian Arab Republic',
		'TW' => 'Taiwan',
		'TJ' => 'Tajikistan',
		'TZ' => 'Tanzania',
		'TH' => 'Thailand',
		'TL' => 'Timor-Leste',
		'TG' => 'Togo',
		'TK' => 'Tokelau',
		'TO' => 'Tonga',
		'TT' => 'Trinidad And Tobago',
		'TN' => 'Tunisia',
		'TR' => 'Turkey',
		'TM' => 'Turkmenistan',
		'TC' => 'Turks And Caicos Islands',
		'TV' => 'Tuvalu',
		'UG' => 'Uganda',
		'UA' => 'Ukraine',
		'AE' => 'United Arab Emirates',
		'GB' => 'United Kingdom',
		'US' => 'United States',
		'UM' => 'United States Outlying Islands',
		'UY' => 'Uruguay',
		'UZ' => 'Uzbekistan',
		'VU' => 'Vanuatu',
		'VE' => 'Venezuela',
		'VN' => 'Viet Nam',
		'VG' => 'Virgin Islands, British',
		'VI' => 'Virgin Islands, U.S.',
		'WF' => 'Wallis And Futuna',
		'EH' => 'Western Sahara',
		'YE' => 'Yemen',
		'ZM' => 'Zambia',
		'ZW' => 'Zimbabwe',
	);

	if ( empty( $first ) ) {
		unset( $countries[0] );
	} else {
		$countries[0] = $first;
	}

	return $countries;
}

function gmw_get_countries_array() {

	return $Countries = [
        [ 'code' => 'US', 'name' => 'United States'],
        [ 'code' => 'CA', 'name' => 'Canada'],
        [ 'code' => 'AU', 'name' => 'Australia'],
        [ 'code' => 'FR', 'name' => 'France'],
        [ 'code' => 'DE', 'name' => 'Germany'],
        [ 'code' => 'IS', 'name' => 'Iceland'],
        [ 'code' => 'IE', 'name' => 'Ireland'],
        [ 'code' => 'IT', 'name' => 'Italy'],
        [ 'code' => 'ES', 'name' => 'Spain'],
        [ 'code' => 'SE', 'name' => 'Sweden'],
        [ 'code' => 'AT', 'name' => 'Austria'],
        [ 'code' => 'BE', 'name' => 'Belgium'],
        [ 'code' => 'FI', 'name' => 'Finland'],
        [ 'code' => 'CZ', 'name' => 'Czech Republic'],
        [ 'code' => 'DK', 'name' => 'Denmark'],
        [ 'code' => 'NO', 'name' => 'Norway'],
        [ 'code' => 'GB', 'name' => 'United Kingdom'],
        [ 'code' => 'CH', 'name' => 'Switzerland'],
        [ 'code' => 'NZ', 'name' => 'New Zealand'],
        [ 'code' => 'RU', 'name' => 'Russian Federation'],
        [ 'code' => 'PT', 'name' => 'Portugal'],
        [ 'code' => 'NL', 'name' => 'Netherlands'],
        [ 'code' => 'IM', 'name' => 'Isle of Man'],
        [ 'code' => 'AF', 'name' => 'Afghanistan'],
        [ 'code' => 'AX', 'name' => 'Aland Islands '],
        [ 'code' => 'AL', 'name' => 'Albania'],
        [ 'code' => 'DZ', 'name' => 'Algeria'],
        [ 'code' => 'AS', 'name' => 'American Samoa'],
        [ 'code' => 'AD', 'name' => 'Andorra'],
        [ 'code' => 'AO', 'name' => 'Angola'],
        [ 'code' => 'AI', 'name' => 'Anguilla'],
        [ 'code' => 'AQ', 'name' => 'Antarctica'],
        [ 'code' => 'AG', 'name' => 'Antigua and Barbuda'],
        [ 'code' => 'AR', 'name' => 'Argentina'],
        [ 'code' => 'AM', 'name' => 'Armenia'],
        [ 'code' => 'AW', 'name' => 'Aruba'],
        [ 'code' => 'AZ', 'name' => 'Azerbaijan'],
        [ 'code' => 'BS', 'name' => 'Bahamas'],
        [ 'code' => 'BH', 'name' => 'Bahrain'],
        [ 'code' => 'BD', 'name' => 'Bangladesh'],
        [ 'code' => 'BB', 'name' => 'Barbados'],
        [ 'code' => 'BY', 'name' => 'Belarus'],
        [ 'code' => 'BZ', 'name' => 'Belize'],
        [ 'code' => 'BJ', 'name' => 'Benin'],
        [ 'code' => 'BM', 'name' => 'Bermuda'],
        [ 'code' => 'BT', 'name' => 'Bhutan'],
        [ 'code' => 'BO', 'name' => 'Bolivia, Plurinational State of'],
        [ 'code' => 'BQ', 'name' => 'Bonaire, Sint Eustatius and Saba'],
        [ 'code' => 'BA', 'name' => 'Bosnia and Herzegovina'],
        [ 'code' => 'BW', 'name' => 'Botswana'],
        [ 'code' => 'BV', 'name' => 'Bouvet Island'],
        [ 'code' => 'BR', 'name' => 'Brazil'],
        [ 'code' => 'IO', 'name' => 'British Indian Ocean Territory'],
        [ 'code' => 'BN', 'name' => 'Brunei Darussalam'],
        [ 'code' => 'BG', 'name' => 'Bulgaria'],
        [ 'code' => 'BF', 'name' => 'Burkina Faso'],
        [ 'code' => 'BI', 'name' => 'Burundi'],
        [ 'code' => 'KH', 'name' => 'Cambodia'],
        [ 'code' => 'CM', 'name' => 'Cameroon'],
        [ 'code' => 'CV', 'name' => 'Cape Verde'],
        [ 'code' => 'KY', 'name' => 'Cayman Islands'],
        [ 'code' => 'CF', 'name' => 'Central African Republic'],
        [ 'code' => 'TD', 'name' => 'Chad'],
        [ 'code' => 'CL', 'name' => 'Chile'],
        [ 'code' => 'CN', 'name' => 'China'],
        [ 'code' => 'CX', 'name' => 'Christmas Island'],
        [ 'code' => 'CC', 'name' => 'Cocos (Keeling) Islands'],
        [ 'code' => 'CO', 'name' => 'Colombia'],
        [ 'code' => 'KM', 'name' => 'Comoros'],
        [ 'code' => 'CG', 'name' => 'Congo'],
        [ 'code' => 'CD', 'name' => 'Congo, the Democratic Republic of the'],
        [ 'code' => 'CK', 'name' => 'Cook Islands'],
        [ 'code' => 'CR', 'name' => 'Costa Rica'],
        [ 'code' => 'CI', 'name' => 'Cote d\'Ivoire'],
        [ 'code' => 'HR', 'name' => 'Croatia'],
        [ 'code' => 'CU', 'name' => 'Cuba'],
        [ 'code' => 'CW', 'name' => 'Curaçao'],
        [ 'code' => 'CY', 'name' => 'Cyprus'],
        [ 'code' => 'DJ', 'name' => 'Djibouti'],
        [ 'code' => 'DM', 'name' => 'Dominica'],
        [ 'code' => 'DO', 'name' => 'Dominican Republic'],
        [ 'code' => 'EC', 'name' => 'Ecuador'],
        [ 'code' => 'EG', 'name' => 'Egypt'],
        [ 'code' => 'SV', 'name' => 'El Salvador'],
        [ 'code' => 'GQ', 'name' => 'Equatorial Guinea'],
        [ 'code' => 'ER', 'name' => 'Eritrea'],
        [ 'code' => 'EE', 'name' => 'Estonia'],
        [ 'code' => 'ET', 'name' => 'Ethiopia'],
        [ 'code' => 'FK', 'name' => 'Falkland Islands (Malvinas)'],
        [ 'code' => 'FO', 'name' => 'Faroe Islands'],
        [ 'code' => 'FJ', 'name' => 'Fiji'],
        [ 'code' => 'GF', 'name' => 'French Guiana'],
        [ 'code' => 'PF', 'name' => 'French Polynesia'],
        [ 'code' => 'TF', 'name' => 'French Southern Territories'],
        [ 'code' => 'GA', 'name' => 'Gabon'],
        [ 'code' => 'GM', 'name' => 'Gambia'],
        [ 'code' => 'GE', 'name' => 'Georgia'],
        [ 'code' => 'GH', 'name' => 'Ghana'],
        [ 'code' => 'GI', 'name' => 'Gibraltar'],
        [ 'code' => 'GR', 'name' => 'Greece'],
        [ 'code' => 'GL', 'name' => 'Greenland'],
        [ 'code' => 'GD', 'name' => 'Grenada'],
        [ 'code' => 'GP', 'name' => 'Guadeloupe'],
        [ 'code' => 'GU', 'name' => 'Guam'],
        [ 'code' => 'GT', 'name' => 'Guatemala'],
        [ 'code' => 'GG', 'name' => 'Guernsey'],
        [ 'code' => 'GN', 'name' => 'Guinea'],
        [ 'code' => 'GW', 'name' => 'Guinea-Bissau'],
        [ 'code' => 'GY', 'name' => 'Guyana'],
        [ 'code' => 'HT', 'name' => 'Haiti'],
        [ 'code' => 'HM', 'name' => 'Heard Island and McDonald Islands'],
        [ 'code' => 'VA', 'name' => 'Holy See (Vatican City State)'],
        [ 'code' => 'HN', 'name' => 'Honduras'],
        [ 'code' => 'HK', 'name' => 'Hong Kong'],
        [ 'code' => 'HU', 'name' => 'Hungary'],
        [ 'code' => 'IN', 'name' => 'India'],
        [ 'code' => 'ID', 'name' => 'Indonesia'],
        [ 'code' => 'IR', 'name' => 'Iran, Islamic Republic of'],
        [ 'code' => 'IQ', 'name' => 'Iraq'],
        [ 'code' => 'IL', 'name' => 'Israel'],
        [ 'code' => 'JM', 'name' => 'Jamaica'],
        [ 'code' => 'JP', 'name' => 'Japan'],
        [ 'code' => 'JE', 'name' => 'Jersey'],
        [ 'code' => 'JO', 'name' => 'Jordan'],
        [ 'code' => 'KZ', 'name' => 'Kazakhstan'],
        [ 'code' => 'KE', 'name' => 'Kenya'],
        [ 'code' => 'KI', 'name' => 'Kiribati'],
        [ 'code' => 'KP', 'name' => 'Korea, Democratic People\'s Republic of'],
        [ 'code' => 'KR', 'name' => 'Korea, Republic of'],
        [ 'code' => 'KW', 'name' => 'Kuwait'],
        [ 'code' => 'KG', 'name' => 'Kyrgyzstan'],
        [ 'code' => 'LA', 'name' => 'Lao People\'s Democratic Republic'],
        [ 'code' => 'LV', 'name' => 'Latvia'],
        [ 'code' => 'LB', 'name' => 'Lebanon'],
        [ 'code' => 'LS', 'name' => 'Lesotho'],
        [ 'code' => 'LR', 'name' => 'Liberia'],
        [ 'code' => 'LY', 'name' => 'Libyan Arab Jamahiriya'],
        [ 'code' => 'LI', 'name' => 'Liechtenstein'],
        [ 'code' => 'LT', 'name' => 'Lithuania'],
        [ 'code' => 'LU', 'name' => 'Luxembourg'],
        [ 'code' => 'MO', 'name' => 'Macao'],
        [ 'code' => 'MK', 'name' => 'Macedonia'],
        [ 'code' => 'MG', 'name' => 'Madagascar'],
        [ 'code' => 'MW', 'name' => 'Malawi'],
        [ 'code' => 'MY', 'name' => 'Malaysia'],
        [ 'code' => 'MV', 'name' => 'Maldives'],
        [ 'code' => 'ML', 'name' => 'Mali'],
        [ 'code' => 'MT', 'name' => 'Malta'],
        [ 'code' => 'MH', 'name' => 'Marshall Islands'],
        [ 'code' => 'MQ', 'name' => 'Martinique'],
        [ 'code' => 'MR', 'name' => 'Mauritania'],
        [ 'code' => 'MU', 'name' => 'Mauritius'],
        [ 'code' => 'YT', 'name' => 'Mayotte'],
        [ 'code' => 'MX', 'name' => 'Mexico'],
        [ 'code' => 'FM', 'name' => 'Micronesia, Federated States of'],
        [ 'code' => 'MD', 'name' => 'Moldova, Republic of'],
        [ 'code' => 'MC', 'name' => 'Monaco'],
        [ 'code' => 'MN', 'name' => 'Mongolia'],
        [ 'code' => 'ME', 'name' => 'Montenegro'],
        [ 'code' => 'MS', 'name' => 'Montserrat'],
        [ 'code' => 'MA', 'name' => 'Morocco'],
        [ 'code' => 'MZ', 'name' => 'Mozambique'],
        [ 'code' => 'MM', 'name' => 'Myanmar'],
        [ 'code' => 'NA', 'name' => 'Namibia'],
        [ 'code' => 'NR', 'name' => 'Nauru'],
        [ 'code' => 'NP', 'name' => 'Nepal'],
        [ 'code' => 'NC', 'name' => 'New Caledonia'],
        [ 'code' => 'NI', 'name' => 'Nicaragua'],
        [ 'code' => 'NE', 'name' => 'Niger'],
        [ 'code' => 'NG', 'name' => 'Nigeria'],
        [ 'code' => 'NU', 'name' => 'Niue'],
        [ 'code' => 'NF', 'name' => 'Norfolk Island'],
        [ 'code' => 'MP', 'name' => 'Northern Mariana Islands'],
        [ 'code' => 'OM', 'name' => 'Oman'],
        [ 'code' => 'PK', 'name' => 'Pakistan'],
        [ 'code' => 'PW', 'name' => 'Palau'],
        [ 'code' => 'PS', 'name' => 'Palestinian Territory, Occupied'],
        [ 'code' => 'PA', 'name' => 'Panama'],
        [ 'code' => 'PG', 'name' => 'Papua New Guinea'],
        [ 'code' => 'PY', 'name' => 'Paraguay'],
        [ 'code' => 'PE', 'name' => 'Peru'],
        [ 'code' => 'PH', 'name' => 'Philippines'],
        [ 'code' => 'PN', 'name' => 'Pitcairn'],
        [ 'code' => 'PL', 'name' => 'Poland'],
        [ 'code' => 'PR', 'name' => 'Puerto Rico'],
        [ 'code' => 'QA', 'name' => 'Qatar'],
        [ 'code' => 'RE', 'name' => 'Reunion'],
        [ 'code' => 'RO', 'name' => 'Romania'],
        [ 'code' => 'RW', 'name' => 'Rwanda'],
        [ 'code' => 'BL', 'name' => 'Saint Barthélemy'],
        [ 'code' => 'SH', 'name' => 'Saint Helena'],
        [ 'code' => 'KN', 'name' => 'Saint Kitts and Nevis'],
        [ 'code' => 'LC', 'name' => 'Saint Lucia'],
        [ 'code' => 'MF', 'name' => 'Saint Martin (French part)'],
        [ 'code' => 'PM', 'name' => 'Saint Pierre and Miquelon'],
        [ 'code' => 'VC', 'name' => 'Saint Vincent and the Grenadines'],
        [ 'code' => 'WS', 'name' => 'Samoa'],
        [ 'code' => 'SM', 'name' => 'San Marino'],
        [ 'code' => 'ST', 'name' => 'Sao Tome and Principe'],
        [ 'code' => 'SA', 'name' => 'Saudi Arabia'],
        [ 'code' => 'SN', 'name' => 'Senegal'],
        [ 'code' => 'RS', 'name' => 'Serbia'],
        [ 'code' => 'SC', 'name' => 'Seychelles'],
        [ 'code' => 'SL', 'name' => 'Sierra Leone'],
        [ 'code' => 'SG', 'name' => 'Singapore'],
        [ 'code' => 'SX', 'name' => 'Sint Maarten (Dutch part)'],
        [ 'code' => 'SK', 'name' => 'Slovakia'],
        [ 'code' => 'SI', 'name' => 'Slovenia'],
        [ 'code' => 'SB', 'name' => 'Solomon Islands'],
        [ 'code' => 'SO', 'name' => 'Somalia'],
        [ 'code' => 'ZA', 'name' => 'South Africa'],
        [ 'code' => 'GS', 'name' => 'South Georgia and the South Sandwich Islands'],
        [ 'code' => 'LK', 'name' => 'Sri Lanka'],
        [ 'code' => 'SD', 'name' => 'Sudan'],
        [ 'code' => 'SR', 'name' => 'Suriname'],
        [ 'code' => 'SJ', 'name' => 'Svalbard and Jan Mayen'],
        [ 'code' => 'SZ', 'name' => 'Swaziland'],
        [ 'code' => 'SY', 'name' => 'Syrian Arab Republic'],
        [ 'code' => 'TW', 'name' => 'Taiwan, Province of China'],
        [ 'code' => 'TJ', 'name' => 'Tajikistan'],
        [ 'code' => 'TZ', 'name' => 'Tanzania, United Republic of'],
        [ 'code' => 'TH', 'name' => 'Thailand'],
        [ 'code' => 'TL', 'name' => 'Timor-Leste'],
        [ 'code' => 'TG', 'name' => 'Togo'],
        [ 'code' => 'TK', 'name' => 'Tokelau'],
        [ 'code' => 'TO', 'name' => 'Tonga'],
        [ 'code' => 'TT', 'name' => 'Trinidad and Tobago'],
        [ 'code' => 'TN', 'name' => 'Tunisia'],
        [ 'code' => 'TR', 'name' => 'Turkey'],
        [ 'code' => 'TM', 'name' => 'Turkmenistan'],
        [ 'code' => 'TC', 'name' => 'Turks and Caicos Islands'],
        [ 'code' => 'TV', 'name' => 'Tuvalu'],
        [ 'code' => 'UG', 'name' => 'Uganda'],
        [ 'code' => 'UA', 'name' => 'Ukraine'],
        [ 'code' => 'AE', 'name' => 'United Arab Emirates'],
        [ 'code' => 'UM', 'name' => 'United States Minor Outlying Islands'],
        [ 'code' => 'UY', 'name' => 'Uruguay'],
        [ 'code' => 'UZ', 'name' => 'Uzbekistan'],
        [ 'code' => 'VU', 'name' => 'Vanuatu'],
        [ 'code' => 'VE', 'name' => 'Venezuela, Bolivarian Republic of'],
        [ 'code' => 'VN', 'name' => 'Viet Nam'],
        [ 'code' => 'VG', 'name' => 'Virgin Islands, British'],
        [ 'code' => 'VI', 'name' => 'Virgin Islands, U.S.'],
        [ 'code' => 'WF', 'name' => 'Wallis and Futuna'],
        [ 'code' => 'EH', 'name' => 'Western Sahara'],
        [ 'code' => 'YE', 'name' => 'Yemen'],
        [ 'code' => 'ZM', 'name' => 'Zambia'],
        [ 'code' => 'ZW', 'name' => 'Zimbabwe']
    ];
}
?>