<?php if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly ?>
<form method="post" action="<?php echo esc_url( add_query_arg( 'tab', 'payments' ) ) ?>" class="ab-settings-form">
    <div class="form-group">
        <label for="ab_currency"><?php _e( 'Currency', 'bookly' ) ?></label>
        <select id="ab_currency" class="form-control" name="ab_currency">
            <?php foreach ( \Bookly\Lib\Config::getCurrencyCodes() as $code ) : ?>
                <option value="<?php echo $code ?>" <?php selected( get_option( 'ab_currency' ), $code ) ?> ><?php echo $code ?></option>
            <?php endforeach ?>
        </select>
    </div>
    <div class="form-group">
        <label for="ab_settings_coupons"><?php _e( 'Coupons', 'bookly' ) ?></label>
        <?php \Bookly\Lib\Utils\Common::optionToggle( 'ab_settings_coupons' ) ?>
    </div>
    <div class="form-group">
        <label for="ab_settings_pay_locally"><?php _e( 'Service paid locally', 'bookly' ) ?></label>
        <?php \Bookly\Lib\Utils\Common::optionToggle( 'ab_settings_pay_locally', array( 'f' => array( 'disabled', __( 'Disabled', 'bookly' ) ), 't' => array( '1', __( 'Enabled', 'bookly' ) ) ) ) ?>
    </div>
    <div class="form-group">
        <div class="panel panel-default">
            <div class="panel-heading">
                <label for="ab_2checkout">2Checkout</label>
                <img style="margin-left: 10px; float: right" src="<?php echo plugins_url( 'frontend/resources/images/2Checkout.png', AB_PATH . '/main.php' ) ?>" />
            </div>
            <div class="panel-body">
                <div class="form-group">
                    <?php \Bookly\Lib\Utils\Common::optionToggle( 'ab_2checkout', array( 'f' => array( 'disabled', __( 'Disabled', 'bookly' ) ), 't' => array( 'standard_checkout', __( '2Checkout Standard Checkout', 'bookly' ) ) ) ) ?>
                </div>
                <div class="form-group ab-2checkout">
                    <fieldset class="ab-instruction">
                        <legend><?php _e( 'Instructions', 'bookly' ) ?></legend>
                        <div>
                            <div style="margin-bottom: 10px">
                                <?php _e( 'In <b>Checkout Options</b> of your 2Checkout account do the following steps:', 'bookly' ) ?>
                            </div>
                            <ol>
                                <li><?php _e( 'In <b>Direct Return</b> select <b>Header Redirect (Your URL)</b>.', 'bookly' ) ?></li>
                                <li><?php _e( 'In <b>Approved URL</b> enter the URL of your booking page.', 'bookly' ) ?></li>
                            </ol>
                            <div style="margin-top: 10px">
                                <?php _e( 'Finally provide the necessary information in the form below.', 'bookly' ) ?>
                            </div>
                        </div>
                    </fieldset>
                </div>
                <div class="form-group ab-2checkout">
                    <label for="ab_2checkout_api_seller_id"><?php _e( 'Account Number', 'bookly' ) ?></label>
                    <input id="ab_2checkout_api_seller_id" class="form-control" type="text" name="ab_2checkout_api_seller_id" value="<?php echo get_option( 'ab_2checkout_api_seller_id' ) ?>"/>
                </div>
                <div class="form-group ab-2checkout">
                    <label for="ab_2checkout_api_secret_word"><?php _e( 'Secret Word', 'bookly' ) ?></label>
                    <input id="ab_2checkout_api_secret_word" class="form-control" type="text" name="ab_2checkout_api_secret_word" value="<?php echo get_option( 'ab_2checkout_api_secret_word' ) ?>"/>
                </div>
                <div class="form-group ab-2checkout">
                    <label for="ab_2checkout_sandbox"><?php _e( 'Sandbox Mode', 'bookly' ) ?></label>
                    <?php \Bookly\Lib\Utils\Common::optionToggle( 'ab_2checkout_sandbox', array( 'f' => array( 0, __( 'No', 'bookly' ) ), 't' => array( 1, __( 'Yes', 'bookly' ) ) ) ) ?>
                </div>
            </div>
        </div>
    </div>
    <div class="panel panel-default">
        <div class="panel-heading">
            <label for="ab_paypal_type">PayPal</label>
            <img style="margin-left: 10px; float: right" src="<?php echo plugins_url( 'frontend/resources/images/paypal.png', AB_PATH . '/main.php' ) ?>" />
        </div>
        <div class="panel-body">
            <div class="form-group">
                <?php \Bookly\Lib\Utils\Common::optionToggle( 'ab_paypal_type', array( 'f' => array( 'disabled', __( 'Disabled', 'bookly' ) ), 't' => array( 'ec', 'PayPal Express Checkout' ) ) ) ?>
            </div>
            <div class="form-group ab-paypal-ec">
                <label for="ab_paypal_api_username"><?php _e( 'API Username', 'bookly' ) ?></label>
                <input id="ab_paypal_api_username" class="form-control" type="text" name="ab_paypal_api_username" value="<?php echo get_option( 'ab_paypal_api_username' ) ?>"/>
            </div>
            <div class="form-group ab-paypal-ec">
                <label for="ab_paypal_api_password"><?php _e( 'API Password', 'bookly' ) ?></label>
                <input id="ab_paypal_api_password" class="form-control" type="text" name="ab_paypal_api_password" value="<?php echo get_option( 'ab_paypal_api_password' ) ?>"/>
            </div>
            <div class="form-group ab-paypal-ec">
                <label for="ab_paypal_api_signature"><?php _e( 'API Signature', 'bookly' ) ?></label>
                <input id="ab_paypal_api_signature" class="form-control" type="text" name="ab_paypal_api_signature" value="<?php echo get_option( 'ab_paypal_api_signature' ) ?>"/>
            </div>
            <div class="form-group ab-paypal-ec">
                <label for="ab_paypal_ec_mode"><?php _e( 'Sandbox Mode', 'bookly' ) ?></label>
                <?php \Bookly\Lib\Utils\Common::optionToggle( 'ab_paypal_ec_mode', array( 't' => array( '.sandbox', __( 'Yes', 'bookly' ) ), 'f' => array( '', __( 'No', 'bookly' ) ) ) ) ?>
            </div>
        </div>
    </div>
    <div class="panel panel-default">
        <div class="panel-heading">
            <label for="ab_authorizenet_type">Authorize.Net</label>
            <img style="margin-left: 10px; float: right" src="<?php echo plugins_url( 'frontend/resources/images/authorize_net.png', AB_PATH . '/main.php' ) ?>" />
        </div>
        <div class="panel-body">
            <div class="form-group">
                <?php \Bookly\Lib\Utils\Common::optionToggle( 'ab_authorizenet_type', array( 'f' => array( 'disabled', __( 'Disabled', 'bookly' ) ), 't' => array( 'aim', 'Authorize.Net AIM' ) ) ) ?>
            </div>
            <div class="form-group authorizenet">
                <label for="ab_authorizenet_api_login_id"><?php _e( 'API Login ID', 'bookly' ) ?></label>
                <input id="ab_authorizenet_api_login_id" class="form-control" type="text" name="ab_authorizenet_api_login_id" value="<?php echo get_option( 'ab_authorizenet_api_login_id' ) ?>"/>
            </div>
            <div class="form-group authorizenet">
                <label for="ab_authorizenet_transaction_key"><?php _e( 'API Transaction Key', 'bookly' ) ?></label>
                <input id="ab_authorizenet_transaction_key" class="form-control" type="text" name="ab_authorizenet_transaction_key" value="<?php echo get_option( 'ab_authorizenet_transaction_key' ) ?>"/>
            </div>
            <div class="form-group authorizenet">
                <label for="ab_authorizenet_sandbox"><?php _e( 'Sandbox Mode', 'bookly' ) ?></label>
                <?php \Bookly\Lib\Utils\Common::optionToggle( 'ab_authorizenet_sandbox', array( 't' => array( 1, __( 'Yes', 'bookly' ) ), 'f' => array( 0, __( 'No', 'bookly' ) ) ) ) ?>
            </div>
        </div>
    </div>
    <div class="panel panel-default">
        <div class="panel-heading">
            <label for="ab_stripe">Stripe</label>
            <img style="margin-left: 10px; float: right" src="<?php echo plugins_url( 'frontend/resources/images/stripe.png', AB_PATH . '/main.php' ) ?>" />
        </div>
        <div class="panel-body">
            <div class="form-group">
                <?php \Bookly\Lib\Utils\Common::optionToggle( 'ab_stripe', array( 'f' => array( 'disabled', __( 'Disabled', 'bookly' ) ), 't' => array( '1', __( 'Enabled', 'bookly' ) ) ) ) ?>
            </div>
            <div class="form-group ab-stripe">
                <fieldset class="ab-instruction">
                    <legend><?php _e( 'Instructions', 'bookly' ) ?></legend>
                    <div>
                        <div style="margin-bottom: 10px">
                            <?php _e( 'If <b>Publishable Key</b> is provided then Bookly will use <a href="https://stripe.com/docs/stripe.js" target="_blank">Stripe.js</a><br/>for collecting credit card details.', 'bookly' ) ?>
                        </div>
                    </div>
                </fieldset>
            </div>
            <div class="form-group ab-stripe">
                <label for="ab_stripe_secret_key"><?php _e( 'Secret Key', 'bookly' ) ?></label>
                <input id="ab_stripe_secret_key" class="form-control" type="text" name="ab_stripe_secret_key" value="<?php echo get_option( 'ab_stripe_secret_key' ) ?>"/>
            </div>
            <div class="form-group ab-stripe">
                <label for="ab_stripe_publishable_key"><?php _e( 'Publishable Key', 'bookly' ) ?></label>
                <input id="ab_stripe_publishable_key" class="form-control" type="text" name="ab_stripe_publishable_key" value="<?php echo get_option( 'ab_stripe_publishable_key' ) ?>"/>
            </div>
        </div>
    </div>
    <div class="panel panel-default">
        <div class="panel-heading">
            <label for="ab_payulatam">PayU Latam</label>
            <img style="margin-left: 10px; float: right" src="<?php echo plugins_url( 'frontend/resources/images/payu_latam.png', AB_PATH . '/main.php' ) ?>" />
        </div>
        <div class="panel-body">
            <div class="form-group">
                <?php \Bookly\Lib\Utils\Common::optionToggle( 'ab_payulatam', array( 'f' => array( 'disabled', __( 'Disabled', 'bookly' ) ), 't' => array( '1', __( 'Enabled', 'bookly' ) ) ) ) ?>
            </div>
            <div class="form-group ab-payulatam">
                <label for="ab_payulatam_api_key"><?php _e( 'API Key', 'bookly' ) ?></label>
                <input id="ab_payulatam_api_key" class="form-control" type="text" name="ab_payulatam_api_key" value="<?php echo get_option( 'ab_payulatam_api_key' ) ?>"/>
            </div>
            <div class="form-group ab-payulatam">
                <label for="ab_payulatam_api_account_id"><?php _e( 'Account ID', 'bookly' ) ?></label>
                <input id="ab_payulatam_api_account_id" class="form-control" type="text" name="ab_payulatam_api_account_id" value="<?php echo get_option( 'ab_payulatam_api_account_id' ) ?>"/>
            </div>
            <div class="form-group ab-payulatam">
                <label for="ab_payulatam_api_merchant_id"><?php _e( 'Merchant ID', 'bookly' ) ?></label>
                <input id="ab_payulatam_api_merchant_id" class="form-control" type="text" name="ab_payulatam_api_merchant_id" value="<?php echo get_option( 'ab_payulatam_api_merchant_id' ) ?>"/>
            </div>
            <div class="form-group ab-payulatam">
                <label for="ab_payulatam_sandbox"><?php _e( 'Sandbox Mode', 'bookly' ) ?></label>
                <?php \Bookly\Lib\Utils\Common::optionToggle( 'ab_payulatam_sandbox', array( 'f' => array( 0, __( 'No', 'bookly' ) ), 't' => array( 1, __( 'Yes', 'bookly' ) ) ) ) ?>
            </div>
        </div>
    </div>
    <div class="panel panel-default">
        <div class="panel-heading">
            <label for="ab_payson">Payson</label>
            <img style="margin-left: 10px; float: right" src="<?php echo plugins_url( 'frontend/resources/images/payson.png', AB_PATH . '/main.php' ) ?>" />
        </div>
        <div class="panel-body">
            <div class="form-group">
                <?php \Bookly\Lib\Utils\Common::optionToggle( 'ab_payson', array( 'f' => array( 'disabled', __( 'Disabled', 'bookly' ) ), 't' => array( '1', __( 'Enabled', 'bookly' ) ) ) ) ?>
            </div>
            <div class="form-group ab-payson">
                <label for="ab_payson_api_agent_id"><?php _e( 'Agent ID', 'bookly' ) ?></label>
                <input id="ab_payson_api_agent_id" class="form-control" type="text" name="ab_payson_api_agent_id" value="<?php echo get_option( 'ab_payson_api_agent_id' ) ?>"/>
            </div>
            <div class="form-group ab-payson">
                <label for="ab_payson_api_key"><?php _e( 'API Key', 'bookly' ) ?></label>
                <input id="ab_payson_api_key" class="form-control" type="text" name="ab_payson_api_key" value="<?php echo get_option( 'ab_payson_api_key' ) ?>"/>
            </div>
            <div class="form-group ab-payson">
                <label for="ab_payson_api_receiver_email"><?php _e( 'Receiver Email (login)', 'bookly' ) ?></label>
                <input id="ab_payson_api_receiver_email" class="form-control" type="text" name="ab_payson_api_receiver_email" value="<?php echo get_option( 'ab_payson_api_receiver_email' ) ?>"/>
            </div>
            <div class="form-group ab-payson">
                <label for="ab_payson_funding"><?php _e( 'Funding', 'bookly' ) ?></label>
                <?php \Bookly\Lib\Utils\Common::optionFlags( 'ab_payson_funding', array( 'f' => array( 'CREDITCARD', __( 'Card', 'bookly' ) ), 't' => array( 'INVOICE', __( 'Invoice', 'bookly' ) ) ) ) ?>
            </div>
            <div class="form-group ab-payson">
                <label for="ab_payson_fees_payer"><?php _e( 'Fees Payer', 'bookly' ) ?></label>
                <?php \Bookly\Lib\Utils\Common::optionToggle( 'ab_payson_fees_payer', array( 'f' => array( 'PRIMARYRECEIVER', __( 'I am', 'bookly' ) ), 't' => array( 'SENDER', __( 'Client', 'bookly' ) ) ) ) ?>
            </div>
            <div class="form-group ab-payson">
                <label for="ab_payson_sandbox"><?php _e( 'Sandbox Mode', 'bookly' ) ?></label>
                <?php \Bookly\Lib\Utils\Common::optionToggle( 'ab_payson_sandbox', array( 'f' => array( 0, __( 'No', 'bookly' ) ), 't' => array( 1, __( 'Yes', 'bookly' ) ) ) ) ?>
            </div>
        </div>
    </div>
    <div class="panel panel-default">
        <div class="panel-heading">
            <label for="ab_mollie">Mollie</label>
            <img style="margin-left: 10px; float: right" src="<?php echo plugins_url( 'frontend/resources/images/mollie.png', AB_PATH . '/main.php' ) ?>" />
        </div>
        <div class="panel-body">
            <div class="form-group">
                <?php \Bookly\Lib\Utils\Common::optionToggle( 'ab_mollie', array( 'f' => array( 'disabled', __( 'Disabled', 'bookly' ) ), 't' => array( '1', __( 'Enabled', 'bookly' ) ) ) ) ?>
            </div>
            <div class="form-group ab-mollie">
                <label for="ab_mollie_api_key"><?php _e( 'API Key', 'bookly' ) ?></label>
                <input id="ab_mollie_api_key" class="form-control" type="text" name="ab_mollie_api_key" value="<?php echo get_option( 'ab_mollie_api_key' ) ?>"/>
            </div>
        </div>
    </div>
    <?php \Bookly\Lib\Utils\Common::submitButton() ?>
    <?php \Bookly\Lib\Utils\Common::resetButton( 'ab-payments-reset' ) ?>
</form>