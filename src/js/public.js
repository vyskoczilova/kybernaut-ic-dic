(function($) {

    var last_ico_value = '';

    // https://stackoverflow.com/questions/14042193/how-to-trigger-an-event-in-input-text-after-i-stop-typing-writing
    // $('#element').donetyping(callback[, timeout=1000])
    // Fires callback when a user has finished typing. This is determined by the time elapsed
    // since the last keystroke and timeout parameter or the blur event--whichever comes first.
    //   @callback: function to be called when even triggers
    //   @timeout:  (default=1000) timeout, in ms, to to wait before triggering event if not
    //              caused by blur.
    // Requires jQuery 1.7+
    //
    $.fn.extend({
        donetyping: function(callback,timeout){
            timeout = timeout || 1e3; // 1 second default timeout
            var timeoutReference,
                doneTyping = function(el){
                    if (!timeoutReference) return;
                    timeoutReference = null;
                    callback.call(el);
                };
            return this.each(function(i,el){
                var $el = $(el);
                // Chrome Fix (Use keyup over keypress to detect backspace)
                // thank you @palerdot
                $el.is(':input') && $( document.body ).on('keyup keypress paste', el, function(e){
                    // This catches the backspace button in chrome, but also prevents
                    // the event from triggering too preemptively. Without this line,
                    // using tab/shift+tab will make the focused element fire the callback.
                    if (e.type=='keyup' && e.keyCode!=8) return;

                    // Check if timeout has been set. If it has, "reset" the clock and
                    // start over again.
                    if (timeoutReference) clearTimeout(timeoutReference);
                    timeoutReference = setTimeout(function(){
                        // if we made it here, our timeout has elapsed. Fire the
                        // callback
                        doneTyping(el);
                    }, timeout);
                });
                $el.is(':input') && $( document.body ).on('blur', el, function(){
                    // If we can, fire the event since we're leaving the field
                    doneTyping(el);
                });
            });
        }
    });

    $(document).ready(function() {

        $(document.body).on('input', '.woolab-ic-dic-no_spaces input', function() {
            $(this).val(function(_, v){
                return v.replace(/\s+/g, '');
            });
        });

        /** On init, check if the toggle is present or is set to "on" and run the hiding logic */
        var $fieldToggle = $('#billing_iscomp');
        if ( ! $fieldToggle.length || $fieldToggle.prop("checked") ) {
            restore_company_data();
            based_on_country();
        }

        /** Toggle fields when country is changed or checkbox is toggled */
        $(document.body).on("change", "#billing_country, #billing_iscomp", function () {
            var $fieldToggle = $('#billing_iscomp');
            if ( ! $fieldToggle.length || $fieldToggle.prop("checked") ) {
                restore_company_data();
                based_on_country();
            } else {
                $(".woolab-ic-dic-toggle").slideUp();
                clear_company_data();
            }
        });

        /** Refresh checkout to validate VAT number and VAT exemption */
        $('#billing_dic, #billing_dic_dph').donetyping(function() {
            var country = $("#billing_country").val();
            if (country != "SK" && this.id == "billing_dic") {
                $(document.body).trigger("update_checkout");
            }
            if (country == "SK" && this.id == "billing_dic_dph") {
                $(document.body).trigger("update_checkout");
            }
        }, 750);
    });

    /** Show/Hide logic for woolab-ic-dic fields */
    function based_on_country() {

        if (woolab.ares_fill) {
            clear_validation();
        }

        $("#billing_ic_field").slideDown();
        $("#billing_dic_field").slideDown();

        var country = $("#billing_country").val();
        switch (country) {
            case 'SK':
                $("#billing_dic_dph_field").slideDown();
                $("#billing_dic_field > label").addClass("woolab-ic-dic-required");
                break;
            case 'CZ':
                $("#billing_dic_dph_field").slideUp();
                $("#billing_dic_field > label").removeClass("woolab-ic-dic-required");
                break;
            default:
                $("#billing_dic_dph_field").slideUp();
                $("#billing_dic_field > label").removeClass("woolab-ic-dic-required");
        }
        if (woolab.ares_check) {
            enable_ares_check();
        }
        $("#billing_company_field").slideDown();
    }

    function clear_company_data() {
        var country   = $("#billing_country").val();
        var vat_field = country == 'SK' ? 'billing_dic_dph' : 'billing_dic';
        $("#billing_company, #billing_ic, #billing_dic, #billing_dic_dph").each(function(index,el) {
            el.setAttribute('data-value', el.value);
            if (el.id == vat_field && el.value.length) {
                $(document.body).trigger("update_checkout");
            }
            el.value = '';
        });
    }

    function restore_company_data() {
        var country   = $("#billing_country").val();
        var vat_field = country == 'SK' ? 'billing_dic_dph' : 'billing_dic';
        $("#billing_company, #billing_ic, #billing_dic, #billing_dic_dph").each(function(index,el) {
            if (el.getAttribute('data-value')) {
                el.value = el.getAttribute('data-value');
                if (el.id == vat_field && el.value.length) {
                    $(document.body).trigger("update_checkout");
                }
            }
        });
    }

    function clear_validation() {
        $('.woolab-ic-dic-tip').remove();
        ares_remove_disabled_from_input();
    }

    function woolab_remove_class_ok ( selector ) {
        selector.removeClass( 'kbnt-ok' ).removeClass( 'woocommerce-validated' );
    }
    function woolab_add_class_ok ( selector ) {
        selector.addClass( 'kbnt-ok' ).addClass( 'woocommerce-validated' ).removeClass( 'woocommerce-invalid' );
    }

    function woolab_remove_class_wrong ( selector ) {
        selector.removeClass( 'kbnt-wrong' ).removeClass( 'woocommerce-invalid' );
    }
    function woolab_add_class_wrong ( selector ) {
        selector.addClass( 'kbnt-wrong' ).addClass( 'woocommerce-invalid' ).removeClass( 'woocommerce-validated' );
    }

    function enable_ares_check() {

        var ico = $('#billing_ic');
        ares_check( ico );
        $( document.body ).on('focusin', '#billing_ic', function() {
            last_ico_value = ico.val();
        });
        ico.donetyping( function() {
            ico = $('#billing_ic'); // Because of Fluid Checkout for WooCommerce - Lite compatibility
            if ( ico.val() !== last_ico_value ) {
                ares_check( ico );
            }
        }, 500);

    }

    function ares_remove_disabled_from_input() {
        $('#billing_company').removeAttr("readonly");
        $('#billing_dic').removeAttr("readonly");
        $('#billing_postcode').removeAttr("readonly");
        $('#billing_city').removeAttr("readonly");
        $('#billing_address_1').removeAttr("readonly");
    }

    function ares_check( ico ) {

        // Only if country is CZ.
        if ( $("#billing_country").val() !== 'CZ' ) {
            return;
        }

        var value = ico.val();
        if ( value !== last_ico_value ) {

            var ico_class = $('#billing_ic_field');
            var not_valid = '<span role="alert" class="woolab-ic-dic-tip">'+woolab.l18n_not_valid+'</span>';

            $('.woolab-ic-dic-tip').remove();
            woolab_remove_class_wrong( ico_class );
            woolab_remove_class_ok( ico_class );

            if ( (value.length == 7 || value.length == 8) && value.match(/^[0-9]+$/) != null ) {

                $.ajax({
                    url: woolab.ajaxurl,
                    data: {
                        action: "ajaxAres",
                        'ico' : value,
                    },
                    beforeSend: function() {
                        ico_class.addClass( 'kbnt-validating' );
                        ico_class.append( '<span role="info" class="woolab-ic-dic-tip">'+woolab.l18n_validating+'</span>' );
                    },
                    success: function ( data ) {
                        ico_class.removeClass( 'kbnt-validating' );
                        if ( data ) {
                            var data = JSON.parse( data );

                            if ( data.error == false ) {

                                $('.woolab-ic-dic-tip').remove();
                                woolab_add_class_ok( ico_class );

                                if ( woolab.ares_fill ) {

                                    // Update values
                                    $('#billing_company').val(data.spolecnost).attr('readonly', true);
                                    $('#billing_dic').val(data.dic).attr('readonly', true);
                                    $('#billing_address_1').val(data.adresa).attr('readonly', true);
                                    $('#billing_postcode').val(data.psc).attr('readonly', true);
                                    $('#billing_city').val(data.mesto).attr('readonly', true);
                                    ico_class.append( '<span role="info" class="woolab-ic-dic-tip">'+woolab.l18n_ok+'</span>' );

                                    // Trigger the checkout update
                                    $('body').trigger('update_checkout');

                                }

                            } else {
                                ares_error( ico_class );
                                if ( $('.woolab-ic-dic-tip').length > 0 ) {
                                    $('.woolab-ic-dic-tip').remove();
                                }
                                ares_remove_disabled_from_input();
                                ico_class.append( '<span role="alert" class="woolab-ic-dic-tip error">'+data.error+'</span>' );
                            }

                        } else {
                            ares_error( ico_class );
                            if ( $('.woolab-ic-dic-tip').length == 0 ) {
                                ares_remove_disabled_from_input();
                                ico_class.append( not_valid );
                            }
                        }


                    },
                    error: function(errorThrown){
                        if ( $('.woolab-ic-dic-tip').length == 0 ) {
                            ico.val('');
                            ares_error( ico_class );
                            ico_class.append( '<span role="alert" class="woolab-ic-dic-tip error">'+woolab.l18n_error+'</span>' );
                        }
                    }
                });

            } else {
                ares_remove_disabled_from_input();
                if ( value.length > 0 ) {
                    woolab_add_class_wrong( ico_class );
                } else {
                    woolab_remove_class_wrong( ico_class );
                }
            }
            last_ico_value = ico.val();
        }
    }

    function ares_error ( ico_class ) {

        if ( woolab.ares_fill ) {
            $('#billing_company').val('');
            $('#billing_dic').val('');
            $('#billing_postcode').val('');
            $('#billing_city').val('');
            $('#billing_address_1').val('');
            ares_remove_disabled_from_input();
        }
        woolab_add_class_wrong( ico_class );
    }

})( jQuery );
