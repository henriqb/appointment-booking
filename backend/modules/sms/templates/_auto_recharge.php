<?php if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly ?>
<div style="max-width: 500px;">
    <div class="panel panel-default">
        <div class="panel-body">
            <?php _e( 'We will only charge your PayPal account when your balance falls below $10.', 'bookly' ) ?>
        </div>
    </div>
    <div id="ab-preapproval-form-init">
        <div class="col-sm-12 form-horizontal">
            <div class="form-group">
                <label for="ab_auto_recharge_amount" class="col-sm-2 control-label"><?php _e( 'Amount', 'bookly' ) ?></label>
                <div class="col-sm-10">
                    <select id="ab_auto_recharge_amount" class="form-control">
                        <?php foreach ( array( 10, 25, 50, 100 ) as $amount ) : ?>
                            <?php printf( '<option value="%1$s" %2$s>$%1$s</option>', $amount, selected( $recharge_amount == $amount, true, false ) ) ?>
                        <?php endforeach ?>
                    </select>
                </div>
            </div>
        </div>
        <div class="col-sm-12">
            <button id="ab-preapproval-create" data-spinner-size="40" data-style="zoom-in" class="btn btn-info ladda-button"><span class="ladda-label"><?php _e( 'Enable Auto-Recharge', 'bookly' ) ?></span></button>
            <button class="btn btn-link" disabled="disabled"><?php _e( 'Disable Auto-Recharge', 'bookly' ) ?></button>
        </div>
    </div>
    <div id="ab-preapproval-form-decline">
        <div class="col-sm-12 form-horizontal">
            <div class="form-group">
                <label class="col-sm-2 control-label"><?php _e( 'Amount', 'bookly' ) ?></label>
                <div class="col-sm-10">
                    <input type="text" value="$<?php echo $recharge_amount ?>" disabled="disabled" class="form-control">
                </div>
            </div>
        </div>
        <div class="col-sm-12">
            <button data-spinner-size="40" data-style="zoom-in" class="btn btn-default" disabled="disabled"><?php _e( 'Enable Auto-Recharge', 'bookly' ) ?></button>
            <button id="ab-preapproval-decline" data-spinner-size="40" data-style="zoom-in" class="btn btn-info ladda-button"><span class="ladda-label"><?php _e( 'Disable Auto-Recharge', 'bookly' ) ?></span></button>
        </div>
    </div>
</div>