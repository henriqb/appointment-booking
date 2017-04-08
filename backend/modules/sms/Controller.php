<?php
namespace Bookly\Backend\Modules\Sms;

use Bookly\Lib;

/**
 * Class Controller
 * @package Bookly\Backend\Modules\Sms
 */
class Controller extends Lib\Controller
{
    const page_slug = 'ab-sms';

    public function index()
    {
        global $wp_locale;

        $this->enqueueStyles( array(
            'frontend' => array_merge(
                array(
                    'css/ladda.min.css',
                ),
                get_option( 'ab_settings_phone_default_country' ) == 'disabled'
                    ? array()
                    : array( 'css/intlTelInput.css' )
            ),
            'backend'  => array(
                'css/bookly.main-backend.css',
                'bootstrap/css/bootstrap.min.css',
                'css/daterangepicker.css',
            ),
            'module'   => array(
                'css/sms.css',
                'css/flags.css',
            )
        ) );

        $this->enqueueScripts( array(
            'backend'  => array(
                'bootstrap/js/bootstrap.min.js' => array( 'jquery' ),
                'js/moment.min.js',
                'js/daterangepicker.js' => array( 'jquery' ),
            ),
            'module'   => array( 'js/sms.js' => array( 'jquery' ) ),
            'frontend' => array_merge(
                array(
                    'js/spin.min.js'  => array( 'jquery' ),
                    'js/ladda.min.js' => array( 'jquery' ),
                ),
                get_option( 'ab_settings_phone_default_country' ) == 'disabled'
                    ? array()
                    : array( 'js/intlTelInput.min.js' => array( 'jquery' ) )
            ),
        ) );

        $is_logged_in = false;
        $messages     = array();
        $errors       = array();
        $prices       = array();
        $form         = new \Bookly\Backend\Modules\Notifications\Forms\Notifications( 'sms' );
        $sms          = new Lib\SMS();
        $cron_reminder = (array) get_option( 'ab_settings_cron_reminder' );

        if ( $this->hasParameter( 'form-login' ) ) {
            $is_logged_in = $sms->login( $this->getParameter( 'username' ), $this->getParameter( 'password' ) );

        } elseif ( $this->hasParameter( 'form-logout' ) ) {
            $sms->logout();

        } elseif ( $this->hasParameter( 'form-registration' ) ) {
            if ( $this->getParameter( 'accept_tos', false ) ) {
                $is_logged_in = $sms->register(
                    $this->getParameter( 'username' ),
                    $this->getParameter( 'password' ),
                    $this->getParameter( 'password_repeat' )
                );
            } else {
                $errors[] = __( 'Please accept terms and conditions.', 'bookly' );
            }

        } else {
            $is_logged_in = $sms->loadProfile();
        }

        if ( ! $is_logged_in ) {
            if ( $response = $sms->getPriceList() ) {
                $prices = $response->list;
            }
            if ( $_SERVER['REQUEST_METHOD'] == 'GET' ) {
                // Hide authentication errors on auto login.
                $sms->clearErrors();
            }
        } else {
            switch ( $this->getParameter( 'paypal_result' ) ) {
                case 'success':
                    $messages[] = __( 'Your payment has been accepted for processing.', 'bookly' );
                    break;
                case 'cancel':
                    $errors[] = __( 'Your payment has been interrupted.', 'bookly' );
                    break;
            }
            if ( $this->hasParameter( 'form-notifications' ) ) {
                update_option( 'ab_sms_administrator_phone', $this->getParameter( 'ab_sms_administrator_phone' ) );

                $form->bind( $this->getPostParameters(), $_FILES );
                $form->save();
                $messages[] = __( 'Notification settings were updated successfully.', 'bookly' );

                foreach ( array( 'staff_agenda', 'client_follow_up', 'client_reminder' ) as $type ) {
                    $cron_reminder[ $type ] = $this->getParameter( $type . '_cron_hour' );
                }
                update_option( 'ab_settings_cron_reminder', $cron_reminder );
            }
            if ( $this->hasParameter( 'tab' ) ) {
                switch ( $this->getParameter( 'auto-recharge' ) ) {
                    case 'approved':
                        update_option( 'ab_sms_auto_recharge_balance', 1 );
                        $messages[] = __( 'Auto-Recharge enabled.', 'bookly' );
                        break;
                    case 'declined':
                        $errors[] = __( 'You declined the Auto-Recharge of your balance.', 'bookly' );
                        break;
                }
            }
        }
        $current_tab = $this->hasParameter( 'tab' ) ? $this->getParameter( 'tab' ) : 'notifications';
        wp_localize_script( 'ab-daterangepicker.js', 'BooklyL10n',
            array(
                'today'         => __( 'Today',        'bookly' ),
                'yesterday'     => __( 'Yesterday',    'bookly' ),
                'last_7'        => __( 'Last 7 Days',  'bookly' ),
                'last_30'       => __( 'Last 30 Days', 'bookly' ),
                'this_month'    => __( 'This Month',   'bookly' ),
                'last_month'    => __( 'Last Month',   'bookly' ),
                'custom_range'  => __( 'Custom Range', 'bookly' ),
                'apply'         => __( 'Apply',  'bookly' ),
                'cancel'        => __( 'Cancel', 'bookly' ),
                'to'            => __( 'To',     'bookly' ),
                'from'          => __( 'From',   'bookly' ),
                'months'        => array_values( $wp_locale->month ),
                'days'          => array_values( $wp_locale->weekday_abbrev ),
                'startOfWeek'   => (int) get_option( 'start_of_week' ),
                'mjsDateFormat' => Lib\Utils\DateTime::convertFormat( 'date', Lib\Utils\DateTime::FORMAT_MOMENT_JS ),
                'current_tab'   => $current_tab,
                'country'       => get_option( 'ab_settings_phone_default_country' ),
                'intlTelInput'  => array(
                    'enabled'   => ( get_option( 'ab_settings_phone_default_country' ) != 'disabled' ),
                    'utils'     => plugins_url( 'intlTelInput.utils.js', AB_PATH . '/frontend/resources/js/intlTelInput.utils.js' ),
                    'country'   => get_option( 'ab_settings_phone_default_country' ),
                ),
                'auto_recharge_balance' => get_option( 'ab_sms_auto_recharge_balance' ) == 1,
                'passwords_no_same'     => __( 'Passwords must be the same.', 'bookly' ),
                'input_old_password'    => __( 'Please enter old password.',  'bookly' ),
            )
        );
        $cron_path = realpath( AB_PATH . '/lib/utils/send_notifications_cron.php' );
        $errors = array_merge( $errors, $sms->getErrors() );
        $recharge_amount = get_option( 'ab_sms_auto_recharge_amount' );

        $this->render( 'index', compact( 'form', 'sms', 'is_logged_in', 'prices', 'errors', 'messages', 'cron_path', 'recharge_amount', 'cron_reminder' ) );
    } // index

