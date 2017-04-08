<?php if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly ?>
<div class="panel panel-default">
    <div class="panel-heading">
        <h3 class="panel-title"><?php _e( 'Appearance', 'bookly' ) ?></h3>
    </div>
    <div class="panel-body">
        <?php \Bookly\Lib\Utils\Common::notice( __( 'Settings saved.', 'bookly' ), 'notice-success', false ) ?>
        <input type=text class="wp-color-picker appearance-color-picker" name=color
               value="<?php echo esc_attr( get_option( 'ab_appearance_color' ) ) ?>"
               data-selected="<?php echo esc_attr( get_option( 'ab_appearance_color' ) ) ?>" />
        <div id="ab-appearance">
            <form method=post id=common_settings>
                <div class="row">
                    <div class="col-md-3">
                        <div id=main_form class="checkbox">
                            <label>
                                <input id=ab-progress-tracker-checkbox name=ab-progress-tracker-checkbox <?php checked( get_option( 'ab_appearance_show_progress_tracker' ) ) ?> type=checkbox />
                                <b><?php _e( 'Show form progress tracker', 'bookly' ) ?></b>
                            </label>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="checkbox">
                            <label>
                                <input id="ab-show-calendar-checkbox" name="ab-show-calendar-checkbox" <?php checked ( get_option( 'ab_appearance_show_calendar' ) ) ?> type="checkbox" />
                                <b><?php _e( 'Show calendar', 'bookly' ) ?></b>
                            </label>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="checkbox">
                            <label>
                                <input id="ab-blocked-timeslots-checkbox" name="ab-blocked-timeslots-checkbox" <?php checked( get_option( 'ab_appearance_show_blocked_timeslots' ) ) ?> type="checkbox" />
                                <b><?php _e( 'Show blocked timeslots', 'bookly' ) ?></b>
                            </label>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="checkbox">
                            <label>
                                <input id="ab-day-one-column-checkbox" name="ab-day-one-column-checkbox" <?php checked( get_option( 'ab_appearance_show_day_one_column' ) ) ?> type="checkbox" />
                                <b><?php _e( 'Show each day in one column', 'bookly' ) ?></b>
                            </label>
                        </div>
                    </div>
                </div>
            </form>
            <!-- Tabs -->
            <div class=tabbable style="margin-top: 20px;">
                <ul class="nav nav-tabs ab-nav-tabs">
                    <?php $i = 1; ?>
                    <?php foreach ( $steps as $step => $step_name ) : ?>
                        <?php if ( $step != 2 || \Bookly\Lib\Utils\Common::isActivePlugin( 'bookly-addon-service-extras/main.php' ) ) : ?>
                        <li class="ab-step-tab-<?php echo $step ?> ab-step-tabs<?php if ( $step == 1 ) : ?> active<?php endif ?>" data-step-id="<?php echo $step ?>">
                            <a href="#" data-toggle=tab><?php echo $i++ ?>. <span class="text_step_<?php echo $step ?>" ><?php echo esc_html( $step_name ) ?></span></a>
                        </li>
                        <?php endif ?>
                    <?php endforeach ?>
                </ul>
                <!-- Tabs-Content -->
                <div class=tab-content>
                    <?php foreach ( $steps as $step => $step_name ) : ?>
                        <div class="tab-pane-<?php echo $step ?><?php if ( $step == 1 ) : ?> active<?php endif ?>" data-step-id="<?php echo $step ?>"<?php if ( $step != 1 ) : ?> style="display: none"<?php endif ?>>
                            <?php // Render unique data per step
                            switch ( $step ) :
                                case 1: include '_1_service.php'; break;
                                case 2: do_action( 'bookly_backend_appearance', $this->render( '_progress_tracker', array( 'step' => $step ), false ) );
                                    break;
                                case 3: include '_3_time.php';    break;
                                case 4: include '_4_cart.php';    break;
                                case 5: include '_5_details.php'; break;
                                case 6: include '_6_payment.php'; break;
                                case 7: include '_7_done.php';    break;
                            endswitch ?>
                        </div>
                    <?php endforeach ?>
                </div>
                <div class="text-right">
                    <?php _e( 'Click on the underlined text to edit.', 'bookly' ) ?>
                </div>
                <div class="clear"></div>
            </div>
        </div>
    </div>
    <div class="panel-footer">
        <?php \Bookly\Lib\Utils\Common::submitButton( 'ajax-send-appearance' ) ?>
        <?php \Bookly\Lib\Utils\Common::resetButton() ?>
    </div>
</div>