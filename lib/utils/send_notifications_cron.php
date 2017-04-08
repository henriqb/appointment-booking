<?php
namespace Bookly\Lib\Utils;

use Bookly\Lib;
use Bookly\Lib\Entities;

define( 'WP_USE_THEMES', false );
if ( isset( $argv ) ) {
    foreach ( $argv as $argument ) {
        if ( strpos( $argument, 'host=' ) === 0 ) {
            $_SERVER['HTTP_HOST'] = substr( $argument, 5 );
        }
    }
}
require_once __DIR__ . '/../../../../../wp-load.php';
require_once ABSPATH . WPINC . '/formatting.php';
require_once ABSPATH . WPINC . '/general-template.php';
require_once ABSPATH . WPINC . '/pluggable.php';
require_once ABSPATH . WPINC . '/link-template.php';
include __DIR__ . '/../../autoload.php';

/**
 * Class Notifier
 * @package Bookly\Lib\Utils
 */
class Notifier
{
    private $mysql_now; // format: YYYY-MM-DD HH:MM:SS

    /**
     * @var Lib\SMS
     */
    private $sms;

    private $sms_authorized = false;

    /**
     * @param Entities\Notification $notification
     */
    public function processNotification( Entities\Notification $notification )
    {
        /** @var \wpdb $wpdb */
        global $wpdb;
        $date  = new \DateTime();
        $hours = get_option( 'ab_settings_cron_reminder' );

        switch ( $notification->get( 'type' ) ) {
            case 'staff_agenda':
                if ( $date->format( 'H' ) >= $hours[ $notification->get( 'type' ) ] ) {
                    $rows = $wpdb->get_results(
                        'SELECT
                            a.*,
                            ca.locale,
                            c.name       AS customer_name,
                            s.title      AS service_title,
                            s.info       AS service_info,
                            st.email     AS staff_email,
                            st.phone     AS staff_phone,
                            st.full_name AS staff_name,
                            st.info      AS staff_info
                        FROM ' . Entities\CustomerAppointment::getTableName() . ' ca
                        LEFT JOIN ' . Entities\Appointment::getTableName() . ' a   ON a.id = ca.appointment_id
                        LEFT JOIN ' . Entities\Customer::getTableName() . ' c      ON c.id = ca.customer_id
                        LEFT JOIN ' . Entities\Service::getTableName() . ' s       ON s.id = a.service_id
                        LEFT JOIN ' . Entities\Staff::getTableName() . ' st        ON st.id = a.staff_id
                        LEFT JOIN ' . Entities\StaffService::getTableName() . ' ss ON ss.staff_id = a.staff_id AND ss.service_id = a.service_id
                        WHERE DATE(DATE_ADD("' . $this->mysql_now . '", INTERVAL 1 DAY)) = DATE(a.start_date) AND NOT EXISTS (
                            SELECT * FROM ' . Entities\SentNotification::getTableName() . ' sn WHERE
                                DATE(sn.created) = DATE("' . $this->mysql_now . '") AND
                                sn.gateway       = "' . $notification->get( 'gateway' ) . '" AND
                                sn.type          = "staff_agenda" AND
                                sn.staff_id      = a.staff_id
                        )
                        ORDER BY a.start_date'
                    );

                    if ( $rows ) {
                        $appointments = array();
                        foreach ( $rows as $row ) {
                            $appointments[ $row->staff_id ][] = $row;
                        }

                        foreach ( $appointments as $staff_id => $collection ) {
                            $sent = false;
                            $staff_email = null;
                            $staff_phone = null;
                            $is_plain = ( get_option( 'ab_email_content_type' ) == 'plain'
                                          || $notification->get( 'gateway' ) == 'sms' );
                            $table  = $is_plain ? '%s' : '<table>%s</table>';
                            $tr     = $is_plain ? "%s %s %s\n" : '<tr><td>%s</td><td>%s</td><td>%s</td></tr>';
                            $agenda = '';
                            foreach ( $collection as $appointment ) {
                                $startDate = new \DateTime( $appointment->start_date );
                                $endDate   = new \DateTime( $appointment->end_date );
                                $agenda   .= sprintf(
                                    $tr,
                                    $startDate->format( 'H:i' ) . '-' . $endDate->format( 'H:i' ),
                                    Lib\Utils\Common::getTranslatedString( 'service_' . $appointment->service_id, $appointment->service_title, $appointment->locale ),
                                    $appointment->customer_name
                                );
                                $staff_email = $appointment->staff_email;
                                $staff_phone = $appointment->staff_phone;
                            }
                            $agenda = sprintf( $table, $agenda );

                            if ( $staff_email || $staff_phone ) {
                                $replacement = new Lib\NotificationCodes();
                                $replacement->set( 'next_day_agenda', $agenda );
                                $replacement->set( 'appointment_start', $appointment->start_date );
                                $replacement->set( 'staff_name',   Lib\Utils\Common::getTranslatedString( 'staff_' . $appointment->staff_id, $appointment->staff_name, $appointment->locale ) );
                                $replacement->set( 'staff_info',   Lib\Utils\Common::getTranslatedString( 'staff_' . $appointment->staff_id . '_info', $appointment->staff_info, $appointment->locale ) );
                                $replacement->set( 'service_info', Lib\Utils\Common::getTranslatedString( 'service_' . $appointment->service_id . '_info', $appointment->service_info, $appointment->locale ) );

                                $message_template = Lib\Utils\Common::getTranslatedString( $notification->get( 'gateway' ) . '_' . $notification->get( 'type' ), $notification->get( 'message' ), $appointment->locale );
                                $message = $replacement->replace( $message_template, $notification->get( 'gateway' ) );
                                if ( $notification->get( 'gateway' ) == 'email' && $staff_email ) {
                                    $subject = $replacement->replace( Lib\Utils\Common::getTranslatedString( $notification->get( 'gateway' ) . '_' . $notification->get( 'type' ) . '_subject', $notification->get( 'subject' ), $appointment->locale ) );
                                    $message = get_option( 'ab_email_content_type' ) == 'plain' ? $message : wpautop( $message );
                                    // Send email.
                                    $sent = wp_mail( $staff_email, $subject, $message, Lib\Utils\Common::getEmailHeaders() );
                                } elseif ( $notification->get( 'gateway' ) == 'sms' && $staff_phone ) {
                                    // Send sms.
                                    $sent = $this->sms->sendSms( $staff_phone, $message );
                                }
                            }

                            if ( $sent ) {
                                $sent_notification = new Entities\SentNotification();
                                $sent_notification->set( 'staff_id', $staff_id );
                                $sent_notification->set( 'gateway',  $notification->get( 'gateway' ) );
                                $sent_notification->set( 'created',  $date->format( 'Y-m-d H:i:s' ) );
                                $sent_notification->set( 'type',     'staff_agenda' );
                                $sent_notification->save();
                            }
                        }
                    }
                }
                break;
            case 'client_follow_up':
                if ( $date->format( 'H' ) >= $hours[ $notification->get( 'type' ) ] ) {
                    $rows = $wpdb->get_results(
                        'SELECT
                            a.*,
                            ca.*
                        FROM ' . Entities\CustomerAppointment::getTableName() . ' ca
                        LEFT JOIN ' . Entities\Appointment::getTableName() . ' a ON a.id = ca.appointment_id
                        WHERE DATE("' . $this->mysql_now . '") = DATE(a.start_date) AND NOT EXISTS (
                            SELECT * FROM ' . Entities\SentNotification::getTableName() . ' sn WHERE
                                DATE(sn.created)           = DATE("' . $this->mysql_now . '") AND
                                sn.gateway                 = "' . $notification->get( 'gateway' ) . '" AND
                                sn.type                    = "client_follow_up" AND
                                sn.customer_appointment_id = ca.id
                        )',
                        ARRAY_A
                    );

                    if ( $rows ) {
                        foreach ( $rows as $row ) {
                            $customer_appointment = new Entities\CustomerAppointment();
                            $customer_appointment->load( $row['id'] );
                            if ( Lib\NotificationSender::sendFromCron( Lib\NotificationSender::CRON_FOLLOW_UP_EMAIL, $notification, $customer_appointment ) ) {
                                $sent_notification = new Entities\SentNotification();
                                $sent_notification->set( 'customer_appointment_id', $customer_appointment->get( 'id' ) );
                                $sent_notification->set( 'gateway', $notification->get( 'gateway' ) );
                                $sent_notification->set( 'created', $date->format( 'Y-m-d H:i:s' ) );
                                $sent_notification->set( 'type',    'client_follow_up' );
                                $sent_notification->save();
                            }
                        }
                    }
                }
                break;
            case 'client_reminder':
                if ( $date->format( 'H' ) >= $hours[ $notification->get( 'type' ) ] ) {
                    $rows = $wpdb->get_results(
                        'SELECT
                            ca.id
                        FROM ' . Entities\CustomerAppointment::getTableName() . ' ca
                        LEFT JOIN ' . Entities\Appointment::getTableName() . ' a ON a.id = ca.appointment_id
                        WHERE DATE(DATE_ADD("' . $this->mysql_now . '", INTERVAL 1 DAY)) = DATE(a.start_date) AND NOT EXISTS (
                            SELECT * FROM ' . Entities\SentNotification::getTableName() . ' sn WHERE
                                DATE(sn.created)           = DATE("' . $this->mysql_now . '") AND
                                sn.gateway                 = "' . $notification->get( 'gateway' ) . '" AND
                                sn.type                    = "client_reminder" AND
                                sn.customer_appointment_id = ca.id
                        )',
                        ARRAY_A
                    );

                    if ( $rows ) {
                        foreach ( $rows as $row ) {
                            $customer_appointment = new Entities\CustomerAppointment();
                            $customer_appointment->load( $row['id'] );
                            if ( Lib\NotificationSender::sendFromCron( Lib\NotificationSender::CRON_NEXT_DAY_APPOINTMENT, $notification, $customer_appointment ) ) {
                                $sent_notification = new Entities\SentNotification();
                                $sent_notification->set( 'customer_appointment_id', $customer_appointment->get( 'id' ) );
                                $sent_notification->set( 'gateway', $notification->get( 'gateway' ) );
                                $sent_notification->set( 'created', $date->format( 'Y-m-d H:i:s' ) );
                                $sent_notification->set( 'type',    'client_reminder' );
                                $sent_notification->save();
                            }
                        }
                    }
                }
                break;
        }
    }

    /**
     * Constructor
     */
    public function __construct()
    {
        date_default_timezone_set( Lib\Utils\Common::getTimezoneString() );

        wp_load_translations_early();

        $now = new \DateTime();
        $this->mysql_now = $now->format( 'Y-m-d H:i:s' );
        $this->sms = new Lib\SMS();
        $this->sms_authorized = $this->sms->loadProfile();

        $query = Entities\Notification::query()
            ->where( 'active', 1 )
            ->whereIn( 'type', array( 'staff_agenda', 'client_follow_up', 'client_reminder' ) );

        foreach ( $query->find() as $notification ) {
            $this->processNotification( $notification );
        }
    }

}

add_action( 'bookly_send_notifications', function() { new Notifier(); } );

do_action( 'bookly_send_notifications' );