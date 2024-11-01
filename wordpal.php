<?php
/*
Plugin Name: WordPal
Plugin URI: http://www.eaglehawkdesign.com
Description: Now you can turn your wordpress blog into a money-making membership site. Charge for subscription membership, to read posts etc. Support is via EaglehawkDesign.Com Only!
Version: 1.0.1
Date: July 27, 2009
Author: Dan O'Riordan
Author URI: http://www.danriordan.net
**************************************************************
Modifications:
**************************************************************
July 28, 2009 - WordPal Configuration Not Showing - Fixed.
**************************************************************
*/ 

//************************************************************
// Buy Now Image and Text
//
// Change the image and text to affect how the Pay Button displays.
//************************************************************
$buyNowImage = "https://www.paypal.com/en_US/i/btn/x-click-butcc.gif";
//
//************************************************************
// This line displays if the user is not logged in.
//************************************************************
$buyNowLogin = '<p>You must <a href="'.get_option('siteurl').'/wp-login.php">login</a> first.</p>';
//
//************************************************************
// Remove the comment (//) code from the $buyNowContent line if you'd like this text to appear before the Buy Now button.
// The amount and currency code will be displayed based on the [paybutton] settings.
//************************************************************
//$buyNowContent = '<p>This content may be purchased for %%amount%% %%currency_code%%.</p>';
//
//************************************************************
//End Buy Now Image and Text
//************************************************************

//************************************************************
// These are the titles of the pages that will
// be used for payments and subscriptions.
// The PostPaymentPage is triggered when a user tries to access the post editor.
// The SubscriptionPaymentPage is triggered when the user logs in.
// The CategoryPaymentPage is a standard post or page.
//************************************************************
$PostPaymentPageTitle = get_option('wordpal_post_payment_page');
$SubscriptionPaymentPageTitle = get_option('wordpal_subscription_payment_page');
$CategoryPaymentPageTitle = get_option('wordpal_category_payment_page');

//************************************************************
// Configuration page menu
//************************************************************
$epp_menu = 'options-general.php';
//************************************************************
// Uncomment this line if you would like
// the config page under the plugins menu.
//************************************************************
//$epp_menu = 'plugins.php';


add_action('admin_menu', 'WordPal_Config_Page');

$epp_payment_email = get_option("wordpal_payment_email");

if(strlen($epp_payment_email) > 0)
{
    add_action ('admin_head', 'WordPal');
    add_action ('load-page-new.php','WordPal_PostPayment');
    add_action ('load-post-new.php','WordPal_PostPayment');
    add_filter('the_content', 'WordPal_the_content');
    add_filter('the_content', 'WordPal_the_custom_content');
    add_filter('the_content', 'WordPalButton_the_content');
    add_action('edit_form_advanced', 'WordPal_Editor');
    add_action('edit_page_form', 'WordPal_Editor');
    add_action('publish_post', 'WordPal_Publish');
}



/********************************************************************
* Function:  WordPal_PostPayment()
* Check credits and forward to payment page if necessary.
********************************************************************/
function WordPal_PostPayment()
{

	global $wpdb, $user_ID, $PostPaymentPageTitle;
	$edit = false;
	
	$post_title = $PostPaymentPageTitle;
	$post_ID = $wpdb->get_var("SELECT ID FROM $wpdb->posts WHERE post_title = '$post_title'");

    if(WordPal_Content_Purchased($user_ID,$post_ID,''))
    {
		$content_items = get_usermeta($user_ID,'eppItems'); 
		is_array($content_items) ? '' : $content_items = array();
	    foreach($content_items as $item)
		{
           if($item['post_ID'] == $post_ID)
           {
				if($item['item_number'] > 0)
				{
				  $edit = true;
				}
		   }		   
      	}
    }
    
	if(!$edit && !empty($post_ID) && !WordPal_BypassCheck())
	{
		header('Location: '.get_option('siteurl').'/?page_id='.$post_ID);
            die();
	}    
}

