jQuery(window).on('load', function () {
    var saved_mehod_value = en_globaltranz_admin_script.wc_settings_cerasis_rate_method
    if (saved_mehod_value == 'Cheapest') {
        jQuery(".cerasis_delivery_estimate").removeAttr('style');
        jQuery(".cerasis_Number_of_label_as").removeAttr('style');
        jQuery(".cerasis_Number_of_options_class").removeAttr('style');

        jQuery("#wc_settings_cerasis_Number_of_options").closest('tr').addClass("cerasis_Number_of_options_class");
        jQuery("#wc_settings_cerasis_Number_of_options").closest('tr').css("display", "none");
        jQuery("#wc_settings_cerasis_label_as").closest('tr').addClass("cerasis_Number_of_label_as");
        jQuery("#wc_settings_cerasis_delivery_estimate").closest('tr').addClass("cerasis_delivery_estimate");
        jQuery("#wc_settings_cerasis_rate_method").closest('tr').addClass("cerasis_rate_mehod");

        jQuery('.cerasis_rate_mehod td span').html('Displays only the cheapest returned Rate.');
        jQuery('.cerasis_Number_of_label_as td span').html('What the user sees during checkout, e.g. Freight. Leave blank to display the carrier name.');
    }
    if (saved_mehod_value == 'cheapest_options') {

        jQuery(".cerasis_delivery_estimate").removeAttr('style');
        jQuery(".cerasis_Number_of_label_as").removeAttr('style');
        jQuery(".cerasis_Number_of_options_class").removeAttr('style');

        jQuery("#wc_settings_cerasis_delivery_estimate").closest('tr').addClass("cerasis_delivery_estimate");
        jQuery("#wc_settings_cerasis_label_as").closest('tr').addClass("cerasis_Number_of_label_as");
        jQuery("#wc_settings_cerasis_label_as").closest('tr').css("display", "none");
        jQuery("#wc_settings_cerasis_Number_of_options").closest('tr').addClass("cerasis_Number_of_options_class");
        jQuery("#wc_settings_cerasis_rate_method").closest('tr').addClass("cerasis_rate_mehod");

        jQuery('.cerasis_rate_mehod td p').html('Displays a list of a specified number of least expensive options.');
        jQuery('.cerasis_Number_of_options_class td p').html('Number of options to display in the shopping cart.');
    }
    if (saved_mehod_value == 'average_rate') {

        jQuery(".cerasis_delivery_estimate").removeAttr('style');
        jQuery(".cerasis_Number_of_label_as").removeAttr('style');
        jQuery(".cerasis_Number_of_options_class").removeAttr('style');

        jQuery("#wc_settings_cerasis_delivery_estimate").closest('tr').addClass("cerasis_delivery_estimate");
        jQuery("#wc_settings_cerasis_delivery_estimate").closest('tr').css("display", "none");
        jQuery("#wc_settings_cerasis_label_as").closest('tr').addClass("cerasis_Number_of_label_as");
        jQuery("#wc_settings_cerasis_Number_of_options").closest('tr').addClass("cerasis_Number_of_options_class");
        jQuery("#wc_settings_cerasis_rate_method").closest('tr').addClass("cerasis_rate_mehod");

        jQuery('.cerasis_rate_mehod td p').html('Displays a single rate based on an average of a specified number of least expensive options.');
        jQuery('.cerasis_Number_of_options_class td p').html('Number of options to include in the calculation of the average.');
        jQuery('.cerasis_Number_of_label_as td span').html('What the user sees during checkout, e.g. Freight. If left blank will default to Freight.');

    }

    const cerasis_gtz_api_endpoint = jQuery('#cerasis_global_tranz_api_endpoint_quote_settings').val();
    if (cerasis_gtz_api_endpoint == 'wc_global_tranz_new_api_fields') {
        jQuery('.wc_settings_globaltranz_label_as, .select_all_global_tranz_services_tr, .global_tranz_quotes_services_tr, .global_tranz_quotes_services_tr').hide();
        jQuery('#sevice_global_tranz_label_as_title, #sevice_global_tranz_title').closest('tr').hide();
    }
});

