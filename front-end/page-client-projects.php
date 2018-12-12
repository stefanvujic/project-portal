<?php

// $sidebar = get_post_meta( get_the_ID(), '_mt_page_sidebar', true);
// $featured_image = wp_get_attachment_image_src(get_post_thumbnail_id(),'page-image', true);

get_header();
global $wpdb;

echo '<div class="site_url" style="display: none;">'.site_url().'</div>';
?>

<div class="page-area clearfix?>">

	<div class="content container">
		<?php
		if ( !is_user_logged_in() && !$_GET['rand_param']) { ?>
				<?php
					$args = array(
						'echo' => true,
						'remember'       => false,
						'redirect'       => get_site_url().'/client-projects/',
					);
					echo "<div class='login-section'>";
				 		wp_login_form($args);
				 	echo "</div>";	
			?>
			</div>
			<?php
		} else {
		?>
		<?php
			if (is_user_logged_in()) {
				$user_id = get_current_user_id();
				echo '<a style="position: relative; bottom: 25px; font-size: 17px; float: right;" href="'.wp_logout_url(site_url() . '/client-projects/').'">Logout</a>';
			} else {
				$user_id = get_post_meta($_GET['project_id'], 'client_id', true);
			}

			$additional_users = get_user_meta($user_id, 'additional_users', true);
			$additional_user_ids  = explode(':', $additional_users);
			$user_2 = $additional_user_ids[0];
			$user_3 = $additional_user_ids[1];

			$project_ids = $wpdb->get_results('SELECT post_id as id FROM wp_postmeta WHERE meta_key LIKE "client_id" AND meta_value =' .$user_id);

			if(empty($project_ids)) {
				echo '<div style="text-align:center; background: #9e0927; color: #ffff; border-radius: 13px; padding: 10px; width: 50%;left: 26%; position: absolute;">YOU HAVE NO PROJECTS YET, PLEASE TRY AGAIN LATER</div>';
			}
			if(!empty($project_ids)) {

				$project_ctr = 0;
				foreach ($project_ids as $key => $project_id) {

					$project_ctr++;
					$project = get_post($project_id->id);

					$documents = $wpdb->get_results("SELECT * FROM wp_posts WHERE post_type = 'attachment' AND post_parent = " . $project->ID . " ORDER BY post_date DESC");
					?>
					<?php
					if (($key != $temp_key || $key == 0) AND $project_ctr != 1) {
						echo '</div></div></div></div></div></div></div>';
						echo '<br><br><br><br>';
					}
					if ($key != $temp_key || $key == 0) {
						//echo '</div></div></div></div>';
						?>

						<div style="background: #ddd; padding: 20px; text-align: center; border: 2px solid #b01e2d; border-bottom-style: none;position: relative; top: 1px;z-index: 0;">
							<h4>Project Details</h4>
								<p>Desciption: <?php echo $project->post_title ?></p>
								<p>Customer Name: <?php echo get_post_meta($project->ID, 'client_full_name', true); ?></p>
								<p>Address: <?php echo get_post_meta($project->ID, 'client_address', true); ?></p>
							<h4>Documents</h4>
						</div>
						<?php
					}
					
					$doc_temp = array();
					foreach ($documents as $document => $value) {
						$date_array = explode("/", date('d/m/Y', strtotime($value->post_date)));
						$month = $date_array[1];
						$year = $date_array[2];

						$doc_temp[$year][$month][] = $value->ID;
					}

					foreach ($doc_temp as $doc_year => $doc_year_array) {
						if ($temp_year != $doc_year) {
							if($key == 0 && $project_ctr != 1) {
							  echo '</div></div></div>';
							}
							echo '<div class="panel-group attach_year" id="accordion_attach_year_'.$project->ID.'">';
							echo '<div style="background: #ddd;" class="panel panel-default">';
							echo '<div class="panel-heading">';
							echo '<h4 class="panel-title">';
							echo '<a data-toggle="collapse" data-parent="accordion_attach_year_'.$project->ID.'" href="#collapse_'.$project->ID.$doc_year.'">'.$doc_year.'</a>';
							echo '</h4>';
							echo '<i style="position: relative; float: right; font-size: 20px; bottom: 19px;" class="fas fa-angle-double-down"></i>';
							echo '</div>';
							echo '<div id="collapse_'.$project->ID.$doc_year.'" class="panel-collapse collapse in">';
							echo '<div style="background: #fffff;" class="panel-body">';								
						}

						$month_ctr = 0;
						foreach ($doc_year_array as $doc_month => $doc_month_array) {
							$month_ctr++;
							if ($month_ctr != 1) {
						    	echo '</div></div></div></div>';
						  	}

							if ($temp_month != $doc_month) {
								echo '<div class="panel-group attach_month" id="accordion_'.$doc_month.$project->ID.'">';
								echo '<div class="panel panel-default">';
								echo '<div class="panel-heading">';
								echo '<h4 class="panel-title">';
								echo '<a data-toggle="collapse" data-parent="accordion_attach_month_'.$project->ID.'" href="#collapse_'.$project->ID.$doc_month.'">'.$doc_month.'/'.$doc_year.'</a>';
								echo '</h4>';
								echo '<i style="position: relative; float: right; font-size: 20px; bottom: 19px;" class="fas fa-angle-double-down"></i>';
								echo '</div>';
								echo '<div id="collapse_'.$project->ID.$doc_month.'" class="panel-collapse collapse in">';
								echo '<div style="background: #ffffff;" class="panel-body">';
							}

							foreach ($doc_month_array as $doc_id => $ids) {
				        		$required = get_post_meta($ids, 'required', true);
				        		$is_visible_to_customer = get_post_meta($ids, 'visible_to_customer', true);
				        		$is_name_printed = get_post_meta($ids, 'printed_name', true);
								$signed = get_post_meta($ids, 'signed', true);
								$login_required = get_post_meta($ids, 'login_required', true);
								$is_archived = get_post_meta($ids, 'is_archived', true);

								$project_unique_key = get_post_meta($ids, 'sha1_url_hash', true);

								$document_content = get_post($ids);

								if( empty($signed) ) { $signed = "Awaiting"; }
								$signature_url = get_post_meta($ids, 'signature_url', true);

								if (!empty($login_required) && !is_user_logged_in() && $project_unique_key == $_GET['rand_param'] && empty($is_archived)) {
									if($is_visible_to_customer !== 'no') {
										?>
										<table>
											<tr>
								                <th>Title</th>
								                <th>Comment</th>
								                <th>Date</th>
								                <th>Required</th>
												<th>Signed</th>
								                <th>View</th>
								            </tr>
											<tr>
								                <td>
								                  <?php echo $document_content->post_title; ?>
								                </td>
								                <td>
								                  <?php echo $document_content->post_content; ?>
								                </td>
								                <td>
								                  <?php echo date('d/m/Y', strtotime($document_content->post_date)); ?>
								                </td>
								                <td>
								                   <?php echo $required; ?>
								                </td>
												<td>
												<?php if ($required == "yes") { ?>
													<a href='#' data-toggle="modal" data-target="#myModal<?php echo $count; ?>"><?php echo $signed; ?></a>
												<?php } else { ?>
													N/A
												<?php } ?>
								            	</td>
								            	<td>
								                  <a href='<?php echo $document_content->guid;?>' target='_blank'>View Document</a>
								            	</td>
								            </tr>
											<?php if (  $required == "yes" && $signed!= "yes" ) { ?>
												<tr class="signature">
													<td>
														Action Required for this document!
													</td>
													<?php if ($required_count == 0) { ?>
														<td>
															<?php
															$required_count++;
															global $document_id;
															$document_id = $ids;
															$_SESSION['attachment_id'] = $ids;
															if (empty($is_name_printed)) {
																echo '<p style="color: red;">In order to sign, please print your name.</p>';
															}
															echo do_shortcode( '[contact-form-7 id="6512" title="Signature"]' ); ?>
														</td>
													<?php } else { ?>
														<td>
															Please sign the document above first.
														</td>
													<?php } ?>

												</tr>
											<?php } ?>
										</table>				            					            
										<?php				
									}
								}
								elseif (is_user_logged_in()) {
									?>
									<table>
										<?php if($is_visible_to_customer !== 'no' && empty($is_archived)) {
											?>										
										<tr>
							                <th>Title</th>
							                <th>Comment</th>
							                <th>Date</th>
							                <th>Required</th>
											<th>Signed</th>
							                <th>View</th>
							            </tr>
										<tr>
							                <td>
							                  <?php echo $document_content->post_title; ?>
							                </td>
							                <td>
							                  <?php echo $document_content->post_content; ?>
							                </td>
							                <td>
							                  <?php echo date('d/m/Y', strtotime($document_content->post_date)); ?>
							                </td>
							                <td>
							                   <?php echo $required; ?>
							                </td>
										<td>
											<?php if ($required == "yes") { ?>
												<a href='#' data-toggle="modal" data-target="#myModal<?php echo $count; ?>"><?php echo $signed; ?></a>
											<?php } else { ?>
												N/A
											<?php } ?>
							            </td>
							            <td>
							                  <a href='<?php echo $document_content->guid;?>' target='_blank'>View Document</a>
							            </td>
							            </tr>
									<?php if ($required == "yes" && $signed!= "yes") { ?>
										<tr class="signature">
											<td>
												Action Required for this document!
											</td>
											<?php if ($required_count == 0) { ?>
												<td>
													<?php
													$required_count++;
													global $document_id;
													$document_id = $ids;
													$_SESSION['attachment_id'] = $ids;
													if (empty($is_name_printed)) {
														echo '<p style="color: red;">In order to sign, please print your name.</p>';
													}
													echo do_shortcode( '[contact-form-7 id="6512" title="Signature"]' ); ?>
												</td>
											<?php } else { ?>
												<td>
													Please sign the document above first.
												</td>
											<?php } ?>
										</tr>
									<?php } ?>
						<?php } ?>
								</table>
							<?php } ?>
								<div id="myModal<?php echo $count; ?>" class="modal fade" role="dialog">
									<div class="modal-dialog">
										<div class="modal-content">
											<div class="modal-header">
								        <h4 class="modal-title">Signature</h4>
								      </div>
											<div class="modal-body">
												<img src="<?php echo $signature_url; ?>" alt="">
											</div>
											<div class="modal-footer">
								        <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
								      </div>
										</div>
									</div>
								</div>
								<?php
								$count++;
							}
							if (is_user_logged_in()) {
								front_end_attachments($project_id->id);
							}
							$temp_month = $doc_month;
							}
						}
					}
					$temp_year = $doc_year;
				}
				$temp_project = $doc_project;
			}
		?>

	</div>
</div>

<?php
wp_footer();