    public function executeGetPurchasesList()
    {
        $sms = new Lib\SMS();
        if ( $this->hasParameter( 'range' ) ) {
            $dates = explode( ' - ', $this->getParameter( 'range' ), 2 );
            $start = Lib\Utils\DateTime::applyTimeZoneOffset( $dates[0], 0 );
            $end   = Lib\Utils\DateTime::applyTimeZoneOffset( date( 'Y-m-d', strtotime( '+1 day', strtotime( $dates[1] ) ) ), 0 );
        } else {
            $start = Lib\Utils\DateTime::applyTimeZoneOffset( date( 'Y-m-d', strtotime( 'first day of this month' ) ), 0 );
            $end   = Lib\Utils\DateTime::applyTimeZoneOffset( date( 'Y-m-d', strtotime( 'first day of next month' ) ), 0 );
        }

        $list  = $sms->getPurchasesList( $start, $end );
        if ( empty ( $list ) ) {
            wp_send_json_error();
        } else {
            wp_send_json( $list );
        }
    }

    public function executeGetSmsList()
    {
        $sms = new Lib\SMS();
        if ( $this->hasParameter( 'range' ) ) {
            $dates = explode( ' - ', $this->getParameter( 'range' ), 2 );
            $start = Lib\Utils\DateTime::applyTimeZoneOffset( $dates[0], 0 );
            $end   = Lib\Utils\DateTime::applyTimeZoneOffset( date( 'Y-m-d', strtotime( '+1 day', strtotime( $dates[1] ) ) ), 0 );
        } else {
            $start = Lib\Utils\DateTime::applyTimeZoneOffset( date( 'Y-m-d', strtotime( 'first day of this month' ) ), 0 );
            $end   = Lib\Utils\DateTime::applyTimeZoneOffset( date( 'Y-m-d', strtotime( 'first day of next month' ) ), 0 );
        }

        $list  = $sms->getSmsList( $start, $end );
        if ( empty ( $list ) ) {
            wp_send_json_error();
        } else {
            wp_send_json( $list );
        }
    }