jQuery(document).ready(function () {

    // Weight threshold for LTL freight
    en_weight_threshold_limit();

    jQuery("#order_shipping_line_items .shipping .view .display_meta").css('display', 'none');

    jQuery("#engtz_freight_handling_weight").closest('tr').addClass("engtz_freight_handling_weight_tr");
    jQuery("#engtz_freight_maximum_handling_weight").closest('tr').addClass("engtz_freight_maximum_handling_weight_tr");

    jQuery("#engtz_freight_handling_weight, #engtz_freight_maximum_handling_weight").keydown(function (e) {
        // Allow: backspace, delete, tab, escape, enter and .
        if (jQuery.inArray(e.keyCode, [46, 8, 9, 27, 13, 110, 190]) !== -1 ||
            // Allow: Ctrl+A, Command+A
            (e.keyCode === 65 && (e.ctrlKey === true || e.metaKey === true)) ||
            // Allow: home, end, left, right, down, up
            (e.keyCode >= 35 && e.keyCode <= 40)|| e.keyCode == 109) {
            // let it happen, don't do anything
            return;
        }
        
        // Ensure that it is a number and stop the keypress
        if ((e.keyCode === 190 || e.shiftKey || (e.keyCode < 48 || e.keyCode > 57)) && (e.keyCode < 96 || e.keyCode > 105)) {
            e.preventDefault();
        }
    
        if ((jQuery(this).val().indexOf('.') != -1) && (jQuery(this).val().substring(jQuery(this).val().indexOf('.'), jQuery(this).val().indexOf('.').length).length > 2)) {
            if (event.keyCode !== 8 && event.keyCode !== 46) { //exception
                event.preventDefault();
            }
        }
    });
        
    jQuery("#engtz_freight_handling_weight, #engtz_freight_maximum_handling_weight").keyup(function (e) {
    
        var val = jQuery(this).val();
    
        if (val.split('.').length - 1 > 1) {
            var newval = val.substring(0, val.length - 1);
            var countDots = newval.substring(newval.indexOf('.') + 1).length;
            newval = newval.substring(0, val.length - countDots - 1);
            jQuery(this).val(newval);
        }
    
        if (val.split('%').length - 1 > 1) {
            var newval = val.substring(0, val.length - 1);
            var countPercentages = newval.substring(newval.indexOf('%') + 1).length;
            newval = newval.substring(0, val.length - countPercentages - 1);
            jQuery(this).val(newval);
        }
    });

    // JS for edit product nested fields
    jQuery("._nestedMaterials").closest('p').addClass("_nestedMaterials_tr");
    jQuery("._nestedPercentage").closest('p').addClass("_nestedPercentage_tr");
    jQuery("._maxNestedItems").closest('p').addClass("_maxNestedItems_tr");
    jQuery("._nestedDimension").closest('p').addClass("_nestedDimension_tr");
    jQuery("._nestedStakingProperty").closest('p').addClass("_nestedStakingProperty_tr");

    if (!jQuery('._nestedMaterials').is(":checked")) {
        jQuery('._nestedPercentage_tr').hide();
        jQuery('._nestedDimension_tr').hide();
        jQuery('._maxNestedItems_tr').hide();
        jQuery('._nestedDimension_tr').hide();
        jQuery('._nestedStakingProperty_tr').hide();
    } else {
        jQuery('._nestedPercentage_tr').show();
        jQuery('._nestedDimension_tr').show();
        jQuery('._maxNestedItems_tr').show();
        jQuery('._nestedDimension_tr').show();
        jQuery('._nestedStakingProperty_tr').show();
    }

    jQuery("input[name=_nestedPercentage]").attr('min', '0');
    jQuery("input[name=_maxNestedItems]").attr('min', '0');
    jQuery("input[name=_nestedPercentage]").attr('max', '100');
    jQuery("input[name=_maxNestedItems]").attr('max', '100');
    jQuery("input[name=_nestedPercentage]").attr('maxlength', '3');
    jQuery("input[name=_maxNestedItems]").attr('maxlength', '3');

    if (jQuery("input[name=_nestedPercentage]").val() == '') {
        jQuery("input[name=_nestedPercentage]").val(0);
    }

    jQuery("._nestedPercentage").keydown(function (eve) {
        globaltranz_lfq_stop_special_characters(eve);
        var nestedPercentage = jQuery('._nestedPercentage').val();
        if (nestedPercentage.length == 2) {
            var newValue = nestedPercentage + '' + eve.key;
            if (newValue > 100) {
                return false;
            }
        }
    });

    jQuery("._nestedDimension").keydown(function (eve) {
        globaltranz_lfq_stop_special_characters(eve);
        var nestedDimension = jQuery('._nestedDimension').val();
        if (nestedDimension.length == 2) {
            var newValue1 = nestedDimension + '' + eve.key;
            if (newValue1 > 100) {
                return false;
            }
        }
    });

    jQuery("._maxNestedItems").keydown(function (eve) {
        globaltranz_lfq_stop_special_characters(eve);
    });

    jQuery("._nestedMaterials").change(function () {
        if (!jQuery('._nestedMaterials').is(":checked")) {
            jQuery('._nestedPercentage_tr').hide();
            jQuery('._nestedDimension_tr').hide();
            jQuery('._maxNestedItems_tr').hide();
            jQuery('._nestedDimension_tr').hide();
            jQuery('._nestedStakingProperty_tr').hide();
        } else {
            jQuery('._nestedPercentage_tr').show();
            jQuery('._nestedDimension_tr').show();
            jQuery('._maxNestedItems_tr').show();
            jQuery('._nestedDimension_tr').show();
            jQuery('._nestedStakingProperty_tr').show();
        }
    });

    // Hide rating method on final mile service
    jQuery.fn.en_rating_status_display = function () {
        jQuery('#wc_settings_cerasis_rate_method, .cerasis_Number_of_options_class, #wc_settings_cerasis_label_as ').closest('tr').show();

    }

    jQuery.fn.en_rating_status_hide = function () {
        jQuery('.cerasis_rate_mehod, #wc_settings_cerasis_Number_of_options, .cerasis_Number_of_label_as ').closest('tr').hide();
    }

    jQuery("#cerasis_global_tranz_shipping_service").change(function () {
        if (jQuery("#cerasis_global_tranz_shipping_service").val() == "wc_final_mile_service") {

            jQuery.fn.en_rating_status_hide();
        }
        if (jQuery("#cerasis_global_tranz_shipping_service").val() == "wc_standard_lfq_service") {

            jQuery.fn.en_rating_status_display();
        }
    });

    jQuery('.checkbox_fr_add_fm').on('click', function () {
        if (!jQuery(this).is(':checked')) {
            return false;
        }
    });

    var checkbox_fr_add_fm = jQuery('.checkbox_fr_add_fm:checked').length;
    if (!checkbox_fr_add_fm > 0) {
        jQuery('#en_cerasis_threshold_checkbox').prop('checked', true);
    }

    // GlobalTranz
    jQuery('#wc_settings_globaltranz_lowest_label_as,#wc_settings_globaltranz_quickest_label_as').closest('tr').addClass('wc_settings_globaltranz_label_as');

    jQuery('.wc_final_mile_service').closest('tr').addClass('wc_final_mile_service_tr');
    jQuery('.cerasis_global_tranz_api_endpoint_class').closest('tr').addClass('cerasis_global_tranz_api_endpoint_class');
    jQuery('#select_all_global_tranz_services').closest('tr').addClass('select_all_global_tranz_services_tr');
    jQuery('.global_tranz_quotes_services').closest('tr').addClass('global_tranz_quotes_services_tr');
    jQuery('.global_tranz_quotes_services').closest('td').addClass('global_tranz_quotes_services_td');

    setTimeout(function(){
        jQuery.fn.rating_status();
    }, 1000);
    
    jQuery('#service_global_tranz_rating_method_ch_unch').on('change', function () {
        jQuery.fn.rating_status();
    });

    // GT rating method
    jQuery.fn.rating_status = function () {
        var standart_lfq_service = jQuery('select[name=cerasis_global_tranz_shipping_service] option').filter(':selected').val();

        if (standart_lfq_service != 'wc_standard_lfq_service' || (typeof standart_lfq_service === 'undefined')) {
            var rating_method_status = jQuery('#service_global_tranz_rating_method_ch_unch').prop('checked');
            if (rating_method_status) {
                jQuery('#wc_settings_cerasis_rate_method, #wc_settings_cerasis_Number_of_options, #wc_settings_cerasis_label_as').prop('disabled', false);
                jQuery('#wc_settings_cerasis_rate_method, #wc_settings_cerasis_Number_of_options, #wc_settings_cerasis_label_as').removeClass('disabled_me');
                jQuery('#wc_settings_globaltranz_quickest_label_as, #wc_settings_globaltranz_lowest_label_as, #select_all_global_tranz_services, #service_global_tranz_lowest_cost_rate_quotes, #service_global_tranz_quickest_transit_rate_quotes').prop('disabled', true);
                jQuery('#wc_settings_globaltranz_quickest_label_as, #wc_settings_globaltranz_lowest_label_as, #select_all_global_tranz_services, #service_global_tranz_lowest_cost_rate_quotes, #service_global_tranz_quickest_transit_rate_quotes').addClass('disabled_me');

            } else {
                jQuery('#wc_settings_cerasis_rate_method, #wc_settings_cerasis_Number_of_options, #wc_settings_cerasis_label_as').prop('disabled', true);
                jQuery('#wc_settings_cerasis_rate_method, #wc_settings_cerasis_Number_of_options, #wc_settings_cerasis_label_as').addClass('disabled_me');
                jQuery('#wc_settings_globaltranz_quickest_label_as, #wc_settings_globaltranz_lowest_label_as, #select_all_global_tranz_services, #service_global_tranz_lowest_cost_rate_quotes, #service_global_tranz_quickest_transit_rate_quotes').removeClass('disabled_me');
                jQuery('#wc_settings_globaltranz_quickest_label_as, #wc_settings_globaltranz_lowest_label_as, #select_all_global_tranz_services, #service_global_tranz_lowest_cost_rate_quotes, #service_global_tranz_quickest_transit_rate_quotes').prop('disabled', false);
            }
        }
    }

    var global_tranz_all_checkboxes = jQuery('.global_tranz_quotes_services');
    if (global_tranz_all_checkboxes.length === global_tranz_all_checkboxes.filter(":checked").length) {
        jQuery('.global_tranz_all_services').prop('checked', true);
    }

    // Check All Checkbox
    jQuery(".global_tranz_all_services").change(function () {
        if (this.checked) {
            jQuery(".global_tranz_quotes_services").each(function () {
                this.checked = true;
            })
        } else {
            jQuery(".global_tranz_quotes_services").each(function () {
                this.checked = false;
            })
        }
    });

    jQuery(".global_tranz_quotes_services").on('change load', function () {
        var checkboxes = jQuery('.global_tranz_quotes_services:checked').length;
        var un_checkboxes = jQuery('.global_tranz_quotes_services').length;
        if (checkboxes === un_checkboxes) {
            jQuery('.global_tranz_all_services').prop('checked', true);
        } else {
            jQuery('.global_tranz_all_services').prop('checked', false);
        }
    });

    jQuery('#wc_settings_global_tranz_customer_id').attr('data-optional', '1');
    jQuery('#wc_gtz_new_api_api_username').attr('data-optional', '1');
    jQuery('#wc_gtz_new_api_api_password').attr('data-optional', '1');

    jQuery('#cerasis_global_tranz_api_endpoint').on('click', function () {
        cerasis_global_tranz_connection_section_api_endpoint();
    });

    cerasis_global_tranz_connection_section_api_endpoint();
    // Cuttoff Time
    jQuery("#gt_freight_shipment_offset_days").closest('tr').addClass("gt_freight_shipment_offset_days_tr");
    jQuery("#all_shipment_days_gt").closest('tr').addClass("all_shipment_days_gt_tr");
    jQuery(".gt_shipment_day").closest('tr').addClass("gt_shipment_day_tr");
    jQuery("#gt_freight_order_cut_off_time").closest('tr').addClass("gt_freight_cutt_off_time_ship_date_offset");

    // backup rates
    jQuery('input[name*="gtz_ltl_backup_rates_category"]').closest('tr').addClass("gtz_ltl_backup_rates_category");
    // backup rates as a fixed rate
    jQuery(".gtz_ltl_backup_rates_category input[value*='fixed_rate']").after('Backup rate as a fixed rate. <br /><input type="text" style="margin-top: 10px;" name="gtz_ltl_backup_rates_fixed_rate" id="gtz_ltl_backup_rates_fixed_rate" title="Fixed Backup Rates" maxlength="50" value="' + en_globaltranz_admin_script.gtz_ltl_backup_rates_fixed_rate + '"> <br> <span class="description"> Enter a value for the fixed rate. (e.g. 10.00)</span><br />');
    // backup rates as a percentage of Cart price
    jQuery(".gtz_ltl_backup_rates_category input[value*='percentage_of_cart_price']").after('Backup rate as a percentage of Cart price. <br /><input type="text" style="margin-top: 10px;" name="gtz_ltl_backup_rates_cart_price_percentage" id="gtz_ltl_backup_rates_cart_price_percentage" title="Backup rate as a percentage of Cart price." maxlength="50" value="' + en_globaltranz_admin_script.gtz_ltl_backup_rates_cart_price_percentage + '"> <br> <span class="description"> Enter a percentage for the backup rate. (e.g. 10.0%)</span><br />');
    // backup rates as a function of the Cart weight
    jQuery(".gtz_ltl_backup_rates_category input[value*='function_of_weight']").after('Backup rate as a function of the Cart weight. <br /><input type="text" style="margin-top: 10px;" name="gtz_ltl_backup_rates_weight_function" id="gtz_ltl_backup_rates_weight_function" title="Backup rate as a function of the Cart weight." maxlength="50" value="' + en_globaltranz_admin_script.gtz_ltl_backup_rates_weight_function + '"> <br> <span class="description"> Enter a rate per pound to use for the backup rate. (e.g. 2.00)</span><br />');

    jQuery('#gtz_ltl_backup_rates_label').attr('maxlength', '50');
    jQuery('#gtz_ltl_backup_rates_fixed_rate, #gtz_ltl_backup_rates_cart_price_percentage, #gtz_ltl_backup_rates_weight_function').attr('maxlength', '10');
    jQuery('#gtz_ltl_backup_rates_carrier_fails_to_return_response, #gtz_ltl_backup_rates_carrier_returns_error').closest('td').css('padding', '0px 10px');

    jQuery("#gtz_ltl_backup_rates_fixed_rate, #gtz_ltl_backup_rates_weight_function").keypress(function (e) {
        if (!String.fromCharCode(e.keyCode).match(/^[0-9\d\.\s]+$/i)) return false;
    });
    jQuery("#gtz_ltl_backup_rates_cart_price_percentage").keypress(function (e) {
        if (!String.fromCharCode(e.keyCode).match(/^[0-9\d\.%\s]+$/i)) return false;
    });
    jQuery('#gtz_ltl_backup_rates_fixed_rate, #gtz_ltl_backup_rates_cart_price_percentage, #gtz_ltl_backup_rates_weight_function').keyup(function(){
        let val = jQuery(this).val();
        const regex = /\./g;
        const count = (val.match(regex) || []).length;
        if(count > 1){
            val = val.replace(/\.+$/, "");
            jQuery(this).val(val);
        }
    });

    var gt_current_time = en_globaltranz_admin_script.gt_freight_order_cutoff_time;
    if (gt_current_time == '') {

        jQuery('#gt_freight_order_cut_off_time').wickedpicker({
            now: '',
            title: 'Cut Off Time',
        });
    } else {
        jQuery('#gt_freight_order_cut_off_time').wickedpicker({

            now: gt_current_time,
            title: 'Cut Off Time'
        });
    }

    var delivery_estimate_val = jQuery('input[name=gt_delivery_estimates]:checked').val();
    if (delivery_estimate_val == 'dont_show_estimates') {
        jQuery("#gt_freight_order_cut_off_time").prop('disabled', true);
        jQuery("#gt_freight_shipment_offset_days").prop('disabled', true);
        jQuery("#gt_freight_shipment_offset_days").css("cursor", "not-allowed");
        jQuery("#gt_freight_order_cut_off_time").css("cursor", "not-allowed");
    } else {
        jQuery("#gt_freight_order_cut_off_time").prop('disabled', false);
        jQuery("#gt_freight_shipment_offset_days").prop('disabled', false);
        jQuery("#gt_freight_order_cut_off_time").css("cursor", "");
    }

    jQuery("input[name=gt_delivery_estimates]").change(function () {
        var delivery_estimate_val = jQuery('input[name=gt_delivery_estimates]:checked').val();
        if (delivery_estimate_val == 'dont_show_estimates') {
            jQuery("#gt_freight_order_cut_off_time").prop('disabled', true);
            jQuery("#gt_freight_shipment_offset_days").prop('disabled', true);
            jQuery("#gt_freight_order_cut_off_time").css("cursor", "not-allowed");
            jQuery("#gt_freight_shipment_offset_days").css("cursor", "not-allowed");
        } else {
            jQuery("#gt_freight_order_cut_off_time").prop('disabled', false);
            jQuery("#gt_freight_shipment_offset_days").prop('disabled', false);
            jQuery("#gt_freight_order_cut_off_time").css("cursor", "auto");
            jQuery("#gt_freight_shipment_offset_days").css("cursor", "auto");
        }
    });

    /*
     * Uncheck Week days Select All Checkbox
     */
    jQuery(".gt_shipment_day").on('change load', function () {

        var checkboxes = jQuery('.gt_shipment_day:checked').length;
        var un_checkboxes = jQuery('.gt_shipment_day').length;
        if (checkboxes === un_checkboxes) {
            jQuery('.all_shipment_days_gt').prop('checked', true);
        } else {
            jQuery('.all_shipment_days_gt').prop('checked', false);
        }
    });

    /*
     * Select All Shipment Week days
     */

    var all_int_checkboxes = jQuery('.all_shipment_days_gt');
    if (all_int_checkboxes.length === all_int_checkboxes.filter(":checked").length) {
        jQuery('.all_shipment_days_gt').prop('checked', true);
    }

    jQuery(".all_shipment_days_gt").change(function () {
        if (this.checked) {
            jQuery(".gt_shipment_day").each(function () {
                this.checked = true;
            });
        } else {
            jQuery(".gt_shipment_day").each(function () {
                this.checked = false;
            });
        }
    });


    //** End: Order Cut Off Time

    /**
     * Offer lift gate delivery as an option and Always include residential delivery fee
     * @returns {undefined}
     */

    jQuery(".checkbox_fr_add").on("click", function () {
        var id = jQuery(this).attr("id");
        if (id == "gt_liftgate") {
            jQuery("#gt_quotes_liftgate_delivery_as_option").prop({checked: false});
            jQuery("#en_woo_addons_liftgate_with_auto_residential").prop({checked: false});

        } else if (id == "gt_quotes_liftgate_delivery_as_option" ||
            id == "en_woo_addons_liftgate_with_auto_residential") {
            jQuery("#gt_liftgate").prop({checked: false});
        }
    });
    jQuery(".refresh-carriers").on("click", function (e) {

        e.preventDefault();

        var action = {'action': 'refresh_carriers'};

        jQuery.ajax({
            type: 'POST',
            url: ajaxurl,
            data: action,
            beforeSend: function () {
                jQuery('.refresh-carriers-loader').html(' Loading .. ');
            },
            success: function (data) {
                location.reload();
            }
        });

    });

    jQuery("#automatically-enable").on("click", function () {

        var auto_enable = jQuery("#automatically-enable").is(":checked") ? "yes" : "no";
        var action = {'action': 'auto_enable_action', 'auto_enable': auto_enable};

        jQuery.ajax({
            type: 'POST',
            url: ajaxurl,
            data: action,
            beforeSend: function () {
                jQuery('#automatically-enable').after(' Loading .. ');
            },
            success: function (data) {
                location.reload();
            }
        });

    });

    jQuery(".carrier_section_class .liftgate_fee").keydown(function (e) {
        // Allow: backspace, delete, tab, escape, enter and .
        if (jQuery.inArray(e.keyCode, [46, 8, 9, 27, 13, 110, 190]) !== -1 ||
            // Allow: Ctrl+A, Command+A
            (e.keyCode === 65 && (e.ctrlKey === true || e.metaKey === true)) ||
            // Allow: home, end, left, right, down, up
            (e.keyCode >= 35 && e.keyCode <= 40)) {
            // let it happen, don't do anything
            return;
        }
        // Ensure that it is a number and stop the keypress
        if ((e.shiftKey || (e.keyCode < 48 || e.keyCode > 57)) && (e.keyCode < 96 || e.keyCode > 105)) {
            e.preventDefault();
        }
    });

    jQuery("#wc_settings_cerasis_residential_delivery").closest('tr').addClass("wc_settings_cerasis_residential_delivery");
    jQuery("#avaibility_auto_residential").closest('tr').addClass("avaibility_auto_residential");
    jQuery("#avaibility_lift_gate").closest('tr').addClass("avaibility_lift_gate");
    jQuery("#wc_settings_cerasis_lift_gate_delivery").closest('tr').addClass("wc_settings_cerasis_lift_gate_delivery");
    jQuery("#cerasis_freights_liftgate_delivery_as_option").closest('tr').addClass("cerasis_freights_liftgate_delivery_as_option");
    jQuery("#wc_settings_cerasis_notify").closest('tr').addClass("wc_settings_cerasis_notify");
    jQuery("#cerasis_freights_notify_as_option").closest('tr').addClass("cerasis_freights_notify_as_option");


    /**
     * Offer lift gate delivery as an option and Always include residential delivery fee
     * @returns {undefined}
     */

    jQuery(".checkbox_fr_add").on("click", function () {
        var id = jQuery(this).attr("id");
        if (id == "wc_settings_cerasis_lift_gate_delivery") {
            jQuery("#cerasis_freights_liftgate_delivery_as_option").prop({checked: false});
            jQuery("#en_woo_addons_liftgate_with_auto_residential").prop({checked: false});

        } else if (id == "cerasis_freights_liftgate_delivery_as_option" ||
            id == "en_woo_addons_liftgate_with_auto_residential") {
            jQuery("#wc_settings_cerasis_lift_gate_delivery").prop({checked: false});
        }
    });

    /**
     * Global tranz notify
     */
    jQuery(".checkbox_notify").on("click", function () {
        var id = jQuery(this).attr("id");
        if (id == "wc_settings_cerasis_notify") {
            jQuery("#cerasis_freights_notify_as_option").prop({checked: false});

        } else if (id == "cerasis_freights_notify_as_option") {
            jQuery("#wc_settings_cerasis_notify").prop({checked: false});
        }

    });

    jQuery(".checkbox_fr_add_fm").on("click", function () {
        var id = jQuery(this).attr("id");
        if (id == "en_cerasis_threshold_checkbox") {
            jQuery("#en_cerasis_room_of_choice_checkbox").prop({checked: false});
            jQuery("#en_cerasis_premium_checkbox").prop({checked: false});

        } else if (id == "en_cerasis_room_of_choice_checkbox") {
            jQuery("#en_cerasis_threshold_checkbox").prop({checked: false});
            jQuery("#en_cerasis_premium_checkbox").prop({checked: false});
        } else if (id == "en_cerasis_premium_checkbox") {
            jQuery("#en_cerasis_threshold_checkbox").prop({checked: false});
            jQuery("#en_cerasis_room_of_choice_checkbox").prop({checked: false});
        }
    });

    var url = getUrlVarsCerasisFreight()["tab"];
    if (url === 'cerasis_freights') {
        jQuery('#footer-left').attr('id', 'wc-footer-left');
    }
    //Restrict Handling Fee with 8 digits limit
    jQuery("#wc_settings_cerasis_hand_free_mark_up").attr('maxlength', '8');

    jQuery(".cerasis_connection_section_class .button-primary, .cerasis_connection_section_class .is-primary").click(function () {
        var input = gtzValidateInput('.cerasis_connection_section_class');
        if (input === false) {
            return false;
        }
    });
    jQuery(".cerasis_connection_section_class .woocommerce-save-button").before('<a href="javascript:void(0)" class="button-primary is-primary en_globalTranz_test_connection">Test connection</a>');
    jQuery('.en_globalTranz_test_connection').click(function (e) {
        var input = gtzValidateInput('.cerasis_connection_section_class');
        if (input === false) {
            return false;
        }

        var postForm = {
            'wc_cerasis_shipper_id': jQuery('#wc_settings_cerasis_shipper_id').val(),
            'wc_cerasis_username': jQuery('#wc_settings_cerasis_username').val(),
            'wc_cerasis_password': jQuery('#wc_settings_cerasis_password').val(),
            'wc_cerasis_licence_key': jQuery('#wc_settings_cerasis_licence_key').val(),
            'authentication_key': jQuery('#wc_settings_cerasis_authentication_key').val(),
            // GlobalTranz
            'wc_global_tranz_username': jQuery('#wc_settings_global_tranz_username').val(),
            'wc_global_tranz_password': jQuery('#wc_settings_global_tranz_password').val(),
            'wc_global_tranz_authentication_key': jQuery('#wc_settings_global_tranz_authentication_key').val(),
            'wc_global_tranz_customer_id': jQuery('#wc_settings_global_tranz_customer_id').val(),
            'cerasis_global_tranz_api_endpoint': jQuery('#cerasis_global_tranz_api_endpoint').val(),
            'action': 'test_connection_call',
            
            // New API
            'wc_gtz_new_api_client_id': jQuery('#wc_gtz_new_api_client_id').val(),
            'wc_gtz_new_api_client_secret': jQuery('#wc_gtz_new_api_client_secret').val(),
            'wc_gtz_new_api_username': jQuery('#wc_gtz_new_api_api_username').val(),
            'wc_gtz_new_api_password': jQuery('#wc_gtz_new_api_api_password').val(),
        };

        const gtzNewApiFields = ['wc_gtz_new_api_client_id', 'wc_gtz_new_api_client_secret', 'wc_gtz_new_api_api_username', 'wc_gtz_new_api_api_password'];

        jQuery.ajax({
            type: 'POST',
            url: ajaxurl,
            data: postForm,
            dataType: 'json',
            beforeSend: function () {

                jQuery(".en_globalTranz_test_connection").css("color", "#fff");
                jQuery(".cerasis_connection_section_class .button-primary").css("cursor", "pointer");
                jQuery('#wc_settings_cerasis_shipper_id').css('background', 'rgba(255, 255, 255, 1) url("' + en_globaltranz_admin_script.plugins_url + '/ltl-freight-quotes-globaltranz-edition/assets/icons/processing.gif") no-repeat scroll 50% 50%');
                jQuery('#wc_settings_cerasis_username').css('background', 'rgba(255, 255, 255, 1) url("' + en_globaltranz_admin_script.plugins_url + '/ltl-freight-quotes-globaltranz-edition/assets/icons/processing.gif") no-repeat scroll 50% 50%');
                jQuery('#wc_settings_cerasis_password').css('background', 'rgba(255, 255, 255, 1) url("' + en_globaltranz_admin_script.plugins_url + '/ltl-freight-quotes-globaltranz-edition/assets/icons/processing.gif") no-repeat scroll 50% 50%');
                jQuery('#wc_settings_cerasis_authentication_key').css('background', 'rgba(255, 255, 255, 1) url("' + en_globaltranz_admin_script.plugins_url + '/ltl-freight-quotes-globaltranz-edition/assets/icons/processing.gif") no-repeat scroll 50% 50%');
                jQuery('#wc_settings_cerasis_licence_key').css('background', 'rgba(255, 255, 255, 1) url("' + en_globaltranz_admin_script.plugins_url + '/ltl-freight-quotes-globaltranz-edition/assets/icons/processing.gif") no-repeat scroll 50% 50%');

                // GlobalTranz
                jQuery('#wc_settings_global_tranz_username').css('background', 'rgba(255, 255, 255, 1) url("' + en_globaltranz_admin_script.plugins_url + '/ltl-freight-quotes-globaltranz-edition/assets/icons/processing.gif") no-repeat scroll 50% 50%');
                jQuery('#wc_settings_global_tranz_password').css('background', 'rgba(255, 255, 255, 1) url("' + en_globaltranz_admin_script.plugins_url + '/ltl-freight-quotes-globaltranz-edition/assets/icons/processing.gif") no-repeat scroll 50% 50%');
                jQuery('#wc_settings_global_tranz_authentication_key').css('background', 'rgba(255, 255, 255, 1) url("' + en_globaltranz_admin_script.plugins_url + '/ltl-freight-quotes-globaltranz-edition/assets/icons/processing.gif") no-repeat scroll 50% 50%');
                jQuery('#wc_settings_global_tranz_customer_id').css('background', 'rgba(255, 255, 255, 1) url("' + en_globaltranz_admin_script.plugins_url + '/ltl-freight-quotes-globaltranz-edition/assets/icons/processing.gif") no-repeat scroll 50% 50%');

                // New API
                for (const field of gtzNewApiFields) {
                    jQuery('#' + field).css('background', 'rgba(255, 255, 255, 1) url("' + en_globaltranz_admin_script.plugins_url + '/ltl-freight-quotes-globaltranz-edition/assets/icons/processing.gif") no-repeat scroll 50% 50%');
                }
            },
            success: function (data) {

                // New API
                for (const field of gtzNewApiFields) {
                    jQuery('#' + field).css('background', '#fff');
                }
                
                if (data.success) {
                    jQuery(".updated").hide();
                    jQuery('#wc_settings_cerasis_shipper_id').css('background', '#fff');
                    jQuery('#wc_settings_cerasis_username').css('background', '#fff');
                    jQuery('#wc_settings_cerasis_password').css('background', '#fff');
                    jQuery('#wc_settings_cerasis_authentication_key').css('background', '#fff');
                    jQuery('#wc_settings_cerasis_licence_key').css('background', '#fff');

                    // GlobalTranz
                    jQuery('#wc_settings_global_tranz_username').css('background', '#fff');
                    jQuery('#wc_settings_global_tranz_password').css('background', '#fff');
                    jQuery('#wc_settings_global_tranz_authentication_key').css('background', '#fff');
                    jQuery('#wc_settings_global_tranz_customer_id').css('background', '#fff');

                    jQuery(".class_success_message").remove();
                    jQuery(".class_error_message").remove();
                    jQuery(".cerasis_connection_section_class .button-primary, .cerasis_connection_section_class .is-primary").attr("disabled", false);
                    jQuery('.warning-msg-ltl').before('<p class="class_success_message" ><b> Success! The test resulted in a successful connection. </b></p>');
                } else {
                    jQuery(".updated").hide();
                    jQuery(".class_error_message").remove();
                    jQuery('#wc_settings_cerasis_shipper_id').css('background', '#fff');
                    jQuery('#wc_settings_cerasis_username').css('background', '#fff');
                    jQuery('#wc_settings_cerasis_password').css('background', '#fff');
                    jQuery('#wc_settings_cerasis_authentication_key').css('background', '#fff');
                    jQuery('#wc_settings_cerasis_licence_key').css('background', '#fff');

                    // GlobalTranz
                    jQuery('#wc_settings_global_tranz_username').css('background', '#fff');
                    jQuery('#wc_settings_global_tranz_password').css('background', '#fff');
                    jQuery('#wc_settings_global_tranz_authentication_key').css('background', '#fff');
                    jQuery('#wc_settings_global_tranz_customer_id').css('background', '#fff');

                    jQuery(".class_success_message").remove();
                    jQuery(".cerasis_connection_section_class .button-primary, .cerasis_connection_section_class .is-primary").attr("disabled", false);
                    if (data.error_desc) {
                        jQuery('.warning-msg-ltl').before('<p class="class_error_message" ><b>Error! ' + data.error_desc + ' </b></p>');
                    } else {
                        jQuery('.warning-msg-ltl').before('<p class="class_error_message" ><b>Error! Your test connection failed. ' + data.error + ' </b></p>');
                    }
                }

            }

        });
        e.preventDefault();
    });
    // fdo va
    jQuery('#fd_online_id_gtz').click(function (e) {
        var postForm = {
            'action': 'gtz_fd',
            'company_id': jQuery('#freightdesk_online_id').val(),
            'disconnect': jQuery('#fd_online_id_gtz').attr("data")
        }
        var id_lenght = jQuery('#freightdesk_online_id').val();
        var disc_data = jQuery('#fd_online_id_gtz').attr("data");
        if(typeof (id_lenght) != "undefined" && id_lenght.length < 1) {
            jQuery(".class_error_message").remove();
            jQuery('.user_guide_fdo').before('<div class="notice notice-error class_error_message"><p><strong>Error!</strong> FreightDesk Online ID is Required.</p></div>');
            return;
        }
        jQuery.ajax({
            type: "POST",
            url: ajaxurl,
            data: postForm,
            beforeSend: function () {
                jQuery('#freightdesk_online_id').css('background', 'rgba(255, 255, 255, 1) url("' + en_globaltranz_admin_script.plugins_url + '/ltl-freight-quotes-globaltranz-edition/assets/icons/processing.gif") no-repeat scroll 50% 50%');
            },
            success: function (data_response) {
                if(typeof (data_response) == "undefined"){
                    return;
                }
                var fd_data = JSON.parse(data_response);
                jQuery('#freightdesk_online_id').css('background', '#fff');
                jQuery(".class_error_message").remove();
                if((typeof (fd_data.is_valid) != 'undefined' && fd_data.is_valid == false) || (typeof (fd_data.status) != 'undefined' && fd_data.is_valid == 'ERROR')) {
                    jQuery('.user_guide_fdo').before('<div class="notice notice-error class_error_message"><p><strong>Error! ' + fd_data.message + '</strong></p></div>');
                }else if(typeof (fd_data.status) != 'undefined' && fd_data.status == 'SUCCESS') {
                    jQuery('.user_guide_fdo').before('<div class="notice notice-success class_success_message"><p><strong>Success! ' + fd_data.message + '</strong></p></div>');
                    window.location.reload(true);
                }else if(typeof (fd_data.status) != 'undefined' && fd_data.status == 'ERROR') {
                    jQuery('.user_guide_fdo').before('<div class="notice notice-error class_error_message"><p><strong>Error! ' + fd_data.message + '</strong></p></div>');
                }else if (fd_data.is_valid == 'true') {
                    jQuery('.user_guide_fdo').before('<div class="notice notice-error class_error_message"><p><strong>Error!</strong> FreightDesk Online ID is not valid.</p></div>');
                } else if (fd_data.is_valid == 'true' && fd_data.is_connected) {
                    jQuery('.user_guide_fdo').before('<div class="notice notice-error class_error_message"><p><strong>Error!</strong> Your store is already connected with FreightDesk Online.</p></div>');

                } else if (fd_data.is_valid == true && fd_data.is_connected == false && fd_data.redirect_url != null) {
                    window.location = fd_data.redirect_url;
                } else if (fd_data.is_connected == true) {
                    jQuery('#con_dis').empty();
                    jQuery('#con_dis').append('<a href="#" id="fd_online_id_gtz" data="disconnect" class="button-primary is-primary">Disconnect</a>')
                }
            }
        });
        e.preventDefault();
    });

    jQuery('.cerasis_connection_section_class .form-table').before('<div class="warning-msg-ltl"><p> <b>Note!</b> You must have a GlobalTranz account to use this application. If you do not have one contact GlobalTranz at <a href="tel:866-275-1407">866-275-1407</a> or <a href="https://www.globaltranz.com/contact/request-a-quote/" target="_blank">register online</a>. </p>');

    jQuery('.carrier_section_class .woocommerce-save-button').on('click', function () {
        jQuery(".updated").hide();
        var num_of_checkboxes = jQuery('.carrier_check:checked').length;
        if (num_of_checkboxes < 1) {
            jQuery(".carrier_section_class:first-child").before('<div id="message" class="error inline no_srvc_select"><p><strong>Please select at least one carrier service.</strong></p></div>');

            jQuery('html, body').animate({
                'scrollTop': jQuery('.no_srvc_select').position().top
            });
            return false;
        }
    });

    jQuery('.engtz_quote_section_main_class_ltl .button-primary, .engtz_quote_section_main_class_ltl .is-primary').on('click', function () {
        jQuery(".updated").hide();
        jQuery('.error').remove();

        if (!engtz_pallet_weight_validation()) {
            return false;
        } else if (!engtz_pallet_max_weight_validation()) {
            return false;
        }

        // backup rates validations
        if (jQuery('#enable_backup_rates_gtz_ltl').is(':checked')) {
            let error_msg = '', field_id = '';
            if (jQuery('#gtz_ltl_backup_rates_label').val() == '') {
                error_msg = 'Backup rates label field is empty.';
                field_id = 'gtz_ltl_backup_rates_label';
            }

            const number_regex = /^([0-9]{1,4})$|(\.[0-9]{1,2})$/;
            const cart_price_regex = /^([0-9]{1,3}%?)$|(\.[0-9]{1,2})%?$/;

            if (!error_msg) {
                const backup_rates_type = jQuery('input[name="gtz_ltl_backup_rates_category"]:checked').val();
                if (backup_rates_type == 'fixed_rate' && jQuery('#gtz_ltl_backup_rates_fixed_rate').val() == '') {
                    error_msg = 'Backup rates as a fixed rate field is empty.';
                    field_id = 'gtz_ltl_backup_rates_fixed_rate';
                } else if (backup_rates_type == 'percentage_of_cart_price' && jQuery('#gtz_ltl_backup_rates_cart_price_percentage').val() == '') {
                    error_msg = 'Backup rates as a percentage of cart price field is empty.';
                    field_id = 'gtz_ltl_backup_rates_cart_price_percentage';
                } else if (backup_rates_type == 'function_of_weight' && jQuery('#gtz_ltl_backup_rates_weight_function').val() == '') {
                    error_msg = 'Backup rates as a function of weight field is empty.';
                    field_id = 'gtz_ltl_backup_rates_weight_function';
                } else if (jQuery('#gtz_ltl_backup_rates_fixed_rate').val() != '' && !number_regex.test(jQuery('#gtz_ltl_backup_rates_fixed_rate').val())) {
                    error_msg = 'Backup rates as a fixed rate format should be 100.20 or 10.';
                    field_id = 'gtz_ltl_backup_rates_fixed_rate';
                } else if (jQuery('#gtz_ltl_backup_rates_cart_price_percentage').val() != '' && !cart_price_regex.test(jQuery('#gtz_ltl_backup_rates_cart_price_percentage').val())) {
                    error_msg = 'Backup rates as a percentage of cart price format should be 100.20 or 10%.';
                    field_id = 'gtz_ltl_backup_rates_cart_price_percentage';
                } else if (jQuery('#gtz_ltl_backup_rates_weight_function').val() != '' && !number_regex.test(jQuery('#gtz_ltl_backup_rates_weight_function').val())) {
                    error_msg = 'Backup rates as a function of weight format should be 100.20 or 10.';
                    field_id = 'gtz_ltl_backup_rates_weight_function';
                }
            }

            if (error_msg) {
                jQuery(".updated").hide();
                jQuery("#mainform .engtz_quote_section_main_class_ltl").first().prepend('<div id="message" class="error inline no_backup_rates"><p><strong>' + error_msg + '</strong></p></div>');
                jQuery('html, body').animate({
                    'scrollTop': jQuery('#' + field_id).position().top
                });
                return false;
            }
        }

        if (jQuery('#wc_settings_cerasis_hand_free_mark_up').length) {
            var handling_fee = jQuery('#wc_settings_cerasis_hand_free_mark_up').val();
            var num_of_checkboxes = jQuery('.global_tranz_quotes_services:checked').length;
            var num_of_checkboxes_rating = jQuery('#service_global_tranz_rating_method_ch_unch:checked').length;
            var cerasis_global_tranz_api_endpoint = jQuery('#cerasis_global_tranz_api_endpoint_quote_settings').val();
            if (num_of_checkboxes < 1 && cerasis_global_tranz_api_endpoint == 'wc_global_tranz_api_fields' && num_of_checkboxes_rating < 1) {
                jQuery(".updated").hide();
                jQuery("#mainform .engtz_quote_section_main_class_ltl").first().prepend('<div id="message" class="error inline no_srvc_select"><p><strong>Please select at least one quote service.</strong></p></div>');
                jQuery('html, body').animate({
                    'scrollTop': jQuery('.no_srvc_select').position().top
                });
                return false;
            }

            if (handling_fee.slice(handling_fee.length - 1) == '%') {
                handling_fee = handling_fee.slice(0, handling_fee.length - 1)
            }
            if (handling_fee === "") {
                return true;
            } else {
                if (isValidNumber(handling_fee) === false) {

                    jQuery("#mainform .engtz_quote_section_main_class_ltl").first().prepend('<div id="message" class="error inline handlng_fee_error"><p><strong>Handling fee format should be 100.20 or 10%.</strong></p></div>');
                    jQuery('html, body').animate({
                        'scrollTop': jQuery('.handlng_fee_error').position().top
                    });
                    return false;
                } else if (isValidNumber(handling_fee) === 'decimal_point_err') {
                    jQuery("#mainform .engtz_quote_section_main_class_ltl").first().prepend('<div id="message" class="error inline handlng_fee_error"><p><strong>Handling fee format should be 100.2000 or 10% and only 4 digits are allowed after decimal</strong></p></div>');
                    jQuery('html, body').animate({
                        'scrollTop': jQuery('.handlng_fee_error').position().top
                    });
                    return false;
                } else {
                    return true;
                }
            }
        }
    });

    var all_checkboxes = jQuery('.carrier_check');
    if (all_checkboxes.length === all_checkboxes.filter(":checked").length) {
        jQuery('.include_all').prop('checked', true);
    }

    jQuery(".include_all").change(function () {
        if (this.checked) {
            jQuery(".carrier_check").each(function () {
                this.checked = true;
            })
        } else {
            jQuery(".carrier_check").each(function () {
                this.checked = false;
            })
        }
    });
    /**
     * EN apply coupon code send an API call to FDO server
     */
     jQuery(".en_fdo_gtz_ltl_apply_promo_btn").on("click", function (e) {
        jQuery.ajax({
            type: "POST",
            url: ajaxurl,
            data: {action: 'en_gtz_ltl_fdo_apply_coupon',
                    coupon: this.getAttribute('data-coupon')
                    },
            success: function (resp) {
                response = JSON.parse(resp);
                if(response.status == 'error'){
                    jQuery('.en_fdo_gtz_ltl_apply_promo_btn').after('<p id="en_fdo_gtz_ltl_apply_promo_error_p" class="en-error-message">'+response.message+'</p>');
                    setTimeout(function(){
                        jQuery("#en_fdo_gtz_ltl_apply_promo_error_p").fadeOut(500);
                    }, 5000)
                }else{
                    window.location.reload(true);
                }
            }
        });

        e.preventDefault();
    });

    /**
     * EN apply coupon code send an API call to Validate addresses server
     */
     jQuery(".en_va_gtz_ltl_apply_promo_btn").on("click", function (e) {
        jQuery.ajax({
            type: "POST",
            url: ajaxurl,
            data: {action: 'en_gtz_ltl_va_apply_coupon',
                    coupon: this.getAttribute('data-coupon')
                    },
            success: function (resp) {
                response = JSON.parse(resp);
                if(response.status == 'error'){
                    jQuery('.en_va_gtz_ltl_apply_promo_btn').after('<p id="en_va_gtz_ltl_apply_promo_error_p" class="en-error-message">'+response.message+'</p>');
                    setTimeout(function(){
                        jQuery("#en_va_gtz_ltl_apply_promo_error_p").fadeOut(500);
                    }, 5000)
                }else{
                    window.location.reload(true);
                }
            }
        });
        e.preventDefault();
    });
    /*
     * Uncheck Select All Checkbox
     */

    jQuery(".carrier_check").on('change load', function () {
        var int_checkboxes = jQuery('.carrier_check:checked').length;
        var int_un_checkboxes = jQuery('.carrier_check').length;
        if (int_checkboxes === int_un_checkboxes) {
            jQuery('.include_all').prop('checked', true);
        } else {
            jQuery('.include_all').prop('checked', false);
        }
    });

    //      Changed
    var wc_settings_cerasis_rate_method = jQuery("#wc_settings_cerasis_rate_method").val();
    if (wc_settings_cerasis_rate_method == 'Cheapest') {
        jQuery("#wc_settings_cerasis_Number_of_options").closest('tr').addClass("cerasis_Number_of_options_class");
        jQuery("#wc_settings_cerasis_Number_of_options").closest('tr').css("display", "none");
    }

    jQuery("#wc_settings_cerasis_rate_method").change(function () {
        var rating_method = jQuery(this).val();

        if (rating_method == 'Cheapest') {

            jQuery(".cerasis_delivery_estimate").removeAttr('style');
            jQuery(".cerasis_Number_of_label_as").removeAttr('style');
            jQuery(".cerasis_Number_of_options_class").removeAttr('style');

            jQuery("#wc_settings_cerasis_Number_of_options").closest('tr').addClass("cerasis_Number_of_options_class");
            jQuery("#wc_settings_cerasis_Number_of_options").closest('tr').css("display", "none");
            jQuery("#wc_settings_cerasis_label_as").closest('tr').addClass("cerasis_Number_of_label_as");
            jQuery("#wc_settings_cerasis_delivery_estimate").closest('tr').addClass("cerasis_delivery_estimate");
            jQuery("#wc_settings_cerasis_rate_method").closest('tr').addClass("cerasis_rate_mehod");

            jQuery('.cerasis_rate_mehod td p').html('Displays only the cheapest returned Rate.');
            jQuery('.cerasis_Number_of_label_as td span').html('What the user sees during checkout, e.g. Freight. Leave blank to display the carrier name.');

        }
        if (rating_method == 'cheapest_options') {

            jQuery(".cerasis_delivery_estimate").removeAttr('style');
            jQuery(".cerasis_Number_of_label_as").removeAttr('style');
            jQuery(".cerasis_Number_of_options_class").removeAttr('style');

            jQuery("#wc_settings_cerasis_delivery_estimate").closest('tr').addClass("cerasis_delivery_estimate");
            jQuery("#wc_settings_cerasis_label_as").closest('tr').addClass("cerasis_Number_of_label_as");
            jQuery("#wc_settings_cerasis_label_as").closest('tr').css("display", "none");
            jQuery("#wc_settings_cerasis_Number_of_options").closest('tr').addClass("cerasis_Number_of_options_class");
            jQuery("#wc_settings_cerasis_rate_method").closest('tr').addClass("cerasis_rate_mehod");

            jQuery('.cerasis_rate_mehod td p').html('Displays a list of a specified number of least expensive options.');
            jQuery('.cerasis_Number_of_options_class td p').html('Number of options to display in the shopping cart.');
        }
        if (rating_method == 'average_rate') {

            jQuery(".cerasis_delivery_estimate").removeAttr('style');
            jQuery(".cerasis_Number_of_label_as").removeAttr('style');
            jQuery(".cerasis_Number_of_options_class").removeAttr('style');

            jQuery("#wc_settings_cerasis_delivery_estimate").closest('tr').addClass("cerasis_delivery_estimate");
            jQuery("#wc_settings_cerasis_delivery_estimate").closest('tr').css("display", "none");
            jQuery("#wc_settings_cerasis_label_as").closest('tr').addClass("cerasis_Number_of_label_as");
            jQuery("#wc_settings_cerasis_Number_of_options").closest('tr').addClass("cerasis_Number_of_options_class");
            jQuery("#wc_settings_cerasis_rate_method").closest('tr').addClass("cerasis_rate_mehod");

            jQuery('.cerasis_rate_mehod td p').html('Displays a single rate based on an average of a specified number of least expensive options.');
            jQuery('.cerasis_Number_of_options_class td p').html('Number of options to include in the calculation of the average.');
            jQuery('.cerasis_Number_of_label_as td span').html('What the user sees during checkout, e.g. Freight. If left blank will default to Freight.');
        }
    });

    jQuery('#cerasis_global_tranz_shipping_service').on('change', function () {
        cerasis_global_tranz_shipping_service();
    });

    jQuery("#wc_settings_cerasis_delivery_estimate").closest('table').addClass("engtz_quote_section_class_ltl en_cerasis_global_tranz_settings");
    jQuery("#service_gt_estimates_title").closest('table').addClass("engtz_quote_section_class_ltl");
    jQuery("#wc_settings_cerasis_hand_free_mark_up").closest('table').addClass("engtz_quote_section_class_ltl");
    jQuery("#cerasis_global_tranz_shipping_service").closest('table').addClass("engtz_quote_section_class_ltl");
    jQuery("#en_cerasis_premium_label").closest('table').addClass("engtz_quote_section_class_ltl en_cerasis_final_mile_settings");

    cerasis_global_tranz_shipping_service();

    jQuery('.cerasis_connection_section_class input[type="text"]').each(function () {
        if (jQuery(this).parent().find('.err').length < 1) {
            jQuery(this).after('<span class="err"></span>');
        }
    });

    // GlobalTranz
    jQuery('#wc_settings_global_tranz_username').attr('title', 'Username');
    jQuery('#wc_settings_global_tranz_password').attr('title', 'Password');
    jQuery('#wc_settings_global_tranz_authentication_key').attr('title', 'Access Key');
    jQuery('#wc_settings_global_tranz_customer_id').attr('title', 'Customer ID');

    jQuery('#wc_settings_cerasis_shipper_id').attr('title', 'Shipper ID');
    jQuery('#wc_settings_cerasis_username').attr('title', 'Username');
    jQuery('#wc_settings_cerasis_password').attr('title', 'Password');
    jQuery('#wc_settings_cerasis_authentication_key').attr('title', 'Access Key');
    jQuery('#wc_settings_cerasis_licence_key').attr('title', 'Eniture API Key');
    jQuery('#wc_settings_cerasis_allow_for_own_arrangment').attr('title', 'Text For Own Arrangement');
    jQuery('#wc_settings_cerasis_hand_free_mark_up').attr('title', 'Handling Fee / Markup');
    jQuery('#wc_settings_cerasis_label_as').attr('title', 'Label As');
    
    // GTZ New API
    jQuery("#wc_gtz_new_api_client_id").attr('minlength', '1');
    jQuery("#wc_gtz_new_api_client_id").attr('maxlength', '100');
    jQuery("#wc_gtz_new_api_client_secret").attr('minlength', '1');
    jQuery("#wc_gtz_new_api_client_secret").attr('maxlength', '100');
    jQuery("#wc_gtz_new_api_api_username").attr('maxlength', '100');
    jQuery("#wc_gtz_new_api_api_password").attr('maxlength', '100');

    jQuery("#en_wd_origin_markup, #en_ds_origin_markup, ._en_product_markup").bind("cut copy paste",function(e) {
        e.preventDefault();
     });
    
    jQuery("#en_wd_origin_markup, #en_ds_origin_markup,._en_product_markup").keypress(function (e) {
        if (!String.fromCharCode(e.keyCode).match(/^[-0-9\d\.%\s]+$/i)) return false;
    });

    jQuery("#en_wd_origin_markup, #en_ds_origin_markup, ._en_product_markup").keydown(function (e) {
        if ((e.keyCode === 109 || e.keyCode === 189) && (jQuery(this).val().length>0) )  return false;
        if (e.keyCode === 53) if (e.shiftKey) if (jQuery(this).val().length == 0) return false; 
        
        if ((jQuery(this).val().indexOf('.') != -1) && (jQuery(this).val().substring(jQuery(this).val().indexOf('.'), jQuery(this).val().indexOf('.').length).length > 2)) {
            if (e.keyCode !== 8 && e.keyCode !== 46) { //exception
                e.preventDefault();
            }
        }

        // Allow: backspace, delete, tab, escape, enter and .
        if (jQuery.inArray(e.keyCode, [46, 8, 9, 27, 13, 110, 190, 53, 189]) !== -1 ||
            // Allow: Ctrl+A, Command+A
            (e.keyCode === 65 && (e.ctrlKey === true || e.metaKey === true)) ||
            // Allow: home, end, left, right, down, up
            (e.keyCode >= 35 && e.keyCode <= 40)) {
            // let it happen, don't do anything
            return;
        }
        // Ensure that it is a number and stop the keypress
        if ((e.shiftKey || (e.keyCode < 48 || e.keyCode > 57)) && (e.keyCode < 96 || e.keyCode > 105)) {
            e.preventDefault();
        }

        if(jQuery(this).val().length > 7){
            e.preventDefault();
        }
    });

    jQuery("#en_wd_origin_markup, #en_ds_origin_markup, ._en_product_markup").keyup(function (e) {
        var val = jQuery(this).val();

        if (val.length && val.includes('%')) {
            jQuery(this).val(val.substring(0, val.indexOf('%') + 1));
        }

        if (val.split('.').length - 1 > 1) {
            var newval = val.substring(0, val.length - 1);
            var countDots = newval.substring(newval.indexOf('.') + 1).length;
            newval = newval.substring(0, val.length - countDots - 1);
            jQuery(this).val(newval);
        }

        if (val.split('%').length - 1 > 1) {
            var newval = val.substring(0, val.length - 1);
            var countPercentages = newval.substring(newval.indexOf('%') + 1).length;
            newval = newval.substring(0, val.length - countPercentages - 1);
            jQuery(this).val(newval);
        }

        if (val.split('-').length - 1 > 1) {
            var newval = val.substring(0, val.length - 1);
            var countPercentages = newval.substring(newval.indexOf('-') + 1).length;
            newval = newval.substring(0, val.length - countPercentages - 1);
            jQuery(this).val(newval);
        }
    });
    
    jQuery('#wc_gtz_new_api_client_id').attr('title', 'Client ID');
    jQuery('#wc_gtz_new_api_client_secret').attr('title', 'Client Secret');
    jQuery('#wc_gtz_new_api_api_username').attr('title', 'Username');
    jQuery('#wc_gtz_new_api_api_password').attr('title', 'Password');

    // limited access delivery
    jQuery('#gtz_limited_access_delivery').closest('tr').addClass('gtz_limited_access_delivery');
    jQuery('#gtz_limited_access_delivery_as_option').closest('tr').addClass('gtz_limited_access_delivery_as_option');
    jQuery('#gtz_limited_access_delivery_fee').closest('tr').addClass('gtz_limited_access_delivery_fee');

    // limited access
    jQuery(".gtz_limited_access_add").on("change", function () {
        const id = jQuery(this).attr("id");
        id == 'gtz_limited_access_delivery' ?
            jQuery('#gtz_limited_access_delivery_as_option').prop({ checked: false }) :
            jQuery('#gtz_limited_access_delivery').prop({ checked: false });

        jQuery('.gtz_limited_access_delivery_fee').css('display', this.checked ? '' : 'none');
    });

    if (jQuery("#gtz_limited_access_delivery_as_option").prop("checked") == false &&
        jQuery("#gtz_limited_access_delivery").prop("checked") == false) {
        jQuery('.gtz_limited_access_delivery_fee').css('display', 'none');
    }

    // limited access delivery fee
    jQuery("#gtz_limited_access_delivery_fee").keypress(function (e) {
        if (!String.fromCharCode(e.keyCode).match(/^[0-9\d\.\s]+$/i)) return false;
    });

    jQuery('#gtz_limited_access_delivery_fee').keyup(function () {
		var val = jQuery(this).val();
		if (val.length > 7) {
			val = val.substring(0, 7);
			jQuery(this).val(val);
		}
	});

    jQuery('#gtz_limited_access_delivery_fee').keyup(function () {
		var val = jQuery(this).val();
		var regex = /\./g;
		var count = (val.match(regex) || []).length;
		
        if (count > 1) {
			val = val.replace(/\.+$/, '');
			jQuery(this).val(val);
		}
    });
    
    jQuery('#wc_settings_cerasis_residential_delivery').on('change', function (e)
    {
        const checked = e.target.checked;
        if (checked) {
            jQuery('#gtz_limited_access_delivery').prop('disabled', true);
            jQuery('#gtz_limited_access_delivery').prop('checked', false);
        } else {
            jQuery('#gtz_limited_access_delivery').prop('disabled', false);
        }
    });

    if (jQuery('#wc_settings_cerasis_residential_delivery').is(":checked")) {
        jQuery('#gtz_limited_access_delivery').prop('disabled', true);
        jQuery('#gtz_limited_access_delivery').prop('checked', false);
    }

    if (jQuery('#gtz_limited_access_delivery').is(":checked")) {
        jQuery('#wc_settings_cerasis_residential_delivery').prop('disabled', true);
        jQuery('#wc_settings_cerasis_residential_delivery').prop('checked', false);
    }
    
    // Product variants settings
    jQuery(document).on("click", "._nestedMaterials", function(e) {
        const checkbox_class = jQuery(e.target).attr("class");
        const name = jQuery(e.target).attr("name");
        const checked = jQuery(e.target).prop('checked');

        if (checkbox_class?.includes('_nestedMaterials')) {
            const id = name?.split('_nestedMaterials')[1];
            setNestMatDisplay(id, checked);
        }
    });

    // Callback function to execute when mutations are observed
    const handleMutations = (mutationList) => {
        let childs = [];
        for (const mutation of mutationList) {
            childs = mutation?.target?.children;
            if (childs?.length) setNestedMaterialsUI();
          }
    };
    const observer = new MutationObserver(handleMutations),
        targetNode = document.querySelector('.woocommerce_variations.wc-metaboxes'),
        config = { childList: true, subtree: true };
    if (targetNode) observer.observe(targetNode, config);

});

