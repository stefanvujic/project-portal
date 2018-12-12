<?php
////////////////////////PROJECT FUNCTIONS //////////////////

function enqueue_project_admin_styles($hook){
	global $post;
	//Load only on edit and add new pages
	if ( ('post-new.php' == $hook && $_GET['post_type'] =='project') || ('post.php' == $hook && $_GET['action'] =='edit' && $post->post_type == 'project')  ) {
			wp_enqueue_style('project-styles', get_template_directory_uri().'-child/inc/project-admin.css');
			wp_enqueue_script('project-js', get_template_directory_uri() . '-child/inc/project.js', array('jquery'), '', true);
		  wp_localize_script( 'admin-ajax', 'myAjax', array( 'ajaxurl' => admin_url( 'admin-ajax.php' )));
		  wp_enqueue_script( 'admin-ajax' );
	}

}
function enqueue_project_front_end_styles(){
	global $post;
	//TODO: might need to change the post ID when pushed live
	if($post->ID == "6499") {
	wp_enqueue_style('project-styles', get_template_directory_uri().'-child/inc/project.css');
	}
}

//Reload page after submitting Conctact 7 Form
//TODO: might need to change the post ID when pushed live
function reload_page_after_signature(){
	global $post;
	if($post->ID == "6499") {
	?>
	<script>
	document.addEventListener( 'wpcf7mailsent', function( event ) {
	    location.reload();
	}, false );
	</script>
	<?php
	}
}
add_action('wp_footer', 'reload_page_after_signature');

add_action('admin_enqueue_scripts', 'enqueue_project_admin_styles');
add_action('wp_enqueue_scripts', 'enqueue_project_front_end_styles');

//Create Metaboxes
function add_custom_meta_boxes() {
    //Project or enquiry
    add_meta_box(
        'project_or_enquiry',
        'Document Type',
        'project_or_enquiry',
        'project',
        'normal',
        'high'
    );  
    //Select or create user
    add_meta_box(
        'project_user_dropdown',
        'Select Client',
        'project_user_dropdown',
        'project',
        'normal',
        'high'
    );
    add_meta_box(
        'project_customer_details',
        'Customer Details',
        'project_customer_details',
        'project',
        'normal',
        'high'
    );
    wp_enqueue_script('mytabs', get_bloginfo('stylesheet_directory'). '/inc/project-archive.js', array('jquery-ui-tabs'));
    //Attachments
    add_meta_box(
        'project_custom_attachment',
        'Attachments',
        'project_custom_attachment',
        'project',
        'normal'
    );
    //Emails
    add_meta_box(
        'project_emails',
        'Emails',
        'project_sent_emails',
        'project',
        'normal'
    ); 
} // end add_custom_meta_boxes
add_action('add_meta_boxes', 'add_custom_meta_boxes');

function pw_loading_scripts_wrong() {
  ?>
  <script type="text/javascript">
    $(document).ready(function() {
      $("#project_customer_details.postbox").addClass("closed");
      $("#project_custom_attachment.postbox").addClass("closed");
      $("#project_emails.postbox").addClass("closed");
    });
  </script>
  <?php 
}
add_action('admin_head', 'pw_loading_scripts_wrong');

//Edit post fort to accept files
function update_edit_form() {
    echo ' enctype="multipart/form-data" ';
} // end update_edit_form
add_action('post_edit_form_tag', 'update_edit_form');

//Create new role - client
add_role(
    'client',
    __('Client'),
    array(
        'read'         => true,  // true allows this capability
        'edit_posts'   => false,
        'delete_posts' => false, // Use false to explicitly deny
    )
);

function project_or_enquiry() {
  echo '<select name="project_or_enquiry">';
    echo '<option value="enquiry">Enquiry</option>';
    echo '<option value="project">Project</option>';
  echo '</select>';
}

function sha1_url_encrypt($attachment_id) {
global $post;
global $wpdb;

    $random_url_param = randomPassword();
    $encrypted_param = sha1($random_url_param);

    return $encrypted_param;
}

