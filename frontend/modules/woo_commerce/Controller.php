<?php
namespace Bookly\Frontend\Modules\WooCommerce;

use Bookly\Lib;

/**
 * Class Controller
 * @package Bookly\Frontend\Modules\WooCommerce
 */
class Controller extends Lib\Controller
{
    private $product_id = 0;
    private $checkout_info = array();

    protected function getPermissions()
    {
        return array(
            '_this' => 'anonymous',
        );
    }

    public function __construct()
    {
        $this->product_id = get_option( 'ab_woocommerce_product', 0 );

        add_action( 'woocommerce_get_item_data',           array( $this, 'getItemData' ), 10, 2 );
        add_action( 'woocommerce_payment_complete',        array( $this, 'paymentComplete' ) );
        add_action( 'woocommerce_order_status_completed',  array( $this, 'paymentComplete' ) );
        add_action( 'woocommerce_thankyou',                array( $this, 'paymentComplete' ) );
        add_action( 'woocommerce_add_order_item_meta',     array( $this, 'addOrderItemMeta' ), 10, 3 );
        add_action( 'woocommerce_before_calculate_totals', array( $this, 'beforeCalculateTotals' ) );
        add_action( 'woocommerce_after_order_itemmeta',    array( $this, 'orderItemMeta' ) );
        add_action( 'woocommerce_order_item_meta_end',     array( $this, 'orderItemMeta' ) );

        add_filter( 'woocommerce_quantity_input_args',     array( $this, 'quantityArgs' ), 10, 2 );
        add_filter( 'woocommerce_before_cart_contents',    array( $this, 'checkAvailableTimeForCart' ) );
        add_filter( 'woocommerce_checkout_get_value',      array( $this, 'checkoutValue' ), 10, 2 );

        parent::__construct();
    }

    /**
     * Verifies the availability of all appointments that are in the cart
     */
    public function checkAvailableTimeForCart()
    {
        $recalculate_totals = false;
        foreach ( WC()->cart->get_cart() as $cart_item_key => $cart_item ) {
            if ( array_key_exists( 'bookly', $cart_item ) ) {
                $userData = new Lib\UserBookingData( null );
                $userData->setData( $cart_item['bookly'] );
                $userData->setCartKey( null );
                if ( $cart_item['quantity'] > 1 ) {
                    // Equal appointments increase quantity
                    $userData->set( 'number_of_persons', $userData->get( 'number_of_persons' ) * $cart_item['quantity'] );
                }
                // Check if appointment's time is still available
                if ( $userData->getFailedCartKey() !== null ) {
                    $notice = strtr( __( 'Sorry, the time slot %date_time% for %service% has been already occupied.', 'bookly' ),
                        array(
                            '%service%'   => '<strong>' . $userData->getCartService()->getTitle() . '</strong>',
                            '%date_time%' => Lib\Utils\DateTime::formatDateTime( $userData->get( 'appointment_datetime' ) )
                    ) );
                    wc_print_notice( $notice, 'notice' );
                    WC()->cart->set_quantity( $cart_item_key, 0, false );
                    $recalculate_totals = true;
                }
            }
        }
        if ( $recalculate_totals ) {
            WC()->cart->calculate_totals();
        }
    }

    /**
     * Assign checkout value from appointment.
     *
     * @param $null
     * @param $field_name
     *
     * @return string|null
     */
    public function checkoutValue( $null, $field_name )
    {
        if ( empty( $this->checkout_info ) ) {
            foreach ( WC()->cart->get_cart() as $cart_item_key => $cart_item ) {
                if ( array_key_exists( 'bookly', $cart_item ) ) {
                    $full_name           = $cart_item['bookly']['name'];
                    $this->checkout_info = array(
                        'billing_first_name' => strtok( $full_name, ' ' ),
                        'billing_last_name'  => strtok( '' ),
                        'billing_email'      => $cart_item['bookly']['email'],
                        'billing_phone'      => $cart_item['bookly']['phone']
                    );
                    break;
                }
            }
        }
        if ( array_key_exists( $field_name, $this->checkout_info ) ) {
            return $this->checkout_info[ $field_name ];
        }

        return null;
    }

    /**
     * Do bookings after checkout.
     *
     * @param $order_id
     */
    public function paymentComplete( $order_id )
    {
        $order = new \WC_Order( $order_id );
        foreach ( $order->get_items() as $item_id => $order_item ) {
            $data = wc_get_order_item_meta( $item_id, 'bookly' );
            if ( $data && ! isset ( $data['processed'] ) ) {
                $book = new Lib\UserBookingData( null );
                $book->setData( $data );
                $book->setCartKey( null );
                if ( $order_item['qty'] > 1 ) {
                    // Equal appointments increase qty
                    $book->set( 'number_of_persons', $book->get( 'number_of_persons' ) * $order_item['qty'] );
                }
                $book->save();
                // Mark item as processed.
                $data['processed'] = true;
                wc_update_order_item_meta( $item_id, 'bookly', $data );
            }
        }
    }

