<?php if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly ?>
<form method="post" action="<?php echo esc_url( add_query_arg( 'tab', 'general' ) ) ?>" enctype="multipart/form-data" class="ab-settings-form">
    <div class="form-group">
        <label for="ab_settings_time_slot_length"><?php _e( 'Time slot length', 'bookly' ) ?></label>
        <p class="help-block"><?php _e( 'Select the time interval that will be used in frontend and backend, e.g. in calendar, second step of the booking process, while indicating the working hours, etc.', 'bookly' ) ?></p>
        <select class="form-control" name="ab_settings_time_slot_length" id="ab_settings_time_slot_length">
            <?php foreach ( array( 5, 10, 12, 15, 20, 30, 45, 60, 90, 120, 180, 240, 360 ) as $duration ) :
                $duration_output = \Bookly\Lib\Utils\DateTime::secondsToInterval( $duration * 60 ); ?>
                <option value="<?php echo $duration ?>" <?php selected( get_option( 'ab_settings_time_slot_length' ), $duration ) ?>>
                    <?php echo $duration_output ?>
                </option>
            <?php endforeach ?>
        </select>
    </div>
    <div class="form-group">
        <label for="ab_settings_minimum_time_prior_booking"><?php _e( 'Minimum time requirement prior to booking', 'bookly' ) ?></label>
        <p class="help-block"><?php _e( 'Set how late appointments can be booked (for example, require customers to book at least 1 hour before the appointment time).', 'bookly' ) ?></p>
        <select class="form-control" name="ab_settings_minimum_time_prior_booking" id="ab_settings_minimum_time_prior_booking">
            <option value="0"><?php _e( 'Disabled', 'bookly' ) ?></option>
            <?php foreach ( array_merge( range( 1, 12 ), range( 24, 144, 24 ), range( 168, 672, 168 ) ) as $hour ) : ?>
                <option value="<?php echo $hour ?>" <?php selected( get_option( 'ab_settings_minimum_time_prior_booking' ), $hour ) ?>><?php echo \Bookly\Lib\Utils\DateTime::secondsToInterval( $hour * HOUR_IN_SECONDS ) ?></option>
            <?php endforeach ?>
        </select>
    </div>
    <div class="form-group">
        <label for="ab_settings_minimum_time_prior_cancel"><?php _e( 'Minimum time requirement prior to canceling', 'bookly' ) ?></label>
        <p class="help-block"><?php _e( 'Set how late appointments can be cancelled (for example, require customers to cancel at least 1 hour before the appointment time).', 'bookly' ) ?></p>
        <select class="form-control" name="ab_settings_minimum_time_prior_cancel" id="ab_settings_minimum_time_prior_cancel">
            <option value="0"><?php _e( 'Disabled', 'bookly' ) ?></option>
            <?php foreach ( array_merge( array( 1 ),  range( 2, 12, 2 ), range( 24, 168, 24 ) ) as $hour ) : ?>
                <option value="<?php echo $hour ?>" <?php selected( get_option( 'ab_settings_minimum_time_prior_cancel' ), $hour ) ?>><?php echo \Bookly\Lib\Utils\DateTime::secondsToInterval( $hour * HOUR_IN_SECONDS ) ?></option>
            <?php endforeach ?>
        </select>
    </div>
    <div class="form-group">
        <label for="ab_settings_cancel_page_url"><?php _e( 'Cancel appointment URL (success)', 'bookly' ) ?></label>
        <p class="help-block"><?php _e( 'Set the URL of a page that is shown to clients after they successfully cancelled their appointment.', 'bookly' ) ?></p>
        <input class="form-control" type="text" name="ab_settings_cancel_page_url" id="ab_settings_cancel_page_url" value="<?php echo esc_attr( get_option( 'ab_settings_cancel_page_url' ) ) ?>" placeholder="<?php esc_attr_e( 'Enter a URL', 'bookly' ) ?>" />
    </div>
    <div class="form-group">
        <label for="ab_settings_cancel_denied_page_url"><?php _e( 'Cancel appointment URL (denied)', 'bookly' ) ?></label>
        <p class="help-block"><?php _e( 'Set the URL of a page that is shown to clients when the cancellation of appointment is not available anymore.', 'bookly' ) ?></p>
        <input class="form-control" type="text" id="ab_settings_cancel_denied_page_url" name="ab_settings_cancel_denied_page_url" value="<?php echo esc_attr( get_option( 'ab_settings_cancel_denied_page_url' ) ) ?>" placeholder="<?php esc_attr_e( 'Enter a URL', 'bookly' ) ?>" />
    </div>
    <div class="form-group">
        <label for="ab_settings_maximum_available_days_for_booking"><?php _e( 'Number of days available for booking', 'bookly' ) ?></label>
        <p class="help-block"><?php _e( 'Set how far in the future the clients can book appointments.', 'bookly' ) ?></p>
        <input class="form-control" type="number" id="ab_settings_maximum_available_days_for_booking" name="ab_settings_maximum_available_days_for_booking" min="1" max="365" value="<?php echo esc_attr( get_option( 'ab_settings_maximum_available_days_for_booking', 365 ) ) ?>" />
    </div>
    <div class="form-group">
        <label for="ab_settings_use_client_time_zone"><?php _e( 'Display available time slots in client\'s time zone', 'bookly' ) ?></label>
        <p class="help-block"><?php _e( 'The value is taken from client’s browser.', 'bookly' ) ?></p>
        <?php \Bookly\Lib\Utils\Common::optionToggle( 'ab_settings_use_client_time_zone' ) ?>
    </div>
    <div class="form-group">
        <label for="ab_settings_final_step_url_mode"><?php _e( 'Final step URL', 'bookly' ) ?></label>
        <p class="help-block"><?php _e( 'Set the URL of a page that the user will be forwarded to after successful booking. If disabled then the default Done step is displayed.', 'bookly' ) ?></p>
        <select class="form-control" id="ab_settings_final_step_url_mode">
            <?php foreach ( array( __( 'Disabled', 'bookly' ) => 0, __( 'Enabled', 'bookly' ) => 1 ) as $text => $mode ) : ?>
                <option value="<?php echo esc_attr( $mode ) ?>" <?php selected( get_option( 'ab_settings_final_step_url' ), $mode ) ?> ><?php echo $text ?></option>
            <?php endforeach ?>
        </select>
        <input class="form-control" style="margin-top: 5px; <?php echo get_option( 'ab_settings_final_step_url' ) == '' ? 'display: none':''; ?>" type="text" name="ab_settings_final_step_url" value="<?php echo esc_attr( get_option( 'ab_settings_final_step_url' ) ) ?>" placeholder="<?php esc_attr_e( 'Enter a URL', 'bookly' ) ?>" />
    </div>
    <div class="form-group">
        <label for="ab_settings_allow_staff_members_edit_profile"><?php _e( 'Allow staff members to edit their profiles', 'bookly' ) ?></label>
        <p class="help-block"><?php _e( 'If this option is enabled then all staff members who are associated with WordPress users will be able to edit their own profiles, services, schedule and days off.', 'bookly' ) ?></p>
        <?php \Bookly\Lib\Utils\Common::optionToggle( 'ab_settings_allow_staff_members_edit_profile' ) ?>
    </div>
    <div class="form-group">
        <label for="ab_settings_link_assets_method"><?php _e( 'Method to include Bookly JavaScript and CSS files on the page', 'bookly' ) ?></label>
        <p class="help-block"><?php _e( 'With "Enqueue" method the JavaScript and CSS files of Bookly will be included on all pages of your website. This method should work with all themes. With "Print" method the files will be included only on the pages which contain Bookly booking form. This method may not work with all themes.', 'bookly' ) ?></p>
        <select class="form-control" name="ab_settings_link_assets_method" id="ab_settings_link_assets_method">
            <option value="enqueue" <?php selected( get_option( 'ab_settings_link_assets_method' ), 'enqueue' ) ?>>Enqueue</option>
            <option value="print" <?php selected( get_option( 'ab_settings_link_assets_method' ), 'print' ) ?>>Print</option>
        </select>
    </div>
    <?php \Bookly\Lib\Utils\Common::submitButton() ?>
    <?php \Bookly\Lib\Utils\Common::resetButton() ?>
</form>