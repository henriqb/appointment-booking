<?php if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly
    /** @var \Bookly\Lib\SMS $sms */
?>
<div class="panel panel-default">
    <div class="panel-heading">
        <h3 class="panel-title"><?php _e( 'SMS Notifications', 'bookly' ) ?></h3>
    </div>
    <div class="panel-body">
        <div class="ab-wrapper-container">
            <?php \Bookly\Lib\Utils\Common::notice( $errors, 'notice-error' ) ?>
            <?php \Bookly\Lib\Utils\Common::notice( $messages ) ?>

            <?php if ( $is_logged_in ) : ?>

                <div class="form-inline">
                    <div class="row">
                        <div class="text-left col-md-4 col-xs-8">
                            <ul class="list-group">
                                <li class="list-group-item">
                                    <?php _e( 'Your balance', 'bookly' ) ?>
                                    <div class="pull-right">
                                        $<?php echo $sms->getBalance() ?>
                                    </div>
                                </li>
                                <li class="list-group-item">
                                    <div class="ab-sms-notify-low-balance">
                                        <img src="<?php echo plugins_url( 'appointment-booking/backend/resources/images/loading.gif' ) ?>" style="display: none; margin-right: 5px;">
                                        <input type="checkbox" name="ab_sms_notify_low_balance" id="ab_sms_notify_low_balance" value="1" <?php checked( get_option( 'ab_sms_notify_low_balance' ) ) ?>>
                                    </div>
                                    <label style="margin-right: 50px" class="ab-block" for="ab_sms_notify_low_balance"><?php _e( 'Send email notification to administrators at low balance', 'bookly' ) ?></label>
                                </li>
                            </ul>
                        </div>
                        <div class="col-md-8 col-xs-4 text-right">
                            <div class="btn-group">
                                <button type="button" class="btn btn-info" data-toggle="modal" data-target="#modal_change_password"><?php echo $sms->getUserName() ?></button>
                                <button type="button" class="btn btn-info dropdown-toggle" data-toggle="dropdown" aria-expanded="false">
                                    <span class="caret"></span>
                                    <span class="sr-only">Toggle Dropdown</span>
                                </button>
                                <ul class="dropdown-menu pull-right" role="menu">
                                    <li>
                                        <a href="javascript:void(0)" data-toggle="modal" data-target="#modal_change_password"><?php _e( 'Change password', 'bookly' ) ?></a>
                                    </li>
                                    <li class="divider"></li>
                                    <li>
                                        <a href="javascript:void(0)">
                                            <form class="" method="post">
                                                <button type="submit" name="form-logout" class="ab-logout"><?php _e( 'Log out', 'bookly' ) ?></button>
                                            </form>
                                        </a>
                                    </li>
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>

                <div>
                    <ul class="nav nav-tabs" id="sms_tabs">
                        <li><a data-toggle="tab" href="#notifications"><?php _e( 'Notifications', 'bookly' ) ?></a></li>
                        <li><a data-toggle="tab" href="#add_money"><?php _e( 'Add money', 'bookly' ) ?></a></li>
                        <li><a data-toggle="tab" href="#auto_recharge"><?php _e( 'Auto-Recharge', 'bookly' ) ?></a></li>
                        <li><a data-toggle="tab" href="#purchases"><?php _e( 'Purchases', 'bookly' ) ?></a></li>
                        <li><a data-toggle="tab" href="#sms_details"><?php _e( 'SMS Details', 'bookly' ) ?></a></li>
                        <li><a data-toggle="tab" href="#price"><?php _e( 'Price list', 'bookly' ) ?></a></li>
                    </ul>
                    <div class="tab-content">
                        <div class="tab-pane" id="purchases">
                            <?php include '_purchases.php' ?>
                        </div>
                        <div class="tab-pane" id="add_money">
                            <?php include '_buttons.php' ?>
                        </div>
                        <div class="tab-pane" id="auto_recharge">
                            <?php include '_auto_recharge.php' ?>
                        </div>
                        <div class="tab-pane" id="notifications">
                            <?php include '_notifications.php' ?>
                        </div>
                        <div class="tab-pane" id="sms_details">
                            <?php include '_sms_details.php' ?>
                        </div>
                        <div class="tab-pane" id="price">
                            <?php include '_price.php' ?>
                        </div>
                    </div>
                </div>
            <?php else : ?>
                <div class="row">
                    <div class="col-xs-12">
                        <div class="alert alert-info"><?php _e( 'SMS Notifications (or "Bookly SMS") is a service for notifying your customers via text messages which are sent to mobile phones.<br/>It is necessary to register in order to start using this service.<br/>After registration you will need to configure notification messages and top up your balance in order to start sending SMS.', 'bookly' ) ?></div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-xs-3">
                        <form method="post" class="ab-login-form well" action="<?php echo esc_url( remove_query_arg( array( 'paypal_result', 'auto-recharge', 'tab' ) ) ) ?>">

                            <fieldset>
                                <legend><?php _e( 'Login', 'bookly' ) ?></legend>
                                <div class="form-group">
                                    <label for="ab_username"><?php _e( 'Email', 'bookly' ) ?></label>
                                    <input id="ab_username" class="form-control" type="text" required="required" value="" name="username"/>
                                </div>
                                <div class="form-group">
                                    <label for="ab_password"><?php _e( 'Password', 'bookly' ) ?></label>
                                    <input id="ab_password" class="form-control" type="password" required="required" name="password"/>
                                </div>
                                <div class="form-group">
                                    <button type="submit" name="form-login" class="btn btn-info"><?php _e( 'Log In', 'bookly' ) ?></button>
                                    <a href="#" class="show-register-form"><?php _e( 'Registration', 'bookly' ) ?></a>
                                    <a href="#" class="show-forgot-form"><?php _e( 'Forgot password', 'bookly' ) ?></a>
                                </div>
                            </fieldset>

                        </form>

                        <form method="post" class="ab-register-form well" style="display: none;">
                            <fieldset>
                                <legend><?php _e( 'Registration', 'bookly' ) ?></legend>
                                <div class="form-group">
                                    <label for="ab_r_username"><?php _e( 'Email', 'bookly' ) ?></label>
                                    <input id="ab_r_username" name="username" class="form-control" required="required" value="" type="text" />
                                </div>
                                <div class="form-group">
                                    <label for="ab_r_password"><?php _e( 'Password', 'bookly' ) ?></label>
                                    <input id="ab_r_password" name="password" class="form-control" required="required" value="" type="password" />
                                </div>
                                <div class="form-group">
                                    <label for="ab_r_repeat_password"><?php _e( 'Repeat password', 'bookly' ) ?></label>
                                    <input id="ab_r_repeat_password" name="password_repeat" class="form-control" required="required" value="" type="password" />
                                </div>
                                <div class="form-group">
                                    <label for="ab_r_tos"><?php _e( 'Accept <a href="javascript:void(0)" data-toggle="modal" data-target="#ab-tos">Terms & Conditions</a>', 'bookly' ) ?></label>
                                    <input id="ab_r_tos" name="accept_tos" class="form-control" required="required" value="1" type="checkbox" style="margin:0" />
                                </div>

                                <div class="form-group">
                                    <button type="submit" name="form-registration" class="btn btn-info"><?php _e( 'Register', 'bookly' ) ?></button>
                                    <a href="#" class="ab--show-login-form"><?php _e( 'Log In', 'bookly' ) ?></a>
                                </div>
                            </fieldset>
                        </form>

                        <form method="post" class="ab-forgot-form well" style="display: none;">
                            <fieldset>
                                <legend><?php _e( 'Forgot password', 'bookly' ) ?></legend>
                                <div class="form-group">
                                    <input name="username" class="form-control" value="" type="text" placeholder="<?php esc_attr_e( 'Email', 'bookly' ) ?>" />
                                </div>
                                <div class="form-group hidden">
                                    <input name="code" class="form-control" value="" type="text" placeholder="<?php esc_attr_e( 'Enter code from email', 'bookly' ) ?>" />
                                </div>
                                <div class="form-group hidden">
                                    <input name="password" class="form-control" value="" type="password" placeholder="<?php esc_attr_e( 'New password', 'bookly' ) ?>" />
                                </div>
                                <div class="form-group hidden">
                                    <input name="password_repeat" class="form-control" value="" type="password" placeholder="<?php esc_attr_e( 'Repeat new password', 'bookly' ) ?>" />
                                </div>
                                <div class="form-group">
                                    <button class="btn btn-info form-forgot-next" data-step="0"><?php _e( 'Next', 'bookly' ) ?></button>
                                    <a href="#" class="ab--show-login-form"><?php _e( 'Log In', 'bookly' ) ?></a>
                                </div>
                            </fieldset>
                        </form>
                    </div>
                    <div class="col-xs-9">
                        <?php include '_price.php' ?>
                    </div>
                </div>
            <?php endif ?>
        </div>
    </div>
