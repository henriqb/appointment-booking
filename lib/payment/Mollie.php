<?php
namespace Bookly\Lib\Payment;

use Bookly\Lib;

/**
 * Class Mollie
 */
class Mollie
{
    // Array for cleaning Mollie request
    public static $remove_parameters = array( 'action', 'ab_fid', 'error_msg' );

    public static function renderForm( $form_id )
    {
        $userData = new Lib\UserBookingData( $form_id );
        if ( $userData->load() ) {
            $html = '<form method="post" class="ab-mollie-form">';
            $html .= '<input type="hidden" name="action" value="ab-mollie-checkout"/>';
            $html .= '<input type="hidden" name="ab_fid" value="' . $form_id . '"/>';
            $html .= '<input type="hidden" name="response_url"/>';
            $html .= '<button class="ab-left ab-back-step ab-btn ladda-button" data-style="zoom-in" style="margin-right: 10px;" data-spinner-size="40"><span class="ladda-label">' . Lib\Utils\Common::getTranslatedOption( 'ab_appearance_text_button_back' ) . '</span></button>';
            $html .= '<button class="ab-right ab-next-step ab-btn ladda-button" data-style="zoom-in" data-spinner-size="40"><span class="ladda-label">' . Lib\Utils\Common::getTranslatedOption( 'ab_appearance_text_button_next' ) . '</span></button>';
            $html .= '</form>';

            echo $html;
        }
    }

    /**
     * Handles IPN messages
     */
    public static function ipn()
    {
        $api     = self::_getApi();
        $payment = $api->payments->get( $_REQUEST['id'] );
        Mollie::handlePayment( $payment );
    }

    /**
     * Check gateway data and if ok save payment info
     *
     * @param \Mollie_API_Object_Payment $details
     */
    public static function handlePayment( \Mollie_API_Object_Payment $details )
    {
        $pending_appointments = explode( ',', $details->metadata->pending );
        if ( $details->isPaid() ) {
            // Handle completed card & bank transfers here
            /** @var \OrderItem $product */
            $payment_total = Lib\Entities\Payment::query( 'p' )->select( 'SUM(p.total) AS payment_total' )->whereIn( 'p.customer_appointment_id', $pending_appointments )->where( 'p.type', Lib\Entities\Payment::TYPE_MOLLIE )->fetchRow();
            $total    = (float) $payment_total['payment_total'];
            $received = (float) $details->amount;

            $difference = ( $received > $total ) ? $received / $total : $total / $received;
            if ( $difference > 1.005 /* 0.5% */ ) {
                // The big difference in the expected and received payment.
                wp_send_json_success();
            }
            $payments = Lib\Entities\Payment::query()->whereIn( 'customer_appointment_id', $pending_appointments )->whereIn( 'type', array( Lib\Entities\Payment::TYPE_MOLLIE, Lib\Entities\Payment::TYPE_COUPON ) )->find();
            if ( empty( $payments ) ) {
                wp_send_json_success();
            }
            $notify = array();
            foreach ( $payments as $payment ) {
                if ( $payment->get( 'status' ) == Lib\Entities\Payment::STATUS_COMPLETED ) {
                    continue;
                }
                $payment->set( 'status', Lib\Entities\Payment::STATUS_COMPLETED );
                $payment->set( 'token',  $details->id );
                $payment->set( 'transaction_id', $details->profileId );
                $payment->save();
                $notify[] = $payment->get( 'customer_appointment_id' );
            }
            foreach ( Lib\Entities\CustomerAppointment::query()->whereIn( 'id', $notify )->find() as $ca ) {
                Lib\NotificationSender::send( Lib\NotificationSender::INSTANT_NEW_APPOINTMENT, $ca );
            }
        } elseif ( ! $details->isOpen() ) {
            foreach ( $pending_appointments as $ca_id ) {
                $ca = new Lib\Entities\CustomerAppointment();
                $ca->load( $ca_id );
                $ca->deleteCascade();
            }
        }
        wp_send_json_success();
    }

