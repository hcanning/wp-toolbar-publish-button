( function( $ ) {


    $.fn.extend({

        duplicateButton: function ( bclass )
        {
            var bclass = ' ' + bclass || '';

            if ( $(this).length ) {

                var val;


                if ( this.is('input') ) {
                    val = $(this).val();
                }
                else if ( this.is('a') ) {
                    val = $(this).html();
                }


                var id = $(this).attr( 'id' ),
                    content = '<li id="wp-admin-bar-wpuxss-tpb-' + id + '"  class="wpuxss-tpb' + bclass + '"><a class="ab-item wpuxss-tpb-button" href="#" id="top-toolbar-submit" for="' + id + '">' + val + '</a></li>';


                if ( tpb_l10n.buttons_to_the_right ) {

                    if ( ! $('#wpuxss-tpb-buttons-right').length ) 
                        $('.ab-top-secondary.ab-top-menu').after('<ul id="wpuxss-tpb-buttons-right" class="ab-top-secondary ab-top-menu"></ul>');
                    
                    el = $('.ab-top-secondary').last();
                }
                else {
                    el = $('.ab-top-menu').first();
                }

                if ( el.children('.wpuxss-tpb').length )
                    el.children('.wpuxss-tpb').last().after( content );
                else if ( el.children('#wp-admin-bar-new-content').length )
                    el.children('#wp-admin-bar-new-content').after( content );
                else
                    el.append( content );


                return true;
            }
            return false;
        }

    });



    function addStyle(styles) {
          
        // create style document
        var css = document.createElement('style');
        css.type = 'text/css';
      
        if (css.styleSheet) 
            css.styleSheet.cssText = styles;
        else 
            css.appendChild(document.createTextNode(styles));
          
        // append style
        document.getElementsByTagName("head")[0].appendChild(css);
    }



    $( document ).ready( function() {

        // input[type="button"] was excluded since v1.6
        // because it's causing issues to some jquery plugins
        var button = $('#wpbody-content .wrap input[type="submit"].button-primary').not('.find-box input, .widget-control-save, #screen-options-wrap input, #bulk_edit'),
            post_view_button = $('#wp-admin-bar-view');


        if ( ! button.attr( 'id' ) )
            button.first().attr( 'id','tpb_publish' );
        button.first().duplicateButton( 'wpuxss-tpb-publish' );



        if ( tpb_l10n.draft_button ) {

            var draft_button = $('#wpbody-content .wrap input[type="submit"]#save-post:visible').not('.find-box input');


            draft_button.first().duplicateButton( 'wpuxss-tpb-save-post' );

            $('.save-post-status').on('click', function() {
                setTimeout( function() {
                    $('.wpuxss-tpb-save-post .ab-item .ab-label').html( draft_button.val() );
                }, 15 );

            });
        }



        if ( tpb_l10n.preview_button ) {

            var preview_button = $('#wpbody-content .wrap a#post-preview');


            preview_button.first().duplicateButton( 'wpuxss-tpb-post-preview' );
        }


        // view post button
        if ( tpb_l10n.buttons_to_the_right ) {  
            post_view_button.appendTo('#wpuxss-tpb-buttons-right');
        }
        
        post_view_button.children('.ab-item').parent('li').addClass('wpuxss-tpb');


        // set the style
        var styles = ( '' !== tpb_l10n.button_bg ) ? ' #wpadminbar:not(.mobile) #wp-toolbar li.wpuxss-tpb > .ab-item { background-color: ' + tpb_l10n.button_bg + '}' : '';
        styles += ' #wpadminbar .wpuxss-tpb .ab-item { padding: 0 7px; margin-left: 1px; }';
        styles += ' #wpadminbar:not(.mobile) #wp-toolbar li.wpuxss-tpb > .ab-item { color: #fff; }';
        styles += ' #wpadminbar:not(.mobile) #wp-toolbar li.wpuxss-tpb:hover > .ab-item, #wpadminbar:not(.mobile) #wp-toolbar li.wpuxss-tpb:focus > .ab-item, #wpadminbar:not(.mobile) #wp-toolbar li.wpuxss-tpb:active > .ab-item { filter: brightness(1.1); }';
        styles += ' #wpadminbar #wpuxss-tpb-buttons-right > li {float: left; }';
        styles += ' #wpadminbar #wpuxss-tpb-buttons-right { margin-right: 10px; }';
    
        addStyle(styles);


        $('#wpadminbar .wpuxss-tpb a.wpuxss-tpb-button').on( 'click', function(e)
        {
            e.preventDefault();
            $('#'+$(this).attr('for')).click();
        });

    });

})( jQuery );
