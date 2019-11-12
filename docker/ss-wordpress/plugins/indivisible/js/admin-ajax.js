/**
 * 
 */

function indv_update ($) {
    $.post(indv_ajax_obj.ajax_url, {         //POST request
        _ajax_nonce: indv_ajax_obj.nonce,     //nonce
         action: "indv_action",            //action
         title: this.value,           //data
         data: 'something',
     }, function($data) {                    //callback'
		jQuery("#indv_plugin_update_status").text("Boo!");
		console.log('eureka');
		if ($data != null)
			console.log(JSON.stringify($data));
     });
}