<?php

/**
 * The Nav Menu Images Plugin
 *
 * Display image as a menu item content.
 *
 * @package Nav Menu Images
 * @subpackage Main
 */

/**
 * Plugin Name: Nav Menu Images
 * Plugin URI:  http://blog.milandinic.com/wordpress/plugins/nav-menu-images/
 * Description: Display image as a menu content.
 * Author:      Milan Dinić
 * Author URI:  http://blog.milandinic.com/
 * Version:     1.0
 * Text Domain: nmi
 * Domain Path: /languages/
 * License:     GPL
 */

/* Exit if accessed directly */
if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * Display image as a menu content.
 * 
 * @since 1.0
 */
class Nav_Menu_Images {
	/**
	 * Name of a plugin's file.
	 *
	 * @var $plugin_basename
	 * @since 1.0 
	 * @access protected
	 */
	protected $plugin_basename;

	/**
	 * Sets class properties.
	 * 
	 * @since 1.0
	 * @access public
	 *
	 * @uses add_action() To hook function.
	 * @uses plugin_basename() To get plugin's file name.
	 */
	public function __construct() {
		// Register init
		add_action( 'init', array( &$this, 'init' ) );

		// Get a basename
		$this->plugin_basename = plugin_basename( __FILE__ );
	}

	/**
	 * Register actions & filters on init.
	 * 
	 * @since 1.0
	 * @access public
	 *
	 * @uses add_post_type_support() To enable thumbs for nav menu.
	 * @uses is_admin() To see if it's admin area.
	 * @uses Nav_Menu_Images_Admin() To call admin functions.
	 * @uses add_action() To hook function.
	 * @uses apply_filters() Calls 'nmi_filter_menu_item_content' to
	 *                        overwrite menu item filter.
	 * @uses add_filter() To hook filters.
	 */
	public function init() {
		// Add thumbnail support to menus
		add_post_type_support( 'nav_menu_item', 'thumbnail' );

		// Load admin file
		if ( is_admin() ) {
			require_once dirname( __FILE__ ) . '/inc/admin.php';
			new Nav_Menu_Images_Admin();
		}

		// Register AJAX handler
		add_action( 'wp_ajax_nmi_added_thumbnail', array( &$this, 'ajax_added_thumbnail' ) );

		// Register menu item content filter if needed
		if ( apply_filters( 'nmi_filter_menu_item_content', true ) ) {
			add_filter( 'nav_menu_css_class',       array( &$this, 'register_menu_item_filter'   ), 15, 3 );
			add_filter( 'walker_nav_menu_start_el', array( &$this, 'deregister_menu_item_filter' ), 15, 2 );
		}

		// Register plugins action links filter
		add_filter( 'plugin_action_links_' .               $this->plugin_basename, array( $this, 'action_links' ) );
		add_filter( 'network_admin_plugin_action_links_' . $this->plugin_basename, array( $this, 'action_links' ) );
	}

	/**
	 * Load textdomain for internationalization.
	 *
	 * @since 1.0
	 * @access public
	 *
	 * @uses is_textdomain_loaded() To check if translation is loaded.
	 * @uses load_plugin_textdomain() To load translation file.
	 * @uses plugin_basename() To get plugin's file name.
	 */
	public function load_textdomain() {
		/* If translation isn't loaded, load it */
		if ( ! is_textdomain_loaded( 'nmi' ) )
			load_plugin_textdomain( 'nmi', false, dirname( plugin_basename( __FILE__ ) ) . '/languages' );
	}

