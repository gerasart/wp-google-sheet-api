<?php
/**
 * Created by PhpStorm.
 * User: gerasart
 * Date: 9/4/2019
 * Time: 1:03 PM
 */

namespace Connector;


use Connector\helpers\TemplateLoader;

class OptionPage {

    public function __construct() {
        add_action( 'admin_menu', array( __CLASS__, 'addAdminPage' ), 12 );
    }

    /**
     * Register admin page
     */
    public static function addAdminPage() {
        add_menu_page( 'Google Api', 'Google Api', 'edit_posts', 'google-api',
            array( __CLASS__, 'pageInner' ), 'dashicons-googleplus' );
    }


    /**
     * Render vue-container and Data transfer
     * @throws \Google_Exception
     */
    public static function pageInner() {
        $options = get_option( GoogleSheetsApi::$optionName );
        $args = [
            'page_title' => get_admin_page_title(),
            'authUrl'    => GoogleSheetsApi::getClientAuthUrl(),
            'token'      => GoogleSheetsApi::$tokenStatus,
            'sheetId'    => isset( $options['sheetId'] ) ? $options['sheetId'] : '',
            'tabName'    => isset( $options['tabName'] ) ? $options['tabName'] : '',
            'columns'    => isset( $options['columns'] ) ? $options['columns'] : '',
        ];

        wp_enqueue_script( 'vuescript' );

        TemplateLoader::localizeArgs( $args, 'connector' );

        echo "<div data-vue='google-api'></div>";

    }
}