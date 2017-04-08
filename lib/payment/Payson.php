<?php
namespace Bookly\Lib\Payment;

use Bookly\Lib;

/**
 * Class Payson
 * @package Bookly\Lib\Payment
 */
class Payson
{
    // Array for cleaning Payson request
    public static $remove_parameters = array( 'TOKEN', 'form_id', 'action', 'ab_fid', 'error_msg' );

    public static function renderForm( $form_id )
    {
        $userData = new Lib\UserBookingData( $form_id );
        if ( $userData->load() ) {
            $html = '<form method="post" class="ab-payson-form">';
            $html .= '<input type="hidden" name="action" value="ab-payson-checkout"/>';
            $html .= '<input type="hidden" name="ab_fid" value="' . $form_id . '"/>';
            $html .= '<input type="hidden" name="response_url"/>';
            $html .= '<button class="ab-left ab-back-step ab-btn ladda-button" data-style="zoom-in" style="margin-right: 10px;" data-spinner-size="40"><span class="ladda-label">' . Lib\Utils\Common::getTranslatedOption( 'ab_appearance_text_button_back' ) . '</span></button>';
            $html .= '<button class="ab-right ab-next-step ab-btn ladda-button" data-style="zoom-in" data-spinner-size="40"><span class="ladda-label">' . Lib\Utils\Common::getTranslatedOption( 'ab_appearance_text_button_next' ) . '</span></button>';
            $html .= '</form>';

            echo $html;
        }
    }

    /**
     * Check gateway data and if ok save payment info
     *
     * @param \PaymentDetails          $details
     * @param bool|false               $ipn      When ipn false, this is request from browser and we use _redirectTo for notification customer
     * @param null|Lib\UserBookingData $userData
     * @return bool
     */
    public static function handlePayment( \PaymentDetails $details, $ipn = false, $userData = null )
    {
        $payment_accepted = ( $details->getType() == 'TRANSFER' && $details->getStatus() == 'COMPLETED' );  // CARD
        if ( $payment_accepted == false &&          // INVOICE
             ( $details->getType() == 'INVOICE' && $details->getStatus() == 'PENDING'
               && in_array( $details->getInvoiceStatus(), array ( 'ORDERCREATED', 'DONE' ) )
             )
        ) {
            $payment_accepted = true;
        }

        if ( $payment_accepted ) {
            // Handle completed card & bank transfers here
            /** @var \OrderItem $product */
            $product = current( $details->getOrderItems() );

            $payment_total = Lib\Entities\Payment::query( 'p' )->select( 'SUM(p.total) AS payment_total' )->whereIn( 'p.customer_appointment_id', explode( ',', $details->getCustom() ) )->where( 'p.type', Lib\Entities\Payment::TYPE_PAYSON )->fetchRow();
            $total    = (float) $payment_total['payment_total'];
            $received = (float) $product->getUnitPrice();

            $difference = ( $received > $total ) ? $received / $total : $total / $received;
            if ( $difference > 1.005 /* 0.5% */ ) {
                // The big difference in the expected and received payment.
                if ( ! $ipn ) {
                    self::_redirectTo( $userData, 'error', __( 'Incorrect payment data', 'bookly' ) );
                }
                return;
            }

            if ( get_option( 'ab_currency' ) == $details->getCurrencyCode() ) {
                $payments = Lib\Entities\Payment::query()->whereIn( 'customer_appointment_id', explode( ',', $details->getCustom() ) )->whereIn( 'type', array( Lib\Entities\Payment::TYPE_PAYSON, Lib\Entities\Payment::TYPE_COUPON ) )->find();
                if ( empty( $payments ) ) {
                    if ( ! $ipn ) {
                        self::_redirectTo( $userData, 'error', __( 'Incorrect payment data', 'bookly' ) );
                    }
                    return;
                }
                $notify = array();
                foreach ( $payments as $payment ) {
                    if ( $payment->get( 'status' ) == Lib\Entities\Payment::STATUS_COMPLETED ) {
                        continue;
                    }
                    $payment->set( 'status', Lib\Entities\Payment::STATUS_COMPLETED );
                    $payment->set( 'token',  $details->getToken() );
                    $payment->set( 'transaction_id', $details->getPurchaseId() );
                    $payment->save();
                    $notify[] = $payment->get( 'customer_appointment_id' );
                }
                foreach ( Lib\Entities\CustomerAppointment::query()->whereIn( 'id', $notify )->find() as $ca ) {
                    Lib\NotificationSender::send( Lib\NotificationSender::INSTANT_NEW_APPOINTMENT, $ca );
                }
                if ( ! $ipn ) {
                    self::_redirectTo( $userData, 'success' );
                } else {
                    wp_send_json_success();
                }
                exit;
            }

        } elseif ( ! $ipn && $details->getStatus() == 'ERROR' ) {
            self::_redirectTo( $userData, 'error', __( 'Error', 'bookly' ) );

        } elseif ( ! $ipn ) {
            if ( in_array( $details->getStatus(), array( 'CREATED', 'PENDING', 'PROCESSING', 'CREDITED' ) ) ) {
                self::_redirectTo( $userData, 'processing' );
            } else {
                header( 'Location: ' . wp_sanitize_redirect( add_query_arg( array(
                        'action'    => 'ab-payson-error',
                        'ab_fid'    => stripslashes( @$_REQUEST['ab_fid'] ),
                        'error_msg' => str_replace( ' ', '%20', __( 'Payment status', 'bookly' ) . ' ' . $details->getStatus() ),
                    ), Lib\Utils\Common::getCurrentPageURL()
                    ) ) );
                exit;
            }
        }
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
        $userData->setPaymentStatus( Lib\Entities\Payment::TYPE_PAYSON, $status, $message );
        @wp_redirect( remove_query_arg( Lib\Payment\Payson::$remove_parameters, Lib\Utils\Common::getCurrentPageURL() ) );
        exit;
    }