// Weight threshold for LTL freight
if (typeof en_weight_threshold_limit != 'function') {
    function en_weight_threshold_limit() {
        // Weight threshold for LTL freight
        jQuery("#en_weight_threshold_lfq").keypress(function (e) {
            if (String.fromCharCode(e.keyCode).match(/[^0-9]/g) || !jQuery("#en_weight_threshold_lfq").val().match(/^\d{0,3}$/)) return false;
        });

        jQuery('#en_plugins_return_LTL_quotes').on('change', function () {
            if (jQuery('#en_plugins_return_LTL_quotes').prop("checked")) {
                jQuery('tr.en_weight_threshold_lfq').css('display', 'contents');
                jQuery('tr.en_suppress_parcel_rates').css('display', '');
            } else {
                jQuery('tr.en_weight_threshold_lfq').css('display', 'none');
                jQuery('tr.en_suppress_parcel_rates').css('display', 'none');
            }
        });

        jQuery("#en_plugins_return_LTL_quotes").closest('tr').addClass("en_plugins_return_LTL_quotes_tr");
        // Weight threshold for LTL freight
        var weight_threshold_class = jQuery("#en_weight_threshold_lfq").attr("class");
        jQuery("#en_weight_threshold_lfq").closest('tr').addClass("en_weight_threshold_lfq " + weight_threshold_class);

        // Weight threshold for LTL freight is empty
        if (jQuery('#en_weight_threshold_lfq').length && !jQuery('#en_weight_threshold_lfq').val().length > 0) {
            jQuery('#en_weight_threshold_lfq').val(150);
        }

         // Suppress parcel rates when thresold is met
         jQuery(".en_suppress_parcel_rates").closest('tr').addClass("en_suppress_parcel_rates");
         if (!jQuery("#en_plugins_return_LTL_quotes").is(":checked")) {
             jQuery('tr.en_suppress_parcel_rates').css('display', 'none');
         } 
    }
}