    /**
     * Redirect to Mollie Payment page, or step payment.
     *
     * @param $form_id
     * @param Lib\UserBookingData $userData
     * @param $response_url
     */
    public static function paymentPage( $form_id, Lib\UserBookingData $userData, $response_url )
    {
        if ( get_option( 'ab_currency' ) != 'EUR' ) {
            $userData->setPaymentStatus( Lib\Entities\Payment::TYPE_MOLLIE, 'error', __( 'Mollie accepts payments in Euro only.', 'bookly' ) );
            @wp_redirect( remove_query_arg( Lib\Payment\Payson::$remove_parameters, Lib\Utils\Common::getCurrentPageURL() ) );
            exit();
        }
        $cart_info = $userData->getCartInfo();
        $coupon = $userData->getCoupon();
        $cart_appointments = array();
        $userData->foreachCartItem( function ( Lib\UserBookingData $userData, $cart_key ) use ( $cart_info, $coupon, &$cart_appointments ) {
            $total_appointment_price = $cart_info['items'][ $cart_key ]['total_price'];
            $customer_appointment = $userData->save( false );
            $cart_appointments[ $customer_appointment->get( 'id' ) ] = $customer_appointment;
            $payment = new Lib\Entities\Payment();
            $payment->set( 'customer_appointment_id', $customer_appointment->get( 'id' ) );
            $payment->set( 'created', current_time( 'mysql' ) );
            if ( $total_appointment_price <= 0 ) {
                // Create fake payment record for 100% discount coupons.
                $payment->set( 'total',  '0.00' );
                $payment->set( 'type',   $coupon ? Lib\Entities\Payment::TYPE_COUPON : Lib\Entities\Payment::TYPE_MOLLIE );
                $payment->set( 'status', Lib\Entities\Payment::STATUS_PENDING );
            } else {
                // Create record for local payment.
                $payment->set( 'total',  $total_appointment_price );
                $payment->set( 'type',   Lib\Entities\Payment::TYPE_MOLLIE );
                $payment->set( 'status', Lib\Entities\Payment::STATUS_PENDING );
            }
            $payment->save();
        } );

        try {
            $api = self::_getApi();
            $payment = $api->payments->create(array(
                'amount'       => $cart_info['total_price'],
                'description'  => $userData->getCartItemsTitle( 125 ),
                'redirectUrl'  => $response_url . 'action=ab-mollie-response&ab_fid=' . $form_id,
                'webhookUrl'   => $response_url . 'action=ab-mollie-ipn',
                'metadata'     => array(
                    'pending'  => implode( ',', array_keys( $cart_appointments ) )
                ),
                'issuer'       => NULL
            ));
            if ( $payment->isOpen() ) {
                $userData->setPaymentStatus( Lib\Entities\Payment::TYPE_MOLLIE, 'pending', $payment->id );
                header( 'Location: ' . $payment->getPaymentUrl() );
                exit;
            } else {
                self::_deleteAppointments( $cart_appointments );
                self::_redirectTo( $userData, 'error', __( 'Mollie error.', 'bookly' ) );
            }
        } catch ( \Exception $e ) {
            self::_deleteAppointments( $cart_appointments );
            $userData->setPaymentStatus( Lib\Entities\Payment::TYPE_MOLLIE, 'error', $e->getMessage() );
            @wp_redirect( remove_query_arg( Lib\Payment\Payson::$remove_parameters, Lib\Utils\Common::getCurrentPageURL() ) );
            exit();
        }
    }

    /**
     * @param Lib\Entities\CustomerAppointment[] $customer_appointments
     */
    private static function _deleteAppointments( $customer_appointments )
    {
        foreach ( $customer_appointments as $customer_appointment ) {
            $customer_appointment->deleteCascade();
        }
    }

    private static function _getApi()
    {
        include_once AB_PATH . '/lib/payment/Mollie/API/Autoloader.php';
        $mollie = new \Mollie_API_Client();
        $mollie->setApiKey( get_option( 'ab_mollie_api_key' ) );

        return $mollie;
    }

    /**
     * Notification for customer
     *
     * @param Lib\UserBookingData $userData
     * @param string $status    success || error || processing
     * @param string $message
     */
    private static function _redirectTo( Lib\UserBookingData $userData, $status = 'success', $message = '' )
    {
        $userData->load();
        $userData->setPaymentStatus( Lib\Entities\Payment::TYPE_MOLLIE, $status, $message );
        @wp_redirect( remove_query_arg( Lib\Payment\Mollie::$remove_parameters, Lib\Utils\Common::getCurrentPageURL() ) );
        exit;
    }

    public static function getCancelledAppointments( $tr_id )
    {
        $api     = self::_getApi();
        $payment = $api->payments->get( $tr_id );
        if ( $payment->isOpen() || $payment->isPaid() ) {
            return array();
        } else {
            return explode( ',', $payment->metadata->pending );
        }
    }

}