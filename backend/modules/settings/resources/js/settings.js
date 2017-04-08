jQuery(function ($) {
    var $form                = $('#business-hours'),
        $final_step_url      = $('input[name=ab_settings_final_step_url]'),
        $final_step_url_mode = $('#ab_settings_final_step_url_mode'),
        $cancel_denied_url   = $('#ab_settings_cancel_denied_page_url_row'),
        $minimum_time_prior_cancel = $('#ab_settings_minimum_time_prior_cancel')
        ;

    Ladda.bind( 'button[type=submit]' );

    $('.select_start', $form).on('change', function () {
        var $row = $(this).parent(),
            $end_select = $('.select_end', $row),
            $start_select = $(this);

        if ($start_select.val()) {
            $end_select.show();
            $('span', $row).show();

            var start_time = $start_select.val();

            $('span > option', $end_select).each(function () {
                $(this).unwrap();
            });

            // Hides end time options with value less than in the start time
            $('option', $end_select).each(function () {
                if ($(this).val() <= start_time) {
                    $(this).wrap('<span>').parent().hide();
                }
            });

            if (start_time >= $end_select.val()) {
                $('option:visible:first', $end_select).attr('selected', true);
            }
        } else { // OFF
            $end_select.hide();
            $('span', $row).hide();
        }
    }).each(function () {
        var $row = $(this).parent(),
            $end_select = $('.select_end', $row);

        $(this).data('default_value', $(this).val());
        $end_select.data('default_value', $end_select.val());

        // Hides end select for "OFF" days
        if (!$(this).val()) {
            $end_select.hide();
            $('span', $row).hide();
        }
    }).trigger('change');

    // Reset.
    $('#ab-hours-reset', $form).on('click', function ( e ) {
        e.preventDefault();
        $('.select_start', $form).each(function () {
            $(this).val($(this).data('default_value'));
            $(this).trigger('click');
        });

        $('.select_end', $form).each(function () {
            $(this).val($(this).data('default_value'));
        });

        $('.select_start', $form).trigger('change');
    });

    // Customers Tab
    var $default_country      = $('#ab_settings_phone_default_country'),
        $default_country_code = $('#ab_sms_default_country_code');

    $.each($.fn.intlTelInput.getCountryData(), function (index, value) {
        $default_country.append('<option value="' + value.iso2 + '" data-code="' + value.dialCode + '">' + value.name + ' +' + value.dialCode + '</option>');
    });
    $default_country.val($default_country.data('country'));

    $default_country.on('change', function () {
        $default_country_code.val($default_country.find('option:selected').data('code'));
    });

    // Company Tab
    $('#ab-delete-logo').on('click', function () {
        $('#ab-show-logo').hide(300).append('<input type="hidden" id="ab-remove-logo" name="ab_remove_logo" value="1" />');
    });
    $('#ab_settings_company_logo').on('change', function() { $('#ab-remove-logo').remove(); });

    $('#ab-settings-company-reset').on('click', function () {
        $('#ab-remove-logo').remove();
        $('#ab-show-logo').show(300);
    });

    // Payment Tab
    $('#ab_paypal_type').change(function () {
        $('.ab-paypal-ec').toggle(this.value != 'disabled');
    }).change();

    $('#ab_authorizenet_type').change(function () {
        $('.authorizenet').toggle(this.value != 'disabled');
    }).change();

    $('#ab_stripe').change(function () {
        $('.ab-stripe').toggle(this.value == 1);
    }).change();

    $('#ab_2checkout').change(function () {
        $('.ab-2checkout').toggle(this.value != 'disabled');
    }).change();

    $('#ab_payulatam').change(function () {
        $('.ab-payulatam').toggle(this.value != 'disabled');
    }).change();

    $('#ab_payson').change(function () {
        $('.ab-payson').toggle(this.value != 'disabled');
    }).change();

    $('#ab_mollie').change(function () {
        $('.ab-mollie').toggle(this.value != 'disabled');
    }).change();

    $('#ab-payments-reset').on('click', function (event) {
        setTimeout(function () {
            $('#ab_paypal_type,#ab_authorizenet_type,#ab_stripe,#ab_2checkout,#ab_payulatam,#ab_payson,#ab_mollie').change();
        }, 50);
    });

    $('#ab-customer-reset').on('click', function (event) {
        $default_country.val($default_country.data('country'));
    });

    if ($final_step_url.val()) { $final_step_url_mode.val(1); }
    $final_step_url_mode.change(function () {
        if (this.value == 0) {
            $final_step_url.hide().val('');
        } else {
            $final_step_url.show();
        }
    });

    $cancel_denied_url.toggle( $minimum_time_prior_cancel.val() > 0 );
    $minimum_time_prior_cancel.change(function () {
        $cancel_denied_url.toggle(this.value > 0)
    });

    $('#settings_tabs a[href="#ab_settings_' + BooklyL10n.current_tab + '"]').tab('show');

});