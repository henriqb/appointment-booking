<?php if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly ?>
<form enctype="multipart/form-data" method="post" action="<?php echo esc_url( add_query_arg( 'tab', 'purchase_code' ) ) ?>" class="ab-settings-form" id="purchase_code">
    <div class="form-group">
        <fieldset class="ab-instruction">
            <legend><?php _e( 'Instructions', 'bookly' ) ?></legend>
            <div><?php _e( 'Upon providing the purchase code you will have access to free updates of Bookly. Updates may contain functionality improvements and important security fixes. For more information on where to find your purchase code see this <a href="https://help.market.envato.com/hc/en-us/articles/202822600-Where-can-I-find-my-Purchase-Code-" target="_blank">page</a>.', 'bookly' ) ?></div>
        </fieldset>
    </div>
    <div class="form-group">
        <label for="ab_envato_purchase_code"><?php _e( 'Purchase Code', 'bookly' ) ?></label>
        <input id="ab_envato_purchase_code" class="purchase-code form-control" type="text" name="purchase_code[bookly]" value="<?php echo get_option( 'ab_envato_purchase_code' ) ?>" />
    </div>
    <?php do_action( 'bookly_backend_purchase_code_form' ) ?>
    <?php \Bookly\Lib\Utils\Common::submitButton() ?>
    <?php \Bookly\Lib\Utils\Common::resetButton() ?>
</form>