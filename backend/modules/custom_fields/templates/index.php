<?php if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly ?>
<div class="panel panel-default">
    <div class="panel-heading">
        <h3 class="panel-title"><?php _e( 'Custom Fields', 'bookly' ) ?></h3>
    </div>
    <div class="panel-body">

        <div style="margin-bottom: 20px" class="form-inline">
            <div class="form-group">
                <span class="help-inline"><?php _e( 'Bind fields to services', 'bookly' ) ?></span>
                <?php \Bookly\Lib\Utils\Common::optionToggle( 'ab_custom_fields_per_service' ) ?>
                <?php \Bookly\Lib\Utils\Common::popover( __( 'When this setting is enabled you will be able to create service specific custom fields. In this case the details step of booking will be displayed before the cart step.', 'bookly' ) ) ?>
            </div>
        </div>

        <ul id="ab-custom-fields"></ul>

        <div id="ab-add-fields">
            <button class="button" data-type="text-field"><i class="glyphicon glyphicon-plus"></i> <?php _e( 'Text Field', 'bookly' ) ?></button>&nbsp;
            <button class="button" data-type="textarea"><i class="glyphicon glyphicon-plus"></i> <?php _e( 'Text Area', 'bookly' ) ?></button>&nbsp;
            <button class="button" data-type="text-content"><i class="glyphicon glyphicon-plus"></i> <?php _e( 'Text Content', 'bookly' ) ?></button>&nbsp;
            <button class="button" data-type="checkboxes"><i class="glyphicon glyphicon-plus"></i> <?php _e( 'Checkbox Group', 'bookly' ) ?></button>&nbsp;
            <button class="button" data-type="radio-buttons"><i class="glyphicon glyphicon-plus"></i> <?php _e( 'Radio Button Group', 'bookly' ) ?></button>&nbsp;
            <button class="button" data-type="drop-down"><i class="glyphicon glyphicon-plus"></i> <?php _e( 'Drop Down', 'bookly' ) ?></button>&nbsp;
            <button class="button" data-type="captcha"><i class="glyphicon glyphicon-plus"></i> <?php _e( 'Captcha', 'bookly' ) ?></button>
        </div>
        <p class="help-block"><?php _e( 'HTML allowed in all texts and labels.', 'bookly' ) ?></p>
        <ul id="ab-templates" style="display:none">

            <li data-type="textarea">
                <i class="ab-handle glyphicon glyphicon-align-justify" title="<?php esc_attr_e( 'Reorder', 'bookly' ) ?>"></i>
                <h2 class="ab-field-title">
                    <?php _e( 'Text Area', 'bookly' ) ?>
                    <i class="ab-delete glyphicon glyphicon-trash" title="<?php esc_attr_e( 'Remove field', 'bookly' ) ?>"></i>
                </h2>
                <table>
                    <tr>
                        <td>
                            <div class="input-group">
                                <input class="ab-label form-control" type="text" value="" placeholder="<?php esc_attr_e( 'Enter a label', 'bookly' ) ?>" />
                                <span class="input-group-addon">
                                    <label>
                                        <input class="ab-required" type="checkbox" />
                                        <span><?php _e( 'Required field', 'bookly' ) ?></span>
                                    </label>
                                </span>
                            </div>
                        </td>
                        <td>
                            <?php echo $services_html ?>
                        </td>
                    </tr>
                </table>
            </li>

            <li data-type="text-content">
                <i class="ab-handle glyphicon glyphicon-align-justify" title="<?php esc_attr_e( 'Reorder', 'bookly' ) ?>"></i>
                <h2 class="ab-field-title">
                    <?php _e( 'Text Content', 'bookly' ) ?>
                    <i class="ab-delete glyphicon glyphicon-trash" title="<?php esc_attr_e( 'Remove field', 'bookly' ) ?>"></i>
                </h2>
                <table style="margin-left: 15px;">
                    <tr>
                        <td>
                            <textarea style="width: 325px;" class="ab-label form-control" type="text" rows="3" placeholder="<?php esc_attr_e( 'Enter a content', 'bookly' ) ?>"></textarea>
                            <input class="ab-required hidden" type="checkbox" disabled="disabled" />
                        </td>
                        <td><?php echo $services_html ?></td>
                    </tr>
                </table>
            </li>

            <li data-type="text-field">
                <i class="ab-handle glyphicon glyphicon-align-justify" title="<?php esc_attr_e( 'Reorder', 'bookly' ) ?>"></i>
                <h2 class="ab-field-title">
                    <?php _e( 'Text Field', 'bookly' ) ?>
                    <i class="ab-delete glyphicon glyphicon-trash" title="<?php esc_attr_e( 'Remove field', 'bookly' ) ?>"></i>
                </h2>
                <table>
                    <tr>
                        <td>
                            <div class="input-group">
                                <input class="ab-label form-control" type="text" value="" placeholder="<?php esc_attr_e( 'Enter a label', 'bookly' ) ?>" />
                                <span class="input-group-addon">
                                    <label>
                                        <input class="ab-required" type="checkbox" />
                                        <span><?php _e( 'Required field', 'bookly' ) ?></span>
                                    </label>
                                </span>
                            </div>
                        </td>
                        <td><?php echo $services_html ?></td>
                    </tr>
                </table>
            </li>

            <li data-type="checkboxes">
                <i class="ab-handle glyphicon glyphicon-align-justify" title="<?php esc_attr_e( 'Reorder', 'bookly' ) ?>"></i>
                <h2 class="ab-field-title">
                    <?php _e( 'Checkbox Group', 'bookly' ) ?>
                    <i class="ab-delete glyphicon glyphicon-trash" title="<?php esc_attr_e( 'Remove field', 'bookly' ) ?>"></i>
                </h2>
                <table>
                    <tr>
                        <td>
                            <div class="input-group">
                                <input class="ab-label form-control" type="text" value="" placeholder="<?php esc_attr_e( 'Enter a label', 'bookly' ) ?>" />
                                <span class="input-group-addon">
                                    <label>
                                        <input class="ab-required" type="checkbox" />
                                        <span><?php _e( 'Required field', 'bookly' ) ?></span>
                                    </label>
                                </span>
                            </div>
                        </td>
                        <td><?php echo $services_html ?></td>
                    </tr>
                </table>
                <ul class="ab-items"></ul>
                <button class="button" data-type="checkboxes-item"><i class="glyphicon glyphicon-plus"></i> <?php _e( 'Checkbox', 'bookly' ) ?></button>
            </li>

            <li data-type="radio-buttons">
                <i class="ab-handle glyphicon glyphicon-align-justify" title="<?php esc_attr_e( 'Reorder', 'bookly' ) ?>"></i>
                <h2 class="ab-field-title">
                    <?php _e( 'Radio Button Group', 'bookly' ) ?>
                    <i class="ab-delete glyphicon glyphicon-trash" title="<?php esc_attr_e( 'Remove field', 'bookly' ) ?>"></i>
                </h2>
                <table>
                    <tr>
                        <td>
                            <div class="input-group">
                                <input class="ab-label form-control" type="text" value="" placeholder="<?php esc_attr_e( 'Enter a label', 'bookly' ) ?>" />
                                <span class="input-group-addon">
                                    <label>
                                        <input class="ab-required" type="checkbox" />
                                        <span><?php _e( 'Required field', 'bookly' ) ?></span>
                                    </label>
                                </span>
                            </div>
                        </td>
                        <td><?php echo $services_html ?></td>
                    </tr>
                </table>
                <ul class="ab-items"></ul>
                <button class="button" data-type="radio-buttons-item"><i class="glyphicon glyphicon-plus"></i> <?php _e( 'Radio Button', 'bookly' ) ?></button>
            </li>

            <li data-type="drop-down">
                <i class="ab-handle glyphicon glyphicon-align-justify" title="<?php esc_attr_e( 'Reorder', 'bookly' ) ?>"></i>
                <h2 class="ab-field-title">
                    <?php _e( 'Drop Down', 'bookly' ) ?>
                    <i class="ab-delete glyphicon glyphicon-trash" title="<?php esc_attr_e( 'Remove field', 'bookly' ) ?>"></i>
                </h2>
                <table>
                    <tr>
                        <td>
                            <div class="input-group">
                                <input class="ab-label form-control" type="text" value="" placeholder="<?php esc_attr_e( 'Enter a label', 'bookly' ) ?>" />
                                <span class="input-group-addon">
                                    <label>
                                        <input class="ab-required" type="checkbox" />
                                        <span><?php _e( 'Required field', 'bookly' ) ?></span>
                                    </label>
                                </span>
                            </div>
                        </td>
                        <td><?php echo $services_html ?></td>
                    </tr>
                </table>
                <ul class="ab-items"></ul>
                <button class="button" data-type="drop-down-item"><i class="glyphicon glyphicon-plus"></i> <?php _e( 'Option', 'bookly' ) ?></button>
            </li>

            <li data-type="captcha">
                <i class="ab-handle glyphicon glyphicon-align-justify" title="<?php esc_attr_e( 'Reorder', 'bookly' ) ?>"></i>
                <h2 class="ab-field-title">
                    <?php _e( 'Captcha', 'bookly' ) ?>
                    <i class="ab-delete glyphicon glyphicon-trash" title="<?php esc_attr_e( 'Remove field', 'bookly' ) ?>"></i>
                </h2>
                <table>
                    <tr>
                        <td>
                            <div class="input-group">
                                <input class="ab-label form-control" type="text" value="" placeholder="<?php esc_attr_e( 'Enter a label', 'bookly' ) ?>" />
                                <span class="input-group-addon">
                                    <label>
                                        <input class="ab-required hidden" type="checkbox" />
                                        <input type="checkbox" disabled="disabled" checked="checked" />
                                        <span><?php _e( 'Required field', 'bookly' ) ?></span>
                                    </label>
                                </span>
                            </div>
                        </td>
                        <td><?php echo $services_html ?></td>
                    </tr>
                </table>
            </li>

            <li data-type="checkboxes-item">
                <i class="ab-inner-handle glyphicon glyphicon-align-justify" title="<?php esc_attr_e( 'Reorder', 'bookly' ) ?>"></i>
                <input class="form-control ab-inline-block" type="text" value="" placeholder="<?php esc_attr_e( 'Enter a label', 'bookly' ) ?>" />
                <i class="ab-delete glyphicon glyphicon-trash" title="<?php esc_attr_e( 'Remove item', 'bookly' ) ?>"></i>
            </li>

            <li data-type="radio-buttons-item">
                <i class="ab-inner-handle glyphicon glyphicon-align-justify" title="<?php esc_attr_e( 'Reorder', 'bookly' ) ?>"></i>
                <input class="form-control ab-inline-block" type="text" value="" placeholder="<?php esc_attr_e( 'Enter a label', 'bookly' ) ?>" />
                <i class="ab-delete glyphicon glyphicon-trash" title="<?php esc_attr_e( 'Remove item', 'bookly' ) ?>"></i>
            </li>

            <li data-type="drop-down-item">
                <i class="ab-inner-handle glyphicon glyphicon-align-justify" title="<?php esc_attr_e( 'Reorder', 'bookly' ) ?>"></i>
                <input class="form-control ab-inline-block" type="text" value="" placeholder="<?php esc_attr_e( 'Enter a label', 'bookly' ) ?>" />
                <i class="ab-delete glyphicon glyphicon-trash" title="<?php esc_attr_e( 'Remove item', 'bookly' ) ?>"></i>
            </li>

        </ul>
    </div>
    <div class="panel-footer">
        <?php \Bookly\Lib\Utils\Common::submitButton( 'ajax-send-custom-fields' ) ?>
        <?php \Bookly\Lib\Utils\Common::resetButton() ?>
    </div>
</div>
