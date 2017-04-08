<?php if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly ?>

<div id="ab_coupons_wrapper" class="panel panel-default">
    <div class="panel-heading">
        <h3 class="panel-title"><?php _e( 'Coupons', 'bookly' ) ?></h3>
    </div>
    <div class="panel-body">
        <div class="no-result"<?php if ( count( $coupons_collection ) ) : ?> style="display: none"<?php endif ?>><?php _e( 'No coupons found', 'bookly' ) ?></div>
        <div class="list-wrapper">
            <div id="ab-coupons-list">
                <?php include '_list.php' ?>
            </div>
        </div>
    </div>
    <div class="panel-footer">
        <div class="list-actions">
            <a class="add-coupon btn btn-info" href="#"><?php _e( 'Add Coupon', 'bookly' ) ?></a>
            <a class="delete btn btn-info" href="#"><?php _e( 'Delete', 'bookly' ) ?></a>
        </div>
    </div>
</div>