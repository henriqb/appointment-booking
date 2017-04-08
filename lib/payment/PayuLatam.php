<?php
namespace Bookly\Lib\Payment;

use Bookly\Lib;

/**
 * Class PayuLatam
 */
class PayuLatam
{
    // Array for cleaning PayU Latam request
    public static $remove_parameters = array( 'merchantId', 'merchant_name', 'merchant_address', 'telephone', 'merchant_url', 'transactionState', 'lapTransactionState', 'message', 'referenceCode', 'reference_pol', 'transactionId', 'description', 'trazabilityCode', 'cus', 'orderLanguage', 'extra1', 'extra2', 'extra3', 'polTransactionState', 'signature', 'polResponseCode', 'lapResponseCode', 'risk', 'polPaymentMethod', 'lapPaymentMethod', 'polPaymentMethodType', 'lapPaymentMethodType', 'installmentsNumber', 'TX_VALUE', 'TX_TAX', 'currency', 'lng', 'pseCycle', 'buyerEmail', 'pseBank', 'pseReference1', 'pseReference2', 'pseReference3', 'authorizationCode', 'processingDate', 'form_id', 'action', 'ab_fid', 'error_msg' );
    // developers.payulatam.com/en/web_checkout/sandbox.html
    CONST SANDBOX_API_KEY = '676k86ks53la6tni6clgd30jf6';
    CONST SANDBOX_API_MERCHANT_ID = '500365';
    CONST SANDBOX_API_ACCOUNT_ID  = '500719';
    CONST APPROVED = 4;

    public static function replaceData( $form_id )
    {
        $replacement = array();
        $userData    = new Lib\UserBookingData( $form_id );
        if ( $userData->load() ) {
            if ( get_option( 'ab_payulatam_sandbox' ) == 1 ) {
                $api_key     = self::SANDBOX_API_KEY;
                $merchant_id = self::SANDBOX_API_MERCHANT_ID;
                $account_id  = self::SANDBOX_API_ACCOUNT_ID;
                $action      = 'https://stg.gateway.payulatam.com/ppp-web-gateway/';
                $test        = 1;
            } else {
                $api_key     = get_option( 'ab_payulatam_api_key' );
                $merchant_id = get_option( 'ab_payulatam_api_merchant_id' );
                $account_id  = get_option( 'ab_payulatam_api_account_id' );
                $action      = 'https://gateway.payulatam.com/ppp-web-gateway/';
                $test        = '0';
            }
            $reference_code  = '';
            for ( $i = 0; $i < 16; $i ++ ) {
                $reference_code .= chr( mt_rand( 65, 90 ) );
            }
            $cart_info = $userData->getCartInfo();
            $replacement = array(
                '%accountId%'     => $account_id,
                '%merchantId%'    => $merchant_id,
                '%referenceCode%' => $reference_code,
                '%description%'   => esc_attr( $userData->getCartItemsTitle( 255 ) ),
                '%signature%'     => md5( implode( '~', array( $api_key, $merchant_id, $reference_code, $cart_info['total_price'], get_option( 'ab_currency' ) ) ) ),
                '%amount%'        => $cart_info['total_price'],
                '%action%'        => $action,
                '%currency%'      => get_option( 'ab_currency' ),
                '%tax%'           => '0.00',
                '%test%'          => $test,
                '%buyerEmail%'    => $userData->get( 'email' ),
                '%back%'          => Lib\Utils\Common::getTranslatedOption( 'ab_appearance_text_button_back' ),
                '%next%'          => Lib\Utils\Common::getTranslatedOption( 'ab_appearance_text_button_next' ),
                '%gateway%'       => Lib\Entities\Payment::TYPE_PAYULATAM
            );
        }

        return $replacement;
    }

