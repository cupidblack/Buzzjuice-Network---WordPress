<?php

        $settings_url = esc_url(admin_url('admin.php?page=pwawp-extension-manager'));
        $license_info = get_option( 'pwawppro_license_info');
        global $all_extensions_data;
        $item_name = "Membership Bundle";
        if(isset($license_info->license_key)){
            $license_k = $license_info->license_key;
        }
        if(isset($license_info->download_id)){
            $download_id = $license_info->download_id;
        }
        global $all_extensions_data;
        $renew = "no";
        $license_exp = "";
        if($license_info){
            $license_exp = date('Y-m-d', strtotime($license_info->expires));
            $license_exp_d = date('d-F-Y', strtotime($license_info->expires));
            $license_info_lifetime = $license_info->expires;
        }
        if(isset($license_info->license_key)){
            $license_k = $license_info->license_key;
        }
        if(isset($license_info->download_id)){
            $download_id = $license_info->download_id;
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
            $exp_id = '';
            $expire_msg = '';
            $renew_mesg = '';
            $span_class = '';
            $alert_icon = '';
            $ext_settings_url = 'ext_url';
            if ( $days == 'Lifetime' ) {
                $renew_url = "https://pwa-for-wp.com/order/?edd_license_key=".$license_k."&download_id=".$download_id."";
                    $expire_msg_before = '<span class="before_msg_active">Your License is</span>';
                    $expire_msg = " Valid for Lifetime ";
                    $span_class = "saswp_addon_icon dashicons dashicons-yes pro_icon";
                    $color = 'color:green';
                }
            elseif( $days>=0 && $days<=30){
                $renew_url = "https://pwa-for-wp.com/order/?edd_license_key=".$license_k."&download_id=".$download_id."";
                $expire_msg_before = '<span class="before_msg_Pro">Your <span class="zeroto30">License is</span></span> <span class="pwafpro-alert">expiring in '.$days.' days</span><a target="blank" class="renewal-license" href="'.$renew_url.'"><span class="renew-lic">'.esc_html__('Renew', 'accelerated-mobile-pages').'</span></a>';
                $alert_icon = '<span class="saswp_addon_icon dashicons dashicons-warning pro_warning"></span>';
                // $span_class = "aeaicon dashicons dashicons-alert pro_icon";
                $color = 'color:green';
            }else if($days<0){
                $ext_settings_url = 'ext_settings_url';
                $renew_url = "https://pwa-for-wp.com/order/?edd_license_key=".$license_k."&download_id=".$download_id."";
                $expire_msg = " Expired ";
                $expire_msg_before = '<span class="Pro_inactive">Your <span class="less_than_zero">License has been</span></span>';
                $exp_class = 'expired';
                $exp_id = 'exp';
                $exp_class_2 = 'renew_license_key_';
                $span_class = "aeaicon dashicons dashicons-no";
                $renew_mesg = '<a target="blank" class="renewal-license" href="'.$renew_url.'"><span class="renew-lic">'.esc_html__('Renew', 'accelerated-mobile-pages').'</span></a>';
                $color = 'color:red';
            }else{
                $expire_msg = " Active ";
                $expire_msg_before = '<span class="before_msg_Pro_active">Your License is</span>';
                $span_class = "aeaicon dashicons dashicons-yes pro_icon";
                $color = 'color:green';
            }


            if( class_exists('PWAFORWPPROExtensionManager') ) {
        $license_info = get_option( 'pwawppro_license_info');
        $fname = '';
    if ($license_info) {
    $fname = $license_info->customer_name;
    $fname = substr($fname, 0, strpos($fname, ' '));
    $check_for_Caps = ctype_upper($fname);
    if ( $check_for_Caps == 1 ) {
    $fname =  strtolower($fname);
    $fname =  ucwords($fname);
    }
    else{
        $fname =  ucwords($fname);   
    }
    $proDetailsProvide = "<div class='pwafwp-extension-mgr-main'>
    <span class='pwafwp-extension-mgr-info'>
    ".$alert_icon."<span class='activated-plugins'>Hi <span class='pwafwp_pro_key_user_name'>".esc_html($fname)."</span>".','."
    <span class='activated-plugins'> ".$expire_msg_before." <span class='inner_span' id=".$exp_id.">".$expire_msg."</span></span>
    <span class='".$span_class."'></span>".$renew_mesg ;    
$proDetailsProvide .= "<a class=".$ext_settings_url." href='".$settings_url."'><i class=\"dashicons-before dashicons-admin-generic\"></i></a></span></div>";
echo $proDetailsProvide;
}

}