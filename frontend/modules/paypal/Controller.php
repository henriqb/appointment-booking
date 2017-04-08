<?php
namespace Bookly\Frontend\Modules\Paypal;

use Bookly\Lib;

/**
 * Class Controller
 * @package Bookly\Frontend\Modules\PayPal
 */
class Controller extends Lib\Controller
{
    const SIGNUP = 'https://www.paypal.com/signup/';
    const HOME   = 'https://paypal.com/';

    protected function getPermissions()
    {
        return array( '_this' => 'anonymous' );
    }

    public function expressCheckout()
    {
        $form_id = $this->getParameter( 'ab_fid' );
        if ( $form_id ) {
            // Create a paypal object.
            $paypal   = new Lib\Payment\PayPal();
            $userData = new Lib\UserBookingData( $form_id );

            if ( $userData->load() ) {
                $cart_info = $userData->getCartInfo();
                $product = new \stdClass();
                $product->name  = $userData->getCartItemsTitle( 126 );
                $product->price = $cart_info['total_price'];
                $product->qty   = 1;
                $paypal->addProduct( $product );

                // and send the payment request.
                try {
                    $paypal->send_EC_Request( $form_id );
                } catch ( \Exception $e ) {
                    $userData->setPaymentStatus( Lib\Entities\Payment::TYPE_PAYPAL, 'error', $this->getParameter( 'error_msg' ) );
                    @wp_redirect( remove_query_arg( Lib\Payment\PayPal::$remove_parameters, Lib\Utils\Common::getCurrentPageURL() ) );
                    exit;
                }
            }
        }
    }

    /**
     * Express Checkout 'CANCELURL' process
     */
    public function cancel()
    {
        $userData = new Lib\UserBookingData( $this->getParameter( 'ab_fid' ) );
        $userData->load();
        $userData->setPaymentStatus( Lib\Entities\Payment::TYPE_PAYPAL, 'cancelled' );
        @wp_redirect( remove_query_arg( Lib\Payment\PayPal::$remove_parameters, Lib\Utils\Common::getCurrentPageURL() ) );
        exit;
    }

    /**
     * Express Checkout 'ERRORURL' process
     */
    public function error()
    {
        $userData = new Lib\UserBookingData( $this->getParameter( 'ab_fid' ) );
        $userData->load();
        $userData->setPaymentStatus( Lib\Entities\Payment::TYPE_PAYPAL, 'error', $this->getParameter( 'error_msg' ) );
        @wp_redirect( remove_query_arg( Lib\Payment\PayPal::$remove_parameters, Lib\Utils\Common::getCurrentPageURL() ) );
        exit;
    }

