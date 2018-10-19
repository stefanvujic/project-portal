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
	$( "input[name='user_name']" ).on( "focusout", function() {
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

	    var ajax_url = $('.site_url').val() + '/wp-content/themes/rebel-child/inc/projects.php';
	    var form_enquiry_email = $("input[name='user_email']").val();
	    $.post(ajax_url, {form_enquiry_email: form_enquiry_email}, function(data){
	        console.log('wdqqwqwqd');
	    });      
	});

	//Ajax call to check if user exist by email
	$( "input[name='user_email']" ).on( "focusout", function() {
			var useremail = $(this).val();
			var input = $(this);
			var check = '<span id="useremail-check" class="dashicons dashicons-yes" style="color:green"></span>';
			var error = '<span id="useremail-error" class="dashicons dashicons-no" style="color:red"></span>'

			function validateEmail(email) {
			    var re = /^(([^<>()\[\]\\.,;:\s@"]+(\.[^<>()\[\]\\.,;:\s@"]+)*)|(".+"))@((\[[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\])|(([a-zA-Z\-0-9]+\.)+[a-zA-Z]{2,}))$/;
			    return re.test(String(email).toLowerCase());
			}
			var isEmail = validateEmail(useremail);

      jQuery.ajax({
         type : "post",
         dataType : "json",
         url : ajaxurl,
         data : {action: "my_user_email_check", useremail: useremail},
         success: function(response) {
					 if(response == 0 && isEmail == true){
						 $('#useremail-error, #useremail-check').remove();
						 $( input ).after( check );
					 }else{
						 $('#useremail-error, #useremail-check').remove();
						 $( input ).after( error );
					 }
         }
      })
	});

	document.addEventListener( 'wpcf7mailsent', function( event ) {
	    location.reload();
	}, false );


});
