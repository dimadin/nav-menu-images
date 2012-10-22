<?php
/**
 * Nav Menu Images Admin Functions
 *
 * @package Nav Menu Images
 * @subpackage Admin Functions
 */

/* Exit if accessed directly */
if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * Nav Menu Images admin functions.
 *
 * @since 1.0
 *
 * @uses Nav_Menu_Images
 */
class Nav_Menu_Images_Admin extends Nav_Menu_Images {
	/**
	 * Sets class properties.
	 * 
	 * @since 1.0
	 * @access public
	 *
	 * @uses add_filter() To hook filters.
	 * @uses add_action() To hook function.
	 */
	public function __construct() {
		// Register walker replacement
		add_filter( 'wp_edit_nav_menu_walker', array( &$this, 'filter_walker' ) );

		// Register enqueuing of scripts
		add_action( 'admin_menu', array( &$this, 'register_enqueuing' ) );
	}

	/**
	 * Register script enqueuing on nav menu page.
	 * 
	 * @since 1.0
	 * @access public
	 *
	 * @uses add_action() To hook function.
	 */
	public function register_enqueuing() {
		add_action( 'admin_print_scripts-nav-menus.php', array( &$this, 'enqueue_scripts' ) );
	}

	/**
	 * Enqueue necessary scripts.
	 * 
	 * @since 1.0
	 * @access public
	 *
	 * @uses Nav_Menu_Images::load_textdomain() To load translations.
	 * @uses wp_enqueue_script() To enqueue script.
	 * @uses plugins_url() To get URL of the file.
	 * @uses wp_localize_script() To add script's variables.
	 * @uses wp_enqueue_style() To enqueue style.
	 */
	public function enqueue_scripts() {
		// Load translations
		$this->load_textdomain();

		wp_enqueue_script( 'nmi-scripts', plugins_url( 'nmi.js', __FILE__ ), array( 'media-upload', 'thickbox' ), '1', true );
		wp_localize_script( 'nmi-scripts', 'nmi_vars', array(
				'alert' => __( 'You need to set an image as a featured image to be able to use it as an menu item image', 'nmi' )
			)
		);
		wp_enqueue_style( 'thickbox' );
	}

	/**
	 * Use custom walker for nav menu edit.
	 * 
	 * @since 1.0
	 * @access public
	 *
	 * @uses Nav_Menu_Images::load_textdomain() To load translations.
	 *
	 * @param string $walker Name of used walker class.
	 */
	public function filter_walker( $walker ) {
		// Load translations
		$this->load_textdomain();

		require_once dirname( __FILE__ ) . '/walker.php';
		return 'NMI_Walker_Nav_Menu_Edit';
	}
}