<div class="<?php echo $this->prefix; ?>-count <?php echo $this->prefix."wrapper"; ?>">
	<div class="error notice hide" id="error_div">
	    <p id="error_msg"></p>
	</div>
	<?php
		wp_nonce_field( $this->nonce_name, $this->nonce_name );
		$license_info = get_option( $this->prefix.'pro_license_info');
		$renew = "no";
		$license_exp = "";
		$lmsg_top_id = "";
		$lil_id = "";
		$expire_msg = "";
		$hide = '';
		$license_info_lifetime = '';
		if($license_info){
			$license_exp = date('Y-m-d', strtotime($license_info->expires));
			$license_exp_d = date('d-F-Y', strtotime($license_info->expires));
			$license_info_lifetime = $license_info->expires;
		}
		$today = date('Y-m-d');
		$exp_date =$license_exp;
		$date1 = date_create($today);
		$date2 = date_create($exp_date);
		$diff = date_diff($date1,$date2);
		$days = $diff->format("%a");
		if( $license_info_lifetime == 'lifetime' ){
				$days = 'Lifetime';
				if ($days == 'Lifetime') {
				$expire_msg = " Your License is Valid for Lifetime ";
				}
			}
			elseif($today > $exp_date){
				$days = -$days;
			}
			if ($days<0) {
				$lmsg_top_id = 'id="lmsg_top_id"';
			}
	if(!$license_info){
	?>
	<?php
	$renew = "yes";
	if (isset($license_info->license_key)) {
		$lisense_k = $license_info->license_key;
	}
	if (isset($license_info->download_id)) {
		$download_id = $license_info->download_id;
	}
	if (isset($license_info->payment_id)) {
		$payment_id = $license_info->payment_id;
	}
	if (isset($license_info->download_id)) {
		$download_id = $license_info->download_id;
	}
	?>
	<h3 class="ext-a-title"><?php echo $this->pro_name; ?> Membership Bundle</h3>
	<div class="afwpp-act-b">
		<div class="lkact">
			<p>Enter Your License Key</p>
			<input type="text" value="" class="regular-text" id="bundle_license_key">
			<button type="button" class="button button-primary" id="active_licence">Activate License</button>
		</div>
	</div>
	<?php 
	}else{
		$b_license = isset($license_info ->license_key) ? $license_info ->license_key : '';
		$strlen = strlen($b_license);
		$show_key = "";
		for($i=1;$i<$strlen;$i++){
			if($i<$strlen-4){
				$show_key .= "*";
			}else{
				$show_key .= $b_license[$i];
			}
		}
	?>
	<?php
	if (isset($license_info->payment_id)) {
		$payment_id = $license_info->payment_id;
	}
	if (isset($license_info->download_id)) {
		$download_id = $license_info->download_id;
	}
	?>
	<h3 class="ext-a-title"><?php echo $this->pro_name; ?> </h3>
	<div class="afwpp-act-b">
		<div class="asblk bor-right">
			<p class="ftitle">License Key<span class="linfo-rmsg_top" id="refresh_license_top">
				<input type="hidden" value="<?php echo $payment_id;?>" id="payment_id">
				<input type="hidden" value="<?php echo $download_id;?>" id="download_id">
				<input type="hidden" value="<?php echo $renew ;?>" id="renew_status">
				<span class="lmsg_top refresh-lib" <?php echo $lmsg_top_id ?>><i class="dashicons dashicons-update-alt" id="refresh_license_icon_top"></i></span>
			</span></p>
			<input type="text" name="main_key" value="<?php echo $show_key;?>" class="regular-text deact-text">
			<button type="button" class="button button-normal" id="revoke_license">Deactivate</button>			
			<?php if($days<0){
				$span_class = "aeaicon dashicons dashicons-no";
				$color = 'color:red';
			}
			else{
				$span_class = "aeaicon dashicons dashicons-yes";
				$color = 'color:green';
			} 
			?>
			<p class="act-msg"><span style="<?php echo $color;?>" class="<?php echo $span_class;?>"></span><?php
			if(isset($license_info->license_key)){
				$lisense_k = $license_info->license_key;
			}
			if(isset($license_info->download_id)){
				$download_id = $license_info->download_id;
			}
		 if($days<0){ ?> <span id="ex_text" style="color:red;float: left;">Expired.</span> <a id="extend"href="https://pwa-for-wp.com/order/?edd_license_key=<?php echo esc_attr($lisense_k);?>&download_id=<?php echo intval($download_id);?>" target="_blank" > Extend </a> <span id="ex_text">the License receive to future updates & support</span>
		 <?php }
		 	else { ?> License is active. You are receiving updates & support.<?php }?></p>
		</div>
		<?php
		$exp_class_2 = 'renew_license_key';
		?>
		<?php 
			$date1 = date_create($today);
			$date2 = date_create($exp_date);
			$diff = date_diff($date1,$date2);
			$days = $diff->format("%a");
			if($days>=0){
			$expire_msg = " Expires in ".intval($days)." days";
		}
			if( $license_info_lifetime == 'lifetime' ){
				$days = 'Lifetime';
				if ($days == 'Lifetime') {
				$expire_msg = " Your License is valid for Lifetime ";
				}
			}
			elseif($today > $exp_date){
				$days = -$days;
			}

			$renew = "yes";
			if(isset($license_info->license_key)){
				$lisense_k = $license_info->license_key;
			}
			if(isset($license_info->download_id)){
				$download_id = $license_info->download_id;
			}
			if(isset($license_info->payment_id)){
				$payment_id = $license_info->payment_id;
			}
			$exp_class = 'lmsg2';
			$exp_id = '';
			if($days<0){
				$expire_msg = " Expired ";
				$exp_class = 'expired';
				$exp_id = 'exp';
				$exp_class_2 = 'renew_license_key_';
			}
			?>
		<?php
		if ($license_info_lifetime == 'lifetime' ) {
				$lil_id = 'lil_id';
				$exp_text = 'Your License is Valid for ';
				$license_exp_d = $license_info_lifetime;
				$hide = 'hide_it';
		}
		else	{
			$lil_id = 'not_lil';
			$exp_text = 'Expire on '; 
		}
		?>

		<div class="asblk bor-right">
			<p class="ftitle">License Information</p>
			<div class="<?php esc_attr($exp_class);?>">
				<a href="https://pwa-for-wp.com/order/?edd_license_key=<?php echo esc_attr($lisense_k);?>&download_id=<?php echo intval($download_id);?>" target="_blank" class="<?php echo $exp_class_2; ?>" id="<?php echo $exp_id; ?>">Renew</a>
				<i class="dashicons dashicons-calendar-alt"></i>				
				<span class="<?php echo $exp_class; ?>">
					<span id="attnl">__</span>  <?php echo esc_attr($expire_msg);?>
				</span>
				<span class="hider <?php echo $hide;?>" id="<?php echo $lil_id; ?>"><span class="<?php echo $hide;?>"><?php echo $exp_text.$license_exp_d; ?></span></span>
				<input class="l_key" type="hidden" value="<?php echo $lisense_k;?>" name="l_key">
				<input class="show_key" type="hidden" value="<?php echo $show_key?>" name="show_key">
			</div>
			<div class="linfo-rmsg" id="refresh_license">
				<input type="hidden" value="<?php echo $payment_id;?>" id="payment_id">
				<input type="hidden" value="<?php echo $download_id;?>" id="download_id">
				<input type="hidden" value="<?php echo $renew ;?>" id="renew_status">
				<span class="lmsg refresh-lib"><i class="dashicons dashicons-update-alt" id="refresh_license_icon"></i> Refresh Extensions Library</span>
			</div>

			<?php
			$trans_check = get_transient( 'pwaforwp_set_trans' );		
			if ( $days<=7 && $trans_check !== 'pwaforwp_set_trans_v' ) {
			  	?>
				<div class="linfo2-rmsg" id="auto_fresh">
				<input type="hidden" value="<?php echo $payment_id;?>" id="payment_id">
				<input type="hidden" value="<?php echo $download_id;?>" id="download_id">
				<input type="hidden" value="<?php echo $renew ;?>" id="renew_status">
				<input type="hidden" value="<?php echo $days ;?>" id="remaining_days">
			</div>
			<?php }?>
			<?php
			$ver_num = PWAFORWPPRO_VERSION;
			$trans_check = get_transient( 'pwaforwp_set_trans_once' );
			if ( $trans_check !== 'pwaforwp_set_trans_once_v' && $ver_num == '1.9.1' ) {
			 ?>
				<div class="linfo2-rmsg" id="auto_fresh2">
				<input type="hidden" value="<?php echo $ver_num;?>" id="ver_num">
				<input type="hidden" value="<?php echo $payment_id;?>" id="payment_id">
				<input type="hidden" value="<?php echo $download_id;?>" id="download_id">
				<input type="hidden" value="<?php echo $renew ;?>" id="renew_status">
				<input type="hidden" value="<?php echo $days ;?>" id="remaining_days">
			</div>
			<?php }?>			
		</div>
		<div class="asblk">
			<p class="ftitle">At Your Service</p>
			<div>
				<a href="<?php echo $this->main_documentation_link; ?>" target="_blank"><span class="lmsg"><i class="dashicons dashicons-media-text"></i> View Documentation</span></a>
			</div>
			<div class="linfo-rmsg">
				<a href="<?php echo $this->support_link; ?>" target="_blank"><span class="lmsg"><i class="dashicons dashicons-businessman"></i> Ask Technical Question</span></a>
			</div>
		</div>
	</div>
	<h3 class="ext-a-title">Extensions List</h3>
	<div class="afwpp-ext-block">
		<?php 
			$current_tab = '';
			if(isset($_GET['tab'])){
				$current_tab = $_GET['tab'];
			}
		?>
		<div class="ext-menu">
			<ul>
				<li class="extension-tab active" id="all">All</li>
				<li class="extension-tab" id="active">Active</li>
			</ul>
		</div>
		<div class="ext-list-block">
			<ul>
				<?php
					$response = $license_info->afwpp_response;
					$selectedOption = get_option('pwaforwp_settings',true);
					$count = 0;
					$product_item_name = $this->get_all_plugins_addon_list();
					foreach ($response as $key => $addons) {

						$saswp_addon_name = '';
						foreach($product_item_name as $item_index=> $item_name){
							if(strtolower($item_name['p-title'])==strtolower($addons->post_title)){
								$saswp_addon_name = $item_index;
							break;
							}
							
						}
						$status = ucfirst($addons->status);
						$title = $addons->post_title;
						$id = $key;
						$plugin_path = '';
						$is_active = '0';
						$update = '';
						$description = '';
						$version = $response[$key]->sl_version;
						$has_license = 'yes';
						if(isset($selectedOption[strtolower($saswp_addon_name).'_addon_license_key_status']) && $selectedOption[strtolower($saswp_addon_name).'_addon_license_key_status']=='active'){
							$is_active = 1;
						}
						$menu_link = admin_url("admin.php?page=pwaforwp&tab=features");
						$tit = $title;
						$ind = trim(strtolower(str_replace(' ', '-', $tit)));
						
						if($status=="Inactive"){
							$status="Activate";
						}else{
							$status="Deactivate";
						}
						$docs = $this->get_documentation($title);

						$button_name = '';
						$button= '';
						$has_plugin = $is_active;
						$changelog_url = '';
						if($is_active=='1' && $has_license == 'yes'){
							$button_name = 'Deactivate';
							$plugin_slug = $ind;
							$changelog_url = self_admin_url( 'plugin-install.php?tab=plugin-information&plugin=' . $plugin_slug . '&section=changelog&TB_iframe=true&width=600&height=800' );
						
							$button= 'deactivate';
							$update = $this->get_plugin_update($product_item_name[$saswp_addon_name]['p-slug'],$product_item_name[$saswp_addon_name]);
							if(function_exists('get_file_data')){
						    $plugin_data = get_file_data(ABSPATH.'/wp-content/plugins/'.$product_item_name[$saswp_addon_name]['p-slug'], array('Version' => 'Version'), false);
						    $version = isset($plugin_data['Version'])?$plugin_data['Version']:$version;
						     }
						}else if($is_active=='1' && $has_license == 'no'){
							$button_name = 'Activate License';
							$is_active='';
						}else if($is_active=='0' && $has_license == 'yes'){
							$button_name = 'Activate';
						}else if($is_active=='0' && $has_license == 'no'){
							$button_name = 'Activate';
							$is_active='';
						}else if($status=="Activate"){
							$button_name = "Deactivate";
						}else if($status=="Deactivate"){
							$button_name = "Activate";
							$button= 'deactivate';
						}
					
						$popular_class = '';
						$popular = $this->ampforwppro_popular_plugin_list($title);
						if($popular){
							$popular_class = 'popular ';
						}
						$recommended_class = '';
						$recommended = $this->ampforwppro_recommended_plugin_list($title);
						if($recommended){
							$recommended_class = 'recommended';
						}
						$active = '';
						if($is_active==1){
							$active = 'active ';
						}
				?>
				<li class="<?php echo $active.$popular_class.$recommended_class;?>">
					<div class="ext-info">
						<input type="hidden" value="<?php echo $plugin_path?>" id="plugin_path-<?php echo $id?>">
						<input type="hidden" value="<?php echo $is_active?>" id="is_active-<?php echo $id?>">
						<strong class="ext-ver-title">
							<?php echo esc_attr($title);?>
						</strong>
							<?php if(!empty($changelog_url)){ ?>
						<a href="<?php echo esc_url( $changelog_url );?>" title="Update Plugin" class="afwpp-link" target="_blank"><span class="ext-var"><?php echo esc_attr($version);?></span></a>
						<?php }else{?>
						<span class="ext-var"><?php echo esc_attr($version);?></span>
					    <?php } ?>
						<?php echo $update;?>
						<p><?php echo esc_attr($description);?></p>
						<div class="ext-act-but">
							<?php if($button_name=="Activate" || $button_name=="Activate License"){?>
								<button type="button" class="button button-normal afwpp_activate_ext"  id="<?php echo $id?>"><?php echo esc_attr($button_name);?></button>
							<?php }?>
							<?php if($button_name=="Deactivate"){?>
							<button type="button" class="button button-primary afwpp_activate_ext"  id="<?php echo $id?>">Deactivate</button>
							<?php }?>
							<?php if($is_active=='1' && $menu_link!=""){?>
								<a href="<?php echo $menu_link;?>" title="Settings" class="afwpp-link" target="_blank"><button class="button btn-setting"><i class="dashicons dashicons-admin-generic"></i> Settings</button></a>
							<?php }?>
							<?php echo $docs;?>
						</div>
					</div>
				</li>
				<?php }?>
				<li class="not-found-plugins hide"> No Plugin Found</li>
			</ul>
		</div>
	</div>
	<?php }?>
</div>