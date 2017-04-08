<?php
namespace Bookly\Lib\Utils;

/**
 * Class Common
 * @package Bookly\Lib\Utils
 */
class Common
{
    /**
     * Get e-mails of wp-admins
     *
     * @return array
     */
    public static function getAdminEmails()
    {
        return array_map(
            create_function( '$a', 'return $a->data->user_email;' ),
            get_users( 'role=administrator' )
        );
    } // getAdminEmails

    /**
     * Generates email's headers FROM: Sender Name < Sender E-mail >
     *
     * @param array $extra
     * @return array
     */
    public static function getEmailHeaders( $extra = array() )
    {
        $headers = array();
        if ( get_option( 'ab_email_content_type' ) == 'plain' ) {
            $headers[] = 'Content-Type: text/plain; charset=utf-8';
        } else {
            $headers[] = 'Content-Type: text/html; charset=utf-8';
        }
        $headers[] = 'From: '. get_option( 'ab_settings_sender_name' ) . ' <' . get_option( 'ab_settings_sender_email' ) . '>';
        if ( isset ( $extra['reply-to'] ) ) {
            $headers[] = 'Reply-To: ' . $extra['reply-to']['name'] . ' <' . $extra['reply-to']['email'] . '>';
        }

        return $headers;
    }

    /**
     * Format price based on currency settings (Settings -> Payments).
     *
     * @param  string $price
     * @return string
     */
    public static function formatPrice( $price )
    {
        $price  = floatval( $price );
        switch ( get_option( 'ab_currency' ) ) {
            case 'AED' : return number_format_i18n( $price, 2 ) . ' AED';
            case 'AUD' : return 'A$' . number_format_i18n( $price, 2 );
            case 'BGN' : return number_format_i18n( $price, 2 ) . ' лв.';
            case 'BRL' : return 'R$ ' . number_format_i18n( $price, 2 );
            case 'CAD' : return 'C$' . number_format_i18n( $price, 2 );
            case 'CHF' : return number_format_i18n( $price, 2 ) . ' CHF';
            case 'CLP' : return 'CLP $' . number_format_i18n( $price, 2 );
            case 'COP' : return '$' . number_format_i18n( $price ) . ' COP';
            case 'CZK' : return number_format_i18n( $price, 2 ) . ' Kč';
            case 'DKK' : return number_format_i18n( $price, 2 ) . ' kr';
            case 'EGP' : return 'EGP ' . number_format_i18n( $price, 2 );
            case 'EUR' : return '€' . number_format_i18n( $price, 2 );
            case 'GBP' : return '£' . number_format_i18n( $price, 2 );
            case 'GTQ' : return 'Q' . number_format_i18n( $price, 2 );
            case 'HKD' : return number_format_i18n( $price, 2 ) . ' $';
            case 'HRK' : return number_format_i18n( $price, 2 ) . ' kn';
            case 'HUF' : return number_format_i18n( $price, 2 ) . ' Ft';
            case 'IDR' : return number_format_i18n( $price, 2 ) . ' Rp';
            case 'INR' : return number_format_i18n( $price, 2 ) . ' ₹';
            case 'ILS' : return number_format_i18n( $price, 2 ) . ' ₪';
            case 'ISK' : return number_format_i18n( $price ) . ' kr';
            case 'JPY' : return '¥' . number_format_i18n( $price, 2 );
            case 'KRW' : return number_format_i18n( $price, 2 ) . ' ₩';
            case 'KZT' : return number_format_i18n( $price, 2 ) . ' тг.';
            case 'MXN' : return number_format_i18n( $price, 2 ) . ' $';
            case 'MYR' : return number_format_i18n( $price, 2 ) . ' RM';
            case 'NAD' : return 'N$' . number_format_i18n( $price, 2 );
            case 'NGN' : return '₦' . number_format_i18n( $price, 2 );
            case 'NOK' : return number_format_i18n( $price, 2 ) . ' kr';
            case 'NZD' : return number_format_i18n( $price, 2 ) . ' $';
            case 'OMR' : return number_format_i18n( $price, 3 ) . ' OMR';
            case 'PEN' : return 'S/.' . number_format_i18n( $price, 2 );
            case 'PHP' : return number_format_i18n( $price, 2 ) . ' ₱';
            case 'PLN' : return number_format_i18n( $price, 2 ) . ' zł';
            case 'QAR' : return number_format_i18n( $price, 2 ) . ' QAR';
            case 'RON' : return number_format_i18n( $price, 2 ) . ' lei';
            case 'RMB' : return number_format_i18n( $price, 2 ) . ' ¥';
            case 'RUB' : return number_format_i18n( $price, 2 ) . ' руб.';
            case 'SAR' : return number_format_i18n( $price, 2 ) . ' SAR';
            case 'SEK' : return number_format_i18n( $price, 2 ) . ' kr';
            case 'SGD' : return number_format_i18n( $price, 2 ) . ' $';
            case 'THB' : return number_format_i18n( $price, 2 ) . ' ฿';
            case 'TRY' : return number_format_i18n( $price, 2 ) . ' TL';
            case 'TWD' : return number_format_i18n( $price, 2 ) . ' NT$';
            case 'UAH' : return number_format_i18n( $price, 2 ) . ' ₴';
            case 'UGX' : return 'UGX ' . number_format_i18n( $price );
            case 'USD' : return '$' . number_format_i18n( $price, 2 );
            case 'VND' : return number_format_i18n( $price ) . ' VNĐ';
            case 'XOF' : return 'CFA ' . number_format_i18n( $price, 2 );
            case 'ZAR' : return 'R ' . number_format_i18n( $price, 2 );
        }

        return number_format_i18n( $price, 2 );
    }

