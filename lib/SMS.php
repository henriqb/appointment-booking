<?php
namespace Bookly\Lib;

/**
 * Class SMS
 * @package Bookly\Lib
 */
class SMS
{
    const API_URL = 'http://sms.booking-wp-plugin.com/1.0';

    const REGISTER            = '/users.json';                              //POST
    const AUTHENTICATE        = '/users.json';                              //GET
    const LOG_OUT             = '/users/%token%/logout';                    //GET
    const GET_PROFILE_INFO    = '/users/%token%.json';                      //GET
    const GET_SMS_LIST        = '/users/%token%/sms.json';                  //GET
    const GET_PURCHASES_LIST  = '/users/%token%/purchases.json';            //GET
    const SEND_SMS            = '/users/%token%/sms.json';                  //POST
    const GET_PRICES          = '/prices.json';                             //GET
    const PASSWORD_FORGOT     = '/recoveries.json';                         //POST
    const PASSWORD_CHANGE     = '/users/%token%.json';                      //PATCH
    const PREAPPROVAL_CREATE  = '/users/%token%/paypal/preapproval.json';   //POST
    const PREAPPROVAL_DELETE  = '/users/%token%/paypal/preapproval.json';   //DELETE

    private $username;

    private $token;

    private $balance;

    private $errors = array();

    public function __construct()
    {
        $this->token    = get_option( 'ab_sms_token' );
        $this->username = get_option( 'ab_sms_username' );
    }

    /**
     * Register new account.
     *
     * @param string $username
     * @param string $password
     * @param string $password_repeat
     * @return bool
     */
    public function register( $username, $password, $password_repeat )
    {
        $data = array( '_username' => $username, '_password' => $password );

        if ( $password !== $password_repeat && ! empty( $password ) ) {
            $this->errors[] = __( 'Passwords must be the same.', 'bookly' );

            return false;
        }

        $response = $this->sendPostRequest( self::REGISTER, $data );
        if ( $response  ) {
            update_option( 'ab_sms_username', $response->username );
            update_option( 'ab_sms_token',    $response->token );
            $this->token    = $response->token;
            $this->username = $response->username;
            $this->balance  = $response->balance;

            return true;
        }

        return false;
    }

    /**
     * Log in.
     *
     * @param string $username
     * @param string $password
     * @return bool
     */
    public function login( $username, $password )
    {
        $data = array( '_username' => $username, '_password' => $password );

        $response = $this->sendGetRequest( self::AUTHENTICATE, $data );
        if ( $response ) {
            update_option( 'ab_sms_username', $response->username );
            update_option( 'ab_sms_token',    $response->token );
            $this->token    = $response->token;
            $this->username = $response->username;
            $this->balance  = $response->balance;

            return true;
        }

        return false;
    }

    /**
     * Change password.
     *
     * @param string $new_password
     * @param string $old_password
     * @return bool
     */
    public function changePassword( $new_password, $old_password )
    {
        $data = array( '_old_password' => $old_password, '_new_password' => $new_password );

        $response = $this->sendPatchRequest( self::PASSWORD_CHANGE, $data );
        if ( $response ) {

            return true;
        }

        return false;
    }

    /**
     * Log out.
     */
    public function logout()
    {
        update_option( 'ab_sms_username', '' );
        update_option( 'ab_sms_token', '' );

        if ( $this->token ) {
            $this->sendGetRequest( self::LOG_OUT );
        }
        $this->token = $this->username = null;
    }

    /**
     * Get PayPal Preapproval key, (for enabling auto recharge)
     *
     * @param $amount
     * @return bool
     */
    public function getPreapprovalKey( $amount )
    {
        if ( $this->token ) {
            $response = $this->sendPostRequest(
                self::PREAPPROVAL_CREATE,
                array(
                    'amount'   => $amount,
                    'approved' => admin_url( 'admin.php?page=' . \Bookly\Backend\Modules\Sms\Controller::page_slug . '&tab=auto_recharge&auto-recharge=approved' ),
                    'declined' => admin_url( 'admin.php?page=' . \Bookly\Backend\Modules\Sms\Controller::page_slug . '&tab=auto_recharge&auto-recharge=declined' ),
                )
            );
            if ( $response ) {
                return $response->preapprovalKey;
            }
        }

        return false;
    }

