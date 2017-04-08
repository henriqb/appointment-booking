<?php if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly
    $color = get_option( 'ab_appearance_color', '#f4662f' );
    $checkbox_img = plugins_url( 'frontend/resources/images/checkbox.png', AB_PATH . '/main.php' );
?>
<style type="text/css">
    /* Service */
    .ab-label-error, li.ab-step-tabs.active a, .ab-cart .ab-error {color: <?php echo $color ?>!important;}
    .ab-back-step, .ab-next-step, .ab-mobile-next-step, .ab-mobile-prev-step, li.ab-step-tabs.active div,.ab-booking-form .picker__frame, .ab-service-step .ab-week-days li label {background: <?php echo $color ?>!important;}
    .ab-booking-form .picker__header {border-bottom: 1px solid <?php echo $color ?>!important;}
    .ab-booking-form .picker__nav--next:before {border-left:  6px solid <?php echo $color ?>!important;}
    .ab-booking-form .picker__nav--prev:before {border-right: 6px solid <?php echo $color ?>!important;}
    .ab-booking-form .picker__nav--next, .ab-booking-form .pickadate__nav--prev, .ab-booking-form .picker__day:hover, .ab-booking-form .picker__day--selected:hover, .ab-booking-form .picker--opened .picker__day--selected, .ab-booking-form .picker__button--clear, .ab-booking-form .picker__button--today {color: <?php echo $color ?>!important;}
    .ab-service-step .ab-week-days li label.active {background: <?php echo $color ?> url(<?php echo $checkbox_img ?>) 0 0 no-repeat!important;}
    /* Time */
    .ab-columnizer .ab-available-day { background: <?php echo $color ?>!important; border: 1px solid <?php echo $color ?>!important; }
    .ab-columnizer .ab-available-hour:hover { border: 2px solid <?php echo $color ?>!important; color: <?php echo $color ?>!important; }
    .ab-columnizer .ab-available-hour:hover .ab-hour-icon { background: none; border: 2px solid <?php echo $color ?>!important; color: <?php echo $color ?>!important; }
    .ab-columnizer .ab-available-hour:hover .ab-hour-icon span, .ab-time-next, .ab-time-prev, .bookly-btn-submit,
    /* Cart */
    .ab-add-item, .ab-goto-cart,
    /* Payment */
    .btn-apply-coupon {background: <?php echo $color ?>!important;}
    .ab-cart .ab--actions { background-color: <?php echo $color ?>!important;}
    label.ab-formLabel, div.ab-error {color: <?php echo $color ?>!important;}
    input.ab-field-error, textarea.ab-field-error, div.ab-error select, .ab-extra-step input:checked + div.ab-thumb {border: 2px solid <?php echo $color ?>!important;}
</style>