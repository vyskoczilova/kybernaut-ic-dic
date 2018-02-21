(function($) {

    $(document).ready(function() {
                                 
        // enable active only if ares_check filled out
        var ares_check = $('#woolab_icdic_ares_check');

        if ( ares_check.length ) {
            enableActive( ares_check );
            ares_check.change( function(){
                enableActive( ares_check );
            });
        }

    });

    function enableActive( ares_check ) {
        
        var active = $('#woolab_icdic_ares_fill');
        if ( ares_check.prop( "checked" ) == true) {
            active.prop("disabled", false);
        } else {
            active.prop("disabled", true).prop( "checked", false );
        }

    }
	
})( jQuery );