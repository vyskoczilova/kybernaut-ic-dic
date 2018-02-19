(function($) {

    var cssc = [];
    cssc['validating'] = 'ares-validating';
    cssc['wrong'] = 'ares-wrong';
    cssc['ok'] = 'ares-ok';

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
    
    function enable_ares_check() {

        var ico = $('#billing_ic');
        ares_check( ico );
        ico.bind('input propertychange', function() {
            ares_check( ico );
        });

    }

    function ares_remove_disabled_from_input() {
        $('#billing_company').removeAttr("disabled");
        $('#billing_dic').removeAttr("disabled");
        $('#billing_postcode').removeAttr("disabled");
        $('#billing_city').removeAttr("disabled");
        $('#billing_address_1').removeAttr("disabled");
    }

    function ares_check( ico ) {
        var value = ico.val();
        var ico_class = $('#billing_ic_field');
        var not_valid = '<span role="alert" class="woolab-ic-dic-tip">'+woolab.l18n_not_valid+'</span>';

        ico_class.removeClass( cssc.ok ).removeClass( cssc.wrong ).removeClass(cssc.wrong);            

        if ( (value.length == 7 || value.length == 8) && value.match(/^[0-9]+$/) != null ) {  
            
            $.ajax({
                url: woolab.ajaxurl,
                data: {
                    action: "ajaxAres",
                    'ico' : value,
                },
                beforeSend: function() {
                    ico_class.addClass(cssc.validating);
                },
                success: function ( data ) {
                    ico_class.removeClass(cssc.validating);
                    if ( data ) {
                        var data = JSON.parse( data );

                        if ( data.error == false ) {

                            $('.woolab-ic-dic-tip').remove();
                            ico_class.addClass( cssc.ok ); 

                            if ( woolab.ares_fill ) {
                                $('#billing_company').val(data.spolecnost).attr('disabled', 'disabled');
                                $('#billing_dic').val(data.dic).attr('disabled', 'disabled');
                                $('#billing_address_1').val(data.adresa).attr('disabled', 'disabled');
                                $('#billing_postcode').val(data.psc).attr('disabled', 'disabled');
                                $('#billing_city').val(data.mesto).attr('disabled', 'disabled');
                                ico_class.append( '<span role="info" class="woolab-ic-dic-tip">'+woolab.l18n_ok+'</span>' ); 
                            }                    

                        } else {
                            ares_error( ico_class );
                            if ( $('.woolab-ic-dic-tip').length > 0 ) {
                                $('.woolab-ic-dic-tip').remove();                                    
                            }
                            ico_class.append( '<span role="alert" class="woolab-ic-dic-tip error">'+data.error+'</span>' ); 
                        }

                    } else {                             
                        ares_error( ico_class );   
                        if ( $('.woolab-ic-dic-tip').length == 0 ) {
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
            ico_class.addClass( cssc.wrong );
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

        ico_class.addClass( cssc.wrong );
    }
	
})( jQuery );