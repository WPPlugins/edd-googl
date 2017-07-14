<?php
/**
 * Product Goo.gl Shortlink Widget.
 *
 * Displays a product's Goo.gl shortlink in a widget.
 *
 * @since 1.0.1
 * @return void
 */

// Exit if accessed directly
if( !defined( 'ABSPATH' ) ) exit;

if( ! class_exists( 'EDD_Googl_Widget' ) ) {

    class EDD_Googl_Widget extends WP_Widget {

        /** Constructor */
        public function __construct() {
            parent::__construct(
                'edd_googl',
                sprintf( __( '%s Goo.gl Shortlink', 'edd-googl' ), edd_get_label_singular() ),
                array(
                    'description' => sprintf( __( 'Display the Goo.gl shortlink of a specific %s', 'edd-googl' ), edd_get_label_singular() ),
                )
            );
        }

        /** @see WP_Widget::widget */
        public function widget( $args, $instance ) {
            $args['id'] = ( isset( $args['id'] ) ) ? $args['id'] : 'edd_googl_widget';

            if ( ! empty( $instance['download_id'] ) ) {
                if ( 'current' === ( $instance['download_id'] ) ) {
                    $instance['display_type'] = 'current';
                    unset( $instance['download_id'] );
                } elseif ( is_numeric( $instance['download_id'] ) ) {
                    $instance['display_type'] = 'specific';
                }
            }

            if ( ! isset( $instance['display_type'] ) || ( 'specific' === $instance['display_type'] && ! isset( $instance['download_id'] ) ) || ( 'current' == $instance['display_type'] && ! is_singular( 'download' ) ) ) {
                return;
            }

            // set correct download ID.
            if ( 'current' == $instance['display_type'] && is_singular( 'download' ) ) {
                $download_id = get_the_ID();
            } else {
                $download_id = absint( $instance['download_id'] );
            }

            // Since we can take a typed in value, make sure it's a download we're looking for
            $download = get_post( $download_id );
            if ( ! is_object( $download ) || 'download' !== $download->post_type ) {
                return;
            }

            $shortlink = edd_googl_shortlink( $download_id );

            if( empty( $shortlink ) ) {
                return;
            }

            if( isset( $instance['show_protocol'] ) && $instance['show_protocol'] !== 'yes' ) {
                $shortlink = str_replace( 'https://', '', $shortlink );
            }

            // Variables from widget settings.
            $title           = apply_filters( 'widget_title', $instance['title'], $instance, $args['id'] );

            // Used by themes. Opens the widget.
            echo $args['before_widget'];

            // Display the widget title.
            if( $title ) {
                echo $args['before_title'] . $title . $args['after_title'];
            }

            do_action( 'edd_googl_widget_before_title' , $instance , $download_id );

            echo '<input type="text" value="' . esc_attr( trim( $shortlink ) ) . '" readonly/>';

            do_action( 'edd_googl_widget_before_end', $instance, $download_id );

            // Used by themes. Closes the widget.
            echo $args['after_widget'];
        }

        /** @see WP_Widget::form */
        public function form( $instance ) {
            // Set up some default widget settings.
            $defaults = array(
                'title'           => sprintf( __( '%s Shortlink', 'edd-googl' ), edd_get_label_singular() ),
                'display_type'    => 'current',
                'show_protocol'   => '',
                'download_id'     => false,
            );

            $instance = wp_parse_args( (array) $instance, $defaults ); ?>

            <?php
            if ( 'current' === ( $instance['download_id'] ) ) {
                $instance['display_type'] = 'current';
                $instance['download_id']  = false;
            } elseif ( is_numeric( $instance['download_id'] ) ) {
                $instance['display_type'] = 'specific';
            }

            ?>

            <!-- Title -->
            <p>
                <label for="<?php echo esc_attr( $this->get_field_id( 'title' ) ); ?>"><?php _e( 'Title:', 'edd-googl' ) ?></label>
                <input class="widefat" id="<?php echo esc_attr( $this->get_field_id( 'title' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'title' ) ); ?>" type="text" value="<?php echo $instance['title']; ?>" />
            </p>

            <p>
                <input type="checkbox" <?php checked( 'yes', $instance['show_protocol'], true ); ?> value="yes" name="<?php echo esc_attr( $this->get_field_name( 'show_protocol' ) ); ?>" id="<?php echo esc_attr( $this->get_field_id( 'show_protocol' ) ); ?>"><label for="<?php echo esc_attr( $this->get_field_id( 'show_protocol' ) ); ?>"><?php _e( 'Show protocol', 'edd-googl' ); ?></label>
            </p>

            <p>
                <?php _e( 'Display Type:', 'edd-googl' ); ?><br />
                <input type="radio" onchange="jQuery(this).parent().next('.download-shortlink-selector').hide();" <?php checked( 'current', $instance['display_type'], true ); ?> value="current" name="<?php echo esc_attr( $this->get_field_name( 'display_type' ) ); ?>" id="<?php echo esc_attr( $this->get_field_id( 'display_type' ) ); ?>-current"><label for="<?php echo esc_attr( $this->get_field_id( 'display_type' ) ); ?>-current"><?php _e( 'Current', 'edd-googl' ); ?></label>
                <input type="radio" onchange="jQuery(this).parent().next('.download-shortlink-selector').show();" <?php checked( 'specific', $instance['display_type'], true ); ?> value="specific" name="<?php echo esc_attr( $this->get_field_name( 'display_type' ) ); ?>" id="<?php echo esc_attr( $this->get_field_id( 'display_type' ) ); ?>-specific"><label for="<?php echo esc_attr( $this->get_field_id( 'display_type' ) ); ?>-specific"><?php _e( 'Specific', 'edd-googl' ); ?></label>
            </p>

            <!-- Download -->
            <?php $display = 'current' === $instance['display_type'] ? ' style="display: none;"' : ''; ?>
            <p class="download-shortlink-selector" <?php echo $display; ?>>
                <label for="<?php echo esc_attr( $this->get_field_id( 'download_id' ) ); ?>"><?php printf( __( '%s:', 'edd-googl' ), edd_get_label_singular() ); ?></label>
                <?php $download_count = wp_count_posts( 'download' ); ?>
                <?php if ( $download_count->publish < 1000 ) : ?>
                    <?php
                    $args = array(
                        'post_type'      => 'download',
                        'posts_per_page' => -1,
                        'post_status'    => 'publish',
                    );
                    $downloads = get_posts( $args );
                    ?>
                    <select class="widefat" name="<?php echo esc_attr( $this->get_field_name( 'download_id' ) ); ?>" id="<?php echo esc_attr( $this->get_field_id( 'download_id' ) ); ?>">
                        <?php foreach ( $downloads as $download ) { ?>
                            <option <?php selected( absint( $instance['download_id'] ), $download->ID ); ?> value="<?php echo esc_attr( $download->ID ); ?>"><?php echo $download->post_title; ?></option>
                        <?php } ?>
                    </select>
                <?php else: ?>
                    <br />
                    <input type="text" value="<?php echo esc_attr( $instance['download_id'] ); ?>" placeholder="<?php printf( __( '%s ID', 'edd-googl' ), edd_get_label_singular() ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'download_id' ) ); ?>" id="<?php echo esc_attr( $this->get_field_id( 'download_id' ) ); ?>">
                <?php endif; ?>
            </p>

            <?php do_action( 'edd_googl_widget_form' , $instance ); ?>
        <?php }

        /** @see WP_Widget::update */
        public function update( $new_instance, $old_instance ) {
            $instance = $old_instance;

            $instance['title']           = strip_tags( $new_instance['title'] );
            $instance['download_id']     = strip_tags( $new_instance['download_id'] );
            $instance['display_type']    = isset( $new_instance['display_type'] )    ? strip_tags( $new_instance['display_type'] ) : '';
            $instance['show_protocol']   = isset( $new_instance['show_protocol'] )   ? 'yes' : 'no';

            do_action( 'edd_googl_widget_update', $instance );

            // If the new view is 'current download' then remove the specific download ID
            if ( 'current' === $instance['display_type'] ) {
                unset( $instance['download_id'] );
            }

            return $instance;
        }

    }

}