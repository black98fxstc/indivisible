<?php

/*
 * Plugin Name: Indivisible Plugin
 * Plugin URI: https://developer.wordpress.org/plugins/the-basics/
 * Description: Provides support Indivisible Politicians, Legislation and Actions
 * Version: 20160911
 * Author: Wayne A. Moore
 * Author URI: https://black-softail.net
 * License: GPL2
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain: indivisible-text-domain
 * Domain Path: /languages
 */
define( 'PUBLIC_STATIC_URL', 'https://static.state-strong.org/' );

//define( 'OPEN_STATES_URL', "https://api.state-strong.org/open-states/" );
//define( 'LEGISCAN_URL',    "https://api.state-strong.org/legiscan/" );
//define( 'CIVIC_KEY_URL', 'http://api.state-strong.org/civic-key/');
// define( 'STATIC_URL', 'https://static.state-strong.org/' );
//define( 'STATIC_URL', 'http://127.0.0.1:8082/' );
//define( 'LEGISCAN_URL', "http://127.0.0.1:8084/legiscan/indv-plugin.php/" );
//define( 'CIVIC_KEY_URL', 'http://127.0.0.1:8085/civic-key/');
define( 'STATIC_URL', 'http://static/' );
define( 'LEGISCAN_URL', 'http://legiscan/legiscan/indv-plugin.php/' );
define( 'CIVIC_KEY_URL', 'http://civic-key:8080/civic-key/');

define( 'OPEN_STATES_URL', STATIC_URL . 'open-states/' );
define( 'CONGRESS_URL', STATIC_URL . 'congress/' );
define( 'CONGRESS_CURRENT', '115' );

// post types
define( 'INDV_POLITICIAN', 'indv_politician' );
define( 'INDV_LEGISLATION', 'indv_legislation' );
define( 'INDV_ACTION', 'indv_action' );
define( 'INDV_INTEREST', 'indv_interest' );
define( 'INDV_ISSUE', 'indv_issue' );

// meta data keys
define( 'INDV_LEXICON', '_indv_lexicon' );
define( 'INDV_POSITION', '_indv_position' );
define( 'INDV_BILL_STATUS', '_indv_bill_status' );
define( 'INDV_PHOTO_URL', '_indv_photo_url' );
define( 'INDV_CONTACT', '_indv_contact' );
define( 'INDV_VOTES', '_indv_votes');
define( 'INDV_VOTE_SCORE', '_indv_vote_score');

// html ids
define( 'INDV_PLUGIN_BOX_',   'indv_plugin_box_' );
define( 'INDV_PLUGIN_NONCE_', 'indv_plugin_nonce_' );

// lexicon
define( 'INDV_LEXICON_LEGISCAN',     'legiscan' );
define( 'INDV_LEXICON_OPEN_STATES',  'open_states' );
define( 'INDV_LEXICON_VOTE_SMART',   'vote_smart' );
define( 'INDV_LEXICON_BALLOTPEDIA',  'ballotpedia' );
define( 'INDV_LEXICON_OPEN_SECRETS', 'open_secrets' );
define( 'INDV_LEXICON_FOLLOW_THE_MONEY', 'follow_the_money' );
define( 'INDV_LEXICON_BIOGUIDE_ID',   'bioguide_id' );
define( 'INDV_LEXICON_GOOGLE_ENTITY', 'google_entity_id' );
define( 'INDV_LEXICON_GOVTRACK', 'govtrack' );
define( 'INDV_LEXICON_FEC_ID', 'fec_id' );

define( 'INDIVISIBLE_TEXT_DOMAIN',  'indivisible-text-domain' );

add_action ( 'init', 'indv_plugin_post_types' );
add_action ( 'init', 'indv_plugin_taxonomies' );
add_action ( 'rest_api_init', 'indv_plugin_rest_init' );
add_action ( 'add_meta_boxes', 'indv_plugin_twitter', 90 );
add_action ( 'add_meta_boxes', 'indv_plugin_meta_boxes', 10, 3 );
add_action ( 'manage_pages_custom_column', 'indv_plugin_column', 10, 2 );
add_action ( 'edit_form_before_permalink', 'indv_plugin_preamble' );
add_action ( 'get_sample_permalink_html',  'indv_plugin_permalink', 10, 5 );
add_action ( 'save_post_indv_politician',  'indv_plugin_save_post', 10, 3 );
add_action ( 'save_post_indv_legislation', 'indv_plugin_save_post', 10, 3 );
// add_action ( 'pre_get_posts', 'indv_plugin_orderby', 10, 1  );
add_action ( 'pre_get_posts', 'indv_plugin_geography_filter', 1, 1 );
add_action ( 'admin_notices', 'indv_plugin_errors' );
add_action ( 'restrict_manage_posts','indv_plugin_taxonomy_select', 10, 2);
add_action ( 'admin_head', 'indv_plugin_admin_style', 10, 1 );
add_action ( 'load-edit.php', 'indv_plugin_add_help' );
add_action ( 'load-post.php', 'indv_plugin_add_help' );
add_action ( 'load-post-new.php', 'indv_plugin_add_help' );
add_action ( 'indv_plugin_politician_add', 'indv_plugin_add_politician', 10, 5 );

add_filter ( 'manage_indv_politician_posts_columns',  'indv_plugin_columns_politician', 10, 1 );
add_filter ( 'manage_edit-indv_politician_sortable_columns', 'indv_plugin_sortable_politician', 10, 1 );
add_filter ( 'manage_indv_legislation_posts_columns', 'indv_plugin_columns_legislation', 10, 1 );

add_filter ( 'query_vars', function ( $vars ) {
	$vars[] = 'lat';
	$vars[] = 'lng';
	$vars[] = 'by_name';
	return $vars;
});

if (is_admin ()) { // admin actions
	add_action ( 'admin_init', 'indv_plugin_settings' );
	add_action ( 'admin_menu', 'indv_plugin_menu' );
}

register_activation_hook ( __FILE__, 'indv_plugin_install' );
register_deactivation_hook ( __FILE__, 'indv_plugin_deactivation' );

add_action ( 'indv_plugin_cron_hook', 'indv_plugin_cron_update' );
if (! wp_next_scheduled ( 'indv_plugin_cron_hook' )) {
	wp_schedule_event ( time (), 'hourly', 'indv_plugin_cron_hook' );
}

function indv_plugin_install() {
	// trigger our function that registers the custom post type
	indv_plugin_post_types();
	indv_plugin_taxonomies();
	foreach (array( 'Interested', 'Support', 'Oppose') as $term)
		if (!term_exists($term, INDV_POSITION))
			wp_insert_term($term, INDV_POSITION);
	foreach (array( 'Introduced',
			'1st House Policy', '1st House Appropriations', '1st House Floor', 
			'2nd House Policy', '2nd House Appropriations', '2nd House Floor',
			'Govenor Signed' ) as $term)
		if (!term_exists($term, INDV_BILL_STATUS))
			wp_insert_term($term, INDV_BILL_STATUS);
	delete_option('legislatures');
	delete_option('states');
			
	// clear the permalinks after the post type has been registered
	flush_rewrite_rules ();
}

function indv_plugin_deactivation() {
	// our post type will be automatically removed, so no need to unregister it
	
	// clear the permalinks to remove our post type's rules
	flush_rewrite_rules ();
	
	// remove cron
	$timestamp = wp_next_scheduled ( 'indv_plugin_cron_hook' );
	wp_unschedule_event ( $timestamp, 'indv_plugin_cron_hook' );
	wp_clear_scheduled_hook( 'indv_plugin_politician_add' );
}

function indv_plugin_cron_update() {
}

function indv_plugin_twitter() {
	remove_meta_box ( 'twitter-custom', get_current_screen (), 'advanced' );
	remove_meta_box ( 'twitter-custom', get_current_screen (), 'normal' );
}

class Indivisible_Plugin {
	private $legiscan_bill = array();
	private $open_states_bill = array();
	private $lexicon = array();
	private $cache = array();
	
	public function getLegiscanBill($post_id) {
		$id = (int) $post_id;
		$bill = $this->legiscan_bill[$id];
		if ($bill)
			return $bill;

		$lexicon = $this->get_lexicon($id);
		$url = LEGISCAN_URL . "?op=getBill&id=" . $lexicon[INDV_LEXICON_LEGISCAN];
		$legiscan = $this->get_json($url);
		if ($legiscan['status'] == "OK")
			$bill = $this->legiscan_bill[$id] = $legiscan['bill'];
				
		return $bill;
	}
	
	public function getOpenStatesBill($post_id) {
		$id = (int) $post_id;
		$bill = $this->open_states_bill[$id];
		if ($bill)
			return $bill;
			
		$lexicon = $this->get_lexicon($id);
		$url = OPEN_STATES_URL . "bills/" . $lexicon[INDV_LEXICON_OPEN_STATES];
		$bill = $this->open_states_bill[$id] = $this->get_json($url);
		
		return $bill;
	}
	
	public function get_json ($endpoint) {
		$cached = $this->cache[$endpoint];
		if ($cached)
			return $cached;
		
		$response = wp_remote_get($endpoint);
		$raw_body = wp_remote_retrieve_body($response);
		$results = json_decode ( $raw_body, true );
		$this->cache[$endpoint] = $results;
		return $results;
	}
	
	public function get_congress ($endpoint) {
		$url = CONGRESS_URL . $endpoint . '.json';
		$result =$this->get_json($url);
		if ($result['status'] == 'OK')
			return $result['results'];
	}
	
	public function get_legiscan ($endpoint) {
		$url = LEGISCAN_URL . $endpoint;
		$result =$this->get_json($url);
		if ($result['status'] == 'OK')
			return $result;
	}
	
	public function legiscan_lookup ( $key ) {
		$endpoint = "?op=search&state=" . $key[0] . "&bill=" . $key[1] . "&year=" . $key[2];
		$results = $this->get_legiscan($endpoint);
		if (empty($results) || count($results) != 2)
			return false;
		return $results['searchresult'][0];
	}
	
