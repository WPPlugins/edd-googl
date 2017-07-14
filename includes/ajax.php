<?php
/**
 * Ajax
 *
 * @package     EDD\Googl\Ajax
 * @since       1.0.0
 */


// Exit if accessed directly
if( !defined( 'ABSPATH' ) ) exit;

/**
 * Ajax action to update all donwload googl url
 */
function edd_googl_update_all_ajax() {
    $args = array(
        'post_type' => array( 'download', 'edd_download_page' ),
        'post_status' => 'publish'
    );

    $query = new WP_Query($args);

    $posts = $query->get_posts();
    $response = array();

    foreach( $posts as $post ) {
        edd_googl()->update_post_shortlink( $post->ID );

        $response[ get_permalink( $post->ID ) ] = edd_googl()->get_shortlink( $post->ID );
    }

    wp_send_json( $response );
}
add_action( 'wp_ajax_edd_googl_update_all', 'edd_googl_update_all_ajax' );