    /**
     * Process the Express Checkout RETURNURL
     */
    public function success()
    {
        $form_id = $this->getParameter( 'ab_fid' );
        $paypal  = new Lib\Payment\PayPal();

        if ( $this->hasParameter( 'token' ) && $this->hasParameter( 'PayerID' ) ) {
            $token    = $this->getParameter( 'token' );
            $payer_id = $this->getParameter( 'PayerID' );

            // Send the request to PayPal.
            $response = $paypal->sendNvpRequest( 'GetExpressCheckoutDetails', sprintf( '&TOKEN=%s', $token ) );

            if ( strtoupper( $response['ACK'] ) == 'SUCCESS' ) {
                $data = sprintf( '&TOKEN=%s&PAYERID=%s&PAYMENTREQUEST_0_PAYMENTACTION=Sale', $token, $payer_id );

                // Response keys containing useful data to send via DoExpressCheckoutPayment operation.
                $response_data_keys_pattern = sprintf( '/^(%s)/', implode( '|', array(
                    'PAYMENTREQUEST_0_AMT',
                    'PAYMENTREQUEST_0_ITEMAMT',
                    'PAYMENTREQUEST_0_CURRENCYCODE',
                    'L_PAYMENTREQUEST_0',
                ) ) );

                foreach ( $response as $key => $value ) {
                    // Collect product data from response using defined response keys.
                    if ( preg_match( $response_data_keys_pattern, $key ) ) {
                        $data .= sprintf( '&%s=%s', $key, $value );
                    }
                }

                // We need to execute the "DoExpressCheckoutPayment" at this point to Receive payment from user.
                $response = $paypal->sendNvpRequest( 'DoExpressCheckoutPayment', $data );
                if ( 'SUCCESS' == strtoupper( $response['ACK'] ) || 'SUCCESSWITHWARNING' == strtoupper( $response['ACK'] ) ) {
                    // Get transaction info
                    $response = $paypal->sendNvpRequest( 'GetTransactionDetails', '&TRANSACTIONID=' . urlencode( $response['PAYMENTINFO_0_TRANSACTIONID'] ) );
                    if ( 'SUCCESS' == strtoupper( $response['ACK'] ) || 'SUCCESSWITHWARNING' == strtoupper( $response['ACK'] ) ) {
                        $userData = new Lib\UserBookingData( $form_id );
                        $userData->load();
                        $cart_info = $userData->getCartInfo();
                        $payment = Lib\Entities\Payment::query( 'p' )
                             ->select( 'p.id' )
                             ->where( 'p.type', Lib\Entities\Payment::TYPE_PAYPAL )
                             ->where( 'p.transaction_id', $response['TRANSACTIONID'] )
                             ->findOne();
                        if ( empty ( $payment ) ) {
                            $userData->foreachCartItem( function ( Lib\UserBookingData $userData, $cart_key ) use ( $cart_info, $response, $token ) {
                                $customer_appointment = $userData->save();
                                $payment = new Lib\Entities\Payment();
                                $payment->set( 'customer_appointment_id', $customer_appointment->get( 'id' ) );
                                $payment->set( 'transaction_id', $response['TRANSACTIONID'] );
                                $payment->set( 'total',   $cart_info['items'][ $cart_key ]['total_price'] );
                                $payment->set( 'token',   $token );
                                $payment->set( 'created', current_time( 'mysql' ) );
                                $payment->set( 'type',    Lib\Entities\Payment::TYPE_PAYPAL );
                                $payment->set( 'status',  Lib\Entities\Payment::STATUS_COMPLETED );
                                $payment->save();
                            } );
                        }
                        $userData->setPaymentStatus( Lib\Entities\Payment::TYPE_PAYPAL, 'success' );

                        @wp_redirect( remove_query_arg( Lib\Payment\PayPal::$remove_parameters, Lib\Utils\Common::getCurrentPageURL() ) );
                        exit;
                    } else {
                        header( 'Location: ' . wp_sanitize_redirect( add_query_arg( array(
                                'action'    => 'ab-paypal-error',
                                'ab_fid'    => $form_id,
                                'error_msg' => str_replace( ' ', '%20', $response['L_LONGMESSAGE0'] )
                            ), Lib\Utils\Common::getCurrentPageURL()
                        ) ) );
                        exit;
                    }
                } else {
                    header( 'Location: ' . wp_sanitize_redirect( add_query_arg( array(
                            'action'    => 'ab-paypal-error',
                            'ab_fid'    => $form_id,
                            'error_msg' => str_replace( ' ', '%20', $response['L_LONGMESSAGE0'] )
                        ), Lib\Utils\Common::getCurrentPageURL()
                    ) ) );
                    exit;
                }
            }
        } else {
            header( 'Location: ' . wp_sanitize_redirect( add_query_arg( array(
                    'action'    => 'ab-paypal-error',
                    'ab_fid'    => $form_id,
                    'error_msg' => str_replace( ' ', '%20', __( 'Invalid token provided', 'bookly' ) )
                ), Lib\Utils\Common::getCurrentPageURL()
                ) ) );
            exit;
        }
    }

}