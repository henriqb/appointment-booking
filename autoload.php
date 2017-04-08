<?php if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

/**
 * Bookly autoload.
 */
spl_autoload_register( function ( $class ) {
    if ( preg_match( '/^Bookly\\\\(.+)?([^\\\\]+)$/U', ltrim( $class, '\\' ), $match ) ) {
        $file = AB_PATH . DIRECTORY_SEPARATOR
            . strtolower( str_replace( '\\', DIRECTORY_SEPARATOR, preg_replace('/([a-z])([A-Z])/', '$1_$2', $match[1] ) ) )
            . $match[2]
            . '.php';
        if ( is_readable( $file ) ) {
            require_once $file;
        }
    }
}, true, true );