<?php
namespace Bookly\Lib\Utils;

class UpdateChecker extends PluginUpdateChecker_3_0
{
    public function requestInfo( $queryArgs = array() )
    {
        global $wp_version;
        $this->debugMode = false;
        return parent::requestInfo( array(
            'api'           => '1.0',
            'action'        => 'update',
            'plugin'        => 'bookly',
            'site'          => parse_url( site_url(), PHP_URL_HOST ),
            'versions'      => array( $this->getInstalledVersion(), 'wp' => $wp_version, 'ab' => get_option( 'ab_db_version' ) ),
            'purchase_code' => get_option( 'ab_envato_purchase_code' ),
        ) );
    }

}