<?php
namespace Bookly\Lib\Payment;

use Bookly\Lib;

/**
 * Class PagSeguro
 * @package Bookly\Lib\Payment
 */
class PagSeguro
{
    // Array for cleaning PayPal request
    static public $remove_parameters = array( 'action', 'token', 'PayerID', 'ab_fid', 'error_msg', 'type' );

    /**
     * The array of products for checkout
     *
     * @var array
     */
    protected $products = array();

    /**
     * Send the PagSeguro Checkout request
     *
     * @param $form_id
     * @throws \Exception
     */
    public function send_EC_Request( $form_id )
    {
        if ( !session_id() ) {
            @session_start();
        }

        if ( ! count( $this->products ) ) {
            throw new \Exception( 'Products not found!' );
        }

        $total = 0;

        // create the data to send on PagSeguro
        $data =
            '&currency='  . urlencode( get_option( 'ab_currency' ) ) .
            '&redirectURL=' . urlencode( add_query_arg( array( 'action' => 'ab-pagseguro-return', 'ab_fid' => $form_id ), Lib\Utils\Common::getCurrentPageURL() ) ) ;
            //'&CANCELURL=' . urlencode( add_query_arg( array( 'action' => 'ab-pagseguro-cancel', 'ab_fid' => $form_id ), Lib\Utils\Common::getCurrentPageURL() ) );

        foreach ( $this->products as $index => $product ) {
            $number = $index+1;
            $data .=
                "&itemId{$number}={$index}" .
                "&itemDescription{$number}=" . urlencode( $product->name ) .
                "&itemAmount{$number}=" . urlencode( number_format($product->price, 2) ) .
                "&itemQuantity{$number}=" . urlencode( $product->qty );

            $total += ( $product->qty * $product->price );
        }
        // send the request to PagSeguro
        $response = self::sendNvpRequest( $data );
        
        // Respond according to message we receive from PagSeguro
        if ( $response->code != '' ) {
            //$pagsegurourl = 'https://' . ltrim( get_option( 'ab_pagseguro_ec_mode' ) , '.') . '.pagseguro.uol.com.br/v2/checkout/payment.html?code=' . urldecode( $response->code );
            //header( 'Location: ' . $pagsegurourl );
            echo urldecode( $response->code );;
            exit;
        } else {
            header( 'Location: ' . wp_sanitize_redirect( add_query_arg( array( 'action' => 'ab-pagseguro-error', 'ab_fid' => $form_id, 'error_msg' => $response[0] ), Lib\Utils\Common::getCurrentPageURL() ) ) );
            exit;
        }
    }

    /**
     * Send the Request to PagSeguro
     *
     * @param $nvpStr
     * @return array
     */
    public function sendNvpRequest( $nvpStr )
    {
        $email   = urlencode( get_option( 'ab_pagseguro_api_email' ) );
        $token   = urlencode( get_option( 'ab_pagseguro_api_token' ) );       

        $url = 'https://ws' . get_option( 'ab_pagseguro_ec_mode' ) . '.pagseguro.uol.com.br/v2/checkout/';

        // Set the curl parameters.
        $ch = curl_init();
        curl_setopt( $ch, CURLOPT_URL, $url );
        curl_setopt( $ch, CURLOPT_VERBOSE, 1 );

        // Turn off the server and peer verification (TrustManager Concept).
        curl_setopt( $ch, CURLOPT_SSL_VERIFYPEER, false );
        curl_setopt( $ch, CURLOPT_SSL_VERIFYHOST, false );

        curl_setopt( $ch, CURLOPT_RETURNTRANSFER, 1 );
        curl_setopt( $ch, CURLOPT_POST, 1 );

        // Set the API operation and API signature in the request.
        $nvpreq = "token={$token}&email={$email}{$nvpStr}";

        // Set the request as a POST FIELD for curl.
        curl_setopt( $ch, CURLOPT_POSTFIELDS, $nvpreq );

        // Get response from the server.
        $httpResponse = curl_exec( $ch );

        if ( ! $httpResponse ) {
            exit( ' Send PagSeguro failed: ' . curl_error( $ch ) . '(' . curl_errno( $ch ) . ')' );
        }

        // Extract the response details.
        $simpleXMLElement = simplexml_load_string($httpResponse);
        
        if ( !isset($simpleXMLElement->code) || trim($simpleXMLElement->code) === ''  ) {
            exit( "Invalid HTTP Response for POST request($nvpreq) to $url." );
        }

        return $simpleXMLElement;
    }

    public static function renderForm( $form_id )
    {
        $html = '<form method="post" class="ab-pagseguro-form">';
        $html .= '<input type="hidden" name="action" value="ab-pagseguro-checkout"/>';
        $html .= '<input type="hidden" name="ab_fid" value="' . $form_id . '"/>';
        $html .= '<button class="ab-left ab-back-step ab-btn ladda-button" data-style="zoom-in" style="margin-right: 10px;" data-spinner-size="40"><span class="ladda-label">' . Lib\Utils\Common::getTranslatedOption( 'ab_appearance_text_button_back' ) . '</span></button>';
        $html .= '<button class="ab-right ab-next-step ab-btn ladda-button" data-style="zoom-in" data-spinner-size="40"><span class="ladda-label">' . Lib\Utils\Common::getTranslatedOption( 'ab_appearance_text_button_next' ) . '</span></button>';
        $html .= '</form>';
        echo $html;
    }

    /**
     * @return array
     */
    public function getProducts()
    {
        return $this->products;
    }
    /**
     * Add the Product for payment
     *
     * @param \stdClass $product
     */
    public function addProduct( \stdClass $product )
    {
        $this->products[] = $product;
    }
}