function isValidNumber(value, noNegative) {
    if (typeof (noNegative) === 'undefined')
        noNegative = false;
    var isValidNumber = false;
    var validNumber = (noNegative == true) ? parseFloat(value) >= 0 : true;
    if ((value == parseInt(value) || value == parseFloat(value)) && (validNumber)) {
        if (value.indexOf(".") >= 0) {
            var n = value.split(".");
            if (n[n.length - 1].length <= 4) {
                isValidNumber = true;
            } else {
                isValidNumber = 'decimal_point_err';
            }
        } else {
            isValidNumber = true;
        }
    }
    return isValidNumber;
}

// Revoke special chars
function globaltranz_lfq_stop_special_characters(e) {
    // Allow: backspace, delete, tab, escape, enter and .
    if (jQuery.inArray(e.keyCode, [46, 9, 27, 13, 110, 190, 189]) !== -1 ||
        // Allow: Ctrl+A, Command+A
        (e.keyCode === 65 && (e.ctrlKey === true || e.metaKey === true)) ||
        // Allow: home, end, left, right, down, up
        (e.keyCode >= 35 && e.keyCode <= 40)) {
        // let it happen, don't do anything
        e.preventDefault();
        return;
    }
    // Ensure that it is a number and stop the keypress
    if ((e.shiftKey || (e.keyCode < 48 || e.keyCode > 90)) && (e.keyCode < 96 || e.keyCode > 105) && e.keyCode != 186 && e.keyCode != 8) {
        e.preventDefault();
    }
    if (e.keyCode == 186 || e.keyCode == 190 || e.keyCode == 189 || (e.keyCode > 64 && e.keyCode < 91)) {
        e.preventDefault();
        return;
    }
}

