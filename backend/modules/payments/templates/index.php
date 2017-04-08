<?php if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly ?>
<div class="panel panel-default">
    <div class="panel-heading">
        <h3 class="panel-title"><?php _e( 'Payments', 'bookly' ) ?></h3>
    </div>
    <div class="panel-body">
        <div class=ab-nav-payment>
            <div class=row-fluid>
                <div id=reportrange class="ab-reportrange ab-inline-block">
                    <i class="glyphicon glyphicon-calendar"></i>
                    <span data-date="<?php echo date( 'Y-m-d', strtotime( '-30 day' ) ) ?> - <?php echo date( 'Y-m-d' ) ?>"><?php echo date_i18n( get_option( 'date_format' ), strtotime( '-30 day' ) ) ?> - <?php echo date_i18n( get_option( 'date_format' ) ) ?></span> <b style="margin-top: 8px;" class=caret></b>
                </div>
                <div class=ab-inline-block>
                    <select id=ab-type-filter class=selectpicker>
                        <option value="-1"><?php _e( 'All payment types', 'bookly' ) ?></option>
                        <?php foreach ( $types as $type ) : ?>
                            <option value="<?php echo esc_attr( $type ) ?>">
                                <?php echo \Bookly\Lib\Entities\Payment::typeToString( $type ) ?>
                            </option>
                        <?php endforeach ?>
                    </select>
                    <select id=ab-provider-filter class=selectpicker>
                        <option value="-1"><?php _e( 'All providers', 'bookly' ) ?></option>
                        <?php foreach ( $providers as $provider ) : ?>
                            <option><?php echo esc_html( $provider ) ?></option>
                        <?php endforeach ?>
                    </select>
                    <select id=ab-service-filter class=selectpicker>
                        <option value="-1"><?php _e( 'All services', 'bookly' ) ?></option>
                        <?php foreach ( $services as $service ) : ?>
                            <option><?php echo esc_html( $service ) ?></option>
                        <?php endforeach ?>
                    </select>
                    <a id=ab-filter-submit href="#" class="btn btn-primary"><?php _e( 'Filter', 'bookly' ) ?></a>
                </div>
                <div class="pull-right"><a href="<?php echo \Bookly\Lib\Utils\Common::escAdminUrl( \Bookly\Backend\Modules\Settings\Controller::page_slug, array( 'tab' => 'payments' ) )?>" class="btn btn-info"><?php _e( 'Settings', 'bookly' )?></a></div>
            </div>
        </div>
        <div id=ab-alert-div class=alert style="display: none"></div>
        <div class="table-responsive">
            <table class="table table-striped" cellspacing=0 cellpadding=0 border=0 id=ab_payments_list>
                <thead>
                <tr>
                    <th width=150 class="desc active" order-by=created><a href="javascript:void(0)"><?php _e( 'Date', 'bookly' ) ?></a></th>
                    <th width=100 order-by=type><a href="javascript:void(0)"><?php _e( 'Type', 'bookly' ) ?></a></th>
                    <th width=150 order-by=customer><a href="javascript:void(0)"><?php _e( 'Customer', 'bookly' ) ?></a></th>
                    <th width=150 order-by=provider><a href="javascript:void(0)"><?php _e( 'Provider', 'bookly' ) ?></a></th>
                    <th width=150 order-by=service><a href="javascript:void(0)"><?php _e( 'Service', 'bookly' ) ?></a></th>
                    <th width=50  order-by=total><a href="javascript:void(0)"><?php _e( 'Amount', 'bookly' ) ?></a></th>
                    <th width=50  order-by=status><a href="javascript:void(0)"><?php _e( 'Status', 'bookly' ) ?></a></th>
                    <th width=50  order-by=coupon><a href="javascript:void(0)"><?php _e( 'Coupon', 'bookly' ) ?></a></th>
                    <th width=150 order-by=start_date><a href="javascript:void(0)"><?php _e( 'Appointment Date', 'bookly' ) ?></a></th>
                </tr>
                </thead>
                <tbody id=ab-tb-body>
                <?php include '_body.php' ?>
                </tbody>
            </table>
        </div>
        <?php include '_alert.php' ?>
        <div style="display: none" class="loading-indicator">
            <span class="ab-loader"></span>
        </div>
    </div>
</div>

<script type="text/javascript">
    jQuery(function($) {
        var data          = {},
            $report_range = $('#reportrange span'),
            picker_ranges = {};

        picker_ranges[BooklyL10n.today]      = [moment(), moment()];
        picker_ranges[BooklyL10n.yesterday]  = [moment().subtract(1, 'days'), moment().subtract(1, 'days')];
        picker_ranges[BooklyL10n.last_7]     = [moment().subtract(7, 'days'), moment()];
        picker_ranges[BooklyL10n.last_30]    = [moment().subtract(30, 'days'), moment()];
        picker_ranges[BooklyL10n.this_month] = [moment().startOf('month'), moment().endOf('month')];
        picker_ranges[BooklyL10n.last_month] = [moment().subtract(1, 'month').startOf('month'), moment().subtract(1, 'month').endOf('month')];

        $('.selectpicker').selectpicker({style: 'btn-info', size: 16});

        function ajaxData(object) {
            data['provider'] = $('#ab-provider-filter').val();
            data['service']  = $('#ab-service-filter').val();
            data['range']    = $report_range.data('date'); //text();
            data['type']     = $('#ab-type-filter').val();
            data['key']      = $('#search_customers').val();

            if ( object ) {
                var $parent = $(object).parent();
                data['order_by'] = $parent.attr('order-by');
                if ($parent.hasClass('active')) {
                    data['sort_order'] = $parent.hasClass('desc') ? 'asc' : 'desc';
                } else {
                    data['sort_order'] = 'asc';
                }
                $('#ab_payments_list th.active').removeClass('active asc desc');
                $parent.addClass('active ' + data['sort_order']);
            }

            return data;
        }

        // sort order
        $('#ab_payments_list th a').on('click', function() {
            var data = { action:'ab_sort_payments', data: ajaxData(this) };
            $('.loading-indicator').show();
            $('#ab_payments_list tbody').load(ajaxurl, data, function() {$('.loading-indicator').hide();});
        });

        $('#reportrange').daterangepicker(
            {
                startDate: moment().subtract(30, 'days'), // by default selected is "Last 30 days"
                ranges: picker_ranges,
                locale: {
                    applyLabel: BooklyL10n.apply,
                    cancelLabel: BooklyL10n.cancel,
                    fromLabel: BooklyL10n.from,
                    toLabel: BooklyL10n.to,
                    customRangeLabel: BooklyL10n.custom_range,
                    daysOfWeek: BooklyL10n.days,
                    monthNames: BooklyL10n.months,
                    firstDay: parseInt(BooklyL10n.startOfWeek),
                    format: BooklyL10n.mjsDateFormat
                }
            },
            function(start, end) {
                var format = 'YYYY-MM-DD';
                $report_range
                    .data('date', start.format(format) + ' - ' + end.format(format))
                    .html(start.format(BooklyL10n.mjsDateFormat) + ' - ' + end.format(BooklyL10n.mjsDateFormat));
            }
        );

        $('#ab-filter-submit').on('click', function() {
            var data = { action: 'ab_filter_payments', data: ajaxData() };
            $('.loading-indicator').show();
            $('#ab_payments_list tbody').load(ajaxurl, data, function(res) {
                $('#ab_filter_error').css('display', res.length ? 'none':'block');
                $('.loading-indicator').hide();
            });
        });
    });
</script>