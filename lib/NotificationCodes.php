<?php
namespace Bookly\Lib;

/**
 * Class NotificationCodes
 * @package Bookly\Lib
 */
class NotificationCodes
{
    /**
     * Source data for all replacements.
     * @var array
     */
    private $data = array(
        'appointment_start'  => '',
        'appointment_end'    => '',
        'appointment_token'  => '',
        'client_email'       => '',
        'client_name'        => '',
        'client_phone'       => '',
        'custom_fields'      => '',
        'custom_fields_2c'   => '',
        'service_name'       => '',
        'service_price'      => '',
        'service_info'       => '',
        'staff_email'        => '',
        'staff_name'         => '',
        'staff_phone'        => '',
        'staff_photo'        => '',
        'staff_info'         => '',
        'category_name'      => '',
        'next_day_agenda'    => '',
        'number_of_persons'  => '',
        'site_address'       => '',
        'new_username'       => '',
        'new_password'       => '',
    );

    /**
     * Set data parameter.
     *
     * @param string $name
     * @param mixed $value
     */
    public function set( $name, $value )
    {
        $this->data[ $name ] = $value;
    }

    /**
     * Get data parameter.
     *
     * @param string $name
     * @return mixed
     */
    public function get( $name )
    {
        return $this->data[ $name ];
    }

    /**
     * Do replacements.
     *
     * @param $text
     * @param string $gateway
     * @return string
     */
    public function replace( $text, $gateway = 'email' )
    {
        $html_content = false;
        $company_logo = '';
        $staff_photo  = '';
        // Cancel appointment URL and <a> tag.
        $cancel_appointment = $cancel_appointment_url = admin_url( 'admin-ajax.php?action=ab_cancel_appointment&token=' . $this->get( 'appointment_token' ) );
        if ( $gateway == 'email' && get_option( 'ab_email_content_type', 'html' ) == 'html' ) {
            $html_content = true;
            if ( get_option( 'ab_settings_company_logo_url' ) != '' ) {
                // Company logo as <img> tag.
                $company_logo = sprintf(
                    '<img src="%s" alt="%s" />',
                    esc_attr( get_option( 'ab_settings_company_logo_url' ) ),
                    esc_attr( get_option( 'ab_settings_company_name' ) )
                );
            }
            if ( $this->data['staff_photo'] != '' ) {
                // Staff photo as <img> tag.
                $staff_photo = sprintf(
                    '<img src="%s" alt="%s" />',
                    esc_attr( $this->get( 'staff_photo' ) ),
                    esc_attr( $this->get( 'staff_name' ) )
                );
            }
            $cancel_appointment = sprintf( '<a href="%1$s">%1$s</a>', $cancel_appointment_url );
        }
        // Add to Google Calendar link.
        $google_calendar_url = sprintf( 'https://calendar.google.com/calendar/render?action=TEMPLATE&text=%s&dates=%s/%s&details=%s',
            urlencode( $this->get( 'service_name' ) ),
            date( 'Ymd\THis', strtotime( $this->get( 'appointment_start' ) ) ),
            date( 'Ymd\THis', strtotime( $this->get( 'appointment_end' ) ) ),
            urlencode( sprintf( "%s\n%s", $this->get( 'service_name' ), $this->get( 'staff_name' ) ) )
        );

        $service_price = $this->get( 'service_price' );

        // Codes.
        $codes = array(
            '[[APPOINTMENT_TIME]]'       => Utils\DateTime::formatTime( $this->get( 'appointment_start' ) ),
            '[[APPOINTMENT_DATE]]'       => Utils\DateTime::formatDate( $this->get( 'appointment_start' ) ),
            '[[CUSTOM_FIELDS]]'          => $this->get( 'custom_fields' ),
            '[[CUSTOM_FIELDS_2C]]'       => $html_content ? $this->get( 'custom_fields_2c' ) : $this->get( 'custom_fields' ),
            '[[CLIENT_NAME]]'            => $this->get( 'client_name' ),
            '[[CLIENT_PHONE]]'           => $this->get( 'client_phone' ),
            '[[CLIENT_EMAIL]]'           => $this->get( 'client_email' ),
            '[[GOOGLE_CALENDAR_URL]]'    => $google_calendar_url,
            '[[SERVICE_NAME]]'           => $this->get( 'service_name' ),
            '[[SERVICE_PRICE]]'          => Utils\Common::formatPrice( $service_price ),
            '[[SERVICE_INFO]]'           => $this->get( 'service_info' ),
            '[[STAFF_EMAIL]]'            => $this->get( 'staff_email' ),
            '[[STAFF_NAME]]'             => $this->get( 'staff_name' ),
            '[[STAFF_PHONE]]'            => $this->get( 'staff_phone' ),
            '[[STAFF_PHOTO]]'            => $staff_photo,
            '[[STAFF_INFO]]'             => $this->get( 'staff_info' ),
            '[[CANCEL_APPOINTMENT]]'     => $cancel_appointment,
            '[[CANCEL_APPOINTMENT_URL]]' => $cancel_appointment_url,
            '[[CATEGORY_NAME]]'          => $this->get( 'category_name' ),
            '[[COMPANY_ADDRESS]]'        => $html_content ? nl2br( get_option( 'ab_settings_company_address' ) ) : get_option( 'ab_settings_company_address' ),
            '[[COMPANY_LOGO]]'           => $company_logo,
            '[[COMPANY_NAME]]'           => get_option( 'ab_settings_company_name' ),
            '[[COMPANY_PHONE]]'          => get_option( 'ab_settings_company_phone' ),
            '[[COMPANY_WEBSITE]]'        => get_option( 'ab_settings_company_website' ),
            '[[NEXT_DAY_AGENDA]]'        => $this->get( 'next_day_agenda' ),
            '[[TOMORROW_DATE]]'          => Utils\DateTime::formatDate( $this->get( 'appointment_start' ) ),
            '[[TOTAL_PRICE]]'            => Utils\Common::formatPrice( $service_price * $this->get( 'number_of_persons' ) ),
            '[[NUMBER_OF_PERSONS]]'      => $this->get( 'number_of_persons' ),
            '[[SITE_ADDRESS]]'           => $this->get( 'site_address' ),
            '[[NEW_USERNAME]]'           => $this->get( 'new_username' ),
            '[[NEW_PASSWORD]]'           => $this->get( 'new_password' ),
        );
        $codes = apply_filters( 'bookly_replace_codes', $codes, $this );

        return strtr( $text, $codes );
    }

}