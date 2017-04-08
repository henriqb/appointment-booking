<?php
namespace Bookly\Lib;

/**
 * Class NotificationSender
 * @package Bookly\Lib
 */
abstract class NotificationSender
{
    const INSTANT_NEW_APPOINTMENT       = 1;
    const INSTANT_CANCELLED_APPOINTMENT = 2;
    const CRON_NEXT_DAY_APPOINTMENT     = 3;
    const CRON_FOLLOW_UP_EMAIL          = 4;

    /**
     * @var SMS
     */
    static $sms = null;
    static $sms_authorized = null;

    /**
     * Send instant notifications.
     *
     * @param int $type  (E_NEW_APPOINTMENT|E_CANCELLED_APPOINTMENT)
     * @param Entities\CustomerAppointment $ca
     */
    public static function send( $type, Entities\CustomerAppointment $ca )
    {
        list ( $codes, $staff, $appointment, $customer ) = self::_prepareData( $ca );

        if ( get_option( 'ab_email_notification_reply_to_customers' ) ) {
            $extra = array( 'reply-to' => array(
                'email' => $customer->get( 'email' ),
                'name'  => $customer->get( 'name' )
            ) );
        } else {
            $extra = array();
        }

        switch ( $type ) {
            case self::INSTANT_NEW_APPOINTMENT:
                foreach ( array( 'email', 'sms' ) as $gateway ) {
                    $to_client = new Entities\Notification();
                    $to_client->loadBy( array( 'type' => 'client_new_appointment', 'gateway' => $gateway ) );

                    $to_staff = new Entities\Notification();
                    $to_staff->loadBy( array( 'type' => 'staff_new_appointment', 'gateway' => $gateway ) );

                    if ( $to_staff->get( 'active' ) ) {
                        // Send email notification to staff member (and admins if necessary).
                        self::_send( $to_staff, $codes, $staff->get( 'email' ), $staff->get( 'phone' ), $extra );
                    }

                    if ( $to_client->get( 'active' ) ) {
                        // Client time zone offset.
                        if ( $ca->get( 'time_zone_offset' ) !== null ) {
                            $codes->set( 'appointment_start', Utils\DateTime::applyTimeZoneOffset( $appointment->get( 'start_date' ), $ca->get( 'time_zone_offset' ) ) );
                        }
                        // Send email notification to client.
                        self::_send( $to_client, $codes, $customer->get( 'email' ), $customer->get( 'phone' ) );
                    }
                }
                break;

            case self::INSTANT_CANCELLED_APPOINTMENT:
                foreach ( array( 'email', 'sms' ) as $gateway ) {
                    $to_staff = new Entities\Notification();
                    $to_staff->loadBy( array( 'type' => 'staff_cancelled_appointment', 'gateway' => $gateway ) );
                    if ( $to_staff->get( 'active' ) ) {
                        // Send email notification to staff member (and admins if necessary).
                        self::_send( $to_staff, $codes, $staff->get( 'email' ), $staff->get( 'phone' ), $extra );
                    }
                }
                break;
        }
    }

    /**
     * Send scheduled notifications.
     *
     * @param int $type  (C_NEXT_DAY_APPOINTMENT|C_FOLLOW_UP_ACTION)
     * @param Entities\Notification $notification
     * @param Entities\CustomerAppointment $ca
     * @return bool
     */
    public static function sendFromCron( $type, Entities\Notification $notification, Entities\CustomerAppointment $ca )
    {
        $result = false;

        list ( $codes, $staff, $appointment, $customer ) = self::_prepareData( $ca );

        switch ( $type ) {
            case self::CRON_NEXT_DAY_APPOINTMENT:
            case self::CRON_FOLLOW_UP_EMAIL:
                // Client time zone offset.
                if ( $ca->get( 'time_zone_offset' ) !== null ) {
                    $codes->set( 'appointment_start', Utils\DateTime::applyTimeZoneOffset( $appointment->get( 'start_date' ), $ca->get( 'time_zone_offset' ) ) );
                }
                // Send email notification to client.
                $result = self::_send( $notification, $codes, $customer->get( 'email' ), $customer->get( 'phone' ), array(), $ca->get( 'locale' ) );
                break;
        }

        return $result;
    }

    /**
     * Send email with username and password for newly created WP user.
     *
     * @param Entities\Customer $customer
     * @param $username
     * @param $password
     */
    public static function sendEmailForNewUser( Entities\Customer $customer, $username, $password )
    {
        foreach ( array( 'email', 'sms' ) as $gateway ) {
            $to_client = new Entities\Notification();
            $to_client->loadBy( array( 'type' => 'client_new_wp_user', 'gateway' => $gateway ) );

            if ( $to_client->get( 'active' ) ) {
                $codes = new NotificationCodes();
                $codes->set( 'client_name',  $customer->get( 'name' ) );
                $codes->set( 'client_phone', $customer->get( 'phone' ) );
                $codes->set( 'client_email', $customer->get( 'email' ) );
                $codes->set( 'new_username', $username );
                $codes->set( 'new_password', $password );
                $codes->set( 'site_address', site_url() );

                self::_send( $to_client, $codes, $customer->get( 'email' ), $customer->get( 'phone' ) );
            }
        }
    }

