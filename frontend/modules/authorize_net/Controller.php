<?php
namespace Bookly\Frontend\Modules\AuthorizeNet;

use Bookly\Lib;

/**
 * Class Controller
 * @package Bookly\Frontend\Modules\AuthorizeNet
 */
class Controller extends Lib\Controller
{
    const SIGNUP = 'https://www.authorize.net/solutions/merchantsolutions/pricing/';
    const HOME   = 'https://www.authorize.net/';

    protected function getPermissions()
    {
        return array( '_this' => 'anonymous' );
    }

    /**
     * Do AIM payment.
     */
    public function executeAuthorizeNetAIM()
    {
        include_once AB_PATH . '/lib/payment/authorize.net/autoload.php';

        $response = null;
        $userData = new Lib\UserBookingData( $this->getParameter( 'form_id' ) );

        if ( $userData->load() ) {
            $failed_cart_key = $userData->getFailedCartKey();
            if ( $failed_cart_key === null ) {
                define( 'AUTHORIZENET_API_LOGIN_ID',    get_option( 'ab_authorizenet_api_login_id' ) );
                define( 'AUTHORIZENET_TRANSACTION_KEY', get_option( 'ab_authorizenet_transaction_key' ) );
                define( 'AUTHORIZENET_SANDBOX',  (bool) get_option( 'ab_authorizenet_sandbox' ) );

                $cart_info = $userData->getCartInfo();
                $total = $cart_info['total_price'];
                $card  = $this->getParameter( 'card' );

                $sale = new \AuthorizeNetAIM();
                $sale->setField( 'amount',     $total );
                $sale->setField( 'card_num',   $card['number'] );
                $sale->setField( 'card_code',  $card['cvc'] );
                $sale->setField( 'exp_date',   $card['exp_month'] . '/' . $card['exp_year'] );
                $sale->setField( 'first_name', $userData->get( 'name' ) );
                $sale->setField( 'email',      $userData->get( 'email' ) );
                $sale->setField( 'phone',      $userData->get( 'phone' ) );

                $response = $sale->authorizeAndCapture();
                if ( $response->approved ) {
                    $payment = Lib\Entities\Payment::query( 'p' )
                        ->select( 'p.id' )
                        ->where( 'p.type', Lib\Entities\Payment::TYPE_AUTHORIZENET )
                        ->where( 'p.transaction_id', $response->transaction_id )
                        ->findOne();
                    if ( empty ( $payment ) ) {
                        $userData->foreachCartItem( function ( Lib\UserBookingData $userData, $cart_key ) use ( $cart_info, $response ) {
                            $customer_appointment = $userData->save();
                            $payment = new Lib\Entities\Payment();
                            $payment->set( 'customer_appointment_id', $customer_appointment->get( 'id' ) );
                            $payment->set( 'transaction_id', $response->transaction_id );
                            $payment->set( 'total',   $cart_info['items'][ $cart_key ]['total_price'] );
                            $payment->set( 'created', current_time( 'mysql' ) );
                            $payment->set( 'type',    Lib\Entities\Payment::TYPE_AUTHORIZENET );
                            $payment->set( 'status',  Lib\Entities\Payment::STATUS_COMPLETED );
                            $payment->save();
                        } );
                    }
                    $response = array ( 'success' => true );
                } else {
                    $response = array ( 'success' => false, 'error_code' => 7, 'error' => $response->response_reason_text );
                }
            } else {
                $response = array(
                    'success'         => false,
                    'error_code'      => 3,
                    'failed_cart_key' => $failed_cart_key,
                    'error'           => get_option( 'ab_settings_step_cart_enabled' )
                        ? __( 'The highlighted time is not available anymore. Please, choose another time slot.', 'bookly' )
                        : __( 'The selected time is not available anymore. Please, choose another time slot.', 'bookly' )
                );
            }
        } else {
            $response = array( 'success' => false, 'error_code' => 1, 'error' => __( 'Session error.', 'bookly' ) );
        }

        wp_send_json( $response );
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
        parent::registerWpActions( 'wp_ajax_nopriv_ab_' );
    }

}
