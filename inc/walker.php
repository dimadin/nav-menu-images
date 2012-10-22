<?php
/**
 * Nav Menu Images Nav Menu Edit Walker
 *
 * @package Nav Menu Images
 * @subpackage Nav Menu Edit Walker
 */

/* Exit if accessed directly */
if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * Filter nav menu items on edit screen.
 *
 * @since 1.0
 *
 * @uses Walker_Nav_Menu_Edit
 */
class NMI_Walker_Nav_Menu_Edit extends Walker_Nav_Menu_Edit {
	/**
	 * @see Walker_Nav_Menu_Edit::start_el()
	 * @since 1.0
	 * @access public
	 *
	 * @uses Walker_Nav_Menu_Edit::start_el()
	 * @uses admin_url() To get URL of uploader.
	 * @uses esc_url() To escape URL.
	 * @uses add_query_arg() To append variables to URL.
	 * @uses esc_attr() To escape string.
	 * @uses has_post_thumbnail() To check if item has thumb.
	 * @uses get_the_post_thumbnail() To get item's thumb.
	 * @uses esc_html() To escape string.
	 *
	 * @param string $output Passed by reference. Used to append additional content.
	 * @param object $item Menu item data object.
	 * @param int $depth Depth of menu item. Used for padding.
	 * @param object $args
	 */
	public function start_el( &$output, $item, $depth, $args ) {
		// First, make item with standard class
		parent::start_el( $output, $item, $depth, $args );

		// Now add additional content
		$item_id = $item->ID;

		// Form upload link
		$upload_url = admin_url( 'media-upload.php' );
		$query_args = array(
			'post_id'   => $item_id,
			'tab'       => 'gallery',
			'TB_iframe' => '1',
			'width'     => '640',
			'height'    => '425'
		);
		$upload_url = esc_url( add_query_arg( $query_args, $upload_url ) );

		// Hidden field with item's ID
		$output .= '<input type="hidden" name="nmi_item_id" id="nmi_item_id" value="' . esc_attr( $item_id ) . '" />';

		// Generate menu item image & link's text string
		if ( has_post_thumbnail( $item_id ) ) {
			$post_thumbnail = get_the_post_thumbnail( $item_id, 'thumb' );
			$output .= '<div class="nmi-current-image" style="display: none;"><a href="' . $upload_url . '" class="thickbox add_media">' . $post_thumbnail . '</a></div>';
			$link_text = __( 'Change menu item image', 'nmi' );
		} else {
			$output .= '<div class="nmi-current-image" style="display: none;"></div>';
			$link_text = __( 'Upload menu item image', 'nmi' );
		}

		// Generate menu item upload link
		$output .= '<div class="nmi-upload-link" style="display: none;"><a href="' . $upload_url . '" class="thickbox add_media">' . esc_html( $link_text ) . '</a></div>';
	}
}