	public function get_open_states ($endpoint) {
		$url = OPEN_STATES_URL . $endpoint;
		$result =$this->get_json($url);
		return $result;	
	}
	
	public function get_lexicon ($post_id) {
		$id = (int) $post_id;
		$lexicon = $this->lexicon[$id];
		if (!$lexicon)
			$lexicon = $this->lexicon[$id] = get_post_meta($post_id, INDV_LEXICON, true);
		return $lexicon;
	}
}

global $indv;
$indv = new Indivisible_Plugin();

/////////////////////////////////////////////////////

function indv_plugin_post_types() {
	$args = array (
			'label' => esc_html__ ( 'Politician', INDIVISIBLE_TEXT_DOMAIN ),
			'labels' => array (
					'menu_name' => esc_html__ ( 'Politicians', INDIVISIBLE_TEXT_DOMAIN ),
					'name_admin_bar' => esc_html__ ( 'Politicians', INDIVISIBLE_TEXT_DOMAIN ),
					'add_new' => esc_html__ ( 'Find More', INDIVISIBLE_TEXT_DOMAIN ),
					'add_new_item' => esc_html__ ( 'Find Another Politician', INDIVISIBLE_TEXT_DOMAIN ),
					'new_item' => esc_html__ ( 'New Politician', INDIVISIBLE_TEXT_DOMAIN ),
					'edit_item' => esc_html__ ( 'Edit Politician', INDIVISIBLE_TEXT_DOMAIN ),
					'view_item' => esc_html__ ( 'View Politician', INDIVISIBLE_TEXT_DOMAIN ),
					'update_item' => esc_html__ ( 'Update Politician', INDIVISIBLE_TEXT_DOMAIN ),
					'all_items' => esc_html__ ( 'Following', INDIVISIBLE_TEXT_DOMAIN ),
					'search_items' => esc_html__ ( 'Search Politicians', INDIVISIBLE_TEXT_DOMAIN ),
					'parent_item_colon' => esc_html__ ( 'Parent Politician', INDIVISIBLE_TEXT_DOMAIN ),
					'not_found' => esc_html__ ( 'No Politicians found', INDIVISIBLE_TEXT_DOMAIN ),
					'not_found_in_trash' => esc_html__ ( 'No Politicians found in Trash', INDIVISIBLE_TEXT_DOMAIN ),
					'name' => esc_html__ ( 'Politicians', INDIVISIBLE_TEXT_DOMAIN ),
					'singular_name' => esc_html__ ( 'Politician', INDIVISIBLE_TEXT_DOMAIN )
			),
			'public' => true,
			'description' => 'Track legislation for Indivisible',
			'exclude_from_search' => false,
			'publicly_queryable' => true,
			'show_ui' => true,
			'show_in_nav_menus' => true,
			'show_in_menu' => true,
			'show_in_admin_bar' => false,
			'show_in_rest' => true,
			'rest_base' => 'politician',
			'capability_type' => 'post',
			'hierarchical' => true,
			'has_archive' => true,
			'query_var' => 'politician',
			'can_export' => false,
			'supports' => array (
					'title',
					'editor',
					'comments',
					'revisions'
			),
			'menu_icon' => 'dashicons-businessman',
			'rewrite' => array (
					'slug' => 'politician',
					'with_front' => false
			),
	);
	register_post_type ( INDV_POLITICIAN, $args );
	
	$args = array (
			'label' => esc_html__ ( 'Legislation', INDIVISIBLE_TEXT_DOMAIN ),
			'labels' => array (
					'menu_name' => esc_html__ ( 'Legislation', INDIVISIBLE_TEXT_DOMAIN ),
					'name_admin_bar' => esc_html__ ( 'Legislation', INDIVISIBLE_TEXT_DOMAIN ),
					'add_new' => esc_html__ ( 'Find More', INDIVISIBLE_TEXT_DOMAIN ),
					'add_new_item' => esc_html__ ( 'Find Legislation', INDIVISIBLE_TEXT_DOMAIN ),
					'new_item' => esc_html__ ( 'New Legislation', INDIVISIBLE_TEXT_DOMAIN ),
					'edit_item' => esc_html__ ( 'Edit Legislation', INDIVISIBLE_TEXT_DOMAIN ),
					'view_item' => esc_html__ ( 'View Legislation', INDIVISIBLE_TEXT_DOMAIN ),
					'update_item' => esc_html__ ( 'Update Legislation', INDIVISIBLE_TEXT_DOMAIN ),
					'all_items' => esc_html__ ( 'Tracking', INDIVISIBLE_TEXT_DOMAIN ),
					'search_items' => esc_html__ ( 'Search Legislation', INDIVISIBLE_TEXT_DOMAIN ),
					'parent_item_colon' => esc_html__ ( 'Parent Legislation', INDIVISIBLE_TEXT_DOMAIN ),
					'not_found' => esc_html__ ( 'No Legislation found', INDIVISIBLE_TEXT_DOMAIN ),
					'not_found_in_trash' => esc_html__ ( 'No Legislation found in Trash', INDIVISIBLE_TEXT_DOMAIN ),
					'name' => esc_html__ ( 'Legislation', INDIVISIBLE_TEXT_DOMAIN ),
					'singular_name' => esc_html__ ( 'Legislation', INDIVISIBLE_TEXT_DOMAIN )
			),
			'public' => true,
			'description' => 'Track legislation for Indivisible',
			'exclude_from_search' => false,
			'publicly_queryable' => true,
			'show_ui' => true,
			'show_in_nav_menus' => true,
			'show_in_menu' => true,
			'show_in_admin_bar' => false,
			'show_in_rest' => true,
			'rest_base' => 'legislation',
			'capability_type' => 'post',
			'hierarchical' => true,
			'has_archive' => true,
			'query_var' => 'legislation',
			'can_export' => true,
			'supports' => array (
					'title',
					'editor',
					'excerpt',
					'comments',
					'revisions'
			),
			'menu_icon' => 'dashicons-media-text',
			'rewrite' => array (
					'slug' => 'legislation',
					'with_front' => false,
			),
	);
	register_post_type ( INDV_LEGISLATION, $args );
	
	$args = array (
			'label' => esc_html__ ( 'Action', INDIVISIBLE_TEXT_DOMAIN ),
			'labels' => array (
					'menu_name' => esc_html__ ( 'Actions', INDIVISIBLE_TEXT_DOMAIN ),
					'name_admin_bar' => esc_html__ ( 'Actions', INDIVISIBLE_TEXT_DOMAIN ),
					'add_new' => esc_html__ ( 'Add New', INDIVISIBLE_TEXT_DOMAIN ),
					'add_new_item' => esc_html__ ( 'Add New Action', INDIVISIBLE_TEXT_DOMAIN ),
					'new_item' => esc_html__ ( 'New Action', INDIVISIBLE_TEXT_DOMAIN ),
					'edit_item' => esc_html__ ( 'Edit Action', INDIVISIBLE_TEXT_DOMAIN ),
					'view_item' => esc_html__ ( 'View Action', INDIVISIBLE_TEXT_DOMAIN ),
					'update_item' => esc_html__ ( 'Update Action', INDIVISIBLE_TEXT_DOMAIN ),
					'all_items' => esc_html__ ( 'All Actions', INDIVISIBLE_TEXT_DOMAIN ),
					'search_items' => esc_html__ ( 'Search Actions', INDIVISIBLE_TEXT_DOMAIN ),
					'parent_item_colon' => esc_html__ ( 'Parent Action', INDIVISIBLE_TEXT_DOMAIN ),
					'not_found' => esc_html__ ( 'No Actions found', INDIVISIBLE_TEXT_DOMAIN ),
					'not_found_in_trash' => esc_html__ ( 'No Actions found in Trash', INDIVISIBLE_TEXT_DOMAIN ),
					'name' => esc_html__ ( 'Action', INDIVISIBLE_TEXT_DOMAIN ),
					'singular_name' => esc_html__ ( 'Action', INDIVISIBLE_TEXT_DOMAIN )
			),
			'public' => true,
			'description' => 'Track legislation for Indivisible',
			'exclude_from_search' => false,
			'publicly_queryable' => true,
			'show_ui' => true,
			'show_in_nav_menus' => true,
			'show_in_menu' => true,
			'show_in_admin_bar' => false,
			'show_in_rest' => true,
			'rest_base' => 'actions',
			'capability_type' => 'post',
			'hierarchical' => true,
			'has_archive' => true,
			'query_var' => 'actions',
			'can_export' => true,
			'supports' => array (
					'title',
					'editor',
					'comments',
					'revisions'
			),
			'menu_icon' => 'dashicons-megaphone',
			'rewrite' => array (
					'slug' => 'actions',
					'with_front' => false
			),
	);
	
	register_post_type ( INDV_ACTION, $args );
}

