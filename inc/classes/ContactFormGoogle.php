<?php
/**
 * Created by PhpStorm.
 * User: gerasart
 * Date: 9/12/2019
 * Time: 5:18 PM
 */

namespace Connector;

class ContactFormGoogle {


    public function __construct() {
        //self::registerWhoops();
        add_action( 'wpcf7_before_send_mail', array( __CLASS__, 'setFormData' ) );
    }

    public static function registerWhoops() {
        if ( WP_DEBUG && class_exists( 'Whoops\\Run' ) ) {
            $whoops = new \Whoops\Run;
            $whoops->prependHandler( new \Whoops\Handler\PrettyPageHandler );
            $whoops->register();
        }
    }

    /**
     * @param $wpcf7
     * @throws \Google\Spreadsheet\Exception\SpreadsheetNotFoundException
     * @throws \Google\Spreadsheet\Exception\WorksheetNotFoundException
     * @throws \Google_Exception
     */
    public static function setFormData( $wpcf7 ) {
        $option = get_option( 'connectorData' );
        $check_form = '';

        if ( isset( $option['form_id'] ) ) {
            $check_form = explode( ',', $option['form_id'] );
        }

        if ( !in_array( (string)$wpcf7->id, $check_form ) ) {
            return;
        }

        $submission = \WPCF7_Submission::get_instance();

        if ( $submission ) {
            $submited = array();
            $submited['posted_data'] = $submission->get_posted_data();

            if ( $option['columns'] ) {
                foreach ( $option['columns'] as $column ) {
                    $value = $column['value'];
                    $post_data = str_replace( '+', '', $submited['posted_data'][ $value ] );
                    $data[ $column['key'] ] = $value ? $post_data : '';
                }
            }

            $entries = GoogleSheetsApi::getAll();
            $data['id'] = count( $entries ) ? count( $entries ) + 1 : 1;
            $data['reportAboutRestoration'] = '';
            GoogleSheetsApi::addEntry( $data );
        }
    }
}