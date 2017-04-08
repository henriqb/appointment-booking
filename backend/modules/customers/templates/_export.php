<?php if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly ?>
<div id="ab_export_customers_dialog" class="modal fade" tabindex=-1 role="dialog" aria-labelledby="exportCustomersModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <form class="form-horizontal" action="<?php echo admin_url( 'admin-ajax.php?action=ab_export_customers' ) ?>" method="POST">
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                    <h4 class="modal-title"><?php _e( 'Export to CSV', 'bookly' ) ?></h4>
                </div>
                <div class="modal-body">
                    <div class="container-fluid">
                        <div class="form-group">
                            <label for="import_customers_delimiter"><?php _e( 'Delimiter', 'bookly' ) ?></label>
                            <select name="export_customers_delimiter" id="export_customers_delimiter" class="form-control">
                                <option value=",">Comma (,)</option>
                                <option value=";">Semicolon (;)</option>
                            </select>
                        </div>
                    </div>
                    <div class="checkbox"> <label> <input checked value="<?php echo \Bookly\Lib\Utils\Common::getTranslatedOption( 'ab_appearance_text_label_name' ) ?>" name="exp[name]" type="checkbox"> <?php echo \Bookly\Lib\Utils\Common::getTranslatedOption( 'ab_appearance_text_label_name' ) ?> </label> </div>
                    <div class="checkbox"> <label> <input checked value="<?php _e( 'User', 'bookly' ) ?>" name="exp[wp_user]" type="checkbox"> <?php _e( 'User', 'bookly' ) ?> </label> </div>
                    <div class="checkbox"> <label> <input checked value="<?php echo \Bookly\Lib\Utils\Common::getTranslatedOption( 'ab_appearance_text_label_phone' ) ?>" name="exp[phone]" type="checkbox"> <?php echo \Bookly\Lib\Utils\Common::getTranslatedOption( 'ab_appearance_text_label_phone' ) ?> </label> </div>
                    <div class="checkbox"> <label> <input checked value="<?php echo \Bookly\Lib\Utils\Common::getTranslatedOption( 'ab_appearance_text_label_email' ) ?>" name="exp[email]" type="checkbox"> <?php echo \Bookly\Lib\Utils\Common::getTranslatedOption( 'ab_appearance_text_label_email' ) ?> </label> </div>
                    <div class="checkbox"> <label> <input checked value="<?php _e( 'Notes', 'bookly' ) ?>" name="exp[notes]" type="checkbox"> <?php _e( 'Notes', 'bookly' ) ?> </label> </div>
                    <div class="checkbox"> <label> <input checked value="<?php _e( 'Last appointment', 'bookly' ) ?>" name="exp[last_appointment]" type="checkbox"> <?php _e( 'Last appointment', 'bookly' ) ?> </label> </div>
                    <div class="checkbox"> <label> <input checked value="<?php _e( 'Total appointments', 'bookly' ) ?>" name="exp[total_appointments]" type="checkbox"> <?php _e( 'Total appointments', 'bookly' ) ?> </label> </div>
                    <div class="checkbox"> <label> <input checked value="<?php _e( 'Payments', 'bookly' ) ?>" name="exp[payments]" type="checkbox"> <?php _e( 'Payments', 'bookly' ) ?> </label> </div>
                </div>
                <div class="modal-footer">
                    <button type="submit" class="btn btn-info ab-popup-save export-customers"><?php _e( 'Export to CSV', 'bookly' ) ?></button>
                    <button class="ab-reset-form" data-dismiss="modal" aria-hidden="true"><?php _e( 'Cancel', 'bookly' ) ?></button>
                </div>
            </div>
        </form>
    </div>
</div>