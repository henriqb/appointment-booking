<?php if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly ?>
<form method="post" action="<?php echo esc_url( add_query_arg( 'tab', 'cart' ) ) ?>"  class="ab-settings-form">
    <div class="form-group">
        <label for="ab_settings_step_cart_enabled"><?php _e( 'Cart', 'bookly' ) ?></label>
        <p class="help-block"><?php _e( 'If cart is enabled then your clients will be able to book several appointments at once. Please note that WooCommerce integration must be disabled.', 'bookly' ) ?></p>
        <?php \Bookly\Lib\Utils\Common::optionToggle( 'ab_settings_step_cart_enabled' ) ?>
    </div>
    <div class="form-group">
        <label><?php _e( 'Columns', 'bookly' ) ?></label>
        <?php \Bookly\Lib\Utils\Common::optionFlags( 'ab_cart_show_columns', array( 'f' => array( 'service', \Bookly\Lib\Utils\Common::getTranslatedOption( 'ab_appearance_text_label_service' ) ), 't' => array( 'date', __( 'Date', 'bookly' ) ), 'time' => array( 'time', __( 'Time', 'bookly' ) ), 'employee' => array( 'employee', \Bookly\Lib\Utils\Common::getTranslatedOption( 'ab_appearance_text_label_employee' ) ), 'price' => array( 'price', __( 'Price', 'bookly' ) ) ) ) ?>
    </div>
    <?php \Bookly\Lib\Utils\Common::submitButton() ?>
    <?php \Bookly\Lib\Utils\Common::resetButton() ?>
</form>