    /**
     * Decline PayPal Preapproval. (disable auto recharge)
     *
     * @return bool
     */
    public function declinePreapproval()
    {
        if ( $this->token ) {
            $response = $this->sendDeleteRequest( self::PREAPPROVAL_DELETE, array() );
            if ( $response ) {

                return true;
            }
        }

        return false;
    }

    /**
     * @param $phone_number
     * @param $message
     * @return bool
     */
    public function sendSms( $phone_number, $message )
    {
        if ( $this->token ) {
            $data = array(
                'phone'    => $this->normalizePhoneNumber( $phone_number ),
                'message'  => $message,
            );
            if ( $data['phone'] != '' ) {
                $response = $this->sendPostRequest( self::SEND_SMS, $data );
                if ( $response ) {
                    if ( property_exists( $response, 'notify_low_balance' ) && get_option( 'ab_sms_notify_low_balance' ) ) {
                        if ( $response->notify_low_balance ) {
                            $this->_sendLowBalanceNotification();
                        }
                    }
                    if ( property_exists( $response, 'gateway_status' ) ) {
                        // array description on this->getSmsList
                        if ( in_array( $response->gateway_status, array( 1, 10, 11, 12, 13 ) ) ) {

                            return true;
                        }
                    }
                }
            } else {
                $this->errors[] = __( 'Phone number is empty.', 'bookly' );
            }
        }

        return false;
    }

    /**
     * Return phone_number in international format without +
     *
     * @param $phone_number
     * @return mixed
     */
    public function normalizePhoneNumber( $phone_number )
    {
        // Remove everything except numbers and "+".
        $phone_number = preg_replace( '/[^\d\+]/', '', $phone_number );

        if ( $phone_number{0} == '+' ) {
            // ok.
        } elseif ( strpos( $phone_number, '00' ) === 0 ) {
            $phone_number = substr( $phone_number, 2 );
        } else {
            $phone_number = get_option( 'ab_sms_default_country_code', '' ) . ltrim( $phone_number, '0' );
        }

        // Remove everything except numbers again (default country code can contain not permitted characters).
        return ltrim( preg_replace( '/\D/', '', $phone_number ), '0' );
    }

    /**
     * Load user profile info.
     *
     * @return bool
     */
    public function loadProfile()
    {
        if ( $this->token ) {
            $response = $this->sendGetRequest( self::GET_PROFILE_INFO );
            if ( $response ) {
                $this->balance = $response->balance;
                update_option( 'ab_sms_auto_recharge_balance', $response->auto_recharge->enabled );
                if ( $response->auto_recharge->enabled ) {
                    update_option( 'ab_sms_auto_recharge_amount', $response->auto_recharge->amount );
                }

                return true;
            }
        }

        return false;
    }

    /**
     * User forgot password for sms
     *
     * @param null $username
     * @param null $step
     * @param null $code
     * @param null $password
     *
     * @return array|mixed
     */
    public function forgotPassword( $username = null, $step = null, $code = null, $password = null )
    {
        $data = array( '_username' => $username, 'step' => $step );
        switch ( $step ) {
            case 0:
                break;
            case 1:
                $data['code'] = $code;
                break;
            case 2:
                $data['code'] = $code;
                $data['password'] = $password;
                break;
        }
        $response = $this->sendPostRequest( self::PASSWORD_FORGOT, $data );

        if ( $response ) {

            return $response;
        }

        return false;
    }

    /**
     * Get purchases list.
     *
     * @param null $start_date
     * @param null $end_date
     * @return array|mixed
     */
    public function getPurchasesList( $start_date = null, $end_date = null )
    {
        if ( $this->token ) {
            $response = $this->sendGetRequest(
                self::GET_PURCHASES_LIST,
                array( 'start_date' => $start_date, 'end_date' => $end_date )
            );
            if ( $response ) {
                array_walk( $response->list, function( &$item ) {
                    $date_time  = Utils\DateTime::UTCToWPTimeZone( $item->datetime );
                    $item->date = Utils\DateTime::formatDate( $date_time );
                    $item->time = Utils\DateTime::formatTime( $date_time );
                } );

                return $response;
            }
        }

        return array();
    }

