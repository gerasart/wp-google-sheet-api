<?php
/**
 * Created by PhpStorm.
 * User: skipin
 * Date: 06.11.18
 * Time: 16:19
 */

namespace Connector\helpers;


class TemplateLoader {

    public static function render_template_part( $slug, $name = null, $args = array(), $echo = true, $rel_path = false ) {
        global $posts, $post, $wp_did_header, $wp_query, $wp_rewrite, $wpdb, $wp_version, $wp, $id, $comment, $user_ID;
        // If template name is omitted, shift parameters
        if ( is_array( $name ) ) {
            $echo = $args;
            $args = $name;
            $name = null;
        }
        do_action( "get_template_part_{$slug}", $slug, $name );
        do_action( "render_template_part_{$slug}", $slug, $name, $args, $echo );

        $args = apply_filters( "render_template_part_{$slug}_args", $args, $slug, $name, $echo );
        $templates = array();
        $template_path = false;

        $name = (string)$name;
        if ( '' !== $name )
            $templates[] = "{$slug}-{$name}.php";

        $templates[] = "{$slug}.php";

        $theme_file = locate_template( $templates );

        if ( !$theme_file ) {
            if ( $rel_path ) {
                $explode = explode( $rel_path, plugin_dir_path( __FILE__ ) );

                if ( !empty($explode) ) {
                    $plugin_dir = $explode[0];
                }
            } else {
                $plugin_dir = plugin_dir_path( __FILE__ ) ;
            }

            foreach ( $templates as $template ) {
                if ( file_exists( $plugin_dir . $template ) ) {
                    $template_path = $plugin_dir . $template;
                }
            }
        } else {
            $template_path = $theme_file;
        }

        foreach ( $templates as $template ) {
            if ( file_exists( $template ) ) {
                $template_path = $template;
            }
        }


        if ( !$template_path ) {
            return;
        }

        if ( is_array( $wp_query->query_vars ) ) {
            extract( $wp_query->query_vars, EXTR_SKIP );
        }
        if ( isset( $s ) ) {
            $s = esc_attr( $s );
        }
        if ( is_array( $args ) ) {
            extract( $args, EXTR_SKIP );
        }
        // If $query variable extracted, assume we need to set up as $wp_query
        if ( isset( $query ) ) {
            $wp_query = $query;
        }
        // If $post_object variable extracted, assume we need to set up as $post
        if ( isset( $post_object ) ) {
            $post = $post_object;
            setup_postdata( $post );
        }
        if ( false === $echo ) {
            ob_start();
            require( $template_path );
            $return = ob_get_clean();
        } else {
            require( $template_path );
        }
        if ( isset( $query ) ) {
            wp_reset_query();
        }

        if ( isset( $post_object ) ) {
            wp_reset_postdata();
        }
        if ( false === $echo ) {
            return $return;
        }
    }

    public static function localizeArgs( $args, $object = false, $handle = 'admin' ) {
        if ( $object ) {
            self::localize( $object, $args, $handle );
        } else {
            foreach ( $args as $name => $value ) {
                self::localize( $name, $value, $handle );
            }
        }
    }

    public static function localize( $name, $value, $handle = 'admin' ) {
        wp_localize_script( $handle, $name, $value );
    }
}