/********************************************************************
* Function:  WordPal_Editor()
* Display instructions on editor page.
********************************************************************/
function WordPal_Editor()
{
    echo '<h3><a href="javascript:void(0)" onclick="eppDiv=document.getElementById(\'wordpal_editor\');eppDiv.style.display == \'none\' ? eppDiv.style.display = \'block\' :eppDiv.style.display = \'none\';">WordPal Help (Show/Hide)</a></h3><br/>';
    echo '<div id="wordpal_editor" style="display:none;border:solid 1px #CCCCCC;background-color:#F1F1F1;padding:10px;">';
    echo '<table cellpadding="5" cellspacing="0" border="0"><tr valign="top"><td><strong>[pay][/pay]</strong></td><td>To hide text from non-subscribers, place subscriber-only text between <strong>[pay][/pay]</strong> tags.<br/>';
    echo 'A user must be logged in to see text that is placed inside [pay][/pay] tags.</td></tr>';
    echo '<tr valign="top"><td><strong>[paybutton][/paybutton]</strong></td><td>Use [paybutton] tags to insert a <strong>Pay Now</strong> button into a post or page.  The [paybutton] tag can be used to charge for special content that even regular subscribers must purchase.  The [paybutton] may also be ';
    echo 'used to create multiple subscription options by including the Subscription Days.</td></tr>';	
    echo '<tr><td colspan="2"><br/><strong>Usage:</strong><br /><br />[paybutton]{Item Name*}|{Item Number*}|{Amount*}|{Currency Code}|{Subscription Days}|{Hidden Text}[/paybutton]</td></tr>';
    echo '<tr><td colspan="2"><br/>* Required</td></tr>';    
    echo '<tr><td colspan="2"><br/><strong>Hidden Text Example:</strong>  [paybutton]My Item|1000|30.00|||This text is hidden until you purchase it.[/paybutton]</td></tr>';
    echo '<tr><td colspan="2"><br/><strong>Subscription Example:</strong>  [paybutton]Monthly Subscription|1000|2.00||30|[/paybutton]</td></tr>';
    echo '</table><br/><br/></div><br/><br/>';
}

/********************************************************************
* Function:  WordPal_Publish()
* Decrement credits when post is published.
********************************************************************/
function WordPal_Publish($post_ID)
{
    global $wpdb, $user_ID, $PostPaymentPageTitle;
    
    $post_title = $PostPaymentPageTitle;
    $post_ID = $wpdb->get_var("SELECT ID FROM $wpdb->posts WHERE post_title = '$post_title'");
    
    if(WordPal_Content_Purchased($user_ID,$post_ID,''))
    {
		$content_items = get_usermeta($user_ID,'eppItems'); 
		is_array($content_items) ? '' : $content_items = array();
	    for($ctr = 0;$ctr < count($content_items);$ctr++)
		{
		   $item = $content_items[$ctr];
           if($item['post_ID'] == $post_ID)
           {
				if($item['item_number'] > 0)
				{
				   //Subtract 1 and update post counter.
				   $content_items[$ctr]['item_number'] = $item['item_number'] - 1;
                   update_usermeta($user_ID,'eppItems',$content_items); 				   
				}
		   }		   
      	}
   	}
}


/********************************************************************
* Function:  WordPal_the_content()
* Hide text if not a user.
********************************************************************/
function WordPal_the_content($text) {

   global $epp_more_login;
   global $user_ID;
   
   unset($tag_pattern);
   $tag_pattern = '/(\[pay\](.*?)\[\/pay\])/is';

   if(strlen($epp_more_login) == 0)
   {
       $epp_more_login = get_option('wordpal_more_login');
   }
    
   if(! is_user_logged_in())
   {
       $text = WordPal_HideText($text, $tag_pattern);
   }
   else
   {
      if (WordPal_Expired($user_ID)) {
         $text = WordPal_HideText($text, $tag_pattern);
      }
      else
      {
	    $tag_pattern = '/\[pay\]/is';
        $text = preg_replace($tag_pattern,'',$text); 
	    $tag_pattern = '/\[\/pay\]/is';
        $text = preg_replace($tag_pattern,'',$text); 
      }
   }

   return $text;

}