    /**
     * Get SMS list.
     *
     * @param null $start_date
     * @param null $end_date
     * @return array|mixed
     */
    public function getSmsList( $start_date = null, $end_date = null )
    {
        if ( $this->token ) {
            $response = $this->sendGetRequest(
                self::GET_SMS_LIST,
                array( 'start_date' => $start_date, 'end_date' => $end_date )
            );
            if ( $response ) {
                array_walk( $response->list, function( &$item ) {
                    $date_time  = Utils\DateTime::UTCToWPTimeZone( $item->datetime );
                    $item->date = Utils\DateTime::formatDate( $date_time );
                    $item->time = Utils\DateTime::formatTime( $date_time );
                    $item->message = nl2br( $item->message );
                    $item->phone   = '+' . $item->phone;
                    $item->charge  = rtrim( $item->charge, '0' );
                    switch ( $item->status ) {
                        case 1:
                        case 10:
                            $item->status = __( 'Queued', 'bookly' );
                            $item->charge = '$' . $item->charge;
                            break;
                        case 2:
                        case 16:
                            $item->status = __( 'Error', 'bookly' );
                            $item->charge = '';
                            break;
                        case 3:
                            $item->status = __( 'Out of credit', 'bookly' );
                            $item->charge = '';
                            break;
                        case 4:
                            $item->status = __( 'Country out of service', 'bookly' );
                            $item->charge = '';
                            break;
                        case 11:
                            $item->status = __( 'Sending', 'bookly' );
                            $item->charge = '$' . $item->charge;
                            break;
                        case 12:
                            $item->status = __( 'Sent', 'bookly' );
                            $item->charge = '$' . $item->charge;
                            break;
                        case 13:
                            $item->status = __( 'Delivered', 'bookly' );
                            $item->charge = '$' . $item->charge;
                            break;
                        case 14:
                            $item->status = __( 'Failed', 'bookly' );
                            $item->charge = '$' . $item->charge;
                            break;
                        case 15:
                            $item->status = __( 'Undelivered', 'bookly' );
                            $item->charge = '$' . $item->charge;
                            break;
                        default:
                            $item->status = __( 'Error', 'bookly' );
                            $item->charge = '';
                    }
                } );

                return $response;
            }
        }

        return array();
    }

    /**
     * Get Price list.
     *
     * @return array|mixed
     */
    public function getPriceList()
    {
        $response = $this->sendGetRequest( self::GET_PRICES );
        if ( $response ) {

            return $response;
        }

        return array();
    }

    /**
     * Send GET request.
     *
     * @param $path
     * @param array $data
     * @return mixed
     */
    private function sendGetRequest( $path, array $data = array() )
    {
        $url = self::API_URL . str_replace( '%token%', $this->token, $path );

        return $this->_handleResponse( $this->_sendRequest( 'GET', $url, $data ) );
    }

    /**
     * Send POST request.
     *
     * @param $path
     * @param array $data
     * @return mixed
     */
    private function sendPostRequest( $path, array $data )
    {
        $url = self::API_URL . str_replace( '%token%', $this->token, $path );

        return $this->_handleResponse( $this->_sendRequest( 'POST', $url, $data ) );
    }

    /**
     * Send PATCH request.
     *
     * @param $path
     * @param array $data
     * @return mixed
     */
    private function sendPatchRequest( $path, array $data )
    {
        $url = self::API_URL . str_replace( '%token%', $this->token, $path );

        return $this->_handleResponse( $this->_sendRequest( 'PATCH', $url, $data ) );
    }

    /**
     * Send DELETE request.
     *
     * @param $path
     * @param array $data
     * @return mixed
     */
    private function sendDeleteRequest( $path, array $data )
    {
        $url = self::API_URL . str_replace( '%token%', $this->token, $path );

        return $this->_handleResponse( $this->_sendRequest( 'DELETE', $url, $data ) );
    }

