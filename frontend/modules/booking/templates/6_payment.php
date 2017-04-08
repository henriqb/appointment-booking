<?php if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly
    echo $progress_tracker;
?>
<?php if ( get_option( 'ab_settings_coupons' ) ) : ?>
    <div style="margin-bottom: 15px!important;" class="ab-row-fluid ab-info-text-coupon"><?php echo $info_text_coupon ?></div>
    <div class="ab-row-fluid ab-list" style="overflow: visible!important;">
        <div class="ab-formGroup ab-full ab-lastGroup">
            <span style="display: inline-block;"><?php echo \Bookly\Lib\Utils\Common::getTranslatedOption( 'ab_appearance_text_label_coupon' ) ?></span>
            <div class="ab-formField" style="display: inline-block; white-space: nowrap;">
                <?php if ( $coupon_code ) : ?>
                    <?php echo esc_attr( $coupon_code ) . ' ✓' ?>
                <?php else : ?>
                    <input class="ab-formElement ab-user-coupon" name="ab_coupon" type="text" value="<?php echo esc_attr( $coupon_code ) ?>" />
                    <button class="ab-btn ladda-button btn-apply-coupon" data-style="zoom-in" data-spinner-size="40">
                        <span class="ab-label"><?php echo \Bookly\Lib\Utils\Common::getTranslatedOption( 'ab_appearance_text_button_apply' ) ?></span><span class="spinner"></span>
                    </button>
                <?php endif ?>
            </div>
            <div class="ab-label-error ab-bold ab-coupon-error"></div>
        </div>
    </div>
<?php endif ?>