//Create callbacks for metaboxes
function project_user_dropdown() {
global $post;
global $wpdb;

  $client_id = get_post_meta($post->ID, 'client_id', true);

  //Check if this is an existing project or a new if a new show dropdown
  if( empty($client_id) ) {

    $users = get_users();
    $html = '<input type="hidden" name="new_project" value="1">';
    $html .= '<select id="user" name="user">';
    $html .= '<option value="0">Please select a user or create a new</option>';

    foreach ($users as $key => $value) {
      if( $value->ID == $client_id) {
        $selected = "selected";
        $selected_user_display_name = $value->display_name;
        $selected_user_email = $value->user_email;
        $disabled = "disabled";
      }else {
        $selected = "";
        $disabled = "";
      }
      $html .= '<option value="'.$value->ID.'" '.$selected.' data-username="'.$value->user_login.'" data-useremail="'.$value->user_email.'">'.$value->display_name.'</option>';
    }
    $html .= '</select><br>';
    }else {
      $selected_user_display_name = get_userdata($client_id)->display_name;
      $selected_user_email = get_userdata($client_id)->user_email;
      $disabled = "disabled";
    }

    $html .= '<label for="user_name"><b>User Name</b></label></br>';
    $html .=  '<input required autocomplete="off" type="text" name="user_name" size="100" value="'.$selected_user_display_name.'" '.$disabled.'><br></br>';
    $html .= '<label for="user_name"><b>Email</b></label></br>';
    $html .=  '<input required autocomplete="off" type="text" name="user_email" size="100" value="'.$selected_user_email.'"  '.$disabled.'><br>';

    $get_enquiry_form_id = $wpdb->get_results("SELECT lead_id FROM wp_rg_lead_detail WHERE form_id = 2 AND value = '".$selected_user_email."'");
    
    $html .= '<br>';
    $html .= '<br>';
    
    $html .= '<input type="hidden" name="enquiry_formq" value="1" class="enquiry_form">';
    $html .= '<select id="form_id" name="enquiry_form">';

    $current_form_id = get_post_meta($_GET['post'], 'enquiry_form_details', true);

      $get_postcode = $wpdb->get_results("SELECT value FROM wp_rg_lead_detail WHERE field_number = 34 AND lead_id = '".$current_form_id."'");
      $get_surname = $wpdb->get_results("SELECT value FROM wp_rg_lead_detail WHERE field_number = 31 AND lead_id = '".$current_form_id."'");
      $get_date_created = $wpdb->get_results("SELECT date_created FROM wp_rg_lead WHERE id = '".$current_form_id."'");

      $date_created = $get_date_created[0]->date_created;
      $date_created = date("d/m/Y", strtotime($date_created));

    if (!empty($current_form_id)) {
      $html .= '<option id="enquiry_box_option" value="'.$current_form_id.'">'.$current_form_id.'</option>';
    }else {
      $html .= '<option id="enquiry_box_option">Please select enquiry form</option>';
    }

    $html .= '</select><br>';

    echo $html;
}

function project_customer_details() {
    global $post;
    $client_full_name = get_post_meta($post->ID, 'client_full_name', true);
    $client_address = get_post_meta($post->ID, 'client_address', true);
    $client_reference = get_post_meta($post->ID, 'client_reference', true);

    $html .= '<label for="full_name"><b>Customer Name</b></label></br>';
    $html .=  '<input required autocomplete="off" type="text" name="full_name" size="100" value="'.$client_full_name.'"><br></br>';
    $html .= '<label for="user_address"><b>Customer Address</b></label></br>';
    $html .=  '<textarea name="user_address" rows="5" cols="99">'.$client_address.'</textarea></br></br>';
    $html .= '<label for="reference"><b>Reference</b></label></br>';
    $html .=  '<input required autocomplete="off" type="text" name="reference" size="100" value="'.$client_reference.'"><br>';

    echo $html;

}

