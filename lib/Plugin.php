<?php
namespace Bookly\Lib;

/**
 * Class Plugin
 * @package Bookly\Lib
 */
abstract class Plugin
{
    protected static $version;

    public static function activate( $register_hook = true )
    {
        $installer = new \Bookly\Installer();
        $installer->install();
        $register_hook ? do_action( 'bookly_plugin_activate', 'bookly' ) : null;
    }

    public static function deactivate( $register_hook = true )
    {
        unload_textdomain( 'bookly' );
        $register_hook ? do_action( 'bookly_plugin_deactivate', 'bookly' ) : null;
    }

    public static function uninstall( $register_hook = true )
    {
        $installer = new \Bookly\Installer();
        $installer->uninstall();
        $register_hook ? do_action( 'bookly_plugin_uninstall', 'bookly' ) : null;
    }

    public static function version()
    {
        if ( self::$version == null ) {
            if ( ! function_exists( 'get_plugin_data' ) ) {
                require_once( ABSPATH . 'wp-admin/includes/plugin.php' );
            }
            $plugin_data = get_plugin_data( AB_PATH . '/main.php' );
            self::$version = $plugin_data['Version'];
        }

        return self::$version;
    }

    public static function registerHooks()
    {
        if ( is_admin() ) {
            register_activation_hook( AB_PATH . '/main.php',   array( __CLASS__, 'activate' ) );
            register_deactivation_hook( AB_PATH . '/main.php', array( __CLASS__, 'deactivate' ) );
            register_uninstall_hook( AB_PATH . '/main.php',    array( __CLASS__, 'uninstall' ) );
            if ( get_option( 'ab_envato_purchase_code' ) ) {
                add_filter( 'puc_manual_check_link-' . basename( AB_PATH ), function () { return __( 'Check for updates', 'bookly' ); } );
                add_filter( 'puc_manual_check_message-' . basename( AB_PATH ), function ( $message, $status ) {
                    switch ( $status ) {
                        case 'no_update':           return __( 'This plugin is up to date.', 'bookly' );
                        case 'update_available':    return __( 'A new version of this plugin is available.', 'bookly' );
                        default:                    return sprintf( __( 'Unknown update checker status "%s"', 'bookly' ), htmlentities( $status ) );
                    }
                }, 10, 2 );
                new Utils\UpdateChecker(
                    'http://booking-wp-plugin.com/index.php',
                    AB_PATH . '/main.php',
                    basename( AB_PATH ),
                    24
                );
            } else {
                add_filter( 'plugin_row_meta', function ( $links, $plugin ) {
                    if ( $plugin == 'appointment-booking/main.php' )
                        return array_merge( $links, array( 0 => '<span class="dashicons dashicons-info"></span> ' . sprintf( __( 'To update - enter the <a href="%s">Purchase Code</a>', 'bookly' ), Utils\Common::escAdminUrl( \Bookly\Backend\Modules\Settings\Controller::page_slug, array( 'tab' => 'purchase_code' ) ) ) ) );
                    return $links;
                }, 10, 2 );
            }
        }
        add_filter( 'bookly_plugins', function( array $plugins ) {
            $plugins['bookly'] = array( 'title' => 'Bookly', 'path' => 'appointment-booking/main.php', 'purchase_code' => 'ab_envato_purchase_code', 'Plugin' => '\Bookly\Lib\Plugin' );

            return $plugins;
        } );
    }

}