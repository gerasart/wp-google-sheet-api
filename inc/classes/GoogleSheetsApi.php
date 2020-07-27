<?php
/**
 * Created by PhpStorm.
 * User: skipin
 * Date: 26.10.18
 * Time: 15:21
 */

namespace Connector;

use Connector\helpers\AjaxHelper;

use Google\Spreadsheet\ListFeed;
use Google\Spreadsheet\Spreadsheet;
use Google\Spreadsheet\Worksheet;
use Google\Spreadsheet\DefaultServiceRequest;
use Google\Spreadsheet\ServiceRequestFactory;
use Google\Spreadsheet\SpreadsheetService;

use Google_Client;


class GoogleSheetsApi {
    use AjaxHelper;

    static $spreadsheetUrl;
    static $spreadsheet;
    static $worksheetFeed;
    static $worksheet;

    static $service;

    static $doctrine;
    static $repository;

    static $tokenPath;

    static $tokenStatus = false;
    static $optionName = 'connectorData';


    public function __construct() {

        self::$tokenPath = BASE_DIR . '/google-token/token.json';

        self::declaration_ajax();
    }

    /**
     * Save admin options
     * @throws \Google_Exception
     */
    public static function ajax_saveGoogleConnectorData() {
        $fields = self::getPostVar( 'fields' );

        if ( $fields['token'] && !empty( $fields['token'] ) ) {
            self::createTokenFile( $fields['token'] );
        }

        if ( $fields['sheetId'] && !empty( $fields['sheetId'] || !empty($fields['columns']) ) ) {
            update_option( self::$optionName, $fields );
        }

        wp_send_json_success($fields);
    }


    /**
     * Create Token file
     * @param $token
     * @return bool
     * @throws \Google_Exception
     */
    private static function createTokenFile( $token ) {
        $authCode = trim( $token );
        $client = self::getApiClient();
        $accessToken = $client->fetchAccessTokenWithAuthCode( $authCode );

        // Check to see if there was an error.
        if ( array_key_exists( 'error', $accessToken ) ) {
            return false;
        }

        $client->setAccessToken( $accessToken );

        if ( !file_exists( dirname( self::$tokenPath ) ) ) {
            mkdir( dirname( self::$tokenPath ), 0700, true );
        }

        file_put_contents( self::$tokenPath, json_encode( $client->getAccessToken() ) );

        return true;
    }


    /**
     * Get first entry from table
     * @return array
     * @throws \Google\Spreadsheet\Exception\SpreadsheetNotFoundException
     * @throws \Google\Spreadsheet\Exception\WorksheetNotFoundException
     * @throws \Google_Exception
     */
    public static function getFirstEntry() {
        $listFeed = self::getListFeed();
        $entries = $listFeed->getEntries();
        $listEntry = $entries[0];

        $values = $listEntry->getValues();

        return $values;
    }


    /**
     * Get all entry from table
     * @return array
     * @throws \Google\Spreadsheet\Exception\SpreadsheetNotFoundException
     * @throws \Google\Spreadsheet\Exception\WorksheetNotFoundException
     * @throws \Google_Exception
     */
    public static function getAll() {
        $listFeed = self::getListFeed();
        $entries = $listFeed->getEntries();

        $rows = [];
        foreach ( $entries as $entry ) {
            $rows[] = $entry->getValues();
        }

        return $rows;
    }


    /**
     * Add entry to Sheet
     * @param $data
     * @throws \Google\Spreadsheet\Exception\SpreadsheetNotFoundException
     * @throws \Google\Spreadsheet\Exception\WorksheetNotFoundException
     * @throws \Google_Exception
     */
    public static function addEntry( $data ) {
        $listFeed = self::getListFeed( $data );

        if ( !$listFeed ) {
            $listFeed = self::createNewWorksheet( $data );
        }

        $row = self::formatData( $data );

        $listFeed->insert( $row );
    }

    /**
     * Format data before send to Google Sheet
     * @param $data
     * @return array
     */
        private static function formatData( $data ) {
        $boolean = [ 'true', 'false' ];

        $row = [];
        foreach ( $data as $key => $value ) {
            $name = strtolower( $key );
            if ( is_array( $value ) ) {
                $row[ $name ] = implode( ', ', $value );
            } elseif ( is_bool( $value ) ) {
                $row[ $name ] = $value ? 'Да' : 'Нет';
            } elseif ( in_array( $value, $boolean ) ) {
                $row[ $name ] = $value === 'true' ? 'Да' : 'Нет';
            } else {
                $row[ $name ] = $value;
            }
        }

        return $row;
    }