//Not good but works for now, needs to be changed later
function project_custom_attachment() {
  global $post;
?>
<style type="text/css">
.ui-widget-header {
    border: 1px solid #eeeeee !important;
    background: #eeeeee !important;;
}  
</style>
<?php
	add_thickbox();

  $project_id = $post->ID;
  $client_id = get_post_meta($project_id, 'client_id', true);
  $main_user_email = get_userdata($client_id)->user_email;
  $get_additional_users = get_user_meta(get_current_user_id(), 'additional_users', true);  

  $additional_user = explode(":", $get_additional_users);
  $additional_user_one = $additional_user[0];
  $additional_user_two = $additional_user[1];

  $additional_user_email1 = get_userdata($additional_user_one)->user_email;
  $additional_user_email2 = get_userdata($additional_user_two)->user_email;

  //display existing attachments
  $attachments = get_attached_media( '', $post->ID );
  if( !empty($attachments) ) {
    ?>
    <style type="text/css">
      .toggle {
          width: 100px;
          height: 300px;
          background: #ccc;
          position: absolute;
          right: 20px;
          top: 80px;
        }
    </style>

    <div id="randomss" class="randomss" style="position: absolute; left: 93%; top: 16px;"><b>Send Email</b></div>
    <div id="toggle">
      <form method="post">
        <table>
          <tr><td style="background: #ffffff; border: none;"><b>Recipient</b><br>
          <select id="email_list">
            <option value="Select From Available Emails">Select From Available Emails</option>
            <option value="<?php echo $main_user_email; ?>"><?php echo $main_user_email; ?></option>
            <option value="<?php echo $additional_user_email1; ?>"><?php echo $additional_user_email1; ?></option>
            <option value="<?php echo $additional_user_email2; ?>"><?php echo $additional_user_email2; ?></option>
          </select>
            <br><input style="border: 1px solid #ddd; width: 52%;" type="text" name="projects_general_email_recipient"></td></tr>
          <tr><td style="background: #ffffff; border: none;"><b>Subject</b><br><input style="border: 1px solid #ddd; width: 52%;" type="text" name="projects_general_subject"></td></tr>
          <tr><td style="background: #ffffff; border: none;"><b>Message</b><br><textarea style="margin-bottom: 10px; width: 52%; height: 200px; border: 1px solid #ddd;" type="textarea" name="projects_general_message"></textarea><br><input type="submit" name="submit_general_message"></td></tr>
        </table>
      </form>
    </div>

    <script type="text/javascript">
      $(document).ready(function() {
        $("#toggle").hide();
        $("#randomss").click(function() {
          $("#toggle").toggle("slow");
        });
        $('#email_list').on('change', function () {
          $('input[name="projects_general_email_recipient"]').val($(this).val());
          if ($(this).val() == 'Select From Available Emails') {
            $('input[name="projects_general_email_recipient"]').val('');
          }
        });
      });
    </script>
    <div id="mytabs">
      <ul class="category-tabs">
          <li><a href="#frag1">Attachments</a></li>
          <li><a href="#frag2">Archive</a></li>
      </ul>
      <br class="clear" />
      <div id="frag1">
      <?php
        echo "<table>
                <tr>
                  <th>Title</th>
                  <th>Comment</th>
                  <th>Date</th>
                  <th>Required</th>
    							<th>Signed</th>
                  <th>View</th>
                  <th>Email</th>
                  <th style='white-space: nowrap;'>Login Required</th>
                  <th>Archive</th>
                </tr>";

    		$count=0;
        foreach ($attachments as $attachment => $value) {
          //get required field for attachment
          $required = get_post_meta($value->ID, 'required', true);
    			$signed = get_post_meta($value->ID, 'signed', true);
          $login_required = get_post_meta($value->ID, 'login_required', true);
    			$signature_url = get_post_meta($value->ID, 'signature_url', true);
          $is_archived = get_post_meta($value->ID, 'is_archived', true);

          if (empty($is_archived)) {

      			$thickbox = 'class="thickbox"';
      			if($required == "yes") {
      				if( empty($signed) ) {
      					$signed = "Awaiting";
      					$signature_url ="#";
      					$thickbox = "";
      				}
      			} else {
      				$signed = "N/A";
      				$signature_url ="#";
      				$thickbox = "";
      			}

            if (!empty($login_required)) {
              $checked = "<p style='margin: 0; position: relative; left: 38px;'>&#10004;</p>";
            }else {
              $checked = "";
            }

            $genre_url = site_url().'/wp-admin/options-general.php?page=project_emails&attachment_id='.$value->ID.'&title='.$value->post_title.'&project_id='.$post->ID;

            echo "<tr>
                    <td>
                      ".$value->post_title."
                    </td>
                    <td>
                      ".$value->post_content."
                    </td>
                    <td>
                      ".$value->post_date."
                    </td>
                    <td>
                      ".$required."
                    </td>
      							<td>
      								<a href='".$signature_url."?TB_iframe=true&width=320&height=300' ".$thickbox." >".$signed."</a>
      							</td>
                    <td>
                      <a href='".$value->guid."' target='_blank'>View</a>
                    </td>
                    <td>
                      <a style='white-space: nowrap;' href='".$genre_url."' target='_blank'>Send Email</a>
                    </td>
                    <td>
                      ".$checked."
                    </td>
                    <td>
                      <input style='position: relative; left: 18px;' type='checkbox' name='archive_attachment_id' value='".$value->ID."' id='archive_submit".$value->ID."' ".$checked_archive.">
                    </td>
                    <?php 
                  </tr>";
                            ?>
          <script type="text/javascript">
            $("#archive_submit<?php echo $value->ID; ?>").click(function() {
              $("#post").submit();
            });
          </script>
          <?php
          }
        }
        echo "</table>";
      }

      //Add new attachments
      for ($i=0; $i < 10; $i++) {
        $html = '<br><b>Upload new attachment</b><br><br>';
        $html .= '<label for="attachment_title"><b>Title</b></label></br>';
        $html .= '<input type="text" name="attachment_title_'.$i.'" size="100" /><br>';
        $html .= '<label for="attachment_title"><b>Comment</b></label></br>';
        $html .=  '<textarea name="attachment_comment_'.$i.'" rows="5" cols="99"></textarea></br></br>';
        $html .= '<input type="file" name="custom_attachment_'.$i.'" id="custom_attachment_'.$i.'"  multiple="true" /><br>';
        $html .= '<label for="attachment_required"><b>Acceptance Required For This Attachment?</b></label></br>';
        $html .= '<input type="radio" name="attachment_required_'.$i.'" value="yes">Yes<br><input type="radio" name="attachment_required_'.$i.'" value="no" checked>No<br><br>';
        $html .= '<label for="visible_to_customer"><b>Visible To Customer?</b></label></br>';
        $html .= '<input type="radio" name="visible_to_customer_'.$i.'" value="yes">Yes<br><input type="radio" name="visible_to_customer_'.$i.'" value="no" checked>No<br><br>';
        
        echo $html;
      }
      ?>
    </div>
    <!-- Archive tab -->
    <div class="hidden" id="frag2">
      <?php
        echo "<table>
                <tr>
                  <th>Title</th>
                  <th>Comment</th>
                  <th>Date</th>
                  <th>Required</th>
                  <th>Signed</th>
                  <th>View</th>
                  <th>Email</th>
                  <th style='white-space: nowrap;'>Login Not Required</th>
                </tr>";

        $count=0;
        foreach ($attachments as $attachment => $value) {
          //get required field for attachment
          $required = get_post_meta($value->ID, 'required', true);
          $signed = get_post_meta($value->ID, 'signed', true);
          $login_required = get_post_meta($value->ID, 'login_required', true);
          $signature_url = get_post_meta($value->ID, 'signature_url', true);
          $is_archived = get_post_meta($value->ID, 'is_archived', true);

          if (!empty($is_archived)) {

            if ($is_archived == "archived") {
              $archive_button = "archived";
            }else{
              $archive_button = "<input type='checkbox' name='archive_attachment' style='position: relative; left: 19px;'>";
            }
            $thickbox = 'class="thickbox"';
            if($required == "yes") {
              if( empty($signed) ) {
                $signed = "Awaiting";
                $signature_url ="#";
                $thickbox = "";
              }
            } else {
              $signed = "N/A";
              $signature_url ="#";
              $thickbox = "";
            }

            if (!empty($login_required)) {
              $checked = "<p style='margin: 0; position: relative; left: 56px;'>&#10004;</p>";
            }else {
              $checked = "";
            }

            $genre_url = site_url().'/wp-admin/options-general.php?page=project_emails&attachment_id='.$value->ID.'&title='.$value->post_title.'&project_id='.$post->ID;

            echo "<tr>
                    <td>
                      ".$value->post_title."
                    </td>
                    <td>
                      ".$value->post_content."
                    </td>
                    <td>
                      ".$value->post_date."
                    </td>
                    <td>
                      ".$required."
                    </td>
                    <td>
                      <a href='".$signature_url."?TB_iframe=true&width=320&height=300' ".$thickbox." >".$signed."</a>
                    </td>
                    <td>
                      <a href='".$value->guid."' target='_blank'>View</a>
                    </td>
                    <td>
                      <a style='white-space: nowrap;' href='".$genre_url."' target='_blank'>Send Email</a>
                    </td>
                    <td>
                      ".$checked."
                    </td>                           
                  </tr>";
          }
        }
        echo "</table>";
      ?>
    </div>
  </div>
  <?php
}

