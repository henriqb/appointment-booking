<?php if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly ?>
<div id="ab_filter_error" class="alert alert-info" style="display: <?php echo ! ( $payments && count( $payments ) ) ? 'block' : 'none' ?>">
  <?php  _e( 'No payments for selected period and criteria.', 'bookly' ) ?>
</div>