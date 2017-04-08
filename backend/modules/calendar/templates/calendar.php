<?php if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly
    $user_names = array();
    $user_ids   = array();
?>
<style>
    .fc-slats tr { height: <?php echo max( 21, intval( 620 / (1440 / get_option( 'ab_settings_time_slot_length' ) ) ) ) ?>px; }
</style>
<div class="panel panel-default">
    <div class="panel-heading">
        <h3 class="panel-title"><?php _e( 'Calendar', 'bookly' ) ?></h3>
    </div>
    <div class="ab-calendar-inner panel-body">
        <div ng-app=appointmentForm>
            <?php if ( $staff_members ) : ?>

                <div id="full_calendar_wrapper">
                    <div class="tabbable" style="margin-bottom: 15px;">
                        <ul class="nav nav-tabs" style="margin-bottom:0;border-bottom: 6px solid #1f6a8c">
                            <?php foreach ( $staff_members as $i => $staff ) : ?>
                                <li class="ab-calendar-tab" data-staff_id="<?php echo $staff->id ?>" style="display: none">
                                    <a href="#" data-toggle="tab"><?php echo $staff->full_name ?></a>
                                </li>
                            <?php endforeach ?>
                            <?php if ( \Bookly\Lib\Utils\Common::isCurrentUserAdmin() ) : ?>
                            <li class="ab-calendar-tab" data-staff_id="0">
                                <a href="#" data-toggle="tab"><?php _e( 'All', 'bookly' ) ?></a>
                            </li>
                            <li class="pull-right">
                                <div class="btn-group pull-right">
                                    <button class="btn btn-info ab-staff-filter-button" data-toggle="dropdown">
                                        <i class="glyphicon glyphicon-user"></i>
                                        <span id="ab-staff-button"></span>
                                    </button>
                                    <button class="btn btn-info dropdown-toggle ab-staff-filter-button" data-toggle="dropdown"><span class="caret"></span></button>
                                    <ul class="dropdown-menu pull-right">
                                        <li>
                                            <a href="javascript:void(0)">
                                                <input style="margin-right: 5px;" type="checkbox" id="ab-filter-all-staff" class="left">
                                                <label for="ab-filter-all-staff"><?php _e( 'All staff', 'bookly' ) ?></label>
                                            </a>
                                            <?php foreach ( $staff_members as $i => $staff ) : ?>
                                                <a style="padding-left: 35px;" href="javascript:void(0)">
                                                    <input style="margin-right: 5px;" type="checkbox" id="ab-filter-staff-<?php echo $staff->id ?>" value="<?php echo $staff->id ?>" data-staff_name="<?php echo esc_attr( $staff->full_name ) ?>" class="ab-staff-filter left" />
                                                    <label style="padding-right: 15px;" for="ab-filter-staff-<?php echo $staff->id ?>"><?php echo $staff->full_name ?></label>
                                                </a>
                                            <?php endforeach ?>
                                        </li>
                                    </ul>
                                </div>
                            </li>
                            <?php endif ?>
                        </ul>
                    </div>
                    <div class="table-responsive">
                        <div class="ab-loading-inner" style="display: none">
                            <span class="ab-loader"></span>
                        </div>
                        <div class="ab-calendar-element"></div>
                    </div>
                </div>
                <?php include '_appointment_form.php' ?>
            <?php else : ?>
            <div class="well">
                <h1><?php _e( 'Welcome to Bookly!',  'bookly' ) ?></h1>
                <h3><?php _e( 'Thank you for purchasing our product.', 'bookly' ) ?></h3>
                <h3><?php _e( 'Bookly offers a simple solution for making appointments. With our plugin you will be able to easily manage your availability time and handle the flow of your clients.', 'bookly' ) ?></h3>
                <p style="font-size: 14px"><?php _e( 'To start using Bookly, you need to follow these steps which are the minimum requirements to get it running!', 'bookly' ) ?></p>
                <ol>
                    <li><?php _e( 'Please add your staff members.', 'bookly' ) ?></li>
                    <li><?php _e( 'Add services and assign them to the staff members you created earlier.', 'bookly' ) ?></li>
                </ol>
                <hr>
                <a class="btn btn-info" href="<?php echo \Bookly\Lib\Utils\Common::escAdminUrl( Bookly\Backend\Modules\Services\Controller::page_slug ) ?>"><?php _e( 'Add Staff Members', 'bookly' ) ?></a>
                <a class="btn btn-info" href="<?php echo \Bookly\Lib\Utils\Common::escAdminUrl( Bookly\Backend\Modules\Services\Controller::page_slug ) ?>"><?php _e( 'Add Services', 'bookly' ) ?></a>
            <?php endif ?>
        </div>
    </div>
</div>