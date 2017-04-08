<?php
namespace Bookly\Backend\Modules\Payments;

use Bookly\Lib;

/**
 * Class Controller
 * @package Bookly\Backend\Modules\Payments
 */
class Controller extends Lib\Controller
{
    public function index()
    {
        /** @var \WP_Locale $wp_locale */
        global $wp_locale;

        $this->enqueueStyles( array(
            'backend' => array(
                'css/bookly.main-backend.css',
                'bootstrap/css/bootstrap.min.css',
                'css/daterangepicker.css',
                'css/bootstrap-select.min.css',
            )
        ) );

        $this->enqueueScripts( array(
            'backend' => array(
                'bootstrap/js/bootstrap.min.js' => array( 'jquery' ),
                'js/moment.min.js',
                'js/daterangepicker.js' => array( 'jquery' ),
                'js/bootstrap-select.min.js',
            )
        ) );

        wp_localize_script( 'ab-daterangepicker.js', 'BooklyL10n', array(
            'today'         => __( 'Today', 'bookly' ),
            'yesterday'     => __( 'Yesterday', 'bookly' ),
            'last_7'        => __( 'Last 7 Days', 'bookly' ),
            'last_30'       => __( 'Last 30 Days', 'bookly' ),
            'this_month'    => __( 'This Month', 'bookly' ),
            'last_month'    => __( 'Last Month', 'bookly' ),
            'custom_range'  => __( 'Custom Range', 'bookly' ),
            'apply'         => __( 'Apply', 'bookly' ),
            'cancel'        => __( 'Cancel', 'bookly' ),
            'to'            => __( 'To', 'bookly' ),
            'from'          => __( 'From', 'bookly' ),
            'months'        => array_values( $wp_locale->month ),
            'days'          => array_values( $wp_locale->weekday_abbrev ),
            'startOfWeek'   => (int) get_option( 'start_of_week' ),
            'mjsDateFormat' => Lib\Utils\DateTime::convertFormat( 'date', Lib\Utils\DateTime::FORMAT_MOMENT_JS ),
        ));

        $request = array(
            'range'      => date( 'Y-m-d', strtotime( '-30 days' ) ) . ' - ' . date( 'Y-m-d' ),
            'order_by'   => 'created',
            'sort_order' => 'desc',
        );
        $payments  = $this->createQuery( $request )->fetchArray();
        $types     = array(
            Lib\Entities\Payment::TYPE_LOCAL,
            Lib\Entities\Payment::TYPE_2CHECKOUT,
            Lib\Entities\Payment::TYPE_PAYPAL,
            Lib\Entities\Payment::TYPE_AUTHORIZENET,
            Lib\Entities\Payment::TYPE_STRIPE,
            Lib\Entities\Payment::TYPE_PAYULATAM,
            Lib\Entities\Payment::TYPE_PAYSON,
            Lib\Entities\Payment::TYPE_MOLLIE,
            Lib\Entities\Payment::TYPE_COUPON,
        );
        $providers = array();
        $services  = array();
        foreach ( Lib\Entities\Staff::query()->select( 'full_name' )->fetchArray() as $staff ) {
            $providers[] = $staff['full_name'];
        }
        foreach ( Lib\Entities\Service::query()->select( 'title' )->fetchArray() as $service ) {
            $services[]  = $service['title'];
        }
        $this->render( 'index', compact( 'payments', 'types', 'providers', 'services' ) );
    }

    /**
     * Sort payments.
     */
    public function executeSortPayments()
    {
        $this->executeFilterPayments();
    }

    /**
     * Filter payments.
     */
    public function executeFilterPayments()
    {
        $data = $this->getParameter( 'data' );
        $payments = empty( $data ) ? array() : $this->createQuery( $data )->fetchArray();
        $this->render( '_body', compact( 'payments' ) );
        exit;
    }

    /**
     * @param $request
     * @return Lib\Query
     */
    private function createQuery( $request )
    {
        $query = Lib\Entities\Payment::query( 'p' )
           ->select( 'p.*, c.name customer, st.full_name provider, s.title service, ca.coupon_code coupon, a.start_date' )
           ->leftJoin( 'CustomerAppointment', 'ca', 'ca.id = p.customer_appointment_id' )
           ->leftJoin( 'Customer', 'c', 'c.id = ca.customer_id' )
           ->leftJoin( 'Appointment', 'a', 'a.id = ca.appointment_id' )
           ->leftJoin( 'Service', 's', 's.id = a.service_id' )
           ->leftJoin( 'Staff', 'st', 'st.id = a.staff_id' );

        if ( isset ( $request['type'] ) && $request['type'] != -1 ) {
            $query->where( 'p.type', $request['type'] );
        }

        if ( isset ( $request['customer'] ) && $request['customer'] != -1 ) {
            $query->where( 'c.name', $request['customer'] );
        }

        if ( isset ( $request['provider'] ) && $request['provider'] != -1 ) {
            $query->where( 'st.full_name', $request['provider'] );
        }

        if ( isset ( $request['service'] ) && $request['service']  != -1 ) {
            $query->where( 's.title', $request['service'] );
        }

        if ( isset ( $request['range'] ) && ! empty ( $request['range'] ) ) {
            $dates = explode( ' - ', $request['range'], 2 );
            $start_date_timestamp = strtotime( $dates[0] );
            $end_date_timestamp   = strtotime( $dates[1] );

            $start = date( 'Y-m-d', $start_date_timestamp );
            $end   = date( 'Y-m-d', strtotime( '+1 day', $end_date_timestamp ) );

            $query->whereBetween( 'p.created', $start, $end );
        }

        if (
            ! empty( $request['sort_order'] ) &&
            in_array( $request['order_by'], array( 'created', 'type', 'customer', 'provider', 'service', 'total', 'start_date', 'coupon', 'status' ) )
        ) {
            $query->sortBy( $request['order_by'] )
                ->order( $request['sort_order'] == 'desc' ? Lib\Query::ORDER_DESCENDING : Lib\Query::ORDER_ASCENDING );
        }

        return $query;
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