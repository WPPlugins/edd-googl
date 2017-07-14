<?php
/**
 * Widgets
 *
 * @package     EDD\Googl\Widgets
 * @since       1.0.1
 */

// Exit if accessed directly
if( !defined( 'ABSPATH' ) ) exit;

require_once EDD_GOOGL_DIR . 'widgets/edd-googl-widget.php';

/**
 * Register Widgets.
 *
 * @since 1.0.1
 * @return void
 */
function edd_googl_register_widgets() {
    register_widget( 'edd_googl_widget' );
}
add_action( 'widgets_init', 'edd_googl_register_widgets' );