function cerasis_global_tranz_shipping_service() {
    var shipping_service = jQuery('#cerasis_global_tranz_shipping_service').val();
    if ('wc_final_mile_service' == shipping_service) {
        jQuery('table.en_cerasis_global_tranz_settings').css("display", "none");
        jQuery('table.en_cerasis_final_mile_settings').css("display", "block");
    } else {
        jQuery('table.en_cerasis_global_tranz_settings').css("display", "block");
        jQuery('table.en_cerasis_final_mile_settings').css("display", "none")
        jQuery('#wc_settings_cerasis_rate_method, #wc_settings_cerasis_Number_of_options, #wc_settings_cerasis_label_as').prop('disabled', false);
        jQuery('#wc_settings_cerasis_rate_method, #wc_settings_cerasis_Number_of_options, #wc_settings_cerasis_label_as').removeClass('disabled_me');
    }
}

function gtzValidateInput(form_id) {
    var has_err = true;
    var api_endpoint = jQuery('#cerasis_global_tranz_api_endpoint').val();
    jQuery(form_id + " input[type='text']").each(function () {
        if (jQuery(this).hasClass(api_endpoint)) {
            var input = jQuery(this).val();
            var response = validateString(input);

            var errorElement = jQuery(this).parent().find('.err');
            jQuery(errorElement).html('');
            var errorText = jQuery(this).attr('title');
            var optional = jQuery(this).data('optional');
            optional = (optional === undefined) ? 0 : 1;
            errorText = (errorText != undefined) ? errorText : '';
            if ((optional == 0) && (response == false || response == 'empty')) {
                errorText = (response == 'empty') ? errorText + ' is required.' : 'Invalid input.';
                jQuery(errorElement).html(errorText);
            }
            has_err = (response != true && optional == 0) ? false : has_err;
        }
    });
    return has_err;
}