    public static function renderForm( $form_id )
    {
        $replacement = self::replaceData( $form_id );

        if ( ! empty( $replacement ) ) {
            $payulatam_form = '<form action="%action%" method="post" class="ab-%gateway%-form" data-gateway="%gateway%">
                <input type="hidden" name="merchantId" value="%merchantId%">
                <input type="hidden" name="accountId" value="%accountId%">
                <input type="hidden" name="description" value="%description%">
                <input type="hidden" name="referenceCode" value="%referenceCode%">
                <input type="hidden" name="amount" value="%amount%" class="ab--coupon-change-price">
                <input type="hidden" name="tax" value="%tax%">
                <input type="hidden" name="currency" value="%currency%">
                <input type="hidden" name="taxReturnBase" value="0">
                <input type="hidden" name="shipmentValue" value="0.00">
                <input type="hidden" name="signature" value="%signature%">
                <input type="hidden" name="buyerEmail" value="%buyerEmail%">
                <input type="hidden" name="discount" value="0">
                <input type="hidden" name="test" value="%test%">
                <input type="hidden" name="responseUrl" value="">
                <input type="hidden" name="extra1" value="" class="ab--pending_appointments">
                <input type="hidden" name="confirmationUrl" value="">
                <button class="ab-left ab-back-step ab-btn ladda-button" data-style="zoom-in" style="margin-right: 10px;" data-spinner-size="40"><span class="ladda-label">%back%</span></button>
                <button class="ab-right ab-next-step ab-btn ladda-button" data-style="zoom-in" data-spinner-size="40"><span class="ladda-label">%next%</span></button>
            </form>';

            echo strtr( $payulatam_form, $replacement );
        }
    }

    /**
     * Payment is Approved when signature correct and amount equal appointment price
     *
     * @param $is_sandbox
     * @param $transaction_state
     * @param $reference_code
     * @param $transaction_id
     * @param $signature
     * @return bool
     */
    public static function paymentIsApproved( $is_sandbox, $transaction_state, $reference_code, $transaction_id, $signature )
    {
        $payment_total = Lib\Entities\Payment::query( 'p' )->select( 'SUM(p.total) AS payment_total' )->whereIn( 'p.customer_appointment_id', explode( ',', stripslashes( $_REQUEST['extra1'] ) ) )->where( 'p.type', Lib\Entities\Payment::TYPE_PAYULATAM )->fetchRow();
        $total    = (float) $payment_total['payment_total'];
        $received = (float) @$_REQUEST['TX_VALUE'];
        $difference = ( $received > $total ) ? $received / $total : $total / $received;
        if ( $difference > 1.005 /* 0.5% */ ) {
            // The big difference in the expected and received payment.
            return false;
        }
        $approved = false;
        if ( $is_sandbox == 1 ) {
            $api_key     = PayuLatam::SANDBOX_API_KEY;
            $merchant_id = PayuLatam::SANDBOX_API_MERCHANT_ID;
        } else {
            $api_key     = get_option( 'ab_payulatam_api_key' );
            $merchant_id = get_option( 'ab_payulatam_api_merchant_id' );
        }
        $TX_VALUE = number_format( $received, 1, '.', '' );
        if ( $signature == md5( implode( '~', array( $api_key, $merchant_id, $reference_code, $TX_VALUE, get_option( 'ab_currency' ), $transaction_state ) ) ) ) {
            $notify   = array();
            $payments = Lib\Entities\Payment::query()->whereIn( 'customer_appointment_id', explode( ',', stripslashes( $_REQUEST['extra1'] ) ) )->where( 'type', Lib\Entities\Payment::TYPE_PAYULATAM )->find();
            foreach ( $payments as $payment ) {
                if ( $payment->get( 'status' ) == Lib\Entities\Payment::STATUS_COMPLETED ) {
                    $approved = true;
                    continue;
                }
                switch ( $transaction_state ) {
                    case self::APPROVED:
                        $approved = true;
                        $payment->set( 'status', Lib\Entities\Payment::STATUS_COMPLETED );
                        $payment->set( 'token',  $reference_code );
                        $payment->set( 'transaction_id', $transaction_id );
                        $payment->save();
                        $notify[] = $payment->get( 'customer_appointment_id' );
                        break;
                    case 6:     // Transaction rejected
                        /** @var Lib\Entities\CustomerAppointment $ca */
                        $ca = Lib\Entities\CustomerAppointment::query()->where( 'id', $payment->get( 'customer_appointment_id' ) )->findOne();
                        $ca->deleteCascade();
                        break;
                }
            }
            foreach ( Lib\Entities\CustomerAppointment::query()->whereIn( 'id', $notify )->find() as $ca ) {
                Lib\NotificationSender::send( Lib\NotificationSender::INSTANT_NEW_APPOINTMENT, $ca );
            }
        }

        return $approved;
    }

    /**
     * Handles IPN messages
     */
    public static function ipn()
    {
        self::paymentIsApproved( stripslashes( @$_REQUEST['test'] ) == 1, stripslashes( @$_REQUEST['state_pol'] ), stripslashes( @$_REQUEST['reference_sale'] ), stripslashes( @$_REQUEST['transaction_id'] ), stripslashes( @$_REQUEST['sign'] ) );
        wp_send_json_success();
    }

}