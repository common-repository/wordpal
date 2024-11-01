<?php
//WordPal Configuration Form

	/********************************************************************
	* WordPal variables. 
	* 
	* $epp_payment_email: Your PayPal email address.
	* $epp_return_URL:    Return URL when the PayPal transaction is complete.
	* $epp_item_name:     An item name that you assign.
	* $epp_item_number:   An item number that you assign.
	* $epp_amount:        The amount you charge for your service.
	* $epp_currency_code  The currency code for the payment.
	* $epp_days_to_add:   The number of days between payments.
	* $epp_auth_code:     This code will be combined with today's date and 
	*                     encrypted to deter PayPal impersonators.    
	* $epp_user_msg:      Message that will display just before the 
	*                     user is sent to PayPal. 
	* $epp_bypass:        Comma-separated list of users who will bypass
	*                      this process.
	* $epp_trial:         Trial period for new users.
      * $epp_categories     Comma-separated list of categories which will
      *                     exclude posts unless the category is purchased.
	*******************************************************************/
	global $epp_action;
	global $epp_payment_email;
	global $epp_return_URL;
	global $epp_item_name;
	global $epp_item_number;
	global $epp_amount;
	global $epp_currency_code;
	global $epp_days_to_add;
	global $epp_auth_code;
	global $epp_user_msg;
	global $epp_trial;
	global $epp_content;
	global $epp_category_payment_page;
	global $epp_post_payment_page;
	global $epp_subscription_payment_page;

	if ( isset($_POST['submit']) ) {
		if ( !current_user_can('manage_options') )
			die(__('Cheatin&#8217; uh?'));
			$epp_test  = $_POST['epp_test'];
			update_option('wordpal_test', $epp_test );
			$epp_payment_email  = $_POST['epp_payment_email'];
			update_option('wordpal_payment_email', $epp_payment_email );
			$epp_return_URL = $_POST['epp_return_URL'];
			update_option('wordpal_return_URL', $epp_return_URL );
			$epp_item_name = $_POST['epp_item_name'];
			update_option('wordpal_item_name', $epp_item_name );
			$epp_item_number = $_POST['epp_item_number'] + "";
			update_option('wordpal_item_number', $epp_item_number );
			update_option('wordpal_amount', "" );
			$epp_amount = $_POST['epp_amount'];
			$epp_amount = number_format($epp_amount,2,".","");
			update_option('wordpal_amount', $epp_amount );
			$epp_currency_code = $_POST['epp_currency_code'];
			update_option('wordpal_currency_code', $epp_currency_code );
			$epp_days_to_add = $_POST['epp_days_to_add'];
			update_option('wordpal_days_to_add', $epp_days_to_add );
			$epp_trial = $_POST['epp_trial'];
			update_option('wordpal_trial', $epp_trial );
			$epp_auth_code = $_POST['epp_auth_code'];
			update_option('wordpal_auth_code', $epp_auth_code );
			$epp_user_msg = $_POST['epp_user_msg'];
			update_option('wordpal_user_msg', $epp_user_msg );
			$epp_bypass = $_POST['epp_bypass'];
			update_option('wordpal_bypass', $epp_bypass );
			$epp_categories = $_POST['epp_categories'];
			update_option('wordpal_categories', $epp_categories );
			$epp_more_login = $_POST['epp_more_login'];
			update_option('wordpal_more_login', $epp_more_login );
			$epp_category_payment_page = $_POST['epp_category_payment_page'];
			update_option('wordpal_category_payment_page', $epp_category_payment_page );
			$epp_post_payment_page = $_POST['epp_post_payment_page'];
			update_option('wordpal_post_payment_page', $epp_post_payment_page );			
			$epp_subscription_payment_page = $_POST['epp_subscription_payment_page'];
			update_option('wordpal_subscription_payment_page', $epp_subscription_payment_page );			
		}
		else {
			//Set Defaults
			$epp_bypass  = get_option('wordpal_bypass');
			if(strlen($epp_bypass) == 0) {
			/* Retrieve the id of the current user. */
			   global $user_login;
			   get_currentuserinfo();
			   $epp_bypass = $user_login;
			   update_option('wordpal_bypass', $epp_bypass );
			}
			$epp_currency_code  = get_option('wordpal_currency_code');
			if(strlen($epp_currency_code) == 0) {
			   $epp_currency_code = "USD";
			   update_option('wordpal_currency_code', $epp_currency_code );
			}
			$epp_user_msg = get_option('wordpal_user_msg');
			if(strlen($epp_user_msg) == 0) {
			   $epp_user_msg = "<p>You will now be redirected to PayPal<br/>"
							  ."for payment processing.</p>"
							  ."<p>You will return to this site once you<br/>"
							  ."have completed the payment process.</p>";
			   update_option('wordpal_user_msg', $epp_user_msg );
			}
			$epp_more_login = get_option('wordpal_more_login');
			if(strlen($epp_more_login) == 0) {
			   $epp_more_login = "Login/Register for more.";
			   update_option('wordpal_more_login ', $epp_more_login );
			}
			
			$epp_subscription_payment_page = get_option('wordpal_subscription_payment_page');
			if(strlen($epp_subscription_payment_page) == 0) {
			   $epp_subscription_payment_page = "[pay]";
			   update_option('wordpal_subscription_payment_page', $epp_subscription_payment_page);
			}
			$epp_post_payment_page = get_option('wordpal_post_payment_page');
			if(strlen($epp_post_payment_page) == 0) {
			   $epp_post_payment_page = "[Payment Options]";
			   update_option('wordpal_post_payment_page', $epp_post_payment_page);
			}			
			$epp_category_payment_page = get_option('wordpal_category_payment_page');
			if(strlen($epp_category_payment_page) == 0) {
			   $epp_category_payment_page = "[Payment Levels]";
			   update_option('wordpal_category_payment_page', $epp_category_payment_page);
			}						
			
		}

