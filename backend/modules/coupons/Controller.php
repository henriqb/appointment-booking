<?php
namespace Bookly\Backend\Modules\Coupons;

use Bookly\Lib;

/**
 * Class Controller
 * @package Bookly\Backend\Modules\Coupons
 */
class Controller extends Lib\Controller
{
    /**
     * Default action
     */
    public function index()
    {
        $this->enqueueStyles( array(
            'backend' => array(
                'css/bookly.main-backend.css',
                'bootstrap/css/bootstrap.min.css',
            ),
            'module'  => array( 'css/coupons.css' )
        ) );

        $this->enqueueScripts( array(
            'backend' => array(
                'js/ab_popup.js' => array( 'jquery' ),
                'bootstrap/js/bootstrap.min.js' => array( 'jquery' ),
            ),
            'module'  => array( 'js/coupons.js' => array( 'jquery' ) )
        ) );

        wp_localize_script( 'ab-coupons.js', 'BooklyL10n', array(
            'are_you_sure' => __( 'Are you sure?', 'bookly' ),
            'please_select_at_least_one_coupon' => __( 'Please select at least one coupon.', 'bookly' ),
        ) );

        $coupons_collection  = $this->getCouponsCollection();
        $this->render( 'index', compact( 'coupons_collection' ) );
    }

    /**
     * Add new coupon
     */
    public function executeAddCoupon()
    {
        $form = new Forms\Coupon();
        $form->bind( $this->getPostParameters() );

        $form->save();

        $coupons_collection  = $this->getCouponsCollection();
        $this->render( '_list', compact( 'coupons_collection' ) );
        exit;
    }

    /**
     * Update coupon
     */
    public function executeUpdateCouponValue()
    {
        $form = new Forms\Coupon();
        $form->bind( $this->getPostParameters() );

        if ( $this->getParameter( 'discount' ) < 0 || $this->getParameter( 'discount' ) > 100 ) {
            wp_send_json_error( array ( 'message' => __( 'Discount should be between 0 and 100.', 'bookly' ) ) );
        } elseif ( $this->getParameter( 'deduction' ) < 0 ) {
            wp_send_json_error( array ( 'message' => __( 'Deduction should be a positive number.', 'bookly' ) ) );
        } else {
            $form->save();
            $coupons_collection  = $this->getCouponsCollection();
            wp_send_json_success( array ( 'html'  => $this->render( '_list', compact( 'coupons_collection' ), false ) ) );
        }
    }

    /**
     * Remove coupon
     */
    public function executeRemoveCoupon()
    {
        $coupon_ids = $this->getParameter( 'coupon_ids', array() );
        if ( is_array( $coupon_ids ) && ! empty( $coupon_ids ) ) {
            $coupon_ids = array_map( 'intval', $coupon_ids );
            Lib\Entities\Coupon::query()->delete()->whereIn( 'id', $coupon_ids )->execute();
        }
    }

    /**
     * @return mixed
     */
    private function getCouponsCollection()
    {
        return Lib\Entities\Coupon::query()->fetchArray();
    }

    // Protected methods.

    /**
     * Override parent method to add 'wp_ajax_ab_' prefix
     * so current 'execute*' methods look nicer.
     *
     * @param string $prefix
     */
    protected function registerWpActions( $prefix = '' )
    {
        parent::registerWpActions( 'wp_ajax_ab_' );
    }

}