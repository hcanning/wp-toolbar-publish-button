window.wpCookies = window.wpCookies || {};



(function($) {

    function saveScrollbarCookies () {

        var scrollTop = 0,
            messageDivsOuterHeight = 0,
            secure = ( 'https:' === window.location.protocol );


        $('.notice').each( function() {
            messageDivsOuterHeight += $(this).outerHeight(true);
        });


        scrollTop = $(window).scrollTop() - messageDivsOuterHeight;

        wpCookies.set( 'tpb-scroll-top', scrollTop, 30 * 24 * 60 * 60, false, false, secure );


        if ( typeof window.acf !== 'undefined' ) {

            var acf_opened_field_groups = [];


            // ACF PRO
            $('.acf-field-object.open').each( function( index ) {
                acf_opened_field_groups.push( $( this ).attr( 'data-key' ) );
            });

            // if not, then ACF free
            if ( ! acf_opened_field_groups.length ) {

                $('.field.form_open').each( function( index ) {
                    acf_opened_field_groups.push( $( this ).attr( 'data-id' ) );
                });
            }

            wpCookies.set( 'tpb-avf-open-field-groups', acf_opened_field_groups, 30 * 24 * 60 * 60, false, false, secure );
        }
    }


    function removeCookies () {

        wpCookies.remove( 'tpb-scroll-top' );
        wpCookies.remove( 'tpb-avf-open-field-groups' );
    }


    var button = $('input[type="submit"].button-primary, input[type="button"].button-primary'),
        draft_button;


    if ( tpb_l10n.draft_button ) {
        draft_button = $('input[type="submit"]#save-post:visible').not('.find-box input');
    }


    button
        .add(draft_button)
        .add('.row-actions.visible .activate a, .row-actions.visible .deactivate a')
        .on( 'click', function(e) {
            saveScrollbarCookies();
        });


    $(window).on( 'load',function( event ) {

        var scrollTimeout = 0,
            currMessageDivOuterHeight = 0,
            c_scrollTop = wpCookies.get( 'tpb-scroll-top' ),
            c_acfOpenFieldGroups = wpCookies.get( 'tpb-avf-open-field-groups' ),
            scrollTop = c_scrollTop ? parseInt( c_scrollTop ) : 0,
            n = $('.acf_wysiwyg').length ? $('.acf_wysiwyg').length : 0,
            acfOpenFieldGroups = c_acfOpenFieldGroups ? c_acfOpenFieldGroups.split(/,/) : 0;


        if ( acfOpenFieldGroups && typeof window.acf !== 'undefined' ) {

            scrollTimeout = 300;

            $.each( acfOpenFieldGroups, function( index, field_key ) {

                // ACF PRO
                if ( typeof window.acf.field_group !== 'undefined' ) {
                    $( '.acf-field-object[data-key=' + field_key + '] .edit-field' ).first().click();
                }
                // if not, then ACF free
                else {
                    $( '.field[data-id=' + field_key + '] .acf_edit_field' ).first().click();
                }
            });
        }
        else {
            scrollTimeout = 15*window.n;
        }

        if ( scrollTop ) {

            setTimeout( function() {

                    $('.notice').each(function() {
                        currMessageDivOuterHeight += $(this).outerHeight(true);
                    });
                    $(window).scrollTop( scrollTop + currMessageDivOuterHeight );

            }, scrollTimeout );
        }

        removeCookies();
    });

})( jQuery );
