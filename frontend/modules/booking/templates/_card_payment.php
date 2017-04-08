<?php if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly ?>
<div class="ab-row-fluid">
    <div class="ab-formGroup ab-left">
        <label class="ab-formLabel"><?php echo \Bookly\Lib\Utils\Common::getTranslatedOption( 'ab_appearance_text_label_ccard_number' ) ?></label>
        <div class="ab-formField">
            <input class="ab-formElement" type="text" name="ab_card_number" autocomplete="off" />
        </div>
    </div>
    <div class="ab-formGroup ab-left" style="width: auto;">
        <label class="ab-formLabel"><?php echo \Bookly\Lib\Utils\Common::getTranslatedOption( 'ab_appearance_text_label_ccard_expire' ) ?></label>
        <div class="ab-formField">
            <select class="ab-formElement" style="width: 45px;float: left;" name="ab_card_exp_month">
                <?php for ( $i = 1; $i <= 12; ++ $i ) : ?>
                    <option value="<?php echo $i ?>"><?php printf( '%02d', $i ) ?></option>
                <?php endfor ?>
            </select>
            <select class="ab-formElement" style="width: 70px;float: left; margin-left: 10px;" name="ab_card_exp_year">
                <?php for ( $i = date( 'Y' ); $i <= date( 'Y' ) + 10; ++ $i ) : ?>
                    <option value="<?php echo $i ?>"><?php echo $i ?></option>
                <?php endfor ?>
            </select>
        </div>
    </div>
</div>
<div class="ab-row-fluid">
    <div class="ab-formGroup ab-left">
        <label class="ab-formLabel"><?php echo \Bookly\Lib\Utils\Common::getTranslatedOption( 'ab_appearance_text_label_ccard_code' ) ?></label>
        <div class="ab-formField">
            <input class="ab-formElement" style="width: 50px;" type="text" name="ab_card_cvc" autocomplete="off" />
        </div>
    </div>
</div><div class="ab-clear"></div>
<div class="ab-error ab-bold ab-card-error"></div>