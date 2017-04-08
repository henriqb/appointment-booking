<?php
namespace Bookly\Lib\Entities;

use Bookly\Lib;

/**
 * Class Payment
 * @package Bookly\Lib\Entities
 */
class Payment extends Lib\Entity
{
    const TYPE_LOCAL        = 'local';
    const TYPE_COUPON       = 'coupon';  // when price reduced to zero due to coupon
    const TYPE_PAYPAL       = 'paypal';
    const TYPE_STRIPE       = 'stripe';
    const TYPE_AUTHORIZENET = 'authorizeNet';
    const TYPE_2CHECKOUT    = '2checkout';
    const TYPE_PAYULATAM    = 'payulatam';
    const TYPE_PAYSON       = 'payson';
    const TYPE_MOLLIE       = 'mollie';

    const STATUS_COMPLETED  = 'completed';
    const STATUS_PENDING    = 'pending';

    protected static $table = 'ab_payments';

    protected static $schema = array(
        'id'                      => array( 'format' => '%d' ),
        'created'                 => array( 'format' => '%s' ),
        'type'                    => array( 'format' => '%s' ),
        'token'                   => array( 'format' => '%s', 'default' => '' ),
        'transaction_id'          => array( 'format' => '%s', 'default' => '' ),
        'total'                   => array( 'format' => '%.2f' ),
        'customer_appointment_id' => array( 'format' => '%d' ),
        'status'                  => array( 'format' => '%s','default' => self::STATUS_COMPLETED ),
    );

    /**
     * Get display name for given payment type.
     *
     * @param string $type
     * @return string
     */
    public static function typeToString( $type )
    {
        switch ( $type ) {
            case self::TYPE_PAYPAL:       return 'PayPal';
            case self::TYPE_LOCAL:        return __( 'Local', 'bookly' );
            case self::TYPE_STRIPE:       return 'Stripe';
            case self::TYPE_AUTHORIZENET: return 'Authorize.Net';
            case self::TYPE_2CHECKOUT:    return '2Checkout';
            case self::TYPE_PAYULATAM:    return 'PayU Latam';
            case self::TYPE_PAYSON:       return 'Payson';
            case self::TYPE_MOLLIE:       return 'Mollie';
            case self::TYPE_COUPON:       return __( 'Coupon', 'bookly' );
            default:                      return '';
        }
    }

    /**
     * Get status of payment.
     *
     * @param string $status
     * @return string
     */
    public static function statusToString( $status )
    {
        switch ( $status ) {
            case self::STATUS_COMPLETED:  return __( 'Completed', 'bookly' );
            case self::STATUS_PENDING:    return __( 'Pending',   'bookly' );
            default:                      return '';
        }
    }

}