function add_project_email_page() {
  add_options_page('Project Emails', 'Project Emails', 'manage_options', 'project_emails', 'project_emails');
}
function project_emails() {
global $wpdb;

  $project_id = $_GET['project_id'];
  $attachment_id = $_GET['attachment_id'];
  $client_id = get_post_meta($project_id, 'client_id', true);
  $main_user_email = get_userdata($client_id)->user_email;
  $get_additional_users = get_user_meta(get_current_user_id(), 'additional_users', true);

  $login_required = get_post_meta($attachment_id, 'login_required', true);
  if (!empty($login_required)) {
    $checked = "checked";
  }else {
    $checked = "";
  }

  $additional_user = explode(":", $get_additional_users);
  $additional_user_one = $additional_user[0];
  $additional_user_two = $additional_user[1];

  $additional_user_email1 = get_userdata($additional_user_one)->user_email;
  $additional_user_email2 = get_userdata($additional_user_two)->user_email;

  ?>
  <h1>Project Emails</h1>
    <form method="post">
      <table>
        <tr><td style="background: #f1f1f1; border: none; width: 800px;"><b>Recipient</b><br>
        <select id="email_list">
          <option value="Select From Available Emails">Select From Available Emails</option>
          <option value="<?php echo $main_user_email; ?>"><?php echo $main_user_email; ?></option>
          <option value="<?php echo $additional_user_email1; ?>"><?php echo $additional_user_email1; ?></option>
          <option value="<?php echo $additional_user_email2; ?>"><?php echo $additional_user_email2; ?></option>
        </select>
          <input style="border: 1px solid #ddd; width: 100%;" type="text" name="projects_specific_email_recipient"></td></tr>
        <tr><td style="background: #f1f1f1; border: none; width: 800px;"><b>Subject</b><br><input style="border: 1px solid #ddd; width: 100%;" type="text" name="projects_specific_subject" value="<?php echo $_GET['title']; ?>"></td></tr>
        <tr><td style="background: #f1f1f1; border: none; width: 800px;"><b>Message</b><br><textarea style="margin-bottom: 10px; width: 100%; height: 200px; border: 1px solid #ddd;" type="textarea" name="projects_specific_message"></textarea><br><label for="login_required"><b>Login Not Required</b></label><br><input type="checkbox" name="login_required" <?php echo $checked; ?>><br><br><input type="submit" name="submit_specific_message"></td></tr>
      </table>
    </form>

<script type="text/javascript">
  jQuery(document).ready(function($){
    $('#email_list').on('change', function () {
        $('input[name="projects_specific_email_recipient"]').val($(this).val());
        if ($(this).val() == 'Select From Available Emails') {
          $('input[name="projects_specific_email_recipient"]').val('');
        }
    });
  });
</script>
  <?php
  $attachment_id = $_GET['attachment_id'];
  $login_required = get_post_meta($attachment_id, 'login_required', true);
  $encrypted_param = sha1_url_encrypt($attachment_id);

  $specific_recipient = $_POST['projects_specific_email_recipient'];
  $specific_subject = $_POST['projects_specific_subject'];
  $specific_message = $_POST['projects_specific_message'];

  $existing_specific_emails = get_post_meta($project_id, 'specific_emails', true);

  if ($_POST['submit_specific_message'] && !empty($specific_recipient) && !empty($specific_subject) && !empty($specific_message)) {
    update_post_meta($project_id, 'specific_emails', $existing_specific_emails.';'.$specific_recipient.':'.$specific_subject.':'.$specific_message.':'.$project_id.':'.$attachment_id.':'.date("d/m/Y"));
    update_post_meta($attachment_id, 'login_required', $_POST['login_required']);

    if ($_POST['login_required']) {
      $specific_message_email .= $specific_message."\n"."\n";
      $specific_message_email .= 'Please sign in to see attachment or click the link below:'."\n";
      $specific_message_email .= site_url() . '/client-projects/?rand_param='.$encrypted_param.'&project_id='.$project_id;
      update_post_meta($attachment_id, 'sha1_url_hash', $encrypted_param);
    }

    wp_mail($specific_recipient, $specific_subject, $specific_message_email);
  }
}
add_action('admin_menu', 'add_project_email_page');

