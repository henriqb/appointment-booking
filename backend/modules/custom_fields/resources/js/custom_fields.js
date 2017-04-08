jQuery(function($) {

    var $fields = $("ul#ab-custom-fields"),
        $cf_per_service = $('#ab_custom_fields_per_service');

    $fields.sortable({
        axis   : 'y',
        handle : '.ab-handle'
    });

    $cf_per_service.change(function() {
        if ($(this).val() == 1) {
            $('.ab--services-holder').fadeIn('slow');
        } else {
            $('.ab--services-holder').fadeOut('slow');
        }
    });

    /**
     * Build initial fields.
     */
    restoreFields();

    /**
     * On "Add new field" button click.
     */
    $('#ab-add-fields').on('click', 'button', function() {
        addField($(this).data('type'));
    });

    /**
     * On "Add new item" button click.
     */
    $fields.on('click', 'button', function() {
        addItem($(this).prev('ul'), $(this).data('type'));
    });

    /**
     * Delete field or checkbox/radio button/drop-down option.
     */
    $fields.on('click', '.ab-delete', function() {
        $(this).closest('li').fadeOut('fast', function() { $(this).remove(); });
    });

    /**
     * Submit custom fields form.
     */
    $('#ajax-send-custom-fields').on('click', function(e) {
        e.preventDefault();
        var data = [];
        $fields.children('li').each(function() {
            var $this = $(this),
                field = {};
            switch ($this.data('type')) {
                case 'checkboxes':
                case 'radio-buttons':
                case 'drop-down':
                    field.items = [];
                    $this.find('ul.ab-items li').each(function() {
                        field.items.push($(this).find('input').val());
                    });
                case 'textarea':
                case 'text-field':
                case 'text-content':
                case 'captcha':
                    field.type     = $this.data('type');
                    field.label    = $this.find('.ab-label').val();
                    field.required = $this.find('.ab-required').prop('checked');
                    field.id       = $this.data('ab-field-id');
                    field.services = $this.find('.ab--services-holder input:checked')
                        .map(function() { return this.value; })
                        .get();
            }
            data.push(field);
        });

        var ladda = Ladda.create(this);
        ladda.start();
        $.ajax({
            type      : 'POST',
            url       : ajaxurl,
            xhrFields : { withCredentials: true },
            data      : { action: 'ab_save_custom_fields', fields: JSON.stringify(data), cf_per_service: $cf_per_service.val() },
            complete  : function() {
                ladda.stop();
            }
        });
    });

    /**
     * On 'Reset' click.
     */
    $('button[type=reset]').on('click', function() {
        $fields.empty();
        restoreFields();
    });

    /**
     * Add new field.
     *
     * @param type
     * @param id
     * @param label
     * @param required
     * @param services
     * @returns {*|jQuery}
     */
    function addField(type, id, label, required, services) {
        var $new_field = $('ul#ab-templates > li[data-type=' + type + ']').clone();
        // Set id, label and required.
        if (typeof id == 'undefined') {
            id = Math.floor((Math.random() * 100000) + 1);
        }
        if (typeof label == 'undefined') {
            label = '';
        }
        if (typeof required == 'undefined') {
            required = false;
        }
        $new_field
            .hide()
            .data('ab-field-id', id)
            .find('.ab-required').prop({
                id      : 'required-' + id,
                checked : required
            })
            .next('label').attr('for', 'required-' + id)
            .end().end()
            .find('.ab-label').val(label)
            .end()
            .find('.ab--services-holder input:checkbox').each(function (index) {
                if (services && $.inArray(this.value, services) > -1) {
                    this.checked = true;
                }
                this.id = 'check-' + id + '-' + index;
                $(this).next().attr('for', 'check-' + id + '-' + index);
            });
        // Add new field to the list.
        $fields.append($new_field);
        $new_field.fadeIn('fast');
        // Make it sortable.
        $new_field.find('ul.ab-items').sortable({
            axis   : 'y',
            handle : '.ab-inner-handle'
        });
        // Set focus to label field.
        $new_field.find('.ab-label').focus();

        return $new_field;
    }

    /**
     * Add new checkbox/radio button/drop-down option.
     *
     * @param $ul
     * @param type
     * @param value
     * @return {*|jQuery}
     */
    function addItem($ul, type, value) {
        var $new_item = $('ul#ab-templates > li[data-type=' + type + ']').clone();
        if (typeof value != 'undefined') {
            $new_item.find('input').val(value);
        }
        $new_item.hide().appendTo($ul).fadeIn('fast').find('input').focus();

        return $new_item;
    }

    /**
     * Restore fields from BooklyL10n.custom_fields.
     */
    function restoreFields() {
        if (BooklyL10n.custom_fields) {
            var custom_fields = jQuery.parseJSON(BooklyL10n.custom_fields);
            $.each(custom_fields, function (i, field) {
                var $new_field = addField(field.type, field.id, field.label, field.required, field.services);
                // add children
                if (field.items) {
                    $.each(field.items, function (i, value) {
                        addItem($new_field.find('ul.ab-items'), field.type + '-item', value);
                    });
                }
            });
        }
        $cf_per_service.change();
        $('.ab--services-holder').each(function (id, elem) {
            updateServiceButton($(elem));
        });
        $(':focus').blur();
    }

    $('.ab-popover').popover({trigger: 'hover'});

    function updateServiceButton($holder) {
        var service_checked = $holder.find('.ab-service:checked').length;
        if (service_checked == 0) {
            $holder.find('.ab-count').text(BooklyL10n.no_service_selected);
            $holder.find('.ab-all-services').prop('checked', false);
        } else if (service_checked == 1) {
            $holder.find('.ab-count').text($holder.find('.ab-service:checked').data('title'));
            $holder.find('.ab-all-services').prop('checked', false);
        } else {
            if( service_checked == $holder.find('.ab-service').length) {
                $holder.find('.ab-all-services').prop('checked', true);
                $holder.find('.ab-count').text(BooklyL10n.all_services);
            } else {
                $holder.find('.ab-all-services').prop('checked', false);
                $holder.find('.ab-count').text(service_checked + '/' + $holder.find('.ab-service').length);
            }
        }
    }

    $(document).on('click', '.ab-all-services', function () {
        var $holder = $(this).parents('.ab--services-holder');
        $holder.find('.ab-service').prop('checked', $(this).prop('checked'));
        updateServiceButton($holder);
    });

    $(document).on('click', '.ab--services-holder ul.dropdown-menu li a[href]', function (e) {
        updateServiceButton($(this).parents('.ab--services-holder'));
        e.stopPropagation();
    });
});