<?php
namespace Bookly\Frontend\Modules\CustomerProfile;

use Bookly\Lib;

/**
 * Class Controller
 * @package Bookly\Frontend\Modules\CustomerProfile
 */
class Controller extends Lib\Controller
{
    protected function getPermissions()
    {
        return array( '_this' => 'user' );
    }

    public function renderShortCode( $attributes )
    {
        global $sitepress;

        $assets = '';

        if ( get_option( 'ab_settings_link_assets_method' ) == 'print' ) {
            if ( ! wp_script_is( 'ab-customer-profile', 'done' ) ) {
                ob_start();

                // The styles and scripts are registered in Frontend.php
                wp_print_styles( 'ab-customer-profile' );
                wp_print_scripts( 'ab-customer-profile' );

                $assets = ob_get_clean();
            }
        }

        $customer = new Lib\Entities\Customer();
        $customer->loadBy( array( 'wp_user_id' => get_current_user_id() ) );
        if ( $customer->isLoaded() ) {
            $appointments = $this->_translateAppointments( $customer->getUpcomingAppointments() );
            $expired      = $customer->getPastAppointments( 1, 1 );
            $this->more   = ! empty ( $expired['appointments'] );
        } else {
            $appointments = array();
            $this->more   = false;
        }
        $this->allow_cancel        = current_time( 'timestamp' );
        $minimum_time_prior_cancel = (int) get_option( 'ab_settings_minimum_time_prior_cancel', 0 );
        if ( $minimum_time_prior_cancel > 0 ) {
            $this->allow_cancel += $minimum_time_prior_cancel * HOUR_IN_SECONDS;
        }

        // Prepare URL for AJAX requests.
        $this->ajax_url = admin_url( 'admin-ajax.php' );

        // Support WPML.
        if ( $sitepress instanceof \SitePress ) {
            switch ( $sitepress->get_setting( 'language_negotiation_type' ) ) {
                case 1: // url: /de             Different languages in directories.
                    $this->ajax_url .= '/' . $sitepress->get_current_language();
                    break;
                case 2: // url: de.example.com  A different domain per language. Not available for Multisite
                    break;
                case 3: // url: ?lang=de        Language name added as a parameter.
                    $this->ajax_url .= '?lang=' . $sitepress->get_current_language();
                    break;
            }
        }

        $this->titles = array();
        if ( @$attributes['show_column_titles'] ) {
            $this->titles = array(
                'category' => Lib\Utils\Common::getTranslatedOption( 'ab_appearance_text_label_category' ),
                'service'  => Lib\Utils\Common::getTranslatedOption( 'ab_appearance_text_label_service' ),
                'staff'    => Lib\Utils\Common::getTranslatedOption( 'ab_appearance_text_label_employee' ),
                'date'     => __( 'Date',   'bookly' ),
                'time'     => __( 'Time',   'bookly' ),
                'price'    => __( 'Price',  'bookly' ),
                'cancel'   => __( 'Cancel', 'bookly' )
            );
        }
        $url_cancel = $this->ajax_url . ( strpos( $this->ajax_url, '?' ) ? '&' : '?' ) . 'action=ab_cancel_appointment';

        return $assets . $this->render( 'short_code', array( 'appointments' => $appointments, 'attributes' => $attributes, 'url_cancel' => $url_cancel ), false );
    }

    /**
     * WPML translation
     *
     * @param $appointments
     */
    private function _translateAppointments( $appointments )
    {
        foreach ( $appointments as &$appointment ) {
            $category = new Lib\Entities\Category( array( 'id' => $appointment['category_id'], 'name' => $appointment['category'] ) );
            $service  = new Lib\Entities\Service( array( 'id' => $appointment['service_id'],  'title' => $appointment['service'] ) );
            $staff    = new Lib\Entities\Staff( array( 'id' => $appointment['staff_id'],  'full_name' => $appointment['staff'] ) );
            $appointment['category'] = $category->getName();
            $appointment['service']  = $service->getTitle();
            $appointment['staff']    = $staff->getName();
        }

        return $appointments;
    }

    /**
     * Get past appointments.
     */
    public function executeGetPastAppointments()
    {
        $customer = new Lib\Entities\Customer();
        $customer->loadBy( array( 'wp_user_id' => get_current_user_id() ) );
        $appointments = $customer->getPastAppointments( $this->getParameter( 'page' ), 30 );
        $appointments['appointments'] = $this->_translateAppointments( $appointments['appointments'] );
        $columns      = $this->getParameter( 'columns' );
        $allow_cancel = current_time( 'timestamp' ) + (int) get_option( 'ab_settings_minimum_time_prior_cancel', 0 );
        $html = $this->render( '_row', array( 'appointments' => $appointments['appointments'], 'columns' => $columns, 'allow_cancel' => $allow_cancel ), false );
        wp_send_json_success( array( 'html' => $html, 'more' => $appointments['more'] ) );
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