<?php
/**
 * Eniture Warehouse Template
 */
if (!defined('ABSPATH')) {
    exit;
}


?>
<script type="text/javascript">
    jQuery(document).ready(function () {
        window.location.href = jQuery('.close').attr('href');
    });

    function engtz_wd_add_warehouse_btn() {
        jQuery('#add_warehouses')[0].reset();
        jQuery('#edit_form_id').val('');
        jQuery("#engtz_wd_origin_zip").val('');
        jQuery('.city_select').hide();
        jQuery('.city_input').show();
        jQuery('#engtz_wd_origin_city').css('background', 'none');
        jQuery("#engtz_wd_origin_city").val('');
        jQuery("#engtz_wd_origin_state").val('');
        // Origin terminal address
        jQuery("#en_wd_origin_address").val('');
        jQuery("#engtz_wd_origin_country").val('');
        jQuery('.engtz_wd_zip_validation_err').hide();
        jQuery('.engtz_wd_city_validation_err').hide();
        jQuery('.engtz_wd_state_validation_err').hide();
        jQuery('.engtz_wd_country_validation_err').hide();
        jQuery('.not_allowed').hide();
        jQuery('.zero_results').hide();
        jQuery('.already_exist').hide();
        jQuery('.wrng_credential').hide();
        jQuery('#add_warehouses').find("input[type='text']").val("");
        jQuery(".engtz_wd_err").html("");
        jQuery('#add_warehouses').find("input[type='checkbox']").prop('checked', false);
        jQuery('#instore-pickup-zipmatch .tag-i, #local-delivery-zipmatch .tag-i').trigger('click');

        setTimeout(function () {
            if (jQuery('.engtz_wd_add_warehouse_popup').is(':visible')) {
                jQuery('.engtz_wd_add_warehouse_input > input').eq(0).focus();
            }
        }, 100);
    }

    function change_warehouse_zip() {
        if (jQuery("#engtz_wd_origin_zip").val() == '') {
            return false;
        }

        jQuery('#engtz_wd_origin_city').css('background', 'rgba(255, 255, 255, 1) url("<?php echo ENGTZ_DIRPATH;?>/includes/warehouse-dropship/wild/assets/images/processing.gif") no-repeat scroll 50% 50%');
        jQuery('#engtz_wd_origin_state').css('background', 'rgba(255, 255, 255, 1) url("<?php echo ENGTZ_DIRPATH;?>/includes/warehouse-dropship/wild/assets/images/processing.gif") no-repeat scroll 50% 50%');
        jQuery('.city_select_css').css('background', 'rgba(255, 255, 255, 1) url("<?php echo ENGTZ_DIRPATH;?>/includes/warehouse-dropship/wild/assets/images/processing.gif") no-repeat scroll 50% 50%');
        jQuery('#engtz_wd_origin_country').css('background', 'rgba(255, 255, 255, 1) url("<?php echo ENGTZ_DIRPATH;?>/includes/warehouse-dropship/wild/assets/images/processing.gif") no-repeat scroll 50% 50%');

        var postForm = {
            'action': 'engtz_wd_get_address',
            'origin_zip': jQuery('#engtz_wd_origin_zip').val(),
        };

        jQuery.ajax({
            type: 'POST',
            url: ajaxurl,
            data: postForm,
            dataType: 'json',
            beforeSend: function () {
                jQuery('.engtz_wd_zip_validation_err').hide();
                jQuery('.engtz_wd_city_validation_err').hide();
                jQuery('.engtz_wd_state_validation_err').hide();
                jQuery('.engtz_wd_country_validation_err').hide();
            },
            success: function (data) {
                if (data) {
                    if (data.country === 'US' || data.country === 'CA') {
                        if (data.postcode_localities == 1) {
                            jQuery('.city_select').show();
                            jQuery('#actname').replaceWith(data.city_option);
                            jQuery('.engtz_wd_multi_state').replaceWith(data.city_option);
                            jQuery('.city-multiselect').change(function () {
                                setCity(this);
                            });
                            jQuery('#engtz_wd_origin_city').val(data.first_city);
                            jQuery('#engtz_wd_origin_state').val(data.state);
                            jQuery('#engtz_wd_origin_country').val(data.country);
                            jQuery('#engtz_wd_origin_state').css('background', 'none');
                            jQuery('.city_select_css').css('background', 'none');
                            jQuery('#engtz_wd_origin_country').css('background', 'none');
                            jQuery('.city_input').hide();
                        } else {
                            jQuery('.city_input').show();
                            jQuery('#_city').removeAttr('value');
                            jQuery('.city_select').hide();
                            jQuery('#engtz_wd_origin_city').val(data.city);
                            jQuery('#engtz_wd_origin_state').val(data.state);
                            jQuery('#engtz_wd_origin_country').val(data.country);
                            jQuery('#engtz_wd_origin_city').css('background', 'none');
                            jQuery('#engtz_wd_origin_state').css('background', 'none');
                            jQuery('#engtz_wd_origin_country').css('background', 'none');
                        }
                    } else if (data.result === 'ZERO_RESULTS') {
                        jQuery('.zero_results').show('slow');
                        jQuery('#engtz_wd_origin_city').css('background', 'none');
                        jQuery('#engtz_wd_origin_state').css('background', 'none');
                        jQuery('#engtz_wd_origin_country').css('background', 'none');
                        setTimeout(function () {
                            jQuery('.zero_results').hide('slow');
                        }, 5000);
                    } else if (data.result === 'false') {
                        jQuery('.zero_results').show('slow').delay(5000).hide('slow');
                        jQuery('#engtz_wd_origin_city').css('background', 'none');
                        jQuery('#engtz_wd_origin_state').css('background', 'none');
                        jQuery('#engtz_wd_origin_country').css('background', 'none');
                        jQuery('#engtz_wd_origin_city').val('');
                        jQuery('#engtz_wd_origin_state').val('');
                        jQuery('#engtz_wd_origin_country').val('');
                    } else if (data.apiResp === 'apiErr') {
                        jQuery('.wrng_credential').show('slow');
                        jQuery('#engtz_wd_origin_city').css('background', 'none');
                        jQuery('#engtz_wd_origin_state').css('background', 'none');
                        jQuery('#engtz_wd_origin_country').css('background', 'none');
                        setTimeout(function () {
                            jQuery('.wrng_credential').hide('slow');
                        }, 5000);
                    } else {
                        jQuery('.not_allowed').show('slow');
                        jQuery('#engtz_wd_origin_city').css('background', 'none');
                        jQuery('#engtz_wd_origin_state').css('background', 'none');
                        jQuery('#engtz_wd_origin_country').css('background', 'none');
                        setTimeout(function () {
                            jQuery('.not_allowed').hide('slow');
                        }, 5000);
                    }
                }
            },
        });
        return false;
    }

    function setCity(e) {
        var city = jQuery(e).val();
        jQuery('#engtz_wd_origin_city').val(city);
    }

    jQuery(function () {
        jQuery('input.alphaonly').keyup(function () {
            if (this.value.match(/[^a-zA-Z ]/g)) {
                this.value = this.value.replace(/[^a-zA-Z ]/g, '');
            }
        });
    });