function indv_plugin_taxonomies() {
	$labels = [
			'name' => _x ( 'Position', 'taxonomy general name' ),
			'singular_name' => _x ( 'Position', 'taxonomy singular name' ),
			'search_items' => __ ( 'Search Positions' ),
			'all_items' => __ ( 'All Positions' ),
			'parent_item' => __ ( 'Parent Position' ),
			'parent_item_colon' => __ ( 'Parent Position:' ),
			'edit_item' => __ ( 'Edit Position' ),
			'update_item' => __ ( 'Update Position' ),
			'add_new_item' => __ ( 'Add New Position' ),
			'new_item_name' => __ ( 'New Position Name' ),
			'menu_name' => __ ( 'Position' )
	];
	$args = [
			'public' => true,
			'hierarchical' => false,
			'labels' => $labels,
			'show_ui' => false,
			'show_admin_column' => true,
			'show_in_rest' => true,
			'rest_base' => 'position',
			'query_var' => 'position',
			'rewrite' => [
					'slug' => 'position',
					'witn_front' => false,
			]
	];
	register_taxonomy ( INDV_POSITION, INDV_LEGISLATION, $args );
			
	$labels = [
			'name' => _x ( 'Status', 'taxonomy general name' ),
			'singular_name' => _x ( 'Status', 'taxonomy singular name' ),
			'search_items' => __ ( 'Search Status' ),
			'all_items' => __ ( 'All Status' ),
			'parent_item' => __ ( 'Parent Status' ),
			'parent_item_colon' => __ ( 'Parent Status:' ),
			'edit_item' => __ ( 'Edit Status' ),
			'update_item' => __ ( 'Update Status' ),
			'add_new_item' => __ ( 'Add New Status' ),
			'new_item_name' => __ ( 'New Status Name' ),
			'menu_name' => __ ( 'Status' )
	];
	$args = [
			'public' => true,
			'hierarchical' => false,
			'labels' => $labels,
			'show_ui' => false,
			'show_admin_column' => true,
			'show_in_rest' => true,
			'rest_base' => 'bill_status',
			'query_var' => 'bill_status',
			'rewrite' => [
					'slug' => 'bill_status',
					'witn_front' => false,
			]
	];
	register_taxonomy ( INDV_BILL_STATUS, INDV_LEGISLATION, $args );
	
	$labels = [
			'name' => _x ( 'Interest', 'taxonomy general name' ),
			'singular_name' => _x ( 'Interest', 'taxonomy singular name' ),
			'search_items' => __ ( 'Search Interests' ),
			'all_items' => __ ( 'All Interested' ),
			'parent_item' => __ ( 'Parent Interest' ),
			'parent_item_colon' => __ ( 'Parent Interest:' ),
			'edit_item' => __ ( 'Edit Interest' ),
			'update_item' => __ ( 'Update Interest' ),
			'add_new_item' => __ ( 'Add New Interest' ),
			'new_item_name' => __ ( 'New Interest Name' ),
			'menu_name' => __ ( 'Interested' )
	];
	$args = [
			'public' => true,
			'hierarchical' => true, // make it hierarchical (like categories)
			'labels' => $labels,
			'show_ui' => true,
			'show_admin_column' => true,
			'show_in_rest' => true,
			'rest_base' => 'interest',
			'query_var' => 'interest',
			'rewrite' => [
					'slug' => 'interest',
					'witn_front' => false,
			]
	];
	register_taxonomy ( INDV_INTEREST, array( INDV_LEGISLATION, INDV_ACTION, INDV_POLITICIAN ), $args );

	$labels = [
			'name' => _x ( 'Issue', 'taxonomy general name' ),
			'singular_name' => _x ( 'Issue', 'taxonomy singular name' ),
			'search_items' => __ ( 'Search Issues' ),
			'all_items' => __ ( 'All Issues' ),
			'parent_item' => __ ( 'Parent Issue' ),
			'parent_item_colon' => __ ( 'Parent Issue:' ),
			'edit_item' => __ ( 'Edit Issue' ),
			'update_item' => __ ( 'Update Issue' ),
			'add_new_item' => __ ( 'Add New Issue' ),
			'new_item_name' => __ ( 'New Issue Name' ),
			'menu_name' => __ ( 'Issues' )
	];
	$args = [
			'public' => true,
			'hierarchical' => true, // make it hierarchical (like categories)
			'labels' => $labels,
			'show_ui' => true,
			'show_admin_column' => true,
			'show_in_rest' => true,
			'rest_base' => 'issue',
			'query_var' => 'issue',
			'rewrite' => [
					'slug' => 'issue',
					'witn_front' => false,
			]
	];
	register_taxonomy ( INDV_ISSUE, array( INDV_LEGISLATION, INDV_ACTION ), $args );
	
}

function indv_plugin_taxonomy_select($post_type, $which){
	
	if (in_array($post_type, array( INDV_LEGISLATION ))) {
		$taxonomy_slug = INDV_POSITION;
		$taxonomy = get_taxonomy($taxonomy_slug);
		$selected = '';
		$request_attr = 'position'; //this will show up in the url
		if ( isset($_REQUEST[$request_attr] ) ) {
			$selected = $_REQUEST[$request_attr]; //in case the current page is already filtered
		}
		$x = wp_dropdown_categories(array(
				'show_option_all' =>  __("Show All {$taxonomy->label}"),
				'taxonomy'        =>  $taxonomy_slug,
				'name'            =>  $request_attr,
				'selected'        =>  $selected,
				'hierarchical'    =>  false,
				'show_count'      =>  false, // Show number of post in parent term
				'hide_empty'      =>  false, // Don't show posts w/o terms
				'value_field'     => 'slug',
		));
	}
	
	if (in_array($post_type, array( INDV_POLITICIAN, INDV_LEGISLATION, INDV_ACTION ))) {
		$taxonomy_slug = INDV_INTEREST;
		$taxonomy = get_taxonomy($taxonomy_slug);
		$selected = '';
		$request_attr = 'interest'; //this will show up in the url
		if ( isset($_REQUEST[$request_attr] ) ) {
			$selected = $_REQUEST[$request_attr]; //in case the current page is already filtered
		}
		wp_dropdown_categories(array(
				'show_option_all' =>  __("Show All {$taxonomy->label}"),
				'taxonomy'        =>  $taxonomy_slug,
				'name'            =>  $request_attr,
				'orderby'         =>  'name',
				'selected'        =>  $selected,
				'hierarchical'    =>  true,
				'depth'           =>  3,
				'show_count'      =>  false, // Show number of post in parent term
				'hide_empty'      =>  false, // Don't show posts w/o terms
				'hide_if_empty'   =>  false,
				'value_field'	  => 'slug',
		));
	}
	
	if (in_array($post_type, array( INDV_LEGISLATION, INDV_ACTION ))) {
		$taxonomy_slug = INDV_ISSUE;
		$taxonomy = get_taxonomy($taxonomy_slug);
		$selected = '';
		$request_attr = 'issue'; //this will show up in the url
		if ( isset($_REQUEST[$request_attr] ) ) {
			$selected = $_REQUEST[$request_attr]; //in case the current page is already filtered
		}
		wp_dropdown_categories(array(
				'show_option_all' =>  __("Show All {$taxonomy->label}"),
				'taxonomy'        =>  $taxonomy_slug,
				'name'            =>  $request_attr,
				'orderby'         =>  'name',
				'selected'        =>  $selected,
				'hierarchical'    =>  true,
				'depth'           =>  3,
				'show_count'      =>  false, // Show number of post in parent term
				'hide_empty'      =>  false, // Don't show posts w/o terms
				'hide_if_empty'   =>  false,
				'value_field'	  => 'slug',
		));
	}
}

