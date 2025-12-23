jQuery( document ).ready( function() {
	jQuery( document ).on( 'click', 'a', function(event) {
        if( jQuery(event.target).hasClass('aep-ac7-never-show')) {
            var data = {
                action: 'aep_ac7_never_show',
            };
        }
		jQuery.post( aep_ac7_notice_params.ajaxurl, data, function() {
            if(data){
                jQuery('.aep-ac7-notice').fadeOut();
            }
		});
    });
});