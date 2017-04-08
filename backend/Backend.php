<?php
namespace Bookly\Backend;

use Bookly\Backend\Modules;
use Bookly\Frontend;
use Bookly\Lib;

/**
 * Class Backend
 * @package Bookly\Backend
 */
class Backend
{
    public function __construct()
    {
        // Backend controllers.
        $this->apearanceController     = new Modules\Appearance\Controller();
        $this->calendarController      = new Modules\Calendar\Controller();
        $this->customerController      = new Modules\Customers\Controller();
        $this->notificationsController = new Modules\Notifications\Controller();
        $this->paymentController       = new Modules\Payments\Controller();
        $this->serviceController       = new Modules\Services\Controller();
        $this->smsController           = new Modules\Sms\Controller();
        $this->settingsController      = new Modules\Settings\Controller();
        $this->staffController         = new Modules\Staff\Controller();
        $this->couponsController       = new Modules\Coupons\Controller();
        $this->customFieldsController  = new Modules\CustomFields\Controller();
        $this->appointmentsController  = new Modules\Appointments\Controller();

        // Frontend controllers that work via admin-ajax.php.
        $this->bookingController         = new Frontend\Modules\Booking\Controller();
        $this->customerProfileController = new Frontend\Modules\CustomerProfile\Controller();
        $this->authorizeNetController    = new Frontend\Modules\AuthorizeNet\Controller();
        $this->stripeController          = new Frontend\Modules\Stripe\Controller();
        $this->payulatamController       = new Frontend\Modules\PayuLatam\Controller();
        $this->wooCommerceController     = new Frontend\Modules\WooCommerce\Controller();

        add_action( 'admin_menu',      array( $this, 'addAdminMenu' ) );
        add_action( 'wp_loaded',       array( $this, 'init' ) );
        add_action( 'admin_init',      array( $this, 'addTinyMCEPlugin' ) );
        add_action( 'admin_notices',   array( $this->settingsController, 'showAdminNotice' ) );
    }

    public function init()
    {
        if ( ! session_id() ) {
            @session_start();
        }
    }

    public function addTinyMCEPlugin()
    {
        new Modules\TinyMce\Plugin();
    }

    public function addAdminMenu()
    {
        /** @var \WP_User $current_user */
        global $current_user;

        // Translated submenu pages.
        $calendar       = __( 'Calendário',      'bookly' );
        $appointments   = __( 'Compromissos',  'bookly' );
        $staff_members  = __( 'Advogados', 'bookly' );
        $services       = __( 'Serviços',      'bookly' );
        $sms            = __( 'Notificações SMS', 'bookly' );
        $notifications  = __( 'Notificações Email', 'bookly' );
        $customers      = __( 'Clientes',     'bookly' );
        $payments       = __( 'Pagamentos',      'bookly' );
        $appearance     = __( 'Aparência',    'bookly' );
        $settings       = __( 'Configuração',      'bookly' );
        $coupons        = __( 'Coupons',       'bookly' );
        $custom_fields  = __( 'Campos personalizados', 'bookly' );

        if ( $current_user->has_cap( 'administrator' ) || Lib\Entities\Staff::query()->where( 'wp_user_id', $current_user->ID )->count() ) {
            if ( function_exists( 'add_options_page' ) ) {
                $dynamic_position = '80.0000001' . mt_rand( 1, 1000 ); // position always is under `Settings`
                add_menu_page( 'Bookly', 'Bookly', 'read', 'ab-system', '',
                    plugins_url( 'resources/images/menu.png', __FILE__ ), $dynamic_position );
                add_submenu_page( 'ab-system', $calendar, $calendar, 'read', 'ab-calendar',
                    array( $this->calendarController, 'index' ) );
                add_submenu_page( 'ab-system', $appointments, $appointments, 'manage_options', 'ab-appointments',
                    array( $this->appointmentsController, 'index' ) );
                if ( $current_user->has_cap( 'administrator' ) ) {
                    add_submenu_page( 'ab-system', $staff_members, $staff_members, 'manage_options', Modules\Staff\Controller::page_slug,
                        array( $this->staffController, 'index' ) );
                } else {
                    if ( get_option( 'ab_settings_allow_staff_members_edit_profile' ) == 1 ) {
                        add_submenu_page( 'ab-system', __( 'Profile', 'bookly' ), __( 'Profile', 'bookly' ), 'read', Modules\Staff\Controller::page_slug,
                            array( $this->staffController, 'index' ) );
                    }
                }
                add_submenu_page( 'ab-system', $services, $services, 'manage_options', Modules\Services\Controller::page_slug,
                    array( $this->serviceController, 'index' ) );
                add_submenu_page( 'ab-system', $customers, $customers, 'manage_options', Modules\Customers\Controller::page_slug,
                    array( $this->customerController, 'index' ) );
                add_submenu_page( 'ab-system', $notifications, $notifications, 'manage_options', 'ab-notifications',
                    array( $this->notificationsController, 'index' ) );
                add_submenu_page( 'ab-system', $sms, $sms, 'manage_options', Modules\Sms\Controller::page_slug,
                    array( $this->smsController, 'index' ) );
                add_submenu_page( 'ab-system', $payments, $payments, 'manage_options', 'ab-payments',
                    array( $this->paymentController, 'index' ) );
                add_submenu_page( 'ab-system', $appearance, $appearance, 'manage_options', 'ab-appearance',
                    array( $this->apearanceController, 'index' ) );
                add_submenu_page( 'ab-system', $custom_fields, $custom_fields, 'manage_options', 'ab-custom-fields',
                    array( $this->customFieldsController, 'index' ) );
                add_submenu_page( 'ab-system', $coupons, $coupons, 'manage_options', 'ab-coupons',
                    array( $this->couponsController, 'index' ) );
                add_submenu_page( 'ab-system', $settings, $settings, 'manage_options', Modules\Settings\Controller::page_slug,
                    array( $this->settingsController, 'index' ) );

                global $submenu;
                do_action( 'bookly_admin_menu', 'ab-system' );
                unset( $submenu['ab-system'][0] );
            }
        }
    }

}