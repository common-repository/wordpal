<?php
include_once('../../../wp-config.php');
include_once(ABSPATH . 'wp-admin/includes/admin.php');
include_once(ABSPATH . 'wp-includes/functions.php');
include_once(dirname(__FILE__).'/wordpal.php');

  global $epp_user_msg, $epp_auth_code, $epp_redirect, $PostPaymentPageTitle, $wpdb;

  /* Retrieve the id of the current user. */
  global $user_ID;
  global $user_login;
  get_currentuserinfo();

  /********************************************************************
   * Check the username against the bypass list.
   ********************************************************************/
  if(WordPal_BypassCheck())
  {
     die("The current user is on the bypass list and will not be processed.");
  }


 /********************************************************************
   * On a return from PayPal, this will contain the $user_ID and the 
   * days to add.
   ********************************************************************/
  $custom = $_POST["custom"];


  $epp_auth_code= get_option("wordpal_auth_code");

  /* MD5 encryption of auth code and date.*/
  $epp_auth_code .= date("Y-m-d");
  $epp_auth_code = md5($epp_auth_code);


  if(strlen($custom) > 0)
  {
     $paypal_return = explode('|',$custom);
     if(count($paypal_return) >= 6)
     {
        $epp_user_id = $paypal_return[0];
        $epp_days_to_add = $paypal_return[1];
        $epp_post_id = $paypal_return[2];
        $epp_item_number = $paypal_return[3];
        $epp_auth_code_ret = $paypal_return[4];
        $epp_redirect = $paypal_return[5];
     }
  }
 
  if($epp_auth_code != $epp_auth_code_ret)
  {
	die("Authorization code check failed.");
  }

  /********************************************************************
   * Posted back from PayPal
   * Update the NextPayment date.
   ********************************************************************/
  if($epp_user_id == $user_ID)
  {
     if($epp_days_to_add > 0)
     {
         update_usermeta($user_ID,'NextPayment',WordPal_DateAdd($epp_days_to_add)); 
     }
     else
     {
         if(strlen($epp_item_number) != '')
         {
	  	      $content_items = get_usermeta($user_ID,'eppItems'); 
              is_array($content_items) ? '' : $content_items = array();
              $found = false;
              foreach($content_items as $item)
              {
                   if($item['post_ID'] == $epp_post_id && $item['item_number'] == $epp_item_number)
                   {
						$found = true;
				   }
              }
              if(!$found)
              {
                  array_push($content_items, array('post_ID'=>$epp_post_id,'item_number'=>$epp_item_number));
                  update_usermeta($user_ID,'eppItems',$content_items); 
              }
         }
     }
     $location = get_option('siteurl');
     if(!empty($epp_post_id))
     {
        $post = get_post($epp_post_id);
        if($post->post_title != $PostPaymentPageTitle)
        {
            if(!empty($post->guid))
            {
				$location = $post->guid;
			}
			else
			{
				$location .= '?page_id='.$epp_post_id;			
			}
		}
     }
	//echo '<script type="text/javascript">location.href="'.$epp_redirect.'";</script>';
	wp_redirect($epp_redirect);
  }
?>
<html>
<head>
<title>WordPal Return Page</title>
<style>
body {font-family:Arial}
</style>
<script type="text/javascript">
window.onload = function(){setTimeout('showNotice()',5000);};
function showNotice()
{
	document.getElementById("notice").style.display="block";
}
</script>
</head>
<body>
<div id="notice" style="display:none"><p>Waiting to be redirected back to the website.</p>
<p>Please DO NOT refresh this page.</p>
<p>Contact support if you are not redirected back to the website.</p>
</div>
</body>
</html>