function indv_plugin_geography_filter( $query ) {
	if ( is_admin() || ! $query->is_main_query() )
		return;
	
	$name = get_query_var('by_name');
	if ($name) {
		global $wpdb;
		$sql = $wpdb->prepare(
			"
			SELECT post_name
			FROM $wpdb->posts
			WHERE post_type = 'indv_politician'
				AND post_status = 'publish'
				AND post_title LIKE %s
				", $name );
		$politcian = $wpdb->get_row($sql);
		if ($politcian) {
			$post_names = $query->query_vars['post_name__in'];
			if (!$post_names)
				$post_names = array();
			$post_names[] = $politcian->post_name;
			$query->set('post_name__in', $post_names);
		}
		return;
	}
					
	if (empty(get_query_var('lat')) || empty(get_query_var('lng')))
		return;
	$lat = get_query_var('lat');
	$lng = get_query_var('lng');

	$endpoint = CIVIC_KEY_URL . 'location-search/?lat=' . $lat . '&lng=' . $lng;
	global $indv;
	$result = $indv->get_json($endpoint);
	if ($result['status'] == 'success') {
		$politicians = $result['politicians'];
		if ($politicians) {
			$post_names = $query->query_vars['post_name__in'];
			if (!$post_names)
				$post_names = array();
			foreach ($politicians as $id) {
				$post_names[] = strtolower($id);
			};				
			$query->set('post_name__in', $post_names);
		}
	}
}

function indv_plugin_rest_init () {
	register_rest_field( array(INDV_POLITICIAN, INDV_LEGISLATION ), 'lexicon', array(
		'get_callback' => function(  $object, $field_name, $request ) {
			global $indv;
			return $indv->get_lexicon( $object[ 'id' ] );
		},
		'schema' => array(
			'description' => __( 'Lexicon', INDIVISIBLE_TEXT_DOMAIN ),
			'type'        => 'object'
		) )
	);
	
	register_rest_field( INDV_POLITICIAN, 'photo_url', array(
		'get_callback' => function(  $object, $field_name, $request ) {
			return get_post_meta( $object[ 'id' ], INDV_PHOTO_URL, true );
		},
		'update_callback' => function( $value, $object, $field_name ) {
			if ( ! $value || ! is_string( $value ) )
				return;
			return update_post_meta( $object->ID, INDV_PHOTO_URL, sanitize_url( $value ) );
		},
		'schema' => array(
				'description' => __( 'Photo URL', INDIVISIBLE_TEXT_DOMAIN ),
				'type'        => 'string'
		) )
	);
	
// 	register_rest_field( INDV_LEGISLATION, 'bill_status', array(
// 		'get_callback' => function(  $object, $field_name, $request ) {
// 			return get_post_meta( $object[ 'id' ], INDV_BILL_STATUS, true );
// 		},
// 		'update_callback' => function( $value, $object, $field_name ) {
// 			if ( ! $value || ! is_string( $value ) )
// 				return;
// 			return update_post_meta( $object->ID, INDV_BILL_STATUS, sanitize_text_field( $value ) );
// 		},
// 		'schema' => array(
// 			'description' => __( 'Photo URL', INDIVISIBLE_TEXT_DOMAIN ),
// 			'type'        => 'string'
// 		) )
// 	);
	
	foreach (array('full_name', 'first_name', 'middle_name', 'last_name', 'roles', 'email', 'url', 'offices') as $rest_field)
		register_rest_field( INDV_POLITICIAN, $rest_field, array(
			'get_callback' => function ($object, $field_name, $request) {
				global $indv;
				$post_slug = $object['slug'];
				$politician = strtoupper($post_slug);
				if (is_national($politician)) {
					$member = $indv->get_congress('members/' . $politician)[0];
					if ($member && isset($member[$field_name]))
						return $member[$field_name];
					if ($field_name == 'full_name') 
						return $member['first_name'] 
						. ($member['middle_name'] ? (' ' . $member['middle_name']) : '') . ' ' 
						. $member['last_name'] 
						. ($member['suffix'] ? (' ' . $member['suffix']) : '');
				} else {
					$url = OPEN_STATES_URL . 'legislators/' . $politician . '/';;
					$legislator = $indv->get_json($url);
					if ($legislator && isset($legislator[$field_name]))
						return $legislator[$field_name];
				}
			},
			'schema' => array(
				'description' => __( 'Reflect Open States', INDIVISIBLE_TEXT_DOMAIN ),
				'type'        => 'object'
			) )
		);
	
	foreach (array('calendar', 'history', 'votes') as $rest_field)
		register_rest_field( INDV_LEGISLATION, $rest_field, array(
			'get_callback' => function ($object, $field_name, $request) {
				global $indv;
				$bill = $indv->getLegiscanBill($object['id']);
				if ($bill && isset($bill[$field_name]))
					return $bill[$field_name];
			},
			'schema' => array(
					'description' => __( 'Reflect Legiscan', INDIVISIBLE_TEXT_DOMAIN ),
					'type'        => 'object'
			) )
		);
		
	foreach (array('sponsors', ) as $rest_field)
		register_rest_field( INDV_LEGISLATION, $rest_field, array(
			'get_callback' => function ($object, $field_name, $request) {
				global $indv;
				$bill = $indv->getOpenStatesBill($object['id']);
				if ($bill && isset($bill[$field_name]))
					return $bill[$field_name];
			},
			'schema' => array(
					'description' => __( 'Reflect Open States', INDIVISIBLE_TEXT_DOMAIN ),
					'type'        => 'object'
			) )
		);
		
	register_rest_route( 'indv/v1', '/autocomplete/politician', array(
		'methods' => 'GET',
		'callback' => 'indv_plugin_politician_directory',
	) );
}

function indv_plugin_meta_boxes ($post_type) {
	switch ($post_type) {
	case INDV_POLITICIAN:
		add_meta_box( INDV_PLUGIN_BOX_ . 'rollcalls',    esc_html__( 'Rollcalls', INDIVISIBLE_TEXT_DOMAIN ),
			'indv_plugin_meta_box', get_current_screen(),'normal', 'default', 'rollcalls' );
		add_meta_box( INDV_PLUGIN_BOX_ . 'finance',    esc_html__( 'Campaign Finances', INDIVISIBLE_TEXT_DOMAIN ),
				'indv_plugin_meta_box', get_current_screen(),'normal', 'default', 'finance' );
		add_meta_box( INDV_PLUGIN_BOX_ . 'links',    esc_html__( 'External Links', INDIVISIBLE_TEXT_DOMAIN ),
			'indv_plugin_meta_box', get_current_screen(),'normal', 'default', 'links' );
		add_meta_box( INDV_PLUGIN_BOX_ . 'committees',    esc_html__( 'Committees', INDIVISIBLE_TEXT_DOMAIN ),
			'indv_plugin_meta_box', get_current_screen(),'normal', 'default', 'committees' );
		add_meta_box( INDV_PLUGIN_BOX_ . 'actions',    esc_html__( 'Actions', INDIVISIBLE_TEXT_DOMAIN ),
				'indv_plugin_meta_box', get_current_screen(),'side', 'default', 'actions' );
		add_meta_box( INDV_PLUGIN_BOX_ . 'photo',    esc_html__( 'Photo', INDIVISIBLE_TEXT_DOMAIN ),
			'indv_plugin_meta_box', get_current_screen(),'side', 'default', 'photo' );
		add_meta_box( INDV_PLUGIN_BOX_ . 'contact',  esc_html__( 'Contact', INDIVISIBLE_TEXT_DOMAIN ),
			'indv_plugin_meta_box', get_current_screen(),'side', 'default', 'contact' );
		add_meta_box( INDV_PLUGIN_BOX_ . 'votes',    esc_html__( 'Votes', INDIVISIBLE_TEXT_DOMAIN ),
			'indv_plugin_meta_box', get_current_screen(),'side', 'default', 'votes' );
		add_meta_box( INDV_PLUGIN_BOX_ . 'lexicon',  esc_html__( 'Lexicon', INDIVISIBLE_TEXT_DOMAIN ),
			'indv_plugin_meta_box', get_current_screen(),'side', 'low', 'lexicon' );
		break;
	case INDV_LEGISLATION:
		add_meta_box( INDV_PLUGIN_BOX_ . 'rollcalls',    esc_html__( 'Rollcalls', INDIVISIBLE_TEXT_DOMAIN ),
			'indv_plugin_meta_box', get_current_screen(),'normal', 'default', 'rollcalls' );
		add_meta_box( INDV_PLUGIN_BOX_ . 'links',    esc_html__( 'External Links', INDIVISIBLE_TEXT_DOMAIN ),
			'indv_plugin_meta_box', get_current_screen(),'normal', 'default', 'links' );
		add_meta_box( INDV_PLUGIN_BOX_ . 'sponsors',    esc_html__( 'Sponsors', INDIVISIBLE_TEXT_DOMAIN ),
				'indv_plugin_meta_box', get_current_screen(),'side', 'default', 'sponsors' );
		add_meta_box( INDV_PLUGIN_BOX_ . 'actions',    esc_html__( 'Actions', INDIVISIBLE_TEXT_DOMAIN ),
				'indv_plugin_meta_box', get_current_screen(),'side', 'default', 'actions' );
		add_meta_box( INDV_PLUGIN_BOX_ . 'position', esc_html__( 'Position', INDIVISIBLE_TEXT_DOMAIN ),
			'indv_plugin_meta_box', get_current_screen(),'side', 'default', 'position' );
		add_meta_box( INDV_PLUGIN_BOX_ . 'status', esc_html__( 'Status', INDIVISIBLE_TEXT_DOMAIN ),
				'indv_plugin_meta_box', get_current_screen(),'side', 'default', 'status' );
		add_meta_box( INDV_PLUGIN_BOX_ . 'lexicon',  esc_html__( 'Lexicon', INDIVISIBLE_TEXT_DOMAIN ),
			'indv_plugin_meta_box', get_current_screen(),'side', 'low', 'lexicon' );
		break;
	case INDV_ACTION:
		add_meta_box( INDV_PLUGIN_BOX_ . 'links',    esc_html__( 'External Links', INDIVISIBLE_TEXT_DOMAIN ),
		'indv_plugin_meta_box', get_current_screen(),'normal', 'default', 'links' );
		break;
	default:
		break;
	}
}

function indv_plugin_columns_politician ( $columns ) {
	$new_columns = array_merge(array_slice( $columns, 0, 2 ), array( 'votes' => __('Votes') ), array_slice($columns, 2));
	$new_columns['title'] = __('Name');
	return $new_columns;
}

function indv_plugin_sortable_politician( $columns ) {
	$columns['votes'] = 'votes';
	return $columns;
}

function indv_plugin_columns_legislation ( $columns ) {
	$new_columns = array_merge(array_slice( $columns, 0, 2 ), array( 'identifier' => __('Identifier'), 'pposition' => __('Position') ), array_slice($columns, 3));
	return $new_columns;
}

function indv_plugin_orderby( $query ) {
	if( !is_admin() )
		return;
		
	$orderby = $query->get( 'orderby');
	switch ($orderby) {
		
		case 'position':
			break;
			
		case 'votes':
			$query->set('meta_key',INDV_VOTE_SCORE);
			$query->set('orderby','meta_value_num');
			break;
			
		default:
			break;
	}
}

/* RENDER */

function indv_plugin_admin_style() {
	if (isset($_GET['post_type']) && INDV_LEGISLATION == $_GET['post_type']) {
		echo '<style type="text/css">';
		echo '.wp-list-table .column-id { width: 5%; }';
		echo '.wp-list-table .column-title { width: 35%; }';
		echo '.wp-list-table .column-author { width: 35%; }';
		echo '.wp-list-table .column-identifier { width: 6%; }';	
// 		echo 'label.indv-radio .indv-radio { margin:20px; color: red; padding: 25px; }';
		echo '</style>';
	}
}

function indv_plugin_column ($column_name, $post_id ) {
	switch ($column_name) {
		case 'pposition':
			$term = wp_get_post_terms( $post_id, INDV_POSITION, array('orderby' => 'name', 'order' => 'ASC', 'fields' => 'all') );
			$term = $term[0];
			echo '<a href=' . get_admin_url() . 'edit.php?post_type=indv_legislation&indv_position=' . $term->slug . '>' . $term->name . '</a>';
			break;
		case 'votes':
			$votes = '?'; //get_post_meta($post_id, INDV_VOTES, true);
			echo '<pre>' . $votes . '</pre>';
			break;
		case 'identifier':
			$post_slug = get_post($post_id)->post_name;
			$legislation = implode(' ',explode('-',strtoupper($post_slug)));
			echo $legislation;
			break;
		default:
			break;
	}
}

function indv_plugin_meta_box ($post, $box) {
	global $indv;
	$post_type = $post->post_type;
	$box_type = $box['args'];
	wp_nonce_field( basename( __FILE__ ), INDV_PLUGIN_NONCE_ . $box_type );
	
	switch ($box_type) {
		
		case 'votes':
			$votes = '?'; //get_post_meta($post->ID, INDV_VOTES, true);
			if ($votes)
				echo $votes;
			break;
		
		case 'position':
			if (INDV_LEGISLATION != $post_type)
				break; // !?
			$post_slug = $post->post_name;
			$legislation = implode(' ',explode('-',strtoupper($post_slug)));
			$term = wp_get_post_terms($post->ID, INDV_POSITION )[0];
			$selected = $term->slug;
			$terms = get_terms( [
					'taxonomy' => INDV_POSITION,
					'orderby' => 'id',
					'hide_empty' => false
			] );
			echo '<label for="indv_plugin_box_position">' . $legislation . '</label>';
			echo '<select id=\'indv_plugin_box_position\' name=\'indv_legislation_position\'>';
			foreach ($terms as $term) {
				echo '<option value=\'' . $term->slug . '\' ' . ($term->slug == $selected ? 'selected' : '') . '>' . $term->name . '</option>';
			};
			echo '</select>';
			break;
			
		case 'status':
			if (INDV_LEGISLATION != $post_type)
				break; // !?
			$post_slug = $post->post_name;
			$legislation = implode(' ',explode('-',strtoupper($post_slug)));
			$term = wp_get_post_terms($post->ID, INDV_BILL_STATUS )[0];
			$selected = $term->slug;
			$terms = get_terms( [
					'taxonomy' => INDV_BILL_STATUS,
					'orderby' => 'id',
					'hide_empty' => false
			] );
			echo '<select id=\'indv_plugin_box_status\' name=\'indv_legislation_status\'>';
			foreach ($terms as $term) {
				echo '<option value=\'' . $term->slug . '\' ' . ($term->slug == $selected ? 'selected' : '') . '>' . $term->name . '</option>';
			};
			echo '</select>';
			break;
				
		case 'sponsors':
			if (INDV_LEGISLATION != $post_type)
				break; // !?
			$post_slug = $post->post_name;
			$legislation = implode(' ',explode('-',strtoupper($post_slug)));
			if ($post_slug) {
				$bill = $indv->getLegiscanBill($post_id);
				$sponsors = $bill['sponsors'];
				if (!empty($sponsors)) {
					echo '<ul>';
					foreach ($sponsors as $sponsor)
						echo '<li>' . $sponsor['name'] . '</li>';
					echo '</ul>';
				}
			}
			break;
		
		case 'links':
			switch ($post_type) {
				case INDV_LEGISLATION:
					$post_slug = $post->post_name;
					$parts = explode('-', strtoupper($legislation));
					$url = 'https://legiscan.com/' . $parts[0] . '/bill/' . $parts[1] . '/' . $parts[2];
					echo '<strong>Legiscan: </strong><a target="_blank" rel="noopener noreferrer" href=\'' . $url . '\'>' . $url . '</a><br>';
					break;
				default:
					echo "Eventually <a >links back to useful places</a>";
			}
			break;
			
		case 'committees':
			$post_slug = $post->post_name;
			$politician = strtoupper($post_slug);
			if ($politician) {
				if (is_national($politician)) {
					$member = $indv->get_congress('members/' . $politician)[0];
					$role = $member['roles'][0];
					$committees = array ();
					foreach ($role['committees'] as $committee)
						$committees [] = $committee['name'];
					asort($committees);
					if (count($committees) > 0) {
						echo '<ul>';
						foreach ($committees as $committee)
							echo '<li>' , $committee . '</li>';
						echo '</ul>';
					}
				} else {
					$url = OPEN_STATES_URL . 'legislators/' . $politician . '/';;
					$body = $indv->get_json($url);
					$roles = $body['roles'];
					$committees = array ();
					foreach ($roles as $role)
						if ('committee member' == $role['type'])
							$committees[] = $role['committee'];
					asort($committees);
					if (count($committees) > 0) {
						echo '<ul>';
						foreach ($committees as $committee)
							echo '<li>' , $committee . '</li>';
						echo '</ul>';
					}
				}
			}				
			break;
			
		case 'rollcalls':
			switch ($post_type) {
				case INDV_LEGISLATION:
					$bill = $indv->getLegiscanBill($post_id);
					if ($bill) {
						$votes = $bill['votes'];
						if (!empty($votes)) {
							echo '<table style="padding:5px;">';
							echo '<tr><th width="3em"></th><th width="3em">Yea</th><th width="3em">Nay</th><th width="3em">NV</th><th></th><th></th><th>Date</th><th width="60%">Description</th></tr>';
							foreach ($votes as $vote) {
								echo '<tr><td><select><option>&#x2795;</option><option selected>&nbsp;</option><option>&#x2796;</option></select></td>';
								echo '<td>' . $vote['yea'] . '</td><td>' . $vote['nay'] . '</td><td>'  . ($vote['nv'] + $vote['absent']) . '</td><td>' . $vote['chamber'] . '</td>';
								if ($vote['passed'])
									echo '<td>Passed</td>';
									else
										echo '<td>Failed</td>';
										echo '<td>' . $vote['date'] . '</td><td> ' . $vote['desc'] . '</td></tr>';
							}
							echo '</table>';
						}
							
// 							$url = OPEN_STATES_URL .  'bills/' . $lexicon[INDV_LEXICON_OPEN_STATES] . '/';
// 							$bill = indv_remote_get_json($url);
// 							$votes = $bill['votes'];
// 							if (count($votes) > 0) {
// 								usort($votes, function ($a, $b) {
// 									if ($a['date'] < $b['date'])
// 										return -1;
// 										if ($a['date'] > $b['date'])
// 											return 1;
// 											return 0; });
// 									echo '<table style="padding:5px;">';
// 									echo '<tr><th width="3em"></th><th width="3em">Yea</th><th width="3em">Nay</th><th width="3em">NV</th><th></th><th></th><th>Date</th><th width="60%">Description</th></tr>';
// 									foreach ($votes as $vote) {
// 										echo '<tr><td><select><option>&#x2795;</option><option selected>&nbsp;</option><option>&#x2796;</option></select></td>';
// 										echo '<td>' . $vote['yes_count'] . '</td><td>' . $vote['no_count'] . '</td><td>'  . $vote['other_count'] . '</td><td>' . $vote['chamber'] . '</td>';
// 										if ($vote['passed'])
// 											echo '<td>Passed</td>';
// 											else
// 												echo '<td>Failed</td>';
// 												echo '<td>' . substr($vote['date'],0,10) . '</td><td> ' . $vote['motion'] . '</td></tr>';
// 									}
// 									echo '</table>';
// 							}
					}
					break;
				default:
			}
			break;
			
		case 'finance':
			$lexicon = $indv->get_lexicon($post->ID);
			$fec_id = $lexicon[INDV_LEXICON_FEC_ID];
			$url = STATIC_URL . 'campaign-finance/v1/2016/candidates/' . $fec_id . '.json';
			$report = $indv->get_json($url);
			if ($report['status'] == 'OK')
				$report = $report['results'][0];
		
			if ($report) {
				if (isset($report['total_receipts'])) 
					echo 'Total ' . $report['total_receipts'] . '<br>';
				if (isset($report['total_from_individuals']))
					echo 'Individuals ' . $report['total_from_individuals'] . '<br>';
				if (isset($report['total_from_pacs']))
					echo 'PACs ' . $report['total_from_pacs'] . '<br>';
			} else
				echo '$$$$';
			break;
		
		case 'lexicon':
			$lexicon = $indv->get_lexicon($post->ID);
			foreach ($lexicon as $key => $value) 
				echo $key . ' => ' . $value . '<br>';
			break;
			
		case 'photo':
			$photo_url = get_post_meta($post->ID, INDV_PHOTO_URL, true);
			echo '<input type=\'text\' value=\'' . $photo_url . '\'>';
			echo '<img width="100%" src="' . $photo_url . '" >';
			break;
		
		case 'contact':
			echo '<input type=\'text\ id=\'indv_plugin_box_contact\'>Contact info</inpug>';
			break;
			
		case 'actions':
			switch ($post_type) {
				case INDV_LEGISLATION:
					echo '<ul><li>A list of actions</li><li>linked to this legislaiton</li></ul>';
					break;
				case INDV_POLITICIAN:
					echo '<ul><li>A list of actions</li><li>linked to this politician</li></ul>';
					break;
				default:
			}
			echo '<button>Start New Action</button>';
			break;
			
		default:
			break;
	}
}

function indv_plugin_preamble ($post)
{
	global $indv;

	wp_nonce_field( basename( __FILE__ ), INDV_PLUGIN_NONCE_ . 'disambiguate');
	
	echo '<div class="inside">';
	switch ($post->post_type) {
	
		case INDV_POLITICIAN:
			$post_slug = $post->post_name;
			if ($post_slug) {
				$subtitles = (array) indv_plugin_subtitle($post);
				echo '<h4>';
				foreach($subtitles as $subtitle)
					echo $subtitle . '<br/>';
				echo '</h4>';
			} else {
				$name = $post->post_title;
				if ($name) {
					$result = $indv->get_congress(CONGRESS_CURRENT . '/senate/members')[0]['members'];
					$senate = array();
					foreach ($result as $politician)
						if (strcasecmp($politician['last_name'], $name) == 0)
							$senate[] = $politician;
					
					$result = $indv->get_congress(CONGRESS_CURRENT . '/house/members')[0]['members'];
					$house = array();
					foreach ($result as $politician)
						if (strcasecmp($politician['last_name'], $name) == 0)
							$house[] = $politician;
								
					$url = OPEN_STATES_URL . 'legislators/' . '?chamber=upper&last_name=' .  sanitize_text_field($name);
					$upper = $indv->get_json($url);

					$url = OPEN_STATES_URL . 'legislators/' . '?chamber=lower&last_name=' .  sanitize_text_field($name);
					$lower = $indv->get_json($url);
					
					function each_polition($politician) {
						echo '<li><input type="radio" id="indv_plugin_radio_' . $politician['id'] 
							. '" class="indv-radio" name="indv_plugin_disambiguate" value="' . $politician['id'] . '"></input>';
						echo '<label width="20%" class="indv-radio" for="indv_plugin_radio_' . $politician['id'] . '">' 						
							. $politician['last_name'] . ', ' . $politician['first_name'] . (isset($politician['middle_name']) ? ' ' . $politician['middle_name'] : '' ) 
							. " (" . strtoupper($politician['state']) . ")</label></li>";
					}
					
					$found = count($senate) + count($house) + count($upper) + count($lower);
					switch ($found) {
						case 0:
							echo __( "Can't find any politician by that name" , INDIVISIBLE_TEXT_DOMAIN);
							break;
						case 1;
							echo __( "Do you mean " , INDIVISIBLE_TEXT_DOMAIN);
							break;
						default:
							echo __( "Select the one you're looking for " , INDIVISIBLE_TEXT_DOMAIN);
							break;
					}
					if ($found > 0) {
						echo '<span class="padding:25px;color:blue;"><ul>';
						if (count($senate) > 0) {
							echo '<li><strong>Senate</strong><ul>';
							foreach ($senate as $politician)
								each_polition($politician);
								echo '</ul></li>';
						}
						if (count($house) > 0) {
							echo '<li><strong>House</strong><ul>';
							foreach ($house as $politician)
								each_polition($politician);
								echo '</ul></li>';
						}
						if (count($upper) > 0) {
							echo '<li><strong>State Upper House</strong><ul>';
							foreach ($upper as $politician)
								each_polition($politician);
								echo '</ul></li>';
						}
						if (count($lower) > 0) {
							echo '<li><strong>State Lower House</strong><ul>';
							foreach ($lower as $politician)
								each_polition($politician);
								echo '</ul></li>';
						}
						echo '</ul></span>';
					}
				} else {
					echo __( "Enter just the surname of a politician", 'inddivisible-text-domain' );
				}
			}
			break;

		case INDV_LEGISLATION:
			$post_slug = $post->post_name;
			if ($post_slug) {
				$subtitles = (array) indv_plugin_subtitle($post);
				echo '<h4>';
				foreach($subtitles as $subtitle)
					echo $subtitle . '<br/>';
				echo '</h4>';
			} else
				echo 'Enter a bill identifier like CA AB123 2018 or paste in the URL of a bill from Legiscan';
			
		default:
			break;
	}
	echo '</div>';
}

function indv_plugin_permalink ($return, $post_id, $new_title, $new_slug, $post ) {
	if (in_array($post->post_type, array ( INDV_POLITICIAN, INDV_LEGISLATION ))) 
		$return = str_replace('button type', 'button disabled type', $return);

	return $return;
}

function is_national ($id) {
	return ctype_digit(substr($id, 1, 1));
}

function indv_plugin_save_post ( $post_id, $post, $update ) {
	global $indv;
	if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE )
		return $post_id;
		
	$post_type = $post->post_type;
	$pto = get_post_type_object( $post_type );
	if ( !current_user_can( $pto->cap->edit_post, $post_id ) )
		return $post_id;
			
	$nonces = [
			INDV_POLITICIAN   => array( 'disambiguate', 'photo', 'contact' ),
			INDV_LEGISLATION => array( 'position' ),
	];
	foreach ($nonces[$post_type] as $nonce )
		if ( !isset( $_POST[INDV_PLUGIN_NONCE_ . $nonce] ) || !wp_verify_nonce( $_POST[INDV_PLUGIN_NONCE_ . $nonce], basename( __FILE__ ) ) )
			return $post_id;
	
	switch ($post_type) {
		
		case INDV_POLITICIAN:
			$post_slug = $post->post_name;
			if ($post_slug) {
				// save any updats
			} else if (isset($_POST['indv_plugin_disambiguate'])) {
				$politician = $_POST['indv_plugin_disambiguate'];
				$query = new WP_Query(array(
						'fields' => 'ids',
						'post_type' => INDV_POLITICIAN,
						'pagename' => strtolower($politician),
				));
				if ($query->found_posts) {
					wp_delete_post($post_id, true);
					wp_redirect(get_edit_post_link($query->posts[0], 'link'));
					exit;
				} else if (is_national($politician)) {
					$member = $indv->get_congress('members/' . $politician)[0];
					$role = $member['roles'][0];
					
					$lexicon = array (
							INDV_LEXICON_BIOGUIDE_ID => $politician,
					);
					if (isset($member['votesmart_id']))
						$lexicon[INDV_LEXICON_VOTE_SMART] = $member['votesmart_id'];
					if (isset($member['crp_id']))
						$lexicon[INDV_LEXICON_OPEN_SECRETS] = $member['crp_id'];
					if (isset($member['ballotpedia']))
						$lexicon[INDV_LEXICON_BALLOTPEDIA] = $member['ballotpedia'];
					if (isset($member['govtrack_id']))
						$lexicon[INDV_LEXICON_GOVTRACK] = $member['govtrack_id'];
					if (isset($role['fec_candidate_id']))
						$lexicon[INDV_LEXICON_FEC_ID] = $role['fec_candidate_id'];					
					
					$photo_url = PUBLIC_STATIC_URL . 'theunitedstates/images/congress/450x550/' . $politician . '.jpg';
					$full_name = sanitize_text_field( $member['last_name'] . ', ' . $member['first_name'] . ' ' . $member['middle_name'] );
				} else {
					$url = OPEN_STATES_URL . 'legislators/' . $politician . '/';
					$member = $indv->get_json($url);
					$politician = $member['id'];

					$lexicon = array (
							INDV_LEXICON_OPEN_STATES => $politician,
					);
					if (isset($member['votesmart_id']))
						$lexicon[INDV_LEXICON_VOTE_SMART] = $member['votesmart_id'];
					if (isset($member['opensecrets_id']))
						$lexicon[INDV_LEXICON_OPEN_SECRETS] = $member['opensecrets_id'];
					if (isset($member['ballotpedia']))
						$lexicon[INDV_LEXICON_BALLOTPEDIA] = $member['ballotpedia'];
				
					$photo_url = sanitize_url($member['photo_url']);
					$full_name = sanitize_text_field( $member['last_name'] . ', ' . $member['first_name'] . ' ' . $member['middle_name'] );
				}
				update_post_meta($post_id, INDV_LEXICON, $lexicon);
				
				if ($photo_url)
					update_post_meta($post_id, INDV_PHOTO_URL, $photo_url);
				
// 				$with = rand( 0, 10 );
// 				$against = rand( 0, 10);
// 				$votes = sprintf('%3d / %3d', $with, $against);
// 				update_post_meta($post_id, INDV_VOTES, $votes);
// 				if (($with + $against) > 0)
// 					update_post_meta($post_id, INDV_VOTE_SCORE, $with / ($with + $against));
						
				global $wpdb;
				$wpdb->update( $wpdb->posts, array( 
					'post_title' => $full_name,
					'post_name' => strtolower($politician),
				), array( 
					'ID' => $post_id ) );
			}
			break;
		
		case INDV_LEGISLATION:
			$post_slug = $post->post_name;
			if ($post_slug) {
				$position = wp_get_post_terms( $post_id, INDV_POSITION, array('orderby' => 'id', 'order' => 'ASC', 'fields' => 'all') )[0];
				if (isset( $_POST['indv_legislation_position'] )) {
					$new_position = $_POST['indv_legislation_position'];
					if ($new_position != $position->slug  && in_array($new_position, array(
							'support', 'oppose', 'interested')))
						wp_set_post_terms( $post_id, $new_position, INDV_POSITION);
				}
				$bill_status = wp_get_post_terms( $post_id, INDV_BILL_STATUS, array('orderby' => 'id', 'order' => 'ASC', 'fields' => 'all') )[0];
				if (isset( $_POST['indv_legislation_status'] )) {
					$new_status = $_POST['indv_legislation_status'];
					if ($new_status != $bill_status->slug  && in_array($new_status, array( 'introduced', 
							'1st-house-policy', '1st-house-appropriations', '1st-house-floor',
							'2nd-house-policy', '2nd-house-appropriations', '2nd-house-floor',
							'govenor-signed' )))
						wp_set_post_terms( $post_id, $new_status, INDV_BILL_STATUS);
				}
			} else {
				preg_match ( '/(?:^|[^A-Z]+)([A-Z][A-Z])(?:\/.*\/|[^A-Z]|[\s,-\/]*)([A-Z]+(?:\s*)[\d]+)[\s,-\]]*(\d\d\d\d|)/',
						strtoupper($post->post_title), $matches, PREG_OFFSET_CAPTURE );
				if (count($matches) == 4)
					$key = array ( $matches [1] [0], $matches [2] [0], $matches [3] [0] );
				else 
					$key = false;
				if ($key && (!isset($key[2]) || $key[2] == ''))
					$key[2] = 2; // legiscan for this year
				$legislation = strtolower(implode('-', $key));
				if ($legislation) {
					$query = new WP_Query(array(
							'fields' => 'ids',
							'post_type' => INDV_LEGISLATION,
							'pagename' => $legislation,
					));
					if ($query->found_posts) {
						wp_delete_post($post_id, true);
						wp_redirect(get_edit_post_link($query->posts[0], 'link'));
						exit;
					} else {
						$legiscan = $indv->legiscan_lookup($key);
						if ($legiscan) {
							$state = $legiscan ['state'];
							$bill = $legiscan ['bill_number'];
							$bill_id = $legiscan ['bill_id'];
							$year = $legiscan ['url'];
							$year = substr ( $year, strlen ( $year ) - 4 );
							$key = array ( $state, $bill, $year );
							$legislation = strtolower(implode('-', $key));
							$legiscan = $indv->legiscan_lookup ( $key );
							if ($bill_id != $legiscan ['bill_id'])
								wp_die("Legiscan sanity check failed");
							$query = new WP_Query(array(
									'fields' => 'ids',
									'post_type' => INDV_LEGISLATION,
									'pagename' => $legislation,
							));
							if ($query->found_posts) {
								wp_delete_post($post_id, true);
								wp_redirect(get_edit_post_link($query->posts[0], 'link'));
								exit;
							}
						}
					}
					if ($legiscan) {
						$url = OPEN_STATES_URL . 'bills/?state=' . $key[0] . '&bill_id=' . $key[1];
						$body = $indv->get_json($url);
						foreach ($body as $bill) {
							$session = $bill['session'];
							$session = substr($bill['session'],0,4);
							if (substr($bill['session'],0,4) == $key[2] || substr($bill['created_at'],0,4) == $key[2])
								$open_states = $bill;
						}
						
						$lexicon = array();
						$lexicon[INDV_LEXICON_LEGISCAN] = $legiscan['bill_id'];
						if ($open_states)
							$lexicon[INDV_LEXICON_OPEN_STATES] = $open_states['id'];
							
						update_post_meta($post_id, INDV_LEXICON, $lexicon);
						wp_set_post_terms( $post_id, 'interested', INDV_POSITION);
						global $wpdb;
						$post_slug = strtolower(implode('-', $key));
						$wpdb->update ( $wpdb->posts, array (
								'post_title' => $legiscan ['title'],
								'post_name' => $post_slug 
							), array (
								'ID' => $post_id 
							) );
					}
				} else {
					add_filter('redirect_post_location', function( $location ) {
						return add_query_arg( 'indv_plugin_error', 'bad_bill_id', $location ); });
				}
			}
		default:
	}
}

