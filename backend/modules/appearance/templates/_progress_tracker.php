<?php if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly
    $i = 1;
?>
<div class="ab-progress-tracker">
    <ul class="ab-progress-bar">
        <li class="ab-step-tabs ab-first active">
            <a href="javascript:void(0)"><?php echo $i ++ ?>. <span data-default="<?php echo esc_attr( get_option( 'ab_appearance_text_step_service' ) ) ?>" data-mirror="text_service" class="text_service ab_editable" id="ab-text-step-service" data-type="text"><?php echo esc_html( get_option( 'ab_appearance_text_step_service' ) ) ?></span></a>
            <div class="step"></div>
        </li>
        <?php if ( \Bookly\Lib\Utils\Common::isActivePlugin( 'bookly-addon-service-extras/main.php' ) ) : ?>
        <li class="ab-step-tabs<?php if ( ( $step >= 2 ) ) : ?> active<?php endif ?>">
            <a href="javascript:void(0)"><?php echo $i ++ ?>. <span data-default="<?php echo esc_attr( get_option( 'ab_appearance_text_step_extras' ) ) ?>" data-mirror="text_extras" class="text_extras ab_editable" id="ab-text-step-extras" data-type="text"><?php echo esc_html(get_option( 'ab_appearance_text_step_extras' ) ) ?></span></a>
            <div class="step"></div>
        </li>
        <?php endif ?>
        <li class="ab-step-tabs<?php if ( $step >= 3 ) : ?> active<?php endif ?>">
            <a href="javascript:void(0)"><?php echo $i ++ ?>. <span data-default="<?php echo esc_attr( get_option( 'ab_appearance_text_step_time' ) ) ?>" data-mirror="text_time" class="text_time ab_editable" id="ab-text-step-time" data-type="text"><?php echo esc_html(get_option( 'ab_appearance_text_step_time' ) ) ?></span></a>
            <div class="step"></div>
        </li>
        <li class="ab-step-tabs<?php if ( $step >= 4 ) : ?> active<?php endif ?>">
            <a href="javascript:void(0)"><?php echo $i ++ ?>. <span data-default="<?php echo esc_attr( get_option( 'ab_appearance_text_step_cart' ) ) ?>" data-mirror="text_cart" class="text_cart ab_editable" id="ab-text-step-cart" data-type="text"><?php echo esc_html( get_option( 'ab_appearance_text_step_cart' ) ) ?></span></a>
            <div class="step"></div>
        </li>
        <li class="ab-step-tabs<?php if ( $step >= 5 ) : ?> active<?php endif ?>">
            <a href="javascript:void(0)"><?php echo $i ++ ?>. <span data-default="<?php echo esc_attr( get_option( 'ab_appearance_text_step_details' ) ) ?>" data-mirror="text_details" class="text_details ab_editable" id="ab-text-step-details" data-type="text"><?php echo esc_html( get_option( 'ab_appearance_text_step_details' ) ) ?></span></a>
            <div class="step"></div>
        </li>
        <li class="ab-step-tabs<?php if ( $step >= 6 ) : ?> active<?php endif ?>">
            <a href="javascript:void(0)"><?php echo $i ++ ?>. <span data-default="<?php echo esc_attr( get_option( 'ab_appearance_text_step_payment' ) ) ?>" data-mirror="text_payment" class="text_payment ab_editable" id="ab-text-step-payment" data-type="text"><?php echo esc_html( get_option( 'ab_appearance_text_step_payment' ) ) ?></span></a>
            <div class="step"></div>
        </li>
        <li class="ab-step-tabs ab-last<?php if ( $step >= 7 ) : ?> active<?php endif ?>">
            <a href="javascript:void(0)"><?php echo $i ++ ?>. <span data-default="<?php echo esc_attr( get_option( 'ab_appearance_text_step_done' ) ) ?>" data-mirror="text_done" class="text_done ab_editable" id="ab-text-step-done" data-type="text"><?php echo esc_html( get_option( 'ab_appearance_text_step_done' ) ) ?></span></a>
            <div class="step"></div>
        </li>
    </ul>
</div>