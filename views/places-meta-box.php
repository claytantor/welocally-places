<script type="text/javascript" src="http://ajax.googleapis.com/ajax/libs/jqueryui/1.8.5/jquery-ui.min.js"></script>
<link rel="stylesheet" href="http://ajax.googleapis.com/ajax/libs/jqueryui/1.8.5/themes/smoothness/jquery-ui.css" type="text/css" />
<script type="text/javascript" charset="utf-8">
var jsonObjFeatures = []; //declare features array
var selectedFeatureIndex = 0;
var selectedCategories='';



function getLocationsByAddress(address, keyword, radiusKm) {	
	jQuery('#welocally-post-error').removeClass('welocally-error welocally-update error updated fade');
	jQuery('#welocally-post-error').html('<em>Loading Places...</em>');
	jQuery('#selectable').empty();
	jsonObjFeatures = [];
	
	
	var missingRequired = false;
	var fields = '';
	if (!jQuery("#place-address").val().match(/\S/)) {
		missingRequired = true;
		fields = fields+'Place Address - ';
	}
	if (!jQuery("#place-search").val().match(/\S/)) {
		missingRequired = true;
		fields = fields+'Search Term - ';
	}
	
	
	if(missingRequired){
		buildMissingFieldsErrorMessages(fields);
		return false;
	}
	    
    var options = {
		action: 'get_places',
		siteKey : '<?php echo wl_get_option('siteKey',null) ?>',
		siteToken : '<?php echo wl_get_option('siteToken',null) ?>',
		baseurl : '<?php echo wl_get_option('siteHome',get_bloginfo('home')); ?>',
		address : address,
		radius: radiusKm,
		query : keyword
	};
				
	jQuery.ajax({
	  type: 'POST',
	  url: ajaxurl,
	  dataType: 'json',
	  data: options,
	  error : function(jqXHR, textStatus, errorThrown) {
			console.error(textStatus);
			jQuery('#welocally-post-error').html('ERROR : '+textStatus);
			jQuery('#welocally-post-error').addClass('welocally-error error fade');
	  },
	  success : function(data, textStatus, jqXHR) {
	  		jQuery('#welocally-post-error').html('');
	  		if(data.places != null && data.places.length == 0) {
	  			jQuery('#welocally-post-error').append('<div class="welocally-context-help"><a href="http://www.welocally.com/wordpress/?page_id=104#info_201" target="_new"><img src="<?php echo WP_PLUGIN_URL; ?>/welocally-places/resources/images/context_help_16.png" alt="" title="Get help" height="16px" width="16px" border="0px"/></a></div>');
	  			jQuery('#welocally-post-error').append('Sorry no places were found that match your query, try again or add this as a new place.');
	  			jQuery('#welocally-post-error').addClass('welocally-update updated fade');
	  			
	  		} else if(data.places != null && data.places.length > 0) {
				jQuery.each(data.places, function(i,item){
					console.log(JSON.stringify(item));
					jsonObjFeatures.push(item);	    		
					jQuery('#selectable').append(buildListItemForPlace(item,i));
					jQuery("#results").show();	
				});
			} else if(data.places == null && data.errors != null) {
			
				buildErrorMessages(data.errors);	
					
			}
	  }
	});
    
    
}

function buildListItemForPlace(place,i) {
        var itemLabel = '<b>'+place.name+'</b>';
        if (place.address) {
            itemLabel += "<br>" + place.address;
        }
		return '<li class=\"ui-widget-content\" id="f'+i+'" title="select place">'+itemLabel+'</li>';
}

function buildSelectedInfoForPlace(place) {
        var itemLabel = '<b>'+place.name+'</b>';
        if (place.address) {
            itemLabel += "<br>" + place.address;
        }
		return '<div class=\"selected-place-info\">'+itemLabel+'</div>';
}

function buildCategorySelectionsForPlace(place, container) {
		container.html('');
		var index = -1;
		for (category in place.categories) {
			index = category;
			container.append('<li class=\"ui-widget-content\">'+place.categories[category]+'</li>');
		}
		
		//show if there are items
		if(index != -1){
			jQuery("#categories-choice").show();
		}
		
}