/********************************************************************
* Function:  WordPal_the_custom_content()
* Hide text based on categories.
********************************************************************/
function WordPal_the_custom_content($text) {

   global $epp_more_login, $post, $post_ID, $wpdb, $CategoryPaymentPageTitle, $user_ID;

    if(!WordPal_BypassCheck())
    {
	      $post_ID = $post->ID;
	   
	      $temp = get_option("wordpal_categories");
	      $temp = str_replace(", ",",",$temp);
	      if(strlen($temp) > 0)
	      {
	          $cats = explode(",",$temp);
	      }
	
	      if(strlen($epp_more_login) == 0)
	      {
	          $epp_more_login = get_option('wordpal_more_login');
	      }
	
		$categories = wp_get_post_categories($post->ID);
		$category_id = '';
	    if( is_array( $categories ) ) { 
			foreach ( $categories as $cat_id ) { 
	    	    if( is_array( $cats))
                      {
                        foreach($cats as $cat)
	     	      {
	     	    	//Match on Cat ID
	     	    	if($cat == $cat_id)
	     	    	{
	     	    		$category_id = $id;
	     	    		break;
	     	    	}
	     	    	//Match on Cat Name
	        	  	$id = get_cat_ID($cat);
	           		if($id == $cat_id)
	           		{
						$category_id = $id;
						break;
					}
	           	}
			}
                     }
	    }

		if(strlen($category_id) > 0 || strlen(get_post_meta($post_ID, "cat", true)) > 0)
	    {
	    	if(! is_user_logged_in())
	        {
	        	$text = $epp_more_login;
	        }
	        else
	        {
	        	//Page
	            if(strlen($category_id) == 0)
	            {
	            	//Loop through WordPal Categories
		           if(is_array($cats))
                              {
                                foreach($cats as $cat)
		              {
		              //Custom field matches the category (could be Cat ID or Cat Name).	
		              if($cat == get_post_meta($post_ID, "cat", true))
		              {
		              	if(is_numeric($cat))
		              	{
		              		$category_id = $cat;
		              	}
		              	else
		              	{
		                  	$category_id = get_cat_ID($cat);
		              	}
		              	  break;
		              }
		            }
                            }
	            }
	            //Check for purchase by category.
	            $post_title = $CategoryPaymentPageTitle;
	            $cat_post_ID = $wpdb->get_var("SELECT ID FROM $wpdb->posts WHERE post_title = '$post_title'");
	            if(!WordPal_Content_Purchased($user_ID,$cat_post_ID,$category_id))
	            {
		            $text = $epp_more_login;
	            }
	        }
	    }
    }
    return $text;
}


function WordPalButton_the_content($text) {

   global $epp_more_login, $user_ID, $post_ID, $post, $PostPaymentPageTitle, $SubscriptionPaymentPageTitle, $CategoryPaymentPageTitle ;

   $post_ID = $post->ID;

   unset($tag_pattern);
   $tag_pattern = '/(\[paybutton\](.*?)\[\/paybutton\])/is';


   preg_match_all($tag_pattern, $text, $matches);

   if(is_array($matches))
   {
     foreach($matches as $match)
     {
        foreach($match as $repl)
        {
	        $buttonVars = explode("|",$repl);
	        if(count($buttonVars) >= 6)
	        {
                 $pay_text = $buttonVars[5];
                 if(!WordPal_BypassCheck() && !WordPal_Content_Purchased($user_ID,$post_ID,$buttonVars[1]))
                 {
                 	$returnURL = '';
                 	 if($post->post_title == $SubscriptionPaymentPageTitle || $post->post_title == $CategoryPaymentPageTitle)
                 	 {
                 	 	$returnURL = get_option("siteurl").'/index.php';
                 	 }
                 	 if($post->post_title == $PostPaymentPageTitle)
                 	 {
                 	 	$returnURL = get_option("siteurl").'/wp-admin/index.php';                 	 	
                 	 }
  	             $pay_text = WordPal_Form($buttonVars, $returnURL);
                 }
	             $text = str_replace($repl,$pay_text,$text);  
	        }
       }

     }
   }

   $tag_pattern = '/\[paybutton\]/is';
   $text = preg_replace($tag_pattern,'',$text); 
   $tag_pattern = '/\[\/paybutton\]/is';
   $text = preg_replace($tag_pattern,'',$text); 

   return $text;

}

function WordPal_Content_Purchased($user_ID, $post_ID, $epp_content_item_number)
{
      $content_items = get_usermeta($user_ID,'eppItems'); 
      is_array($content_items) ? '' : $content_items = array();
      $found = false;
      if(is_array($content_items))
      {
        foreach($content_items as $item)
        {
           	if($item['post_ID'] == $post_ID && ($item['item_number'] == $epp_content_item_number || $epp_content_item_number == ''))
           	{
				$found = true;
	     	}
        }
      }


    return $found;
}



function WordPal_HideText($text, $tag_pattern){

    global $epp_more_login;

    $login_text = '...<p><a href="'.get_settings('siteurl').'/wp-login.php">'
               .$epp_more_login.'</a></p>';
    $text = preg_replace($tag_pattern,$login_text,$text); 

    return $text;
}