<div class="ab-payment-nav">
    <div style="margin-bottom: 15px!important;" class="ab-row-fluid"><?php echo $info_text ?></div>
    <?php if ( $pay_local ) : ?>
        <div class="ab-row-fluid ab-list">
            <label>
                <input type="radio" class="ab-payment" checked="checked" name="payment-method-<?php echo $form_id ?>" value="local"/>
                <?php echo \Bookly\Lib\Utils\Common::getTranslatedOption( 'ab_appearance_text_label_pay_locally' ) ?>
            </label>
        </div>
    <?php endif ?>

    <?php if ( $pay_paypal ) : ?>
        <div class="ab-row-fluid ab-list">
            <label>
                <input type="radio" class="ab-payment" <?php checked( !$pay_local ) ?> name="payment-method-<?php echo $form_id ?>" value="paypal"/>
                <?php echo \Bookly\Lib\Utils\Common::getTranslatedOption( 'ab_appearance_text_label_pay_paypal' ) ?>
                <img src="<?php echo plugins_url( 'frontend/resources/images/paypal.png', AB_PATH . '/main.php' ) ?>" style="margin-left: 10px;" alt="paypal" />
            </label>
            <?php if ( $payment['gateway'] == Bookly\Lib\Entities\Payment::TYPE_PAYPAL && $payment['status'] == 'error' ) : ?>
                <div class="ab-label-error ab-bold" style="padding-top: 5px;">* <?php echo $payment['data'] ?></div>
            <?php endif ?>
        </div>
    <?php endif ?>

    <?php if ( $pay_authorizenet ) : ?>
        <div class="ab-row-fluid ab-list">
            <label>
                <input type="radio" class="ab-payment" <?php checked( !$pay_local && !$pay_paypal ) ?> name="payment-method-<?php echo $form_id ?>" value="card" data-form="authorizenet" />
                <?php echo \Bookly\Lib\Utils\Common::getTranslatedOption( 'ab_appearance_text_label_pay_ccard' ) ?>
                <img src="<?php echo plugins_url( 'resources/images/cards.png', dirname( dirname( dirname( __FILE__ ) ) ) ) ?>" style="margin-left: 10px;" alt="cards" />
            </label>
            <form class="ab-authorizenet" style="<?php if ( $pay_local || $pay_paypal ) echo "display: none;"; ?> margin-top: 15px;">
                <?php include '_card_payment.php' ?>
            </form>
        </div>
    <?php endif ?>

    <?php if ( $pay_stripe ) : ?>
        <div class="ab-row-fluid ab-list">
            <label>
                <input type="radio" class="ab-payment" <?php checked( !$pay_local && !$pay_paypal && !$pay_authorizenet ) ?> name="payment-method-<?php echo $form_id ?>" value="card" data-form="stripe" />
                <?php echo \Bookly\Lib\Utils\Common::getTranslatedOption( 'ab_appearance_text_label_pay_ccard' ) ?>
                <img src="<?php echo plugins_url( 'resources/images/cards.png', dirname( dirname( dirname( __FILE__ ) ) ) ) ?>" style="margin-left: 10px;" alt="cards" />
            </label>
            <?php if ( get_option( 'ab_stripe_publishable_key' ) != '' ) : ?>
                <script type="text/javascript" src="https://js.stripe.com/v2/"></script>
            <?php endif ?>
            <form class="ab-stripe" style="<?php if ( $pay_local || $pay_paypal || $pay_authorizenet ) echo "display: none;"; ?> margin-top: 15px;">
                <input type="hidden" id="publishable_key" value="<?php echo get_option( 'ab_stripe_publishable_key' ) ?>">
                <?php include '_card_payment.php' ?>
            </form>
        </div>
    <?php endif ?>

    <?php if ( $pay_2checkout ) : ?>
        <div class="ab-row-fluid ab-list">
            <label>
                <input type="radio" class="ab-payment" <?php checked( !$pay_local && !$pay_paypal && !$pay_authorizenet && !$pay_stripe ) ?> name="payment-method-<?php echo $form_id ?>" value="2checkout"/>
                <?php echo \Bookly\Lib\Utils\Common::getTranslatedOption( 'ab_appearance_text_label_pay_ccard' ) ?>
                <img src="<?php echo plugins_url( 'resources/images/cards.png', dirname( dirname( dirname( __FILE__ ) ) ) ) ?>" style="margin-left: 10px;" alt="cards" />
            </label>
        </div>
    <?php endif ?>

    <?php if ( $pay_payulatam ) : ?>
        <div class="ab-row-fluid ab-list">
            <label>
                <input type="radio" class="ab-payment" <?php checked( !$pay_local && !$pay_paypal && !$pay_authorizenet && !$pay_stripe && !$pay_2checkout ) ?> name="payment-method-<?php echo $form_id ?>" value="payulatam"/>
                <?php echo \Bookly\Lib\Utils\Common::getTranslatedOption( 'ab_appearance_text_label_pay_ccard' ) ?>
                <img src="<?php echo plugins_url( 'resources/images/cards.png', dirname( dirname( dirname( __FILE__ ) ) ) ) ?>" style="margin-left: 10px;" alt="cards" />
            </label>
            <?php if ( $payment['gateway'] == Bookly\Lib\Entities\Payment::TYPE_PAYULATAM && $payment['status'] == 'error' ) : ?>
                <div class="ab-label-error ab-bold" style="padding-top: 5px;">* <?php echo $payment['data'] ?></div>
            <?php endif ?>
        </div>
    <?php endif ?>
    <div class="ab-row-fluid ab-list" style="display: none">
        <input type="radio" class="ab-coupon-free" name="payment-method-<?php echo $form_id ?>" value="coupon" />
    </div>

    <?php if ( $pay_payson ) : ?>
        <div class="ab-row-fluid ab-list">
            <label>
                <input type="radio" class="ab-payment" <?php checked( !$pay_local && !$pay_paypal && !$pay_authorizenet && !$pay_stripe && !$pay_payulatam ) ?> name="payment-method-<?php echo $form_id ?>" value="payson"/>
                <?php echo \Bookly\Lib\Utils\Common::getTranslatedOption( 'ab_appearance_text_label_pay_ccard' ) ?>
                <img src="<?php echo plugins_url( 'resources/images/cards.png', dirname( dirname( dirname( __FILE__ ) ) ) ) ?>" style="margin-left: 10px;" alt="cards" />
            </label>
            <?php if ( $payment['gateway'] == Bookly\Lib\Entities\Payment::TYPE_PAYSON && $payment['status'] == 'error' ) : ?>
                <div class="ab-label-error ab-bold" style="padding-top: 5px;">* <?php echo $payment['data'] ?></div>
            <?php endif ?>
        </div>
    <?php endif ?>

    <?php if ( $pay_mollie ) : ?>
        <div class="ab-row-fluid ab-list">
            <label>
                <input type="radio" class="ab-payment" <?php checked( !$pay_local && !$pay_paypal && !$pay_authorizenet && !$pay_stripe && !$pay_payulatam && !$pay_payson ) ?> name="payment-method-<?php echo $form_id ?>" value="mollie"/>
                <?php echo \Bookly\Lib\Utils\Common::getTranslatedOption( 'ab_appearance_text_label_pay_mollie' ) ?>
                <img src="<?php echo plugins_url( 'resources/images/mollie.png', dirname( dirname( dirname( __FILE__ ) ) ) ) ?>" style="margin-left: 10px;" alt="mollie" />
            </label>
            <?php if ( $payment['gateway'] == Bookly\Lib\Entities\Payment::TYPE_MOLLIE && $payment['status'] == 'error' ) : ?>
                <div class="ab-label-error ab-bold" style="padding-top: 5px;">* <?php echo $payment['data'] ?></div>
            <?php endif ?>
        </div>
    <?php endif ?>