</script>
<div class="engtz_wd_setting_section">
    <h1><?php esc_html_e("Warehouses", "eniture-technology"); ?></h1><br>

    <?php engtz_warehouse_template(); ?>

    <!-- Add Popup for new warehouse -->
    <div id="engtz_wd_add_warehouse_btn" class="engtz_wd_warehouse_overlay">
        <div class="engtz_wd_add_warehouse_popup">
            <h2 class="warehouse_heading"><?php esc_html_e("Warehouses", "eniture-technology"); ?></h2>
            <a class="close" href="#">&times;</a>
            <div class="content" style="overflow-y: scroll; height: 500px;">
                <div class="already_exist">
                    <?php echo force_balance_tags("<strong>Error!</strong> Zip code already exists.") ?>
                </div>
                <div class="not_allowed">
                    <p><?php echo force_balance_tags("<strong>Error!</strong> Please enter US zip code.") ?></p>
                </div>
                <div class="zero_results">

                    <p><?php echo force_balance_tags("<strong>Error!</strong> Please enter valid US zip code.") ?></p>
                </div>
                <div class="wrng_credential">
                    <p><?php echo force_balance_tags("<strong>Error!</strong> Please verify credentials at connection settings panel.") ?></p>
                </div>
                <div class="wrng_local wrng_standard_pckg">
                    <p><?php echo force_balance_tags("<strong>Error!</strong> Local delivery is enabled you must enter miles or postal codes.") ?></p>
                </div>
                <div class="wrng_instore wrng_standard_pckg">
                    <p><?php echo force_balance_tags("<strong>Error!</strong> in-store pick up is enabled you must enter miles or postal codes.") ?></p>
                </div>

                <!-- Wordpress Form closed -->
                </form>

                <form method="post" id="add_warehouses">
                    <input type="hidden" name="edit_form_id" value="" id="edit_form_id">
                    <!-- Origin terminal address -->
                    <div class="engtz_wd_add_warehouse_input">
                        <label for="engtz_wd_origin_address">Street Address</label>
                        <input type="text" title="Street Address" data-optional="1"
                               name="en_wd_origin_address" data-optional="1"
                               placeholder="320 W. Lanier Ave, Ste 200"
                               id="en_wd_origin_address">
                        <span class="en_wd_err"></span>
                    </div>
                    <div class="engtz_wd_add_warehouse_input">
                        <label for="engtz_wd_origin_zip"><?php esc_html_e("Zip", "eniture-technology"); ?> </label>
                        <input type="text" title="Zip" maxlength="7" onchange="change_warehouse_zip()" value=""
                               name="engtz_wd_origin_zip" placeholder="30214" id="engtz_wd_origin_zip">
                        <span class="engtz_wd_err"></span>
                    </div>
                    <div class="engtz_wd_add_warehouse_input city_input">
                        <label for="engtz_wd_origin_city"><?php esc_html_e("City", "eniture-technology"); ?> </label>
                        <input type="text" class="alphaonly" title="City" value="" name="engtz_wd_origin_city"
                               placeholder="Fayetteville" id="engtz_wd_origin_city">
                        <span class="engtz_wd_err"></span>
                    </div>
                    <div class="engtz_wd_add_warehouse_input city_select">
                        <label for="engtz_wd_origin_city"><?php esc_html_e("City", "eniture-technology"); ?></label>
                        <select id="actname"></select>
                        <span class="engtz_wd_err"></span>
                    </div>
                    <div class="engtz_wd_add_warehouse_input">
                        <label for="engtz_wd_origin_state"><?php esc_html_e("State", "eniture-technology"); ?></label>
                        <input type="text" class="alphaonly" maxlength="2" title="State" value=""
                               name="engtz_wd_origin_state" placeholder="GA" id="engtz_wd_origin_state">
                        <span class="engtz_wd_err"></span>
                    </div>
                    <div class="engtz_wd_add_warehouse_input">
                        <label for="engtz_wd_origin_country"><?php esc_html_e("Country", "eniture-technology"); ?></label>
                        <input type="text" class="alphaonly" maxlength="2" title="Country"
                               name="engtz_wd_origin_country" value="" placeholder="US" id="engtz_wd_origin_country">
                        <input type="hidden" name="engtz_wd_location" value="warehouse" id="engtz_wd_location">
                        <span class="engtz_wd_err"></span>
                    </div>

                    <!-- Origin level handlin fee / markup -->
                    <div class="engtz_wd_add_warehouse_input">
                        <label for="en_wd_origin_markup"><?php esc_html_e("Handling Fee / Markup", "eniture-technology"); ?></label>
                        <input type="text" class="numericonly"  maxlength="8" title="Handling Fee / Markup" name="en_wd_origin_markup"
                               data-optional="1" value="" placeholder="e.g Currency 1.00 or percentage 5%" id="en_wd_origin_markup">
                    </div>

                    <div style="clear: both;"></div>
                    <br>

                    <?php
                    $disabled = "";
                    $package_required = "";
                    $plugin_tab = (isset($_REQUEST['tab'])) ? sanitize_text_field($_REQUEST['tab']) : "";

                    $action_instore = apply_filters($plugin_tab . "_quotes_plans_suscription_and_features", 'instore_pickup_local_devlivery');

                    if (is_array($action_instore)) {
                        $disabled = "disabled_me";
                        $package_required = apply_filters($plugin_tab . "_plans_notification_link", $action_instore);
                    }
                    ?>

                    <!--
                        Instore Pick Up Starts
                    -->
                    <div class="heading">
                        <h2 class="warehouse_heading heading_left"><?php esc_html_e("In-store pick up", "eniture-technology"); ?></h2>
                        <a href="#">
                            <h2 class="warehouse_heading pakage_notify_instore_warehouse heading_right"><?php echo $package_required; ?></h2>
                        </a>
                    </div>

                    <div class="engtz_wd_add_warehouse_input">
                        <label><?php esc_html_e("Enable in-store pick up", "eniture-technology"); ?></label>
                        <div class="pickup-delivery-checkboxes">
                            <input type="checkbox" title="Enable in-store pick up" id="enable-instore-pickup"
                                   data-optional="1" name="enable-instore-pickup" value=""
                                   class="enable-instore-pickup <?php echo esc_attr($disabled); ?>"/>
                        </div>
                    </div>
                    <div class="engtz_wd_add_warehouse_input ">
                        <label><?php esc_html_e("Offer if address is within (miles):", "eniture-technology"); ?></label>
                        <input type="text" title="Offer if address is within (miles):" data-optional="1"
                               onchange="validate_delivery_fee(this);" id="instore-pickup-address"
                               class="<?php echo esc_attr($disabled); ?>" name="instore-pickup-address">
                    </div>
                    <div class="engtz_wd_add_warehouse_tagging">
                        <label><?php esc_html_e("Offer if postal code matches:", "eniture-technology"); ?></label>
                        <div data-tags-input-name="tag" title="Offer if postal code matches:" data-optional="1"
                             id="instore-pickup-zipmatch" value="" name="instore-pickup-zipmatch"
                             class="tagging-js <?php echo esc_attr($disabled); ?>"></div>
                    </div>

                    <div class="engtz_wd_add_warehouse_input">
                        <label><?php esc_html_e("Checkout description:", "eniture-technology"); ?></label>
                        <input type="text" class="<?php echo esc_attr($disabled); ?>" title="Checkout description:"
                               id="instore-pickup-desc" placeholder="In-store pick up" data-optional="1"
                               name="instore-pickup-desc">
                    </div>

                    <!-- Terminal phone number -->
                    <div class="engtz_wd_add_warehouse_input">
                        <label>Phone number:</label>
                        <input type="text" class="<?php echo $disabled; ?> en-phone-number" title="Phone number:"
                               id="en-phone-number"
                               placeholder="404-369-0680" data-optional="1" name="en-phone-number">
                    </div>


                    <div style="clear: both;"></div>
                    <br>

                    <!--
                        Local Delivery Starts
                    -->

                    <div class="heading">
                        <h2 class="local-delivery-heading heading_left"><?php esc_html_e("Local Delivery", "eniture-technology"); ?></h2>
                        <a href="#">
                            <h2 class="local-delivery-heading pakage_notify_local_warehouse heading_right"><?php echo $package_required; ?></h2>
                        </a>
                    </div>

                    <div class="engtz_wd_add_warehouse_input">
                        <label><?php esc_html_e("Enable local delivery", "eniture-technology"); ?></label>
                        <div class="pickup-delivery-checkboxes">
                            <input type="checkbox" title="Enable local delivery" id="enable-local-delivery"
                                   data-optional="1" name="enable-local-delivery" value=""
                                   class="enable-local-delivery <?php echo esc_attr($disabled); ?>"/>
                        </div>
                    </div>
                    <div class="engtz_wd_add_warehouse_input">
                        <label><?php esc_html_e("Offer if address is within (miles):", "eniture-technology"); ?></label>
                        <input type="text" title="Offer if address is within (miles):"
                               class="<?php echo esc_attr($disabled); ?>" data-optional="1"
                               onchange="validate_delivery_fee(this);" id="local-delivery-address"
                               name="local-delivery-address">
                    </div>
                    <div class="engtz_wd_add_warehouse_tagging">
                        <label><?php esc_html_e("Offer if postal code matches:", "eniture-technology"); ?></label>
                        <div data-tags-input-name="tag" title="Offer if postal code matches:" data-optional="1"
                             id="local-delivery-zipmatch" value="" name="local-delivery-zipmatch"
                             class="tagging-js <?php echo esc_attr($disabled); ?>"></div>
                    </div>
                    <div class="engtz_wd_add_warehouse_input">
                        <label><?php esc_html_e("Checkout description:", "eniture-technology"); ?></label>
                        <input id="local-delivery-desc" title="Checkout description:" placeholder="Local delivery"
                               type="text" class="<?php echo esc_attr($disabled); ?>" data-optional="1"
                               name="local-delivery-desc">
                    </div>
                    <div class="engtz_wd_add_warehouse_input">
                        <label><?php esc_html_e("Local delivery fee", "eniture-technology"); ?></label>
                        <input type="text" class="<?php echo esc_attr($disabled); ?>" title="Local delivery fee"
                               data-optional="1" onchange="validate_delivery_fee(this);" id="local-delivery-fee"
                               name="local-delivery-fee">
                        <span class="engtz_wd_err"></span>
                    </div>
                    <div class="engtz_wd_add_warehouse_input">
                        <label><?php esc_html_e("Suppress other rates", "eniture-technology"); ?><span
                                    class="suppress-span"
                                    title="This setting only suppresses rates that would otherwise be returned by the Eniture Technology products.">[?]</span></label>
                        <div class="pickup-delivery-checkboxes <?php echo esc_attr($disabled); ?>">
                            <input type="checkbox" title="Suppress other rates" id="suppress-local-delivery"
                                   name="suppress-local-delivery" value="" class="suppress-local-delivery"/>
                        </div>
                    </div>


                    <!--
                        Local Delivery Ends
                    -->
                    <div class="form-btns">
                        <input type="submit" name="en_wd_submit_warehouse" value="Save" class="save_warehouse_form"
                               onclick="return engtz_wd_save_warehouse();">
                    </div>
                </form>
            </div>
        </div>
    </div>
