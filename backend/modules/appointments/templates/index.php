<?php if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly ?>
<div class="panel panel-default">
    <div class="panel-heading">
        <h3 class="panel-title"><?php _e( 'Appointments', 'bookly' ) ?></h3>
    </div>
    <div class="panel-body">
        <div ng-app="appointments" ng-controller="appointmentsCtrl" class="form-horizontal ng-cloak">

            <form style="margin-bottom: 20px" class="form-horizontal" action="<?php echo admin_url( 'admin-ajax.php' ) ?>?action=ab_export_appointments" method="POST">
                <div id=reportrange class="pull-left ab-reportrange">
                    <i class="glyphicon glyphicon-calendar"></i>
                    <span data-date="<?php echo date( 'F j, Y', strtotime( 'first day of' ) ) ?> - <?php echo date( 'F j, Y', strtotime( 'last day of' ) ) ?>"><?php echo date_i18n( get_option( 'date_format' ), strtotime( 'first day of' ) ) ?> - <?php echo date_i18n( get_option( 'date_format' ), strtotime( 'last day of' ) ) ?></span> <b style="margin-top: 8px;" class=caret></b>
                </div>
                <input type="hidden" name="date_start" ng-value="date_start" />
                <input type="hidden" name="date_end" ng-value="date_end" />
                <a style="margin-left: 5px;" href="#ab_export_appointments_dialog" class="btn btn-info pull-right" data-toggle="modal"><?php _e( 'Export to CSV', 'bookly' ) ?></a>
                <button type="button" class="btn btn-info pull-right" ng-click="newAppointment()"><?php _e( 'New appointment', 'bookly' ) ?></button>
                <div class="ab-clear"></div>
                <?php include '_export.php' ?>
            </form>

            <div style="display: none" class="control-group">
                <label for="ab_filter"><?php _e( 'Quick search appointment', 'bookly' ) ?></label>
                <div class=controls>
                    <input id="ab_filter" style="display: inline-block;width: auto;margin-bottom: 20px" class="form-control" type=text ng-model=filter />
                </div>
            </div>

            <div class="table-responsive">
                <table id="ab_appointments_list" class="table table-striped ab-clear" cellspacing=0 cellpadding=0 border=0>
                    <thead>
                    <tr>
                        <th style="width: 14%;" ng-class="css_class.start_date"><a href="" ng-click="reload({sort:'start_date'})"><?php _e( 'Booking Time', 'bookly' ) ?></a></th>
                        <th style="width: 14%;" ng-class="css_class.staff_name"><a href="" ng-click="reload({sort:'staff_name'})"><?php _e( 'Staff Member', 'bookly' ) ?></a></th>
                        <th style="width: 14%;" ng-class="css_class.customer_name"><a href="" ng-click="reload({sort:'customer_name'})"><?php _e( 'Customer Name', 'bookly' ) ?></a></th>
                        <th style="width: 14%;" ng-class="css_class.service_title"><a href="" ng-click="reload({sort:'service_title'})"><?php _e( 'Service', 'bookly' ) ?></a></th>
                        <th style="width: 14%;" ng-class="css_class.service_duration"><a href="" ng-click="reload({sort:'service_duration'})"><?php _e( 'Duration', 'bookly' ) ?></a></th>
                        <th colspan="2" ng-class="css_class.payment_total"><a href="" ng-click="reload({sort:'payment_total'})"><?php _e( 'Payment', 'bookly' ) ?></a></th>
                        <th style="width: 1%;" ><input type="checkbox" ng-model="selectedAll" ng-click="checkAll()"></th>
                    </tr>
                    </thead>
                    <tbody>
                    <tr ng-repeat="appointment in dataSource.appointments">
                        <td>{{appointment.start_date_f}}</td>
                        <td>{{appointment.staff_name}}</td>
                        <td>{{appointment.customer_name}}</td>
                        <td>{{appointment.service_title}}</td>
                        <td>{{appointment.service_duration}}</td>
                        <td data-ng-bind-html="appointment.price"></td>
                        <td>
                            <button class="btn btn-info pull-right" ng-click="editAppointment(appointment)">
                                <?php _e( 'Edit', 'bookly' ) ?>
                            </button>
                        </td>
                        <td><input type="checkbox" data-appointment_id="{{appointment.id}}" ng-model="appointment.Selected"></td>
                    </tr>
                    </tbody>
                </table>
                <div ng-hide="dataSource.appointments.length || loading" class="alert alert-info"><?php _e( 'No appointments for selected period.', 'bookly' ) ?></div>
            </div>

            <div>
                <div class="col-xs-8" role="toolbar">
                    <div ng-show="dataSource.pages.length > 1">
                        <div class="btn-group" role="group" ng-show="dataSource.paginator.beg">
                            <button ng-click=reload({page:1}) class="btn btn-default">
                                1
                            </button>
                        </div>
                        <div class="btn-group" role="group">
                            <button ng-click=reload({page:page.number}) class="btn btn-default" ng-class="{'active': page.active}" ng-repeat="page in dataSource.pages">
                                {{page.number}}
                            </button>
                        </div>
                        <div class="btn-group" role="group" ng-show="dataSource.paginator.end != false">
                            <button ng-click=reload({page:dataSource.paginator.end.number}) class="btn btn-default">
                                {{dataSource.paginator.end.number}}
                            </button>
                        </div>
                    </div>
                </div>
                <div class="col-xs-4">
                    <a class="btn btn-info pull-right" ng-click="deleteAppointments()"><?php _e( 'Delete', 'bookly' ) ?></a>
                </div>
            </div>

            <div ng-show="loading" class="loading-indicator">
                <span class="ab-loader"></span>
            </div>
        </div>
        <div id="ab-appointment-form">
            <?php include AB_PATH . '/backend/modules/calendar/templates/_appointment_form.php' ?>
        </div>
    </div>
</div>
