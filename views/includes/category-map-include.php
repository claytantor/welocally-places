<style type="text/css">
	.map_all { width: 100%; margin-bottom:5px; } 
	#map_spacer { width: 100%; margin-bottom:20px; } 

	.wl-map-infobox { border: 1px solid #444444; margin-top: 8px; background: #eeeeee; padding: 5px;  /* for IE */ }

	ul#icons {margin: 0; padding: 0;}
	ul#icons li {margin: 2px; position: relative; padding: 4px 0; cursor: pointer; float: left;  list-style: none;}
	ul#icons span.ui-icon {float: left; margin: 0 4px;}

	/*---- selectable ----*/
	.wl-map .selectable { list-style-type: none; margin: 0; padding: 0; width: 100%; }
	.wl-map .selectable li { 
			padding: 5px;
			height: <?php echo wl_get_option("cat_map_select_height", "160"); ?>px; 
			width: <?php echo wl_get_option("cat_map_select_width", "160"); ?>px; 
			display:inline-block; 
			vertical-align:top; 
            overflow:hidden; 
            margin:3px;
            border: 1px solid #777777; 
    }	
    
    .wl-map .map-items ul, .wl-map .map-items ul, { margin: 0px;  }
    
    #content-body ul, #content-body ol { margin: 0px;  }


	.wl-map .selectable .ui-selecting { background: #C4C4C4; }
	.wl-map .selectable .ui-selected { background: #e4e4e4; }
	.wl-map .selectable .wl-item-content { 
			position:relative;
			top:-20px;
			left:0px;
			margin: 3px; 
			padding: 0.4em; 
			z-index:1;
	}
	
    
	#selected_content { margin: 10; padding: 10; width: 100%;  }
	
	.wl-category-title { 
		/*margin-bottom: 5px;
		border-bottom: 1px solid #777777;*/
		font-size:2.0em; 
		font-weight:bold;
		font-style: italic; 
		color: #911D1D; 
		font-family:Adobe Garamond Pro, Garamond, Palatino, Palatino Linotype, Times, Times New Roman, Georgia, serif;
	}
	
	.un-select-box { border: 1px solid #ffffff; }
	
	.wl-over-box { background: #a4a4a4; }
	

	img.wl-link-image { 
		/*float: right;*/ 
		margin: 4px; 
		border:0px;
}
	
.wl-place-widget-links { text-align:right; }
.wl-left-sidebar { width: 100% }
	
#wl-sidebar-1 {  width: 100%, display: inline-block;}	
#wl-map-content {  width: 100%, display: inline-block;}

.title-selectable-place { 
		position:relative; 	
		top:0px;
		left:0px; 
		width:100%;
		z-index:200;}
	

/* override the font style catmap   */
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

.wl-place-address { 
	font-family: <?php echo wl_get_option("font_place_address", "Sorts Mill Goudy"); ?>; 
	color: #<?php echo wl_get_option("color_place_address", "000000"); ?>; 
}
.wl-place-widget-address{ }

.wl-infobox-text_scale { 
	font-size: <?php echo wl_get_option("cat_map_infobox_text_scale", "100"); ?>%;
}


.map-all { width:100%; background: #eeeeee; }
.map-canvas { font-size:  <?php echo wl_get_option("cat_map_infobox_text_scale", "100"); ?>%; }
</style>
<link type="text/css" href="https://ajax.googleapis.com/ajax/libs/jqueryui/1.8.16/themes/base/jquery-ui.css" rel="stylesheet" />			
<script type="text/javascript" src="https://ajax.googleapis.com/ajax/libs/jqueryui/1.8.16/jquery-ui.min.js"></script>