    /**
     * Get google ListFeed
     * @param array $data
     * @return bool|ListFeed
     * @throws \Google\Spreadsheet\Exception\SpreadsheetNotFoundException
     * @throws \Google\Spreadsheet\Exception\WorksheetNotFoundException
     * @throws \Google_Exception
     */
    private static function getListFeed( $data = [] ) {
        $options = get_option( self::$optionName );
        $spreadsheetId = (isset( $options['sheetId'] )) ? $options['sheetId'] : false;
        self::$spreadsheetUrl = self::createPrivateUrl( $spreadsheetId );
        $client = self::getApiClient();
        $token = $client->getAccessToken();

        self::$service = new \Google_Service_Sheets( $client );

        $serviceRequest = new DefaultServiceRequest( $token['access_token'] );
        ServiceRequestFactory::setInstance( $serviceRequest );

        $spreadsheetService = new SpreadsheetService();
        $spreadsheetFeed = $spreadsheetService->getSpreadsheetFeed();

        self::$spreadsheet = $spreadsheetFeed->getById( self::$spreadsheetUrl );
        self::$worksheetFeed = self::$spreadsheet->getWorksheetFeed();

        if ( isset( $options['tabName'] ) && !empty( $options['tabName'] ) ) {
            $worksheet = self::$worksheetFeed->getByTitle( $options['tabName'] );
        } else {
            $allWorksheet = self::$worksheetFeed->getEntries();

            if ( !empty( $allWorksheet ) ) {
                $worksheet = $allWorksheet[0];
            }
        }

        if ( isset( $worksheet ) ) {
            self::$worksheet = $worksheet;

            if ( !empty( $data ) ) {
                self::checkHeader( $data );
            }

            $listFeed = $worksheet->getListFeed();

            return $listFeed;
        } else {
            return false;
        }
    }

    /**
     * Check exists headers or create
     * @param $data
     */
    private static function checkHeader( $data ) {
        /** @var Worksheet $worksheet */
        $worksheet = self::$worksheet;
        $cellFeed = $worksheet->getCellFeed();
        $cell = $cellFeed->getCell( 1, 1 );

        if ( !isset( $cell ) ) {
            self::createHeaders( $data );
        }
    }

    /**
     * Create table headers
     * @param $data
     */
    private static function createHeaders( $data ) {
        /** @var Worksheet $worksheet */
        $worksheet = self::$worksheet;
        $cellFeed = $worksheet->getCellFeed();
        foreach ( array_keys( $data ) as $key => $value ) {
            $name = self::formatKeyToName( $value );
            $cellFeed->editCell( 1, $key + 1, $name );
        }
    }

    private static function formatKeyToName( $key ) {
        return ucwords( preg_replace( "/([A-Z])/", " $1", $key ) );
    }


    /**
     * Create new Worksheet & ListFeed
     * @param array $data
     * @param bool $name
     * @return ListFeed
     */
    public static function createNewWorksheet( $data = [], $name = false ) {
        /** @var Spreadsheet $spreadsheet */
        $spreadsheet = self::$spreadsheet;

        $tabName = $name ? $name : 'Tab' . rand( 0, 100 );
        $worksheet = $spreadsheet->addWorksheet( $tabName, 50, 20 );

        $cellFeed = $worksheet->getCellFeed();
        foreach ( array_keys( $data ) as $key => $value ) {
            $cellFeed->editCell( 1, $key + 1, $value );
        }

        return $worksheet->getListFeed();
    }

    private static function createPrivateUrl( $id ) {
        return 'https://spreadsheets.google.com/feeds/spreadsheets/private/full/' . $id;
    }

    /**
     * Get Google Api client
     * @return Google_Client
     * @throws \Google_Exception
     */
    private static function getApiClient() {
        $client = new \Google_Client();
        $client->setApplicationName( 'Google Sheets API PHP Quickstart' );

        $dir = dirname( __FILE__ );
        $client->setAuthConfig( $dir . '/libs/credentials.json' );
        $client->setScopes( [ \Google_Service_Sheets::SPREADSHEETS ] );

        $client->setAccessType( 'offline' );
//        $client->setAccessType( 'online' );
        $client->setPrompt( 'select_account consent' );

        if ( file_exists( self::$tokenPath ) ) {
            $accessToken = json_decode( file_get_contents( self::$tokenPath ), true );
            $client->setAccessToken( $accessToken );
            self::$tokenStatus = true;
        }

        // If there is no previous token or it's expired.
        if ( $client->isAccessTokenExpired() ) {
            // Refresh the token if possible, else fetch a new one.
            if ( $client->getRefreshToken() ) {
                $client->fetchAccessTokenWithRefreshToken( $client->getRefreshToken() );
                self::$tokenStatus = true;
            } else {
                self::$tokenStatus = false;
            }
        }

        return $client;
    }

    /**
     * Get client authorisation url
     * @return string
     * @throws \Google_Exception
     */
    public static function getClientAuthUrl() {
        $client = self::getApiClient();
        $authUrl = $client->createAuthUrl();

        return $authUrl;
    }

}
