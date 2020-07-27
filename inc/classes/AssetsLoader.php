<?php
/**
 * Created by PhpStorm.
 * User: skipin
 * Date: 06.11.18
 * Time: 9:40
 */

namespace Connector;

class AssetsLoader {

    public function __construct() {

        add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_front' ) );
        add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_admin' ) );

        self::registerWhoops();
    }

    public static function registerWhoops() {
        if ( WP_DEBUG && class_exists( 'Whoops\\Run' ) ) {
            $whoops = new \Whoops\Run;
            $whoops->appendHandler( new \Whoops\Handler\PrettyPageHandler );
            $whoops->register();
        }
    }

    public function enqueue_admin() {
        $this->enqueue( 'admin', false, [] );
        $this->enqueue( 'vue', false, [], false );
    }

    public function enqueue_front() {
        $this->enqueue( 'front-*' );
    }

    public function enqueue( $mask = '*', $in_footer = false, $depends = [], $enq = true ) {
        $styles_url = CONNECTOR_URL . 'dist/styles/';
        $scripts_url = CONNECTOR_URL . 'dist/scripts/';

            foreach ( glob( CONNECTOR_PATH . 'dist/styles/' . $mask . '.css' ) as $file ) {
            /* Enqueue CSS */
            $name = $this->get_filename( $file );
            if ( $name[1] !== 'map' ) {
                wp_register_style( $name[0], $styles_url . $name[1], $depends, '1', 'all' );
                wp_enqueue_style( $name[0] );
            }
        }

        foreach ( glob( CONNECTOR_PATH . 'dist/scripts/' . $mask . '.js' ) as $file ) {
            /* Enqueue Scripts */
            $name = $this->get_filename( $file );
            wp_register_script( $name[0], $scripts_url . $name[1], array( 'jquery' ), null, true );
            wp_enqueue_script( $name[0] );
        }
    }

    public function get_filename( $file ) {
        $basename = basename( $file );
        $exp = explode( '.', $basename );
        array_pop( $exp );

        $parts = array();
        $parts[] = implode( '.', $exp );
        $parts[] = $basename;
        return $parts;
    }
}