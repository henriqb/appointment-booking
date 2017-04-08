<?php
namespace Bookly\Frontend\Modules\TwoCheckout;

use Bookly\Lib;

/**
 * Class Controller
 * @package Bookly\Frontend\Modules\TwoCheckout
 */
class Controller extends Lib\Controller
{
    const SIGNUP = 'https://www.2checkout.com/signup/';
    const HOME   = 'https://www.2checkout.com/';

    protected function getPermissions()
    {
        return array( '_this' => 'anonymous' );
    }

    public function approved()
    {
        $userData = new Lib\UserBookingData( $this->getParameter( 'ab_fid' ) );
        if ( ( $redirect = $this->getParameter( 'x_receipt_link_url', false ) ) === false ) {
            // Clean GET parameters from 2Checkout.
            $redirect = remove_query_arg( Lib\Payment\TwoCheckout::$remove_parameters, Lib\Utils\Common::getCurrentPageURL() );
        }
        if ( $userData->load() ) {
            $cart_info = $userData->getCartInfo();
            $total = number_format( $cart_info['total_price'], 2, '.', '' );
            $StringToHash = strtoupper( md5( get_option( 'ab_2checkout_api_secret_word' ) . get_option( 'ab_2checkout_api_seller_id' ) . $this->getParameter( 'order_number' ) . $total ) );
            if ( $StringToHash != $this->getParameter( 'key' ) ) {
                header( 'Location: ' . wp_sanitize_redirect( add_query_arg( array(
                        'action'    => 'ab-2checkout-error',
                        'ab_fid'    => $this->getParameter( 'ab_fid' ),
                        'error_msg' => str_replace( ' ', '%20', __( 'Invalid token provided', 'bookly' ) )
                    ), Lib\Utils\Common::getCurrentPageURL()
                ) ) );
                exit;
            } else {
                $transaction_id = $this->getParameter( 'order_number' );
                $payment = Lib\Entities\Payment::query( 'p' )
                    ->select( 'p.id' )
                    ->where( 'p.type', Lib\Entities\Payment::TYPE_2CHECKOUT )
                    ->where( 'transaction_id', $transaction_id )
                    ->findOne();
                if ( empty ( $payment ) ) {
                    $token = $this->getParameter( 'invoice_id' );
                    $userData->foreachCartItem( function ( Lib\UserBookingData $userData, $cart_key ) use ( $cart_info, $transaction_id, $token ) {
                        $customer_appointment = $userData->save();
                        $payment = new Lib\Entities\Payment();
                        $payment->set( 'customer_appointment_id', $customer_appointment->get( 'id' ) );
                        $payment->set( 'transaction_id', $transaction_id );
                        $payment->set( 'total',   $cart_info['items'][ $cart_key ]['total_price'] );
                        $payment->set( 'token',   $token );
                        $payment->set( 'created', current_time( 'mysql' ) );
                        $payment->set( 'type',    Lib\Entities\Payment::TYPE_2CHECKOUT );
                        $payment->set( 'status',  Lib\Entities\Payment::STATUS_COMPLETED );
                        $payment->save();
                    } );
                }

                $userData->setPaymentStatus( Lib\Entities\Payment::TYPE_2CHECKOUT, 'success' );

                @wp_redirect( $redirect );
                exit;
            }
        } else {
            header( 'Location: ' . wp_sanitize_redirect( add_query_arg( array(
                    'action'    => 'ab-2checkout-error',
                    'ab_fid'    => $this->getParameter( 'ab_fid' ),
                    'error_msg' => str_replace( ' ', '%20', __( 'Invalid session', 'bookly' ) )
                ), $redirect
            ) ) );
            exit;
        }
    }

    public function error()
    {
        $userData = new Lib\UserBookingData( $this->getParameter( 'ab_fid' ) );
        $userData->load();
        $userData->setPaymentStatus( Lib\Entities\Payment::TYPE_2CHECKOUT, 'error', $this->getParameter( 'error_msg' ) );
        @wp_redirect( remove_query_arg( Lib\Payment\TwoCheckout::$remove_parameters, Lib\Utils\Common::getCurrentPageURL() ) );
        exit;
    }

}