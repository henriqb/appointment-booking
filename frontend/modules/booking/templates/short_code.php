<?php if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly ?>
<!--
Plugin Name: Bookly – Responsive WordPress Appointment Booking and Scheduling Plugin
Plugin URI: http://booking-wp-plugin.com
Version: <?php echo \Bookly\Lib\Plugin::version() ?>
-->
<?php if ( $print_assets ) include '_css.php' ?>
<div id="ab-booking-form-<?php echo $form_id ?>" class="ab-booking-form" data-form_id="<?php echo $form_id ?>">
    <div style="text-align: center"><img src="<?php echo includes_url( 'js/tinymce/skins/lightgray/img/loader.gif' ) ?>" alt="<?php esc_attr_e( 'Loading...', 'bookly' ) ?>" /></div>
</div>
<script type="text/javascript">
    (function (win, fn) {
        var done = false, top = true,
            doc = win.document,
            root = doc.documentElement,
            modern = doc.addEventListener,
            add = modern ? 'addEventListener' : 'attachEvent',
            rem = modern ? 'removeEventListener' : 'detachEvent',
            pre = modern ? '' : 'on',
            init = function(e) {
                if (e.type == 'readystatechange') if (doc.readyState != 'complete') return;
                (e.type == 'load' ? win : doc)[rem](pre + e.type, init, false);
                if (!done) { done = true; fn.call(win, e.type || e); }
            },
            poll = function() {
                try { root.doScroll('left'); } catch(e) { setTimeout(poll, 50); return; }
                init('poll');
            };
        if (doc.readyState == 'complete') fn.call(win, 'lazy');
        else {
            if (!modern) if (root.doScroll) {
                try { top = !win.frameElement; } catch(e) { }
                if (top) poll();
            }
            doc[add](pre + 'DOMContentLoaded', init, false);
            doc[add](pre + 'readystatechange', init, false);
            win[add](pre + 'load', init, false);
        }
    })(window, function() {
        window.bookly({
            ajaxurl        : <?php echo json_encode( $ajax_url ) ?>,
            form_id        : <?php echo json_encode( $form_id ) ?>,
            attributes     : <?php echo json_encode( $attrs ) ?>,
            status         : <?php echo json_encode( $status ) ?>,
            start_of_week  : <?php echo (int) get_option( 'start_of_week' ) ?>,
            show_calendar  : <?php echo (int) get_option( 'ab_appearance_show_calendar' ) ?>,
            skip_steps     : <?php echo json_encode( $skip_steps ) ?>,
            date_format    : <?php echo json_encode( \Bookly\Lib\Utils\DateTime::convertFormat( 'date', \Bookly\Lib\Utils\DateTime::FORMAT_PICKADATE ) ) ?>,
            final_step_url : <?php echo json_encode( get_option( 'ab_settings_final_step_url' ) ) ?>,
            intlTelInput   : {
            <?php if ( get_option( 'ab_settings_phone_default_country' ) == 'disabled' ) : ?>
                enabled  : false
            <?php else : ?>
                enabled  : true,
                utils    : <?php echo json_encode( plugins_url( 'intlTelInput.utils.js', AB_PATH . '/frontend/resources/js/intlTelInput.utils.js' ) ) ?>,
                country  : <?php echo json_encode( get_option( 'ab_settings_phone_default_country' ) ) ?>
            <?php endif ?>
            },
            woocommerce    : {
                enabled  : <?php echo $woocommerce_enabled = intval( class_exists( 'WooCommerce' ) && get_option( 'ab_woocommerce_enabled' ) && get_option( 'ab_woocommerce_product' ) && ( WC()->cart->get_cart_url() !== false ) ) ?>,
                cart_url : <?php echo json_encode( $woocommerce_enabled ? WC()->cart->get_cart_url() : '' ) ?>
            },
            cart           : {
                enabled  : <?php echo $woocommerce_enabled ? '0' : (int) get_option( 'ab_settings_step_cart_enabled' ) ?>,
                after_details: <?php echo (int) get_option( 'ab_custom_fields_per_service' ) ?>
            }
        });
    });
</script>