function validateString(string) {
    if (string == '') {
        return 'empty';
    } else {
        return true;
    }
}

// Update plan
if (typeof engtz_update_plan != 'function') {
    function engtz_update_plan(input) {
        let action = jQuery(input).attr('data-action');
        jQuery.ajax({
            type: "POST",
            url: ajaxurl,
            data: {action: action},
            success: function (data_response) {
                window.location.reload(true);
            }
        });
    }
}

/**
 * Read a page's GET URL variables and return them as an associative array.
 */
function cerasis_global_tranz_connection_section_api_endpoint() {
    var api_endpoint = jQuery('#cerasis_global_tranz_api_endpoint').val();
    switch (api_endpoint) {
        case 'wc_global_tranz_api_fields':
            jQuery('.wc_cerasis_api_fields').closest('tr').hide();
            jQuery('.wc_global_tranz_new_api_fields').closest('tr').hide();
            jQuery('.wc_global_tranz_api_fields').closest('tr').show();
            break;
        case 'wc_global_tranz_new_api_fields':
            jQuery('.wc_cerasis_api_fields').closest('tr').hide();
            jQuery('.wc_global_tranz_api_fields').closest('tr').hide();
            jQuery('.wc_global_tranz_new_api_fields').closest('tr').show();
            break;
        default:
            jQuery('.wc_global_tranz_api_fields').closest('tr').hide();
            jQuery('.wc_global_tranz_new_api_fields').closest('tr').hide();
            jQuery('.wc_cerasis_api_fields').closest('tr').show();
            break;
    }
}