</div>

<?php if ( $is_logged_in ) : ?>

<div class="modal fade" id="modal_change_password">
    <form id="form-change-password">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                <h4 class="modal-title"><?php _e( 'Change password', 'bookly' ) ?></h4>
            </div>
            <div class="modal-body">
                <div class="form-group">
                    <label for="old_password"><?php _e( 'Old password', 'bookly' ) ?></label>
                    <input type="password" class="form-control" id="old_password" name="old_password" placeholder="<?php esc_attr_e( 'Old password', 'bookly' ) ?>">
                </div>
                <div class="form-group">
                    <label for="new_password"><?php _e( 'New password', 'bookly' ) ?></label>
                    <input type="password" class="form-control" id="new_password" name="new_password" placeholder="<?php esc_attr_e( 'New password', 'bookly' ) ?>">
                </div>
                <div class="form-group">
                    <label for="new_password_repeat"><?php _e( 'Repeat new password', 'bookly' ) ?></label>
                    <input type="password" class="form-control" id="new_password_repeat" placeholder="<?php esc_attr_e( 'Repeat new password', 'bookly' ) ?>">
                </div>
            </div>
            <div class="modal-footer">
                <?php \Bookly\Lib\Utils\Common::submitButton( 'ajax-send-change-password' ) ?>
                <button type="button" class="ab-reset-form" data-dismiss="modal"><?php _e( 'Cancel', 'bookly' ) ?></button>
            </div>
            <input type="hidden" name="action" value="ab_change_password">
        </div>
    </div>
    </form>
