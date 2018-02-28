(function($) {

    $(document).ready(function() {
                                 
        // Country based
        var country = jQuery('#billing_country').val();
        based_on_country( country );

        $( 'body' ).bind( 'country_to_state_changing', function( event, country, wrapper ){            
            based_on_country( country );
        });


    });

    function based_on_country( country ) {  

        if ( woolab.ares_fill ) {
            clear_validation();
        }

        switch( country ) {
            case 'SK':
                $('#billing_dic_dph_field').show();
                break;
            case 'CZ':
                $('#billing_dic_dph_field').hide();
                if ( woolab.ares_check ) {
                    enable_ares_check();
                }
                break;
            default:
                $('#billing_dic_dph_field').hide();
        }

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
        ico.bind('input propertychange', function() {
            ares_check( ico );
        });

    }

    function ares_remove_disabled_from_input() {
        $('#billing_company').removeAttr("readonly");
        $('#billing_dic').removeAttr("readonly");
        $('#billing_postcode').removeAttr("readonly");
        $('#billing_city').removeAttr("readonly");
        $('#billing_address_1').removeAttr("readonly");
    }

    function ares_check( ico ) {
        var value = ico.val();
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
                },
                success: function ( data ) {
                    ico_class.removeClass( 'kbnt-validating' );
                    if ( data ) {
                        var data = JSON.parse( data );

                        if ( data.error == false ) {

                            $('.woolab-ic-dic-tip').remove(); 
                            woolab_add_class_ok( ico_class );

                            if ( woolab.ares_fill ) {
                                $('#billing_company').val(data.spolecnost).attr('readonly', true);
                                $('#billing_dic').val(data.dic).attr('readonly', true);
                                $('#billing_address_1').val(data.adresa).attr('readonly', true);
                                $('#billing_postcode').val(data.psc).attr('readonly', true);
                                $('#billing_city').val(data.mesto).attr('readonly', true);
                                ico_class.append( '<span role="info" class="woolab-ic-dic-tip">'+woolab.l18n_ok+'</span>' ); 
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