jQuery(document).ready(function($){

	$( "#user" ).on( "change", function() {
		var optionVal = $(this).val();
  	var userLogin =  $('option[value='+optionVal+']').attr('data-username');
		var userEmail =  $('option[value='+optionVal+']').attr('data-useremail');

		$('#username-error, #username-check').remove();
		$('#useremail-error, #useremail-check').remove();

		if($(this).val() != 0) {
			$( "input[name='user_name']" ).prop('disabled', true);
			$( "input[name='user_name']" ).val(userLogin);
			$( "input[name='user_name']" ).css('background-color', '#e6e6e6');

			$( "input[name='user_email']" ).prop('disabled', true);
			$( "input[name='user_email']" ).val(userEmail);
			$( "input[name='user_email']" ).css('background-color', '#e6e6e6');
		}else {
			$( "input[name='user_name']" ).prop('disabled', false);
			$( "input[name='user_name']" ).css('background-color', '#fff');
			$( "input[name='user_name']" ).val('');

			$( "input[name='user_email']" ).prop('disabled', false);
			$( "input[name='user_email']" ).css('background-color', '#fff');
			$( "input[name='user_email']" ).val('');
		}
	});

	//Ajax call to check if user exist by username
	$("#user").on("focusout", function() {
			var input = $(this);
			var username = $(this).val();
			var check = '<span id="username-check" class="dashicons dashicons-yes" style="color:green"></span>';
			var error = '<span id="username-error" class="dashicons dashicons-no" style="color:red"></span>'
      jQuery.ajax({
         type : "post",
         dataType : "json",
         url : ajaxurl,
         data : {action: "my_user_check", username: username},
         success: function(response) {
            if(response == 0 && input.val().length > 3){
							$('#username-error, #username-check').remove();
							$( input ).after( check );
						}else {
							$('#username-error, #username-check').remove();
							$( input ).after( error );
						}
         }
      })

	    var ajax_url = 'https://www.spstimberwindows.co.uk/staging3/wp-content/themes/rebel-child/inc/enquiry-ajax.php';
	    var form_enquiry_email = $("input[name='user_email']").val();
	    $.post(ajax_url, {form_enquiry_email: form_enquiry_email}, function(data){

	    	var dataCleanUp1 = data.replace('"', '');
	    	var dataCleanUp2 = dataCleanUp1.replace('"', '');
	    	var dataCleanUp3 = dataCleanUp2.replace('[', '');
	    	var dataCleanUp4 = dataCleanUp3.replace(']', '');
	    	var dataCleanUp5 = dataCleanUp4.replace(']', '');
	    	var cleanData    = dataCleanUp5.replace(/\\/g, '');

	    	$("#enquiry_box_option").text(cleanData);
	    	$("#enquiry_box_option").val(cleanData);
	    });     
	});

	document.addEventListener('wpcf7mailsent', function(event) {
	    location.reload();
	}, false );


});
