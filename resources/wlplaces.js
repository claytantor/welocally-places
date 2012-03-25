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
	    		trim: function (str) { 
	    			return WELOCALLY.util.ltrim(WELOCALLY.util.rtrim(str), ' '); 
	    		}, 
	    		ltrim: function (str) { 
	    			return str.replace(new RegExp("^[" + ' ' + "]+", "g"), ""); 
	    		},    		 
	    		rtrim: function (str) { 
	    			return str.replace(new RegExp("[" + ' ' + "]+$", "g"), ""); 
	    		},
    			preload: function(arrayOfImages) {
    			    jQuery(arrayOfImages).each(function(){
    			    	jQuery('<img/>')[0].src = this;
    			        // Alternatively you could use:
    			        // (new Image()).src = this;
    			    });
    			},
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
                unescape: function (unsafe) {
                	  return unsafe
                	      .replace(/&amp;/g, "&")
                	      .replace(/&lt;/g, "<")
                	      .replace(/&gt;/g, ">")
                	      .replace(/&quot;/g, '"')
                	      .replace(/&#039;/g, "'");
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
        		infobox: {
        			baseOffsetX: 0,
        			baseOffsetY: -5,
        			baseWidth: 150,
        	        thumbMaxSize: '150px',
        	        setOffset: function(contentsBox,infobox){
						var width = 
							eval(jQuery(contentsBox)
									.find('#info-contents-box')
									.css('width')
									.replace('px',''));
						
						var offsetX = ((width/2)+10)*-1;
						infobox.pixelOffset_ = 
							new google.maps.Size(
									offsetX+WELOCALLY.places.map.infobox.baseOffsetX, 
									WELOCALLY.places.map.infobox.baseOffsetY);
        			}
        		},
        		
        		setMapEvents: function(map){
        			google.maps.event.addListener(map, 'tilesloaded', function() {
        				console.log('tiles loaded');
        				jQuery(map).find('img').css('max-width','none');
        				jQuery(".map_canvas_post").find('img').css('max-width','none');
        				WELOCALLY.util.preload([
        				         'http://maps.google.com/mapfiles/openhand.cur'
        				]);          				
        			});    
        			
        			//we need this to override what themes sometimes do to images
        			google.maps.event.addListener(map, 'idle', function() {
        				map.setOptions({ draggableCursor: 'url(http://maps.google.com/mapfiles/openhand.cur), move' });
        				jQuery('#info-contents-box').css('line-height','15px');
        			});
        			
        			//we need this to override what themes sometimes do to images
        			google.maps.event.addListener(map, 'mouseover', function() {
        				map.setOptions({ draggableCursor: 'url(http://maps.google.com/mapfiles/openhand.cur), move' });
        				jQuery('#info-contents-box').css('line-height','15px');
        				jQuery('#info-contents-box ul').css('margin','0px');
        			});
        			
        			//we need this to override what themes sometimes do to images
        			google.maps.event.addListener(map, 'mousemove', function() {
        				map.setOptions({ draggableCursor: 'url(http://maps.google.com/mapfiles/openhand.cur), move' });
        				jQuery('#info-contents-box').css('line-height','15px');
        				jQuery('#info-contents-box ul').css('margin','0px');
        			});      			

        		},
        	
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
            			
            			//make this a function
            			WELOCALLY.places.map.setMapEvents(map_canvas_post);
            			
            			//home location
            			var mMarker = new google.maps.Marker({
            				position: latlng,
            				map: map_canvas_post,
            				icon: options.where_image
            			});
            			
            			jQuery('.map_canvas_post', $sel).show();
            			
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
            			
            			WELOCALLY.places.map.setMapEvents(map_canvas_post);
            		
            			
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
    };
}

/*
 * Category Map
 */
