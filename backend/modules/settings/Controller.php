<?php
namespace Bookly\Backend\Modules\Settings;

use Bookly\Lib;

/**
 * Class Controller
 * @package Bookly\Backend\Modules\Settings
 */
class Controller extends Lib\Controller
{
    const page_slug = 'ab-settings';

    public function index()
    {
        /** @var \WP_Locale $wp_locale */
        global $wp_locale;

        $this->enqueueStyles( array(
            'frontend' => array( 'css/ladda.min.css' ),
            'backend'  => array(
                'css/bookly.main-backend.css',
                'bootstrap/css/bootstrap.min.css',
                'css/jCal.css',
            )
        ) );

        $this->enqueueScripts( array(
            'backend'  => array(
                'bootstrap/js/bootstrap.min.js' => array( 'jquery' ),
                'js/jCal.js'      => array( 'jquery' ),
            ),
            'module'   => array( 'js/settings.js' => array( 'jquery', 'ab-intlTelInput.min.js' ) ),
            'frontend' => array(
                'js/intlTelInput.min.js' => array( 'jquery' ),
                'js/spin.min.js'  => array( 'jquery' ),
                'js/ladda.min.js' => array( 'jquery' ),
            )
        ) );

        $current_tab = $this->hasParameter( 'tab' ) ? $this->getParameter( 'tab' ) : 'general';
        wp_localize_script( 'ab-jCal.js', 'BooklyL10n',  array(
            'we_are_not_working'  => __( 'We are not working on this day', 'bookly' ),
            'repeat'              => __( 'Repeat every year', 'bookly' ),
            'months'              => array_values( $wp_locale->month ),
            'days'                => array_values( $wp_locale->weekday_abbrev ),
            'current_tab'         => $current_tab,
        ) );
        
        $message = array();
        $notice_class = '';
        // Save the settings.
        if ( ! empty ( $_POST ) ) {
            switch ( $this->getParameter( 'tab' ) ) {
                case 'payments':  // Payments form.
                    $form = new Forms\Payments();
                    break;
                case 'business_hours':  // Business hours form.
                    $form = new Forms\BusinessHours();
                    break;
                case 'purchase_code':  // Purchase Code form.
                    $purchase_codes = $this->getParameter( 'purchase_code' );
                    do_action( 'bookly_backend_purchase_codes_save', $purchase_codes );
                    $bookly_purchase_code = array_key_exists( 'bookly', $purchase_codes ) ? $purchase_codes['bookly'] : '';
                    if ( $bookly_purchase_code == '' || Lib\Utils\Common::verifyPurchaseCode( $bookly_purchase_code, 'bookly' ) ) {
                        update_option( 'ab_envato_purchase_code', $bookly_purchase_code );
                        $notice_class = 'notice-success';
                        $message[ $this->getParameter( 'tab' ) ] = __( 'Settings saved.', 'bookly' );
                    } else {
                        $notice_class = 'notice-error';
                        $message[ $this->getParameter( 'tab' ) ] = sprintf( __( 'Error. <strong>%s</strong> is not a valid purchase code.', 'bookly' ), $bookly_purchase_code );
                    }
                    break;
                case 'general':  // General form.
                    $ab_settings_time_slot_length = $this->getParameter( 'ab_settings_time_slot_length' );
                    if ( in_array( $ab_settings_time_slot_length, array( 5, 10, 12, 15, 20, 30, 45, 60, 90, 120, 180, 240, 360 ) ) ) {
                        update_option( 'ab_settings_time_slot_length', $ab_settings_time_slot_length );
                    }
                    update_option( 'ab_settings_minimum_time_prior_booking', (int) $this->getParameter( 'ab_settings_minimum_time_prior_booking' ) );
                    update_option( 'ab_settings_maximum_available_days_for_booking', (int) $this->getParameter( 'ab_settings_maximum_available_days_for_booking' ) );
                    update_option( 'ab_settings_use_client_time_zone', (int) $this->getParameter( 'ab_settings_use_client_time_zone' ) );
                    update_option( 'ab_settings_final_step_url', $this->getParameter( 'ab_settings_final_step_url' ) );
                    update_option( 'ab_settings_link_assets_method', $this->getParameter( 'ab_settings_link_assets_method' ) );
                    update_option( 'ab_settings_cancel_page_url', $this->getParameter( 'ab_settings_cancel_page_url' ) );
                    update_option( 'ab_settings_cancel_denied_page_url', $this->getParameter( 'ab_settings_cancel_denied_page_url' ) );
                    update_option( 'ab_settings_minimum_time_prior_cancel', $this->getParameter( 'ab_settings_minimum_time_prior_cancel' ) );
                    update_option( 'ab_settings_allow_staff_members_edit_profile', (int) $this->getParameter( 'ab_settings_allow_staff_members_edit_profile' ) );
                    $message[ $this->getParameter( 'tab' ) ] = __( 'Settings saved.', 'bookly' );
                    break;
                case 'google_calendar':  // Google calendar form.
                    update_option( 'ab_settings_google_client_id', $this->getParameter( 'ab_settings_google_client_id' ) );
                    update_option( 'ab_settings_google_client_secret', $this->getParameter( 'ab_settings_google_client_secret' ) );
                    update_option( 'ab_settings_google_two_way_sync', $this->getParameter( 'ab_settings_google_two_way_sync' ) );
                    update_option( 'ab_settings_google_limit_events', $this->getParameter( 'ab_settings_google_limit_events' ) );
                    update_option( 'ab_settings_google_event_title', $this->getParameter( 'ab_settings_google_event_title' ) );
                    $message[ $this->getParameter( 'tab' ) ] = __( 'Settings saved.', 'bookly' );
                    break;
                case 'customers':  // Customers form.
                    update_option( 'ab_settings_create_account', (int) $this->getParameter( 'ab_settings_create_account' ) );
                    update_option( 'ab_settings_phone_default_country', $this->getParameter( 'ab_settings_phone_default_country' ) );
                    update_option( 'ab_sms_default_country_code', $this->getParameter( 'ab_sms_default_country_code' ) );
                    $message[ $this->getParameter( 'tab' ) ] = __( 'Settings saved.', 'bookly' );
                    break;
                case 'woocommerce':  // WooCommerce form.
                    update_option( 'ab_woocommerce_enabled', $this->getParameter( 'ab_woocommerce_enabled' ) );
                    update_option( 'ab_woocommerce_product', $this->getParameter( 'ab_woocommerce_product' ) );
                    update_option( 'ab_woocommerce_cart_info_name',  $this->getParameter( 'ab_woocommerce_cart_info_name' ) );
                    update_option( 'ab_woocommerce_cart_info_value', $this->getParameter( 'ab_woocommerce_cart_info_value' ) );
                    $message[ $this->getParameter( 'tab' ) ] = __( 'Settings saved.', 'bookly' );
                    break;
                case 'cart':  // Cart form.
                    update_option( 'ab_settings_step_cart_enabled', (int) $this->getParameter( 'ab_settings_step_cart_enabled' ) );
                    update_option( 'ab_cart_show_columns',     $this->getParameter( 'ab_cart_show_columns', array() ) );
                    $message[ $this->getParameter( 'tab' ) ] = __( 'Settings saved.', 'bookly' );
                    if ( get_option( 'ab_woocommerce_enabled' ) && $this->getParameter( 'ab_settings_step_cart_enabled' ) ) {
                        $message[ $this->getParameter( 'tab' ) ] .= '<br/>' . sprintf( __( 'To use the cart, disable integration with WooCommerce <a href="%s">here</a>.', 'bookly' ), Lib\Utils\Common::escAdminUrl( self::page_slug, array( 'tab' => 'woocommerce' ) ) );
                    }
                    break;
                case 'company':  // Company form.
                    $form = new Forms\Company();
                    break;
                default:
                    $response = apply_filters( 'bookly_backend_settings_save', array(), $this->getParameter( 'tab' ), $this->getPostParameters() );
                    foreach ( $response as $tab => $values ) {
                        if ( isset( $values['message'] ) ) {
                            $message[ $tab ] = $values['message'];
                        }
                    }
            }
            if ( in_array( $this->getParameter( 'tab' ), array ( 'payments', 'business_hours', 'company' ) ) ) {
                $form->bind( $this->getPostParameters(), $_FILES );
                $form->save();
                $message[ $this->getParameter( 'tab' ) ] = __( 'Settings saved.', 'bookly' );
            }
        }

        $holidays   = $this->getHolidays();
        $candidates = $this->getCandidatesBooklyProduct();

        // Check if WooCommerce cart exists.
        $wc_cart_error_message = '';
        if ( get_option( 'ab_woocommerce_enabled' ) && class_exists( 'WooCommerce' ) ) {
            $post = get_post( wc_get_page_id( 'cart' ) );
            if ( $post === null || $post->post_status != 'publish' ) {
                $wc_cart_error_message = sprintf(
                    __( 'WooCommerce cart is not set up. Follow the <a href="%s">link</a> to correct this problem.', 'bookly' ),
                    Lib\Utils\Common::escAdminUrl( 'wc-status', array( 'tab' => 'tools' ) )
                );
            }
        }

        $this->render( 'index', compact( 'holidays', 'candidates', 'message', 'wc_cart_error_message', 'notice_class' ) );
    } // index