function WordPal_Config_Page() {
	global $wpdb;
	global $epp_menu;

	if ( function_exists('add_submenu_page') )
		add_submenu_page($epp_menu, __('WordPal Configuration'), __('WordPal Configuration'), 'manage_options', 'wordpal/wordpal_config.php', '');
}

function WordPal_Expired($user_ID) {

    $ret = false;
    $result = WordPal_PaymentDateGet($user_ID);
    
    if (!WordPal_BypassCheck() && (strlen($result) == 0 || $result < date("Y-m-d"))) {
        $ret = true;
    }
    
    return $ret;
}

function WordPal_PaymentDateGet($user_ID) {

  global $epp_trial;
  
  empty($epp_trial)  ? $epp_trial = get_option("wordpal_trial"): '';  

  //Retrieve the next payment date.
  $result = get_usermeta($user_ID,'NextPayment'); 

  //Trial Period
  if (strlen($result) == 0 && $epp_trial > 0)
  {
     $trial_ends = WordPal_DateAdd($epp_trial);
     update_usermeta($user_ID,'NextPayment',$trial_ends); 
     //Retrieve the next payment date.
     $result = $trial_ends;
  }

  return $result;
}
  
/********************************************************************
* Function:  WordPal()
********************************************************************/
function WordPal() {

  global $epp_user_msg, $epp_redirect, $PostPaymentPageTitle, $wpdb;

  /* Retrieve the id of the current user. */
  global $user_ID;
  global $user_login;
  get_currentuserinfo();


  /********************************************************************
   * Check the username against the bypass list.
   ********************************************************************/
  if(WordPal_BypassCheck())
  {
     return;
  }

  if (WordPal_Expired($user_ID)) {
     $text = WordPal_PageGet();
     if(strlen($text) > 0)
     {
        $epp_user_msg = WordPalButton_the_content($text);
     }
     else
     {
        $formVars = array();
        echo WordPal_Form($formVars);
		//Submit the PayPal form.
		echo '<script type="text/javascript">';
                  echo 'window.onload = function(){';
		echo 'setTimeout(\'document.getElementById("eppForm").submit()\',3000);};';
		echo '</script>';
     }
     WordPal_Alert($epp_user_msg); 
     die(); 
  }

}