function setSelectedPlaceInfo(selectedItem) {
	//hide the selection area
	jQuery("#place-selector").hide();	
	jQuery("#add-place-form").hide();	
	
	
	//set the form value
	jQuery("#place-selected").val(JSON.stringify(selectedItem));
	
	//show the *selected* area
	var info = buildSelectedInfoForPlace(selectedItem); 
	jQuery("#selected-place-info").html(info);
	
	//build the categories
	buildCategorySelectionsForPlace(selectedItem, jQuery("#selectable-cat")); 
	
	jQuery("#selected-place").show();	
}

function buildErrorMessages(errors) {
	jQuery('#welocally-post-error').html('');
	jQuery('#welocally-post-error').append('<ul>');
	jQuery.each(errors, function(i,error){
		jQuery('#welocally-post-error').append('<li><a href="http://www.welocally.com/wordpress/?page_id=104#error_'+error.errorCode+'" target="_new"><img src="<?php echo WP_PLUGIN_URL; ?>/welocally-places/resources/images/context_help_16.png" alt="" title="Get help" height="16px" width="16px" border="0px"/></a>  ERROR '+error.errorCode+' : '+error.errorMessage+'</li>');
		
	});
	jQuery('#welocally-post-error').append('</ul>');
	jQuery('#welocally-post-error').addClass('welocally-error error fade');	
}

function buildMissingFieldsErrorMessages(fields) {
	jQuery('#welocally-post-error').html('');
	jQuery('#welocally-post-error').append('<ul>');
	jQuery('#welocally-post-error').append('<li><a href="http://www.welocally.com/wordpress/?page_id=104#error_104" target="_new"><img src="<?php echo WP_PLUGIN_URL; ?>/welocally-places/resources/images/context_help_16.png" alt="" title="Get help" height="16px" width="16px" border="0px"/></a>  ERROR 104 : Required field is missing, please check all required fields. Missing fields: - '+fields+'</li>');
	jQuery('#welocally-post-error').append('</ul>');
	jQuery('#welocally-post-error').addClass('welocally-error error fade');		
}

