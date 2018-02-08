(function($) {

    $(document).ready(function() {
                                 
        var country = jQuery('#billing_country').val();
        hide_show_billing_dic_dph( country );

        $( 'body' ).bind( 'country_to_state_changing', function( event, country, wrapper ){            
            hide_show_billing_dic_dph( country );
        });

    });

    function hide_show_billing_dic_dph( country ) {        
        if ( country == 'SK' ) {
            $('#billing_dic_dph_field').show();
        } else {
            $('#billing_dic_dph_field').hide();
        }
    }
	
})( jQuery );