<?php
namespace Bookly\Lib;

/**
 * Class Validator
 * @package Bookly\Lib
 */
class Validator
{
    private $errors = array();

    /**
     * @param $field
     * @param $data
     */
    public function validateEmail( $field, $data )
    {
        if ( $data['email'] ) {
            if ( ! is_email( $data['email'] ) ) {
                $this->errors[ $field ] = __( 'Invalid email', 'bookly' );
            }
            // Check email for uniqueness when a new WP account will be created.
            if ( get_option( 'ab_settings_create_account', 0 ) && $data['name'] && ! get_current_user_id() ) {
                $wp_user = Entities\Customer::query( 'c' )->where( 'c.name', $data['name'] )->where( 'c.email', $data['email'] )->fetchRow();
                if ( $wp_user == null && email_exists( $data['email'] ) ) {
                    $this->errors[ $field ] = __( 'This email is already in use', 'bookly' );
                }
            }
        } else {
            $this->errors[ $field ] = Utils\Common::getTranslatedOption( 'ab_appearance_text_required_email' );
        }
    }

    /**
     * @param $field
     * @param $phone
     * @param bool $required
     */
    public function validatePhone( $field, $phone, $required = false )
    {
        if ( empty( $phone ) && $required ) {
            $this->errors[ $field ] = Utils\Common::getTranslatedOption( 'ab_appearance_text_required_phone' );
        }
    }

    /**
     * @param $field
     * @param $string
     * @param $max_length
     * @param bool $required
     * @param bool $is_name
     * @param int $min_length
     */
    public function validateString( $field, $string, $max_length, $required = false, $is_name = false, $min_length = 0 )
    {
        if ( $string ) {
            if ( strlen( $string ) > $max_length ) {
                $this->errors[ $field ] = sprintf(
                    __( '"%s" is too long (%d characters max).', 'bookly' ),
                    $string,
                    $max_length
                );
            } elseif ( $min_length > strlen( $string ) ) {
                $this->errors[ $field ] = sprintf(
                    __( '"%s" is too short (%d characters min).', 'bookly' ),
                    $string,
                    $min_length
                );
            }
        } elseif ( $required && $is_name ) {
            $this->errors[ $field ] =  Utils\Common::getTranslatedOption( 'ab_appearance_text_required_name' );
        } elseif ( $required ) {
            $this->errors[ $field ] = __( 'Required', 'bookly' );
        }
    }

    /**
     * @param $field
     * @param $number
     * @param bool $required
     */
    public function validateNumber( $field, $number, $required = false )
    {
        if ( $number ) {
            if ( ! is_numeric( $number ) ) {
                $this->errors[ $field ] = __( 'Invalid number', 'bookly' );
            }
        } elseif ( $required ) {
            $this->errors[ $field ] = __( 'Required', 'bookly' );
        }
    }

    /**
     * @param $field
     * @param $start_time
     * @param $end_time
     */
    public function validateTimeGt( $field, $start_time, $end_time )
    {
        if ( Utils\DateTime::timeToSeconds( $start_time ) >= Utils\DateTime::timeToSeconds( $end_time ) ) {
            $this->errors[ $field ] = __( 'The start time must be less than the end time', 'bookly' );
        }
    }

    /**
     * @param $field
     * @param $datetime
     * @param bool $required
     */
    public function validateDateTime( $field, $datetime, $required = false )
    {
        if ( $datetime ) {
            if ( date_create( $datetime ) === false ) {
                $this->errors[ $field ] = __( 'Invalid date or time', 'bookly' );
            }
        } elseif ( $required ) {
            $this->errors[ $field ] = __( 'Required', 'bookly' );
        }
    }

    /**
     * @param $value
     * @param $form_id
     * @param $service_id
     */
    public function validateCustomFields( $value, $form_id, $service_id )
    {
        $fields = array();
        $cf_per_service = get_option( 'ab_custom_fields_per_service' );
        foreach ( json_decode( get_option( 'ab_custom_fields' ) ) as $field ) {
            if ( !$cf_per_service || in_array( $service_id, $field->services ) ) {
                $fields[ $field->id ] = $field;
            }
        }

        $custom_fields = array();
        foreach ( json_decode( $value ) as $field ) {
            if ( isset( $fields[ $field->id ] ) ) {
                if ( ( $fields[ $field->id ]->type == 'captcha' ) && ! Captcha\Captcha::validate( $form_id, $field->value ) ) {
                    $this->errors['custom_fields'][ $field->id ] = __( 'Incorrect code', 'bookly' );
                } elseif ( $fields[ $field->id ]->required && empty ( $field->value ) && $field->value != '0' ) {
                    $this->errors['custom_fields'][ $field->id ] = __( 'Required', 'bookly' );
                } else {
                    $custom_fields[ $field->id ] = $field;
                }
            }
        }

        // find the missing fields
        foreach ( array_diff_key( $fields, $custom_fields ) as $missing_field ) {
            if ( $missing_field->required ) {
                $this->errors['custom_fields'][ $missing_field->id ] = __( 'Required', 'bookly' );
            }
        }

        // TODO extra fields in request
        foreach ( array_diff_key( $custom_fields, $fields ) as $extra_field ) {

        }
    }

    /**
     * @return array
     */
    public function getErrors()
    {
        return $this->errors;
    }

}