    /**
     * Change attr for WC quantity input
     *
     * @param $args
     * @param $product
     *
     * @return mixed
     */
    function quantityArgs( $args, $product )
    {
        if ( $product->id == $this->product_id ) {
            $args['max_value'] = $args['input_value'];
            $args['min_value'] = $args['input_value'];
        }

        return $args;
    }

    /**
     * Change item price in cart.
     *
     * @param $cart_object
     */
    public function beforeCalculateTotals( $cart_object )
    {
        foreach ( $cart_object->cart_contents as $key => $value ) {
            if ( isset ( $value['bookly'] ) ) {
                $userData = new Lib\UserBookingData( null );
                $userData->setData( $value['bookly'] );
                $cart_info = $userData->getCartInfo();
                $value['data']->price = $cart_info['total_price'];
            }
        }
    }

    public function addOrderItemMeta( $item_id, $values, $cart_item_key )
    {
        if ( isset ( $values['bookly'] ) ) {
            wc_update_order_item_meta( $item_id, 'bookly', $values['bookly'] );
        }
    }

    /**
     * Get item data for cart.
     *
     * @param $other_data
     * @param $cart_item
     *
     * @return array
     */
    function getItemData( $other_data, $cart_item )
    {
        if ( isset ( $cart_item['bookly'] ) ) {
            $info_name  = get_option( 'ab_woocommerce_cart_info_name' );
            $info_value = get_option( 'ab_woocommerce_cart_info_value' );
            $userData = new Lib\UserBookingData( null );
            $userData->setData( $cart_item['bookly'] );
            $userData->setCartKey( null );
            if ( get_option( 'ab_settings_use_client_time_zone' ) ) {
                $appointment_datetime = Lib\Utils\DateTime::applyTimeZoneOffset(
                    $userData->get( 'appointment_datetime' ), $userData->get( 'time_zone_offset' )
                );
            } else {
                $appointment_datetime = $userData->get( 'appointment_datetime' );
            }
            $info_value = strtr( $info_value, array(
                '[[APPOINTMENT_TIME]]'  => Lib\Utils\DateTime::formatTime( $appointment_datetime ),
                '[[APPOINTMENT_DATE]]'  => Lib\Utils\DateTime::formatDate( $appointment_datetime ),
                '[[CATEGORY_NAME]]'     => $userData->getCartService()->getCategoryName(),
                '[[SERVICE_NAME]]'      => $userData->getCartService()->getTitle(),
                '[[SERVICE_PRICE]]'     => $userData->getCartService()->get( 'price' ),
                '[[STAFF_NAME]]'        => $userData->getCartStaff()->getName(),
                '[[NUMBER_OF_PERSONS]]' => $userData->get( 'number_of_persons' )
            ) );

            $other_data[] = array( 'name' => $info_name, 'value' => $info_value );
        }

        return $other_data;
    }

    /**
     * Print appointment details inside order items in the backend.
     *
     * @param $item_id
     */
    public function orderItemMeta( $item_id )
    {
        $data = wc_get_order_item_meta( $item_id, 'bookly' );
        if ( $data ) {
            $other_data = $this->getItemData( array(), array( 'bookly' => $data ) );
            echo '<br/>' . $other_data[0]['name'] . '<br/>' . nl2br( $other_data[0]['value'] );
        }
    }

    /**
     * Add product to cart
     *
     * @return string JSON
     */
    public function executeAddToWoocommerceCart()
    {
        if ( ! get_option( 'ab_woocommerce_enabled' ) ) {
            exit;
        }
        $response = null;
        $userData = new Lib\UserBookingData( $this->getParameter( 'form_id' ) );

        if ( $userData->load() ) {
            $session =  WC()->session;
            /** @var \WC_Session_Handler $session */
            if ( $session instanceof \WC_Session_Handler && $session->get_session_cookie() === false ) {
                $session->set_customer_session_cookie( true );
            }
            if ( $userData->getFailedCartKey() === null ) {
                // Qnt 1 product in $userData exist value with number_of_persons
                WC()->cart->add_to_cart( $this->product_id, 1, '', array(), array( 'bookly' => $userData->getData() ) );
                $response = array( 'success' => true );
            } else {
                $response = array( 'success' => false, 'error' => __( 'The selected time is not available anymore. Please, choose another time slot.', 'bookly' ) );
            }
        } else {
            $response = array( 'success' => false, 'error' => __( 'Session error.', 'bookly' ) );
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