    /**
     * Ajax request for Holidays calendar
     */
    public function executeSettingsHoliday()
    {
        $id      = $this->getParameter( 'id',  false );
        $day     = $this->getParameter( 'day', false );
        $holiday = $this->getParameter( 'holiday' ) == 'true';
        $repeat  = $this->getParameter( 'repeat' )  == 'true';

        // update or delete the event
        if ( $id ) {
            if ( $holiday ) {
                $this->getWpdb()->update( Lib\Entities\Holiday::getTableName(), array( 'repeat_event' => intval( $repeat ) ), array( 'id' => $id ), array( '%d' ) );
                $this->getWpdb()->update( Lib\Entities\Holiday::getTableName(), array( 'repeat_event' => intval( $repeat ) ), array( 'parent_id' => $id ), array( '%d' ) );
            } else {
                Lib\Entities\Holiday::query()->delete()->where( 'id', $id )->where( 'parent_id', $id, 'OR' )->execute();
            }
            // add the new event
        } elseif ( $holiday && $day ) {
            $holiday = new Lib\Entities\Holiday( array( 'date' => $day, 'repeat_event' => intval( $repeat ) ) );
            $holiday->save();
            foreach ( Lib\Entities\Staff::query()->fetchArray() as $employee ) {
                $staff_holiday = new Lib\Entities\Holiday( array( 'date' => $day, 'repeat_event' => intval( $repeat ), 'staff_id'  => $employee['id'], 'parent_id' => $holiday->get( 'id' ) ) );
                $staff_holiday->save();
            }
        }

        // and return refreshed events
        echo $this->getHolidays();
        exit;
    }