function WordPal_Form($formVars, $returnURL='')
{
  /********************************************************************
   * WordPal variables. 
   * 
   * $epp_payment_email: Your PayPal email address.
   * $epp_return_URL:    Return URL when the PayPal transaction is complete.
   * $epp_item_name:     An item name that you assign.
   * $epp_auth_code:     This code will be combined with today's date and 
   *                     encrypted to deter PayPal impersonators.    
   * $epp_item_number:   An item number that you assign.
   * $epp_amount:        The amount you charge for your service.
   * $epp_currency_code  The currency code for the payment.
   * $epp_days_to_add:   The number of days between payments.
   * $epp_user_msg:      Message that will display just before the 
   *                     user is sent to PayPal. 
   * $epp_bypass:        Comma-separated list of users who will bypass
   *                      this process.
   * $epp_trial:         Trial period for new users.
   *******************************************************************/
  global $epp_action;
  global $epp_payment_email;
  global $epp_return_URL;
  global $epp_item_name;
  global $epp_item_number;
  global $epp_amount;
  global $epp_auth_code;
  global $epp_currency_code;
  global $epp_days_to_add;
  global $epp_user_msg;
  global $epp_trial;

  global $buyNowImage;
  global $buyNowLogin;
  global $buyNowContent;
  
  global $user_ID;
  global $post_ID;
  
  $plugin_url = get_option( 'siteurl' ) . '/wp-content/plugins/' . plugin_basename(dirname(__FILE__)) ;

  if(count($formVars) >= 5)
  {
	$epp_item_name = $formVars[0];
	$epp_item_number = $formVars[1];
	$epp_amount = $formVars[2];
	$epp_currency_code = $formVars[3];
	$epp_days_to_add = $formVars[4];
	$epp_content = $formVars[5];
	if($formVars[4] == 0 || strlen($formVars[4]) == 0)
	{
		$epp_days_to_add = -1;
	}
   }
  

   empty($epp_payment_email) ? $epp_payment_email = get_option("wordpal_payment_email"):'';
   if(strlen($returnURL) > 0)
   {
   		$epp_return_URL = $returnURL;
   }
   empty($epp_return_URL) ? $epp_return_URL = WordPal_selfURL():'';
   empty($epp_item_name) ? $epp_item_name = get_option("wordpal_item_name"):'';
   empty($epp_item_number) ? $epp_item_number = get_option("wordpal_item_number"):'';
   empty($epp_amount) ? $epp_amount = get_option("wordpal_amount"):'';
   empty($epp_currency_code) ? $epp_currency_code = get_option("wordpal_currency_code"):'';
   empty($epp_days_to_add) ? $epp_days_to_add = get_option("wordpal_days_to_add"):'';
   empty($epp_user_msg)  ? $epp_user_msg = get_option("wordpal_user_msg"):'';
   empty($epp_trial)  ? $epp_trial = get_option("wordpal_trial"): '';
  
  /********************************************************************
   * LIVE - PayPal Live URL
   ********************************************************************/
   $epp_action = "https://www.paypal.com/cgi-bin/webscr";
  /********************************************************************
   * TESTING ONLY - PayPal Sandbox
   ********************************************************************/
   empty($epp_test) ? $epp_test = get_option("wordpal_test"): '';
   if($epp_test == "checked")
   {
      $epp_action = "https://www.sandbox.paypal.com/cgi-bin/webscr";
   }
  /********************************************************************/

  //Always bypass 'admin' user.
  $epp_bypass = "admin, ".$epp_bypass;

  $epp_form = '<div style="text-align:left">';
  if($epp_days_to_add < 0)
  {
     $buyNowContentShow = str_replace('%%amount%%',$epp_amount,$buyNowContent);
     $buyNowContentShow = str_replace('%%currency_code%%',$epp_currency_code,$buyNowContentShow);
     $epp_form .= $buyNowContentShow;
  }  
  $epp_user_ID = $user_ID;
  if( is_user_logged_in() || strtolower($epp_content) == 'login=no[/paybutton]')
  {
     $epp_form .= '<form id="eppForm" action="'.$epp_action.'" method="post" target="_top" style="text-align:left">';
     if(strtolower($epp_content) == 'login=no[/paybutton]')
     {
		$epp_user_ID = '';
     }
  }
  else
  {
     $epp_form .= '<form action="'.get_option('siteurl').'/wp-login.php" method="get" target="_top" style="text-align:left">';
     $epp_form .= $buyNowLogin;
  }
  $epp_form .= '<input type="image" src="'.$buyNowImage.'" border="0" name="submit" alt="Make payments with PayPal - it\'s fast, free and secure!">';

  $epp_form .= '<input type="hidden" name="cmd" value="_xclick">';
  $epp_form .= '<input type="hidden" name="bn" value="PP-BuyNowBF">';
  $epp_form .= '<input type="hidden" name="amount" value="'.$epp_amount.'">';
  $epp_form .= '<input type="hidden" name="rm" value="2">';
//Subscription Code -- Not used due to IPN issues.
//     $epp_period = WordPal_Period($epp_days_to_add);
//     $epp_period_arr = explode("|",$epp_period);
//     $epp_form .= '<input type="hidden" name="cmd" value="_xclick-subscriptions">';
//     $epp_form .= '<input type="hidden" name="lc" value="US">';
//     $epp_form .= '<input type="hidden" name="bn" value="PP-SubscriptionsBF">';
//     $epp_form .= '<input type="hidden" name="a3" value="'.$epp_amount.'">';
//     $epp_form .= '<input type="hidden" name="p3" value="'.$epp_period_arr[0].'">';
//     $epp_form .= '<input type="hidden" name="t3" value="'.$epp_period_arr[1].'">';
//     $epp_form .= '<input type="hidden" name="src" value="1">';
//     $epp_form .= '<input type="hidden" name="sra" value="1">';
//     $epp_days_to_add = 9999;
  $epp_form .= '<input type="hidden" name="business" value="'.$epp_payment_email.'">';
  $epp_form .= '<input type="hidden" name="item_name" value="'.$epp_item_name.'">';
  $epp_form .= '<input type="hidden" name="item_number" value="'.$epp_item_number.'">';
  $epp_form .= '<input type="hidden" name="currency_code" value="'.$epp_currency_code.'">';
  $epp_form .= '<input type="hidden" name="return" value="'.$plugin_url.'/wordpal_return.php">';
  $epp_form .= '<input type="hidden" name="cancel_return" value="'.$epp_return_URL.'">';
  $epp_form .= '<input type="hidden" name="no_shipping" value="1">';
  $epp_form .= '<input type="hidden" name="no_note" value="1">';

  $epp_auth_code= get_option("wordpal_auth_code");
  /* MD5 encryption of auth code and date.*/
  $epp_auth_code .= date("Y-m-d");
  $epp_auth_code = md5($epp_auth_code);

  $epp_form .= '<input type="hidden" name="custom" value="'.$epp_user_ID.'|'.$epp_days_to_add.'|'.$post_ID.'|'.$epp_item_number.'|'.$epp_auth_code.'|'.$epp_return_URL.'|">';

  $epp_form .= '</form></div>';

  //die($epp_form);
  return $epp_form;

}

