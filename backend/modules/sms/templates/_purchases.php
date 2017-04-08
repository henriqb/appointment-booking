<?php if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly ?>
<form method="post">
    <div id="reportrange_purchases" class="pull-left ab-reportrange" style="margin-bottom: 10px">
        <i class="glyphicon glyphicon-calendar"></i>
        <input type="hidden" name="form-purchases">
        <span data-date="<?php echo date( 'Y-m-d', strtotime( '-30 days' ) ) ?> - <?php echo date( 'Y-m-d' ) ?>"><?php echo date_i18n( get_option( 'date_format' ), strtotime( '-30 days' ) ) ?> - <?php echo date_i18n( get_option( 'date_format' ) ) ?></span> <b style="margin-top: 8px;" class=caret></b>
    </div>
    <div class="btn btn-info" id="get_list_purchases"><?php _e( 'Filter', 'bookly' ) ?></div>
</form>
<table class="table table-striped">
    <thead>
    <tr>
        <th><?php _e( 'Date', 'bookly' ) ?></th>
        <th><?php _e( 'Time', 'bookly' ) ?></th>
        <th><?php _e( 'Type', 'bookly' ) ?></th>
        <th><?php _e( 'Order', 'bookly' ) ?></th>
        <th><?php _e( 'Status', 'bookly' ) ?></th>
        <th><?php _e( 'Amount', 'bookly' ) ?></th>
    </tr>
    </thead>
    <tbody id="pay_orders">
        <tr><td colspan="6"><span class="ab-loader"></span></td></tr>
    </tbody>
</table>