	/**
	 * Return thumbnail's HTML after addition.
	 *
	 * @since 1.0
	 * @access public
	 *
	 * @uses absint() To get positive integer.
	 * @uses has_post_thumbnail() To check if item has thumb.
	 * @uses admin_url() To get URL of uploader.
	 * @uses esc_url() To escape URL.
	 * @uses add_query_arg() To append variables to URL.
	 * @uses get_the_post_thumbnail() To get item's thumb.
	 */
	public function ajax_added_thumbnail() {
		// Get submitted values
		$post_id      = isset( $_POST[ 'post_id' ]      ) ? absint( $_POST[ 'post_id' ]      ) : 0;
		$thumbnail_id = isset( $_POST[ 'thumbnail_id' ] ) ? absint( $_POST[ 'thumbnail_id' ] ) : 0;

		// If there aren't values, exit
		if ( 0 == $post_id || 0 == $thumbnail_id )
			die( '0' );

		// If there isn't featured image, exit
		if ( ! has_post_thumbnail( $post_id ) )
			die( '1' );

		// Form upload link
		$upload_url = admin_url( 'media-upload.php' );
		$query_args = array(
			'post_id'   => $post_id,
			'tab'       => 'gallery',
			'TB_iframe' => '1',
			'width'     => '640',
			'height'    => '425'
		);
		$upload_url = esc_url( add_query_arg( $query_args, $upload_url ) );

		// Item's featured image
		$post_thumbnail = get_the_post_thumbnail( $post_id, 'thumb' );

		// Full HTML
		$return_html = '<a href="' . $upload_url . '" class="thickbox add_media">' . $post_thumbnail . '</a>';

		die( $return_html );		
	}

	/**
	 * Display an image as menu item content.
	 *
	 * @since 1.0
	 * @access public
	 *
	 * @uses has_post_thumbnail() To check if item has thumb.
	 * @uses apply_filters() Calls 'nmi_menu_item_content' to
	 *                        filter outputted content.
	 * @uses get_the_post_thumbnail() To get item's thumb.
	 *
	 * @param string $content Item's content
	 * @param int $item_id Item's ID
	 * @return string $content Item's content
	 */
	public function menu_item_content( $content, $item_id ) {
		if ( has_post_thumbnail( $item_id ) )
			$content = apply_filters( 'nmi_menu_item_content', get_the_post_thumbnail( $item_id, 'full', array( 'alt' => $content, 'title' => $content ) ), $item_id, $content );

		return $content;
	}

	/**
	 * Register menu item content filter.
	 *
	 * @since 1.0
	 * @access public
	 *
	 * @uses has_post_thumbnail() To check if item has thumb.
	 * @uses add_filter() To hook filter.
	 *
	 * @param array $item_classes Item's classes
	 * @param object $item Menu item data object.
	 * @param object $args Item's arguments.
	 * @return array $item_classes Item's classes
	 */
	public function register_menu_item_filter( $item_classes, $item, $args ) {
		if ( has_post_thumbnail( $item->ID ) )
			add_filter( 'the_title', array( &$this, 'menu_item_content' ), 15, 2 );

		return $item_classes;
	}

	/**
	 * Deregister menu item content filter.
	 *
	 * @since 1.0
	 * @access public
	 *
	 * @uses remove_filter() To unhook filter.
	 *
	 * @param string $item_output Item's content
	 * @param object $item Menu item data object.
	 * @return string $item_output Item's content
	 */
	public function deregister_menu_item_filter( $item_output, $item ) {
		remove_filter( 'the_title', array( &$this, 'menu_item_content' ), 15, 3 );

		return $item_output;
	}

	/**
	 * Add action links to plugins page.
	 *
	 * @since 1.0
	 * @access public
	 *
	 * @uses Nav_Menu_Images::load_textdomain() To load translations.
	 *
	 * @param array $link Plugin's action links.
	 * @return array $link Plugin's action links.
	 */
	public function action_links( $links ) {
		// Load translations
		$this->load_textdomain();

		$links['donate'] = '<a href="http://blog.milandinic.com/donate/">' . __( 'Donate', 'nmi' ) . '</a>';
		return $links;
	}
}

/**
 * Initialize a plugin.
 *
 * Load class when all plugins are loaded
 * so that other plugins can overwrite it.
 *
 * @since 1.0
 *
 * @uses Nav_Menu_Images To initialize plugin.
 */
function nmi_instantiate() {
	new Nav_Menu_Images();
}
add_action( 'plugins_loaded', 'nmi_instantiate', 15 );