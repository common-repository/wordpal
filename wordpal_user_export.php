<?php
include_once('../../../wp-config.php');
include_once(ABSPATH . 'wp-admin/includes/admin.php');
include_once(ABSPATH . 'wp-includes/functions.php');

header("Content-Type: application/vnd.ms-excel");
header("Expires: 0");
header("Cache-Control: must-revalidate, post-check=0, pre-check=0");

global $wpdb;
$rs = $wpdb->get_results("select user_id from $wpdb->usermeta where meta_key='eppItems'",ARRAY_A);
$tab = "\t";
$crlf = "\r\n";
$output = "username $tab next payment date $tab title $tab link $tab post id $tab item number $crlf";

if($rs)
{
	foreach($rs as $row)
	{
	
		$user_login = get_userdata($row["user_id"])->user_login;
		$next_payment_date = get_usermeta($row["user_id"],'NextPayment');
		$content_items = get_usermeta($row["user_id"],'eppItems');
		if(!empty($content_items))
		{
			is_array($content_items) ? '' : $content_items = array();
			foreach($content_items as $item)
			{
				$output .= $user_login;
				$output .= $tab.$next_payment_date;
				$postdata = wp_get_single_post($item['post_ID'], ARRAY_A);
				extract($postdata);
				$title = $postdata['post_title'];
				$output .= $tab.$title;	
				$output .= $tab.get_option('siteurl').'?p='.$item['post_ID'];
				$output .= $tab.$item['post_ID']; 				
				$output .= $tab.$item['item_number'];
				$output .= $crlf;
			}
		}
	}
}

echo $output;