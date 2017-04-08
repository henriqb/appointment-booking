<?php if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly ?>
<div class="btn-group ab--services-holder" style="display: none;">
    <button data-toggle="dropdown" class="btn btn-info"><span class="ab-count"></span></button>
    <button data-toggle="dropdown" class="btn btn-info dropdown-toggle"><span class="caret"></span></button>
    <ul class="dropdown-menu">
        <li>
            <a href="javascript:void(0)">
                <input type="checkbox" class="left ab-all-services" style="margin-right: 5px;">
                <label><?php _e( 'All Services', 'bookly' ) ?></label>
            </a>
            <?php foreach ( $services as $service ) : ?>
                <a href="javascript:void(0)" style="padding-left: 35px;">
                    <input type="checkbox" class="ab-service" value="<?php echo $service['id'] ?>" data-title="<?php echo esc_attr( $service['title'] ) ?>">
                    <label style="padding-right: 15px;"><?php echo $service['title'] ?></label>
                </a>
            <?php endforeach ?>
        </li>
    </ul>
</div>