if (!WELOCALLY.places.CategoryMap) {
    WELOCALLY.places.CategoryMap = function(id, cfg) {
        this.config = jQuery.extend({}, {showExcerpts: false, showSelectBoxes: false}, cfg);

        /* HTML elements */
        this.container = jQuery('#' + id);
        this.canvas = this.container.find('.map-canvas');
        this.items = this.container.find('.map-items');
        this.selectable = this.items.find('.selectable');
        
        /* map */
        this.map = new google.maps.Map(this.canvas[0], cfg.mapOptions);
        this.canvas.height(400); // basic size

        this.bounds = new google.maps.LatLngBounds();

        /* items */
        this.items = new Array();
        this.selectedIndex = null;
        this.selectedItem = null;
    }

    WELOCALLY.places.CategoryMap.prototype._buildListItemHTML = function (itemid, place, marker, excerpt, link, showExcerpt) {
        if (place != null) {
            var content = '<li data-item-id="' + itemid + '" class="ui-widget-content morePosts">';

            content +=
                '<div class="wl-infobox-text_scale wl-place-name title-selectable-place wl-infobox-text_scale">' + 
                    '<a href="' + marker.link +'" >' + place.properties.name + '</a>' + 
                '</div>';

            if (showExcerpt)
                content += '<div class="wl-infobox-text_scale wl-place-excerpt">' + excerpt + '</div>';

            content += '</li>';
            
            var item = new Item(place, content, marker, link);
            return item;
        } else {
            return '';
        }
    }

    WELOCALLY.places.CategoryMap.prototype.add = function(place, options) { 
        var latlng = new google.maps.LatLng(place.geometry.coordinates[1], place.geometry.coordinates[0]);

        var marker = addItemMarker('category-map', this.items.length, place, this.map,
                                    options.marker, options.title, options.link, options.excerpt,
                                    options.webicon, options.directionsicon, true, options.showThumb, options.thumbnail);
        var item = this._buildListItemHTML(this.items.length, place, marker, options.excerpt, options.link, this.config.showExcerpts);

        this.items.push(item);
        this.bounds.extend(latlng);

        if (this.config.showSelectBoxes)
            this.selectable.append(item.content);

        return item; /* FIXME: we don't need to return anything */

    }

    WELOCALLY.places.CategoryMap.prototype.done = function() {
        var categoryMap = this;

        WELOCALLY.places.map.setMapEvents(this.map);

        /* fit map */
        if (this.items.length == 1) {
            this.map.setCenter(new google.maps.LatLng(this.items[0].place.geometry.coordinates[1], this.items[0].place.geometry.coordinates[0]));
            this.map.setZoom(14);
        } else {
            this.map.fitBounds(this.bounds);
        }

        this.selectable.find('li')
        .mouseover(function(){
            jQuery(this).attr('class', 'ui-widget-content ui-selected select-box');
        })
        .mouseout(function(){
            var index = jQuery(this).attr('data-item-id');

            if (categoryMap.selectedIndex == null || index != categoryMap.selectedIndex)
                jQuery(this).attr('class', 'ui-widget-content un-select-box');
        });

        this.selectable.selectable({
            selected: function(event, ui) {
                var index = jQuery(ui.selected).attr('data-item-id');
                var selectedItem = categoryMap.items[index];
                var placeLatLng = new google.maps.LatLng(selectedItem.place.geometry.coordinates[1], selectedItem.place.geometry.coordinates[0]);
                categoryMap.map.panTo(placeLatLng);
                            
                var contentsBox = jQuery(document.createElement('div'));

                var contents = buildContentForInfoWindow(
                    WELOCALLY.places.map.infobox.baseWidth,
                    selectedItem.place, ",", 
                    selectedItem.marker.webicon, 
                    selectedItem.marker.directionsicon,
                    selectedItem.marker.linkedTitle,
                    selectedItem.marker.link,
                    selectedItem.marker.showThumb,
                    selectedItem.marker.thumbUrl,
                    WELOCALLY.places.map.infobox.thumbMaxSize);

                jQuery(contentsBox).html(contents);
                
                boxText.innerHTML = contents;
                
                WELOCALLY.places.map.infobox.setOffset(contentsBox,ib);
        
                ib.open(categoryMap.map, selectedItem.marker);
                ib.show();
                ib_widget.hide();
                
                jQuery('#info-contents-box ul li a').css('background','none').css('padding','0px');
                            
                categoryMap.selectedIndex = index;
            },
            cancel: ":input,option,a"
        });
    }   
}