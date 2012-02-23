//making sure this is committed, rel 1_1_16
if (!window.WELOCALLY) {
    window.WELOCALLY = {
    	env: {
    		initJQuery: function(){
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
    		}
    	},
        util: {
                update: function() {
                        var obj = arguments[0], i = 1, len=arguments.length, attr;
                        for (; i<len; i++) {
                                for (attr in arguments[i]) {
                                        obj[attr] = arguments[i][attr];
                                }
                        }
                        return obj;
                },
                escape: function(s) {
                        return ((s == null) ? '' : s)
                                .toString()
                                .replace(/[<>"&\\]/g, function(s) {
                                        switch(s) {
                                                case '<': return '&lt;';
                                                case '>': return '&gt;';
                                                case '"': return '\"';
                                                case '&': return '&amp;';
                                                case '\\': return '\\\\';
                                                default: return s;
                                        }
                                });
                },
                notundef: function(a, b) {
                        return typeof(a) == 'undefined' ? b : a;
                },
                guidGenerator: function() {
            	    return (WELOCALLY.util.S4()+WELOCALLY.util.S4()+"-"+
            	    		WELOCALLY.util.S4()+"-"+WELOCALLY.util.S4()+"-"+
            	    		WELOCALLY.util.S4()+"-"+
            	    		WELOCALLY.util.S4()+WELOCALLY.util.S4()+WELOCALLY.util.S4());
                },
                keyGenerator: function() {
            	    return (WELOCALLY.util.S4()+WELOCALLY.util.S4());
                },
                tokenGenerator: function() {
                	 return (WELOCALLY.util.S4()+WELOCALLY.util.S4()+
             	    		WELOCALLY.util.S4()+WELOCALLY.util.S4()+
             	    		WELOCALLY.util.S4()+
             	    		WELOCALLY.util.S4()+WELOCALLY.util.S4()+WELOCALLY.util.S4());
                },
                S4: function() {
         	       return (((1+Math.random())*0x10000)|0).toString(16).substring(1);
        	    },
        	    replaceAll: function(txt, replace, with_this) {
        	    	  return txt.replace(new RegExp(replace, 'g'),with_this);
        	    }
        },
        places: {
        	
        	//PLACES SEARCH META
        	search: {       		
        	},
        	//MAP SECTION=========
        	map: {
        	
        		/**
        		 * make the item for the search results list
        		 */
            	buildContentForInfoForList: function(place, index, permalink, webicon, directionsicon) {
            		var content= '<div class="wl-place-name wl-place-widget-name" id="plugin-place-name'+index+'"><a href="'+
            				permalink+'" >'+place.properties.name+'</a></div>'+
            				'<div class="wl-place-address wl-place-widget-address">'+place.properties.address+", "+
            				place.properties.city+" "+
            				place.properties.province+" "+
            				place.properties.postcode+'</div>';
            		
            		//only add phone if exists
            		if(place.properties.phone != null) {	
            				content = content+'<div class="wl-place-widget-phone">'+place.properties.phone+'</div>';
            		}
            		
            		//only add website if it exists
            		content = content+'<div class="wl-place-widget-links" id="plugin-place-links'+index+'">';
            		if(place.properties.website != null && place.properties.website != '') {
            				var website = place.properties.website;
            				if(place.properties.website.indexOf('http://') == -1) {
            					website = 'http://'+place.properties.website;
            				}					
            				content = content+'<a href="'+
            					website+'" target="_new"><img src="'+webicon+'" border="0" class="wl-link-image"/></a>';
            					
            		} 
            		
            		//city state
            		if(place.properties.city != null && place.properties.province != null){
            			var qS = place.properties.city+" "+place.properties.province;
            			if(place.properties.address != null)
            				qs=place.properties.address+" "+qS;
            			if(place.properties.postalcode != null)
            				qs=qs+" "+place.properties.postalcode;
            			var qVal = qs.replace(" ","+");
            			content = content+'<a href="http://maps.google.com/maps?f=d&source=s_q&hl=en&geocode=&q='+qVal+
            			'" target="_new"><img src="'+directionsicon+'" class="wl-link-image"/></a>';
            		}
            		content = content+'</div">';

            		return content;
            	}
        		
        	},
        	
        	//TAGS SECTION=========
            tag: {
        		postMaps: new Array(),
            	init: function() {
                    // themes can screw up google maps
                    jQuery('.map_canvas_post img').css('max-width' ,'1030px');
                    jQuery('.gmnoprint img').css('max-width' ,'1030px');
                    
                    return true;
                },
                
                insertPlace: function(sel, place, options) {
                    var $sel = jQuery('#' + sel);
                    
                	var showMap = options.showmap;
                	var customStyle = options.isCustom;
                    
            	    jQuery('.wl-place-name', $sel).html(place.properties.name);
                	jQuery('.wl-place-address', $sel).html(
                		place.properties.address+", "+
                		place.properties.city+" "+
                		place.properties.province+" "+
                		place.properties.postcode);
                		
                	if(place.properties.phone != null) {
                		jQuery('.wl-place-phone', $sel).html(place.properties.phone);
                	}

                	if(place.properties.website != null && place.properties.website != '' ) {
                		var website = place.properties.website;
                		if(place.properties.website.indexOf('http://') == -1) {
                			website = 'http://'+place.properties.website;
                		}
                		jQuery('.wl-place-website', $sel)
                				.html(
                					'<table><tr><td class="wl-place-link-item"><a href="'+
                					website+'">'+
                					'<img src="' + options.map_icon_web + '" border="0"/></a></td>'+
                					'<td class="wl-place-link-item"><a href="'+
                					website+'" target="_new">website</a></td></tr></table>');
                	} 

                	if(place.properties.city != null && place.properties.province != null){
                			var qS = place.properties.city+" "+place.properties.province;
                			if(place.properties.address != null)
                				qs=place.properties.address+" "+qS;
                			if(place.properties.postcode != null)
                				qs=qs+" "+place.properties.postcode;
                			var qVal = qs.replace(" ","+");
            			
                			jQuery('.wl-place-driving', $sel)
                				.html(
                					'<table><tr><td class="wl-place-link-item"><a href="http://maps.google.com/maps?f=d&source=s_q&hl=en&geocode=&q='+
                				qVal+'" target="_new"><img src="' + options.map_icon_directions + '"/></a></td>'+
                					'<td class="wl-place-link-item"><a href="http://maps.google.com/maps?f=d&source=s_q&hl=en&geocode=&q='+
                				qVal+'" target="_new">directions</a></td></tr></table>');
                	 }

            		 if(showMap && customStyle ){		 	
            		 	console.log("using custom style");
            		 	
            		 	var latlng = new google.maps.LatLng(place.geometry.coordinates[1], place.geometry.coordinates[0]);
            		
            			var welocallyMapStyle = eval(options.map_custom_style);
            			            			
            			var mapOptions = {
							zoom : 16,
							center : latlng,
							mapTypeId: google.maps.MapTypeId.ROADMAP,
							styles: welocallyMapStyle
						};
            			
            			var map_canvas_post = new google.maps.Map(jQuery('.map_canvas_post', $sel)[0],
            				mapOptions);
            			
            			//we need this to override what themes sometimes do to images
            			google.maps.event.addListener(map_canvas_post, 'tilesloaded', function() {
            				jQuery('.map_canvas_post img', $sel).css('max-width','none');
            				jQuery('.map_canvas_post img', $sel).css('padding','0px');
            				jQuery('.map_canvas_post img', $sel).css('margin','0px');
            				jQuery('.map_canvas_post img', $sel).removeAttr( 'max-width' );
            				
            			});
//            			
//            			google.maps.event.addListener(map_canvas_post, 'mouseover', function() {
//            				jQuery(this).css('cursor','move');
//            			});
//            			
      			        			
            			//home location
            			var mMarker = new google.maps.Marker({
            				position: latlng,
            				map: map_canvas_post,
            				icon: options.where_image
            			});
            			
            			jQuery('.map_canvas_post', $sel).show();
            			//jQuery('.map_canvas_post img', $sel).css('max-width','none');
            			
            			WELOCALLY.places.tag.postMaps.push(map_canvas_post);
            			          			
            		 
            		 } else if(showMap && !customStyle ){
        			 	var latlng = new google.maps.LatLng(place.geometry.coordinates[1], place.geometry.coordinates[0]);
        			
        				var mapOptions = {
        				  zoom: 16,
        				  center: latlng,
        				  mapTypeId: google.maps.MapTypeId.ROADMAP
        				};
            			
            			var map_canvas_post = new google.maps.Map(jQuery('.map_canvas_post', $sel)[0],
                				mapOptions);
            		
//            			//we need this to override what themes sometimes do to images
//            			google.maps.event.addListener(map_post, 'tilesloaded', function() {
//            				jQuery('.map_canvas_post img').css('max-width','none');
//            			});
//            			
//            			google.maps.event.addListener(map_post, 'mouseover', function() {
//            				jQuery(this).css('cursor','move');
//            			});
            			
            			
            			//home location
            			var mMarker = new google.maps.Marker({
            				position: latlng,
            				map: map_canvas_post,
            				icon: options.where_image
            			});
            			
            			jQuery('.map_canvas_post', $sel).show();
            			jQuery('.map_canvas_post img', $sel).css('max-width','none');
            			
            			WELOCALLY.places.tag.postMaps.push(map_canvas_post);
            			
            		 }
                },                 
                makePlaceTag: function(place, post) {
                	var tag = '';
        			if(post == null){
        				tag = '[welocally id="'+place._id+'" /]';
        			} else {
        				tag = '[welocally id="'+place._id+'" postId="'+post.id+'" type="'+post.type+'" /]';
        			}
        			return tag;
                }               			                	
            }
        }
    }
}

/**
 * this is essentially the same thing as a tag
 */
if (!WELOCALLY.PlaceComponent) {
    WELOCALLY.PlaceComponent = function(cfg) {
    	var error;
        // validate config
        if (!cfg) {
            error = "Please provide configuration for the Welocally place";
            cfg = {};
        }
    }
}

/**
 * when we come along we will encapsulate the searcher here
 */
if (!WELOCALLY.PlacesSearchComponent) {
    WELOCALLY.PlacesSearchComponent = function(cfg) {
    }
}