function project_sent_emails() {

  $project_id = $_GET['post'];
  $attachments = get_attached_media('', $project_id);
  $get_general_email = get_post_meta($project_id, 'general_emails', true);
  $get_specific_email = get_post_meta($project_id, 'specific_emails', true);

  $general_emails = explode(";", $get_general_email);
  $specific_emails = explode(";", $get_specific_email);

  echo "<table>";
  echo  "<tr>
        <th>Subject</th>
        <th>Message</th>
        <th>Recipient</th>
        <th>Attachment</th>
        <th>Date</th>
      </tr>";
  foreach ($general_emails as $general_key => $general_email) {

    $general_email_info = explode(":", $general_email);
    $general_user_email = $general_email_info[0];
    $general_user_subject = $general_email_info[1];
    $general_user_message = $general_email_info[2];
    $general_email_date = $general_email_info[3];

    if (!empty($general_user_email)) {
      echo  "<tr>
                <td style='white-space: nowrap;'>
                  ".$general_user_subject."
                </td>
                <td style='white-space: nowrap;'>
                  ".$general_user_message."
                </td>
                <td style='white-space: nowrap;'>
                  ".$general_user_email."
                </td>
                <td style='white-space: nowrap;'>
                  General
                </td>
                <td style='white-space: nowrap;'>
                  ".$general_email_date."
                </td>
              </tr>";
    }    
  }
  foreach ($specific_emails as $specific_key => $specific_email) {

    $specific_email_info = explode(":", $specific_email);
    $specific_user_email = $specific_email_info[0];
    $specific_user_subject = $specific_email_info[1];
    $specific_user_message = $specific_email_info[2];
    $specific_email_attachment = $specific_email_info[4];
    $specific_email_date = $specific_email_info[5];

    if (!empty($specific_user_email)) {
      echo  "<tr>
                <td style='white-space: nowrap;'>
                  ".$specific_user_subject."
                </td>
                <td style='white-space: nowrap;'>
                  ".$specific_user_message."
                </td>
                <td style='white-space: nowrap;'>
                  ".$specific_user_email."
                </td>
                <td style='white-space: nowrap;'>
                  ".$attachments[$specific_email_attachment]->post_title."
                </td>
                <td style='white-space: nowrap;'>
                  ".$specific_email_date."
                </td>
              </tr>";
    }    
  }
  echo "</table>";
}

