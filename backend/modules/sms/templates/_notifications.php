<?php if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly
    /** @var Bookly\Backend\Modules\Notifications\Forms\Notifications $form */
    $administrator_phone = get_option( 'ab_sms_administrator_phone' );
    $notif_id = 0;
?>
<form action="<?php echo esc_url( remove_query_arg( array( 'paypal_result', 'auto-recharge', 'tab' ) ) ) ?>" method="post">
    <input type="hidden" name="form-notifications">
    <div class="ab-notifications form-inline">
        <table>
            <tr>
                <td>
                    <label for="ab_sms_administrator_phone" style="display: inline"><?php _e( 'Administrator phone', 'bookly' ) ?></label>
                </td>
                <td>
                    <div class="input-group">
                        <input id="ab_sms_administrator_phone" name="ab_sms_administrator_phone" class="ab-auto-w ab-sender" type="text" value="<?php echo esc_attr( $administrator_phone ) ?>"/>
                        <span class="input-group-btn">
                            <button class="btn btn-info" id="send_test_sms"><?php _e( 'Send test SMS', 'bookly' ) ?></button>
                        </span>
                    </div>
                    <?php \Bookly\Lib\Utils\Common::popover( __( 'Enter a phone number in international format. E.g. for the United States a valid phone number would be +17327572923.', 'bookly' ) ) ?>
                </td>
            </tr>
        </table>
    </div>
    <div class="panel-group" id="accordion" role="tablist" aria-multiselectable="true">
    <?php foreach ( $form->types as $type ) : ?>
        <?php $notif_id += 1;
              $form_data = $form->getData();
              $active    = isset( $form_data[ $type ]['active'] ) ? $form_data[ $type ]['active'] : false;
        ?>
        <div class="panel panel-default ab-notifications">
            <div class="panel-heading" role="tab" id="headingOne">
                <h4 class="panel-title">
                    <input name="<?php echo $type ?>[active]" value="0" type="checkbox" checked="checked" class="hidden" />
                    <input id="<?php echo $type ?>_active" name="<?php echo $type ?>[active]" value="1" type="checkbox" <?php checked( $active ) ?> />
                    <a class="collapsed" role="button" data-toggle="collapse" data-parent="#accordion" href="#collapse_<?php echo $notif_id ?>">
                        <?php echo $form->renderActive( $type ) ?>
                    </a>
                </h4>
            </div>
            <div id="collapse_<?php echo $notif_id ?>" class="panel-collapse collapse">
                <div class="panel-body">
                    <div class="ab-form-field">
                        <div class="ab-form-row">
                            <?php if ( array_key_exists( $type, $cron_reminder ) ) : ?>
                                <label class="ab-form-label" for="<?php echo $type ?>_cron_hour"><?php _e( 'Sending time', 'bookly' ) ?></label>
                                <select style="margin-right: 5px; min-width: 0;" class="form-control ab-inline-block ab-auto-w " name="<?php echo $type ?>_cron_hour" id="<?php echo $type ?>_cron_hour">
                                    <?php foreach ( range( 0, 23 ) as $hour ) : ?>
                                        <option value="<?php echo $hour ?>" <?php selected( $cron_reminder[ $type ], $hour ) ?>><?php echo \Bookly\Lib\Utils\DateTime::buildTimeString( $hour * HOUR_IN_SECONDS, false ) ?></option>
                                    <?php endforeach ?>
                                </select>
                                <?php \Bookly\Lib\Utils\Common::popover( __( 'Set the time you want the notification to be sent.', 'bookly' ) ) ?>
                            <?php endif ?>
                        </div>
                        <div class="ab-form-row">
                            <label class="ab-form-label"><?php _e( 'Message', 'bookly' ) ?></label>
                            <div class='ab-sms-holder'>
                                <?php echo $form->renderMessage( $type ) ?>
                                <span></span>
                            </div>
                        </div>
                        <div class="ab-form-row">
                            <label class="ab-form-label"><?php _e( 'Codes', 'bookly' ) ?></label>
                            <div class="ab-codes left">
                                <table>
                                    <tbody>
                                    <?php
                                    switch ( $type ) {
                                        case 'staff_agenda':        include '_codes_staff_agenda.php';        break;
                                        case 'client_new_wp_user':  include '_codes_client_new_wp_user.php';  break;
                                        default:                    include '_codes.php';
                                    } ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                        <?php if ( $type == 'staff_new_appointment' || $type == 'staff_cancelled_appointment' ) : ?>
                            <?php echo $form->renderCopy( $type ) ?>
                        <?php endif ?>
                    </div>
                </div>
            </div>
        </div>
    <?php endforeach ?>
    </div>
    <div>
        <?php if ( is_multisite() ) : ?>
            <i><?php printf( __( 'To send scheduled notifications please refer to <a href="%1$s">Bookly Multisite</a> add-on <a href="%2$s">message</a>.', 'bookly' ), 'http://codecanyon.net/item/bookly-multisite-addon/13903524?ref=ladela', network_admin_url( 'admin.php?page=bookly-multisite-network' ) ) ?></i><br />
        <?php else : ?>
            <i><?php _e( 'To send scheduled notifications please execute the following script hourly with your cron:', 'bookly' ) ?></i><br />
            <b>php -f <?php echo $cron_path ?></b>
        <?php endif ?>
    </div>
    <div class="ab-notifications" style="border: 0">
        <?php \Bookly\Lib\Utils\Common::submitButton( 'js-submit-notifications' ) ?>
        <?php \Bookly\Lib\Utils\Common::resetButton() ?>
    </div>
</form>