function WordPal_Period($epp_days_to_add)
{
    $t3 = 'D';
    $p3 = $epp_days_to_add;
    if ($epp_days_to_add <= 30)
    {
        //Default
    }
    else
    {
    	if ($epp_days_to_add < 366)
      {	    
        $t3 = 'M';
            $p3 = ceil($p3 / 30);
            if($p3 >= 12)
            {
               $t3 = 'Y';
               $p3 = 1;
            }
      }
      else
      {
         if ($epp_days_to_add >= 366)
        {
            $t3 = 'Y';
            $p3 = ceil($p3 / 365);
        }
      }
    }

    return $p3.'|'.$t3;

}


function WordPal_BypassCheck()
{
  global $epp_bypass;
  global $user_login;

  $bypass = false;
  $login = strtolower($user_login);

  /********************************************************************
   * Check the bypass string.  If the user is in the bypass string,
   * then no payment is required.
   ********************************************************************/
   empty($epp_bypass) ? $epp_bypass = get_option("wordpal_bypass"):'';

   $bypass_array = split(',',strtolower($epp_bypass));
   for ($i=0;$i<count($bypass_array);$i++) {
      if(trim($bypass_array[$i]) == $login)
      {
        $bypass = true;
      }

   }

   if(!$bypass && current_user_can( 'manage_options' )) 
   {
      $bypass = true;
   }

   return $bypass;

}

function escape(&$array) {
	global $wpdb;
	
	foreach ( (array) $array as $k => $v ) {
		if (is_array($v)) {
			escape($array[$k]);
		} else if (is_object($v)) {
				//skip
			} else {
				$array[$k] = $wpdb->escape($v);
			}
	}
}

function WordPal_PageGet()
{
    global $wpdb, $SubscriptionPaymentPageTitle; 
    $post_title = $SubscriptionPaymentPageTitle;
    $post_ID = $wpdb->get_var("SELECT ID FROM $wpdb->posts WHERE post_title = '$post_title'");

    $postdata = wp_get_single_post($post_ID, ARRAY_A);
    extract($postdata);

    $content = $postdata['post_content'];

    return $content;
}


function WordPal_ChangeBK() 
{ 
   echo '<iframe style="position:absolute;left:0px;top:0px;width:100%;height:9999px;border:solid 2px black;background-color:#C0C0C0;padding:20px"></iframe><div style="position:absolute;left:0px;top:0px;width:100%;height:9999px;border:solid 2px black;background-color:#C0C0C0;padding:20px"></div>';
}

//Alert box.
function WordPal_Alert($msg) 
{ 
   WordPal_ChangeBK(); 
   echo '<div style="position:absolute;left:100px;top:200px;border:solid 2px black;background-color:white;padding:20px">'.$msg.'</div>';
}

//DateAdd
function WordPal_DateAdd($v,$d=null ,$f="Y-m-d"){ 
  $d=($d?$d:date("Y-m-d")); 
  return date($f,strtotime($v." days",strtotime($d))); 
}

function WordPal_selfURL() {
	$s = empty($_SERVER["HTTPS"]) ? ''
		: ($_SERVER["HTTPS"] == "on") ? "s"
		: "";
	$protocol = WordPal_strleft(strtolower($_SERVER["SERVER_PROTOCOL"]), "/").$s;
	$port = ($_SERVER["SERVER_PORT"] == "80") ? ""
		: (":".$_SERVER["SERVER_PORT"]);
	return $protocol."://".$_SERVER['SERVER_NAME'].$port.$_SERVER['REQUEST_URI'];
}
function WordPal_strleft($s1, $s2) {
	return substr($s1, 0, strpos($s1, $s2));
}


?>