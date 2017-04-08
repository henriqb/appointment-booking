<?php if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly ?>
<div id="ab_settings_controls" class="panel panel-default">
    <div class="panel-heading">
        <h3 class="panel-title"><?php _e( 'Settings', 'bookly' ) ?></h3>
    </div>
    <div class="panel-body">
        <ul class="nav nav-tabs" id="settings_tabs">
            <?php $tab = isset ( $_GET['tab'] ) ? $_GET['tab'] : 'general' ?>
            <li><a href="#ab_settings_general" data-toggle="tab"><?php _e( 'General', 'bookly' ) ?></a></li>
            <li><a href="#ab_settings_company" data-toggle="tab"><?php _e( 'Company', 'bookly' ) ?></a></li>
            <li><a href="#ab_settings_customers" data-toggle="tab"><?php _e( 'Customers', 'bookly' ) ?></a></li>
            <li><a href="#ab_settings_google_calendar" data-toggle="tab"><?php _e( 'Google Calendar', 'bookly' ) ?></a></li>
            <li><a href="#ab_settings_woocommerce" data-toggle="tab">WooCommerce</a></li>
            <li><a href="#ab_settings_cart" data-toggle="tab"><?php _e( 'Cart', 'bookly' ) ?></a></li>
            <?php do_action( 'bookly_backend_settings_extras_menu', $tab ) ?>
            <li><a href="#ab_settings_payments" data-toggle="tab"><?php _e( 'Payments', 'bookly' ) ?></a></li>
            <li><a href="#ab_settings_business_hours" data-toggle="tab"><?php _e( 'Business hours', 'bookly' ) ?></a></li>
            <li><a href="#ab_settings_holidays" data-toggle="tab"><?php _e( 'Holidays', 'bookly' ) ?></a></li>
            <li><a href="#ab_settings_purchase_code" data-toggle="tab"><?php _e( 'Purchase Code', 'bookly' ) ?></a></li>
        </ul>
        <div class="tab-content">
            <div class="tab-pane" id="ab_settings_general">
                <?php isset( $message['general'] ) && \Bookly\Lib\Utils\Common::notice( $message['general'] ) ?>
                <?php include '_generalForm.php' ?>
            </div>
            <div class="tab-pane" id="ab_settings_company">
                <?php isset( $message['company'] ) && \Bookly\Lib\Utils\Common::notice( $message['company'] ) ?>
                <?php include '_companyForm.php' ?>
            </div>
            <div class="tab-pane" id="ab_settings_customers">
                <?php isset( $message['customers'] ) && \Bookly\Lib\Utils\Common::notice( $message['customers'] ) ?>
                <?php include '_customers.php' ?>
            </div>
            <div class="tab-pane" id="ab_settings_google_calendar">
                <?php isset( $message['google_calendar'] ) && \Bookly\Lib\Utils\Common::notice( $message['google_calendar'] ) ?>
                <?php include '_googleCalendarForm.php' ?>
            </div>
            <div class="tab-pane" id="ab_settings_woocommerce">
                <?php isset( $message['woocommerce'] ) && \Bookly\Lib\Utils\Common::notice( $message['woocommerce'] ) ?>
                <?php \Bookly\Lib\Utils\Common::notice( $wc_cart_error_message, 'notice-error' ) ?>
                <?php include '_woocommerce.php' ?>
            </div>
            <div class="tab-pane" id="ab_settings_cart">
                <?php isset( $message['cart'] ) && \Bookly\Lib\Utils\Common::notice( $message['cart'] ) ?>
                <?php include '_cartForm.php' ?>
            </div>
            <?php do_action( 'bookly_backend_settings', $tab, $message, array( '\Bookly\Lib\Utils\Common', 'notice' ) ) ?>
            <div class="tab-pane" id="ab_settings_payments">
                <?php isset( $message['payments'] ) && \Bookly\Lib\Utils\Common::notice( $message['payments'] ) ?>
                <?php include '_paymentsForm.php' ?>
            </div>
            <div class="tab-pane" id="ab_settings_business_hours">
                <?php isset( $message['business_hours'] ) && \Bookly\Lib\Utils\Common::notice( $message['business_hours'] ) ?>
                <?php include '_hoursForm.php' ?>
            </div>
            <div class="tab-pane" id="ab_settings_holidays">
                <?php isset( $message['holidays'] ) && \Bookly\Lib\Utils\Common::notice( $message['holidays'] ) ?>
                <?php include '_holidaysForm.php' ?>
            </div>
            <div class="tab-pane" id="ab_settings_purchase_code">
                <?php isset( $message['purchase_code'] ) && \Bookly\Lib\Utils\Common::notice( $message['purchase_code'], $notice_class ) ?>
                <?php include '_purchaseCodeForm.php' ?>
            </div>
        </div>
    </div>
</div>