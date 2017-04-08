<?php if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly ?>
<div class="ab-booking-form">
    <!-- Progress Tracker-->
    <?php include '_progress_tracker.php'; ?>
    <div style="display: table; width: 100%" class="ab-row-fluid">
    <div class="ab-desc">
        <div class="ab-col-1">
            <span data-inputclass="input-xxlarge" data-notes="<?php echo esc_attr( $this->render( '_codes', array( 'step' => 4 ), false ) ) ?>" data-placement="bottom" data-default="<?php echo esc_attr( get_option( 'ab_appearance_text_info_cart_step' ) ) ?>" class="ab-row-fluid ab_editable" id="ab-text-info-cart" data-type="textarea"><?php echo esc_html( get_option( 'ab_appearance_text_info_cart_step' ) ) ?></span>
        </div>
        <div class="ab-col-2">
            <div class="ab-add-item ab-btn ab-inline-block">
                <span class="ab_editable" id="ab-text-button-book-more" data-type="text" data-default="<?php echo esc_attr( get_option( 'ab_appearance_text_button_book_more' ) ) ?>"><?php echo esc_html( get_option( 'ab_appearance_text_button_book_more' ) ) ?></span>
            </div>
        </div>
    </div>
    </div>
    <div class="ab-cart-step">
        <div class="ab-cart">
            <table>
                <thead class="ab-desktop-version">
                    <tr>
                        <th data-default="<?php echo esc_attr( get_option( 'ab_appearance_text_label_service' ) ) ?>" class="ab-service-list"><?php echo esc_html( get_option( 'ab_appearance_text_label_service' ) ) ?></th>
                        <th><?php _e( 'Date', 'bookly' ) ?></th>
                        <th><?php _e( 'Time', 'bookly' ) ?></th>
                        <th data-default="<?php echo esc_attr( get_option( 'ab_appearance_text_label_employee' ) ) ?>" class="ab-employee-list"><?php echo \Bookly\Lib\Utils\Common::getTranslatedOption( 'ab_appearance_text_label_employee' ) ?></th>
                        <th><?php _e( 'Price', 'bookly' ) ?></th>
                        <th></th>
                    </tr>
                </thead>
                <tbody class="ab-desktop-version">
                    <tr>
                        <td>Crown and Bridge</td>
                        <td><?php echo \Bookly\Lib\Utils\DateTime::formatDate( strtotime( '+2 days' ) ) ?></td>
                        <td><?php echo \Bookly\Lib\Utils\DateTime::formatTime( 28800 ) ?></td>
                        <td>Nick Knight</td>
                        <td><?php echo \Bookly\Lib\Utils\Common::formatPrice( 350 ) ?></td>
                        <td>
                            <span title="<?php esc_attr_e( 'Edit', 'bookly' ) ?>" class="ab--actions" data-action="edit"></span>
                            <span title="<?php esc_attr_e( 'Remove', 'bookly' ) ?>" class="ab--actions" data-action="drop"></span>
                        </td>
                    </tr>
                    <tr>
                        <td>Teeth Whitening</td>
                        <td><?php echo \Bookly\Lib\Utils\DateTime::formatDate( strtotime( '+3 days' ) ) ?></td>
                        <td><?php echo \Bookly\Lib\Utils\DateTime::formatTime( 57600 ) ?></td>
                        <td>Wayne Turner</td>
                        <td><?php echo \Bookly\Lib\Utils\Common::formatPrice( 400 ) ?></td>
                        <td>
                            <span title="<?php esc_attr_e( 'Edit', 'bookly' ) ?>" class="ab--actions" data-action="edit"></span>
                            <span title="<?php esc_attr_e( 'Remove', 'bookly' ) ?>" class="ab--actions" data-action="drop"></span>
                        </td>
                    </tr>
                </tbody>
                <tbody class="ab-mobile-version">
                    <tr>
                        <th data-default="<?php echo esc_attr( get_option( 'ab_appearance_text_label_service' ) ) ?>" class="ab-service-list"><?php echo \Bookly\Lib\Utils\Common::getTranslatedOption( 'ab_appearance_text_label_service' ) ?></th>
                        <td>Crown and Bridge</td>
                    </tr>
                    <tr>
                        <th><?php _e( 'Date', 'bookly' ) ?></th>
                        <td><?php echo \Bookly\Lib\Utils\DateTime::formatDate( strtotime( '+2 days' ) ) ?></td>
                    </tr>
                    <tr>
                        <th><?php _e( 'Time', 'bookly' ) ?></th>
                        <td><?php echo \Bookly\Lib\Utils\DateTime::formatTime( 28800 ) ?></td>
                    </tr>
                    <tr>
                        <th data-default="<?php echo esc_attr( get_option( 'ab_appearance_text_label_employee' ) ) ?>" class="ab-employee-list"><?php echo \Bookly\Lib\Utils\Common::getTranslatedOption( 'ab_appearance_text_label_employee' ) ?></th>
                        <td>Nick Knight</td>
                    </tr>
                    <tr>
                        <th><?php _e( 'Price', 'bookly' ) ?></th>
                        <td><?php echo \Bookly\Lib\Utils\Common::formatPrice( 350 ) ?></td>
                    </tr>
                    <tr>
                        <th></th>
                        <td>
                            <a href="javascript:void(0)" title="<?php esc_attr_e( 'Edit', 'bookly' ) ?>" class="ab--actions ladda-button" data-style="zoom-in" data-spinner-size="20" data-action="edit"></a>
                            <a href="javascript:void(0)" title="<?php esc_attr_e( 'Remove', 'bookly' ) ?>" class="ab--actions ladda-button" data-style="zoom-in" data-spinner-size="20" data-action="drop"></a>
                        </td>
                    </tr>
                    <tr>
                        <th data-default="<?php echo esc_attr( get_option( 'ab_appearance_text_label_service' ) ) ?>" class="ab-service-list"><?php echo \Bookly\Lib\Utils\Common::getTranslatedOption( 'ab_appearance_text_label_service' ) ?></th>
                        <td>Teeth Whitening</td>
                    </tr>
                    <tr>
                        <th><?php _e( 'Date', 'bookly' ) ?></th>
                        <td><?php echo \Bookly\Lib\Utils\DateTime::formatDate( strtotime( '+3 days' ) ) ?></td>
                    </tr>
                    <tr>
                        <th><?php _e( 'Time', 'bookly' ) ?></th>
                        <td><?php echo \Bookly\Lib\Utils\DateTime::formatTime( 57600 ) ?></td>
                    </tr>
                    <tr>
                        <th data-default="<?php echo esc_attr( get_option( 'ab_appearance_text_label_employee' ) ) ?>" class="ab-employee-list"><?php echo \Bookly\Lib\Utils\Common::getTranslatedOption( 'ab_appearance_text_label_employee' ) ?></th>
                        <td>Wayne Turner</td>
                    </tr>
                    <tr>
                        <th><?php _e( 'Price', 'bookly' ) ?></th>
                        <td><?php echo \Bookly\Lib\Utils\Common::formatPrice( 400 ) ?></td>
                    </tr>
                    <tr>
                        <th></th>
                        <td>
                            <a href="javascript:void(0)" title="<?php esc_attr_e( 'Edit', 'bookly' ) ?>" class="ab--actions ladda-button" data-style="zoom-in" data-spinner-size="20" data-action="edit"></a>
                            <a href="javascript:void(0)" title="<?php esc_attr_e( 'Remove', 'bookly' ) ?>" class="ab--actions ladda-button" data-style="zoom-in" data-spinner-size="20" data-action="drop"></a>
                        </td>
                    </tr>
                </tbody>
                <tfoot class="ab-desktop-version">
                    <tr>
                        <td colspan="4"><strong><?php _e( 'Total', 'bookly' ) ?>:</strong></td>
                        <td><strong class="ab-total-price"><?php echo \Bookly\Lib\Utils\Common::formatPrice( 750 ) ?></strong></td>
                        <td></td>
                    </tr>
                </tfoot>
                <tfoot class="ab-mobile-version">
                    <tr>
                        <th><strong><?php _e( 'Total', 'bookly' ) ?>:</strong></th>
                        <td><strong class="ab-total-price"><?php echo \Bookly\Lib\Utils\Common::formatPrice( 750 ) ?></strong></td>
                    </tr>
                </tfoot>
            </table>
        </div>
    </div>
    <div class="ab-row-fluid ab-nav-steps last-row ab-clear">
        <div class="ab-left ab-back-step ab-btn">
            <span class="text_back ab_editable" id="ab-text-button-back" data-mirror="text_back" data-type="text" data-default="<?php echo esc_attr( get_option( 'ab_appearance_text_button_back' ) ) ?>"><?php echo esc_html( get_option( 'ab_appearance_text_button_back' ) ) ?></span>
        </div>
        <div class="ab-right ab-next-step ab-btn">
            <span class="text_next ab_editable" id="ab-text-button-next" data-mirror="text_next" data-type="text" data-default="<?php echo esc_attr( get_option( 'ab_appearance_text_button_next' ) ) ?>"><?php echo esc_html( get_option( 'ab_appearance_text_button_next' ) ) ?></span>
        </div>
    </div>
</div>