    /**
     * Prepare data for email.
     *
     * @param Entities\CustomerAppointment $ca
     * @return array
     */
    private static function _prepareData( Entities\CustomerAppointment $ca )
    {
        global $sitepress;
        if ( $sitepress instanceof \SitePress ) {
            $sitepress->switch_lang( $ca->get( 'locale' ), true );
        }
        $appointment = new Entities\Appointment();
        $appointment->load( $ca->get( 'appointment_id' ) );

        $customer = new Entities\Customer();
        $customer->load( $ca->get( 'customer_id' ) );

        $staff = new Entities\Staff();
        $staff->load( $appointment->get( 'staff_id' ) );

        $service = new Entities\Service();
        $service->load( $appointment->get( 'service_id' ) );

        $staff_service = new Entities\StaffService();
        $staff_service->loadBy( array( 'staff_id' => $staff->get( 'id' ), 'service_id' => $service->get( 'id' ) ) );

        $price = $staff_service->get( 'price' );
        if ( $ca->get( 'coupon_discount' ) || $ca->get( 'coupon_deduction' ) ) {
            $coupon = new Entities\Coupon();
            $coupon->set( 'discount',  $ca->get( 'coupon_discount' ) );
            $coupon->set( 'deduction', $ca->get( 'coupon_deduction' ) );
            $price = round( $coupon->apply( $price * $ca->get( 'number_of_persons' ) ) / $ca->get( 'number_of_persons' ), 2 );
        }

        $codes = new NotificationCodes();
        $codes->set( 'appointment_start', $appointment->get( 'start_date' ) );
        $codes->set( 'appointment_end',   $appointment->get( 'end_date' ) );
        $codes->set( 'appointment_token', $ca->get( 'token' ) );
        $codes->set( 'category_name',     $service->getCategoryName() );
        $codes->set( 'client_name',       $customer->get( 'name' ) );
        $codes->set( 'client_phone',      $customer->get( 'phone' ) );
        $codes->set( 'client_email',      $customer->get( 'email' ) );
        $codes->set( 'custom_fields',     $ca->getFormattedCustomFields( 'text' ) );
        $codes->set( 'custom_fields_2c',  $ca->getFormattedCustomFields( 'html' ) );
        $codes->set( 'number_of_persons', $ca->get( 'number_of_persons' ) );
        $codes->set( 'service_name',      $service->getTitle() );
        $codes->set( 'service_price',     $price );
        $codes->set( 'service_info',      $service->getInfo() );
        $codes->set( 'staff_name',        $staff->getName() );
        $codes->set( 'staff_info',        $staff->getInfo() );
        $codes->set( 'staff_email',       $staff->get( 'email' ) );
        $codes->set( 'staff_phone',       $staff->get( 'phone' ) );
        $codes->set( 'staff_photo',       $staff->get( 'avatar_url' ) );
        $codes = apply_filters( 'bookly_notification_codes', $codes, $ca );

        return array( $codes, $staff, $appointment, $customer );
    }

    /**
     * Send email to $mail_to.
     *
     * @param Entities\Notification $notification
     * @param NotificationCodes $codes
     * @param $mail_to
     * @param string $phone
     * @param array $extra
     * @param null|string $language_code
     * @return bool
     */
    private static function _send( Entities\Notification $notification, NotificationCodes $codes, $mail_to, $phone = '', $extra = array(), $language_code = null )
    {
        $result  = false;
        $message_template = Utils\Common::getTranslatedString( $notification->get( 'gateway' ) . '_' . $notification->get( 'type' ), $notification->get( 'message' ), $language_code );
        $message = $codes->replace( $message_template, $notification->get( 'gateway' ) );
        if ( $notification->get( 'gateway' ) == 'email' ) {
            // Send email to recipient.
            $subject = $codes->replace( Utils\Common::getTranslatedString( $notification->get( 'gateway' ) . '_' . $notification->get( 'type' ) . '_subject', $notification->get( 'subject' ), $language_code ) );
            $headers = Utils\Common::getEmailHeaders( $extra );
            $message = get_option( 'ab_email_content_type' ) == 'plain' ? $message : wpautop( $message );
            $result  = wp_mail( $mail_to, $subject, $message, $headers );
            // Send copy to administrators.
            if ( $notification->get( 'copy' ) ) {
                $admin_emails = Utils\Common::getAdminEmails();
                if ( ! empty ( $admin_emails ) ) {
                    wp_mail( $admin_emails, $subject, $message, $headers );
                }
            }
        } elseif ( $notification->get( 'gateway' ) == 'sms' ) {
            // Send sms.
            if ( self::$sms_authorized === null ) {
                self::$sms = new SMS();
                self::$sms_authorized = self::$sms->loadProfile();
            }
            if ( self::$sms_authorized ) {
                if ( $phone != '' ) {
                    $result = self::$sms->sendSms( $phone, $message );
                }
                if ( $notification->get( 'copy' ) ) {
                    if ( ( $administrator_phone = get_option( 'ab_sms_administrator_phone', '' ) != '' ) ) {
                        self::$sms->sendSms( $administrator_phone, $message );
                    }
                }
            }
        }

        return $result;
    }

}