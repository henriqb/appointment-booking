<?php if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly
    /** @var \Bookly\Lib\Entities\Staff $staff */
?>
<div id="ab-edit-staff">
    <?php \Bookly\Lib\Utils\Common::notice( __( 'Settings saved.', 'bookly' ), 'notice-success', isset ( $updated ) ) ?>
    <?php \Bookly\Lib\Utils\Common::notice( $errors, 'notice-error' ) ?>

    <div class="ab-nav-head" style="">
        <h2 class="pull-left"><?php echo $staff->get( 'full_name' ) ?></h2>
        <?php if ( \Bookly\Lib\Utils\Common::isCurrentUserAdmin() ) : ?>
        <a class="btn btn-info" id="ab-staff-delete"><?php _e( 'Delete this staff member', 'bookly' ) ?></a>
        <?php endif ?>
    </div>
    <div class="tabbable">
        <ul class="nav nav-tabs ab-nav-tabs">
            <li class="active"><a id="ab-staff-details-tab" href="#tab1" data-toggle="tab"><?php _e( 'Details', 'bookly' ) ?></a></li>
            <li><a id="ab-staff-services-tab" href="#services" data-toggle="tab"><?php _e( 'Services', 'bookly' ) ?></a></li>
            <li><a id="ab-staff-schedule-tab" href="#schedule" data-toggle="tab"><?php _e( 'Schedule', 'bookly' ) ?></a></li>
            <li><a id="ab-staff-holidays-tab" href="#dayoff" data-toggle="tab"><?php _e( 'Days off', 'bookly' ) ?></a></li>
        </ul>
        <div class="tab-content">
            <div style="display: none;" class="loading-indicator">
                <span class="ab-loader"></span>
            </div>
            <div class="tab-pane active" id="tab1">
                <div id="ab-staff-details-container" class="ab-staff-tab-content">
                    <form class="ab-staff-form form-horizontal" action="" name="ab_staff" method="POST" enctype="multipart/form-data">
                        <?php if ( \Bookly\Lib\Utils\Common::isCurrentUserAdmin() ) : ?>
                            <div class="form-group">
                                <div class="col-sm-11 col-xs-10">
                                    <label for="ab-staff-wpuser"><?php _e( 'User', 'bookly' ) ?></label>
                                    <select class="form-control" name="wp_user_id" id="ab-staff-wpuser">
                                        <option value=""><?php _e( 'Select from WP users', 'bookly' ) ?></option>
                                        <?php foreach ( $users_for_staff as $user ) : ?>
                                            <option value="<?php echo $user->ID ?>" data-email="<?php echo $user->user_email ?>" <?php selected( $user->ID, $staff->get( 'wp_user_id' ) ) ?>><?php echo $user->display_name ?></option>
                                        <?php endforeach ?>
                                    </select>
                                </div>
                                <div class="col-sm-1 col-xs-2">
                                    <img
                                        src="<?php echo plugins_url( 'backend/resources/images/help.png', AB_PATH . '/main.php' ) ?>"
                                        alt=""
                                        style="margin: 28px 0 0 -20px;"
                                        class="ab-popover-ext"
                                        data-ext_id="ab-staff-popover-ext"
                                        />
                                </div>
                            </div>
                        <?php endif ?>
                        <div class="form-group">
                            <div class="col-xs-11">
                                <label class="control-label" for="ab-staff-avatar"><?php _e( 'Photo', 'bookly' ) ?></label>
                                <div id="ab-staff-avatar-image">
                                    <?php if ( $staff->get( 'avatar_url' ) ) : ?>
                                        <img src="<?php echo $staff->get( 'avatar_url' ) ?>" alt="<?php _e( 'Avatar', 'bookly' ) ?>"/>
                                        <a id="ab-delete-avatar" href="javascript:void(0)"><?php _e( 'Delete current photo', 'bookly' ) ?></a>
                                    <?php endif ?>
                                </div>
                                <input id="ab-staff-avatar" name="avatar" type="file"/>
                            </div>
                        </div>
                        <div class="form-group">
                            <div class="col-sm-11 col-xs-10">
                                <label for="ab-staff-full-name"><?php _e( 'Full name', 'bookly' ) ?></label>
                                <input class="form-control" id="ab-staff-full-name" name="full_name" value="<?php echo esc_attr( $staff->get( 'full_name' ) ) ?>" type="text"/>
                            </div>
                            <div class="col-sm-1 col-xs-2">
                                <span style="position: relative;top: 28px;left: -20px;" class="ab-red"> *</span>
                            </div>
                        </div>
                        <div class="form-group">
                            <div class="col-xs-11">
                                <label for="ab-staff-email"><?php _e( 'Email', 'bookly' ) ?></label>
                                <input class="form-control" id="ab-staff-email" name="email" value="<?php echo esc_attr( $staff->get( 'email' ) ) ?>" type="text"/>
                            </div>
                        </div>
                        <div class="form-group">
                            <div class="col-xs-11">
                                <label for="ab-staff-phone"><?php _e( 'Phone', 'bookly' ) ?></label>
                                <div class="ab-clear"></div>
                                <input class="form-control" id="ab-staff-phone" name="phone" value="<?php echo esc_attr( $staff->get( 'phone' ) ) ?>" type="text" />
                            </div>
                        </div>
                        <div class="form-group">
                            <div class="col-xs-11">
                                <label for="ab-staff-info"><?php _e( 'Info', 'bookly' ) ?></label>
                                <div class="ab-clear"></div>
                                <textarea class="form-control" id="ab-staff-info" name="info" rows="3" type="text"><?php echo esc_textarea( $staff->get( 'info' ) ) ?></textarea>
                            </div>
                        </div>
                        <div class="form-group">
                            <div class="col-xs-11">
                                <h4 class="pull-left"><?php _e( 'Google Calendar integration', 'bookly' ) ?></h4>
                                <div style="margin: 5px;display: inline-block;">
                                    <?php \Bookly\Lib\Utils\Common::popover( __( 'Synchronize the data of the staff member bookings with Google Calendar.', 'bookly' ) ) ?>
                                </div>
                            </div>
                        </div>
                        <div class="form-group">
                            <div class="col-xs-11">
                                <label>
                                    <?php if ( isset( $authUrl ) ) : ?>
                                        <?php if ( $authUrl ) : ?>
                                            <a href="<?php echo $authUrl ?>"><?php _e( 'Connect', 'bookly' ) ?></a>
                                        <?php else : ?>
                                            <?php printf( __( 'Please configure Google Calendar <a href="%s">settings</a> first', 'bookly' ), \Bookly\Lib\Utils\Common::escAdminUrl( \Bookly\Backend\Modules\Settings\Controller::page_slug, array( 'tab' => 'google_calendar' ) ) ) ?>
                                        <?php endif ?>
                                    <?php else : ?>
                                        <?php _e( 'Connected', 'bookly' ) ?> (<a href="<?php echo \Bookly\Lib\Utils\Common::escAdminUrl( \Bookly\Backend\Modules\Staff\Controller::page_slug, array( 'google_logout' => $staff->get( 'id' ) ) ) ?>" ><?php _e( 'disconnect', 'bookly' ) ?></a>)
                                    <?php endif ?>
                                </label>
                            </div>
                        </div>
                        <?php if ( ! isset( $authUrl ) ) : ?>
                            <div class="form-group">
                                <div class="col-sm-11 col-xs-10">
                                    <label for="ab-calendar-id"><?php _e( 'Calendar', 'bookly' ) ?></label>
                                    <select class="form-control" name="google_calendar_id" id="ab-calendar-id">
                                        <?php foreach ( $calendar_list as $id => $calendar ) : ?>
                                            <option <?php selected( $staff->get( 'google_calendar_id' ) == $id || $staff->get( 'google_calendar_id' ) == '' && $calendar['primary'] ) ?> value="<?php echo esc_attr( $id ) ?>"><?php echo esc_html( $calendar['summary'] ) ?></option>
                                        <?php endforeach ?>
                                    </select>
                                </div>
                            </div>
                        <?php endif ?>
                        <input type="hidden" name="id" value="<?php echo $staff->get( 'id' ) ?>"/>
                        <input type="hidden" name="staff" value="update"/>
                        <div class="form-group">
                            <div class="col-xs-11">
                                <?php \Bookly\Lib\Utils\Common::submitButton() ?>
                                <?php \Bookly\Lib\Utils\Common::resetButton() ?>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
            <div class="tab-pane" id="services">
                <div id="ab-staff-services-container" class="ab-staff-tab-content" style="display: none"></div>
            </div>
            <div class="tab-pane" id="schedule">
                <div id="ab-staff-schedule-container" class="ab-staff-tab-content" style="display: none"></div>
            </div>
            <div class="tab-pane" id="dayoff">
                <div id="ab-staff-holidays-container" class="ab-staff-tab-content" style="display: none"></div>
            </div>
        </div>
    </div>
</div>