?>

<div class="wrap">
<h2><?php _e('WordPal Configuration'); ?></h2>
	<p><?php printf(__('Configure WordPal below.&nbsp;&nbsp;For help, see the <a href="http://www.eaglehawkdesign.com">support page</a>')); ?></p>
<h3>&#x0bb; <a href="options-general.php?page=wordpal/wordpal_users.php">User Manager</a></h3>
<form action="" method="post" id="WordPal-Conf" style="margin: 0; width: 100%; ">
<p><input id="epp_test" name="epp_test" type="checkbox" value="checked" <?php echo get_option('wordpal_test'); ?> />&nbsp;&nbsp;<label for="epp_test">
<?php _e('<a href="http://developer.paypal.com" target=_blank>Use PayPal Sandbox</a> (testing only)'); ?></label></p>
<p>To disable login subscriptions, enter 9999 in the <strong>Trial Period</strong> below.</p>
<table width="100%" cellpadding="3" cellspacing="3"> 
<!--Begin Field-->
<tr  class='alternate'>
<td><label for="epp_auth_code"><?php _e('Authorization Code'); ?></label></td>
<td><input id="epp_auth_code" name="epp_auth_code" type="text" size="30" maxlength="30" value="<?php echo get_option('wordpal_auth_code'); ?>"  /></td>
<td>Enter a unique code here.  It will be combined with today's date and encrypted to deter evil PayPal impersonators.<br/>Example:  PH8675309</td>
</tr>
<!--End Field-->
<!--Begin Field-->
<tr>
<td><label for="epp_payment_email"><?php _e('Your PayPal Email Address'); ?></label></td>
<td><input id="epp_payment_email" name="epp_payment_email" type="text" size="30" maxlength="50" value="<?php echo get_option('wordpal_payment_email'); ?>"  /> </td>
<td>The email address through which you will receive payments.</td>
</tr>
<!--End Field-->
<!--Begin Field-->
<tr class='alternate'>
<td><label for="epp_item_name"><?php _e('Item Name'); ?></label></td>
<td><input id="epp_item_name" name="epp_item_name" type="text" size="30" maxlength="150" value="<?php echo get_option('wordpal_item_name'); ?>"  /> </td>
<td>The name of the item or service to display on the PayPal order form.</td>
</tr>
<!--End Field-->
<!--Begin Field-->
<tr>
<td><label for="epp_item_number"><?php _e('Item Number'); ?></label></td>
<td><input id="epp_item_number" name="epp_item_number" type="text" size="10" maxlength="30" value="<?php echo get_option('wordpal_item_number'); ?>"  /> </td>
<td>A number, defined by you, to represent this item for tracking purposes.</td>
</tr>
<!--End Field-->
<!--Begin Field-->
<tr  class='alternate'>
<td><label for="epp_amount"><?php _e('Payment Amount'); ?></label></td>
<td><input id="epp_amount" name="epp_amount" type="text" size="10" maxlength="10" value="<?php echo get_option('wordpal_amount'); ?>"  /> </td>
<td>The payment amount per period.<br/>Example:  45.00</td>
</tr>
<!--End Field-->
<!--Begin Field-->
<tr>
<td><label for="epp_currency_code"><?php _e('Currency Code'); ?></label></td>
<td><input id="epp_currency_code" name="epp_currency_code" type="text" size="3" maxlength="3" value="<?php echo get_option('wordpal_currency_code'); ?>"  /> </td>
<td>The currency code for the payment amount.<br/>Example:  USD</td>
</tr>
<!--End Field-->
<!--Begin Field-->
<tr  class='alternate'>
<td><label for="epp_days_to_add"><?php _e('Payment Period (days)'); ?></label></td>
<td><input id="epp_days_to_add" name="epp_days_to_add" type="text" size="4" maxlength="4" value="<?php echo get_option('wordpal_days_to_add'); ?>"  /> </td>
<td>The number of days between payments.<br/>Example:  30</td>
</tr>
<!--End Field-->
<!--Begin Field-->
<tr>
<td><label for="epp_trial"><?php _e('Trial Period (days)'); ?></label></td>
<td><input id="epp_trial" name="epp_trial" type="text" size="4" maxlength="4" value="<?php echo get_option('wordpal_trial'); ?>"  /> </td>
<td>The number of days for a trial period.  Blank or zero for no trial.<p>Enter 9999 to turn off subscriptions completely.</p><br/>Example:  30</td>
</tr>
<!--End Field-->
<!--Begin Field-->
<tr  class='alternate'>
<td><label for="epp_user_msg"><?php _e('User Message'); ?></label></td>
<td><textarea id="epp_user_msg" name="epp_user_msg" rows=5 cols=30 ><?php echo get_option('wordpal_user_msg'); ?></textarea></td>
<td>The text that will be displayed just before a user is transferred to PayPal. (HTML is allowed)</td>
</tr>
<!--End Field-->
<!--Begin Field-->
<tr>
<td><label for="epp_more_login"><?php _e('Login Message'); ?></label></td>
<td><textarea id="epp_more_login" name="epp_more_login" rows=5 cols=30 ><?php echo get_option('wordpal_more_login'); ?></textarea></td>
<td>The text that will be displayed in a post when a user is not logged in and [pay] tags are used in the post. <br/>(HTML is allowed)</td>
</tr>
<!--End Field-->
<!--Begin Field-->
<tr class='alternate'>
<td><label for="epp_bypass"><?php _e('Exception List'); ?></label></td>
<td><textarea id="epp_bypass" name="epp_bypass" rows=5 cols=30  ><?php echo get_option('wordpal_bypass'); ?></textarea></td>
<td>A comma-separated list of user names.  These users will bypass the payment process.</td>
</tr>
<!--End Field-->
<!--Begin Field-->
<tr>
<td><label for="epp_categories"><?php _e('Categories'); ?></label></td>
<td><textarea id="epp_categories" name="epp_categories" rows=5 cols=30  ><?php echo get_option('wordpal_categories'); ?></textarea></td>
<td>A comma-separated list of category names.  Posts with these categories will be hidden unless purchased.</td>
</tr>
<!--End Field-->
<!--Begin Field-->
<tr class="alternate">
<td><label for="epp_subscription_payment_page"><?php _e('Subscription Payment Page Title'); ?></label></td>
<td><input id="epp_subscription_payment_page" name="epp_subscription_payment_page" size="30" maxlength="150" value="<?php echo get_option('wordpal_subscription_payment_page'); ?>"></td>
<td>Optional.  The page title of a special page for subscription options.</td>
</tr>
<!--End Field-->
<!--Begin Field-->
<tr>
<td><label for="epp_post_payment_page"><?php _e('Post Payment Page Title'); ?></label></td>
<td><input id="epp_post_payment_page" name="epp_post_payment_page" size="30" maxlength="150" value="<?php echo get_option('wordpal_post_payment_page'); ?>"></td>
<td>Optional.  The page title of a special page for purchasing credits to publish posts.</td>
</tr>
<!--End Field-->
<!--Begin Field-->
<tr class="alternate">
<td><label for="epp_category_payment_page"><?php _e('Category Payment Page Title'); ?></label></td>
<td><input id="epp_category_payment_page" name="epp_category_payment_page" size="30" maxlength="150" value="<?php echo get_option('wordpal_category_payment_page'); ?>"></td>
<td>Optional.  The page title of a special page for purchasing categories.</td>
</tr>
<!--End Field-->




</table>
	<p class="submit"><input type="submit" name="submit" value="<?php _e('Update Options &raquo;'); ?>" /></p>
<p style="height:25px"></p>
</form>
</div>