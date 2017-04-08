<?php
namespace Bookly\Lib\Entities;

use Bookly\Lib;

/**
 * Class Coupon
 * @package Bookly\Lib\Entities
 */
class Coupon extends Lib\Entity
{
    protected static $table = 'ab_coupons';

    protected static $schema = array(
        'id'          => array( 'format' => '%d' ),
        'code'        => array( 'format' => '%s', 'default' => '' ),
        'discount'    => array( 'format' => '%d', 'default' => 0 ),
        'deduction'   => array( 'format' => '%d', 'default' => 0 ),
        'usage_limit' => array( 'format' => '%d', 'default' => 1 ),
        'used'        => array( 'format' => '%d', 'default' => 0 ),
    );

    /**
     * Apply coupon.
     *
     * @param $price
     * @return float
     */
    public function apply( $price )
    {
        return round( $price * ( 100 - $this->get( 'discount' ) ) / 100 - $this->get( 'deduction' ), 2 );
    }

    /**
     * Increase the number of times the coupon has been used.
     *
     * @param int $quantity
     */
    public function claim( $quantity = 1 )
    {
        $this->set( 'used', $this->get( 'used' ) + $quantity );
    }

}
