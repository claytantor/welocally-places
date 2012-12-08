<?php
 /*
	Copyright 2012 clay graham, welocally & RateCred Inc.

   Licensed under the Apache License, Version 2.0 (the "License");
   you may not use this file except in compliance with the License.
   You may obtain a copy of the License at

       http://www.apache.org/licenses/LICENSE-2.0

   Unless required by applicable law or agreed to in writing, software
   distributed under the License is distributed on an "AS IS" BASIS,
   WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
   See the License for the specific language governing permissions and
   limitations under the License.
*/
global $wlPlaces;
$options = $wlPlaces->getOptions();
?>

<?php 
$menubar_include = WP_PLUGIN_DIR . '/' .$wlPlaces->pluginDir . '/options/options-infobar.php';
include($menubar_include);
?>
<div class="wrap">

<div class="icon32"><img src="<?php echo WP_PLUGIN_URL; ?>/welocally-places/resources/images/screen_icon.png" alt="" title="" height="32px" width="32px"/><br /></div>
<h2>Welocally Places About</h2>
<div style="margin-top:20px;">
<table class="wl-form-table">
	<tr valign="top">
		<th scope="row"><?php _e( 'Version' ); ?></th>
		<td><div><?php echo $wlPlaces->wl_places_version();  ?></div></td>
	</tr>
	<tr valign="top">
		<th scope="row"><?php _e( 'Places Version Support' ); ?></th>	
		<td>			
		<?php if(get_places_legacy_count() > 0): ?>	
		<div><img width="48" height="48" src="<?php echo WP_PLUGIN_URL; ?>/welocally-places/resources/images/Crystal_Clear_cancel.png" alt="" title=""/></div>
		<div>You have recenty upgraded Welocally Places. <?php echo(get_places_legacy_count());  ?> Legacy Posts were found. You can use the <a href="<?php echo bloginfo( 'wpurl' ).'/wp-admin/admin.php?page=welocally-places-manager' ?>">Place Manager</a> 
		to track your migration. Please read the <a href="http://www.welocally.com/?page_id=104" target="_blank">help documentation</a> when upgrading. If you have problems <a href="http://www.welocally.com/?page_id=139" target="_new">email us</a>. 
		<p/><strong>ALWAYS BACKUP PRIOR TO UPGRADE</strong></div>
		<?php else: ?>
		<img width="48" height="48" src="<?php echo WP_PLUGIN_URL; ?>/welocally-places/resources/images/Crystal_Clear_check.png" alt="" title=""/>	Your places are up to date. 	
		<?php endif; ?>
		</td>
	</tr>		
</table>
</div>
				
</div> <!--wrap end-->