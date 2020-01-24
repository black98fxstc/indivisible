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

// define( 'OPEN_STATES_URL', "https://api.state-strong.org/open-states/" );
// define( 'LEGISCAN_URL',    "https://api.state-strong.org/legiscan/" );
// define( 'CIVIC_KEY_URL', 'https://api.state-strong.org/civic-key/');
// define( 'STATIC_URL', 'https://static.state-strong.org/' );
// define( 'STATIC_URL', 'http://127.0.0.1:8082/' );
// define( 'LEGISCAN_URL', "http://127.0.0.1:8084/legiscan/indv-plugin.php/" );
// define( 'CIVIC_KEY_URL', 'http://127.0.0.1:8085/civic-key/');
define( 'STATIC_URL', 'http://static/' );
define( 'LEGISCAN_URL', 'http://legiscan/legiscan/indv-plugin.php/' );
define( 'CIVIC_KEY_URL', 'http://civic-key:8080/civic-key/');

define( 'OPEN_STATES_URL', STATIC_URL . 'open-states/' );
define( 'CONGRESS_URL', STATIC_URL . 'congress/' );
define( 'CONGRESS_CURRENT', '116' );

// post types
// define( 'INDV_POLITICIAN', 'indv_politician' );
// define( 'INDV_LEGISLATION', 'indv_legislation' );
// define( 'INDV_ACTION', 'indv_action' );
// define( 'INDV_INTEREST', 'indv_interest' );
// define( 'INDV_ISSUE', 'indv_issue' );

// meta data keys
// define( 'INDV_LEXICON', 'indv_lexicon' );
// define( 'INDV_POSITION', 'indv_position' );
define( 'INDV_BILL_STATUS', 'indv_bill_status' );
// define( 'INDV_PHOTO_URL', 'indv_photo_url' );
// define( 'INDV_CONTACT', 'indv_contact' );
// define( 'INDV_VOTES', 'indv_votes');
// define( 'INDV_VOTE_SCORE', 'indv_vote_score');

// html ids
define( 'INDV_PLUGIN_BOX_',   'indv_plugin_box_' );
define( 'INDV_PLUGIN_NONCE_', 'indv_plugin_nonce_' );

// lexicon
// define( 'INDV_LEXICON_LEGISCAN',     'legiscan' );
// define( 'INDV_LEXICON_OPEN_STATES',  'open_states' );
// define( 'INDV_LEXICON_VOTE_SMART',   'vote_smart' );
// define( 'INDV_LEXICON_BALLOTPEDIA',  'ballotpedia' );
// define( 'INDV_LEXICON_OPEN_SECRETS', 'open_secrets' );
// define( 'INDV_LEXICON_FOLLOW_THE_MONEY', 'follow_the_money' );
// define( 'INDV_LEXICON_BIOGUIDE_ID',   'bioguide_id' );
// define( 'INDV_LEXICON_GOOGLE_ENTITY', 'google_entity_id' );
// define( 'INDV_LEXICON_GOVTRACK', 'govtrack' );
// define( 'INDV_LEXICON_FEC_ID', 'fec_id' );

// define( 'INDIVISIBLE_TEXT_DOMAIN',  'indivisible-text-domain' );


// add_action ( 'pre_get_posts', 'indv_plugin_orderby', 10, 1  );
// add_action ( 'pre_get_posts', 'indv_plugin_geography_filter', 1, 1 );
// add_action ( 'indv_plugin_politician_add', 'indv_plugin_add_politician', 10, 5 );

// add_filter ( 'manage_edit-indv_politician_sortable_columns', 'indv_plugin_sortable_politician', 10, 1 );

include 'KEYS.php';

abstract class Indv_Post {
	const POLITICIAN = 'indv_politician';
	const LEGISLATION = 'indv_legislation';
	const ACTION = 'indv_action';
}

abstract class Indv_Field {
	const INDV_ID = 'indv_id';
	const IMAGE = 'image';
	const CONTACT = 'contact';
	const COMMITTEES = 'committees';
	CONST LEXICON = 'lexicon';
	const SUBTITLE = 'subtitle';
	const POLITICIANS = 'politicians';
	const LEGISLATION = 'legislation';
}

abstract class Indv_Term {
	const POSITION = 'position';
	const INTEREST = 'interest';
	const CHAMBER  = 'chamber';
}

abstract class Indv_Lexicon {
	const OPENSTATES = 'openstates';
	const LEGISCAN = 'legiscan';
}

class Indivisible_Plugin {
	public const NAMESPACE = 'indv/v1';
	public const TEXT_DOMAIN = 'indivisible-text-domain';
    
	private $legiscan_bill = array();
	private $open_states_bill = array();
	private $lexicon = array();
	private $cache = array();
	