function indv_plugin_add_help () {
	$screen = get_current_screen();
		
	$screen->add_help_tab( array(
			'id'	=> 'indv_plugin_help_tab',
			'title'	=> __('My Help Tab'),
			'content'	=> '<p>' . __( 'Descriptive content that will show in My Help Tab-body goes here.' ) . '</p>',
		) );
}

function indv_plugin_subtitle ($post) {
	global $indv;
	$subtitle = array();
	
	switch ($post->post_type) {
		case INDV_POLITICIAN:
			$post_slug = $post->post_name;
			$politician = strtoupper($post_slug);
			$url = OPEN_STATES_URL . 'metadata/';;
			$metadata = $indv->get_json($url);
			if (is_national($politician)) {
				$member = $indv->get_congress('members/' . $politician)[0];
				
				$role = $member['roles'][0];
				$state = 'N/A';
				foreach ($metadata as $st)
					if (strcasecmp($st['abbreviation'], $role['state']) == 0)
						$state = $st['name'];
				if ($role['chamber'] == 'Senate')
					$subtitle[] = 'Senator for ' . $state;
				else
					$subtitle[] = $state . ' Congressional District ' . $role['district'];

				$html = '';
				$party = $member['current_party'];				
				switch ($party) {
					case 'R':
						$html .= 'Rebpulican';
						break;
					case 'D':
						$html .= 'Democratic';
						break;
					default:
						$html .=  'Fix Me';
						break;
				}
				$leadership = $role['leadership_role'];
				if ($leadership)
					$html .= ', ' . $role['leadership_role'];
				$subtitle[] = $html;
			} else {
				$url = OPEN_STATES_URL . 'legislators/' . $politician . '/';;
				$body = $indv->get_json($url);
				
				$roles = $body['roles'];
				foreach ($roles as $role)
					if ('member' == $role['type']) {
						$url = OPEN_STATES_URL . 'metadata/' . $role['state'] . '/';;
						$state = $indv->get_json($url);
						$chamber = $state['chambers'][$role['chamber']]['name'];
						$subtitle[] = $state['name'] . '&nbsp;' . $chamber . '&nbsp;District&nbsp;' . $role['district'];
						$subtitle[] = $body['party'];
					}
			}
			break;
			
		case INDV_LEGISLATION:
			$post_slug = $post->post_name;
			$legislation = implode(' ',explode('-', strtoupper($post_slug)));
			if ($post_slug) {
				$html = $legislation;
				$lexicon = $indv->get_lexicon($post->ID);
				$bill = $indv->getLegiscanBill($post->ID);
				
				$sponsors = $bill['sponsors'];
				switch (count($sponsors)) {
					case 1:
						$html .= ' ' . $sponsors[0]['name'];
						break;
					case 2:
						$html .= ' ' . $sponsors[0]['name'];
						$html .= ', ' . $sponsors[1]['name'];
						break;
					case 3:
						$html .= ' ' . $sponsors[0]['name'];
						$html .= ', ' . $sponsors[1]['name'];
						$html .= ', ' . $sponsors[2]['name'];
						break;
					default:
						$html .= ' ' . $sponsors[0]['name'];
						$html .= ', ' . $sponsors[1]['name'];
						$html .= ' and ' . (count($sponsors) - 2) . ' others';
						break;
				}
				$subtitle[] = $html;
				
				
				$last_event = end($bill['history']);
				if ($last_event)
					$subtitle[] = $last_event['date'] . ' ' . $last_event['action'];
			}
			
		default:
			break;
	}
	
	return $subtitle;
}