    /**
     * @return string
     */
    public static function getCurrentPageURL()
    {
        if ( ( ! empty( $_SERVER['HTTPS'] ) && $_SERVER['HTTPS'] !== 'off' ) || $_SERVER['SERVER_PORT'] == 443 ) {
            $url = 'https://';
        } else {
            $url = 'http://';
        }
        $url .= isset( $_SERVER['HTTP_X_FORWARDED_HOST'] ) ? $_SERVER['HTTP_X_FORWARDED_HOST'] : $_SERVER['HTTP_HOST'];

        return $url . $_SERVER['REQUEST_URI'];
    }

    /**
     * @return mixed|string|void
     */
    public static function getTimezoneString()
    {
        // if site timezone string exists, return it
        if ( $timezone = get_option( 'timezone_string' ) ) {
            return $timezone;
        }

        // get UTC offset, if it isn't set then return UTC
        if ( 0 === ( $utc_offset = get_option( 'gmt_offset', 0 ) ) ) {
            return 'UTC';
        }

        // adjust UTC offset from hours to seconds
        $utc_offset *= 3600;

        // attempt to guess the timezone string from the UTC offset
        if ( $timezone = timezone_name_from_abbr( '', $utc_offset, 0 ) ) {
            return $timezone;
        }

        // last try, guess timezone string manually
        $is_dst = date( 'I' );

        foreach ( timezone_abbreviations_list() as $abbr ) {
            foreach ( $abbr as $city ) {
                if ( $city['dst'] == $is_dst && $city['offset'] == $utc_offset )
                    return $city['timezone_id'];
            }
        }

        // fallback to UTC
        return 'UTC';
    }

    /**
     * Escape params for admin.php?page
     *
     * @param $page_slug
     * @param array $params
     * @return string
     */
    public static function escAdminUrl( $page_slug, $params = array() )
    {
        $path = 'admin.php?page=' . $page_slug;
        if ( ( $query = build_query( $params ) ) != '' ) {
            $path .= '&' . $query;
        }

        return esc_url( admin_url( $path ) );
    }

    /**
     * Build control for boolean option
     *
     * @param $option_name
     * @param array $options
     */
    public static function optionToggle( $option_name, array $options = array() )
    {
        $options = array_merge( array(
            'f' => array( 0, __( 'Disabled', 'bookly' ) ),
            't' => array( 1, __( 'Enabled',  'bookly' ) )
        ), $options );

        $control = sprintf( '<select class="form-control" name="%1$s" id="%1$s">', esc_attr( $option_name ) );
        foreach ( $options as $attr ) {
            $control .= sprintf( '<option value="%s" %s>%s</option>', esc_attr( $attr[0] ), selected( get_option( $option_name ), $attr[0], false ), $attr[1] );
        }
        echo $control . '</select>';
    }

    /**
     * Build control for multi values option
     *
     * @param $option_name
     * @param array $options
     */
    public static function optionFlags( $option_name, array $options = array() )
    {
        $options = array_merge( array(
            'f' => array( 0, __( 'Disabled', 'bookly' ) ),
            't' => array( 1, __( 'Enabled',  'bookly' ) )
        ), $options );
        $values = (array) get_option( $option_name );
        $control = '';
        foreach ( $options as $attr ) {
            $control .= sprintf( '<div class="checkbox"><label><input type="checkbox" name="%s[]" value="%s" %s>%s</label></div>', $option_name, esc_attr( $attr[0] ), checked( in_array( $attr[0], $values ), true, false ), $attr[1] );
        }
        echo $control;
    }

    /**
     * Build popover control
     *
     * @param $text
     * @param string $style
     * @param bool $echo
     * @return string
     */
    public static function popover( $text, $style = '', $echo = true )
    {
        $control = sprintf(
            '<img src="%s" alt="" class="ab-popover" data-content="%s" %s/>',
            esc_attr( plugins_url( 'backend/resources/images/help.png', AB_PATH . '/main.php' ) ),
            esc_attr( $text ),
            $style != '' ? 'style="' . esc_attr( $style ) . '" ' : ''
        );

        if ( $echo ) {
            echo $control;
        }

        return $control;
    }

    /**
     * Get option translated with WPML.
     *
     * @param $option_name
     * @return mixed|void
     */
    public static function getTranslatedOption( $option_name )
    {
        return self::getTranslatedString( $option_name, get_option( $option_name ) );
    }

