<?php

	//Next Payment
	if ( isset($_POST['submit_date_update']) ) {
		   if ( strlen($_POST['epp_reset_date']) > 0 ) {
			  $epp_reset_user=get_userdatabylogin($_POST['epp_reset_user']);
			  update_usermeta($epp_reset_user->ID,'NextPayment',$_POST['epp_reset_date']); 
		   }
	  }
	//Delete Purchased Items
	if ( isset($_POST['submit_content_delete']) ) {
           if ( strlen($_POST['epp_reset_user']) > 0 ) {
              if(isset($_POST['epp_content_post_id']) && isset($_POST['epp_content_item_number']))
              {
	              $epp_reset_user=get_userdatabylogin($_POST['epp_reset_user']);
			  	  $content_items = get_usermeta($epp_reset_user->ID,'eppItems'); 
                  is_array($content_items) ? '' : $content_items = array();
                  for($index = 0;$index < count($content_items);$index++)
                  {
                       $item = $content_items[$index];
                       if($item['post_ID'] == $_POST['epp_content_post_id'] && $item['item_number'] == $_POST['epp_content_item_number'])
                       {
							array_splice($content_items,$index,1);
					   }
                  }
	              update_usermeta($epp_reset_user->ID,'eppItems',$content_items); 
              }
           }
      }
	//Add Purchased Items
	if ( isset($_POST['submit_content_update']) ) {
			if ( strlen($_POST['epp_reset_user']) > 0 ) {
				if(isset($_POST['epp_content_post_id']) && isset($_POST['epp_content_item_number']))
				{
					if(!empty($_POST['epp_content_post_id']) && !empty($_POST['epp_content_item_number']))
					{
						$epp_reset_user=get_userdatabylogin($_POST['epp_reset_user']);
	  					$content_items = get_usermeta($epp_reset_user->ID,'eppItems'); 
						is_array($content_items) ? '' : $content_items = array();
						$found = false;
						foreach($content_items as $item)
						{
						   if($item['post_ID'] == $_POST['epp_content_post_id'] && $item['item_number'] == $_POST['epp_content_item_number'])
						   {
								$found = true;
							}
						}
						if(!$found)
						{
						    $item = array("post_ID"=>$_POST['epp_content_post_id'],"item_number"=>$_POST['epp_content_item_number']);
							array_push($content_items,$item);
							update_usermeta($epp_reset_user->ID,'eppItems',$content_items); 
						}	
					}
				}
			}

	}
?>

<script type="text/javascript">
function WordPalDelete(post_ID, item_number)
{
    var ret = false;
    if(confirm("Delete the following item?\n\nPost ID:  " + post_ID + "\nItem Number:  " + item_number))
    {
	    document.getElementById("epp_content_post_id").value = post_ID;
	    document.getElementById("epp_content_item_number").value = item_number;
        ret = true;
    }
    return ret;
}
</script>


<form action="" method="post" id="WordPal-Conf" style="margin: 0; width: 100%; ">
<div class="wrap" style="width:70%">
<h3>User Update</h3>
<table width="100%" cellpadding="3" cellspacing="3"> 
<tr>
<td colspan="3">
<p>Enter a user name below and click <strong>Get User Info.</strong></p> 
<br/>
</td>
<td>
</tr>
<tr>
<td><label for="epp_reset_user"><?php _e('Username'); ?></label></td><td><input id="epp_reset_user" name="epp_reset_user" type="text" size="15" maxlength="50"  value="<?php  echo $_POST['epp_reset_user']; ?>
" />
</td><td align="left"><span class="submit"><input type="submit" name="submit_get_date" value="<?php _e('Get User Info. &raquo;'); ?>" /></span></td>
</tr>
<tr>
<td colspan="3">
<hr />
<p>View or modify a user's Next Payment date.</p> 
<br/>
</td>
</tr>
<tr>
<tr>
<td><label for="epp_reset_date"><?php _e('Next Payment Date'); ?></label></td><td><input id="epp_reset_date" name="epp_reset_date" type="text" size="10" maxlength="10"  value="<?php $epp_reset_user=get_userdatabylogin($_POST['epp_reset_user']);echo get_usermeta($epp_reset_user->ID,'NextPayment'); ?>" /><br/>YYYY-MM-DD</td><td></td>
</tr>
<tr>
<td></td>
<td colspan="2"><span class="submit"><input type="submit" name="submit_date_update" value="<?php _e('Update Next Payment Date &raquo;'); ?>" /></span></td>
</tr>
<tr>
<td colspan="3"><hr/>
<p><?php _e('View or modify a user\'s purchased content.'); ?></p>
</td>
<td>
<tr><td colspan="3">&nbsp;</td></tr>
<tr valign="top">
<td colspan="2">
<table width="100%" cellpadding="3" cellspacing="3"> 
<tr valign="top"><td>Title</td><td>Post ID</td><td>Item Number</td><td></td></tr>
<?php
$content_items = get_usermeta($epp_reset_user->ID,'eppItems');
if(!empty($content_items))
{
    is_array($content_items) ? '' : $content_items = array();
    $alt = false;
	foreach($content_items as $item)
	{
		$postdata = wp_get_single_post($item['post_ID'], ARRAY_A);
		extract($postdata);
	    $title = $postdata['post_title'];	
	    if(strlen($title) > 20)
	    {
			$title = substr($title,0,20).'...';
		}
		$alt ? $alt_class='alternate' : $alt_class='';
		echo '<tr class='.$alt_class.'>';
		echo '<td>'.$title.'</td>'; 		
		echo '<td><a href="'.get_option('siteurl').'?p='.$item['post_ID'].'" target="_blank">'.$item['post_ID'].'</a></td>'; 				
		echo '<td>'.$item['item_number'].'</td><td><span class="submit"><input type="submit" name="submit_content_delete" onclick="return WordPalDelete('.'\''.$item['post_ID'].'\''.','.'\''.$item['item_number'].'\''.')" value="'. _('Delete &raquo;').'" /></span></td></tr>'; 
        $alt ? $alt = false : $alt = true;
	}
}
else
{
	echo '<tr><td colspan="4">None Yet</td></tr>';
}
?>
</table>
</td>
</tr>
<tr>
<td colspan="3"><hr />Add to a user's purchased content, even if they haven't purchased it.<br /><br />A username must be entered above.<br /><br /></td>
</tr>
<tr>
<td><label for="epp_content_post_id"><?php _e('Post ID'); ?></label></td><td><input id="epp_content_post_id" name="epp_content_post_id" type="text" size="15" maxlength="50"  value="" />
</tr>
<tr>
<td><label for="epp_content_item_number"><?php _e('Item Number'); ?></label></td><td><input id="epp_content_item_number" name="epp_content_item_number" type="text" size="15" maxlength="50"  value="" />
</tr>
<tr>
<td></td>
<td colspan="2"><span class="submit"><input type="submit" name="submit_content_update" value="<?php _e('Add content &raquo;'); ?>" /></span></td>
</tr>
</td>
</tr>
</table>
<hr/>
<h3>Export</h3>
<p><a href="<?php echo get_option('siteurl').'/wp-content/plugins/wordpal/wordpal_user_export.php'?>" target="_blank">Export User Information</a></p>
</div>
</form>