    /**
     * @return mixed|string|void
     */
    protected function getHolidays()
    {
        $collection = Lib\Entities\Holiday::query()->where( 'staff_id', null )->fetchArray();
        $holidays = array();
        if ( count( $collection ) ) {
            foreach ( $collection as $holiday ) {
                $holidays[ $holiday['id'] ] = array(
                    'm'     => intval( date( 'm', strtotime( $holiday['date'] ) ) ),
                    'd'     => intval( date( 'd', strtotime( $holiday['date'] ) ) ),
                );
                // If not repeated holiday, add the year
                if ( ! $holiday['repeat_event'] ) {
                    $holidays[ $holiday['id'] ]['y'] = intval( date( 'Y', strtotime( $holiday['date'] ) ) );
                }
            }
        }

        return json_encode( $holidays );
    }

    protected function getCandidatesBooklyProduct()
    {
        $goods = array( array( 'id' => 0, 'name' => __( 'Select product', 'bookly' ) ) );
        $args  = array(
            'numberposts'      => 0,
            'post_type'        => 'product',
            'suppress_filters' => true
        );
        $collection = get_posts( $args );
        foreach ( $collection as $item ) {
            $goods[] = array( 'id' => $item->ID, 'name' => $item->post_title );
        }
        wp_reset_postdata();

        return $goods;
    }

    /**
     * Show admin notice about purchase code and license.
     */
    public function showAdminNotice()
    {
        if ( Lib\Utils\Common::isCurrentUserAdmin() &&
             get_option( 'ab_envato_purchase_code' ) == '' &&
             ! get_user_meta( get_current_user_id(), 'ab_dismiss_admin_notice', true ) &&
             time() > get_option( 'ab_installation_time' ) + WEEK_IN_SECONDS
        ) {
            $this->render( 'admin_notice' );
        }
        do_action( 'bookly_backend_admin_notice' );
    }

    /**
     * Ajax request to dismiss admin notice for current user.
     */
    public function executeDismissAdminNotice()
    {
        update_user_meta( get_current_user_id(), 'ab_dismiss_admin_notice', 1 );
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
    }

}