	function register_post_types () {
	    $args = array (
	        'label' => esc_html__ ( 'Politician', Indivisible_Plugin::TEXT_DOMAIN ),
	        'labels' => array (
	            'menu_name' => esc_html__ ( 'Politicians', Indivisible_Plugin::TEXT_DOMAIN ),
	            'name_admin_bar' => esc_html__ ( 'Politicians', Indivisible_Plugin::TEXT_DOMAIN ),
	            'add_new' => esc_html__ ( 'Find More', Indivisible_Plugin::TEXT_DOMAIN ),
	            'add_new_item' => esc_html__ ( 'Find Another Politician', Indivisible_Plugin::TEXT_DOMAIN ),
	            'new_item' => esc_html__ ( 'New Politician', Indivisible_Plugin::TEXT_DOMAIN ),
	            'edit_item' => esc_html__ ( 'Edit Politician', Indivisible_Plugin::TEXT_DOMAIN ),
	            'view_item' => esc_html__ ( 'View Politician', Indivisible_Plugin::TEXT_DOMAIN ),
	            'update_item' => esc_html__ ( 'Update Politician', Indivisible_Plugin::TEXT_DOMAIN ),
	            'all_items' => esc_html__ ( 'Following', Indivisible_Plugin::TEXT_DOMAIN ),
	            'search_items' => esc_html__ ( 'Search Politicians', Indivisible_Plugin::TEXT_DOMAIN ),
	            'parent_item_colon' => esc_html__ ( 'Parent Politician', Indivisible_Plugin::TEXT_DOMAIN ),
	            'not_found' => esc_html__ ( 'No Politicians found', Indivisible_Plugin::TEXT_DOMAIN ),
	            'not_found_in_trash' => esc_html__ ( 'No Politicians found in Trash', Indivisible_Plugin::TEXT_DOMAIN ),
	            'name' => esc_html__ ( 'Politicians', Indivisible_Plugin::TEXT_DOMAIN ),
	            'singular_name' => esc_html__ ( 'Politician', Indivisible_Plugin::TEXT_DOMAIN )
	        ),
	        'public' => true,
	        'description' => 'Track politicians for Indivisible',
	        'exclude_from_search' => false,
	        'publicly_queryable' => true,
	        'show_ui' => true,
	        'show_in_nav_menus' => true,
	        'show_in_menu' => true,
	        'show_in_admin_bar' => false,
	        'show_in_rest' => true,
	        'rest_base' => 'politicians',
	        'rest_controller_class' => 'Indv_REST_Controller',
	        'capability_type' => 'post',
	        'hierarchical' => true,
	        'has_archive' => true,
	        'query_var' => 'politicians',
	        'can_export' => false,
	        'supports' => array (
	            'title',
	            'editor',
	            'comments',
	            'revisions'
	        ),
			'menu_icon' => 'dashicons-businessman',
			'menu_position' => 5,
	        'rewrite' => array (
	            'slug' => 'politicians',
	            'with_front' => false
	        ),
	    );
	    register_post_type ( Indv_Post::POLITICIAN, $args );
	    
	    $args = array (
	        'label' => esc_html__ ( 'Legislation', Indivisible_Plugin::TEXT_DOMAIN ),
	        'labels' => array (
	            'menu_name' => esc_html__ ( 'Legislation', Indivisible_Plugin::TEXT_DOMAIN ),
	            'name_admin_bar' => esc_html__ ( 'Legislation', Indivisible_Plugin::TEXT_DOMAIN ),
	            'add_new' => esc_html__ ( 'Find More', Indivisible_Plugin::TEXT_DOMAIN ),
	            'add_new_item' => esc_html__ ( 'Find Legislation', Indivisible_Plugin::TEXT_DOMAIN ),
	            'new_item' => esc_html__ ( 'New Legislation', Indivisible_Plugin::TEXT_DOMAIN ),
	            'edit_item' => esc_html__ ( 'Edit Legislation', Indivisible_Plugin::TEXT_DOMAIN ),
	            'view_item' => esc_html__ ( 'View Legislation', Indivisible_Plugin::TEXT_DOMAIN ),
	            'update_item' => esc_html__ ( 'Update Legislation', Indivisible_Plugin::TEXT_DOMAIN ),
	            'all_items' => esc_html__ ( 'Tracking', Indivisible_Plugin::TEXT_DOMAIN ),
	            'search_items' => esc_html__ ( 'Search Legislation', Indivisible_Plugin::TEXT_DOMAIN ),
	            'parent_item_colon' => esc_html__ ( 'Parent Legislation', Indivisible_Plugin::TEXT_DOMAIN ),
	            'not_found' => esc_html__ ( 'No Legislation found', Indivisible_Plugin::TEXT_DOMAIN ),
	            'not_found_in_trash' => esc_html__ ( 'No Legislation found in Trash', Indivisible_Plugin::TEXT_DOMAIN ),
	            'name' => esc_html__ ( 'Legislation', Indivisible_Plugin::TEXT_DOMAIN ),
	            'singular_name' => esc_html__ ( 'Legislation', Indivisible_Plugin::TEXT_DOMAIN )
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
	        'rest_controller_class' => 'Indv_REST_Controller',
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
			'menu_position' => 5,
	        'rewrite' => array (
	            'slug' => 'legislation',
	            'with_front' => false,
	        ),
	    );
	    register_post_type ( Indv_Post::LEGISLATION, $args );
	    
	    $args = array (
	        'label' => esc_html__ ( 'Action', Indivisible_Plugin::TEXT_DOMAIN ),
	        'labels' => array (
	            'menu_name' => esc_html__ ( 'Actions', Indivisible_Plugin::TEXT_DOMAIN ),
	            'name_admin_bar' => esc_html__ ( 'Actions', Indivisible_Plugin::TEXT_DOMAIN ),
	            'add_new' => esc_html__ ( 'Add New', Indivisible_Plugin::TEXT_DOMAIN ),
	            'add_new_item' => esc_html__ ( 'Add New Action', Indivisible_Plugin::TEXT_DOMAIN ),
	            'new_item' => esc_html__ ( 'New Action', Indivisible_Plugin::TEXT_DOMAIN ),
	            'edit_item' => esc_html__ ( 'Edit Action', Indivisible_Plugin::TEXT_DOMAIN ),
	            'view_item' => esc_html__ ( 'View Action', Indivisible_Plugin::TEXT_DOMAIN ),
	            'update_item' => esc_html__ ( 'Update Action', Indivisible_Plugin::TEXT_DOMAIN ),
	            'all_items' => esc_html__ ( 'All Actions', Indivisible_Plugin::TEXT_DOMAIN ),
	            'search_items' => esc_html__ ( 'Search Actions', Indivisible_Plugin::TEXT_DOMAIN ),
	            'parent_item_colon' => esc_html__ ( 'Parent Action', Indivisible_Plugin::TEXT_DOMAIN ),
	            'not_found' => esc_html__ ( 'No Actions found', Indivisible_Plugin::TEXT_DOMAIN ),
	            'not_found_in_trash' => esc_html__ ( 'No Actions found in Trash', Indivisible_Plugin::TEXT_DOMAIN ),
	            'name' => esc_html__ ( 'Action', Indivisible_Plugin::TEXT_DOMAIN ),
	            'singular_name' => esc_html__ ( 'Action', Indivisible_Plugin::TEXT_DOMAIN )
	        ),
	        'public' => true,
	        'description' => 'Track Indivisible actions',
	        'exclude_from_search' => false,
	        'publicly_queryable' => true,
	        'show_ui' => true,
	        'show_in_nav_menus' => true,
	        'show_in_menu' => true,
	        'show_in_admin_bar' => false,
	        'show_in_rest' => true,
	        'rest_base' => 'actions',
	        'rest_controller_class' => 'Indv_REST_Controller',
	        'capability_type' => 'post',
	        'hierarchical' => true,
	        'has_archive' => true,
	        'query_var' => 'actions',
	        'can_export' => true,
	        'supports' => array (
				'title',
				'author',
	            'editor',
	            'comments',
	            'revisions'
	        ),
	        'menu_icon' => 'dashicons-megaphone',
			'menu_position' => 5,
	        'rewrite' => array (
	            'slug' => 'actions',
	            'with_front' => false
	        ),
	    );
	    
	    register_post_type ( Indv_Post::ACTION, $args );
	}
	
	function register_taxonomies () {
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
	    register_taxonomy ( Indv_Term::POSITION, Indv_Post::LEGISLATION, $args );
	    
/* 	    $labels = [
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
	    register_taxonomy ( INDV_BILL_STATUS, Indv_Post::LEGISLATION, $args );
 */	    
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
	    register_taxonomy ( Indv_Term::INTEREST, array( Indv_Post::LEGISLATION, Indv_Post::ACTION, Indv_Post::POLITICIAN ), $args );
	    
	    $labels = [
	        'name' => _x ( 'Chamber', 'taxonomy general name' ),
	        'singular_name' => _x ( 'Chamber', 'taxonomy singular name' ),
	        'search_items' => __ ( 'Search Chambers' ),
	        'all_items' => __ ( 'All Chambers' ),
	        'parent_item' => __ ( 'Parent Chamber' ),
	        'parent_item_colon' => __ ( 'Parent Chamber:' ),
	        'edit_item' => __ ( 'Edit Chamber' ),
	        'update_item' => __ ( 'Update Chamber' ),
	        'add_new_item' => __ ( 'Add New Chamber' ),
	        'new_item_name' => __ ( 'New Chamber Name' ),
	        'menu_name' => __ ( 'Chambers' )
	    ];
	    $args = [
	        'public' => true,
	        'hierarchical' => true, // make it hierarchical (like categories)
	        'labels' => $labels,
	        'show_ui' => true,
	        'show_admin_column' => true,
	        'show_in_rest' => true,
	        'rest_base' => 'chamber',
	        'query_var' => 'chamber',
	        'rewrite' => [
	            'slug' => 'chamber',
	            'witn_front' => false,
	        ]
	    ];
	    register_taxonomy ( Indv_Term::CHAMBER, array( Indv_Post::LEGISLATION, Indv_Post::POLITICIAN ), $args );
	}

	function register_meta_boxes ($post_type) {
		switch ($post_type) {
		case Indv_Post::POLITICIAN:
			add_meta_box( INDV_PLUGIN_BOX_ . 'photo',    esc_html__( 'Photo', Indivisible_Plugin::TEXT_DOMAIN ),
				array( $this, 'render_meta_box' ), get_current_screen(),'side', 'default', 'photo' );
			add_meta_box( INDV_PLUGIN_BOX_ . 'contact',  esc_html__( 'Contact', Indivisible_Plugin::TEXT_DOMAIN ),
				array( $this, 'render_meta_box' ), get_current_screen(),'side', 'default', 'contact' );
			add_meta_box( INDV_PLUGIN_BOX_ . 'actions',  esc_html__( 'Actions', Indivisible_Plugin::TEXT_DOMAIN ),
				array( $this, 'render_meta_box' ), get_current_screen(),'side', 'default', 'actions' );
			add_meta_box( INDV_PLUGIN_BOX_ . 'committees',  esc_html__( 'Committees', Indivisible_Plugin::TEXT_DOMAIN ),
				array( $this, 'render_meta_box' ), get_current_screen(),'side', 'default', 'committees' );
			add_meta_box( INDV_PLUGIN_BOX_ . 'lexicon',  esc_html__( 'Lexicon', Indivisible_Plugin::TEXT_DOMAIN ),
				array( $this, 'render_meta_box' ), get_current_screen(),'side', 'low', 'lexicon' );
			add_meta_box( INDV_PLUGIN_BOX_ . 'links',    esc_html__( 'External Links', Indivisible_Plugin::TEXT_DOMAIN ),
				array( $this, 'render_meta_box' ), get_current_screen(),'normal', 'default', 'links' );
			break;
		case Indv_Post::LEGISLATION:
			add_meta_box( INDV_PLUGIN_BOX_ . 'position', esc_html__( 'Position', Indivisible_Plugin::TEXT_DOMAIN ),
				array( $this, 'render_meta_box' ), get_current_screen(),'side', 'default', 'position' );
			add_meta_box( INDV_PLUGIN_BOX_ . 'acitons',  esc_html__( 'Actions', Indivisible_Plugin::TEXT_DOMAIN ),
				array( $this, 'render_meta_box' ), get_current_screen(),'side', 'default', 'actions' );
			add_meta_box( INDV_PLUGIN_BOX_ . 'lexicon',  esc_html__( 'Lexicon', Indivisible_Plugin::TEXT_DOMAIN ),
				array( $this, 'render_meta_box' ), get_current_screen(),'side', 'low', 'lexicon' );
			add_meta_box( INDV_PLUGIN_BOX_ . 'rollcalls',    esc_html__( 'Rollcall Votes', Indivisible_Plugin::TEXT_DOMAIN ),
				array( $this, 'render_meta_box' ), get_current_screen(),'normal', 'default', 'rollcalls' );
			add_meta_box( INDV_PLUGIN_BOX_ . 'links',    esc_html__( 'External Links', Indivisible_Plugin::TEXT_DOMAIN ),
				array( $this, 'render_meta_box' ), get_current_screen(),'normal', 'low', 'links' );
				break;

		case Indv_Post::ACTION:
			add_meta_box( INDV_PLUGIN_BOX_ . 'links',    esc_html__( 'External Links', Indivisible_Plugin::TEXT_DOMAIN ),
				array( $this, 'render_meta_box' ), get_current_screen(),'normal', 'default', 'links' );
			add_meta_box( INDV_PLUGIN_BOX_ . 'legislation',  esc_html__( 'Legislation', Indivisible_Plugin::TEXT_DOMAIN ),
				array( $this, 'render_meta_box' ), get_current_screen(),'side', 'default', 'legislation' );
			add_meta_box( INDV_PLUGIN_BOX_ . 'politicians',  esc_html__( 'Politicians', Indivisible_Plugin::TEXT_DOMAIN ),
				array( $this, 'render_meta_box' ), get_current_screen(),'side', 'default', 'politicians' );
			break;
		default:
			break;
		}
	}

	function render_meta_box ($post, $box) {
		global $indv;
		$post_type = $post->post_type;
		$box_type = $box['args'];
		wp_nonce_field( basename( __FILE__ ), INDV_PLUGIN_NONCE_ . $box_type );
		
		switch ($box_type) {

			case 'links':
				switch ($post_type) {
					case Indv_Post::LEGISLATION:
						// $post_slug = $post->post_name;
						// $parts = explode('-', strtoupper($post_slug));
						// $url = 'https://legiscan.com/' . $parts[0] . '/bill/' . $parts[1] . '/' . $parts[2];
						// echo '<strong>Legiscan: </strong><a target="_blank" rel="noopener noreferrer" href=\'' . $url . '\'>' . $url . '</a><br>';
						break;
					default:
						echo "Eventually <a >links back to useful places</a>";
				}
				break;
				
			case 'lexicon':
				$lexicon = $indv->get_lexicon($post->ID);
				if (!is_array($lexicon))
					break;
				foreach ($lexicon as $key => $value) 
					echo $key . ' => ' . $value . '<br>';
				break;
				
			case 'photo':
				$photo_url = get_post_meta($post->ID, Indv_Field::IMAGE, true);
				echo '<input type=\'text\' value=\'' . $photo_url . '\'>';
				echo '<img width="100%" src="' . $photo_url . '" >';
				break;
			
			case 'contact':
				$contact = get_post_meta($post->ID, 'contact', true);
				echo '<table id=\'indv_plugin_box_contact\'>';
				if ($contact)
					foreach($contact as $detail) {
						echo '<tr><td>' . $detail['type'] . '</td><td>' . $detail['value'] . '</td><td>' . $detail['label'] . '</td><td>' . $detail['note'] . '</td></tr>';
					}
				echo '</table>';
				break;
					
			case 'committees':
				$committees = get_post_meta($post->ID, Indv_Field::COMMITTEES, false);
				echo '<table id=\'indv_plugin_box_committees\'>';
				if ($committees)
					foreach($committees as $committee) {
						echo '<tr><td>' . $committee . '</td></tr>';
					}
				echo '</table>';
				break;
				
			case 'position':
				if (Indv_Post::LEGISLATION != $post_type)
					break; // !?
				$post_slug = $post->post_name;
				$legislation = implode(' ',explode('-',strtoupper($post_slug)));
				$term = wp_get_post_terms($post->ID, Indv_Term::POSITION );
				if ($term)				
					$selected = $term[0]->slug;
				else
					$selected = 'no selection';
				$terms = get_terms( [
						'taxonomy' => Indv_Term::POSITION,
						'orderby' => 'id',
						'hide_empty' => false
				] );
				echo '<label for="indv_plugin_box_position">' . $legislation . '</label>';
				echo '<select id="indv_plugin_box_position" name="' . Indv_Term::POSITION . '">';
				foreach ($terms as $term) {
					echo '<option value=\'' . $term->slug . '\' ' . ($term->slug == $selected ? 'selected' : '') . '>' . $term->name . '</option>';
				};
				echo '</select>';
				break;

			case 'rollcalls':
				$bill = $indv->getLegiscanBill($post->ID);
				if ($bill) {
					$votes = $bill['votes'];
					echo '<table style="padding:5px;">';
					if (!empty($votes)) {
						echo '<tr><th>Date</th><th></th><th width="3em">Yea</th><th width="3em">Nay</th><th width="3em">NV</th><th></th><th width="60%">Description</th></tr>';
						foreach ($votes as $vote) {
							// echo '<tr><td><select><option>&#x2795;</option><option selected>&nbsp;</option><option>&#x2796;</option></select></td>';
							echo '<td>' . $vote['date'] . '</td><td>' . $vote['chamber'] . '</td><td> ' . $vote['yea'] . '</td><td>' . $vote['nay'] . '</td><td>'  . ($vote['nv'] + $vote['absent']) . '</td>';
							if ($vote['passed'])
								echo '<td>Passed</td>';
							else
								echo '<td>Failed</td>';
							echo '<td>' . $vote['desc'] . '</td></tr>';
						}
					}
					echo '</table>';
				}
				break;

			case 'actions':
				switch($post_type) {
					case Indv_Post::POLITICIAN:
						$query = new WP_Query(array(
							'fields' => 'all',
							'post_type' => Indv_Post::ACTION,
							'politician' => $post->ID,
						));
						if ($query->found_posts)
							foreach($query->posts as $action)
								echo '<h4><a id="indv_action_' . $action->ID . '" href="' . site_url() . '?p=' . $action->ID 
								. '" target="indv_window_' . $action->ID . '">' 
								. ($action->post_title ? $action->post_title : '(no title)') . '</a></h4>';
						echo '<a class="button button-primary" id="indv_state_new_action" href="' 
						. admin_url('post-new.php?post_type=indv_action') . '&' . Indv_Field::POLITICIANS . '=' . $post->ID 
						. '&post_title=' . $post->post_title . '">Start New Action<span class="screen-reader-text"> (opens in a new tab)</span></a>';
					break;

					case Indv_Post::LEGISLATION:
						$query = new WP_Query(array(
							'fields' => 'all',
							'post_type' => Indv_Post::ACTION,
							'politician' => $post->ID,
						));
						if ($query->found_posts)
							foreach($query->posts as $legislation)
								echo '<h4><a id="indv_action_' . $legislation->ID . '" href="' . site_url() . '?p=' . $legislation->ID 
								. '" target="indv_window_' . $legislation->ID . '">' 
								. ($legislation->post_title ? $legislation->post_title : 'No title') . '</a></h4>';
						echo '<a class="button button-primary" id="indv_state_new_action" href="' 
						. admin_url('post-new.php?post_type=indv_action') . '&' . Indv_Field::LEGISLATION . '=' . $post->ID 
						. '&post_title=' . $post->post_title . '">Start New Action<span class="screen-reader-text"> (opens in a new tab)</span></a>';
					break;
				}
				break;
				
			case 'politicians':
				$politcians = get_post_meta($post->ID, Indv_Field::POLITICIANS, false);
				foreach($politcians as $politcian) {
					$pol_post = get_post($politcian);
					echo '<a href="' . site_url() . '?p=' . $politcian . '" target="indv_window_' . $politcian . '">' . $pol_post->post_title . '</a>';
				};
				break;
			
			case 'legislation':
				$legislation = get_post_meta($post->ID, Indv_Field::LEGISLATION, false);
				foreach($legislation as $bill) {
					$leg_post = get_post($bill);
					echo '<a href="' . site_url() . '?p=' . $bill . '" target="indv_window_' . $bill . '">' . $leg_post->post_title . '</a>';
				}
				break;
						
			default:
				break;
		}
	}
	
	function register_rest_api () {
    	register_rest_field( Indv_Post::POLITICIAN, 'indv_id', array(
    	    'get_callback'    => function ( $object, $field_name, $request ) {
				return get_post_meta( $object[ 'id' ], $field_name, true );
			},
    	    'update_callback' => function ( $value, $object, $field_name, $request ) {
				if ( ! $value || ! is_string( $value ) )
					return;
				return update_post_meta( $object->ID, $field_name, sanitize_text_field( $value ) );
			},
    	    'schema'          => array(
    	        'description' => __( 'Indivisible Identifier', Indivisible_Plugin::TEXT_DOMAIN ),
    	        'type'        => 'string'
    	    ) )
    	);
    	register_rest_field( Indv_Post::POLITICIAN, 'image', array(
    	    'get_callback'    => function ( $object, $field_name, $request ) {
				return get_post_meta( $object[ 'id' ], $field_name, true );
			},
    	    'update_callback' => function ( $value, $object, $field_name, $request ) {
				if ( ! $value || !is_string( $value ) )
					return;
				return update_post_meta( $object->ID, $field_name, sanitize_url( $value ) );
			},
    	    'schema'          => array(
    	        'description' => __( 'URL of stock image', Indivisible_Plugin::TEXT_DOMAIN ),
    	        'type'        => 'string'
    	    ) )
    	);
     	register_rest_field( Indv_Post::POLITICIAN, Indv_Field::CONTACT, array(
    	    'get_callback'    => function ( $object, $field_name, $request ) {
				return get_post_meta( $object[ 'id' ], $field_name, true );
			},
    	    'update_callback' => function ( $value, $object, $field_name, $request ) {
				if ( ! $value || ! is_array( $value ) )
					return;
				return update_post_meta( $object->ID, $field_name, $value );
			},
    	    'schema'          => array(
    	        'description' => __( 'Contact details', Indivisible_Plugin::TEXT_DOMAIN ),
				'type'        => 'array',
				'items'       => array(
					'type'		=> 'object',
					'properties' => array(
						'type'     => array( 'type' => 'string' ),
						'value'    => array( 'type' => 'string' ),
						'note'     => array( 'type' => 'string' ),
						'label'    => array( 'type' => 'string' ),
					),
					'aditionalProperties' => false,
				),
    	    ) )
    	);
    	register_rest_field( Indv_Post::POLITICIAN, Indv_Field::COMMITTEES, array(
    	    'get_callback'    => function ( $object, $field_name, $request ) {
				return get_post_meta( $object[ 'id' ], $field_name, false );
			},
    	    'update_callback' => function ( $value, $object, $field_name, $request ) {
				if ( ! $value || ! is_array( $value ) )
					return;
				foreach($value as $committee)
					add_post_meta( $object->ID, $field_name, sanitize_text_field( $committee ) );
			},
    	    'schema'          => array(
    	        'description' => __( 'Legislative committee', Indivisible_Plugin::TEXT_DOMAIN ),
				'type'       => 'array',
				'items'		  => array(
					'type'	    => 'string',
				)
			) )
    	);
 	    register_rest_field( array(Indv_Post::POLITICIAN, Indv_Post::LEGISLATION ), 'lexicon', array(
	        'get_callback' => function(  $object, $field_name, $request ) {
				global $indv;
				return $indv->get_lexicon( $object[ 'id' ] );
	        },
    	    'update_callback' => function ( $value, $object, $field_name, $request ) {
				if ( ! $value || !is_array( $value ) )
					return;
				foreach($value as $index => $text ) {
					if (is_string($index) && is_string($text)) 
						$value[$index] = sanitize_text_field($text);
					else
						return;
				}
				$this->lexicon[$object->ID] = $value;
				return update_post_meta( $object->ID, $field_name, $value );
			},
	        'schema' => array(
	            'description' => __( 'Lexicon of external identifiers', Indivisible_Plugin::TEXT_DOMAIN ),
	            'type'        => 'object'
	        ) )
	    );
    	register_rest_field( Indv_Post::POLITICIAN, 'subtitle', array(
    	    'get_callback'    => function( $object, $field_name, $request ) {
				return get_post_meta( $object[ 'id' ], $field_name, true );
			},
    	    'update_callback' => function ( $value, $object, $field_name, $request ) {
				if ( ! $value || ! is_array( $value ) )
					return;
				foreach($value as $index => $text ) {
					if (is_integer($index) && is_string($text)) 
						$value[$index] = sanitize_text_field($text);
					else
						return;
				}
				return update_post_meta( $object->ID, $field_name, $value );
			},
    	    'schema'          => array(
    	        'description' => __( 'Subtitle text array', Indivisible_Plugin::TEXT_DOMAIN ),
				'type'        => 'array',
				'items'       => array (
					'type'      => 'string',
				),
    	    ) )
    	);
        foreach (array('calendar', 'history', 'votes') as $rest_field)
            register_rest_field( Indv_Post::LEGISLATION, $rest_field, array(
                'get_callback' => function ($object, $field_name, $request) {
					global $indv;
					$bill = $indv->getLegiscanBill($object['id']);
					if ($bill && isset($bill[$field_name]))
						return $bill[$field_name];
                },
                'schema' => array(
                    'description' => __( 'Reflect Legiscan', Indivisible_Plugin::TEXT_DOMAIN ),
                    'type'        => 'object'
                ) )
            );
		register_rest_route( 'indv/v1', '/politicians/autocomplete', array(
			'methods' => 'GET',
			'callback' => array( $this, 'politician_directory' ),
		) );

// 	    register_rest_field( INDV_POLITICIAN, 'photo_url', array(
// 	        'get_callback' => function(  $object, $field_name, $request ) {
// 	        return get_post_meta( $object[ 'id' ], INDV_PHOTO_URL, true );
// 	        },
// 	        'update_callback' => function( $value, $object, $field_name ) {
// 	        if ( ! $value || ! is_string( $value ) )
// 	            return;
// 	            return update_post_meta( $object->ID, INDV_PHOTO_URL, sanitize_url( $value ) );
// 	        },
// 	        'schema' => array(
// 	            'description' => __( 'Photo URL', INDIVISIBLE_TEXT_DOMAIN ),
// 	            'type'        => 'string'
// 	        ) )
// 	    );
	    
// 	    foreach (array('full_name', 'first_name', 'middle_name', 'last_name', 'roles', 'email', 'url', 'offices') as $rest_field)
// 	        register_rest_field( INDV_POLITICIAN, $rest_field, array(
// 	            'get_callback' => function ($object, $field_name, $request) {
//     	            global $indv;
//     	            $post_slug = $object['slug'];
//     	            $politician = strtoupper($post_slug);
//     	            if (is_national($politician)) {
//     	                $member = $indv->get_congress('members/' . $politician)[0];
//     	                if ($member && isset($member[$field_name]))
//     	                    return $member[$field_name];
//     	                    if ($field_name == 'full_name')
//     	                        return $member['first_name']
//     	                        . ($member['middle_name'] ? (' ' . $member['middle_name']) : '') . ' '
//     	                            . $member['last_name']
//     	                            . ($member['suffix'] ? (' ' . $member['suffix']) : '');
//     	            } else {
//     	                $url = OPEN_STATES_URL . 'legislators/' . $politician . '/';;
//     	                $legislator = $indv->get_json($url);
//     	                if ($legislator && isset($legislator[$field_name]))
//     	                    return $legislator[$field_name];
//     	            }
// 	            },
// 	            'schema' => array(
// 	                'description' => __( 'Reflect Open States', INDIVISIBLE_TEXT_DOMAIN ),
// 	                'type'        => 'object'
// 	            )
// 	        )
// 	    );
	        
            
//         foreach (array('sponsors', ) as $rest_field)
//             register_rest_field( INDV_LEGISLATION, $rest_field, array(
//                 'get_callback' => function ($object, $field_name, $request) {
//                 global $indv;
//                 $bill = $indv->getOpenStatesBill($object['id']);
//                 if ($bill && isset($bill[$field_name]))
//                     return $bill[$field_name];
//                 },
//                 'schema' => array(
//                     'description' => __( 'Reflect Open States', INDIVISIBLE_TEXT_DOMAIN ),
//                     'type'        => 'object'
//                 ) )
//             );
            
//             register_rest_route( 'indv/v1', '/autocomplete/politician', array(
//                 'methods' => 'GET',
//                 'callback' => 'indv_plugin_politician_directory',
//             ) );
    }
	
	public function register_settings () {
	    register_setting ( 'indivisible', 'legislatures', array (
	        'description' => 'Legislatures to follow'  ) );
	    
	    register_setting ( 'indivisible', 'states', array (
	        // 'sanitize_callback' => 'indv_plugin_sanitize',
	        'description' => 'States to follow'  ) );
	    
	    add_settings_section (
	        'default',
	        __( 'Follow Politicians', Indivisible_Plugin::TEXT_DOMAIN ),
	        '',
	        'indv_settings' );
	        
		add_settings_field(
			'legislatures',
			__( 'Legislatures', Indivisible_Plugin::TEXT_DOMAIN ),
			function () {
				$current = get_option ( 'legislatures' );
				
				echo '<table><tr>';
				echo '<td><input type="checkbox" id="indv_settings_federal" name="legislatures[federal]"' . (isset($current['federal']) ? ' checked' : '') . '>';
				echo '<label for="indv_settings_federal">Federal</label></td>';
				echo '<td><input type="checkbox" id="indv_settings_state" name="legislatures[state]"' . (isset($current['state']) ? ' checked' : '') . '>';
				echo '<label for="indv_settings_state">State</label></td>';
				echo '</tr></table>';
			},
			'indv_settings',
			'default'
		);
		
		add_settings_field(
			'states',
			__( 'States', Indivisible_Plugin::TEXT_DOMAIN ),
			function () {
				$states = [
					[  'AK', 'AL', 'AR', 'AZ', 'CA', 'CO', 'CT', 'DE', 'FL', 'GA', ],
					[  'HI', 'IA', 'ID', 'IL', 'IN', 'KS', 'KY', 'LA', 'MA', 'MD', ],
					[  'ME', 'MO', 'MI', 'MN', 'MS', 'MT', 'NB', 'NC', 'ND', 'NH', ],
					[  'NJ', 'NM', 'NV', 'NY', 'OH', 'OK', 'OR', 'PA', 'RI', 'SC', ],
					[  'SD', 'TN', 'TX', 'UT', 'VA', 'VT', 'WA', 'WI', 'WV', 'WY', ],
					[  'AS', 'DC', 'PR', ]
				];
				
				$current = get_option ( 'states' );
				
				echo '<table>';
				foreach ($states as $row) {
					echo '<tr>';
					foreach ($row as $state) {
						$html = '<td width="">' . '<input type="checkbox" id="indv_settings_state_' . $state .'" name="states[' . $state . ']" ' . (isset($current[$state]) ? 'checked' : '') . '>';
						$html .= '<label for="indv_settings_state_' . $state . '">' . $state . '</label>';
						$html .= '</td>';
						echo $html;
					}
					echo '</tr>';
				}
				echo '</table>';
			},
			'indv_settings',
			'default'
		);
	}
	
	function render_settings() {
	    if (! current_user_can ( 'manage_options' )) {
	        wp_die ( __ ( 'You do not have sufficient permissions to access this page.' ) );
	    }
	    // show error/update messages
		settings_errors( 'indv_messages' );
		
	    ?>
		<div class="wrap">
		<h1>Indivisible Settings</h1>

		<form method="post" action="options.php">
            <?php  settings_fields( 'indivisible' ); ?>
            <?php  do_settings_sections( 'indv_settings' ); ?>

            <?php submit_button(); ?>
        </form>
		<?php
	    if ( isset( $_GET['settings-updated'] ) ) {
		?> 
		<progress id="indv_plugin_update_progress">Progress Bar</progress>
		<br>
		<?php
		} else {
			?> 
			<progress id="indv_plugin_update_progress" value=100 max=`100>Progress Bar</progress>
			<br>
			<?php
			}
			?> 
        <button id="indv_plugin_ajax_button" class="button button-primary" onclick="console.log('set');">Update Politicians</button>
    	<div id="indv_plugin_update_status" >
    	</div>
    	<table id="indv_plugin_update_new" >
    	</table>
		<hr/>
    	<table id="indv_plugin_update_old" >
    	</table>
        <script>
        	jQuery(document).ready(function($) {
        		var data = {
        			'action': 'my_action',
        			'whatever': 1234
        		};
        		console.log("ready");
				jQuery("#indv_plugin_ajax_button").click(()=>{ 
					console.log("go");
					indv_update($);
				});
        	});
        </script>
    	</div>
    	<?php
    }

	function plugin_errors() {
		global $post_type, $post_id;
	   if (!$post_id || $post_type != Indv_Post::LEGISLATION)
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
						case 'cant_create_politicians' :
							echo 'You can\'t create politicians this way. Only the administrator can do it.';
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

	function plugin_column($column_name, $post_id)
	{
		switch ($column_name) {
			case 'pposition':
				$term = wp_get_post_terms($post_id, Indv_Term::POSITION, array('orderby' => 'name', 'order' => 'ASC', 'fields' => 'all'));
				if (!isset($term[0]))
					break;
				$term = $term[0];
				echo '<a href=' . get_admin_url() . 'edit.php?post_type=indv_legislation&indv_position=' . $term->slug . '>' . $term->name . '</a>';
				break;
			case 'votes':
				$votes = '?'; //get_post_meta($post_id, INDV_VOTES, true);
				echo '<pre>' . $votes . '</pre>';
				break;
			case 'chamber':
				$term = wp_get_post_terms($post_id, Indv_Term::CHAMBER, array('orderby' => 'name', 'order' => 'ASC', 'fields' => 'all'));
				if (!isset($term[0]))
					break;
				$term = $term[0];
				echo '<a href=' . get_admin_url() . 'edit.php?post_type=indv_legislation&chamber=' . $term->slug . '>' . $term->name . '</a>';
				break;
			case 'identifier':
				$post_slug = get_post($post_id)->post_name;
				$legislation = implode(' ', explode('-', strtoupper($post_slug)));
				echo $legislation;
				break;
			default:
				break;
		}
	}

	function plugin_subtitle ($post) {
		global $indv;
		
		switch ($post->post_type) {
			case Indv_Post::POLITICIAN:
				$subtitle =  get_post_meta( $post->ID, Indv_Field::SUBTITLE, true );
				break;
				
			case Indv_Post::LEGISLATION:
				$subtitle = array();
				$post_slug = $post->post_name;
				$legislation = implode(' ',explode('-', strtoupper($post_slug)));
				if ($post_slug) {
					$html = $legislation;
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
				};
				break;
			
			case Indv_Post::ACTION:
				$subtitle = array();
				if (isset($_REQUEST['referrer'])) {
					$politcian = absint($_REQUEST['referrer']);
					$subtitle[] = get_the_title( $politcian );
					$oldtitle =  get_post_meta( $politcian, Indv_Field::SUBTITLE, true );
					foreach($oldtitle as $title)
						$subtitle[] = $title;
				}
				break;
				
			default:
				break;
		}
		
		return $subtitle;
	}
	
	function plugin_permalink($return, $post_id, $new_title, $new_slug, $post)
	{
		if (in_array($post->post_type, array(Indv_Post::POLITICIAN, Indv_Post::LEGISLATION)))
			$return = str_replace('button type', 'button disabled type', $return);

		return $return;
	}

	function plugin_preamble ($post)
	{
		global $indv;
		
		echo '<div class="inside">';
		$post_slug = $post->post_name;
		if ($post_slug) {
			$subtitles = $this->plugin_subtitle($post);
			echo '<h4>';
			if ($subtitles) 
				foreach($subtitles as $subtitle)
				echo $subtitle . '<br/>';
			echo '</h4>';
		} else 
			switch ($post->post_type) {
		
			case Indv_Post::POLITICIAN:
				echo __( "Enter just the surname of a politician", Indivisible_Plugin::TEXT_DOMAIN );
				break;
	
			case Indv_Post::LEGISLATION:
				echo __( 'Enter a bill identifier like CA AB123 2018 or paste in the URL of a bill from Legiscan', Indivisible_Plugin::TEXT_DOMAIN );
				break;

			default:
				break;
		}
		echo '</div>';
	}
	   
	function save_post ( $post_id, $post, $update ) {
		global $indv;
		if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE )
			return $post_id;
			
		$post_type = $post->post_type;
		$pto = get_post_type_object( $post_type );
		if ( !current_user_can( $pto->cap->edit_post, $post_id ) )
			return $post_id;
				
		$nonces = [
				Indv_Post::POLITICIAN  => array( 'photo', 'contact' ),
				Indv_Post::LEGISLATION => array( 'position', 'lexicon' ),
				Indv_Post::ACTION      => array (),
		];
		foreach ($nonces[$post_type] as $nonce )
			if ( !isset( $_POST[INDV_PLUGIN_NONCE_ . $nonce] ) || !wp_verify_nonce( $_POST[INDV_PLUGIN_NONCE_ . $nonce], basename( __FILE__ ) ) )
				return $post_id;
		
		switch ($post_type) {
			
			case Indv_Post::POLITICIAN:
				$post_slug = $post->post_name;
				$legislature = $post->legislature;
				$chamber = $legislature . ' ' . $post->chamber;
	            // $gov = wp_insert_term($legislature, Indv_Term::CHAMBER);
	            // $leg = wp_insert_term($chamber, Indv_Term::CHAMBER, array(
				// 	'parent' => $gov['id'],
				// ));
				// wp_set_post_terms( $post_id, $leg['id'], Indv_Term::CHAMBER, true );

				$title = $post->post_title;
				if (!$title) {
					wp_redirect(get_admin_url() . 'edit.php?post_type=' . Indv_Post::POLITICIAN);
					exit();
				}
				if ($post_slug == '') {
					wp_delete_post($post_id, true);
					add_filter('redirect_post_location', function ($location) {
						return add_query_arg('indv_plugin_error', 'cant_create_politicians', $location);
					});
					wp_redirect(get_admin_url() . 'edit.php?post_type=indv_politician');
					exit();
				}
				break;

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
					update_post_meta($post_id, Indv_Field::LEXICON, $lexicon);
					
					if ($photo_url)
						update_post_meta($post_id, Indv_Field::IMAGE, $photo_url);
					
					// $with = rand( 0, 10 );
					// $against = rand( 0, 10);
					// $votes = sprintf('%3d / %3d', $with, $against);
					// update_post_meta($post_id, INDV_VOTES, $votes);
					// if (($with + $against) > 0)
					// 	update_post_meta($post_id, INDV_VOTE_SCORE, $with / ($with + $against));
							
					global $wpdb;
					$wpdb->update( $wpdb->posts, array( 
						'post_title' => $full_name,
						'post_name' => strtolower($politician),
					), array( 
						'ID' => $post_id ) );
				}
				break;
			
			case Indv_Post::LEGISLATION:
				$post_slug = $post->post_name;
				$lexicon = get_post_meta( $post_id, Indv_Field::LEXICON, true);
				if ($lexicon) {
					$position = wp_get_post_terms( $post_id, Indv_Term::POSITION, array('orderby' => 'id', 'order' => 'ASC', 'fields' => 'all') )[0];
					if (isset( $_POST[Indv_Term::POSITION] )) {
						$new_position = $_POST[Indv_Term::POSITION];
						if ($new_position != $position->slug  && in_array($new_position, array(
								'support', 'oppose', 'interested')))
							wp_set_post_terms( $post_id, $new_position, Indv_Term::POSITION);
					};
					// $bill_status = wp_get_post_terms( $post_id, INDV_BILL_STATUS, array('orderby' => 'id', 'order' => 'ASC', 'fields' => 'all') )[0];
					// if (isset( $_POST['indv_legislation_status'] )) {
					// 	$new_status = $_POST['indv_legislation_status'];
					// 	if ($new_status != $bill_status->slug  && in_array($new_status, array( 'introduced', 
					// 			'1st-house-policy', '1st-house-appropriations', '1st-house-floor',
					// 			'2nd-house-policy', '2nd-house-appropriations', '2nd-house-floor',
					// 			'govenor-signed' )))
					// 		wp_set_post_terms( $post_id, $new_status, INDV_BILL_STATUS);
					// }
				} else {
					preg_match ( '/(?:^|[^A-Z]+)([A-Z][A-Z])(?:\/.*\/|[^A-Z]|[\s,-\/]*)([A-Z]+(?:\s*)[\d]+)[\s,-\/]*(\d\d\d\d|)/',
							strtoupper($post->post_title), $matches, PREG_OFFSET_CAPTURE );
					if (count($matches) == 4)
						$key = array ( $matches [1] [0], $matches [2] [0], $matches [3] [0] );
					else 
						$key = false;
					if ($key && (!isset($key[2]) || $key[2] == ''))
						$key[2] = 2; // legiscan for this year
					if ($key) {
						$legislation = strtolower(implode('-', $key));
						$query = new WP_Query(array(
								'fields' => 'ids',
								'post_type' => Indv_Post::LEGISLATION,
								'pagename' => $legislation,
						));
						if ($query->found_posts) {
							wp_delete_post($post_id, true);
							wp_redirect(get_edit_post_link($query->posts[0], 'link'));
							exit;
						} else {
							$legiscan = $indv->legiscan_lookup($key);
							if ($legiscan) {
								$bill_id = $legiscan ['bill_id'];
								$state = $legiscan ['state'];
								$bill = $legiscan ['bill_number'];
								$year = $legiscan ['url'];
								$year = substr ( $year, strlen ( $year ) - 4 );
								$key = array ( $state, $bill, $year );
								$legislation = strtolower(implode('-', $key)); // canonical id
								$legiscan = $indv->legiscan_lookup ( $key );
								if ($bill_id != $legiscan ['bill_id']) {
									wp_die("Legiscan sanity check failed");
									exit();
								}
								$query = new WP_Query(array(
										'fields' => 'ids',
										'post_type' => Indv_Post::LEGISLATION,
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
							$leg2 = $this->get_legiscan('?op=getBill&id=' . $legiscan['bill_id']);
							if ($leg2['status'] == 'OK')
								$legiscan = $leg2['bill'];
							$session = $legiscan['session'];
							if ($session['year_start'] != $session['year_end'])
								$session = $session['year_start'] . $session['year_end'];
							else
								$session = $session['year_end'];
							$jurisdiction = 'ocd-jurisdiction/country:us/state:' . strtolower($state) . '/government';
							preg_match ( '/([A-Z]+(?:\s*))([\d]+)/',
								strtoupper($bill), $matches, PREG_OFFSET_CAPTURE );
							$open_states = $this->graph_query('https://openstates.org/graphql', OS_BILL, array(
								'jurisdiction'=> $jurisdiction,
								'session'=> $session,
								'bill' => $matches[1][0] . ' ' . $matches[2][0],
								// 'bill' =>'AB 1285',
							));
							if ($open_states)
								$open_states = $open_states['bill'];

							$lexicon = array();
							$lexicon[Indv_Lexicon::LEGISCAN] = $legiscan['bill_id'];
							if ($open_states)
								$lexicon[Indv_Lexicon::OPENSTATES] = $open_states['id'];
							
							$post_slug = strtolower(implode('-', $key));
							$title = sanitize_text_field( $legiscan['title'] );
							$excerpt = sanitize_text_field( $legiscan['description'] );
							update_post_meta( $post_id, 'lexicon', $lexicon);
							wp_set_post_terms( $post_id, 'interested', Indv_Term::POSITION);
							global $wpdb;
							$wpdb->update ( $wpdb->posts, array (
									'post_title' => $title,
									'post_name' => $post_slug,
									'post_content' => $excerpt,
								), array (
									'ID' => $post_id 
								) );
						}
					} else {
						add_filter('redirect_post_location', function( $location ) {
							return add_query_arg( 'indv_plugin_error', 'bad_bill_id', $location ); });
					}
				}
			
			case Indv_Post::ACTION:
				if (isset($_REQUEST[Indv_Field::POLITICIANS])) {
					$politcians = absint( $_REQUEST[Indv_Field::POLITICIANS] );
					update_post_meta( $post_id, Indv_Field::POLITICIANS, $politcians );
				}
				if (isset($_REQUEST[Indv_Field::LEGISLATION])) {
					$legislation = absint( $_REQUEST[Indv_Field::LEGISLATION] );
					update_post_meta( $post_id, Indv_Field::LEGISLATION, $legislation );
				}
				if (isset($_REQUEST['indv_inactivate']) && $_REQUEST['indv_inactivate'] === 'inactivate') {
					global $wpdb;
					$wpdb->update( $wpdb->posts, array( 
						'post_status' => 'inactive',
					), array( 
						'ID' => $post_id ) );
				}
				break;

			default:
		}
	}
		
	function taxonomy_select($post_type, $which){
	
		if (in_array($post_type, array( Indv_Post::LEGISLATION ))) {
			$taxonomy_slug = Indv_Term::POSITION;
			$taxonomy = get_taxonomy($taxonomy_slug);
			$selected = '';
			$request_attr = 'position'; //this will show up in the url
			if ( isset($_REQUEST[$request_attr] ) ) {
				$selected = $_REQUEST[$request_attr]; //in case the current page is already filtered
			}
			$x = wp_dropdown_categories(array(
					'show_option_all' =>  $taxonomy->labels->all_items,
					'show_option_none' =>  __( 'Suspense' ),
					'taxonomy'        =>  $taxonomy_slug,
					'name'            =>  $request_attr,
					'selected'        =>  $selected,
					'hierarchical'    =>  false,
					'show_count'      =>  false, // Show number of post in parent term
					'hide_empty'      =>  false, // Don't show posts w/o terms
					'value_field'     => 'slug',
			));
		}
		
		if (in_array($post_type, array( Indv_Post::POLITICIAN, Indv_Post::LEGISLATION, Indv_Post::ACTION ))) {
			$taxonomy_slug = Indv_Term::INTEREST;
			$taxonomy = get_taxonomy($taxonomy_slug);
			$selected = '';
			$request_attr = 'interest'; //this will show up in the url
			if ( isset($_REQUEST[$request_attr] ) ) {
				$selected = $_REQUEST[$request_attr]; //in case the current page is already filtered
			}
			wp_dropdown_categories(array(
					'show_option_all' =>  $taxonomy->labels->all_items,
					'taxonomy'        =>  $taxonomy_slug,
					'name'            =>  $request_attr,
					'orderby'         =>  'name',
					'selected'        =>  $selected,
					'hierarchical'    =>  true,
					'depth'           =>  3,
					'show_count'      =>  false, // Show number of post in parent term
					'hide_empty'      =>  false, // Don't show posts w/o terms
					'hide_if_empty'   =>  true,
					'value_field'	  => 'slug',
			));
		}
		
		if (in_array($post_type, array( Indv_Post::POLITICIAN, Indv_Post::LEGISLATION ))) {
			$taxonomy_slug = Indv_Term::CHAMBER;
			$taxonomy = get_taxonomy($taxonomy_slug);
			$selected = '';
			$request_attr = 'chamber'; //this will show up in the url
			if ( isset($_REQUEST[$request_attr] ) ) {
				$selected = $_REQUEST[$request_attr]; //in case the current page is already filtered
			}
			wp_dropdown_categories(array(
					'show_option_all' =>  $taxonomy->labels->all_items,
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
		
		// if (in_array($post_type, array( INDV_LEGISLATION, INDV_ACTION ))) {
		// 	$taxonomy_slug = INDV_ISSUE;
		// 	$taxonomy = get_taxonomy($taxonomy_slug);
		// 	$selected = '';
		// 	$request_attr = 'issue'; //this will show up in the url
		// 	if ( isset($_REQUEST[$request_attr] ) ) {
		// 		$selected = $_REQUEST[$request_attr]; //in case the current page is already filtered
		// 	}
		// 	wp_dropdown_categories(array(
		// 			'show_option_all' =>  __("Show All {$taxonomy->label}"),
		// 			'taxonomy'        =>  $taxonomy_slug,
		// 			'name'            =>  $request_attr,
		// 			'orderby'         =>  'name',
		// 			'selected'        =>  $selected,
		// 			'hierarchical'    =>  true,
		// 			'depth'           =>  3,
		// 			'show_count'      =>  false, // Show number of post in parent term
		// 			'hide_empty'      =>  false, // Don't show posts w/o terms
		// 			'hide_if_empty'   =>  false,
		// 			'value_field'	  => 'slug',
		// 	));
		// }
	}

	function query_filter( $query ) {
		if($query->get("indv-id")) {
			$query->set( 'meta_key', 'indv_id' );
			$query->set( 'meta_value', $query->get('indv-id') );
		}
		$post_type = $query->get('post_type');
		switch ($post_type) {
			case Indv_Post::POLITICIAN:
				if (isset($request['legislation'])) {
					$id = absint($request['legislation']);
					$bills = get_terms( array(
						'taxonomy' => Indv_Term::CHAMBER,
						'object_ids' => $id,
						'hierarchical' => false,
						'fields' => 'tt_ids',
					) );
					$args['tax_query'] = array( array(
						'taxonomy' => Indv_Term::CHAMBER,
						'terms' => $bills,
						'field' => 'term_taxonomy_ids',
						'operator' => 'IN',
					) );
				};
				$name = $query->get('by_name');
				if ($name) {
					global $wpdb;
					$sql = $wpdb->prepare( "
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
						break;
					}
				}
				$keyword = $query->get('s');
				if ($keyword && $keyword !== '')
					break;
				$civic_key = $query->get('civic_key');
				if (!$civic_key && isset($query->query_vars['civic_key']) && isset($_COOKIE['civic_key']))
					$civic_key = $_COOKIE['civic_key'];
				$lat = $query->get('lat');
				$lng = $query->get('lng');
				if ($civic_key || $lat && $lng) {
					if ($lat && $lng) {
						$politicians = $this->get_json((CIVIC_KEY_URL . 'location-search?lat=' . $lat . '&lng=' . $lng));
						if (isset($query->query_vars['civic_key']))
							setcookie( 'civic_key', $politicians['civic_key'], 0, '/' );
					} else
						$politicians = $this->get_json((CIVIC_KEY_URL . 'location-search?civic_key=' . $civic_key));
					$politicians = $politicians['politicians'];
					$meta_query = array ( 'relation' => 'OR' );
					foreach($politicians as $politician) 
						$meta_query[] = array(
							'key' => 'indv_id',
							'value' => $politician,
						);
					$query->set('meta_query', $meta_query );
				}
				break;

			case Indv_Post::LEGISLATION:
				$politician = $query->get( 'politician' );
				if ($politician) {
					$politician = absint($politician);
					$chambers = get_terms( array(
						'taxonomy' => Indv_Term::CHAMBER,
						'object_ids' => $politician,
						'hierarchical' => false,
						'fields' => 'tt_ids',
					) );
					$query->set( 'tax_query', array( array(
						'taxonomy' => Indv_Term::CHAMBER,
						'terms' => $chambers,
						'field' => 'term_taxonomy_ids',
						'include_children' => false,
						'operator' => 'IN',
					) ) );
				};
				break;

			case Indv_Post::ACTION:
				$politician = $query->get( 'politician' );
				if ($politician) {
					$politician = absint($politician);
					$subquery = new WP_Query( array(
						'post_type' => Indv_Post::LEGISLATION,
						'fields' => 'ids',
						'politician' => $politician,
					) );
					$legislation = $subquery->posts;
					if (empty($legislation))
						$query->set( 'meta_query', array(
							 array (
								'key' => Indv_Field::POLITICIANS,
								'value' => $politician, ),
					) );
					else
						$query->set( 'meta_query', array(
							'relation' => 'OR',
							array(
								'key' => Indv_Field::LEGISLATION,
								'value' => $legislation,
								'type' => 'UNSIGNED',
								'compare' => 'IN', ),
							 array (
								'key' => Indv_Field::POLITICIANS,
								'value' => $politician, ),
					) );
				};
				$legislation = $query->get('bill');
				if ($legislation) {
					$chambers = get_terms( array(
						'taxonomy' => Indv_Term::CHAMBER,
						'object_ids' => $legislation,
						'hierarchical' => false,
						'fields' => 'tt_ids',
					) );
					if (empty($chambers))
						$legislation = 0;
					$query->set( 'meta_query', array( array(
						'key' => Indv_Field::LEGISLATION,
						'value' => array( absint( $legislation ) ),
						'type' => 'UNSIGNED',
						'compare' => 'IN',
					) ) );
				};
				break;

			default:
		}
	}

	public function ajax_handler () {
	    check_ajax_referer('indv_action');
	    echo "sonething";
	    // do something
	    wp_die();
	}
	    
	public function activate () {
	    // trigger our function that registers the custom post type
	    $this->register_post_types();
		$this->register_taxonomies();
		
	    foreach (array( 'Interested', 'Support', 'Oppose') as $term)
	        if (!term_exists($term, Indv_Term::POSITION))
	            wp_insert_term($term, Indv_Term::POSITION);
		
		// foreach (array( 'Introduced',
		// 		'1st House Policy', '1st House Appropriations', '1st House Floor',
		// 		'2nd House Policy', '2nd House Appropriations', '2nd House Floor',
		// 		'Govenor Signed' ) as $term)
		// 	if (!term_exists($term, INDV_BILL_STATUS))
		// 		wp_insert_term($term, INDV_BILL_STATUS);

		$role_set = get_role( 'editor' )->capabilities;
		add_role( 'publisher', 'Publisher', $role_set );
		wp_roles()->remove_cap( 'editor', 'publish_posts' );
		wp_roles()->remove_cap( 'author', 'publish_posts' );

		delete_option('legislatures');
		delete_option('states');
		
		// clear the permalinks after the post type has been registered
		flush_rewrite_rules ();	                    
	}
	
	public function deactivate () {
	    // our post type will be automatically removed, so no need to unregister it
	    
		$role_set = get_role( 'editor' )->capabilities;
		remove_role( 'publisher' );
		wp_roles()->add_cap( 'editor', 'publish_posts' );
		wp_roles()->add_cap( 'author', 'publish_posts' );

	    // clear the permalinks to remove our post type's rules
	    flush_rewrite_rules ();
	    
	    // remove cron
	    $timestamp = wp_next_scheduled ( 'indv_plugin_cron_hook' );
	    wp_unschedule_event ( $timestamp, 'indv_plugin_cron_hook ');
	    wp_clear_scheduled_hook( 'indv_plugin_politician_add' );
	}
	
	public function uninstall () {
	    
	}
	
	public static function cron_update () {
	    
	}
	
	public function add_help () {
	    $screen = get_current_screen();
	    
	    $screen->add_help_tab( array(
	        'id'	=> 'indv_plugin_help_tab',
	        'title'	=> __('My Help Tab'),
	        'content'	=> '<p>' . __( 'Descriptive content that will show in My Help Tab-body goes here.' ) . '</p>',
	    ) );
	}
	
	public function __construct() {
		add_action ( 'init', 		function () {
			register_post_status( 'inactive', array(
				'label'                     => _x( 'Inactive', 'post' ),
				'public'                    => false,
				'show_in_admin_all_list'    => true,
				'show_in_admin_status_list' => true,
				'label_count'               => _n_noop( 'Inactive (%s)', 'Inactive (%s)' ),
			) );
			// add_rewrite_tag( '%politician%', '([^&]+)' );
			// add_rewrite_rule( '^politician/([^/]*)/?', 'index.php?politicians=$matches[1]','top' );
			// add_rewrite_rule( '^politicians/?', 'index.php?post_type=indv_politician','top' );
		} );
	    add_action ( 'init', array( $this, 'register_post_types' ) );
	    add_action ( 'init', array( $this, 'register_taxonomies' ) );
		add_action ( 'rest_api_init', array( $this, 'register_rest_api' ) );
		
		add_shortcode( 'indv_react', function ( $atts, $content = null ) {
			$atts = shortcode_atts( array(
				'class' => 'default',
				'args' => '',
				'id' => null,
			), $atts );
			if (!isset($atts['id']))
				return;
			$content 
			= '<div id="' . $atts['id'] . '"></div>'
			. '<script type="text/javascript">'
			. 'let dom_element = document.getElementById("' . $atts['id'] . '");'
			. 'let react_element = React.createElement(' . $atts['class'] . ', {toWhat: "' . $atts['args'] . '"}, null);'
			. 'ReactDOM.render( react_element, dom_element );'
			. '</script>';
			return $content;
		} );


		add_filter ( 'query_vars', function ($vars) {
	        $vars[] = 'lng';
	        $vars[] = 'lat';
			$vars[] = 'politician';
			$vars[] = 'bill';
			$vars[] = 'by_name';
			$vars[] = 'referrer';
			$vars[] = 'civic_key';
			$vars[] = 'indv-id';
	        return $vars;
	    } );
		add_action ( 'pre_get_posts', array( $this, 'query_filter' ), 10, 1 );
	    
	    add_action ( 'indv_plugin_chron_hook', array( $this, 'cron_update' ) );
	    if (! wp_next_scheduled ( 'indv_plugin_chron_hook' )) {
	        wp_schedule_event ( time(), 'hourly', 'indv_plugin_chron_hook' );
	    }
	    
	    if (is_admin()) {
	        add_action ( 'admin_init', array( $this, 'register_settings' ) );
	        add_action ( 'admin_menu', function () {
	            add_options_page ( 'Indivisible', 'Indivisible', 'manage_options', 'indv_settings', array( $this, 'render_settings' ) );
				remove_submenu_page( 'edit.php?post_type=indv_politician', 'post-new.php?post_type=indv_politician' );
	        } );
			add_action ( 'admin_head', function () {
				echo '<style type="text/css">';
				if (isset($_REQUEST['post_type']) && $_REQUEST['post_type'] == Indv_Post::POLITICIAN) {
					echo '.wrap .page-title-action { display:none; } ';	
				};
				if (isset($_REQUEST['post_type']) && $_REQUEST['post_type'] == Indv_Post::LEGISLATION) {
					echo '#chamber-adder { display:none; } ';	
					echo '.wp-list-table .column-id { width: 5%; }';
					echo '.wp-list-table .column-title { width: 35%; }';
					echo '.wp-list-table .column-author { width: 35%; }';
					echo '.wp-list-table .column-identifier { width: 6%; }';	
			// 		echo 'label.indv-radio .indv-radio { margin:20px; color: red; padding: 25px; }';
				}
				echo '#chamber-adder { display: none; } ';	
				echo '.indv-admin { text-align: left;
					padding-top: 10px;
					// speak: none;
						}
						#indv-inactivate::before { 
						display: inline-block;
						font: normal 20px/1 dashicons;
						content: "\f157";
						margin-left: -1px;
						padding-right: 3px;
						vertical-align: top;
							}';
				echo '</style>';
			}, 10, 1 );
    	    add_action ( 'load-edit.php',     array( $this, 'add_help' ) );
	        add_action ( 'load-post.php',     array( $this, 'add_help' ) );
			add_action ( 'load-post-new.php', array( $this, 'add_help' ) );
			add_action ( 'load-post-new.php',function () {
				if (isset($_REQUEST['post_type']) && $_REQUEST['post_type'] === Indv_Post::POLITICIAN) {
					wp_redirect(get_admin_url() . 'edit.php?post_type=' . Indv_Post::POLITICIAN);
					exit();
				}
			} );

	        add_action ( 'wp_ajax_indv_action', array( $this, 'ajax_handler') );
	        add_action ( 'admin_enqueue_scripts', function ( $hook ) {
	            if( 'settings_page_indv_settings' != $hook ) return;
	            wp_enqueue_script( 'indv-ajax-script',
	                plugins_url( '/js/admin-ajax.js', __FILE__ ),
	                array( 'jquery' )
	            );
	            wp_localize_script( 'indv-ajax-script', 'indv_ajax_obj', array(
	                'ajax_url'   => admin_url( 'admin-ajax.php' ),
					'ajax_nonce' => wp_create_nonce( 'indv_action' ),
					'rest_url'   => rest_url(),
					'rest_nonce' => wp_create_nonce( 'wp_rest' ),
					'user_id'    => get_current_user_id(),
				) );
				
				wp_enqueue_style( 'indv-admin-style', plugins_url( '/css/admin-style.css', __FILE__ ) );
    	    } );
			
			add_action ( 'admin_notices', array( $this, 'plugin_errors' ) );
			add_action ( 'edit_form_before_permalink', array( $this, 'plugin_preamble' ) );
			add_action ( 'get_sample_permalink_html',  array( $this, 'plugin_permalink' ), 10, 5 );
			add_action ( 'add_meta_boxes', array( $this, 'register_meta_boxes'), 10, 3 );
			add_action ( 'save_post_indv_politician',  array( $this, 'save_post' ), 10, 3 );
			add_action ( 'save_post_indv_legislation',  array( $this, 'save_post' ), 10, 3 );
			add_action ( 'save_post_indv_action',  array( $this, 'save_post' ), 10, 3 );
			add_action ( 'manage_pages_custom_column', array( $this, 'plugin_column' ), 10, 2 );
			add_action ( 'restrict_manage_posts', array( $this, 'taxonomy_select' ), 10, 2);
			add_action ( 'post_submitbox_minor_actions', function (WP_Post $post) {
				if ($post->post_type === Indv_Post::ACTION ) {
					if ($post->post_status === 'publish') {
						echo '<div class="clear"></div>';
						echo '<div id="indv-inactivate"  class="indv-admin">';
						echo ' Inactivate: ';
						echo '<input type="checkbox" name="indv_inactivate" value="inactivate" />';
						echo '</div>';
					}
				}
			} );

			add_filter ( 'manage_indv_politician_posts_columns',  function ( $columns ) {
				$new_columns = array_merge(array_slice( $columns, 0, 2 ), array( 'votes' => __('Votes') ), array_slice($columns, 2));
				$new_columns['title'] = __('Name');
				return $new_columns;
			}, 10, 1 );
			add_filter ( 'manage_indv_legislation_posts_columns', function ( $columns ) {
				$new_columns = array_merge(array_slice( $columns, 0, 2 ), array( 'identifier' => __('Identifier'), 'pposition' => __('Position') ), array_slice($columns, 3));
				return $new_columns;
			}, 10, 1 );

			add_filter ( 'use_block_editor_for_post_type', 
				function ($use_block_editor, $post_type) {
					if (Indv_Post::LEGISLATION === $post_type 
					 || Indv_Post::POLITICIAN  === $post_type
					 || Indv_Post::ACTION      === $post_type)
						return false;
					else
						return $use_block_editor;
				}, 10, 2 );

			// wp_enqueue_script( 'wp-api' );
		}

	    register_activation_hook   ( __FILE__, array( $this, 'activate'   ) );
	    register_deactivation_hook ( __FILE__, array( $this, 'deactivate' ) );
// 	    register_uninstall_hook    ( __FILE__, array( $this , 'uninstall' ) );

		add_action ( 'add_meta_boxes', function() {
			remove_meta_box ( 'twitter-custom', get_current_screen (), 'advanced' );
			remove_meta_box ( 'twitter-custom', get_current_screen (), 'normal' );
		}, 90 );
	}

	function politician_directory ($data) {
		global $wpdb;
		
		$query = "
			SELECT post_name, post_title
			FROM $wpdb->posts
			WHERE post_type = 'indv_politician'
				AND post_status = 'publish' ";
		$term = $data['term'];
		if ($term)	
			$query = $wpdb->prepare(
				$query . "
					AND post_title LIKE '%s' ",
					'%' . $term . '%' );
		
		$politcians = $wpdb->get_results($query);
		$directory = array ();
		foreach ($politcians as $politician)
			$directory[] = $politician->post_title;
		
		return $directory;
	}
		
	public function getLegiscanBill($post_id) {
		$id = (int) $post_id;
		if (isset($this->legiscan_bill[$id]))
			return $this->legiscan_bill[$id];

		$lexicon = $this->get_lexicon($id);
		if (!$lexicon)
			return null;

		$url = LEGISCAN_URL . "?op=getBill&id=" . $lexicon[Indv_Lexicon::LEGISCAN];
		$legiscan = $this->get_json($url);
		if ($legiscan['status'] == "OK")
			return $this->legiscan_bill[$id] = $legiscan['bill'];
		else
			return null;
	}
	
	public function getOpenStatesBill($post_id) {
		$id = (int) $post_id;
		if (isset($this->open_states_bill[$id]))
			return $this->open_states_bill[$id];
			
		$lexicon = $this->get_lexicon($id);
		$url = OPEN_STATES_URL . "bills/" . $lexicon[Indv_Lexicon::OPENSTATES];
		$bill = $this->open_states_bill[$id] = $this->get_json($url);
		
		return $bill;
	}
	
	protected function get_json ($endpoint) {
		if (isset($this->cache[$endpoint]))
			return $this->cache[$endpoint];
		
		$response = wp_remote_get($endpoint);
		$raw_body = wp_remote_retrieve_body($response);
		$results = json_decode ( $raw_body, true );
		if ($results)
			$this->cache[$endpoint] = $results;
		return $results;
	}

	public function graph_query ($endpoint, $query, $data = array() ) {

		$response = wp_remote_post($endpoint, array(
			'body' => json_encode( array (
				'query' => $query,
				'variables' => $data,
			) ),
			'headers' => array(
				'Content-Type'=> 'application/json',
				'Accept'=> 'application/json',
				'X-API-KEY' => $keys['open_states'],
			),
		) );
		$raw_body = wp_remote_retrieve_body($response);
		$results = json_decode( $raw_body, true );
		if (!isset($results['errors']))
			return $results['data'];
		else
			return null;
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
		if (isset($this->lexicon[$id]))
			return $this->lexicon[$id];
		else
			return $this->lexicon[$id] = get_post_meta($post_id, 'lexicon', true);
	}
}

class Indv_REST_Controller extends WP_REST_Posts_Controller {

	function query_filter( $args, $request ) {
		if(isset($request["indv-id"])) {
			$args['meta_key'] = 'indv_id';
			$args['meta_value'] = $request["indv-id"];
		}
		switch ($this->post_type) {
			case Indv_Post::POLITICIAN:
				if (isset($request['bill'])) {
					$id = absint($request['bill']);
					$bills = get_terms( array(
						'taxonomy' => Indv_Term::CHAMBER,
						'object_ids' => $id,
						'hierarchical' => false,
						'fields' => 'tt_ids',
					) );
					$args['tax_query'] = array( array(
						'taxonomy' => Indv_Term::CHAMBER,
						'terms' => $bills,
						'field' => 'term_taxonomy_ids',
						'operator' => 'IN',
					) );
				};
				break;
			case Indv_Post::LEGISLATION:
				if (isset($request['politician'])) {
					$id = absint($request['politician']);
					$chambers = get_terms( array(
						'taxonomy' => Indv_Term::CHAMBER,
						'object_ids' => $id,
						'hierarchical' => false,
						'fields' => 'tt_ids',
					) );
					$args['tax_query'] = array( array(
						'taxonomy' => Indv_Term::CHAMBER,
						'terms' => $chambers,
						'field' => 'term_taxonomy_ids',
						'include_children' => false,
						'operator' => 'IN',
					) );
				};
				break;
			case Indv_Post::ACTION:
				if (isset($request['politician'])) {
					$politician = absint($request['politician']);
					$chambers = get_terms( array(
						'taxonomy' => Indv_Term::CHAMBER,
						'object_ids' => $politician,
						'hierarchical' => false,
						'fields' => 'tt_ids',
					) );
					$query = new WP_Query( array(
						'post_type' => Indv_Post::LEGISLATION,
						'fields' => 'ids',
						'tax_query' => array( array(
							'taxonomy' => Indv_Term::CHAMBER,
							'terms' => $chambers,
							'field' => 'term_taxonomy_ids',
							'include_children' => false,
							'operator' => 'IN',
					) ) ) );
					$legislation = $query->posts;
					if (empty($legislation))
							$legislation = array( 0 );
					$args['meta_query'] = array( 
						'relation' => 'OR',
						array(
							'key' => Indv_Field::LEGISLATION,
							'value' => $legislation,
							'type' => 'UNSIGNED',
							'compare' => 'IN', ),
						array(
							'key' => Indv_Field::POLITICIANS,
							'value' => $politician,
					) );
				};
				if (isset($request['bill'])) {
					$legislation = absint($request['bill']);
					// $args['meta_key'] = Indv_Field::LEGISLATION;
					// $args['meta_value'] = $legislation;
					$args['meta_query'] = array( array(
						'key' => 'bill',
						'value' => array( $legislation ),
						'type' => 'UNSIGNED',
						'compare' => 'IN',
					) );
				};
				break;
			}
	 
		return $args;
	}

	public function get_items( $request ) {
		if ( $request->get_param('lng') != null && $request->get_param('lat') != null ) {
			$url = CIVIC_KEY_URL . 'location-search';
			$url = $url . '?lng=' . $request->get_param('lng');
			$url = $url . '&lat=' . $request->get_param('lat');
			$response = wp_remote_get( $url );
			$response = rest_ensure_response($response);
			if (is_wp_error($response))
				return $response;
			$json = json_decode($response->get_data()['body']);
			$politicians = $json->politicians;
			$results = array();
			foreach ($politicians as $politician) {
				if (!(substr($politician, 0, 4 ) == 'ocd-'))
					continue;
				$query = file_get_contents(plugin_dir_path(__FILE__) . 'graphql/politician-by-id.gql');
				$vars = array( 'id' => $politician );
				$body = array( 'query' =>preg_replace('/\s+/', ' ', $query), 'variables' => $vars);
				$body = json_encode($body);
				$args = array(
					'headers' => array(
						'Content-Type' => 'application/json',
						'Accept' => 'application/json',
						'X-API-KEY' => $keys['open_states'],
					),
					'body' => $body,
				);
				$response = wp_remote_post('http://openstates.org/graphql/', $args );
				if (is_wp_error($response))
					return $response;
				$json = json_decode($response['body']);
				$results[] = $json->data;
			}
			return $results;
		} elseif ($request->get_param('indv-id') != null) {
			$id = $request->get_param('indv-id');
		}

		return parent::get_items( $request );
	}

	function create_item( $request ) {
		$legislature = $request->get_param('legislature');
		$chamber = $request->get_param('chamber_name');
		$description = $legislature . ' ' . $chamber;
		$gov = term_exists($legislature, Indv_Term::CHAMBER);
		if (!$gov)
			$gov = wp_insert_term($legislature, Indv_Term::CHAMBER, array(
				'description' => $legislature,
				'slug' => sanitize_title_with_dashes($legislature),

			));
		if (is_wp_error($gov))
			return $response;
		$leg = term_exists($chamber, Indv_Term::CHAMBER, absint($gov['term_id']));
		if (!$leg)
			$leg = wp_insert_term($chamber, Indv_Term::CHAMBER, array(
				'parent' => $gov['term_id'],
				'description' => $description,
				'slug' => sanitize_title_with_dashes($description),
		));
		if (is_wp_error($leg))
			return $response;
		$request->set_param('chamber', array( absint($leg['term_id']) ));

		return parent::create_item( $request );
	}
    
    /**
     * Retrieves the query params for the collections.
     *
     * @since 4.7.0
     *
     * @return array Query parameters for the collection.
     */
    public function get_collection_params() {
        $query_params = parent::get_collection_params();
        
        $query_params['context']['default'] = 'view';
        
        switch ($this->post_type) {
            case Indv_Post::POLITICIAN:
                $query_params['lng'] = array(
                    'description' => __( 'Longitude' ),
                    'type'        => 'number', 
                    'minimum'     => -180.0,
                    'maximum'     =>  180.0,
                );
                $query_params['lat'] = array(
                    'description' => __( 'Latitude' ),
                    'type'        => 'number',
                    'minimum'     => -90.0,
                    'maximum'     =>  90.0,
                );
                $query_params['indv-id'] = array(
                    'description' => __( 'Indivisible Identifier' ),
                    'type'        => 'string',
                    'sanatize_callback' => 'sanatize_text_field',
				);
				break;
	
			case Indv_Post::LEGISLATION:
				$query_params['politician'] = array(
					'description' => __( 'Politicians voting on this bill' ),
					'type'              => 'integer',
					'default'           => 1,
					'sanitize_callback' => 'absint',
				);
				break;

			case Indv_Post::ACTION:
				$query_params['politician'] = array(
					'description' => __( 'Politicians voting on this bill' ),
					'oneOf' => array(
						array(
							'type'              => 'integer',
							'minimum'           => 1,
							'sanitize_callback' => 'absint',
						),
						array(
							'typ' => 'array',
							'items' => array( 
								'type' => 'integer',
								'minimum' => 1,
								'sanitize_callback' => 'absint',
							 ),
						),
					),
				);
				$query_params['bill'] = array(
					'description' => __( 'Politicians voting on this bill' ),
					'oneOf' => array(
						array(
							'type'              => 'integer',
							'minimum'           => 1,
							'sanitize_callback' => 'absint',
						),
						array(
							'typ' => 'array',
							'items' => array( 
								'type' => 'integer',
								'minimum' => 1,
								'sanitize_callback' => 'absint',
							 ),
						),
					),
				);
				break;
			}

        return $query_params;
    }

	function __construct( $post_type ) {
		parent::__construct( $post_type );
		add_filter('rest_' . $post_type .'_query', array( $this, 'query_filter' ), 10, 2);
	}
}


global $indv;
$indv = new Indivisible_Plugin();

/////////////////////////////////////////////////////

// function indv_plugin_post_types() {
// 	$args = array (
// 			'label' => esc_html__ ( 'Politician', INDIVISIBLE_TEXT_DOMAIN ),
// 			'labels' => array (
// 					'menu_name' => esc_html__ ( 'Politicians', INDIVISIBLE_TEXT_DOMAIN ),
// 					'name_admin_bar' => esc_html__ ( 'Politicians', INDIVISIBLE_TEXT_DOMAIN ),
// 					'add_new' => esc_html__ ( 'Find More', INDIVISIBLE_TEXT_DOMAIN ),
// 					'add_new_item' => esc_html__ ( 'Find Another Politician', INDIVISIBLE_TEXT_DOMAIN ),
// 					'new_item' => esc_html__ ( 'New Politician', INDIVISIBLE_TEXT_DOMAIN ),
// 					'edit_item' => esc_html__ ( 'Edit Politician', INDIVISIBLE_TEXT_DOMAIN ),
// 					'view_item' => esc_html__ ( 'View Politician', INDIVISIBLE_TEXT_DOMAIN ),
// 					'update_item' => esc_html__ ( 'Update Politician', INDIVISIBLE_TEXT_DOMAIN ),
// 					'all_items' => esc_html__ ( 'Following', INDIVISIBLE_TEXT_DOMAIN ),
// 					'search_items' => esc_html__ ( 'Search Politicians', INDIVISIBLE_TEXT_DOMAIN ),
// 					'parent_item_colon' => esc_html__ ( 'Parent Politician', INDIVISIBLE_TEXT_DOMAIN ),
// 					'not_found' => esc_html__ ( 'No Politicians found', INDIVISIBLE_TEXT_DOMAIN ),
// 					'not_found_in_trash' => esc_html__ ( 'No Politicians found in Trash', INDIVISIBLE_TEXT_DOMAIN ),
// 					'name' => esc_html__ ( 'Politicians', INDIVISIBLE_TEXT_DOMAIN ),
// 					'singular_name' => esc_html__ ( 'Politician', INDIVISIBLE_TEXT_DOMAIN )
// 			),
// 			'public' => true,
// 			'description' => 'Track legislation for Indivisible',
// 			'exclude_from_search' => false,
// 			'publicly_queryable' => true,
// 			'show_ui' => true,
// 			'show_in_nav_menus' => true,
// 			'show_in_menu' => true,
// 			'show_in_admin_bar' => false,
// 			'show_in_rest' => true,
// 			'rest_base' => 'politician',
// 			'capability_type' => 'post',
// 			'hierarchical' => true,
// 			'has_archive' => true,
// 			'query_var' => 'politician',
// 			'can_export' => false,
// 			'supports' => array (
// 					'title',
// 					'editor',
// 					'comments',
// 					'revisions'
// 			),
// 			'menu_icon' => 'dashicons-businessman',
// 			'rewrite' => array (
// 					'slug' => 'politician',
// 					'with_front' => false
// 			),
// 	);
// 	register_post_type ( INDV_POLITICIAN, $args );
	
// 	$args = array (
// 			'label' => esc_html__ ( 'Legislation', INDIVISIBLE_TEXT_DOMAIN ),
// 			'labels' => array (
// 					'menu_name' => esc_html__ ( 'Legislation', INDIVISIBLE_TEXT_DOMAIN ),
// 					'name_admin_bar' => esc_html__ ( 'Legislation', INDIVISIBLE_TEXT_DOMAIN ),
// 					'add_new' => esc_html__ ( 'Find More', INDIVISIBLE_TEXT_DOMAIN ),
// 					'add_new_item' => esc_html__ ( 'Find Legislation', INDIVISIBLE_TEXT_DOMAIN ),
// 					'new_item' => esc_html__ ( 'New Legislation', INDIVISIBLE_TEXT_DOMAIN ),
// 					'edit_item' => esc_html__ ( 'Edit Legislation', INDIVISIBLE_TEXT_DOMAIN ),
// 					'view_item' => esc_html__ ( 'View Legislation', INDIVISIBLE_TEXT_DOMAIN ),
// 					'update_item' => esc_html__ ( 'Update Legislation', INDIVISIBLE_TEXT_DOMAIN ),
// 					'all_items' => esc_html__ ( 'Tracking', INDIVISIBLE_TEXT_DOMAIN ),
// 					'search_items' => esc_html__ ( 'Search Legislation', INDIVISIBLE_TEXT_DOMAIN ),
// 					'parent_item_colon' => esc_html__ ( 'Parent Legislation', INDIVISIBLE_TEXT_DOMAIN ),
// 					'not_found' => esc_html__ ( 'No Legislation found', INDIVISIBLE_TEXT_DOMAIN ),
// 					'not_found_in_trash' => esc_html__ ( 'No Legislation found in Trash', INDIVISIBLE_TEXT_DOMAIN ),
// 					'name' => esc_html__ ( 'Legislation', INDIVISIBLE_TEXT_DOMAIN ),
// 					'singular_name' => esc_html__ ( 'Legislation', INDIVISIBLE_TEXT_DOMAIN )
// 			),
// 			'public' => true,
// 			'description' => 'Track legislation for Indivisible',
// 			'exclude_from_search' => false,
// 			'publicly_queryable' => true,
// 			'show_ui' => true,
// 			'show_in_nav_menus' => true,
// 			'show_in_menu' => true,
// 			'show_in_admin_bar' => false,
// 			'show_in_rest' => true,
// 			'rest_base' => 'legislation',
// 			'capability_type' => 'post',
// 			'hierarchical' => true,
// 			'has_archive' => true,
// 			'query_var' => 'legislation',
// 			'can_export' => true,
// 			'supports' => array (
// 					'title',
// 					'editor',
// 					'excerpt',
// 					'comments',
// 					'revisions'
// 			),
// 			'menu_icon' => 'dashicons-media-text',
// 			'rewrite' => array (
// 					'slug' => 'legislation',
// 					'with_front' => false,
// 			),
// 	);
// 	register_post_type ( INDV_LEGISLATION, $args );
	
// 	$args = array (
// 			'label' => esc_html__ ( 'Action', INDIVISIBLE_TEXT_DOMAIN ),
// 			'labels' => array (
// 					'menu_name' => esc_html__ ( 'Actions', INDIVISIBLE_TEXT_DOMAIN ),
// 					'name_admin_bar' => esc_html__ ( 'Actions', INDIVISIBLE_TEXT_DOMAIN ),
// 					'add_new' => esc_html__ ( 'Add New', INDIVISIBLE_TEXT_DOMAIN ),
// 					'add_new_item' => esc_html__ ( 'Add New Action', INDIVISIBLE_TEXT_DOMAIN ),
// 					'new_item' => esc_html__ ( 'New Action', INDIVISIBLE_TEXT_DOMAIN ),
// 					'edit_item' => esc_html__ ( 'Edit Action', INDIVISIBLE_TEXT_DOMAIN ),
// 					'view_item' => esc_html__ ( 'View Action', INDIVISIBLE_TEXT_DOMAIN ),
// 					'update_item' => esc_html__ ( 'Update Action', INDIVISIBLE_TEXT_DOMAIN ),
// 					'all_items' => esc_html__ ( 'All Actions', INDIVISIBLE_TEXT_DOMAIN ),
// 					'search_items' => esc_html__ ( 'Search Actions', INDIVISIBLE_TEXT_DOMAIN ),
// 					'parent_item_colon' => esc_html__ ( 'Parent Action', INDIVISIBLE_TEXT_DOMAIN ),
// 					'not_found' => esc_html__ ( 'No Actions found', INDIVISIBLE_TEXT_DOMAIN ),
// 					'not_found_in_trash' => esc_html__ ( 'No Actions found in Trash', INDIVISIBLE_TEXT_DOMAIN ),
// 					'name' => esc_html__ ( 'Action', INDIVISIBLE_TEXT_DOMAIN ),
// 					'singular_name' => esc_html__ ( 'Action', INDIVISIBLE_TEXT_DOMAIN )
// 			),
// 			'public' => true,
// 			'description' => 'Track legislation for Indivisible',
// 			'exclude_from_search' => false,
// 			'publicly_queryable' => true,
// 			'show_ui' => true,
// 			'show_in_nav_menus' => true,
// 			'show_in_menu' => true,
// 			'show_in_admin_bar' => false,
// 			'show_in_rest' => true,
// 			'rest_base' => 'actions',
// 			'capability_type' => 'post',
// 			'hierarchical' => true,
// 			'has_archive' => true,
// 			'query_var' => 'actions',
// 			'can_export' => true,
// 			'supports' => array (
// 					'title',
// 					'editor',
// 					'comments',
// 					'revisions'
// 			),
// 			'menu_icon' => 'dashicons-megaphone',
// 			'rewrite' => array (
// 					'slug' => 'actions',
// 					'with_front' => false
// 			),
// 	);
	
// 	register_post_type ( INDV_ACTION, $args );
// }

// function indv_plugin_taxonomies() {
// 	$labels = [
// 			'name' => _x ( 'Position', 'taxonomy general name' ),
// 			'singular_name' => _x ( 'Position', 'taxonomy singular name' ),
// 			'search_items' => __ ( 'Search Positions' ),
// 			'all_items' => __ ( 'All Positions' ),
// 			'parent_item' => __ ( 'Parent Position' ),
// 			'parent_item_colon' => __ ( 'Parent Position:' ),
// 			'edit_item' => __ ( 'Edit Position' ),
// 			'update_item' => __ ( 'Update Position' ),
// 			'add_new_item' => __ ( 'Add New Position' ),
// 			'new_item_name' => __ ( 'New Position Name' ),
// 			'menu_name' => __ ( 'Position' )
// 	];
// 	$args = [
// 			'public' => true,
// 			'hierarchical' => false,
// 			'labels' => $labels,
// 			'show_ui' => false,
// 			'show_admin_column' => true,
// 			'show_in_rest' => true,
// 			'rest_base' => 'position',
// 			'query_var' => 'position',
// 			'rewrite' => [
// 					'slug' => 'position',
// 					'witn_front' => false,
// 			]
// 	];
// 	register_taxonomy ( INDV_POSITION, INDV_LEGISLATION, $args );
			
// 	$labels = [
// 			'name' => _x ( 'Status', 'taxonomy general name' ),
// 			'singular_name' => _x ( 'Status', 'taxonomy singular name' ),
// 			'search_items' => __ ( 'Search Status' ),
// 			'all_items' => __ ( 'All Status' ),
// 			'parent_item' => __ ( 'Parent Status' ),
// 			'parent_item_colon' => __ ( 'Parent Status:' ),
// 			'edit_item' => __ ( 'Edit Status' ),
// 			'update_item' => __ ( 'Update Status' ),
// 			'add_new_item' => __ ( 'Add New Status' ),
// 			'new_item_name' => __ ( 'New Status Name' ),
// 			'menu_name' => __ ( 'Status' )
// 	];
// 	$args = [
// 			'public' => true,
// 			'hierarchical' => false,
// 			'labels' => $labels,
// 			'show_ui' => false,
// 			'show_admin_column' => true,
// 			'show_in_rest' => true,
// 			'rest_base' => 'bill_status',
// 			'query_var' => 'bill_status',
// 			'rewrite' => [
// 					'slug' => 'bill_status',
// 					'witn_front' => false,
// 			]
// 	];
// 	register_taxonomy ( INDV_BILL_STATUS, INDV_LEGISLATION, $args );
	
// 	$labels = [
// 			'name' => _x ( 'Interest', 'taxonomy general name' ),
// 			'singular_name' => _x ( 'Interest', 'taxonomy singular name' ),
// 			'search_items' => __ ( 'Search Interests' ),
// 			'all_items' => __ ( 'All Interested' ),
// 			'parent_item' => __ ( 'Parent Interest' ),
// 			'parent_item_colon' => __ ( 'Parent Interest:' ),
// 			'edit_item' => __ ( 'Edit Interest' ),
// 			'update_item' => __ ( 'Update Interest' ),
// 			'add_new_item' => __ ( 'Add New Interest' ),
// 			'new_item_name' => __ ( 'New Interest Name' ),
// 			'menu_name' => __ ( 'Interested' )
// 	];
// 	$args = [
// 			'public' => true,
// 			'hierarchical' => true, // make it hierarchical (like categories)
// 			'labels' => $labels,
// 			'show_ui' => true,
// 			'show_admin_column' => true,
// 			'show_in_rest' => true,
// 			'rest_base' => 'interest',
// 			'query_var' => 'interest',
// 			'rewrite' => [
// 					'slug' => 'interest',
// 					'witn_front' => false,
// 			]
// 	];
// 	register_taxonomy ( INDV_INTEREST, array( INDV_LEGISLATION, INDV_ACTION, INDV_POLITICIAN ), $args );

// 	$labels = [
// 			'name' => _x ( 'Issue', 'taxonomy general name' ),
// 			'singular_name' => _x ( 'Issue', 'taxonomy singular name' ),
// 			'search_items' => __ ( 'Search Issues' ),
// 			'all_items' => __ ( 'All Issues' ),
// 			'parent_item' => __ ( 'Parent Issue' ),
// 			'parent_item_colon' => __ ( 'Parent Issue:' ),
// 			'edit_item' => __ ( 'Edit Issue' ),
// 			'update_item' => __ ( 'Update Issue' ),
// 			'add_new_item' => __ ( 'Add New Issue' ),
// 			'new_item_name' => __ ( 'New Issue Name' ),
// 			'menu_name' => __ ( 'Issues' )
// 	];
// 	$args = [
// 			'public' => true,
// 			'hierarchical' => true, // make it hierarchical (like categories)
// 			'labels' => $labels,
// 			'show_ui' => true,
// 			'show_admin_column' => true,
// 			'show_in_rest' => true,
// 			'rest_base' => 'issue',
// 			'query_var' => 'issue',
// 			'rewrite' => [
// 					'slug' => 'issue',
// 					'witn_front' => false,
// 			]
// 	];
// 	register_taxonomy ( INDV_ISSUE, array( INDV_LEGISLATION, INDV_ACTION ), $args );
	
// }

/* function indv_plugin_taxonomy_select($post_type, $which){
	
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
 */
/* function indv_plugin_geography_filter( $query ) {
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
 */
// function indv_plugin_rest_init () {
// 	register_rest_field( array(INDV_POLITICIAN, INDV_LEGISLATION ), 'lexicon', array(
// 		'get_callback' => function(  $object, $field_name, $request ) {
// 			global $indv;
// 			return $indv->get_lexicon( $object[ 'id' ] );
// 		},
// 		'schema' => array(
// 			'description' => __( 'Lexicon', INDIVISIBLE_TEXT_DOMAIN ),
// 			'type'        => 'object'
// 		) )
// 	);
	
// 	register_rest_field( INDV_POLITICIAN, 'photo_url', array(
// 		'get_callback' => function(  $object, $field_name, $request ) {
// 			return get_post_meta( $object[ 'id' ], INDV_PHOTO_URL, true );
// 		},
// 		'update_callback' => function( $value, $object, $field_name ) {
// 			if ( ! $value || ! is_string( $value ) )
// 				return;
// 			return update_post_meta( $object->ID, INDV_PHOTO_URL, sanitize_url( $value ) );
// 		},
// 		'schema' => array(
// 				'description' => __( 'Photo URL', INDIVISIBLE_TEXT_DOMAIN ),
// 				'type'        => 'string'
// 		) )
// 	);
	
// // 	register_rest_field( INDV_LEGISLATION, 'bill_status', array(
// // 		'get_callback' => function(  $object, $field_name, $request ) {
// // 			return get_post_meta( $object[ 'id' ], INDV_BILL_STATUS, true );
// // 		},
// // 		'update_callback' => function( $value, $object, $field_name ) {
// // 			if ( ! $value || ! is_string( $value ) )
// // 				return;
// // 			return update_post_meta( $object->ID, INDV_BILL_STATUS, sanitize_text_field( $value ) );
// // 		},
// // 		'schema' => array(
// // 			'description' => __( 'Photo URL', INDIVISIBLE_TEXT_DOMAIN ),
// // 			'type'        => 'string'
// // 		) )
// // 	);
	
// 	foreach (array('full_name', 'first_name', 'middle_name', 'last_name', 'roles', 'email', 'url', 'offices') as $rest_field)
// 		register_rest_field( INDV_POLITICIAN, $rest_field, array(
// 			'get_callback' => function ($object, $field_name, $request) {
// 				global $indv;
// 				$post_slug = $object['slug'];
// 				$politician = strtoupper($post_slug);
// 				if (is_national($politician)) {
// 					$member = $indv->get_congress('members/' . $politician)[0];
// 					if ($member && isset($member[$field_name]))
// 						return $member[$field_name];
// 					if ($field_name == 'full_name') 
// 						return $member['first_name'] 
// 						. ($member['middle_name'] ? (' ' . $member['middle_name']) : '') . ' ' 
// 						. $member['last_name'] 
// 						. ($member['suffix'] ? (' ' . $member['suffix']) : '');
// 				} else {
// 					$url = OPEN_STATES_URL . 'legislators/' . $politician . '/';;
// 					$legislator = $indv->get_json($url);
// 					if ($legislator && isset($legislator[$field_name]))
// 						return $legislator[$field_name];
// 				}
// 			},
// 			'schema' => array(
// 				'description' => __( 'Reflect Open States', INDIVISIBLE_TEXT_DOMAIN ),
// 				'type'        => 'object'
// 			) )
// 		);
	
// 	foreach (array('calendar', 'history', 'votes') as $rest_field)
// 		register_rest_field( INDV_LEGISLATION, $rest_field, array(
// 			'get_callback' => function ($object, $field_name, $request) {
// 				global $indv;
// 				$bill = $indv->getLegiscanBill($object['id']);
// 				if ($bill && isset($bill[$field_name]))
// 					return $bill[$field_name];
// 			},
// 			'schema' => array(
// 					'description' => __( 'Reflect Legiscan', INDIVISIBLE_TEXT_DOMAIN ),
// 					'type'        => 'object'
// 			) )
// 		);
		
// 	foreach (array('sponsors', ) as $rest_field)
// 		register_rest_field( INDV_LEGISLATION, $rest_field, array(
// 			'get_callback' => function ($object, $field_name, $request) {
// 				global $indv;
// 				$bill = $indv->getOpenStatesBill($object['id']);
// 				if ($bill && isset($bill[$field_name]))
// 					return $bill[$field_name];
// 			},
// 			'schema' => array(
// 					'description' => __( 'Reflect Open States', INDIVISIBLE_TEXT_DOMAIN ),
// 					'type'        => 'object'
// 			) )
// 		);
		
// 	register_rest_route( 'indv/v1', '/autocomplete/politician', array(
// 		'methods' => 'GET',
// 		'callback' => 'indv_plugin_politician_directory',
// 	) );
// }

/* function indv_plugin_meta_boxes ($post_type) {
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
 */


// function indv_plugin_sortable_politician( $columns ) {
// 	$columns['votes'] = 'votes';
// 	return $columns;
// }

// function indv_plugin_columns_legislation ( $columns ) {
// 	$new_columns = array_merge(array_slice( $columns, 0, 2 ), array( 'identifier' => __('Identifier'), 'pposition' => __('Position') ), array_slice($columns, 3));
// 	return $new_columns;
// }

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

/* function indv_plugin_admin_style() {
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
 */
/* function indv_plugin_column ($column_name, $post_id ) {
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
 */
/* unction indv_plugin_meta_box ($post, $box) {
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
 */
/* function indv_plugin_preamble ($post)
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
 */
// function is_national ($id) {
// 	return ctype_digit(substr($id, 1, 1));
// }

/* function indv_plugin_save_post ( $post_id, $post, $update ) {
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
				preg_match ( '/(?:^|[^A-Z]+)([A-Z][A-Z])(?:\/.*\/|[^A-Z]|[\s,-\/]*)([A-Z]+(?:\s*)[\d]+)[\s,-\/]*(\d\d\d\d|)/',
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
 */
// function indv_plugin_add_help () {
// 	$screen = get_current_screen();
		
// 	$screen->add_help_tab( array(
// 			'id'	=> 'indv_plugin_help_tab',
// 			'title'	=> __('My Help Tab'),
// 			'content'	=> '<p>' . __( 'Descriptive content that will show in My Help Tab-body goes here.' ) . '</p>',
// 		) );
// }

 function indv_plugin_subtitle ($post) {
	global $indv;
	return $indv->plugin_subtitle($post);
}

///////////////////////////////////////////////////
///////////////////////////////////////////////////















// function indv_legislation_manage_sortable_columns( $columns ) {
// 	// 	$columns['position'] = 'position';
// 	return $columns;
// }


/* function indv_plugin_settings() {
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
 */
/* function indv_plugin_add_politician( $id, $user_id, $full_name, $lexicon, $photo_url) {
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
 */
// function indv_plugin_sanitize($input) {
// 	global $indv;
	
// 	$legislatures = get_option('legislatures');
// 	$states = get_option('states');
// 	$count = 0;
// 	$now = time();
	
// 	if (isset($legislatures['national'])) {
// 	    $members = array();
// 	    $senators = $indv->get_congress(CONGRESS_CURRENT . '/senate/members')[0]['members'];
// 	    $representatives = $indv->get_congress(CONGRESS_CURRENT . '/house/members')[0]['members'];
	    
// 	    foreach ($input as $state => $on) {
// 			foreach ($senators as $member)
// 				if ($member['state'] == $state)
// 					$members[] = $member;
// 			foreach ($representatives as $member)
// 				if ($member['state'] == $state)
// 					$members[] = $member;
// 	    }	
// 		foreach ($members as $member) {
// 			$role = $member['roles'][0];
// 			$politician = $member['id'];
// 			$lexicon = array (
// 					INDV_LEXICON_BIOGUIDE_ID => $politician,
// 			);
// 			if (isset($member['votesmart_id']))
// 				$lexicon[INDV_LEXICON_VOTE_SMART] = $member['votesmart_id'];
// 			if (isset($member['crp_id']))
// 				$lexicon[INDV_LEXICON_OPEN_SECRETS] = $member['crp_id'];
// 			if (isset($member['ballotpedia']))
// 				$lexicon[INDV_LEXICON_BALLOTPEDIA] = $member['ballotpedia'];
// 			if (isset($member['govtrack_id']))
// 				$lexicon[INDV_LEXICON_GOVTRACK] = $member['govtrack_id'];
// 			if (isset($role['fec_candidate_id']))
// 				$lexicon[INDV_LEXICON_FEC_ID] = $role['fec_candidate_id'];
				
// 			$photo_url = PUBLIC_STATIC_URL . 'theunitedstates/images/congress/450x550/' . $politician . '.jpg';
// 			$full_name = sanitize_text_field( $member['last_name'] . ', ' . $member['first_name'] . ' ' . $member['middle_name'] );
            
//             wp_schedule_single_event( $now + ++$count * 60, 'indv_plugin_politician_add', array(
//                 $politician,
//                 get_current_user_id(),
//                 $full_name,
//                 $lexicon,
//                 $photo_url
//             ));
// 		}
// 	}
		
// 	if (isset($legislatures['state'])) {
// 	    foreach ($input as $state => $on) {
// 	        $members = $indv->get_open_states('legislators/?state=' . $state);
// 			foreach ($members as $member) {
// 				$politician = $member['id'];
// 				$lexicon = array (
// 						INDV_LEXICON_OPEN_STATES => $politician,
// 				);
// 				if (isset($member['votesmart_id']))
// 					$lexicon[INDV_LEXICON_VOTE_SMART] = $member['votesmart_id'];
// 				if (isset($member['opensecrets_id']))
// 					$lexicon[INDV_LEXICON_OPEN_SECRETS] = $member['opensecrets_id'];
// 				if (isset($member['ballotpedia']))
// 					$lexicon[INDV_LEXICON_BALLOTPEDIA] = $member['ballotpedia'];
							
// 				$photo_url = sanitize_url($member['photo_url']);
// 				$full_name = sanitize_text_field( $member['last_name'] . ', ' . $member['first_name'] . ' ' . $member['middle_name'] );
				
// 				wp_schedule_single_event($now + ++$$count * 60, 'indv_plugin_politician_add', array(
// 				    $politician,
// 				    get_current_user_id(),
// 				    $full_name,
// 				    $lexicon,
// 				    $photo_url
// 				));
// 			}
// 		}
// 	}
	
// 	return $input;
// }

// function indv_plugin_menu() {
// 	add_options_page ( 'Indivisible', 'Indivisible', 'manage_options', 'indv_settings', 'indv_plugin_render_settings' );
// }

/* function indv_plugin_legislatures_render() {
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
        
        <button id="indv_plugin_ajax_button" class="button button-primary" onclick="console.log('set');">Ajax Button</button>
        <script>
        	jQuery(document).ready(function($) {
        		var data = {
        			'action': 'my_action',
        			'whatever': 1234
        		};
        		console.log("ready");
				jQuery("#indv_plugin_ajax_button").click(()=>{console.log("go")});
				// Create a new post
				var post = new wp.api.models.Post( { title: 'This is a test post' } );
				post.save();
				 
				// Load an existing post
				var post = new wp.api.models.Post( { id: 1 } );
				post.fetch();
				 // 				jQuery("#indv_plugin_ajax_button").css("color: red");
// 				jQuery("#indv_plugin_ajax_button").text("Button Pushed");
 //         		jQuery.post(ajaxurl, data, function(response) {
//         			alert('Got this from the server: ' + response);
//         		});
        	});
        </script>
	</div>
	<?php
}
 */
/* function indv_legislation_render_section(  ) {
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
 */
/* function indv_legislation_radio_render( ) {
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
 */
/* function indv_legislation_render_select( ) {
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
 */
/* function indv_legislation_render_user_key() {
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
 */





// function indv_plugin_politician_directory ($data) {
// 	global $wpdb;
	
// 	$query =
// 		"
// 		SELECT post_name, post_title
// 		FROM $wpdb->posts
// 		WHERE post_type = 'indv_politician'
// 			AND post_status = 'publish' 
// 		";
// 	$term = $data['term'];
// 	if ($term)	
// 		$query = $wpdb->prepare(
// 			$query .
// 				"
// 				AND post_title LIKE '%s'
// 				",
// 			'%' . $term . '%' );
	
// 	$politcians = $wpdb->get_results($query);
// 	$directory = array ();
// 	foreach ($politcians as $politician)
// 		$directory[] = $politician->post_title;
	
// 	return $directory;
// }

function indv_plugin_stock_photo ($post) {
	if ($post->post_type === Indv_Post::POLITICIAN)
		return get_post_meta($post->ID, Indv_Field::IMAGE, true);
	if ($post->post_type === Indv_Post::ACTION && isset($_REQUEST['referrer'])) {
		$politcian = absint( $_REQUEST['referrer'] );
		$photo_url = get_post_meta($politcian, Indv_Field::IMAGE, true);
		return $photo_url;
	}	
	return false;
}

function indv_plugin_get_actions ($post) {
	switch ($post->post_type) {
		case Indv_Post::POLITICIAN :
			$query = new WP_Query( array(
				'fields' => 'all',
				'post_type' => Indv_Post::ACTION,
				'politician' => $post->ID,
			) );
			if ($query->found_posts)
				return $query->posts;
			else
				return false;

		case Indv_Post::LEGISLATION :
			$query = new WP_Query( array(
				'post_type' => Indv_Post::ACTION,
				'fields' => 'all',
				'bill' => $post->ID,
				) );
			if ($query->found_posts)
				return $query->posts;
			else
				return false;

		case Indv_Post::ACTION :
			if (isset($_REQUEST['referrer'])) {
				$politcian = absint( $_REQUEST['referrer'] );
				$contact = get_post_meta($politcian, 'contact', true);
				return $contact;
			}
			else
				return false;
		}
		
	return false;
}

const OS_BILL = '
query bill($jurisdiction: String, $session: String, $bill: String) {
	bill(jurisdiction: $jurisdiction, session: $session, identifier: $bill) {
	  title
	  id
	  legislativeSession {
		identifier
		name
		classification
		startDate
		endDate
	  }
	  openstatesUrl
	  identifier
	  extras
	  classification
	  otherIdentifiers {
		identifier
		scheme
		note
	  }
	  abstracts {
		abstract
		note
		date
	  }
	  
	  sponsorships {
		name
		entityType
		primary
		classification
	  }
	  actions {
		description
		date
	  }
	  votes {
		edges {
		  node {
			counts {
			  value
			  option
			}
			votes {
			  voterName
			  voter {
				id
				contactDetails {
				  value
				  note
				  type
				}
			  }
			  option
			}
		  }
		}
	  }
	  sources {
		url
	  }
	  createdAt
	  updatedAt
	}
  }
  ';