///////////////////////////////////////////////////
///////////////////////////////////////////////////















// function indv_legislation_manage_sortable_columns( $columns ) {
// 	// 	$columns['position'] = 'position';
// 	return $columns;
// }


function indv_plugin_settings() {
	register_setting ( 'indivisible', 'legislatures', array (
			'description' => 'Legislatures to preload'  ) );
	
	register_setting ( 'indivisible', 'states', array (
			'sanitize_callback' => 'indv_plugin_sanitize',
			'description' => 'States to preload'  ) );
	
	add_settings_section (
			'default',
			__( 'Preload Politicians', INDIVISIBLE_TEXT_DOMAIN ),
			'',
			'indv_settings' );
			
	add_settings_field(
			'legislatures',
			__( 'Legislatures', INDIVISIBLE_TEXT_DOMAIN ),
			'indv_plugin_legislatures_render',
			'indv_settings',
			'default'
			);
	
	add_settings_field(
			'states',
			__( 'States', INDIVISIBLE_TEXT_DOMAIN ),
			'indv_plugin_states_render',
			'indv_settings',
			'default'
			);
			
// 			add_settings_field(
// 			'whatever',
// 			__( 'Whatever', INDIVISIBLE_TEXT_DOMAIN ),
// 			'indv_legislation_render_user_key',
// 			'indv_settings',
// 			''
// 			);
	
// 	add_settings_section (
// 			'legislatures',
// 			__( '', INDIVISIBLE_TEXT_DOMAIN ),
// 			'indv_legislature_render_section',
// 			'indv_settings' );
			
// 			add_settings_field(
// 					'user_key',
// 					__( 'Identifier', INDIVISIBLE_TEXT_DOMAIN ),
// 					'indv_legislation_render_user_key',
// 					'indv_settings',
// 					'legislatures'
// 					);
			
// 			add_settings_field(
// 					'title',
// 					__( 'Title', INDIVISIBLE_TEXT_DOMAIN ),
// 					'indv_legislation_render_title',
// 					'indv_settings',
// 					'legislation'
// 					);
			
// 			add_settings_field(
// 					'following',
// 					__( 'Following', INDIVISIBLE_TEXT_DOMAIN ),
// 					'indv_legislation_render_follow',
// 					'indv_settings',
// 					'legislation'
// 					);
			
// 			add_settings_field(
// 					'position',
// 					__( 'Position', INDIVISIBLE_TEXT_DOMAIN ),
// 					'indv_legislation_render_select',
// 					'indv_settings',
// 					'legislation'
// 					);
}

