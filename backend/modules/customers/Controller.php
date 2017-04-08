<?php
namespace Bookly\Backend\Modules\Customers;

use Bookly\Lib;

/**
 * Class Controller
 * @package Bookly\Backend\Modules\Customers
 */
class Controller extends Lib\Controller
{
    const page_slug = 'ab-customers';

    protected function getPermissions()
    {
        return array(
            'executeSaveCustomer' => 'user',
            'executeGetNgNewCustomerDialogTemplate' => 'user',
        );
    }

    public function index()
    {
        if ( $this->hasParameter( 'import-customers' ) ) {
            $this->importCustomers();
        }

        $this->enqueueStyles( array(
            'frontend' => get_option( 'ab_settings_phone_default_country' ) == 'disabled'
                ? array()
                : array( 'css/intlTelInput.css' ),
            'module'   => array( 'css/customers.css' ),
            'backend'  => array(
                'css/bookly.main-backend.css',
                'bootstrap/css/bootstrap.min.css',
            )
        ) );
        $this->enqueueScripts( array(
            'backend' => array(
                'bootstrap/js/bootstrap.min.js' => array( 'jquery' ),
                'js/angular.min.js',
                'js/angular-sanitize.min.js'    => array( 'ab-angular.min.js' ),
                'js/angular-ui-utils.min.js'    => array( 'ab-angular.min.js' ),
                'js/angular-ui-date-0.0.8.js'   => array( 'ab-angular.min.js' ),
                'js/ng-new_customer_dialog.js'  => array( 'jquery', 'ab-angular.min.js' ),
            ),
            'module' => array(
                'js/ng-app.js' => array(
                    'jquery',
                    'ab-angular.min.js',
                    'ab-angular-ui-utils.min.js',
                    'ab-angular-ui-date-0.0.8.js',
                ),
            ),
            'frontend' => get_option( 'ab_settings_phone_default_country' ) == 'disabled'
                ? array()
                : array( 'js/intlTelInput.min.js' => array( 'jquery' ) ),
        ) );

        wp_localize_script( 'ab-ng-app.js', 'BooklyL10n', array(
            'are_you_sure' => __( 'Are you sure?', 'bookly' ),
            'wp_users'     => $this->getWpUsers(),
            'module'       => 'customer',
            'intlTelInput' => array(
                'enabled'  => ( get_option( 'ab_settings_phone_default_country' ) != 'disabled' ),
                'utils'    => plugins_url( 'intlTelInput.utils.js', AB_PATH . '/frontend/resources/js/intlTelInput.utils.js' ),
                'country'  => get_option( 'ab_settings_phone_default_country' ),
            ),
            'please_select_at_least_one_row' => __( 'Please select at least one customer.', 'bookly' ),
        ) );

        $this->render( 'index' );
    }

