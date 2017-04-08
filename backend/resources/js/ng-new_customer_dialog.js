;(function() {

    angular.module('newCustomerDialog', []).directive('newCustomerDialog', function() {
        return {
            restrict : 'A',
            replace  : true,
            scope    : {
                callback  : '&newCustomerDialog',
                backdrop  : '@',
                btn_class : '@btnClass'
            },
            templateUrl : ajaxurl + '?action=ab_get_ng_new_customer_dialog_template&module=' + ((typeof BooklyL10n.module == 'undefined')? 'calendar' : BooklyL10n.module) ,
            // The linking function will add behavior to the template.
            link: function(scope, element, attrs) {
                // Init properties.
                var init = function() {
                    // Form fields.
                    scope.form = {
                        wp_user_id : '',
                        name       : '',
                        phone      : '',
                        email      : ''
                    };
                    // Custom fields.
                    element.find('input.ab-custom-field:text, textarea.ab-custom-field, select.ab-custom-field').val('');
                    element.find('input.ab-custom-field:checkbox, input.ab-custom-field:radio').prop('checked', false);
                    element.find('label.ab_service > input:checkbox').prop('checked', false);
                    if (BooklyL10n.intlTelInput.enabled) {
                        element.find('#phone').intlTelInput({
                            preferredCountries: [BooklyL10n.intlTelInput.country],
                            defaultCountry: BooklyL10n.intlTelInput.country,
                            geoIpLookup: function (callback) {
                                jQuery.get(ajaxurl, {action: 'ab_ip_info'}, function () {
                                }, 'json').always(function (resp) {
                                    var countryCode = (resp && resp.country) ? resp.country : '';
                                    callback(countryCode);
                                });
                            },
                            utilsScript: BooklyL10n.intlTelInput.utils
                        });
                    }
                    // Form errors.
                    scope.errors = {
                        name : {
                            required : false
                        }
                    };
                    // Loading indicator.
                    scope.loading = false;
                };

                // Run init.
                init();

                // On 'Cancel' button click.
                scope.closeDialog = function() {
                    // Close the dialog.
                    element.children('#ab_new_customer_dialog').modal('hide');
                    // Re-init all properties.
                    init();
                };

                /**
                 * Send form to server.
                 */
                scope.processForm = function() {
                    scope.errors  = {};
                    scope.loading = true;
                    scope.form.phone = BooklyL10n.intlTelInput.enabled ? jQuery('#phone').intlTelInput('getNumber') : jQuery('#phone').val();
                    jQuery.ajax({
                        url  : ajaxurl,
                        type : 'POST',
                        data : jQuery.extend({ action : 'ab_save_customer' }, scope.form),
                        dataType : 'json',
                        success : function ( response ) {
                            scope.$apply(function(scope) {
                                if (response.success) {
                                    // save custom fields of new customer into parent scope
                                    var result  = [],
                                        extras = [],
                                        $fields = element.find('.new-customer-custom-fields .ab-formField'),
                                        $extras
                                    ;
                                    if (scope.form.service) {
                                        $extras = jQuery('#ab_custom_fields_dialog label.service_' + scope.form.service.id + ' input:checkbox:checked');
                                    } else {
                                        $extras = jQuery('#ab_custom_fields_dialog label.ab_service input:checkbox:checked');
                                    }

                                    $fields.each(function() {
                                        var $this = jQuery(this);
                                        var value;
                                        switch ($this.data('type')) {
                                            case 'checkboxes':
                                                value = [];
                                                $this.find('.ab-custom-field:checked').each(function() {
                                                    value.push(this.value);
                                                });
                                                break;
                                            case 'radio-buttons':
                                                value = $this.find('.ab-custom-field:checked').val();
                                                break;
                                            default:
                                                value = $this.find('.ab-custom-field').val();
                                                break;
                                        }
                                        result.push({ id: $this.data('id'), value: value });
                                    });

                                    $extras.each(function () {
                                        extras.push(jQuery(this).val());
                                    });

                                    response.customer.custom_fields = result;
                                    response.customer.extras = extras;

                                    // Send new customer to the parent scope.
                                    scope.callback({customer : response.customer});
                                    // Close the dialog.
                                    scope.closeDialog();
                                } else {
                                    // Set errors.
                                    jQuery.each(response.errors, function(field, errors) {
                                        scope.errors[field] = {};
                                        jQuery.each(errors, function(key, error) {
                                            scope.errors[field][error] = true;
                                        });
                                    });
                                }
                                scope.loading = false;
                            });
                        },
                        error : function() {
                            scope.$apply(function(scope) {
                                scope.loading = false;
                            });
                        }
                    });
                };
            }
        };
    });

})();