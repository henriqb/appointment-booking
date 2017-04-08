<?php if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly
    /** @var \Bookly\Lib\UserBookingData $userData */
    echo $progress_tracker;
?>

<div class="ab-row-fluid">
    <div class="ab-desc"><?php echo $info_text ?></div>
    <?php if ( $info_text_guest ) : ?>
        <div class="ab-desc ab-guest-desc"><?php echo $info_text_guest ?></div>
    <?php endif ?>
</div>

<form class="ab-details-step">
    <div class="ab-row-fluid ab-col-phone">
        <div class="ab-formGroup ab-left">
            <label class="ab-formLabel"><?php echo \Bookly\Lib\Utils\Common::getTranslatedOption( 'ab_appearance_text_label_name' ) ?></label>
            <div class="ab-formField">
                <input class="ab-formElement ab-full-name" type="text" value="<?php echo esc_attr( $userData->get( 'name' ) ) ?>" maxlength="60"/>
            </div>
            <div class="ab-full-name-error ab-label-error ab-bold"></div>
        </div>
        <div class="ab-formGroup ab-left">
            <label class="ab-formLabel"><?php echo \Bookly\Lib\Utils\Common::getTranslatedOption( 'ab_appearance_text_label_phone' ) ?></label>
            <div class="ab-formField">
                <input class="ab-formElement ab-user-phone-input<?php if ( get_option( 'ab_settings_phone_default_country' ) != 'disabled' ) : ?> ab-user-phone<?php endif ?>" value="<?php echo esc_attr( $userData->get( 'phone' ) ) ?>" type="text" />
            </div>
            <div class="ab-user-phone-error ab-label-error ab-bold"></div>
        </div>
        <div class="ab-formGroup ab-left">
            <label class="ab-formLabel"><?php echo \Bookly\Lib\Utils\Common::getTranslatedOption( 'ab_appearance_text_label_email' ) ?></label>
            <div class="ab-formField" style="margin-right: 0">
                <input class="ab-formElement ab-user-email" maxlength="40" type="text" value="<?php echo esc_attr( $userData->get( 'email' ) ) ?>"/>
            </div>
            <div class="ab-user-email-error ab-label-error ab-bold"></div>
        </div>
    </div>
    <?php foreach ( $custom_fields as $custom_field ) : ?>
        <div class="ab-row-fluid ab-custom-field-row ab-clear" data-id="<?php echo $custom_field->id ?>" data-type="<?php echo $custom_field->type ?>">
            <div class="ab-formGroup ab-full ab-lastGroup">
                <?php if ( $custom_field->type != 'text-content' ) : ?>
                    <label class="ab-formLabel"><?php echo $custom_field->label ?></label>
                <?php endif ?>
                <div class="ab-formField">
                    <?php if ( $custom_field->type == 'text-field' ) : ?>
                        <input type="text" class="ab-formElement ab-custom-field" value="<?php echo esc_attr( @$cf_data[ $custom_field->id ] ) ?>" />
                    <?php elseif ( $custom_field->type == 'textarea' ) : ?>
                        <textarea rows="3" class="ab-formElement ab-custom-field"><?php echo esc_html( @$cf_data[ $custom_field->id ] ) ?></textarea>
                    <?php elseif ( $custom_field->type == 'text-content' ) : ?>
                        <?php echo nl2br( $custom_field->label ) ?>
                    <?php elseif ( $custom_field->type == 'checkboxes' ) : ?>
                        <?php foreach ( $custom_field->items as $item ) : ?>
                            <label>
                                <input type="checkbox" class="ab-custom-field" value="<?php echo esc_attr( $item['value'] ) ?>" <?php checked( @in_array( $item['value'], @$cf_data[ $custom_field->id ] ), true, true ) ?> />
                                <?php echo $item['label'] ?>
                            </label><br/>
                        <?php endforeach ?>
                    <?php elseif ( $custom_field->type == 'radio-buttons' ) : ?>
                        <?php foreach ( $custom_field->items as $item ) : ?>
                            <label>
                                <input type="radio" class="ab-custom-field" name="ab-custom-field-<?php echo $custom_field->id ?>" value="<?php echo esc_attr( $item['value'] ) ?>" <?php checked( $item['value'], @$cf_data[ $custom_field->id ], true ) ?> />
                                <?php echo $item['label'] ?>
                            </label><br/>
                        <?php endforeach ?>
                    <?php elseif ( $custom_field->type == 'drop-down' ) : ?>
                        <select class="ab-formElement ab-custom-field">
                                <option value=""></option>
                            <?php foreach ( $custom_field->items as $item ) : ?>
                                <option value="<?php echo esc_attr( $item['value'] ) ?>" <?php selected( $item['value'], @$cf_data[ $custom_field->id ], true ) ?>><?php echo esc_html( $item['label'] ) ?></option>
                            <?php endforeach ?>
                        </select>
                    <?php elseif ( $custom_field->type == 'captcha' ) : ?>
                        <img class="ab-captcha-img" src="<?php echo esc_url( $captcha_url ) ?>" alt="<?php esc_attr_e( 'Captcha', 'bookly' ) ?>" height="75" width="160" style="width:160px;height:75px;" />
                        <img class="ab-captcha-refresh" width="16" height="16" title="<?php esc_attr_e( 'Another code', 'bookly' ) ?>" alt="<?php esc_attr_e( 'Another code', 'bookly' ) ?>" src="<?php echo plugins_url( 'frontend/resources/images/refresh.png', AB_PATH . '/main.php' ) ?>" style="cursor: pointer" />
                        <div class="ab-clear"></div>
                        <input type="text" class="ab-formElement ab-custom-field ab-captcha" value="<?php echo esc_attr( @$cf_data[ $custom_field->id ] ) ?>" />
                    <?php endif ?>
                </div>
                <?php if ( $custom_field->type != 'text-content' ) : ?>
                    <div class="ab-label-error ab-custom-field-error"></div>
                <?php endif ?>
            </div>
        </div>
    <?php endforeach ?>
</form>
<div class="ab-row-fluid ab-nav-steps ab-clear">
    <button class="ab-left ab-back-step ab-btn ladda-button" data-style="zoom-in" data-spinner-size="40">
        <span class="ladda-label"><?php echo \Bookly\Lib\Utils\Common::getTranslatedOption( 'ab_appearance_text_button_back' ) ?></span>
    </button>
    <button class="ab-right ab-next-step ab-btn ladda-button" data-style="zoom-in" data-spinner-size="40">
        <span class="ladda-label"><?php echo \Bookly\Lib\Utils\Common::getTranslatedOption( 'ab_appearance_text_button_next' ) ?></span>
    </button>
    <?php if ( $show_cart_btn ) : ?>
        <button class="ab-left ab-goto-cart ab-btn ladda-button" data-style="zoom-in" data-spinner-size="30">
            <span class="ladda-label"><img style="max-height: 16px" src="<?php echo plugins_url( 'appointment-booking/frontend/resources/images/cart.png' ) ?>" /></span>
        </button>
    <?php endif ?>
</div>