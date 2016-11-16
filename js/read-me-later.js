/**
 * Created by marko on 2016-11-13.
 */
jQuery(function(){

    console.log("read-me-later loaded...");

    console.log(readmelater_ajax);

    jQuery('#content').on('click', 'a.rml_bttn', function(e) {
        e.preventDefault();
        var that = jQuery(this);



        var rml_post_id = jQuery(this).data( 'id' );
        jQuery.ajax({
            url : readmelater_ajax.ajax_url,
            type : 'post',
            data : {
                action : 'read_me_later',
                post_id : rml_post_id,
                security : readmelater_ajax.check_nonce
            },
            success : function( response ) {
                jQuery('.rml_contents').html(response);

            }
        });

        that.hide();
    });
});