function getUrlVarsCerasisFreight() {
    var vars = [], hash;
    var hashes = window.location.href.slice(window.location.href.indexOf('?') + 1).split('&');
    for (var i = 0; i < hashes.length; i++) {
        hash = hashes[i].split('=');
        vars.push(hash[0]);
        vars[hash[0]] = hash[1];
    }
    return vars;
}

function engtz_pallet_weight_validation() {
    var weight_of_handling_unit = jQuery('#engtz_freight_handling_weight').val();
    if (typeof weight_of_handling_unit != 'undefined' && weight_of_handling_unit.length > 0) {
        var validResponse = isValidDecimal(weight_of_handling_unit, 'engtz_freight_handling_weight');
    } else {
        validResponse = true;
    }
    if (validResponse) {
        return true;
    } else {
        jQuery("#mainform .engtz_quote_section_main_class_ltl").first().before('<div id="message" class="error inline engtz_freight_pallet_weight_error"><p><strong>Error! </strong>Weight of Handling Unit format should be like, e.g. 48.5 and only 2 digits are allowed after decimal point. The value can be up to 20,000.</p></div>');
        jQuery('html, body').animate({
            'scrollTop': jQuery('.engtz_freight_pallet_weight_error').position().top
        });
        jQuery("#engtz_freight_handling_weight").css({'border-color': '#e81123'});
        return false;
    }
}