</div>

<?php else : ?>

<div class="modal fade" id="ab-tos">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                <h4 class="modal-title"><?php _e( 'Terms & Conditions', 'bookly' ) ?></h4>
            </div>
            <div class="modal-body">
                <p>Sivid Software Limited, Belize trading as Bookly SMS undertakes to provide the Customer with value-added SMS services ("the Service"). Bookly SMS will use its reasonable endeavors to provide a prompt and continuing Service but will not be liable for any loss of data resulting from delays, non-deliveries, missed deliveries, or service interruptions caused by events beyond the control of Bookly SMS, or by errors or omissions of the Customer. Bookly SMS specifically excludes any warranty as to the accuracy of information received through the Service.</p>
                <p>Save as expressly set out herein, all conditions or warranties which may be implied or incorporated into this contract by law or otherwise are hereby expressly excluded to the extent permitted by law. In no circumstances whatsoever will Bookly SMS be liable for economic, indirect or consequential loss.</p>
                <p>Save where the Service is terminated by Bookly SMS without cause, the Customer shall not be entitled to a refund of subscriptions paid.</p>
                <h3>Term, Suspension and Termination of Service</h3>
                <p>This contract shall be for a term of 3 months from the date of adding money to balance by the Customer or the contract is terminated in accordance with the terms hereof.</p>
                <p>Bookly SMS may elect to suspend or terminate the Service immediately and without prior notice, on breach of any of the terms and conditions of this contract, including without limitation late or non-payment of sums due.</p>
                <p>From time to time certain mobile gateways, servers, or the whole or part of the Service may be closed down for routine repair, upgrade or maintenance work. Bookly SMS shall give as much notice as in the circumstances is reasonable and shall endeavor to carry out such works during the scheduled maintenance periods as published from time to time.</p>
                <h3>Improper Use and Liabilities</h3>
                <h4>Use by the Customer</h4>
                <p>The Customer acknowledges that it may only use the Service for lawful purposes. The Customer warrants that:</p>
                <ul style="list-style:disc;padding-left:40px;">
                    <li>it shall not (or authorise or permit any other party to) use the Service to receive or transmit material which is in violation of any law, regulation or the Bookly SMS Acceptable Use Policy, or which is obscene, threatening, menacing, offensive, defamatory, in breach of confidence, in breach of any intellectual property right (including copyright), or otherwise unlawful;</li>
                    <li>it shall not knowingly or recklessly transmit any electronic material (including viruses) through the Service which shall cause or is likely to cause detriment or harm, in any degree, to computer systems owned by Bookly SMS, other customers of the Service, or any other Service users;</li>
                    <li>it shall not use any source address that is not allocated for its use by Bookly SMS;</li>
                    <li>it as the registered user of the account will keep all allocated username(s) and password(s) secure and not let them become public knowledge and that the password(s) will not be stored anywhere on a computer in plain text;</li>
                    <li>if any password for the Service becomes known to any other unauthorised user it will inform Bookly SMS immediately;</li>
                    <li>all information they provide, including during service application, will be accurate and correct;</li>
                    <li>any breach of these obligations shall entitle Bookly SMS to immediately terminate the Service to the Customer without notice.</li>
                </ul>
                <p>The Customer hereby agrees to fully indemnify and to hold Bookly SMS harmless from and against any claim brought by a third party resulting from the use of the Service by the Customer and in respect of all losses, costs, actions, proceedings, claims, damages, expenses (including reasonable legal costs and expenses), or liabilities, whatsoever suffered or incurred directly by Bookly SMS in consequence of the Customer's breach or non-observance of these terms and conditions.</p>
                <p>The Customer shall defend and pay all costs, damages, awards, fees (including any reasonable legal fees) and judgments awarded against Bookly SMS arising from the above claims and shall provide Bookly SMS with notice of such claims, full authority to defend, compromise or settle such claims and reasonable assistance necessary to defend such claims, at the Customer's sole expense.</p>
                <p>The Customer shall be liable to pay all and any additional charges in connection with the use of the Service including those levied by its telephone service provider(s).</p>
                <h4>Use by others</h4>
                <p>The Customer acknowledges that Bookly SMS is unable to exercise control over the content of information passing over the Service, and Bookly SMS hereby excludes all liability of any kind for the transmission or reception of infringing information of whatever nature.</p>
                <h3>Prices</h3>
                <p>All prices are subject to change without notice. The prices shown in this online price list supersede all previous prices. However, we cannot control price increases by our suppliers. We also reserve the right to correct misprints.</p>
                <h3>Taxes</h3>
                <p>Fees and all other amounts mentioned in this Agreement do not include any taxes, all of which will be paid by Customer (except for Bookly SMS income taxes). In the event that Bookly SMS is required by applicable law to pay or remit such Taxes, Customer will reimburse MessageMedia for such amounts.</p>
                <h3>Password</h3>
                <p>Bookly SMS reserves the right to change the Customer's allocated password(s) at any time at its sole discretion.</p>
                <h3>Data Protection</h3>
                <p>You agree that we may put your name and other information obtained about you from your subscription and the sales process into a computerised directory for internal use only, until we receive specific written instructions from you. Note that no personal details will be passed from us onto other companies, organisations or individuals not connected with servicing your subscription.</p>
                <h3>Trial Accounts</h3>
                <p></p>Trial (or evaluation) account Customers acknowledge that access to the Service may be restricted at the sole discretion of Bookly SMS in the interests of fully subscribed customers.</p>
                <h3>Refunds</h3>
                <p>Refunds will be given at the discretion of the Company Management.</p>
                <h3>General</h3>
                <p>Bookly SMS reserves the right to vary these terms and conditions from time to time. Such changes shall be notified to the Customer by posting on the Bookly SMS Web site. Changes in this manner shall be deemed to have been accepted if the Customer continues to use the Service after a period of two weeks from the date of posting on the Web site.</p>
                <p>Bookly SMS shall not be liable in respect of any breach of this contract due to any cause beyond its reasonable control including but not limited to, inclement weather, hardware failures, network outages, act or omission of Government or public telephone operators or other competent authority or other party for whom Bookly SMS is not responsible.</p>
                <p>The Customer acknowledges that it has read and accepts the terms of this contract. Use of the service by the Customer shall be deemed acceptance of the terms of this contract.</p>
                <p>This contract shall be governed by and construed in accordance with the laws of Belize and the Customer hereby submits to the exclusive jurisdiction of the courts of Belize.</p>
            </div>
            <div class="modal-footer">

            </div>
        </div>
    </div>
</div>

<?php endif ?>