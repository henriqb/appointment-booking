<?php
namespace Bookly\Lib\Entities;

use Bookly\Lib;

/**
 * Class CustomerAppointment
 * @package Bookly\Lib\Entities
 */
class CustomerAppointment extends Lib\Entity
{

    protected static $table = 'ab_customer_appointments';

    protected static $schema = array(
        'id'                => array( 'format' => '%d' ),
        'customer_id'       => array( 'format' => '%d' ),
        'appointment_id'    => array( 'format' => '%d' ),
        'number_of_persons' => array( 'format' => '%d', 'default' => 1 ),
        'custom_fields'     => array( 'format' => '%s' ),
        'coupon_code'       => array( 'format' => '%s' ),
        'coupon_discount'   => array( 'format' => '%d' ),
        'coupon_deduction'  => array( 'format' => '%d' ),
        'token'             => array( 'format' => '%s' ),
        'time_zone_offset'  => array( 'format' => '%d' ),
        'locale'            => array( 'format' => '%s', 'default' => null ),
        'extras'            => array( 'format' => '%s' ),
    );

    /** @var Customer */
    public $customer = null;

    /**
     * Save entity to database.
     * Generate token before saving.
     *
     * @return int|false
     */
    public function save()
    {
        // Generate new token if it is not set.
        if ( $this->get( 'token' ) == '' ) {
            $test = new self();
            do {
                $token = md5( uniqid( time(), true ) );
            }
            while ( $test->loadBy( array( 'token' => $token ) ) === true );

            $this->set( 'token', $token );
        }
        $this->set( 'locale', apply_filters( 'wpml_current_language', null ) );

        return parent::save();
    }


    /**
     * Get array of custom fields with labels and values.
     *
     * @return array
     */
    public function getCustomFields()
    {
        $service_id = null;
        if ( get_option( 'ab_custom_fields_per_service' ) ) {
            $appointment = new Appointment();
            $appointment->load( $this->get( 'appointment_id' ) );
            $service_id = $appointment->get( 'service_id' );
        }
        $result = array();
        if ( $this->get( 'custom_fields' ) != '' ) {
            $custom_fields = array();
            foreach ( Lib\Utils\Common::getTranslatedCustomFields( $service_id ) as $field ) {
                $custom_fields[ $field->id ] = $field;
            }
            $data = json_decode( $this->get( 'custom_fields' ), true );
            if ( is_array( $data ) ) {
                foreach ( $data as $customer_custom_field ) {
                    if ( array_key_exists( $customer_custom_field['id'], $custom_fields ) ) {
                        $field = $custom_fields[ $customer_custom_field['id'] ];
                        $translated_value = array();
                        if ( array_key_exists( 'value', $customer_custom_field ) ) {
                            // Custom field have items ( radio group, etc. )
                            if ( property_exists( $field, 'items' ) ) {
                                foreach ( $field->items as $item ) {
                                    // Customer select many values ( checkbox )
                                    if ( is_array( $customer_custom_field['value'] ) ) {
                                        foreach ( $customer_custom_field['value'] as $field_value ) {
                                            if ( $item['value'] == $field_value ) {
                                                $translated_value[] = $item['label'];
                                            }
                                        }
                                    } elseif ( $item['value'] == $customer_custom_field['value'] ) {
                                        $translated_value[] = $item['label'];
                                    }
                                }
                            } else {
                                $translated_value[] = $customer_custom_field['value'];
                            }
                        }
                        $result[] = array(
                            'id'    => $customer_custom_field['id'],
                            'label' => $field->label,
                            'value' => implode( ', ', $translated_value )
                        );
                    }
                }
            }
        }

        return $result;
    }

    /**
     * Get formatted custom fields.
     *
     * @param string $format
     * @return string
     */
    public function getFormattedCustomFields( $format )
    {
        $result = '';
        switch ( $format ) {
            case 'html':
                foreach ( $this->getCustomFields() as $custom_field ) {
                    if ( $custom_field['value'] != '' ) {
                        $result .= sprintf(
                            '<tr valign=top><td>%s:&nbsp;</td><td>%s</td></tr>',
                            $custom_field['label'], $custom_field['value']
                        );
                    }
                }
                if ( $result != '' ) {
                    $result = "<table cellspacing=0 cellpadding=0 border=0>$result</table>";
                }
                break;

            case 'text':
                foreach ( $this->getCustomFields() as $custom_field ) {
                    if ( $custom_field['value'] != '' ) {
                        $result .= sprintf(
                            "%s: %s\n",
                            $custom_field['label'], $custom_field['value']
                        );
                    }
                }
                break;
        }

        return $result;
    }

    public function deleteCascade()
    {
        $appointment_id = $this->get( 'appointment_id' );
        parent::delete();
        $appointment = new Appointment();
        $appointment->load( $appointment_id );
        // Check exist customer appointments with current appointment_id.
        if ( CustomerAppointment::query()->where( 'appointment_id', $appointment_id )->count() == 0 ) {
            $appointment->delete();
        } else {
            $extras_duration = $appointment->getMaxCustomersExtrasDuration();
            if ( $extras_duration != $appointment->get( 'extras_duration' ) ) {
                $appointment->set( 'extras_duration', $extras_duration );
                $appointment->save();
            }
            $appointment->handleGoogleCalendar();
        }
    }

}