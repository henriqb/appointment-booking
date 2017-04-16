<?php
namespace Bookly\Frontend\Modules\PagSeguro;

use Bookly\Lib;

/**
 * Class Controller
 * @package Bookly\Frontend\Modules\PagSeguro
 */
class Controller extends Lib\Controller
{
    const SIGNUP = 'https://pagseguro.uol.com.br/registration/registration.jhtml';
    const HOME   = 'https://pagseguro.uol.com.br/';

    protected function getPermissions()
    {
        return array( '_this' => 'anonymous' );
    }

    public function checkout()
    {
        $form_id = $this->getParameter( 'ab_fid' );
        if ( $form_id ) {
            // Create a pagseguro object.
            $pagseguro   = new Lib\Payment\PagSeguro();
            $userData = new Lib\UserBookingData( $form_id );

            if ( $userData->load() ) {
                $cart_info = $userData->getCartInfo();
                $product = new \stdClass();
                $product->name  = $userData->getCartItemsTitle( 126 );
                $product->price = $cart_info['total_price'];
                $product->qty   = 1;
                $pagseguro->addProduct( $product );

                // and send the payment request.
                try {
                    $pagseguro->send_EC_Request( $form_id );
                } catch ( \Exception $e ) {
                    $userData->setPaymentStatus( Lib\Entities\Payment::TYPE_PAGSEGURO, 'error', $this->getParameter( 'error_msg' ) );
                    @wp_redirect( remove_query_arg( Lib\Payment\PagSeguro::$remove_parameters, Lib\Utils\Common::getCurrentPageURL() ) );
                    exit;
                }
            }
        }
    }

    /**
     * 'CANCELURL' process
     */
    public function cancel()
    {
        $userData = new Lib\UserBookingData( $this->getParameter( 'ab_fid' ) );
        $userData->load();
        $userData->setPaymentStatus( Lib\Entities\Payment::TYPE_PAGSEGURO, 'cancelled' );
        @wp_redirect( remove_query_arg( Lib\Payment\PagSeguro::$remove_parameters, Lib\Utils\Common::getCurrentPageURL() ) );
        exit;
    }

    /**
     * 'ERRORURL' process
     */
    public function error()
    {
        $userData = new Lib\UserBookingData( $this->getParameter( 'ab_fid' ) );
        $userData->load();
        $userData->setPaymentStatus( Lib\Entities\Payment::TYPE_PAGSEGURO, 'error', $this->getParameter( 'error_msg' ) );
        @wp_redirect( remove_query_arg( Lib\Payment\PagSeguro::$remove_parameters, Lib\Utils\Common::getCurrentPageURL() ) );
        exit;
    }

    /**
     * Process the Express Checkout RETURNURL TODO
     */
    public function success()
    {
        $form_id = $this->getParameter( 'ab_fid' );
        $pagseguro  = new Lib\Payment\PagSeguro();

        if ( $this->hasParameter( 'transaction' ) && $this->hasParameter('token') ) {
            // TODO Validate payment has been made
            $token = $this->getParameter('token'); 
            $transaction = $this->getParameter( 'transaction' );

            $userData = new Lib\UserBookingData( $form_id );
            $userData->load();
            $cart_info = $userData->getCartInfo();
            $payment = Lib\Entities\Payment::query( 'p' )
                ->select( 'p.id' )
                ->where( 'p.type', Lib\Entities\Payment::TYPE_PAGSEGURO )
                ->where( 'p.transaction_id', $transaction )
                ->findOne();
            if ( empty ( $payment ) ) {
                $userData->foreachCartItem( function ( Lib\UserBookingData $userData, $cart_key ) use ( $cart_info, $transaction, $token ) {
                    $customer_appointment = $userData->save();
                    $payment = new Lib\Entities\Payment();
                    $payment->set( 'customer_appointment_id', $customer_appointment->get( 'id' ) );
                    $payment->set( 'transaction_id', $transaction );
                    $payment->set( 'total',   $cart_info['items'][ $cart_key ]['total_price'] );
                    $payment->set( 'token',   $token );
                    $payment->set( 'created', current_time( 'mysql' ) );
                    $payment->set( 'type',    Lib\Entities\Payment::TYPE_PAGSEGURO );
                    $payment->set( 'status',  Lib\Entities\Payment::STATUS_COMPLETED );
                    $payment->save();
                });
            }
            $userData->setPaymentStatus( Lib\Entities\Payment::TYPE_PAGSEGURO, 'success' );

            @wp_redirect( remove_query_arg( Lib\Payment\PagSeguro::$remove_parameters, Lib\Utils\Common::getCurrentPageURL() ) );
            exit;
        } else {
            header( 'Location: ' . wp_sanitize_redirect( add_query_arg( array(
                    'action'    => 'ab-pagseguro-error',
                    'ab_fid'    => $form_id,
                    'error_msg' => str_replace( ' ', '%20', __( 'Invalid token provided', 'bookly' ) )
                ), Lib\Utils\Common::getCurrentPageURL()
                ) ) );
            exit;
        }
    }
}