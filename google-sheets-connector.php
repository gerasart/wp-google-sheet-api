<?php
/*
* Plugin Name: Google Sheets Connector
* Version: 1.3
* Plugin URI: https://svitsoft.com/
* Description: developer plugin.
* Author: Svitsoft
* Author URI: https://svitsoft.com/
*/

if ( !defined( 'ABSPATH' ) ) exit;

require_once dirname( __FILE__ ) . '/vendor/autoload.php';

define('BASE_DIR', wp_upload_dir()['basedir']);
define('CONNECTOR_URL',  plugin_dir_url( __FILE__ ));
define('CONNECTOR_PATH',  plugin_dir_path( __FILE__ ));

use HaydenPierce\ClassFinder\ClassFinder;

class GoogleSheetsConnector {

    static $plugin_dir;

    private static $basedir;

    public function __construct() {
        self::$plugin_dir = plugin_dir_path( __FILE__ );
        self::$basedir = plugin_dir_path( __FILE__ ) . '/inc/classes/';

        self::cc_autoload();
    }

    private static function cc_autoload() {
        foreach (glob(self::$basedir . '*.*') as $file) {
            include_once ( self::$basedir . basename($file) );
        }

        $namespaces = self::getDefinedNamespaces();
        foreach ($namespaces as $namespace => $path) {
            $clear = str_replace('\\', '', $namespace);

            ClassFinder::setAppRoot( self::$plugin_dir );
            $level = error_reporting(E_ERROR);
            $classes = ClassFinder::getClassesInNamespace( $clear );
            error_reporting($level);

            foreach ( $classes as $class ) {
                new $class();
            }
        }
    }

    private static function getDefinedNamespaces()
    {
        $composerJsonPath = dirname( __FILE__ ) . '/composer.json';
        $composerConfig = json_decode(file_get_contents($composerJsonPath));

        //Apparently PHP doesn't like hyphens, so we use variable variables instead.
        $psr4 = "psr-4";
        return (array) $composerConfig->autoload->$psr4;
    }
}

new GoogleSheetsConnector();


require 'vendor/yahnis-elsts/plugin-update-checker/plugin-update-checker.php';


$myUpdateChecker = Puc_v4_Factory::buildUpdateChecker(
    'https://github.com/gerasart/wp-google-sheet-api.git',
    __FILE__,
    'wp-google-sheet-api'
);
$myUpdateChecker->setAuthentication('6539d3f6b6df3db249de160e713aaa5301d80262');
$myUpdateChecker->setBranch('master');
$myUpdateChecker->getVcsApi()->enableReleaseAssets();