function engtz_pallet_max_weight_validation() {
    var max_weight_of_handling_unit = jQuery('#engtz_freight_maximum_handling_weight').val();
    if (typeof max_weight_of_handling_unit != 'undefined' && max_weight_of_handling_unit.length > 0) {
        var validResponse = isValidDecimal(max_weight_of_handling_unit, 'engtz_freight_maximum_handling_weight');
    } else {
        validResponse = true;
    }
    if (validResponse) {
        return true;
    } else {
        jQuery("#mainform .engtz_quote_section_main_class_ltl").first().before('<div id="message" class="error inline engtz_freight_pallet_max_weight_error"><p><strong>Error! </strong>Maximum Weight per Handling Unit format should be like, e.g. 48.5 and only 2 digits are allowed after decimal point. The value can be up to 20,000.</p></div>');
        jQuery('html, body').animate({
            'scrollTop': jQuery('.engtz_freight_pallet_max_weight_error').position().top
        });
        jQuery("#engtz_freight_maximum_handling_weight").css({'border-color': '#e81123'});
        return false;
    }
}

/**
 * Check is valid number
 * @param num
 * @param selector
 * @param limit | LTL weight limit 20K
 * @returns {boolean}
 */
function isValidDecimal(num, selector, limit = 20000) {
    // validate the number:
    // positive and negative numbers allowed
    // just - sign is not allowed,
    // -0 is also not allowed.
    if (parseFloat(num) === 0) {
        // Change the value to zero
        return false;
    }

    const reg = /^(-?[0-9]{1,5}(\.\d{1,4})?|[0-9]{1,5}(\.\d{1,4})?)$/;
    let isValid = false;
    if (reg.test(num)) {
        isValid = inRange(parseFloat(num), -limit, limit);
    }
    if (isValid === true) {
        return true;
    }
    return isValid;
}


function en_gtz_ltl_fdo_connection_status_refresh(input) {
    jQuery.ajax({
        type: "POST",
        url: ajaxurl,
        data: {action: 'en_gtz_ltl_fdo_connection_status_refresh'},
        success: function (data_response) {
            window.location.reload(true);
        }
    });
}

function en_gtz_ltl_va_connection_status_refresh(input) {
    jQuery.ajax({
        type: "POST",
        url: ajaxurl,
        data: {action: 'en_gtz_ltl_va_connection_status_refresh'},
        success: function (data_response) {
            window.location.reload(true);
        }
    });
}

if (typeof setNestedMaterialsUI != 'function') {
    function setNestedMaterialsUI() {
        const nestedMaterials = jQuery('._nestedMaterials');
        const productMarkups = jQuery('._en_product_markup');
        
        if (productMarkups?.length) {
            for (const markup of productMarkups) {
                jQuery(markup).attr('maxlength', '7');

                jQuery(markup).keypress(function (e) {
                    if (!String.fromCharCode(e.keyCode).match(/^[0-9.%-]+$/))
                        return false;
                });
            }
        }

        if (nestedMaterials?.length) {
            for (let elem of nestedMaterials) {
                const className = elem.className;

                if (className?.includes('_nestedMaterials')) {
                    const checked = jQuery(elem).prop('checked'),
                        name = jQuery(elem).attr('name'),
                        id = name?.split('_nestedMaterials')[1];
                    setNestMatDisplay(id, checked);
                }
            }
        }
    }
}

if (typeof setNestMatDisplay != 'function') {
    function setNestMatDisplay (id, checked) {
        
        jQuery(`input[name="_nestedPercentage${id}"]`).attr('min', '0');
        jQuery(`input[name="_nestedPercentage${id}"]`).attr('max', '100');
        jQuery(`input[name="_nestedPercentage${id}"]`).attr('maxlength', '3');
        jQuery(`input[name="_maxNestedItems${id}"]`).attr('min', '0');
        jQuery(`input[name="_maxNestedItems${id}"]`).attr('max', '100');
        jQuery(`input[name="_maxNestedItems${id}"]`).attr('maxlength', '3');

        jQuery(`input[name="_nestedPercentage${id}"], input[name="_maxNestedItems${id}"]`).keypress(function (e) {
            if (!String.fromCharCode(e.keyCode).match(/^[0-9]+$/))
                return false;
        });

        jQuery(`input[name="_nestedPercentage${id}"]`).closest('p').css('display', checked ? '' : 'none');
        jQuery(`select[name="_nestedDimension${id}"]`).closest('p').css('display', checked ? '' : 'none');
        jQuery(`input[name="_maxNestedItems${id}"]`).closest('p').css('display', checked ? '' : 'none');
        jQuery(`select[name="_nestedStakingProperty${id}"]`).closest('p').css('display', checked ? '' : 'none');
    }
}