    /**
     * Redirect to Payson Payment page, or step payment.
     *
     * @param $form_id
     * @param Lib\UserBookingData $userData
     * @param $response_url
     */
    public static function paymentPage( $form_id, Lib\UserBookingData $userData, $response_url )
    {
        $api = self::_getApi();
        $url = array(
            'return'  => $response_url . 'action=ab-payson-response&ab_fid=' . $form_id,
            'cancel'  => $response_url . 'action=ab-payson-cancel&ab_fid=' . $form_id,
            'ipn'     => $response_url . 'action=ab-payson-ipn'
        );
        $payson_email = get_option( 'ab_payson_api_receiver_email' );

        $cart_info = $userData->getCartInfo();
        $receiver  = new \Receiver( $payson_email, $cart_info['total_price'] );
        $receivers = array( $receiver );
        $client    = array(
            'email'      => $userData->get( 'email' ),
            'first_name' => $userData->get( 'name' ),
            'last_name'  => ''
        );
        $sender   = new \Sender( $client['email'], $client['first_name'], $client['last_name'] );

        $pay_data = new \PayData( $url['return'], $url['cancel'], $url['ipn'], $userData->getCartItemsTitle( 128 ), $sender, $receivers );
        $products = array ( new \OrderItem( $userData->getCartItemsTitle( 128 ), $cart_info['total_price'], 1, 0, Lib\Utils\Common::getTranslatedOption( 'ab_appearance_text_label_service' ) ) );
        $pay_data->setOrderItems( $products );

        // $constraints = array( FundingConstraint::BANK, FundingConstraint::CREDITCARD, FundingConstraint::INVOICE, FundingConstraint::SMS ); // all available
        $funding     = (array) get_option( 'ab_payson_funding' );
        $reflector   = new \ReflectionClass( 'FundingConstraint' );
        $enum        = $reflector->getConstants();
        $constraints = array();
        foreach ( $funding as $type ) {
            if ( array_key_exists( $type, $enum ) ) {
                $constraints[] = $enum[ $type ];
            }
        }
        if ( empty( $constraints ) ) {
            $constraints = array( \FundingConstraint::CREDITCARD );
        }
        $pay_data->setFundingConstraints( $constraints );
        $pay_data->setFeesPayer( get_option( 'ab_payson_fees_payer' ) );
        $pay_data->setGuaranteeOffered( 'NO' );  // Disable PaysonGuarantee™
        $pay_data->setCurrencyCode( get_option( 'ab_currency' ) );

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
                $payment->set( 'total',    '0.00' );
                $payment->set( 'type',     $coupon ? Lib\Entities\Payment::TYPE_COUPON : Lib\Entities\Payment::TYPE_PAYSON );
                $payment->set( 'status',   Lib\Entities\Payment::STATUS_PENDING );
            } else {
                // Create record for local payment.
                $payment->set( 'total',    $total_appointment_price );
                $payment->set( 'type',     Lib\Entities\Payment::TYPE_PAYSON );
                $payment->set( 'status',   Lib\Entities\Payment::STATUS_PENDING );
            }
            $payment->save();
        } );
        $pay_data->setCustom( implode( ',', array_keys( $cart_appointments ) ) );
        try {
            $pay_response = $api->pay( $pay_data );
            if ( $pay_response->getResponseEnvelope()->wasSuccessful() ) {
                header( 'Location: ' . $api->getForwardPayUrl( $pay_response ) );
                exit;
            } else {
                self::_deleteAppointments( $cart_appointments );
                /** @var \PaysonApiError[] $errors */
                $errors = $pay_response->getResponseEnvelope()->getErrors();
                self::_redirectTo( $userData, 'error', $errors[0]->getMessage() );
            }
        } catch ( \Exception $e ) {
            self::_deleteAppointments( $cart_appointments );
            $userData->setPaymentStatus( Lib\Entities\Payment::TYPE_PAYSON, 'error', $e->getMessage() );
            @wp_redirect( remove_query_arg( Lib\Payment\Payson::$remove_parameters, Lib\Utils\Common::getCurrentPageURL() ) );
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

    /**
     * Handles IPN messages
     */
    public static function ipn()
    {
        $api      = self::_getApi();
        $received = file_get_contents( 'php://input' );
        $response = $api->validate( $received );

        if ( $response->isVerified() ) {
            Lib\Payment\Payson::handlePayment( $response->getPaymentDetails(), true, null );
        }
        wp_send_json_success();
    }

    /**
     * Response when payment form completed
     *
     * @return \PaymentDetailsResponse or redirect
     */
    public static function response()
    {
        $api   = self::_getApi();
        $token = stripslashes( @$_GET['TOKEN'] );
        $details_response = $api->paymentDetails( new \PaymentDetailsData( $token ) );

        if ( $details_response->getResponseEnvelope()->wasSuccessful() ) {
            Lib\Payment\Payson::handlePayment( $details_response->getPaymentDetails(), false, new Lib\UserBookingData( stripslashes( @$_GET['ab_fid'] ) ) );
        } else {
            return $details_response;
        }
    }

    /**
     * Handle cancel request
     */
    public static function cancel()
    {
        $api   = self::_getApi();
        $token = stripslashes( @$_GET['TOKEN'] );
        $payment_details = $api->paymentDetails( new \PaymentDetailsData( $token ) )->getPaymentDetails();
        if ( $payment_details->getStatus() == 'ABORTED' ) {
            /** @var Lib\Entities\CustomerAppointment[] $customer_appointments */
            $customer_appointments = Lib\Entities\CustomerAppointment::query()->whereIn( 'id', explode( ',', $payment_details->getCustom() ) )->find();
            self::_deleteAppointments( $customer_appointments );
        }
    }

    /**
     * Init Api
     *
     * @return \PaysonApi
     */
    private static function _getApi()
    {
        include_once AB_PATH . '/lib/payment/payson/paysonapi.php';
        $agent       = get_option( 'ab_payson_api_agent_id' );
        $api_key     = get_option( 'ab_payson_api_key' );
        $credentials = new \PaysonCredentials( $agent, $api_key );

        return new \PaysonApi( $credentials, ( get_option( 'ab_payson_sandbox' ) == 1 ) );
    }

}