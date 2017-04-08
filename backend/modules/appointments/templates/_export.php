<?php if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly ?>
<div id="ab_export_appointments_dialog" class="modal fade" tabindex=-1 role="dialog" aria-labelledby="exportAppointmentsModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                <h4 class="modal-title"><?php _e( 'Export to CSV', 'bookly' ) ?></h4>
            </div>
            <div class="modal-body">
                <div class="container-fluid">
                    <div class="form-group">
                        <label for="import_appointments_delimiter"><?php _e( 'Delimiter', 'bookly' ) ?></label>
                        <select name="export_appointments_delimiter" id="export_appointments_delimiter" class="form-control">
                            <option value=",">Comma (,)</option>
                            <option value=";">Semicolon (;)</option>
                        </select>
                    </div>
                </div>
                <div class="checkbox"> <label> <input checked value="<?php _e( 'Booking Time', 'bookly' ) ?>" name="app_exp[start_date]" type="checkbox"> <?php _e( 'Booking Time', 'bookly' ) ?> </label> </div>
                <div class="checkbox"> <label> <input checked value="<?php _e( 'Staff Member', 'bookly' ) ?>" name="app_exp[staff_name]" type="checkbox"> <?php _e( 'Staff Member', 'bookly' ) ?> </label> </div>
                <div class="checkbox"> <label> <input checked value="<?php _e( 'Service', 'bookly' ) ?>" name="app_exp[service_title]" type="checkbox"> <?php _e( 'Service', 'bookly' ) ?> </label> </div>
                <div class="checkbox"> <label> <input checked value="<?php _e( 'Duration', 'bookly' ) ?>" name="app_exp[service_duration]" type="checkbox"> <?php _e( 'Duration', 'bookly' ) ?> </label> </div>
                <div class="checkbox"> <label> <input checked value="<?php _e( 'Payment', 'bookly' ) ?>" name="app_exp[payment_total]" type="checkbox"> <?php _e( 'Payment', 'bookly' ) ?> </label> </div>
                <div class="checkbox"> <label> <input checked value="<?php _e( 'Customer Name', 'bookly' ) ?>" name="app_exp[customer_name]" type="checkbox"> <?php _e( 'Customer Name', 'bookly' ) ?> </label> </div>
                <div class="checkbox"> <label> <input checked value="<?php _e( 'Customer Phone', 'bookly' ) ?>" name="app_exp[customer_phone]" type="checkbox"> <?php _e( 'Customer Phone', 'bookly' ) ?> </label> </div>
                <div class="checkbox"> <label> <input checked value="<?php _e( 'Customer Email', 'bookly' ) ?>" name="app_exp[customer_email]" type="checkbox"> <?php _e( 'Customer Email', 'bookly' ) ?> </label> </div>
                <div class="checkbox"> <label> <input checked value="<?php _e( 'Custom Fields', 'bookly' ) ?>" name="custom_fields" type="checkbox"> <?php _e( 'Custom Fields', 'bookly' ) ?> </label> </div>
            </div>
            <div class="modal-footer">
                <button type="submit" class="btn btn-info ab-popup-save export-appointments"><?php _e( 'Export to CSV', 'bookly' ) ?></button>
                <button class="ab-reset-form" data-dismiss="modal" aria-hidden="true"><?php _e( 'Cancel', 'bookly' ) ?></button>
            </div>
        </div>
    </div>
</div>