    /**
     * Get string translated with WPML.
     *
     * @param $name
     * @param string $original_value
     * @param null|string $language_code       Return the translation in this language
     * @return mixed|void
     */
    public static function getTranslatedString( $name, $original_value = '', $language_code = null )
    {
        return apply_filters( 'wpml_translate_single_string', $original_value, 'bookly', $name, $language_code );
    }

    /**
     * Get translated custom fields
     *
     * @param integer $service_id
     * @param string $language_code       Return the translation in this language
     * @return array|mixed|object
     */
    public static function getTranslatedCustomFields( $service_id = null, $language_code = null )
    {
        $custom_fields  = json_decode( get_option( 'ab_custom_fields' ) );
        foreach ( $custom_fields as $key => $custom_field ) {
            if ( $service_id === null || in_array( $service_id, $custom_field->services ) ) {
                switch ( $custom_field->type ) {
                    case 'textarea':
                    case 'text-content':
                    case 'text-field':
                    case 'captcha':
                        $custom_field->label = self::getTranslatedString( 'custom_field_' . $custom_field->id . '_' . sanitize_title( $custom_field->label ), $custom_field->label, $language_code );
                        break;
                    case 'checkboxes':
                    case 'radio-buttons':
                    case 'drop-down':
                        $custom_field->label = self::getTranslatedString( 'custom_field_' . $custom_field->id . '_' . sanitize_title( $custom_field->label ), $custom_field->label, $language_code );
                        $items               = $custom_field->items;
                        foreach ( $items as $pos => $label ) {
                            $items[ $pos ] = array(
                                'value' => $label,
                                'label' => self::getTranslatedString( 'custom_field_' . $custom_field->id . '_' . sanitize_title( $custom_field->label ) . '=' . sanitize_title( $label ), $label, $language_code )
                            );
                        }
                        $custom_field->items = $items;
                        break;
                }
            } else {
                unset( $custom_fields[ $key ] );
            }
        }

        return $custom_fields;
    }

    /**
     * Check whether the current user is administrator or not.
     *
     * @return bool
     */
    public static function isCurrentUserAdmin()
    {
        return current_user_can( 'manage_options' );
    }

    /**
     * Submit button helper
     *
     * @param string $id
     * @param string $class
     */
    public static function submitButton( $id = '', $class = '' )
    {
        $html = sprintf(
            '<button %s type="submit" class="btn btn-info ladda-button %s" data-style="zoom-in" data-spinner-size="40"><span class="ladda-label">' . __( 'Save', 'bookly' ) . '</span></button>',
            $id != '' ? 'id="' . esc_attr( $id ) . '" ' : '',
            esc_attr( $class )
        );

        echo $html;
    }

    /**
     * Reset button helper
     *
     * @param string $id
     * @param string $class
     */
    public static function resetButton( $id = '', $class = '' )
    {
        $html = sprintf(
            '<button %s class="ab-reset-form %s" type="reset">' . __( 'Reset', 'bookly' ) . '</button>',
            $id != '' ? 'id="' . esc_attr( $id ) . '" ' : '',
            esc_attr( $class )
        );

        echo $html;
    }

    /**
     * Echo WP like notice
     *
     * @param $messages
     * @param string $class
     * @param bool|true $show
     */
    public static function notice( $messages, $class = 'notice-success', $show = true )
    {
        if ( ! empty ( $messages ) ) {
            $html = '<div class="%s ab-notice notice is-dismissible" %s><p>%s</p></div>';
            $text = is_array( $messages ) ? implode( '</p><p>', $messages ) : $messages;

            echo sprintf( $html, $class, $show ? '' : 'style="display:none"', $text );
        }
    }

    /**
     * Verify envato.com Purchase Code
     *
     * @param $purchase_code
     * @param $plugin
     * @return bool
     */
    public static function verifyPurchaseCode( $purchase_code, $plugin )
    {
        $options   = array(
            'timeout' => 10, //seconds
            'headers' => array(
                'Accept' => 'application/json'
            ),
        );
        $queryArgs = array(
            'api'           => '1.0',
            'action'        => 'verify-purchase-code',
            'plugin'        => $plugin,
            'purchase_code' => $purchase_code,
            'site'          => parse_url( site_url(), PHP_URL_HOST ),
        );
        $url = add_query_arg( $queryArgs, 'http://booking-wp-plugin.com/' );
        try {
            $response = wp_remote_get( $url, $options );
            if ( $response instanceof \WP_Error ) {

            } elseif ( isset( $response['body'] ) ) {
                $json = json_decode( $response['body'], true );
                if ( isset( $json['success'] ) ) {
                    return (bool) $json['success'];
                }
            }
        } catch ( \Exception $e ) {

        }

        return false;
    }

    /**
     * @param string $plugin Like 'bookly-addon-service-extras/main.php'
     *
     * @return bool
     */
    public static function isActivePlugin( $plugin )
    {
        // In MultiSite exist 2 methods activation plugin for site.
        return ( in_array(
                     $plugin,
                     apply_filters( 'active_plugins', get_option( 'active_plugins' ) ) )
                 || is_plugin_active_for_network( $plugin )
        );
    }

}