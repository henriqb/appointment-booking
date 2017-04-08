<?php if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly ?>
<div class="ab-row-fluid">
    <div class="ab-formGroup ab-left">
        <label class="ab-formLabel">
            <span data-default="<?php echo esc_attr( get_option( 'ab_appearance_text_label_ccard_number' ) ) ?>" class="ab_editable editable editable-click inline-block" id="ab-text-label-ccard-number" data-type="text"><?php echo esc_html( get_option( 'ab_appearance_text_label_ccard_number' ) ) ?></span>
        </label>
        <div class="ab-formField">
            <input class="ab-formElement ab-full-name" type="text" name="ab_card_number">
        </div>
    </div>
    <div class="ab-formGroup ab-left" style="width: auto;">
        <label class="ab-formLabel">
            <span data-default="<?php echo esc_attr( get_option( 'ab_appearance_text_label_ccard_expire' ) ) ?>" class="ab_editable editable editable-click inline-block" id="ab-text-label-ccard-expire" data-type="text"><?php echo esc_html( get_option( 'ab_appearance_text_label_ccard_expire' ) ) ?></span>
        </label>
        <div class="ab-formField">
            <select class="ab-formElement ab-full-name" style="width: 40px;float: left;" name="ab_card_month">
                <?php for ( $i = 1; $i <= 12; ++ $i ) : ?>
                    <option value="<?php echo $i ?>"><?php printf( '%02d', $i ) ?></option>
                <?php endfor ?>
            </select>
            <select class="ab-formElement ab-full-name" style="width: 55px;float: left; margin-left: 10px;" name="ab_card_year">
                <?php for ( $i = date( 'Y' ); $i <= date( 'Y' ) + 10; ++ $i ) : ?>
                    <option value="<?php echo $i ?>"><?php echo $i ?></option>
                <?php endfor ?>
            </select>
        </div>
    </div>
</div>
<div class="ab-row-fluid">
    <div class="ab-formGroup ab-left">
        <label class="ab-formLabel">
            <span data-default="<?php echo esc_attr( get_option( 'ab_appearance_text_label_ccard_code' ) ) ?>" class="ab_editable editable editable-click inline-block" id="ab-text-label-ccard-code" data-type="text"><?php echo esc_html( get_option( 'ab_appearance_text_label_ccard_code' ) ) ?></span>
        </label>
        <div class="ab-formField">
            <input class="ab-formElement ab-full-name" style="width: 50px;float: left;" type="text" name="ab_card_code" />
        </div>
    </div>
    <div class="ab-clear"></div>
    <div class="ab-error ab-bold ab-card-error"></div>
</div>