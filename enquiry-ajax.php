<?php

include '../../../../wp-load.php';

if(isset($_POST["form_enquiry_email"])){
	$user_email = $_POST["form_enquiry_email"];
	$get_enquiry_form_id = $wpdb->get_results("SELECT lead_id FROM wp_rg_lead_detail WHERE form_id = 2 AND value = '".$user_email."'");

    foreach ($get_enquiry_form_id as $enquiry_form_id_key => $enquiry_form_id_value) {

      $get_postcode = $wpdb->get_results("SELECT value FROM wp_rg_lead_detail WHERE field_number = 34 AND lead_id = '".$get_enquiry_form_id[$enquiry_form_id_key]->lead_id."'");
      $get_surname = $wpdb->get_results("SELECT value FROM wp_rg_lead_detail WHERE field_number = 31 AND lead_id = '".$get_enquiry_form_id[$enquiry_form_id_key]->lead_id."'");
      $get_date_created = $wpdb->get_results("SELECT date_created FROM wp_rg_lead WHERE id = '".$get_enquiry_form_id[$enquiry_form_id_key]->lead_id."'");

      $creation_date = $get_date_created[$enquiry_form_id_key]->date_created;
      $creation_date = date("d/m/Y", strtotime($creation_date));

      if (!empty($get_surname[$enquiry_form_id_key]->value)) {
      	$inquiry_info[] = $get_postcode[$enquiry_form_id_key]->value.' - '.$get_surname[$enquiry_form_id_key]->value.' - '.$creation_date . ' - '.$get_enquiry_form_id[$enquiry_form_id_key]->lead_id;
      }
    }
	echo json_encode($inquiry_info);
}