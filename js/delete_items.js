
jQuery(document).ready(function(){
    jQuery('.deleteitem').click(function(){
        var abc = jQuery(this).prop('id');
        if(jQuery.isNumeric(abc)){
            jQuery(this).parents('tr').css('display', 'none');
        } else {
            //alert('name');
        }

        //alert(jQuery(this).data('nonce'));

        jQuery('.wcvinforesponse').html('deleting...');

        var request = {
            'id': jQuery(this).prop('id'),
            'nonce': jQuery(this).data('nonce'),
            'action': 'wcv_delete_items'
        };

        jQuery.post(ajaxurl, request, function(response){
            //alert(response);
            //jQuery('.crazyresponze').html(response);
            jQuery('.wcvinforesponse').html(response);
        });
    });
});