function indv_plugin_add_politician( $id, $user_id, $full_name, $lexicon, $photo_url) {
    $politician = strtolower($id);
    $query = new WP_Query(array(
        'fields' => 'ids',
        'post_type' => INDV_POLITICIAN,
        'pagename' => $politician,
    ));
    if ($query->found_posts) 
        return;
        
    $request = new WP_REST_Request( 'POST', '/wp/v2/politician' );
    $request->set_body_params(array(
        'status' => 'publish',
        'slug' => strtolower($id),
        'title' => $full_name,
        'author' => $user_id,
    ));
    
    $response = rest_do_request($request);
    $response = rest_ensure_response($response);
    if ( $response->is_error() ) {
        throw $response->as_error();
    }
    
    $data = $response->get_data();
    $post_id = $data['id'];
    update_post_meta($post_id, INDV_LEXICON, $lexicon);
    if ($photo_url)
        update_post_meta($post_id, INDV_PHOTO_URL, $photo_url);
}

function indv_plugin_sanitize($input) {
	global $indv;
	
	$legislatures = get_option('legislatures');
	$states = get_option('states');
	$count = 0;
	$now = time();
	
	if (isset($legislatures['national'])) {
	    $members = array();
	    $senators = $indv->get_congress(CONGRESS_CURRENT . '/senate/members')[0]['members'];
	    $representatives = $indv->get_congress(CONGRESS_CURRENT . '/house/members')[0]['members'];
	    
	    foreach ($input as $state => $on) {
			foreach ($senators as $member)
				if ($member['state'] == $state)
					$members[] = $member;
			foreach ($representatives as $member)
				if ($member['state'] == $state)
					$members[] = $member;
	    }	
		foreach ($members as $member) {
			$role = $member['roles'][0];
			$politician = $member['id'];
			$lexicon = array (
					INDV_LEXICON_BIOGUIDE_ID => $politician,
			);
			if (isset($member['votesmart_id']))
				$lexicon[INDV_LEXICON_VOTE_SMART] = $member['votesmart_id'];
			if (isset($member['crp_id']))
				$lexicon[INDV_LEXICON_OPEN_SECRETS] = $member['crp_id'];
			if (isset($member['ballotpedia']))
				$lexicon[INDV_LEXICON_BALLOTPEDIA] = $member['ballotpedia'];
			if (isset($member['govtrack_id']))
				$lexicon[INDV_LEXICON_GOVTRACK] = $member['govtrack_id'];
			if (isset($role['fec_candidate_id']))
				$lexicon[INDV_LEXICON_FEC_ID] = $role['fec_candidate_id'];
				
			$photo_url = PUBLIC_STATIC_URL . 'theunitedstates/images/congress/450x550/' . $politician . '.jpg';
			$full_name = sanitize_text_field( $member['last_name'] . ', ' . $member['first_name'] . ' ' . $member['middle_name'] );
            
            wp_schedule_single_event( $now + ++$count * 60, 'indv_plugin_politician_add', array(
                $politician,
                get_current_user_id(),
                $full_name,
                $lexicon,
                $photo_url
            ));
		}
	}
		
	if (isset($legislatures['state'])) {
	    foreach ($input as $state => $on) {
	        $members = $indv->get_open_states('legislators/?state=' . $state);
			foreach ($members as $member) {
				$politician = $member['id'];
				$lexicon = array (
						INDV_LEXICON_OPEN_STATES => $politician,
				);
				if (isset($member['votesmart_id']))
					$lexicon[INDV_LEXICON_VOTE_SMART] = $member['votesmart_id'];
				if (isset($member['opensecrets_id']))
					$lexicon[INDV_LEXICON_OPEN_SECRETS] = $member['opensecrets_id'];
				if (isset($member['ballotpedia']))
					$lexicon[INDV_LEXICON_BALLOTPEDIA] = $member['ballotpedia'];
							
				$photo_url = sanitize_url($member['photo_url']);
				$full_name = sanitize_text_field( $member['last_name'] . ', ' . $member['first_name'] . ' ' . $member['middle_name'] );
				
				wp_schedule_single_event($now + ++$$count * 60, 'indv_plugin_politician_add', array(
				    $politician,
				    get_current_user_id(),
				    $full_name,
				    $lexicon,
				    $photo_url
				));
			}
		}
	}
	
	return $input;
}