    /**
     * Send HTTP request.
     *
     * @param $method
     * @param $url
     * @param $data
     *
     * @return mixed
     */
    private function _sendRequest( $method, $url, $data )
    {
        $curl = new Curl\Curl();
        $curl->options['CURLOPT_CONNECTTIMEOUT'] = 8;
        $curl->options['CURLOPT_TIMEOUT']        = 30;

        $method   = strtolower( $method );
        $response = $curl->$method( $url, $data );
        $error = $curl->error();
        if ( $error ) {
            $this->errors[] = $error;
        }

        return $response;
    }

    /**
     * Check response for errors.
     *
     * @param mixed $response
     * @return mixed
     */
    private function _handleResponse( $response )
    {
        $response = json_decode( $response );

        if ( $response !== null && property_exists( $response, 'success' ) ) {
            if ( $response->success == true ) {

                return $response;
            }
            $this->errors[] = $this->translateError( $response->message );
        } else {
            $this->errors[] = __( 'Error connecting to server.', 'bookly' );
        }

        return false;
    }

    /**
     * Send notification to administrators about low balance.
     */
    private function _sendLowBalanceNotification()
    {
        $add_money_url = admin_url( 'admin.php?' . build_query( array( 'page' => \Bookly\Backend\Modules\Sms\Controller::page_slug, 'tab' => 'add_money' ) ) );
        $message = sprintf( __( "Dear Bookly SMS customer.\nWe would like to notify you that your Bookly SMS balance fell lower than 5 USD. To use our service without interruptions please recharge your balance by visiting Bookly SMS page <a href='%s'>here</a>.\n\nIf you want to stop receiving these notifications, please update your settings <a href='%s'>here</a>.", 'bookly' ), $add_money_url, $add_money_url );

        wp_mail(
            Utils\Common::getAdminEmails(),
            __( 'Bookly SMS - Low Balance', 'bookly' ),
            get_option( 'ab_email_content_type' ) == 'plain' ? $message : wpautop( $message ),
            Utils\Common::getEmailHeaders()
        );
    }

    public function getUserName()
    {
        return $this->username;
    }

    public function getBalance()
    {
        return $this->balance;
    }

    /**
     * @return array
     */
    public function getErrors()
    {
        return $this->errors;
    }

    /**
     * @return array
     */
    public function clearErrors()
    {
        $this->errors = array();
    }

    /**
     * Translate error message.
     *
     * @param string $error_code
     * @return string
     */
    private function translateError( $error_code )
    {
        $error_codes = array(
            'ERROR_RECOVERY_CODE_EXPIRED'          => __( 'Recovery code expired.', 'bookly' ),
            'ERROR_EMPTY_PASSWORD'                 => __( 'Empty password.', 'bookly' ),
            'ERROR_INCORRECT_PASSWORD'             => __( 'Incorrect password.', 'bookly' ),
            'ERROR_INCORRECT_RECOVERY_CODE'        => __( 'Incorrect recovery code.', 'bookly' ),
            'ERROR_INCORRECT_USERNAME_OR_PASSWORD' => __( 'Incorrect email or password.', 'bookly' ),
            'ERROR_INVALID_USERNAME'               => __( 'Invalid email.', 'bookly' ),
            'ERROR_SENDING_EMAIL'                  => __( 'Error sending email.', 'bookly' ),
            'ERROR_USER_NOT_FOUND'                 => __( 'User not found.', 'bookly' ),
            'ERROR_USERNAME_ALREADY_EXISTS'        => __( 'Email already in use.', 'bookly' ),
        );
        if ( array_key_exists( $error_code, $error_codes ) ) {
            $message = $error_codes[ $error_code ];
        } else {
            // Build message from error code.
            $message = __( ucfirst( strtolower ( str_replace( '_', ' ', substr( $error_code, 6 ) ) ) ), 'bookly' );
        }

        return $message;
    }

}