function front_end_attachments($post_id) {
global $wpdb;

  require_once( ABSPATH . 'wp-admin/includes/image.php' );
  require_once( ABSPATH . 'wp-admin/includes/file.php' );
  require_once( ABSPATH . 'wp-admin/includes/media.php' );
  $current_user = wp_get_current_user();

  echo '<form method="post" action="#" enctype="multipart/form-data">';
  for ($i=0; $i < 5; $i++) {
      $html = '<br><b>Upload new attachment</b><br><br>';
      $html .= '<label for="attachment_title"><b>Title</b></label></br>';
      $html .= '<input type="text" name="attachment_title_'.$i.'" size="100" /><br>';
      $html .= '<label for="attachment_title"><b>Comment</b></label></br>';
      $html .=  '<textarea name="attachment_comment_'.$i.'" rows="5" cols="99"></textarea></br></br>';
      $html .= '<input type="file" name="custom_attachment_'.$i.'" id="custom_attachment_'.$i.'"  multiple="true" /><br>';

    echo $html;

    if( !empty($_FILES['custom_attachment_'.$i]['name']) ){
      $attachment_id = media_handle_upload( 'custom_attachment_'.$i, $post_id, array(
                          'post_title' => sanitize_text_field( $_POST['attachment_title_'.$i]),
                          'post_content' => sanitize_textarea_field( $_POST['attachment_comment_'.$i])
                        )
                      );
      //Save required filed to post meta
      update_post_meta($attachment_id, 'required', $_POST['attachment_required_'.$i]);
      update_post_meta($attachment_id, 'visible_to_customer', $_POST['visible_to_customer_'.$i]);

      //Send Email notification to sps
      //TODO: change email $to field
      //add customer project and name
      $to = 'stefanvujic576@gmail.com';
      $subject = sanitize_text_field( $_POST['attachment_title_'.$i]) . ' - New file upload from '. $current_user->display_name;
      $body = 'Customer has uploaded an attachment<br>';
      $headers = array('Content-Type: text/html; charset=UTF-8');
      $headers[] = 'From: SPS Timber Windows <donotreply@spstimberwindows.co.uk>';
      wp_mail( $to, $subject, $body, $headers );
    }
  }
    echo '<input style="border-style: groove; border-width: 1px; font-size: 21px;" type="submit" value="Submit all"/><br>';
  echo '</form>';
}