    public function executeGetPriceList()
    {
        $sms  = new Lib\SMS();
        $list = $sms->getPriceList();
        if ( empty ( $list ) ) {
            wp_send_json_error();
        } else {
            wp_send_json( $list );
        }
    }

    /**
     * Initial for enabling Auto-Recharge balance
     */
    public function executeShowPreapproval()
    {
        $sms = new Lib\SMS();
        $key = $sms->getPreapprovalKey( $this->getParameter( 'amount' ) );
        if ( $key !== false ) {
            update_option( 'ab_sms_auto_recharge_amount', $this->getParameter( 'amount' ) );
            wp_send_json_success( array( 'paypal_preapproval' => 'https://www.paypal.com/cgi-bin/webscr?cmd=_ap-preapproval&preapprovalkey=' . $key ) );
        } else {
            wp_send_json_error( array( 'message' => __( 'Auto-Recharge has failed, please replenish your balance directly.', 'bookly' ) ) );
        }
    }

    /**
     * Disable Auto-Recharge balance
     */
    public function executeDeclinePreapproval()
    {
        $sms  = new Lib\SMS();
        $declined = $sms->declinePreapproval();
        update_option( 'ab_sms_auto_recharge_balance', 0 );
        if ( $declined !== false ) {
            wp_send_json_success( array( 'message' => __( 'Auto-Recharge disabled', 'bookly' ) ) );
        } else {
            wp_send_json_error( array( 'message' => __( 'Error. Can\'t disable Auto-Recharge, you can perform this action in your PayPal account.', 'bookly' ) ) );
        }
    }

    public function executeChangePassword()
    {
        $sms  = new Lib\SMS();
        $old_password = $this->getParameter( 'old_password' );
        $new_password = $this->getParameter( 'new_password' );

        $result = $sms->changePassword( $new_password, $old_password );
        if ( $result === false ) {
            wp_send_json_error( array( 'message' => current( $sms->getErrors() ) ) );
        } else {
            wp_send_json_success();
        }
    }

    public function executeSendTestSms()
    {
        $sms = new Lib\SMS();
        $phone_number = $this->getParameter( 'phone_number' );
        if ( $phone_number != '' ) {
            $response = array( 'success' => $sms->sendSms( $phone_number, 'Bookly test SMS.' ) );
            if ( $response['success'] ) {
                $response['message'] = __( 'SMS has been sent successfully.', 'bookly' );
            } else {
                $response['message'] = __( 'Failed to send SMS.', 'bookly' );
            }
            wp_send_json( $response );
        } else {
            wp_send_json( array( 'success' => false, 'message' => __( 'Phone number is empty.', 'bookly' ) ) );
        }
    }

    public function executeForgotPassword()
    {
        $sms      = new Lib\SMS();
        $step     = $this->getParameter( 'step' );
        $code     = $this->getParameter( 'code' );
        $username = $this->getParameter( 'username' );
        $password = $this->getParameter( 'password' );
        $result   = $sms->forgotPassword( $username, $step, $code, $password );
        if ( $result === false ) {
            wp_send_json_error( array( 'message' => current( $sms->getErrors() ) ) );
        } else {
            wp_send_json_success();
        }
    }

    /**
     * Enable or Disable email notification about low balance.
     */
    public function executeNotifyLowBalance()
    {
        update_option( 'ab_sms_notify_low_balance', $this->getParameter( 'value' ) );
        wp_send_json_success();
    }

    /**
     * Override parent method to add 'wp_ajax_ab_' prefix
     * so current 'execute*' methods look nicer.
     *
     * @param string $prefix
     */
    protected function registerWpActions( $prefix = '' )
    {
        parent::registerWpActions( 'wp_ajax_ab_' );
    }

}