jQuery(document).ready(function(jQuery) {
	
	 if (typeof(jQuery.fn.parseJSON) == "undefined" || typeof(jQuery.parseJSON) != "function") { 

	    //extensions, this is because prior to 1.4 there was no parse json function
		jQuery.extend({
			parseJSON: function( data ) {
				if ( typeof data !== "string" || !data ) {
					return null;
				}    
				data = jQuery.trim( data );    
				if ( /^[\],:{}\s]*$/.test(data.replace(/\\(?:["\\\/bfnrt]|u[0-9a-fA-F]{4})/g, "@")
					.replace(/"[^"\\\n\r]*"|true|false|null|-?\d+(?:\.\d*)?(?:[eE][+\-]?\d+)?/g, "]")
					.replace(/(?:^|:|,)(?:\s*\[)+/g, "")) ) {    
					return window.JSON && window.JSON.parse ?
						window.JSON.parse( data ) :
						(new Function("return " + data))();    
				} else {
					jQuery.error( "Invalid JSON: " + data );
				}
			}
		});
	}	
	
	var selectedPlaceObject = null;
	
	<?php if( $_isWLPlace == 'true') : ?>
	jQuery("#placeForm").show();
	<?php endif; ?>
	
	<?php if( $_PlaceSelected != '') : ?>
	selectedPlaceObject = jQuery.parseJSON( '<?php echo $bodytag = str_replace("'", "\'", $_PlaceSelected); ?>' );
	setSelectedPlaceInfo(selectedPlaceObject);
	<?php endif; ?>
	
	
	jQuery("#welocally_default_search_radius").val('<?php echo wl_get_option('default_search_radius',null) ?>');
	
	jQuery( "#search-places-action" ).click(function() {
		getLocationsByAddress(
			jQuery('#place-address').val(),
			jQuery('#place-search').val(),
			jQuery('#welocally_default_search_radius').val());
		return false;		
	});

    jQuery( "#add-place-action" ).click(function() {
    
    	jQuery('#welocally-post-error').removeClass('welocally-error welocally-update error updated fade');
		jQuery('#welocally-post-error').html('');
	
    	jQuery("#place-selector").hide();
    	jQuery("#add-place-form").show();
    	
        return false;
    });
    
    jQuery( "#cancel-add-link" ).click(function() {
        jQuery("#add-place-form").hide();
        if(selectedPlaceObject == null) {
        	jQuery("#place-selector").show();
        } else {
        	jQuery("#place-selector").show();
        }
        return false;
    });

    
    jQuery( "#btn-new-select" ).click(function() {  
    	jQuery("#selected-place").hide();
    	jQuery("#place-selector").show();    	     
        return false;
    });
    
    //saves a new place
    jQuery( "#save-place-action" ).click(function() {   
    
    	jQuery('#welocally-post-error').removeClass('welocally-error welocally-update error updated fade');
	    jQuery('#welocally-post-error').html('<em>Saving New Place...</em>');
    
    	var missingRequired = false;
    	var fields = '';
 		if (!jQuery("#add-place-name").val().match(/\S/)) {
            missingRequired = true;
            fields = fields+'Place Name - ';
            //return false;
        }
        if (!jQuery("#add-place-street").val().match(/\S/)) {
            //alert("Please enter the new place's street address");
            //return false;
            missingRequired = true;
            fields = fields+'Street Address - ';
        }
        if (!jQuery("#add-place-city").val().match(/\S/)) {
            //alert("Please enter the new place's city");
            //return false;
             missingRequired = true;
            fields = fields+'City - ';
        }
        if (!jQuery("#add-place-state").val().match(/\S/)) {
            //alert("Please enter the new place's state");
            //return false;
             missingRequired = true;
             fields = fields+'State or Provence - ';
        }
        if (!jQuery("#add-place-zip").val().match(/\S/)) {
            //alert("Please enter the new place's zip code");
            //return false;
            missingRequired = true;
             fields = fields+'Postal Code - ';
        }
        
        if(missingRequired){
			buildMissingFieldsErrorMessages(fields);
			return false;
		}

                
        var options = { 
        	action: 'add_place',
        	siteKey : '<?php echo wl_get_option('siteKey',null) ?>',
		    siteToken : '<?php echo wl_get_option('siteToken',null) ?>',
		    baseurl : '<?php echo wl_get_option('siteHome',get_bloginfo('home')); ?>',
        	placeName: jQuery('#add-place-name').val(),
        	placeStreet: jQuery('#add-place-street').val(),
        	placeCity: jQuery('#add-place-city').val(),
        	placeState: jQuery('#add-place-state').val(),
        	placeZip: jQuery('#add-place-zip').val(),
 	      	placePhone: jQuery('#add-place-phone').val(),
    	   	placeWeb: jQuery('#add-place-web').val(),
       		placeCats: jQuery('#add-place-cats').val()  
       		
        };
        
		jQuery.ajax({
		  type: 'POST',
		  url: ajaxurl,
		  dataType: 'json',
		  data: options,
		  error : function(jqXHR, textStatus, errorThrown) {
					console.error(textStatus);
					jQuery('#welocally-post-error').html('ERROR : '+textStatus);
					jQuery('#welocally-post-error').addClass('welocally-error error fade');
		  },		  
		  success : function(data, textStatus, jqXHR) {
		  	jQuery('#welocally-post-error').html('');
			if(data.errors != null) {
				buildErrorMessages(data.errors);		
			} else {
				setSelectedPlaceInfo(data);
			}
		  }
		});
        
        
        return false;
    });
    
    jQuery("input[name='isWLPlace']").change(function(){
    	if (jQuery("input[name='isWLPlace']:checked").val() == 'true') { 
        	jQuery("#placeForm").show();
        	
        	//no place has been selected yet
        	if(selectedPlaceObject == null) {
        		jQuery("#place-selector").show();
        	}
        		
    	} else if (jQuery("input[name='isWLPlace']:checked").val() == 'false') { 
	        jQuery("#placeForm").hide();	
    	} 
    });   
    
    jQuery( "#selectable" ).selectable({
		   selected: function(event, ui) {
				selectedFeatureIndex = jQuery("#scroller-places li").index(ui.selected);
				setSelectedPlaceInfo(jsonObjFeatures[selectedFeatureIndex]);				
		   }
	});
	
	jQuery( "#selectable-cat" ).selectable({
		   selected: function(event, ui) {
			   	if(selectedCategories.indexOf(ui.selected.innerText) == -1) {
			   		selectedCategories = selectedCategories + ui.selected.innerText+",";
			   	}
		   		jQuery( "#place-categories-selected" ).val(selectedCategories);		
		   },
		   unselected: function(event, ui) {
		   		if(selectedCategories.indexOf(ui.unselected.innerText) != -1) {
		   			var replaceText =  ui.unselected.innerText+",";
		   			selectedCategories = selectedCategories.replace(new RegExp(replaceText, 'g'),"");
		   			jQuery( "#place-categories-selected" ).val(selectedCategories);	
		   		}		
		   }
	});
	
	
<?php if( $isPlaceChecked == 'true') : ?>
	jQuery("#placeForm").show();	
<?php endif; ?>


});

</script>
<style type="text/css">
	
	#place-intro { margin-top: 5px; margin-bottom: 5px; }
	
	#placeForm { 
		border-color:#dfdfdf;
		background-color:#F9F9F9;
		border-width:1px;
		border-style:solid;
		-moz-border-radius:3px;
		-khtml-border-radius:3px;
		-webkit-border-radius:3px;
		border-radius:3px;
		margin: 0;
		width:95%;
		border-style:solid;
		border-spacing:0;
		padding: 10px;
		display:none; 
	 }
	
    #add-span { }
    #add-place-action { }
    #selection { margin-bottom: 10px; }
    #add-form-div { margin-top: 5px; display:none; }
   
       
    /* ------ titles */   
    .meta-title2 {
    	border-bottom:1px solid #cccccc; padding-bottom:3px;
    	margin-bottom: 10px;
		font-weight:bold;
		font-size:1.2em;
		text-transform:uppercase;
	}
	
	/* ------- is place */
	#all_place_info { width: 100%; }	
	
	/* ------ selection form */
	#place-selector { margin-bottom: 10px; display:none; }
	#scroller-places { height: 240px; width: 100%; overflow-y: scroll;}
	.search-field { width: 100%; margin-bottom: 10px; }
    #results { margin-top: 5px; display:none; }
	#selectable .ui-selecting { background: #AAAAAA; color: black; }
	#selectable .ui-selected { background: #444444; color: white; }
	#selectable { list-style-type: none; margin: 0; padding: 0; }
	#selectable li { margin: 3px 3px 3px 0; padding: 0.2em; cursor: pointer; }	
	#categories-choice {  margin-bottom: 10px; display:none; }	
		
	/* ------ selected place */
	#selected-place { margin-bottom: 10px; width: 100%; display:none; }
	#btn-new-select { margin-bottom: 10px; margin-top: 10px; }
	#selected-place-categories { margin-bottom: 10px; }
    #selected-place-info { margin-bottom: 10px; }
    #selectable-cat .ui-selecting { background: #AAAAAA; color: black; }
	#selectable-cat .ui-selected { background: #444444; color: white; }
	#selectable-cat { list-style-type: none; margin: 0; padding: 0; }
	#selectable-cat li { margin: 3px 3px 3px 0; padding: 0.2em; cursor: pointer; }
	#show_place_info { margin-bottom: 10px; }
	
	/* ------ add new place */
	#add-place-form { margin-bottom: 10px; width: 100%; display:none; }
	 	
</style>
<div id="place-intro">
<div id="welocally-post-error"></div>
<?php
if(is_subscribed()):

try {
	do_action('sp_places_post_errors', $postId );
	if( !$this->postExceptionThrown ) delete_post_meta( $postId, self::WLERROROPT );
} catch ( WLPLACES_Post_Exception $e) {
	$this->postExceptionThrown = true;
	update_post_meta( $postId, self::WLERROROPT, trim( $e->getMessage() ) );
	$e->displayMessage( $postId );
}


?>


	<?php _e('Associate a place with this post?',$this->pluginDomain); ?>&nbsp;
	<label><input type='radio' name='isWLPlace' value='true' <?php echo $isPlaceChecked; ?> />&nbsp;<b><?php _e('Yes', $this->pluginDomain); ?></b></label>
	<label><input type='radio' name='isWLPlace' value='false' <?php echo $isNotPlaceChecked; ?> />&nbsp;<b><?php _e('No', $this->pluginDomain); ?></b></label>
</div>
<div id="placeForm">
	<input type="hidden" id="place-selected" name="PlaceSelected">
	<input type="hidden" id="place-categories-selected" name="PlaceCategoriesSelected">
	<div id="all_place_info">
		<!-- start place selector -->
		<div id="place-selector">
			<div class="meta-title2">Select Place</div>
			<div>	
				<div>*<em>Please enter the closest address, this can just be the city and state (ie. Oakland, CA), or the full address...</em> REQUIRED</div>
				<div style="margin-bottom:5px">
					<input type="text" id="place-address" 
						class="search-field" 
						value="<?php echo wl_get_option('default_search_addr',null) ?>">
					<select id="welocally_default_search_radius" name="welocally_default_search_radius" >
						<option value="2">2 km</option>
						<option value="4">4 km</option>
						<option value="8">8 km</option>
						<option value="12">12 km</option>
						<option value="16">16 km</option>
						<option value="25">25 km</option>
						<option value="50">50 km</option>
					</select>&nbsp;<em>Distance in Km</em> 	
						
				</div>	
				<div>*<em>What is the name of the place you are writing about or a simillar keyword...</em> REQUIRED</div> 
				<div><input type="text" id="place-search" class="search-field"></div>
				<div>
					<button id="search-places-action">find places</button>
		            <span id="add-span">don't see a match?&nbsp;&nbsp;<button id="add-place-action">add new place</button></span>
				</div>
			</div>
			<div id="results">
				<div id="scroller-places">
					<ol id="selectable">
					</ol>	
				</div>
			</div>		
		</div> 
		<!-- end place selector -->
		<!-- start place selector -->
		<div id="selected-place">
			<div class="meta-title2">Selected Place</div>
			<div id="selected-place-info"></div>
			<div id="categories-choice">
				<strong><?php _e('Choose categories for post:',$this->pluginDomain); ?></strong>&nbsp;
				<ol id="selectable-cat"></ol> 
			</div>
			<button id="btn-new-select" href="#">new selection</button>
		</div> 
		<!-- end place selector -->	
		<!-- add place form -->	
	    <div id="add-place-form">	    	
	    	<div class="meta-title2">Add New Place</div>
	        Place Name: <em>Required</em></br>
	        <input type="text" id="add-place-name" name="add-place-name" class="search-field"></br>
	        Street Address: <em>Required</em></br>
	        <input type="text" id="add-place-street" name="add-place-street" class="search-field"></br>
	        City: <em>Required</em></br>
	        <input type="text" id="add-place-city" name="add-place-city" class="search-field"></br>
	        State or Provence: <em>Required</em></br>
	        <input type="text" id="add-place-state" name="add-place-state" class="search-field"></br>
	        Zip or Postal Code: <em>Required</em></br>
	        <input type="text" id="add-place-zip" name="add-place-zip" class="search-field"></br>
	        Phone Number: (optional):</br>
	        <input type="text" id="add-place-phone" name="add-place-phone" class="search-field"></br>
	        Website: (optional):</br>
	        <input type="text" id="add-place-web" name="add-place-web" class="search-field"></br>
	        Categories (comma seperated):</br>
	        <input type="text" id="add-place-cats" name="add-place-cats" class="search-field"></br>	        
	        <button id="cancel-add-link" href="#">Cancel</button>
	        <button id="save-place-action" href="#">Add Place</button>
	    </div>
	    <!-- end add place form -->

    </div>
    
<?php else: 
	echo '<div class="error fade"><p><strong>' . __( 'Please Subscribe To Activate Welocally Places' ) . "</strong></p></div>\n";
endif;?>     
</div>