function project_meta_save($post_id, $post, $update) {
  global $wpdb;
  $post_type = get_post_type($post_id);

  if ( "project" == $post_type ) {

    $general_recipient = $_POST['projects_general_email_recipient'];
    $general_subject = $_POST['projects_general_subject'];
    $general_message = $_POST['projects_general_message'];

    $existing_emails = get_post_meta($post_id, 'general_emails', true);

    if ($_POST['submit_general_message'] && !empty($general_recipient) && !empty($general_subject) && !empty($general_message)) {
      update_post_meta($post_id, 'general_emails', $existing_emails.';'.$general_recipient.':'.$general_subject.':'.$general_message.':'.date("d/m/Y"));
      wp_mail($general_recipient, $general_subject, $general_message);
    }

    //Create new user
    if( isset($_POST['new_project']) ){

      if ( $_POST['user'] == 0 ) {
        	$random_password = wp_generate_password( $length=12, $include_standard_special_chars=false );
        	$user_id = wp_create_user( $_POST['user_name'], $random_password, $_POST['user_email'] );

          //Send email notification with password
          //TODO: change to user email = $_POST['user_email']
          $to = $_POST['user_email'];
          $subject = 'SPS Project created';
          $body = 'Please login to your SPS account to see your project details<br><br>';
          $body .= 'Reference: '.$_POST['reference'].$_POST['enquiry_form'].'<br>';
          $body .= 'Username: '.$_POST['user_name'].'<br>';
          $body .= 'Password: '.$random_password.'<br>';
          $headers = array('Content-Type: text/html; charset=UTF-8');
          $headers[] = 'From: SPS Timber Windows <donotreply@spstimberwindows.co.uk>';

          wp_mail($to, $subject, $body, $headers);
					//update project with new user id
		      update_post_meta($post_id, 'client_id', $user_id );
          update_post_meta($post_id, 'enquiry_form_details', $_POST['enquiry_form'] );
      } else {
				 update_post_meta($post_id, 'client_id', $_POST['user'] );
         update_post_meta($post_id, 'enquiry_form_details', $_POST['enquiry_form'] );
      }
    }
    if (isset($_POST['archive_attachment_id'])) {
      update_post_meta($_POST['archive_attachment_id'], 'is_archived', 'yes');
    }
    //Save user details to post meta
    update_post_meta($post_id, 'client_full_name', sanitize_text_field( $_POST['full_name']) ) ;
    update_post_meta($post_id, 'client_address', sanitize_text_field( $_POST['user_address']) ) ;
    update_post_meta($post_id, 'client_reference', sanitize_text_field( $_POST['reference']) ) ;
    update_post_meta($post_id, 'enquiry_form_details', sanitize_text_field( $_POST['enquiry_form'] ) );
    update_post_meta($post_id, 'document_type', sanitize_text_field( $_POST['project_or_enquiry']));

    // These files need to be included as dependencies when on the front end.
  	require_once( ABSPATH . 'wp-admin/includes/image.php' );
  	require_once( ABSPATH . 'wp-admin/includes/file.php' );
  	require_once( ABSPATH . 'wp-admin/includes/media.php' );

  	//Save attachments
    for ($i=0; $i < 10; $i++) {
      if( !empty($_FILES['custom_attachment_'.$i]['name']) ){
        $attachment_id = media_handle_upload( 'custom_attachment_'.$i, $post_id, array(
                            'post_title' => sanitize_text_field( $_POST['attachment_title_'.$i]),
                            'post_content' => sanitize_text_field( $_POST['attachment_comment_'.$i])
                          )
                        );

        //Save required filed to post meta
        update_post_meta($attachment_id, 'required', $_POST['attachment_required_'.$i]);
        update_post_meta($attachment_id, 'visible_to_customer', $_POST['visible_to_customer_'.$i]);
        update_post_meta($attachment_id, 'login_required', $_POST['login_required']);
      }
    }
  }
}
add_action( 'save_post', 'project_meta_save' );

