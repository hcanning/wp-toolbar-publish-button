( function( $ ) {

    $( document ).ready( function() {

        $('#wpuxss-tpb-scrollbar-position input').change();
    });

    $( document ).on( 'change', '#wpuxss-tpb-scrollbar-position input', function( event ) {

        var isChecked = $(this).prop( 'checked' );

        $('#wpuxss-tpb-expand-acf').prop( 'hidden', ! isChecked );
    });

})( jQuery );