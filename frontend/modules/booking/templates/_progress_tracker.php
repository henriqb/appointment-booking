<?php if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly
    $i = 1;
?>
<div class="ab-progress-tracker">
    <ul class=ab-progress-bar>
        <?php if ( $skip_service_step == false ) : ?>
        <li class="ab-step-tabs<?php if ( $step >= 1 ) : ?> active<?php endif ?>">
            <a href="javascript:void(0)"><?php echo $i ++ . '. ' . \Bookly\Lib\Utils\Common::getTranslatedOption( 'ab_appearance_text_step_service' ) ?></a>
            <div class=step></div>
        </li>
        <?php endif ?>
        <?php if ( \Bookly\Lib\Config::showStepExtras() ) : ?>
        <li class="ab-step-tabs<?php if ( $step >= 2 ) : ?> active<?php endif ?>">
            <a href="javascript:void(0)"><?php echo $i ++ . '. ' . \Bookly\Lib\Utils\Common::getTranslatedOption( 'ab_appearance_text_step_extras' ) ?></a>
            <div class=step></div>
        </li>
        <?php endif ?>
        <li class="ab-step-tabs<?php if ( $step >= 3 ) : ?> active<?php endif ?>">
            <a href="javascript:void(0)"><?php echo $i ++ . '. ' . \Bookly\Lib\Utils\Common::getTranslatedOption( 'ab_appearance_text_step_time' ) ?></a>
            <div class=step></div>
        </li>
        <?php if ( $show_cart && get_option( 'ab_custom_fields_per_service' ) != 1 ) : ?>
            <li class="ab-step-tabs<?php if ( $step >= 4 ) : ?> active<?php endif ?>">
                <a href="javascript:void(0)"><?php echo $i ++ . '. ' . \Bookly\Lib\Utils\Common::getTranslatedOption( 'ab_appearance_text_step_cart' ) ?></a>
                <div class=step></div>
            </li>
        <?php endif ?>
        <li class="ab-step-tabs<?php if ( $step >= ( 5 - intval( get_option( 'ab_custom_fields_per_service' ) ) ) ) : ?> active<?php endif ?>">
            <a href="javascript:void(0)"><?php echo $i ++ . '. ' . \Bookly\Lib\Utils\Common::getTranslatedOption( 'ab_appearance_text_step_details' ) ?></a>
            <div class=step></div>
        </li>
        <?php if ( $show_cart && get_option( 'ab_custom_fields_per_service' ) == 1 ) : ?>
            <li class="ab-step-tabs<?php if ( $step == 4 || $step > 5 ) : ?> active<?php endif ?>">
                <a href="javascript:void(0)"><?php echo $i ++ . '. ' . \Bookly\Lib\Utils\Common::getTranslatedOption( 'ab_appearance_text_step_cart' ) ?></a>
                <div class=step></div>
            </li>
        <?php endif ?>
        <?php if ( $payment_disabled == false ) : ?>
            <li class="ab-step-tabs<?php if ( $step >= 6 ) : ?> active<?php endif ?>">
                <a href="javascript:void(0)"><?php echo $i ++ . '. ' . \Bookly\Lib\Utils\Common::getTranslatedOption( 'ab_appearance_text_step_payment' ) ?></a>
                <div class=step></div>
            </li>
        <?php endif ?>
        <li class="ab-step-tabs<?php if ( $step >= 7 ) : ?> active<?php endif ?>">
            <a href="javascript:void(0)"><?php echo $i ++ . '. ' . \Bookly\Lib\Utils\Common::getTranslatedOption( 'ab_appearance_text_step_done' ) ?></a>
            <div class=step></div>
        </li>
    </ul>
</div>