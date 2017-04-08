<?php if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly
    $start_of_week = (int) get_option( 'start_of_week' );
    $form = new \Bookly\Backend\Modules\Settings\Forms\BusinessHours()
?>
<form method="post" action="<?php echo esc_url( add_query_arg( 'tab', 'business_hours' ) ) ?>" class="ab-settings-form" id="business-hours">
    <?php for ( $i = 0; $i < 7; $i ++ ) :
        $day = strtolower( \Bookly\Lib\Utils\DateTime::getWeekDayByNumber( ( $i + $start_of_week ) % 7 ) );
        ?>
        <div class="form-group">
            <label><?php _e( ucfirst( $day ) ) ?> </label><br/>
            <?php echo $form->renderField( 'ab_settings_' . $day ) ?>
            <span>&nbsp;<?php _e( 'to', 'bookly' ) ?>&nbsp;</span>
            <?php echo $form->renderField( 'ab_settings_' . $day, false ) ?>
        </div>
    <?php endfor ?>
    <?php \Bookly\Lib\Utils\Common::submitButton() ?>
    <?php \Bookly\Lib\Utils\Common::resetButton( 'ab-hours-reset' ) ?>
</form>