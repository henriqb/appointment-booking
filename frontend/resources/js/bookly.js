(function($) {
    window.bookly = function(options) {
        var $container = $('#ab-booking-form-' + options.form_id);
        var today      = new Date();
        var Options    = $.extend(options, {
            skip_service   : options.skip_service_step ? true : ( options.attributes.hide_categories
                && options.attributes.hide_services
                && options.attributes.service_id
                && options.attributes.hide_staff_members
                && !options.attributes.show_number_of_persons
            ),
            skip_date_time : options.skip_service_step ? true : ( options.attributes.hide_date
                && options.attributes.hide_week_days
                && options.attributes.hide_time_range
            )
        });

        // initialize
        if (Options.status.booking == 'finished') {
            stepComplete();
        } else if (Options.status.booking == 'cancelled') {
            stepPayment(Options.status.cart_key);
        } else {
            stepService();
        }

        /**
         * Service step.
         */
        function stepService(cart_key) {
            if (Options.skip_steps.service) {
                if (!Options.skip_steps.extras) {
                    stepExtras(cart_key)
                } else {
                    stepTime(undefined, cart_key);
                }
                return;
            }
            $.ajax({
                url         : Options.ajaxurl,
                data        : { action: 'ab_render_service', form_id: Options.form_id, time_zone_offset: today.getTimezoneOffset(), cart_key: cart_key },
                dataType    : 'json',
                xhrFields   : { withCredentials: true },
                crossDomain : 'withCredentials' in new XMLHttpRequest(),
                success     : function (response) {
                    if (response.success) {
                        $container.html(response.html);
                        cart_key = response.cart_key;

                        var $select_category  = $('.ab-select-category', $container),
                            $select_service   = $('.ab-select-service', $container),
                            $select_nop       = $('.ab-select-number-of-persons', $container),
                            $select_staff     = $('.ab-select-employee', $container),
                            $date_from        = $('.ab-date-from', $container),
                            $week_day         = $('.ab-week-day', $container),
                            $select_time_from = $('.ab-select-time-from', $container),
                            $select_time_to   = $('.ab-select-time-to', $container),
                            $service_error    = $('.ab-select-service-error', $container),
                            $time_error       = $('.ab-select-time-error', $container),
                            $next_step        = $('.ab-next-step', $container),
                            $mobile_next_step = $('.ab-mobile-next-step', $container),
                            $mobile_prev_step = $('.ab-mobile-prev-step', $container),
                            categories        = response.categories,
                            services          = response.services,
                            staff             = response.staff,
                            category_id       = Options.attributes.category_id,
                            staff_member_id   = Options.attributes.staff_member_id,
                            service_id        = Options.attributes.service_id,
                            number_of_persons = Options.attributes.number_of_persons
                        ;
                        // Overwrite attributes if necessary.
                        if (response.attributes) {
                            if (!Options.attributes.hide_categories && Options.attributes.service_id != response.attributes.service_id) {
                                category_id = null;
                            }
                            service_id = response.attributes.service_id;
                            if (!Options.attributes.hide_staff_members) {
                                staff_member_id = response.attributes.staff_member_id;
                            }
                            number_of_persons = response.attributes.number_of_persons;
                        }

                        // Init Pickadate.
                        $date_from.pickadate({
                            formatSubmit    : 'yyyy-mm-dd',
                            format          : Options.date_format,
                            min             : response.date_min || true,
                            max             : response.date_max || true,
                            clear           : false,
                            close           : false,
                            today           : BooklyL10n.today,
                            monthsFull      : BooklyL10n.months,
                            weekdaysFull    : BooklyL10n.days,
                            weekdaysShort   : BooklyL10n.daysShort,
                            labelMonthNext  : BooklyL10n.nextMonth,
                            labelMonthPrev  : BooklyL10n.prevMonth,
                            firstDay        : Options.start_of_week,
                            onSet           : function(timestamp) {
                                if ($.isNumeric(timestamp.select)) {
                                    // Checks appropriate day of the week
                                    var date = new Date(timestamp.select);
                                    $('.ab-week-day[value="' + (date.getDay() + 1) + '"]:not(:checked)', $container).attr('checked', true).trigger('change');
                                }
                            }
                        });

                        $('.ab-goto-cart', $container).on('click', function(e) {
                            e.preventDefault();
                            ladda_start(this);
                            stepCart(cart_key);
                        });
                        function setSelectNumberOfPersons() {
                            var service_id = $select_service.val();
                            if (service_id) {
                                var staff_id = $select_staff.val();
                                var number_of_persons = $select_nop.val();
                                var max_capacity = staff_id ? staff[staff_id].services[service_id].max_capacity : services[service_id].max_capacity;
                                $select_nop.empty();
                                for (var i = 1; i <= max_capacity; ++ i) {
                                    $select_nop.append('<option value="' + i +'">' + i + '</option>');
                                }
                                if (number_of_persons <= max_capacity) {
                                    $select_nop.val(number_of_persons);
                                }
                            } else {
                                $select_nop.empty().append('<option value="1">1</option>');
                            }
                        }

                        // fill the selects
                        setSelect($select_category, categories);
                        setSelect($select_service, services);
                        setSelect($select_staff, staff);

                        // Category select change
                        $select_category.on('change', function() {
                            var category_id = this.value;

                            // filter the services and staff
                            // if service or staff is selected, leave it selected
                            if (category_id) {
                                setSelect($select_service, categories[category_id].services);
                                setSelect($select_staff, categories[category_id].staff, true);
                            // show all services and staff
                            // if service or staff is selected, reset it
                            } else {
                                setSelect($select_service, services);
                                setSelect($select_staff, staff);
                            }
                        });

                        // Service select change
                        $select_service.on('change', function() {
                            var service_id = this.value;

                            // select the category
                            // filter the staffs by service
                            // show staff with price
                            // if staff selected, leave it selected
                            // if staff not selected, select all
                            if (service_id) {
                                $select_category.val(services[service_id].category_id);
                                setSelect($select_staff, services[service_id].staff, true);
                            // filter staff by category
                            } else {
                                var category_id = $select_category.val();
                                if (category_id) {
                                    setSelect($select_staff, categories[category_id].staff, true);
                                } else {
                                    setSelect($select_staff, staff, true);
                                }

                            }
                            setSelectNumberOfPersons();
                        });

                        // Staff select change
                        $select_staff.on('change', function() {
                            var staff_id = this.value;
                            var category_id = $select_category.val();

                            // filter services by staff and category
                            // if service selected, leave it
                            if (staff_id) {
                                var services_a = {};
                                if (category_id) {
                                    $.each(staff[staff_id].services, function(index, st) {
                                        if (services[st.id].category_id == category_id) {
                                            services_a[st.id] = st;
                                        }
                                    });
                                } else {
                                    services_a = staff[staff_id].services;
                                }
                                setSelect($select_service, services_a, true);
                            // filter services by category
                            } else {
                                if (category_id) {
                                    setSelect($select_service, categories[category_id].services, true);
                                } else {
                                    setSelect($select_service, services, true);
                                }
                            }
                            setSelectNumberOfPersons();
                        });

                        // Category
                        if (category_id) {
                            $select_category.val(category_id).trigger('change');
                        }
                        // Services
                        if (service_id) {
                            $select_service.val(service_id).trigger('change');
                        }
                        // Employee
                        if (staff_member_id) {
                            $select_staff.val(staff_member_id).trigger('change');
                        }
                        // Number of persons
                        if (number_of_persons) {
                            $select_nop.val(number_of_persons);
                        }

                        hideByAttributes();

                        // change the week days
                        $week_day.on('change', function () {
                            var $this = $(this);
                            if ($this.is(':checked')) {
                                $this.parent().not("[class*='active']").addClass('active');
                            } else {
                                $this.parent().removeClass('active');
                            }
                        });

                        // time from
                        $select_time_from.on('change', function () {
                            var start_time       = $(this).val(),
                                end_time         = $select_time_to.val(),
                                $last_time_entry = $('option:last', $select_time_from);

                            $select_time_to.empty();

                            // case when we click on the not last time entry
                            if ($select_time_from[0].selectedIndex < $last_time_entry.index()) {
                                // clone and append all next "time_from" time entries to "time_to" list
                                $('option', this).each(function () {
                                    if ($(this).val() > start_time) {
                                        $select_time_to.append($(this).clone());
                                    }
                                });
                            // case when we click on the last time entry
                            } else {
                                $select_time_to.append($last_time_entry.clone()).val($last_time_entry.val());
                            }

                            var first_value = $('option:first', $select_time_to).val();
                            $select_time_to.val(end_time >= first_value ? end_time : first_value);
                        });

                        var stepServiceValidator = function(button_type) {
                            var valid           = true,
                                $select_wrap    = $select_service.parent(),
                                $time_wrap_from = $select_time_from.parent(),
                                $time_wrap_to   = $select_time_to.parent(),
                                $scroll_to      = null;

                            $service_error.hide();
                            $time_error.hide();
                            $select_wrap.removeClass('ab-error');
                            $time_wrap_from.removeClass('ab-error');
                            $time_wrap_to.removeClass('ab-error');

                            // service validation
                            if (!$select_service.val()) {
                                valid = false;
                                $select_wrap.addClass('ab-error');
                                $service_error.show();
                                $scroll_to = $select_wrap;
                            }

                            // date validation
                            $date_from.css('borderColor', $date_from.val() ? '' : 'red');
                            if (!$date_from.val()) {
                                valid = false;
                                if ($scroll_to === null) {
                                    $scroll_to = $date_from;
                                }
                            }

                            // time validation
                            if (button_type !== 'mobile' && $select_time_from.val() == $select_time_to.val()) {
                                valid = false;
                                $time_wrap_from.addClass('ab-error');
                                $time_wrap_to.addClass('ab-error');
                                $time_error.show();
                                if ($scroll_to === null) {
                                    $scroll_to = $time_wrap_from;
                                }
                            }

                            // week days
                            if (!$('.ab-week-day:checked', $container).length) {
                                valid = false;
                                if ($scroll_to === null) {
                                    $scroll_to = $week_day;
                                }
                            }

                            if ($scroll_to !== null) {
                                scrollTo($scroll_to);
                            }

                            return valid;
                        };

                        // "Next" click
                        $next_step.on('click', function (e) {
                            e.preventDefault();

                            if (stepServiceValidator('simple')) {

                                ladda_start(this);

                                // Prepare staff ids.
                                var staff_ids = [];
                                if ($select_staff.val()) {
                                    staff_ids.push($select_staff.val());
                                } else {
                                    $select_staff.find('option').each(function() {
                                        if (this.value) {
                                            staff_ids.push(this.value);
                                        }
                                    });
                                }
                                // Prepare days.
                                var days = [];
                                $('.ab-week-days .active input.ab-week-day', $container).each(function() {
                                    days.push(this.value);
                                });

                                $.ajax({
                                    url  : Options.ajaxurl,
                                    data : {
                                        action            : 'ab_session_save',
                                        form_id           : Options.form_id,
                                        service_id        : $select_service.val(),
                                        number_of_persons : $select_nop.val(),
                                        staff_ids         : staff_ids,
                                        date_from         : $date_from.pickadate('picker').get('select', 'yyyy-mm-dd'),
                                        days              : days,
                                        time_from         : $select_time_from.val(),
                                        time_to           : $select_time_to.val(),
                                        cart_key          : cart_key
                                    },
                                    dataType    : 'json',
                                    xhrFields   : { withCredentials: true },
                                    crossDomain : 'withCredentials' in new XMLHttpRequest(),
                                    success     : function (response) {
                                        if (!Options.skip_steps.extras) {
                                            stepExtras(cart_key);
                                        } else {
                                            stepTime(undefined, cart_key);
                                        }
                                    }
                                });
                            }
                        });

                        //
                        $mobile_next_step.on('click', function () {
                            if (stepServiceValidator('mobile')) {
                                if (Options.skip_date_time) {
                                    ladda_start(this);
                                    $next_step.trigger('click');
                                } else {
                                    $('.ab-mobile-step_1', $container).hide();
                                    $('.ab-mobile-step_2', $container).css('display', 'block');
                                    if (Options.skip_service) {
                                        $mobile_prev_step.remove();
                                    }
                                    scrollTo($container);
                                }
                            }

                            return false;
                        });

                        $mobile_prev_step.on('click', function () {
                            $('.ab-mobile-step_1', $container).show();
                            $('.ab-mobile-step_2', $container).hide();

                            if ($select_service.val()) {
                                $('.ab-select-service', $container).parent().removeClass('ab-error');
                            }
                            return false;
                        });

                        if (Options.skip_service) {
                            $mobile_next_step.trigger('click');
                        }
                    }
                } // ajax success
            }); // ajax
        }

        /**
         * Extras step.
         */
        function stepExtras(cart_key) {
            $.ajax({
                url: Options.ajaxurl,
                data: {action: 'ab_render_extras', form_id: Options.form_id, time_zone_offset: today.getTimezoneOffset(), cart_key: cart_key},
                dataType: 'json',
                xhrFields: {withCredentials: true},
                crossDomain: 'withCredentials' in new XMLHttpRequest(),
                success: function (response) {
                    if (response.success) {
                        cart_key = response.cart_key;
                        $container.html(response.html);
                        var $next_step = $('.ab-next-step', $container),
                            $back_step = $('.ab-back-step', $container),
                            $goto_cart_step = $('.ab-goto-cart', $container),
                            $ab_extra = $('.ab-extra', $container),
                            $extras_summary = $('.ab-summary span', $container),
                            money = response.money;

                        $ab_extra.on('click', function () {
                            var $ch = $(this).find('[type=checkbox]'),
                                amount = 0;
                            $ch.prop('checked', !$ch.prop('checked'));
                            $(':checkbox:checked', $container).each(function () {
                                amount += parseFloat($(this).data('price'));
                            });
                            if (amount) {
                                $extras_summary.html(' + ' + money.format.replace('1', amount.toFixed(money.precision)));
                            } else {
                                $extras_summary.html('');
                            }
                        });

                        $goto_cart_step.on('click', function (e) {
                            e.preventDefault();
                            ladda_start(this);
                            stepCart(cart_key);
                        });
                        $next_step.on('click', function (e) {
                            e.preventDefault();
                            ladda_start(this);
                            var values = [];

                            $(':checkbox:checked', $container).each(function () {
                                values.push($(this).val());
                            });

                            $.ajax({
                                type: 'POST',
                                url: Options.ajaxurl,
                                data: {
                                    action: 'ab_session_save',
                                    form_id: Options.form_id,
                                    extras: values.length ? values : null,
                                    cart_key: cart_key
                                },
                                dataType: 'json',
                                xhrFields: {withCredentials: true},
                                crossDomain: 'withCredentials' in new XMLHttpRequest(),
                                success: function (response) {
                                    stepTime(undefined, cart_key);
                                }
                            });
                        });
                        $back_step.on('click', function (e) {
                            e.preventDefault();
                            ladda_start(this);
                            stepService(cart_key);
                        }).toggle(!Options.skip_steps.service);
                    }
                }
            });
        }

        /**
         * Time step.
         */
        var xhr_render_time = null;
        function stepTime(selected_date, cart_key, time_busy_message) {
            if (xhr_render_time != null) {
                xhr_render_time.abort();
                xhr_render_time = null;
            }
            var data = { action: 'ab_render_time', form_id: Options.form_id, selected_date: selected_date, cart_key: cart_key };
            if (typeof cart_key == 'undefined') {
                // If cart_key is undefined then Service step is skipped and we need to send time zone offset.
                data.time_zone_offset = today.getTimezoneOffset();
            }
            xhr_render_time = $.ajax({
                url         : Options.ajaxurl,
                data        : data,
                dataType    : 'json',
                xhrFields   : { withCredentials: true },
                crossDomain : 'withCredentials' in new XMLHttpRequest(),
                success     : function (response) {
                    if (response.success == false) {
                        // The session doesn't contain data.
                        stepService();
                        return;
                    }
                    $container.html(response.html);
                    // The cart_key can be created at this step if Service step is skipped.
                    cart_key = response.cart_key;

                    var $columnizer_wrap  = $('.ab-columnizer-wrap', $container),
                        $columnizer       = $('.ab-columnizer', $container),
                        $time_next_button = $('.ab-time-next',  $container),
                        $time_prev_button = $('.ab-time-prev',  $container),
                        $current_screen   = null,
                        slot_height       = 35,
                        column_width      = 127,
                        calendar_width    = 310,
                        columns           = 0,
                        screen_index      = 0,
                        has_more_slots    = response.has_more_slots,
                        $screens,
                        slots_per_column,
                        columns_per_screen,
                        show_day_per_column = response.day_one_column
                    ;
                    // 'BACK' button.
                    $('.ab-back-step', $container).on('click', function (e) {
                        e.preventDefault();
                        ladda_start(this);
                        if (!Options.skip_steps.extras) {
                            stepExtras(cart_key);
                        } else {
                            stepService(cart_key);
                        }
                    }).toggle(!Options.skip_steps.service || !Options.skip_steps.extras);

                    $('.ab-goto-cart', $container).on('click', function(e) {
                        e.preventDefault();
                        ladda_start(this);
                        stepCart(cart_key);
                    });
                    if (Options.show_calendar) {
                        // Init calendar.
                        var $input = $('.ab-selected-date', $container);
                        $input.pickadate({
                            formatSubmit  : 'yyyy-mm-dd',
                            format        : Options.date_format,
                            min           : response.date_min || true,
                            max           : response.date_max || true,
                            weekdaysFull  : BooklyL10n.days,
                            weekdaysShort : BooklyL10n.daysShort,
                            monthsFull    : BooklyL10n.months,
                            firstDay      : Options.start_of_week,
                            clear         : false,
                            close         : false,
                            today         : false,
                            disable       : response.disabled_days,
                            closeOnSelect : false,
                            klass : {
                                picker: 'picker picker--opened picker--focused'
                            },
                            onSet: function(e) {
                                if (e.select) {
                                    var selected_date = this.get('select', 'yyyy-mm-dd');
                                    if (response.slots[selected_date]) {
                                        // Get data from response.slots.
                                        $columnizer.html(response.slots[selected_date]).css('left', '0px');
                                        columns = 0;
                                        screen_index = 0;
                                        $current_screen = null;
                                        initSlots();
                                        $time_prev_button.hide();
                                        $time_next_button.toggle($screens.length != 1);
                                    } else {
                                        // Load new data from server.
                                        stepTime(selected_date, cart_key);
                                        showSpinner();
                                    }
                                }
                                this.open();   // Fix ultimate-member plugin
                            },
                            onClose: function() {
                                this.open(false);
                            },
                            onRender: function() {
                                var selected_date = new Date(Date.UTC(this.get('view').year, this.get('view').month));
                                $('.picker__nav--next').on('click', function() {
                                    selected_date.setUTCMonth(selected_date.getUTCMonth() + 1);
                                    stepTime(selected_date.toJSON().substr(0, 10), cart_key);
                                    showSpinner();
                                });
                                $('.picker__nav--prev').on('click', function() {
                                    selected_date.setUTCMonth(selected_date.getUTCMonth() - 1);
                                    stepTime(selected_date.toJSON().substr(0, 10), cart_key);
                                    showSpinner();
                                });
                            }
                        });
                        // Insert slots for selected day.
                        var selected_date = $input.pickadate('picker').get('select', 'yyyy-mm-dd');
                        $columnizer.html(response.slots[selected_date]);
                    } else {
                        // Insert all slots.
                        var slots = '';
                        $.each(response.slots, function(group, group_slots) {
                            slots += group_slots;
                        });
                        $container.find('.ab-columnizer').html(slots);
                    }

                    if (response.has_slots) {
                        if (time_busy_message) {
                            $container.find('.ab--holder.ab-label-error').html(time_busy_message);
                        } else {
                            $container.find('.ab--holder.ab-label-error').hide();
                        }

                        // Calculate number of slots per column.
                        slots_per_column = parseInt($(window).height() / slot_height, 10);
                        if (slots_per_column < 4) {
                            slots_per_column = 4;
                        } else if (slots_per_column > 10) {
                            slots_per_column = 10;
                        }
                        // Calculate number of columns per screen.
                        columns_per_screen = parseInt(( $container.width() / column_width ), 10);
                        if (Options.show_calendar && ( $container.width() - calendar_width >= column_width )) {
                            // slots right
                            columns_per_screen = parseInt(( ( $container.width() - calendar_width ) / column_width ), 10);
                        } // else slots bottom sub calendar

                        if (columns_per_screen > 10) {
                            columns_per_screen = 10;
                        }

                        initSlots();

                        if (!has_more_slots && $screens.length == 1) {
                            $time_next_button.hide();
                        }

                        var hammertime = $('.ab-time-step', $container).hammer({ swipe_velocity: 0.1 });

                        hammertime.on('swipeleft', function() {
                            if ($time_next_button.is(':visible')) {
                                $time_next_button.trigger('click');
                            }
                        });

                        hammertime.on('swiperight', function() {
                            if ($time_prev_button.is(':visible')) {
                                $time_prev_button.trigger('click');
                            }
                        });

                        $time_next_button.on('click', function (e) {
                            $time_prev_button.show();
                            if ($screens.eq(screen_index + 1).length) {
                                $columnizer.animate(
                                    { left: '-=' + $current_screen.width() },
                                    { duration: 800 }
                                );
                                $current_screen = $screens.eq(++ screen_index);
                                $columnizer_wrap.animate(
                                    { height: $current_screen.height() },
                                    { duration: 800 }
                                );

                                if (screen_index + 1 == $screens.length && !has_more_slots) {
                                    $time_next_button.hide();
                                }
                            } else if (has_more_slots) {
                                // Do ajax request when there are more slots.
                                var $button = $('> button:last', $columnizer);
                                if ($button.length == 0) {
                                    $button = $('.ab-column:hidden:last > button:last', $columnizer);
                                    if ($button.length == 0) {
                                        $button = $('.ab-column:last > button:last', $columnizer);
                                    }
                                }

                                // Render Next Time
                                var data = {
                                        action: 'ab_render_next_time',
                                        form_id: options.form_id,
                                        last_slot: $button.val(),
                                        cart_key: cart_key
                                    },
                                    ladda = ladda_start(document.querySelector('.ab-time-next'));

                                $.ajax({
                                    type : 'POST',
                                    url  : options.ajaxurl,
                                    data : data,
                                    dataType : 'json',
                                    xhrFields : { withCredentials: true },
                                    crossDomain : 'withCredentials' in new XMLHttpRequest(),
                                    success : function (response) {
                                        if (response.success) {
                                            if (response.has_slots) { // if there are available time
                                                has_more_slots = response.has_more_slots;
                                                var $html = $(response.html);
                                                // The first slot is always a day slot.
                                                // Check if such day slot already exists (this can happen
                                                // because of time zone offset) and then remove the first slot.
                                                var $first_day = $html.eq(0);
                                                if ($('button.ab-available-day[value="' + $first_day.attr('value') + '"]', $container).length) {
                                                    $html = $html.not(':first');
                                                }
                                                $columnizer.append($html);
                                                initSlots();
                                                $time_next_button.trigger('click');
                                            } else { // no available time
                                                $time_next_button.hide();
                                            }
                                        } else { // no available time
                                            $time_next_button.hide();
                                        }
                                        ladda.stop();
                                    }
                                });
                            }
                        });

                        $time_prev_button.on('click', function () {
                            $time_next_button.show();
                            $current_screen = $screens.eq(-- screen_index);
                            $columnizer.animate(
                                { left: '+=' + $current_screen.width() },
                                { duration: 800 }
                            );
                            $columnizer_wrap.animate(
                                { height: $current_screen.height() },
                                { duration: 800 }
                            );
                            if (screen_index === 0) {
                                $time_prev_button.hide();
                            }
                        });
                    }
                    // skip scroll when first step is hidden
                    if (!Options.skip_service_step) {
                        scrollTo($container);
                    }

                    function showSpinner() {
                        $('.ab-time-screen,.ab-not-time-screen', $container).addClass('ab-spin-overlay');
                        var opts = {
                            lines: 11, // The number of lines to draw
                            length: 11, // The length of each line
                            width: 4, // The line thickness
                            radius: 5 // The radius of the inner circle
                        };
                        new Spinner(opts).spin($screens.eq(screen_index).get(0));
                    }

                    function initSlots() {
                        var $buttons     = $('> button', $columnizer),
                            slots_count  = 0,
                            max_slots    = 0,
                            $button,
                            $column,
                            $screen;

                        if (show_day_per_column) {
                            /**
                             * Create columns for 'Show each day in one column' mode.
                             */
                            while ($buttons.length > 0) {
                                // Create column.
                                if ($buttons.eq(0).hasClass('ab-available-day')) {
                                    slots_count = 1;
                                    $column = $('<div class="ab-column" />');
                                    $button = $($buttons.splice(0, 1));
                                    $button.addClass('ab-first-child');
                                    $column.append($button);
                                } else {
                                    slots_count ++;
                                    $button = $($buttons.splice(0, 1));
                                    // If it is last slot in the column.
                                    if (!$buttons.length || $buttons.eq(0).hasClass('ab-available-day')) {
                                        $button.addClass('ab-last-child');
                                        $column.append($button);
                                        $columnizer.append($column);
                                    } else {
                                        $column.append($button);
                                    }
                                }
                                // Calculate max number of slots.
                                if (slots_count > max_slots) {
                                    max_slots = slots_count;
                                }
                            }
                        } else {
                            /**
                             * Create columns for normal mode.
                             */
                            while (has_more_slots ? $buttons.length > slots_per_column : $buttons.length) {
                                $column = $('<div class="ab-column" />');
                                max_slots = slots_per_column;
                                if (columns % columns_per_screen == 0 && !$buttons.eq(0).hasClass('ab-available-day')) {
                                    // If this is the first column of a screen and the first slot in this column is not day
                                    // then put 1 slot less in this column because createScreens adds 1 more
                                    // slot to such columns.
                                    -- max_slots;
                                }
                                for (var i = 0; i < max_slots; ++ i) {
                                    if (i + 1 == max_slots && $buttons.eq(0).hasClass('ab-available-day')) {
                                        // Skip the last slot if it is day.
                                        break;
                                    }
                                    $button = $($buttons.splice(0, 1));
                                    if (i == 0) {
                                        $button.addClass('ab-first-child');
                                    } else if (i + 1 == max_slots) {
                                        $button.addClass('ab-last-child');
                                    }
                                    $column.append($button);
                                }
                                $columnizer.append($column);
                                ++ columns;
                            }
                        }
                        if (response.selected_slot) {
                            $columnizer.find('button[value="' + response.selected_slot + '"]').addClass('ab-bold');
                        }
                        /**
                         * Create screens.
                         */
                        var $columns = $('> .ab-column', $columnizer),
                            cols_per_screen = $columns.length < columns_per_screen ? $columns.length : columns_per_screen;

                        while (has_more_slots ? $columns.length >= cols_per_screen : $columns.length) {
                            $screen = $('<div class="ab-time-screen"/>');
                            for (var i = 0; i < cols_per_screen; ++i) {
                                $column = $($columns.splice(0, 1));
                                if (i == 0) {
                                    $column.addClass('ab-first-column');
                                    var $first_slot = $column.find('.ab-first-child');
                                    // In the first column the first slot is time.
                                    if (!$first_slot.hasClass('ab-available-day')) {
                                        var group = $first_slot.data('group'),
                                            $group_slot = $('button.ab-available-day[value="' + group + '"]:last', $container);
                                        // Copy group slot to the first column.
                                        $column.prepend($group_slot.clone());
                                    }
                                }
                                $screen.append($column);
                            }
                            $columnizer.append($screen);
                        }
                        $screens = $('.ab-time-screen', $columnizer);
                        if ($current_screen === null) {
                            $current_screen = $screens.eq(0);
                        }

                        // On click on a slot.
                        $('button.ab-available-hour', $container).off('click').on('click', function (e) {
                            e.preventDefault();
                            var $this = $(this),
                                data = {
                                    action: 'ab_session_save',
                                    appointment_datetime: $this.val(),
                                    staff_id: $this.data('staff_id'),
                                    form_id: options.form_id,
                                    cart_key: cart_key
                                };

                            ladda_start(this);
                            $.ajax({
                                type : 'POST',
                                url  : options.ajaxurl,
                                data : data,
                                dataType  : 'json',
                                xhrFields : { withCredentials: true },
                                crossDomain : 'withCredentials' in new XMLHttpRequest(),
                                success : function (response) {
                                    if (Options.cart.enabled && !Options.cart.after_details) {
                                        stepCart(cart_key);
                                    } else {
                                        stepDetails(cart_key);
                                    }
                                }
                            });
                        });

                        // Columnizer width & height.
                        $('.ab-time-step', $container).width(cols_per_screen * column_width);
                        $columnizer_wrap.height($current_screen.height());
                    }
                }
            });
        }

        /**
         * Cart step.
         */
        function stepCart(cart_key, message) {
            if (!Options.cart.enabled) {
                stepDetails(cart_key);
            } else {
                $.ajax({
                    url: Options.ajaxurl,
                    data: {action: 'ab_render_cart', form_id: Options.form_id, cart_key: cart_key},
                    dataType: 'json',
                    xhrFields: {withCredentials: true},
                    crossDomain: 'withCredentials' in new XMLHttpRequest(),
                    success: function (response) {
                        if (response.success) {
                            $container.html(response.html);
                            if(message){
                                $('.ab--holder.ab-label-error', $container).html(message);
                                $('tr[data-cart-key="'+ cart_key +'"]', $container).addClass('ab-error');
                            } else {
                                $('.ab--holder.ab-label-error', $container).hide();
                            }
                            scrollTo($container);
                            var $appointment_action = $('.ab--actions');
                            $('.ab-next-step', $container).on('click', function () {
                                ladda_start(this);
                                if (Options.cart.after_details) {
                                    stepPayment(cart_key);
                                } else {
                                    stepDetails(cart_key);
                                }
                            });
                            $('.ab-add-item', $container).on('click', function () {
                                ladda_start(this);
                                stepService();
                            });
                            // 'BACK' button.
                            $('.ab-back-step', $container).on('click', function (e) {
                                e.preventDefault();
                                ladda_start(this);
                                if (Options.cart.after_details) {
                                    stepDetails(cart_key);
                                } else {
                                    stepTime(undefined, cart_key);
                                }
                            });
                            // We back from payments.
                            if (cart_key === undefined) {
                                var $last_cart_item = $('tbody.ab-desktop-version tr:last-child', $container);
                                if ($last_cart_item.length == 1) {
                                    cart_key = $last_cart_item.data('cart-key');
                                }
                            }

                            $appointment_action.on('click', function () {
                                ladda_start(this);
                                var $me = $(this),
                                    $appointment = $(this).parents('tr');
                                switch ($me.data('action')) {
                                    case 'drop':
                                        $.post(Options.ajaxurl, {action: 'ab_cart_drop_appointment', form_id: Options.form_id, cart_key: $appointment.data('cart-key')})
                                            .done(function (response) {
                                                if (response.success) {
                                                    var remove_cart_key = $appointment.data('cart-key'),
                                                        $appointment_tr = $('tr[data-cart-key="'+remove_cart_key+'"]')
                                                    ;
                                                    $appointment_tr.delay(300).fadeOut(200, function () {
                                                        $('.ab-total-price', $container).html(response.data.total_price);
                                                        $appointment_tr.remove();
                                                        var $last_cart_item = $('tbody.ab-desktop-version tr:last-child', $container);
                                                        if ($last_cart_item.length == 1) {
                                                            cart_key = $last_cart_item.data('cart-key');
                                                            $('.ab-cart-step', $container).data(cart_key);
                                                        } else {
                                                            $('.ab-back-step', $container).hide();
                                                            $('.ab-next-step', $container).hide();
                                                        }
                                                    });
                                                }
                                            }, 'json');
                                        break;
                                    case 'edit':
                                        stepService($appointment.data('cart-key'));
                                        break;
                                }
                            });
                        }
                    }
                });
            }
        }

        /**
         * Details step.
         */
        function stepDetails(cart_key) {
            $.ajax({
                url         : Options.ajaxurl,
                data        : { action: 'ab_render_details', form_id: Options.form_id, cart_key: cart_key },
                dataType    : 'json',
                xhrFields   : { withCredentials: true },
                crossDomain : 'withCredentials' in new XMLHttpRequest(),
                success     : function (response) {
                    if (response.success) {
                        $container.html(response.html);
                        scrollTo($container);
                        // Init
                        var $phone_field  = $('.ab-user-phone-input', $container),
                            $email_field  = $('.ab-user-email',       $container),
                            $name_field   = $('.ab-full-name',        $container),
                            $phone_error  = $('.ab-user-phone-error', $container),
                            $email_error  = $('.ab-user-email-error', $container),
                            $name_error   = $('.ab-full-name-error',  $container),
                            $captcha      = $('.ab-captcha-img',      $container),
                            $errors       = $('.ab-user-phone-error, .ab-user-email-error, .ab-full-name-error, div.ab-custom-field-error', $container),
                            $fields       = $('.ab-user-phone-input, .ab-user-email, .ab-full-name, .ab-custom-field', $container),
                            phone_number  = ''
                        ;
                        if (Options.intlTelInput.enabled) {
                            $phone_field.intlTelInput({
                                preferredCountries: [Options.intlTelInput.country],
                                defaultCountry: Options.intlTelInput.country,
                                geoIpLookup: function (callback) {
                                    $.get(Options.ajaxurl, {action: 'ab_ip_info'}, function () {
                                    }, 'json').always(function (resp) {
                                        var countryCode = (resp && resp.country) ? resp.country : '';
                                        callback(countryCode);
                                    });
                                },
                                utilsScript: Options.intlTelInput.utils
                            });
                        }
                        $('.ab-next-step', $container).on('click', function(e) {
                            e.preventDefault();
                            var custom_fields_data = [],
                                checkbox_values,
                                captcha_id = '',
                                ladda = ladda_start(this)
                            ;
                            $('div.ab-custom-field-row', $container).each(function() {
                                var $this = $(this);
                                switch ($this.data('type')) {
                                    case 'text-field':
                                        custom_fields_data.push({
                                            id      : $this.data('id'),
                                            value   : $this.find('input.ab-custom-field').val()
                                        });
                                        break;
                                    case 'textarea':
                                        custom_fields_data.push({
                                            id      : $this.data('id'),
                                            value   : $this.find('textarea.ab-custom-field').val()
                                        });
                                        break;
                                    case 'checkboxes':
                                        checkbox_values = [];
                                        $this.find('input.ab-custom-field:checked').each(function () {
                                            checkbox_values.push(this.value);
                                        });
                                        custom_fields_data.push({
                                            id      : $this.data('id'),
                                            value   : checkbox_values
                                        });
                                        break;
                                    case 'radio-buttons':
                                        custom_fields_data.push({
                                            id      : $this.data('id'),
                                            value   : $this.find('input.ab-custom-field:checked').val() || null
                                        });
                                        break;
                                    case 'drop-down':
                                        custom_fields_data.push({
                                            id      : $this.data('id'),
                                            value   : $this.find('select.ab-custom-field').val()
                                        });
                                        break;
                                    case 'captcha':
                                        custom_fields_data.push({
                                            id      : $this.data('id'),
                                            value   : $this.find('input.ab-custom-field').val()
                                        });
                                        captcha_id = $this.data('id');
                                        break;
                                }
                            });
                            try {
                                phone_number = Options.intlTelInput.enabled ? $phone_field.intlTelInput('getNumber') : $phone_field.val();
                            } catch (error) {  // In case when intlTelInput can't return phone number.
                                phone_number = $phone_field.val();
                            }
                            var data = {
                                    action        : 'ab_session_save',
                                    form_id       : Options.form_id,
                                    name          : $name_field.val(),
                                    cart_key      : cart_key,
                                    phone         : phone_number,
                                    email         : $email_field.val(),
                                    custom_fields : JSON.stringify(custom_fields_data),
                                    captcha_id    : captcha_id
                                };

                            $.ajax({
                                type        : 'POST',
                                url         : Options.ajaxurl,
                                data        : data,
                                dataType    : 'json',
                                xhrFields   : { withCredentials: true },
                                crossDomain : 'withCredentials' in new XMLHttpRequest(),
                                success     : function (response) {
                                    // Error messages
                                    $errors.empty();
                                    $fields.removeClass('ab-field-error');

                                    if (response.length == 0) {
                                        if (Options.woocommerce.enabled) {
                                            var data = {
                                                action  : 'ab_add_to_woocommerce_cart',
                                                form_id : Options.form_id
                                            };
                                            $.ajax({
                                                type        : 'POST',
                                                url         : Options.ajaxurl,
                                                data        : data,
                                                dataType    : 'json',
                                                xhrFields   : { withCredentials: true },
                                                crossDomain : 'withCredentials' in new XMLHttpRequest(),
                                                success     : function (response) {
                                                    if (response.success) {
                                                        window.location.href = Options.woocommerce.cart_url;
                                                    } else {
                                                        ladda.stop();
                                                        stepTime(undefined, undefined, response.error);
                                                    }
                                                }
                                            });
                                        } else {
                                            if (Options.cart.enabled && Options.cart.after_details) {
                                                stepCart(cart_key);
                                            } else {
                                                stepPayment(cart_key);
                                            }
                                        }
                                    } else {
                                        ladda.stop();
                                        var $scroll_to = null;
                                        if (response.name) {
                                            $name_error.html(response.name);
                                            $name_field.addClass('ab-field-error');
                                            $scroll_to = $name_field;
                                        }
                                        if (response.phone) {
                                            $phone_error.html(response.phone);
                                            $phone_field.addClass('ab-field-error');
                                            if ($scroll_to === null) {
                                                $scroll_to = $phone_field;
                                            }
                                        }
                                        if (response.email) {
                                            $email_error.html(response.email);
                                            $email_field.addClass('ab-field-error');
                                            if ($scroll_to === null) {
                                                $scroll_to = $email_field;
                                            }
                                        }
                                        if (response.custom_fields) {
                                            $.each(response.custom_fields, function(key, value) {
                                                var $div = $('[data-id=' + key + ']', $container);
                                                $div.find('.ab-custom-field-error').html(value);
                                                $div.find('.ab-custom-field').addClass('ab-field-error');
                                                if ($scroll_to === null) {
                                                    $scroll_to = $div.find('.ab-custom-field');
                                                }
                                            });
                                        }
                                        if ($scroll_to !== null) {
                                            scrollTo($scroll_to);
                                        }
                                    }
                                }
                            });
                        });

                        $('.ab-back-step', $container).on('click', function (e) {
                            e.preventDefault();
                            ladda_start(this);
                            if (Options.cart.enabled && !Options.cart.after_details) {
                                stepCart(cart_key);
                            } else {
                                stepTime(undefined, cart_key);
                            }
                        });

                        $('.ab-goto-cart', $container).on('click', function(e) {
                            e.preventDefault();
                            ladda_start(this);
                            stepCart(cart_key);
                        });

                        $('.ab-captcha-refresh',  $container).on('click', function() {
                            $captcha.css('opacity','0.5');
                            $.get(Options.ajaxurl, {action: 'ab_captcha_refresh', form_id: Options.form_id}, function(response) {
                                if (response.success) {
                                    $captcha.attr('src', response.data.captcha_url).on('load', function() {
                                        $captcha.css('opacity', '1');
                                    });
                                }
                            }, 'json');
                        });
                    }
                }
            });
        }

        /**
         * Payment step.
         */
        function stepPayment(cart_key) {
            $.ajax({
                url        : Options.ajaxurl,
                data       : {action: 'ab_render_payment', form_id: Options.form_id},
                dataType   : 'json',
                xhrFields  : {withCredentials: true},
                crossDomain: 'withCredentials' in new XMLHttpRequest(),
                success    : function (response) {
                    if (response.success) {
                        // If payment step is disabled.
                        if (response.disabled) {
                            save();
                            return;
                        }

                        $container.html(response.html);
                        scrollTo($container);

                        if (Options.status.booking == 'cancelled') {
                            Options.status.booking = 'ok';
                        }

                        var $payments  = $('.ab-payment', $container),
                            $coupon_pay_button = $('.ab-coupon-payment-button', $container),
                            $apply_coupon_button = $('.btn-apply-coupon', $container),
                            $coupon_input = $('input.ab-user-coupon', $container),
                            $coupon_error = $('.ab-coupon-error', $container),
                            $coupon_info_text = $('.ab-info-text-coupon', $container),
                            $ab_payment_nav = $('.ab-payment-nav', $container),
                            $buttons = $('.ab-paypal-payment-button,.ab-card-payment-button,form.ab-authorizenet,form.ab-stripe,.ab-local-payment-button,.ab-2checkout-payment-button,.ab-payulatam-payment-button,.ab-payson-payment-button,.ab-mollie-payment-button', $container),
                            response_url = document.URL
                        ;
                        $('.ab-2checkout-form > input[name=x_receipt_link_url]', $container).val(response_url);
                        response_url  = response_url.split('#')[0];
                        response_url += (response_url.indexOf('?') == -1) ? '?' : '&';
                        $('input[name=response_url]', $container).val(response_url); // for ab-payson-form && ab-mollie-form
                        $('.ab-payulatam-form > input[name=responseUrl]', $container).val(response_url + 'action=ab-payulatam-checkout&ab_fid=' + Options.form_id );
                        $('.ab-payulatam-form > input[name=confirmationUrl]', $container).val(response_url + 'action=ab-payulatam-ipn&ab_fid=' + Options.form_id );
                        $payments.on('click', function() {
                            $buttons.hide();
                            $('.ab-' + $(this).val() + '-payment-button', $container).show();
                            if ($(this).val() == 'card') {
                                $('form.ab-' + $(this).data('form'), $container).show();
                            }
                        });

                        $apply_coupon_button.on('click', function (e) {
                            var ladda = ladda_start(this);
                            $coupon_error.text('');
                            $coupon_input.removeClass('ab-field-error');

                            var data = {
                                action : 'ab_apply_coupon',
                                form_id: Options.form_id,
                                coupon : $coupon_input.val()
                            };

                            $.ajax({
                                type        : 'POST',
                                url         : Options.ajaxurl,
                                data        : data,
                                dataType    : 'json',
                                xhrFields   : {withCredentials: true},
                                crossDomain : 'withCredentials' in new XMLHttpRequest(),
                                success     : function (response) {
                                    if (response.success) {
                                        $coupon_info_text.html(response.text);
                                        $coupon_input.replaceWith(data.coupon);
                                        $apply_coupon_button.replaceWith('✓');
                                        if (response.total <= 0) {
                                            $ab_payment_nav.hide();
                                            $buttons.hide();
                                            $coupon_pay_button.show('fast', function () {
                                                $('.ab-coupon-free', $container).attr('checked', 'checked').val(data.coupon);
                                            });
                                        } else {
                                            // Set new price for payment request
                                            $('input.ab--coupon-change-price', $container).val(response.total);
                                            var $payulatam_form = $('.ab-payulatam-form', $container);
                                            if ($payulatam_form.length) {
                                                $.post(Options.ajaxurl, {action: 'ab_payulatam_refresh_tokens', form_id: Options.form_id})
                                                    .done(function(response){
                                                        if (response.success) {
                                                            $payulatam_form.find('input[name=referenceCode]').val(response.data.referenceCode);
                                                            $payulatam_form.find('input[name=signature]').val(response.data.signature);
                                                        }
                                                    }, 'json' );
                                            }
                                        }
                                    } else if (response.error_code == 6) {
                                        $coupon_error.html(response.error);
                                        $coupon_input.addClass('ab-field-error');
                                        $coupon_info_text.html(response.text);
                                        scrollTo($coupon_error);
                                    }
                                    ladda.stop();
                                },
                                error : function () {
                                    ladda.stop();
                                }
                            });
                        });

                        $('.ab-next-step', $container).on('click', function (e) {
                            var ladda = ladda_start(this),
                                $form
                            ;
                            if ($('.ab-payment[value=local]', $container).is(':checked') || $(this).hasClass('ab-coupon-payment')) {
                                // handle only if was selected local payment !
                                e.preventDefault();
                                save();

                            } else if ($('.ab-payment[value=card]', $container).is(':checked')) {
                                var stripe = $('.ab-payment[data-form=stripe]', $container).is(':checked');
                                var card_action = stripe ? 'ab_stripe' : 'ab_authorize_net_aim';
                                $form = $container.find(stripe ? '.ab-stripe' : '.ab-authorizenet');
                                e.preventDefault();

                                var data = {
                                    action: card_action,
                                    card: {
                                        number   : $form.find('input[name="ab_card_number"]').val(),
                                        cvc      : $form.find('input[name="ab_card_cvc"]').val(),
                                        exp_month: $form.find('select[name="ab_card_exp_month"]').val(),
                                        exp_year : $form.find('select[name="ab_card_exp_year"]').val()
                                    },
                                    form_id: Options.form_id
                                };

                                var card_payment = function (data) {
                                    $.ajax({
                                        type       : 'POST',
                                        url        : Options.ajaxurl,
                                        data       : data,
                                        dataType   : 'json',
                                        xhrFields  : {withCredentials: true},
                                        crossDomain: 'withCredentials' in new XMLHttpRequest(),
                                        success    : function (response) {
                                            if (response.success) {
                                                stepComplete();
                                            } else if (response.error_code == 3) {
                                                handle_error_3(response);
                                            } else if (response.error_code == 7) {
                                                ladda.stop();
                                                $form.find('.ab-card-error').text(response.error);
                                            }
                                        }
                                    });
                                };
                                if (stripe && $form.find('#publishable_key').val()) {
                                    try {
                                        Stripe.setPublishableKey($form.find('#publishable_key').val());
                                        Stripe.createToken(data.card, function (status, response) {
                                            if (response.error) {
                                                $form.find('.ab-card-error').text(response.error.message);
                                                ladda.stop();
                                            } else {
                                                // Token from stripe.js
                                                data['card'] = response['id'];
                                                card_payment(data);
                                            }
                                        });
                                    } catch (e) {
                                        $form.find('.ab-card-error').text(e.message);
                                        ladda.stop();
                                    }
                                } else {
                                    card_payment(data);
                                }
                            } else if (    $('.ab-payment[value=paypal]',    $container).is(':checked')
                                        || $('.ab-payment[value=2checkout]', $container).is(':checked')
                                        || $('.ab-payment[value=payulatam]', $container).is(':checked')
                                        || $('.ab-payment[value=payson]',    $container).is(':checked')
                                        || $('.ab-payment[value=mollie]',    $container).is(':checked')
                            ) {
                                e.preventDefault();
                                $form = $(this).closest('form');
                                if ($form.find('input.ab--pending_appointments').length > 0 ) {
                                    $.ajax({
                                        type       : 'POST',
                                        url        : Options.ajaxurl,
                                        xhrFields  : {withCredentials: true},
                                        crossDomain: 'withCredentials' in new XMLHttpRequest(),
                                        data       : {action: 'ab_save_pending_appointment', form_id: Options.form_id, gateway: $form.data('gateway')},
                                        dataType   : 'json',
                                        success    : function (response) {
                                            if (response.success) {
                                                $form.find('input.ab--pending_appointments').val(response.ca_ids);
                                                $form.submit();
                                            } else if (response.error_code == 3) {
                                                handle_error_3(response);
                                            }
                                        }
                                    });
                                } else {
                                    $.ajax({
                                        type       : 'POST',
                                        url        : Options.ajaxurl,
                                        xhrFields  : {withCredentials: true},
                                        crossDomain: 'withCredentials' in new XMLHttpRequest(),
                                        data       : {action: 'ab_check_cart', form_id: Options.form_id},
                                        dataType   : 'json',
                                        success    : function (response) {
                                            if (response.success) {
                                                $form.submit();
                                            } else if (response.error_code == 3) {
                                                handle_error_3(response);
                                            }
                                        }
                                    });
                                }
                            }
                        });

                        $('.ab-back-step', $container).on('click', function (e) {
                            e.preventDefault();
                            ladda_start(this);
                            if (Options.cart.enabled && Options.cart.after_details) {
                                stepCart(cart_key);
                            } else {
                                stepDetails(cart_key);
                            }
                        });
                    }
                }
            });
        }

        /**
         * Complete step.
         */
        function stepComplete() {
            if (Options.final_step_url) {
                document.location.href = Options.final_step_url;
            } else {
                $.ajax({
                    url: Options.ajaxurl,
                    data: {action: 'ab_render_complete', form_id: Options.form_id},
                    dataType: 'json',
                    xhrFields: {withCredentials: true},
                    crossDomain: 'withCredentials' in new XMLHttpRequest(),
                    success: function (response) {
                        if (response.success) {
                            $container.html(response.html);
                            scrollTo($container);
                        }
                    }
                });
            }
        }

        // =========== helpers ===================

        function hideByAttributes() {
            if (Options.attributes.hide_categories) {
                $('.ab-category', $container).hide();
            }
            if (Options.attributes.hide_services && Options.attributes.service_id) {
                $('.ab-service', $container).hide();
            }
            if (Options.attributes.hide_staff_members) {
                $('.ab-employee', $container).hide();
            }
            if (Options.attributes.hide_date) {
                $('.ab-available-date', $container).hide();
            }
            if (Options.attributes.hide_week_days) {
                $('.ab-available-days', $container).hide();
            }
            if (Options.attributes.hide_time_range) {
                $('.ab-time-range', $container).hide();
            }
            if (!Options.attributes.show_number_of_persons) {
                $('.ab-number-of-persons', $container).hide();
            }
            if (Options.attributes.show_number_of_persons &&
                !Options.attributes.hide_staff_members &&
                !Options.attributes.hide_services &&
                !Options.attributes.hide_categories) {
                $('.ab-mobile-step_1', $container).addClass('ab-four-cols');
            }
        }

        // insert data into select
        function setSelect($select, data, leave_selected) {
            var selected = $select.val();
            var reset    = true;
            // reset select
            $('option:not([value=""])', $select).remove();
            // and fill the new data
            var docFragment = document.createDocumentFragment();

            function valuesToArray(obj) {
                return Object.keys(obj).map(function (key) { return obj[key]; });
            }

            function compare(a, b) {
                if (parseInt(a.position) < parseInt(b.position))
                    return -1;
                if (parseInt(a.position) > parseInt(b.position))
                    return 1;
                return 0;
            }

            // sort select by position
            data = valuesToArray(data).sort(compare);

            $.each(data, function(id, object) {
                id = object.id;

                if (selected === id && leave_selected) {
                    reset = false;
                }
                var option = document.createElement('option');
                option.value = id;
                option.text = object.name;
                docFragment.appendChild(option);
            });
            $select.append(docFragment);
            // set default value of select
            $select.val(reset ? '' : selected);
        }

        //
        function save() {
            $.ajax({
                type        : 'POST',
                url         : Options.ajaxurl,
                xhrFields   : { withCredentials: true },
                crossDomain : 'withCredentials' in new XMLHttpRequest(),
                data        : { action : 'ab_save_appointment', form_id : Options.form_id },
                dataType    : 'json'
            }).done(function(response) {
                if (response.success) {
                    stepComplete();
                } else if (response.error_code == 3) {
                    handle_error_3(response);
                }
            });
        }

        function ladda_start($elem) {
            var ladda = Ladda.create($elem);
            ladda.start();
            return ladda;
        }

        /**
         * Handle error with code 3 which means one of the cart item is not available anymore.
         *
         * @param response
         */
        function handle_error_3(response) {
            if (Options.cart.enabled) {
                stepCart(response.failed_cart_key, response.error);
            } else {
                stepTime(undefined, response.failed_cart_key, response.error);
            }
        }

        /**
         * Scroll to element if it is not visible.
         *
         * @param $elem
         */
        function scrollTo($elem) {
            var elemTop   = $elem.offset().top;
            var scrollTop = $(window).scrollTop();
            if (elemTop < $(window).scrollTop() || elemTop > scrollTop + window.innerHeight) {
                $('html,body').animate({ scrollTop: (elemTop - 24) }, 500);
            }
        }

    };

})(jQuery);