//user ajax check
add_action("wp_ajax_my_user_check", "my_user_check");
add_action("wp_ajax_nopriv_my_user_check", "my_user_check");
add_action("wp_ajax_my_user_email_check", "my_user_email_check");
add_action("wp_ajax_nopriv_my_user_email_check", "my_user_email_check");

function my_user_check() {
  if(isset($_POST['username'])){
    $username = sanitize_text_field( $_POST['username'] );
    $user = get_user_by( 'login', $username );
    echo ( !empty($user) ) ? 1 : 0;
  }
  wp_die();
}

function my_user_email_check() {
  if(isset($_POST['useremail'])){
    $useremail = sanitize_text_field( $_POST['useremail'] );
    $user = get_user_by( 'email', $useremail );
    echo ( !empty($user) ) ? 1 : 0;
  }
  wp_die();
}

//Add image to attachemnt post_meta
add_action( 'wpcf7_posted_data', 'wpcf7_add_text_to_mail_body' );

 function wpcf7_add_text_to_mail_body($posted_data){
	 //Check if the form is the signature one
	 //TODO: 6512 ID might need to be changed if new form created or imported on live site
 	if($posted_data['_wpcf7'] == "6512"){
    update_post_meta($_SESSION['attachment_id'], 'printed_name', $posted_data['printed_name']);
    $is_name_updated = get_post_meta($_SESSION['attachment_id'], 'printed_name', true);
    if (!empty($is_name_updated)) {
  		update_post_meta($_SESSION['attachment_id'], 'signed', 'yes');
   		update_post_meta($_SESSION['attachment_id'], 'signature_url', $posted_data['project-signature']);
   		unset($_SESSION['attachment_id']);
 		  return $posted_data;
    }
	}
}