    /**
     * Get list of customers.
     */
    public function executeGetCustomers()
    {
        /** @var \wpdb $wpdb */
        global $wpdb;

        $items_per_page = 20;
        $response = array(
            'customers'   => array(),
            'total'       => 0,
            'pages'       => 0,
            'active_page' => 0,
        );

        $page   = intval( $this->getParameter( 'page' ) );
        $sort   = in_array( $this->getParameter( 'sort' ), array( 'name', 'phone', 'email', 'notes', 'last_appointment', 'total_appointments', 'payments', 'wp_user' ) )
            ? $this->getParameter( 'sort' ) : 'name';
        $order  = in_array( $this->getParameter( 'order' ), array( 'asc', 'desc' ) ) ? $this->getParameter( 'order' ) : 'asc';

        $query  = Lib\Entities\Customer::query( 'c' );
        $filter = Lib\Query::escape( $this->getParameter( 'filter' ) );
        // WHERE
        if ( $filter !== '' ) {
            $query->whereLike( 'c.name',  "%{$filter}%" )
                  ->whereLike( 'c.phone', "%{$filter}%", 'OR' )
                  ->whereLike( 'c.email', "%{$filter}%", 'OR' );
        }
        $total = $query->count();

        $pages = ceil( $total / $items_per_page );
        if ( $page < 1 || $page > $pages ) {
            $page = 1;
        }

        $data = $query->select( 'c.*, MAX(a.start_date) AS last_appointment,
                COUNT(a.id) AS total_appointments,
                COALESCE(SUM(p.total),0) AS payments,
                wpu.display_name AS wp_user' )
            ->leftJoin( 'CustomerAppointment', 'ca', 'ca.customer_id = c.id' )
            ->leftJoin( 'Appointment', 'a', 'a.id = ca.appointment_id' )
            ->leftJoin( 'Payment', 'p', 'p.customer_appointment_id = ca.id' )
            ->tableJoin( $wpdb->users, 'wpu', 'wpu.ID = c.wp_user_id' )
            ->groupBy( 'c.id' )
            ->sortBy( $sort )
            ->order( $order )
            ->limit( $items_per_page )
            ->offset( ( $page - 1 ) * $items_per_page )
            ->fetchArray();

        array_walk( $data, function ( &$row ) {
            if ( $row['last_appointment'] ) {
                $row['last_appointment'] = Lib\Utils\DateTime::formatDateTime( $row['last_appointment'] );
            }
            $row['payments'] = Lib\Utils\Common::formatPrice( $row['payments'] );
        } );

        // Populate response.
        $response['customers']   = $data;
        $response['total']       = $total;
        $response['pages']       = $pages;
        $response['active_page'] = $page;

        wp_send_json_success( $response );
    }

    /**
     * Get WP users array.
     *
     * @return array
     */
    public function getWpUsers()
    {
        return get_users( array( 'fields' => array( 'ID', 'display_name' ), 'orderby' => 'display_name' ) );
    }

    /**
     * Create or edit a customer.
     */
    public function executeSaveCustomer()
    {
        $response = array();
        $form = new Forms\Customer();

        do {
            if ( $this->getParameter( 'name' ) !== '' ) {
                $form->bind( $this->getPostParameters() );
                /** @var Lib\Entities\Customer $customer */
                $customer = $form->save();
                if ( $customer ) {
                    $response['success']  = true;
                    $response['customer'] = array(
                        'id'      => $customer->id,
                        'name'    => $customer->name,
                        'phone'   => $customer->phone,
                        'email'   => $customer->email,
                        'notes'   => $customer->notes,
                        'wp_user_id' => $customer->wp_user_id,
                        'jsonString' => json_encode( array(
                            'name'  => $customer->name,
                            'phone' => $customer->phone,
                            'email' => $customer->email,
                            'notes' => $customer->notes
                        ) )
                    );
                    break;
                }
            }
            $response['success'] = false;
            $response['errors']  = array( 'name' => array( 'required' ) );
        } while ( 0 );

        wp_send_json( $response );
    }

    /**
     * Import customers from CSV.
     */
    private function importCustomers()
    {
        @ini_set( 'auto_detect_line_endings', true );

        $csv_mime_types = array(
            'text/csv',
            'application/csv',
            'text/comma-separated-values',
            'application/excel',
            'application/vnd.ms-excel',
            'application/vnd.msexcel'
        );

        if ( in_array( $_FILES['import_customers_file']['type'], $csv_mime_types ) ) {
            $file = fopen ( $_FILES['import_customers_file']['tmp_name'], 'r' );
            while ( $line = fgetcsv( $file, null, $this->getParameter( 'import_customers_delimiter' ) ) ) {
                if ( $line[0] != '' ) {
                    $customer = new Lib\Entities\Customer();
                    $customer->set( 'name', $line[0] );
                    if ( isset( $line[1] ) ) {
                        $customer->set( 'phone', $line[1] );
                    }
                    if ( isset( $line[2] ) ) {
                        $customer->set( 'email', $line[2] );
                    }
                    $customer->save();
                }
            }
        }
    }

    /**
     * Get angulars template for new customer dialog.
     */
    public function executeGetNgNewCustomerDialogTemplate()
    {
        if ( $this->getParameter( 'module' ) == 'calendar' ) {
            $custom_fields = array_filter( json_decode( get_option( 'ab_custom_fields' ) ), function( $field ) { return $field->type != 'captcha'; } );
        } else {
            $custom_fields = array();
        }
        $this->render( 'ng-new_customer_dialog', array(
            'custom_fields' => $custom_fields,
            'module'        => $this->getParameter( 'module' ),
            'wp_users'      => $this->getWpUsers()
        ) );
        exit;
    }

    /**
     * Delete customers.
     */
    public function executeDeleteCustomers()
    {
        foreach ( $this->getParameter( 'ids' ) as $id ) {
            $customer = new Lib\Entities\Customer();
            $customer->load( $id );
            $customer->deleteWithWPUser( (bool) $this->getParameter( 'with_wp_user' ) );
        }
        wp_send_json_success();
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

    /**
     * Export Customers to CSV
     */
    public function executeExportCustomers()
    {
        $wpdb = $this->getWpdb();
        $delimiter = $this->getParameter( 'export_customers_delimiter', ',' );

        header( 'Content-Type: text/csv; charset=utf-8' );
        header( 'Content-Disposition: attachment; filename=Customers.csv' );

        $header = array();
        $column  = array();

        foreach ( $this->getParameter( 'exp' ) as $key => $value ) {
            $header[] = $value;
            $column[] = $key;
        }

        $output = fopen( 'php://output', 'w' );
        fwrite( $output, pack( 'CCC', 0xef, 0xbb, 0xbf ) );
        fputcsv( $output, $header, $delimiter );

        $rows = Lib\Entities\Customer::query( 'c' )->select( 'c.*, MAX(a.start_date) AS last_appointment,
                COUNT(a.id) AS total_appointments,
                COALESCE(SUM(p.total),0) AS payments,
                wpu.display_name AS wp_user' )
            ->leftJoin( 'CustomerAppointment', 'ca', 'ca.customer_id = c.id' )
            ->leftJoin( 'Appointment', 'a', 'a.id = ca.appointment_id' )
            ->leftJoin( 'Payment', 'p', 'p.customer_appointment_id = ca.id' )
            ->tableJoin( $wpdb->users, 'wpu', 'wpu.ID = c.wp_user_id' )
            ->groupBy( 'c.id' )
            ->fetchArray();

            foreach( $rows as $row ) {
                $row_data = array_fill(0, count($column),'');
                foreach($row as $key => $value ){
                    $pos = array_search($key, $column);
                    if( $pos !== false ){
                        $row_data[$pos] = $value;
                    }
                }
                fputcsv( $output, $row_data, $delimiter );
            }

        fclose( $output );

        exit;
    }

}