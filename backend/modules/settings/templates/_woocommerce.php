<?php if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly ?>
<form enctype="multipart/form-data" method="post" action="<?php echo esc_url( add_query_arg( 'tab', 'woocommerce' ) ) ?>" class="ab-settings-form" id="woocommerce">
    <div class="form-group">
        <fieldset class="ab-instruction">
            <legend><?php _e( 'Instructions', 'bookly' ) ?></legend>
            <div>
                <div style="margin-bottom: 10px">
                    <?php _e( 'You need to install and activate WooCommerce plugin before using the options below.<br/><br/>Once the plugin is activated do the following steps:', 'bookly' ) ?>
                </div>
                <ol>
                    <li><?php _e( 'Create a product in WooCommerce that can be placed in cart.', 'bookly' ) ?></li>
                    <li><?php _e( 'In the form below enable WooCommerce option.', 'bookly' ) ?></li>
                    <li><?php _e( 'Select the product that you created at step 1 in the drop down list of products.', 'bookly' ) ?></li>
                    <li><?php _e( 'If needed, edit item data which will be displayed in the cart.', 'bookly' ) ?></li>
                </ol>
                <div style="margin-top: 10px">
                    <?php _e( 'Note that once you have enabled WooCommerce option in Bookly the built-in payment methods will no longer work. All your customers will be redirected to WooCommerce cart instead of standard payment step.', 'bookly' ) ?>
                </div>
            </div>
        </fieldset>
    </div>
    <div class="form-group">
        <label for="ab_woocommerce_enabled">WooCommerce</label>
        <?php \Bookly\Lib\Utils\Common::optionToggle( 'ab_woocommerce_enabled' ) ?>
    </div>
    <div class="form-group">
        <label for="ab_woocommerce_product"><?php _e( 'Booking product', 'bookly' ) ?></label>
        <select id="ab_woocommerce_product" class="form-control" name="ab_woocommerce_product">
            <?php foreach ( $candidates as $item ) : ?>
                <option value="<?php echo $item['id'] ?>" <?php selected( get_option( 'ab_woocommerce_product' ), $item['id'] ) ?>>
                    <?php echo $item['name'] ?>
                </option>
            <?php endforeach ?>
        </select>
    </div>
    <div class="form-group">
        <label for="ab_woocommerce_cart_info_name"><?php _e( 'Cart item data', 'bookly' ) ?></label>
        <input id="ab_woocommerce_cart_info_name" class="form-control" type="text" name="ab_woocommerce_cart_info_name" value="<?php echo esc_attr( get_option( 'ab_woocommerce_cart_info_name' ) ) ?>" placeholder="<?php esc_attr_e( 'Enter a name', 'bookly' ) ?>" /><br/>
        <textarea class="form-control" rows="8" name="ab_woocommerce_cart_info_value" placeholder="<?php _e( 'Enter a value', 'bookly' ) ?>"><?php echo esc_textarea( get_option( 'ab_woocommerce_cart_info_value' ) ) ?></textarea><br/>
        <div class="ab-codes">
            <table>
                <tr><td><input value="[[APPOINTMENT_DATE]]" readonly="readonly" onclick="this.select()"> - <?php _e('date of appointment', 'bookly') ?></td></tr>
                <tr><td><input value="[[APPOINTMENT_TIME]]" readonly="readonly" onclick="this.select()"> - <?php _e('time of appointment', 'bookly') ?></td></tr>
                <tr><td><input value="[[CATEGORY_NAME]]" readonly="readonly" onclick="this.select()"> - <?php _e('name of category', 'bookly') ?></td></tr>
                <tr><td><input value="[[SERVICE_NAME]]" readonly="readonly" onclick="this.select()"> - <?php _e('name of service', 'bookly') ?></td></tr>
                <tr><td><input value="[[SERVICE_PRICE]]" readonly="readonly" onclick="this.select()"> - <?php _e('price of service', 'bookly') ?></td></tr>
                <tr><td><input value="[[STAFF_NAME]]" readonly="readonly" onclick="this.select()"> - <?php _e('name of staff', 'bookly') ?></td></tr>
                <tr><td><input value="[[NUMBER_OF_PERSONS]]" readonly="readonly" onclick="this.select()"> - <?php _e('number of persons', 'bookly') ?></td></tr>
            </table>
        </div>
    </div>
    <?php \Bookly\Lib\Utils\Common::submitButton() ?>
    <?php \Bookly\Lib\Utils\Common::resetButton() ?>
</form>