</div>

<?php if ( $pay_local ) : ?>
    <div class="ab-local-payment-button ab-row-fluid ab-nav-steps">
        <button class="ab-left ab-back-step ab-btn ladda-button" data-style="zoom-in"  data-spinner-size="40">
            <span class="ladda-label"><?php echo \Bookly\Lib\Utils\Common::getTranslatedOption( 'ab_appearance_text_button_back' ) ?></span>
        </button>
        <button class="ab-right ab-next-step ab-btn ladda-button" data-style="zoom-in" data-spinner-size="40">
            <span class="ladda-label"><?php echo \Bookly\Lib\Utils\Common::getTranslatedOption( 'ab_appearance_text_button_next' ) ?></span>
        </button>
    </div>
<?php endif ?>

<?php if ( $pay_paypal ) : ?>
    <div class="ab-paypal-payment-button ab-row-fluid ab-nav-steps" <?php if ( $pay_local ) echo 'style="display:none"' ?>>
        <?php Bookly\Lib\Payment\PayPal::renderForm( $form_id ) ?>
    </div>
<?php endif ?>

<?php if ( $pay_2checkout ) : ?>
    <div class="ab-2checkout-payment-button ab-row-fluid ab-nav-steps" <?php if ( $pay_local || $pay_paypal ) echo 'style="display:none"' ?>>
        <?php Bookly\Lib\Payment\TwoCheckout::renderForm( $form_id ) ?>
    </div>
<?php endif ?>

<?php if ( $pay_payulatam ) : ?>
    <div class="ab-payulatam-payment-button ab-row-fluid ab-nav-steps" <?php if ( $pay_local || $pay_paypal || $pay_2checkout ) echo 'style="display:none"' ?>>
        <?php Bookly\Lib\Payment\PayuLatam::renderForm( $form_id ) ?>
    </div>
<?php endif ?>

<?php if ( $pay_authorizenet || $pay_stripe ) : ?>
    <div class="ab-card-payment-button ab-row-fluid ab-nav-steps" <?php if ( $pay_local || $pay_paypal || $pay_2checkout || $pay_payulatam ) echo 'style="display:none"' ?>>
        <button class="ab-left ab-back-step ab-btn ladda-button" data-style="zoom-in" data-spinner-size="40">
            <span class="ladda-label"><?php echo \Bookly\Lib\Utils\Common::getTranslatedOption( 'ab_appearance_text_button_back' ) ?></span>
        </button>
        <button class="ab-right ab-next-step ab-btn ladda-button" data-style="zoom-in" data-spinner-size="40">
            <span class="ladda-label"><?php echo \Bookly\Lib\Utils\Common::getTranslatedOption( 'ab_appearance_text_button_next' ) ?></span>
        </button>
    </div>
<?php endif ?>

<?php if ( $pay_payson ) : ?>
    <div class="ab-payson-payment-button ab-row-fluid ab-nav-steps" <?php if ( $pay_local || $pay_paypal || $pay_2checkout || $pay_payulatam || $pay_authorizenet || $pay_stripe ) echo 'style="display:none"' ?>>
        <?php Bookly\Lib\Payment\Payson::renderForm( $form_id ) ?>
    </div>
<?php endif ?>

<?php if ( $pay_mollie ) : ?>
    <div class="ab-mollie-payment-button ab-row-fluid ab-nav-steps" <?php if ( $pay_local || $pay_paypal || $pay_2checkout || $pay_payulatam || $pay_authorizenet || $pay_stripe || $pay_payson ) echo 'style="display:none"' ?>>
        <?php Bookly\Lib\Payment\Mollie::renderForm( $form_id ) ?>
    </div>
<?php endif ?>

<div class="ab-coupon-payment-button ab-row-fluid ab-nav-steps" style="display: none">
    <button class="ab-left ab-back-step ab-btn ladda-button" data-style="zoom-in" data-spinner-size="40">
        <span class="ladda-label"><?php echo \Bookly\Lib\Utils\Common::getTranslatedOption( 'ab_appearance_text_button_back' ) ?></span>
    </button>
    <button class="ab-right ab-next-step ab-coupon-payment ab-btn ladda-button" data-style="zoom-in" data-spinner-size="40">
        <span class="ladda-label"><?php echo \Bookly\Lib\Utils\Common::getTranslatedOption( 'ab_appearance_text_button_next' ) ?></span>
    </button>
</div>