function indv_plugin_menu() {
	add_options_page ( 'Indivisible', 'Indivisible', 'manage_options', 'indv_settings', 'indv_plugin_render_settings' );
}

function indv_plugin_legislatures_render() {
	$current = get_option ( 'legislatures' );
	
	echo '<table><tr>';
	echo '<td><input type="checkbox" id="indv_settings_national" name="legislatures[national]"' . (isset($current['national']) ? ' checked' : '') . '>';
	echo '<label for="indv_settings_national">National</label></td>';
	echo '<td><input type="checkbox" id="indv_settings_state" name="legislatures[state]"' . (isset($current['state']) ? ' checked' : '') . '>';
	echo '<label for="indv_settings_state">State</label></td>';
	echo '</tr></table>';
}

function indv_plugin_states_render() {
	$states = [
			[  'AK', 'AL', 'AR', 'AZ', 'CA', 'CO', 'CT', 'DE', 'FL', 'GA', ],
			[  'HI', 'IA', 'ID', 'IL', 'IN', 'KS', 'KY', 'LA', 'MA', 'MD', ],
			[  'ME', 'MO', 'MI', 'MN', 'MS', 'MT', 'NB', 'NC', 'ND', 'NH', ],
			[  'NJ', 'NM', 'NV', 'NY', 'OH', 'OK', 'OR', 'PA', 'RI', 'SC', ],
			[  'SD', 'TN', 'TX', 'UT', 'VA', 'VT', 'WA', 'WI', 'WV', 'WY', ],
			[  'AS', 'DC', 'PR', ]
	];
	
	function checkbox ($state, $checked) {
		$html = '<td width="">' . '<input type="checkbox" id="indv_settings_state_' . $state .'" name="states[' . $state . ']" ' . ($checked ? 'checked' : '') . '>';
		$html .= '<label for="indv_settings_state_' . $state . '">' . $state . '</label>';
		$html .= '</td>';
		echo $html;
	}
	
	$current = get_option ( 'states' );
	
	echo '<table>';
	foreach ($states as $row) {
		echo '<tr>';
		foreach ($row as $state) {
			checkbox($state,isset($current[$state]));
		}
		echo '</tr>';
	}
	echo '</table>';
}

function indv_plugin_render_settings() {
	if (! current_user_can ( 'manage_options' )) {
		wp_die ( __ ( 'You do not have sufficient permissions to access this page.' ) );
	}
	?>
	<div class="wrap">
		<h1>Indivisible Settings</h1>

		<form method="post" action="options.php">
            <?php  settings_fields( 'indivisible' ); ?>
            <?php  do_settings_sections( 'indv_settings' ); ?>

            <?php submit_button(); ?>
        </form>
	</div>
	<?php
}

function indv_legislation_render_section(  ) {
	//	echo __( 'This section description', 'indivisible-dtxt-domain' );
}

function indv_legislation_render_text( $key ) {
	$options = get_option ( 'legislation' );
	?>
	<input type='text' size='40' name='<?php echo 'legislation[' . $key . ']' ?>'
		value='<?php echo $options[$key]; ?>'>
	<?php
}

function indv_legislation_textarea_render( $key ) {
	$options = get_option ( 'legislation' );
	?>
	<textarea cols='40' class='{text-align: left;}' readonly name='<?php echo 'legislation[' . $key . ']' ?>'
	><?php echo $options[$key]; ?></textarea>
	<?php
}

function indv_legislation_render_title () {
	indv_legislation_textarea_render('title');
}

function indv_legislation_render_follow( ) {
	$options = get_option ( 'legislation' );
	$key = 'post_id';
	$post_id = $options[$key];
	$valid_key = $options['valid_key'];
	?>
	<input type='<?php echo $post_id ? 'hidden' : 'checkbox'; ?>' id='legislation_follow' name='<?php echo 'legislation[' . $key . ']' ?>'
		<?php if ($post_id) echo "checked"; ?>
		<?php if (!$options['valid_key']) echo 'disabled=true'; ?>
		value='<?php echo  $valid_key ? ($post_id ? $post_id : 'follow') : ''; ?>'>
	<?php
	if ($post_id) { ?>
		<a href='<?php echo get_page_link($post_id); ?>'>Read</a>
		or
		<a href='<?php echo get_edit_post_link($post_id); ?>'>Edit</a>
	<?php } else { ?>
		<label for="legislation_follow">Start following</label>
	<?php }
}

function indv_legislation_radio_render( ) {
	$options = get_option ( 'legislation' );
	$key = 'position';
	$position = $options[$key];
	?>
	<input type='radio' id='legislation_position_support' name='<?php echo 'legislation[' . $key . ']' ?>'
		<?php if ($position && $position == 'support') echo "checked"; ?>
		value='support'>
	<label for="legislation_position_oppose">Support</label>
	<input type='radio' name='<?php echo 'legislation[' . $key . ']' ?>'
		<?php if ($position && $position == 'oppose') echo "checked"; ?>
		value='oppose'>
	<label for="legislation_position_oppose">Oppose</label>
	<input type='radio' name='<?php echo 'legislation[' . $key . ']' ?>'
		<?php if ($position && $position == 'interested') echo "checked"; ?>
		value='interested'>
	<label for="legislation_position_interested">Interested</label>
	<?php
}

function indv_legislation_render_select( ) {
	$options = get_option ( 'legislation' );
	$key = 'position';
	$position = $options[$key];
	$post_id = $options['post_id'];
	$position = get_post_meta($post_id, '_indv_position', true);
	?>
	<select name='<?php echo 'legislation[' . $key . ']'; ?>' <?php if (!$options['post_id']) echo 'disabled=true'; ?>>
		<option value='support' 
		 	<?php if ($position && $position == 'support') echo "selected"; ?>
		>Support</option>
		<option value='oppose' 
		 	<?php if ($position && $position == 'oppose') echo "selected"; ?>
		>Oppose</option>
		<option value='interested' 
		 	<?php if ($position && $position == 'interested') echo "selected"; ?>
		>Interested</option>
	</select>
	<?php
}

function indv_legislation_render_user_key() {
	indv_legislation_render_text('user_key');
}

function indv_plugin_error_filter ($location, $post_id)
{
	return add_query_arg ( "indivisible-error", $error, $location );
}

function indv_plugin_error_filter_duplicates ($location, $post_id)
{
	return add_query_arg ( "indv_plugin_error", 'duplicates', $location );
}

function indv_plugin_render_error( $error, $class ) {
	?>
	<div class="<?php echo $class ? $class : 'error'; ?>">
		<p> <?php echo $error; ?>
		</p>
	</div> 
	<?php
}

function indv_plugin_errors() {
 	global $post_type, $post_id;
	if (!$post_id || $post_type != INDV_LEGISLATION)
		return;

	if (array_key_exists ( 'indv_plugin_error', $_GET )) { 
		?>
		<div class="error">
			<p> <?php
				switch ($_GET ['indv_plugin_error']) {
					case 'bad_bill_id' :
						echo 'The legislation failed to save because the bill id could not be parsed.';
						break;
					case 'legiscan_insanity' :
						echo 'The legislation failed to save because Legiscan returned inconsistent results.';
						break;
					case 'legiscan_lookup' :
						echo 'The legislation failed to save because the bill does not exist.';
						break;
					default :
						echo 'An error ocurred when saving the legislation.';
						break;
				} ?>
			</p>
		</div> 
		<?php
	}
}





function indv_plugin_politician_directory ($data) {
	global $wpdb;
	
	$query =
		"
		SELECT post_name, post_title
		FROM $wpdb->posts
		WHERE post_type = 'indv_politician'
			AND post_status = 'publish' 
		";
	$term = $data['term'];
	if ($term)	
		$query = $wpdb->prepare(
			$query .
				"
				AND post_title LIKE '%s'
				",
			'%' . $term . '%' );
	
	$politcians = $wpdb->get_results($query);
	$directory = array ();
	foreach ($politcians as $politician)
		$directory[] = $politician->post_title;
	
	return $directory;
}

function indv_plugin_stock_photo ($post) {
	if ($post->post_type == INDV_POLITICIAN)
		return get_post_meta($post->ID, INDV_PHOTO_URL, true);
	return false;
}
