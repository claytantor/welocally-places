<style>
	#place-details { width: 100%; display: none; background-color: #eeeeee; }
	#sp-click-action-call { width: 100%; background-color: #eeeeee; }
	.simple_grey_italic { 
		font-size:1.2em; 
		font-style:italic; 
		font-weight:normal; 
		color: #333333; 
		font-family:Adobe Garamond Pro, Garamond, Palatino, Palatino Linotype, Times, Times New Roman, Georgia, serif;
	}
	
	img.wl-link-image { 
		/*float: right;*/ 
		margin: 4px; 
		border:0px;
	}
	
.wl-place-widget-links { text-align:right; }

#details-place-name{ margin: 3px;}

#details-place-excerpt{ margin: 3px 3px 10px 3px;}

.sidebar-item li {
	list-style: none;
 }

/* override the font style   */
.content-sidebar ul li a:hover, .content-sidebar .recentcomments a:hover {
color: #<?php echo wl_get_option("color_place_name", "000000"); ?>; 
}
.wl-place-name { 
	font-family: <?php echo wl_get_option("font_place_name", "Sorts Mill Goudy"); ?>; 
	color: #<?php echo wl_get_option("color_place_name", "000000"); ?>; 
}
.wl-place-name a {
	font-family: <?php echo wl_get_option("font_place_name", "Sorts Mill Goudy"); ?>; 
	color: #<?php echo wl_get_option("color_place_name", "000000"); ?>; 
}

.wl-place-widget-name{ }
.wl-place-address { 
	font-family: <?php echo wl_get_option("font_place_address", "Sorts Mill Goudy"); ?>; 
	color: #<?php echo wl_get_option("color_place_address", "000000"); ?>; 
}
.wl-place-widget-address{ }
</style>
<div id="wl-place-content-<?php echo $t->uid; ?>" class="wl-place-content">
	<div class="map_canvas_post"></div>	
	<div class="wl-place-name" id="place-name-<?php echo $t->uid; ?>"></div>
	<div class="wl-place-address" id="place-address-<?php echo $t->uid; ?>"></div>
	<div class="wl-place-phone" id="place-phone-<?php echo $t->uid; ?>"></div>	
	<div>
		<ul class="wl-place-links">
			<li class="wl-place-links-lines" >
				<span class="wl-place-website" id="place-website-<?php echo $t->uid; ?>"></span>
			</li>
			<li class="wl-place-links-lines" >
				<span class="wl-place-driving" id="place-driving-<?php echo $t->uid; ?>"></span>
			</li>
			<li class="wl-place-links-lines">
				<table>
					<tbody>
					<tr>
						<td class="wl-place-link-item">
						<img width="16" height="16" 
							src="<?php echo WP_PLUGIN_URL; ?>/welocally-places/resources/images/we_16.png" alt="" title=""/></td>
						<td class="wl-place-link-item"><a id="share-link-<?php echo $t->uid; ?>" href="#">embed</a></td>
					</tr>
					</tbody>
				</table>
			    <script>
				jQuery("#share-link-<?php echo $t->uid; ?>").click(function () {	
				  jQuery("#place-tag-field-<?php echo $t->uid; ?>").val(WELOCALLY.places.tag.makePlaceTag(<?php echo $t->placeJSON; ?>));  
				  jQuery("#share-tag-<?php echo $t->uid; ?>").toggle();	
				  return false;
				});			
				</script>
			</li>
		</ul>
		<div class="share-place-tag" id="share-tag-<?php echo $t->uid; ?>">
			<table width="100%">
				<tr><td colspan="2"><input class="share-place-tag-tagtext" type="text" id="place-tag-field-<?php echo $t->uid; ?>"></input></td></tr>
				<tr><td align="left"><div>Place this tag in your own <a href="http://welocally.com/?page_id=2">Welocally Places</a> powered <a href="http://wordpress.org/extend/plugins/welocally-places/">wordpress</a> site.</div></td>
				<td align="right"><a href="http://welocally.com/?page_id=2"><img width="95" height="20" 
				src="<?php echo WP_PLUGIN_URL; ?>/welocally-places/resources/images/places_powered.png" 
				alt="" title="Powered by Welocally Places"/></a></td></tr>
			</table>
			<?php /*<div class="tag-line">
				<input class="share-place-tag-tagtext" type="text" id="place-tag-field-<?php echo $t->uid; ?>"></input>
			</div>
			<div style="display:inline-block; width:100%; height:10px">
				<div class="share-place-tag-info">Place this tag in your own <a href="http://welocally.com/?page_id=2">Welocally Places</a> powered <a href="http://wordpress.org/extend/plugins/welocally-places/">wordpress</a> site.</div>
				<div class="tag-powered-by"><a href="http://welocally.com/?page_id=2"><img width="95" height="20" 
				src="<?php echo WP_PLUGIN_URL; ?>/welocally-places/resources/images/places_powered.png" 
				alt="" title="Powered by Welocally Places"/></a></div>
			</div>*/ ?>
		</div>	
	</div>

</div>
<script type="text/javascript" charset="utf-8">
    WELOCALLY.places.tag.insertPlace('wl-place-content-<?php echo $t->uid; ?>',
                         <?php echo $t->placeJSON; ?>,
                         <?php echo json_encode($t->options); ?>);
</script>