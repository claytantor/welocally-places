/*!
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 *       http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 */
function replaceAll(txt, replace, with_this) {
  return txt.replace(new RegExp(replace, 'g'),with_this);
}

function buildContentForInfoForList(place, index, permalink, webicon, directionsicon) {
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
	

function addItemMarker(
	container,
	index, 
	place, 
	map,  
	image, 
	title, 
	link, 
	excerpt,
	webicon,
	directionsicon,
	linkedTitle,
	linkUrl,
	showThumb,
	thumbUrl) {
	
	var myLatLng = new google.maps.LatLng(place.geometry.coordinates[1], place.geometry.coordinates[0]);
	var mMarker = new google.maps.Marker({
		container: container,
		position: myLatLng,
		icon: image,
		map: map,
		title: title,
		index: index,
		place: place,
		index: index,
		link: link,
		excerpt: excerpt,
		webicon: webicon,
		directionsicon: directionsicon,
		linkedTitle: linkedTitle,
		linkUrl: linkUrl,
		showThumb: showThumb,
		thumbUrl: thumbUrl
	});
	
	google.maps.event.addListener(mMarker, 'click', function(event) {
			var placeSelected = mMarker.place;
			var container = mMarker.container;
			
			var newLocation = 
				new google.maps.LatLng(
				placeSelected.geometry.coordinates[1], placeSelected.geometry.coordinates[0]);
			
			map.panTo(mMarker.position);
						
			boxText.innerHTML = buildContentForInfoWindow(
				mMarker.place, ', ', mMarker.webicon, mMarker.directionsicon, 
				mMarker.linkedTitle, mMarker.linkUrl, 
				mMarker.showThumb, mMarker.thumbUrl);
					
			//if this is the widget show the excerpt
			if(container == 'places-map'){
				ib_widget.open(mMarker.map, this);
				jQuery('#details-place-name').html('<a href="'+
						mMarker.link+'" >'+placeSelected.properties.name+'</a>');
				jQuery('#details-place-excerpt').html(mMarker.excerpt);
				jQuery('#place-details').show();
				jQuery('#sp-click-action-call').hide();
			} else {
				ib.open(mMarker.map, this);
			}
	
		
	});
	
	return mMarker;
	
}

function buildListItemForPlace(position, place, marker, excerpt, link, showExcerpt) {
	if (place != null) {
		var content = 
		'<li id=\"item'+position+'\" class=\"ui-widget-content morePosts\">'+	
			buildContentForSelector(place,excerpt,position, marker.link, showExcerpt)+
		'</li>';
				
		var item = new Item(
			place,	
			content,
			marker,
			link);

		return item;
	} else {
		return '';
	}
}

function buildContentForSelector(place,excerpt,position,link, showExcerpt) {
	
		var content= '<div class="wl-infobox-text_scale wl-place-name title-selectable-place wl-infobox-text_scale">'+'<a href="'+
				link+'" >'+place.properties.name+'</a>'+'</div>';
		
		if(showExcerpt){ 
			content=  content+
				'<div class="wl-infobox-text_scale wl-place-excerpt">'+excerpt+'</div>';
		}
		return content;
}

function buildContentForInfoWindow(place, catsDiv, wicon, dicon,
		linkedTitle, linkUrl, 
		showThumb, thumbUrl) {
						
	var placeNameTemplate1= '<div class="wl-place-name wl-place-widget-name"><a href="{PLACE_POST_LINK}">{PLACE_NAME}</a></div>';
	
	var placeNameTemplate2= '<div class="wl-place-name wl-place-widget-name">{PLACE_NAME}</div>';
	
	var webTemplate='<li style="display:inline-block; margin-right:0px"><a href="{WEB_LINK}" target="_blank"><img src="{WEB_ICON}" border="0" class="wl-link-image" border="0"></a><div style="position:relative; display: inline-block; font-size:85%; top: -10px"><a href="{WEB_LINK}" target="_blank">web</a></div></li> ';

	var directionsTemplate='<li style="display:inline-block; margin-right:0px"><a href="{DRIVE_LINK}" target="_blank"><img src="{DRIVE_ICON}" class="wl-link-image"></a><div style="position:relative; display: inline-block; font-size:85%; top: -10px"><a href="{DRIVE_LINK}" target="_blank">directions</a></div></li>';
	
	var thumbTemplate1='<a href="{PLACE_POST_LINK}"><img src="{THUMB_IMAGE_URL}" border="0" style="float:left; margin-right:5px; max-width:150px; max-height:150px"></a>';

	var thumbTemplate2='<img src="{THUMB_IMAGE_URL}" border="0" style="float:left; margin-right:5px; max-width:150px; max-height:150px">';
	
	var content='<div id="infobox_content">{THUMB_CONTENT}<div style="margin-left:5px">{PLACE_NAME_CONTENT}<div class="wl-place-address wl-place-widget-address">{PLACE_ADDRESS}</div><div class="wl-place-widget-phone">{PLACE_PHONE}</div><div class="wl-place-widget-links"> <ul style="text-align:left; list-style-type: none; padding: 0px; margin: 0px;">{WEB_CONTENT}{DIRECTIONS_CONTENT}</ul></div></div></div><div style="clear:left; height:1px">&nbsp;</div>';
			
		//thumb
		if(showThumb){
			
			if(linkedTitle){
				var thumbContent = thumbTemplate1
					.replace('{PLACE_POST_LINK}', linkUrl)
					.replace('{THUMB_IMAGE_URL}',thumbUrl)
					.replace('{THUMB_IMAGE_URL}',thumbUrl);
				content = content.replace('{THUMB_CONTENT}',thumbContent);
				
			} else {
				var thumbContent = thumbTemplate2
				.replace('{THUMB_IMAGE_URL}',thumbUrl)
				.replace('{THUMB_IMAGE_URL}',thumbUrl);
				content = content.replace('{THUMB_CONTENT}',thumbContent);
			}
			
			
			
		} else {
			content = content.replace('{THUMB_CONTENT}','');
		}
		
		content = content.replace('{THUMB_CONTENT}','');
		
		//placename
		if(linkedTitle){
			var placeNameContent = placeNameTemplate1
				.replace('{PLACE_POST_LINK}', linkUrl)
				.replace('{PLACE_NAME}', place.properties.name);
			
			content = content.replace('{PLACE_NAME_CONTENT}',placeNameContent);
			
		} else {
			var placeNameContent = placeNameTemplate2
			.replace('{PLACE_NAME}', place.properties.name);
		
			content = content.replace('{PLACE_NAME_CONTENT}',placeNameContent);
		}
		
		
				
		//address
		if(place.properties.city != null && place.properties.province != null){
			var fullAddress = place.properties.city+" "+place.properties.province;
			if(place.properties.address != null)
				fullAddress=place.properties.address+", "+fullAddress;
			if(place.properties.postalcode != null)
				fullAddress=fullAddress+" "+place.properties.postalcode;			
			var content= content		
			.replace('{PLACE_ADDRESS}', fullAddress);
			
			var searchQuery = replaceAll(fullAddress, ',', '');
			searchQuery = replaceAll(searchQuery, ' ', '+');
			
			var drivingUrl = 'http://maps.google.com/maps?f=d&source=s_q&hl=en&geocode=&q='+searchQuery;
					
			var directionsContent = 
				replaceAll(directionsTemplate, '{DRIVE_LINK}', drivingUrl); 		
	
			directionsContent = directionsContent		
				.replace('{DRIVE_ICON}', dicon);	
			content= content		
				.replace('{DIRECTIONS_CONTENT}', directionsContent);		
			
			
		} else {
			content= content		
				.replace('{PLACE_ADDRESS}', '');
			content= content		
				.replace('{DIRECTIONS_CONTENT}', '');
		}
		
		//phone
		if(place.phone != null) {	
			content= content		
			.replace('{PLACE_PHONE}', place.phone);
		} else {
			content= content		
			.replace('{PLACE_PHONE}', '');
		}
		
		//website
		if(place.website != null && place.website != '') {
			
			var webContent = 
				replaceAll(webTemplate, '{WEB_LINK}', place.website ); 
			webContent = 
				replaceAll(webContent, '{WEB_ICON}', wicon ); 
			content= content		
				.replace('{WEB_CONTENT}', webContent);
			
			
		} else {
			content= content		
			.replace('{WEB_CONTENT}', '');
		}
	
		return content;
}


/**
 * @name InfoBoxOptions
 * @class This class represents the optional parameter passed to the {@link InfoBox} constructor.
 * @property {string|Node} content The content of the InfoBox (plain text or an HTML DOM node).
 * @property {boolean} disableAutoPan Disable auto-pan on <tt>open</tt> (default is <tt>false</tt>).
 * @property {number} maxWidth The maximum width (in pixels) of the InfoBox. Set to 0 if no maximum.
 * @property {Size} pixelOffset The offset (in pixels) from the top left corner of the InfoBox
 *  (or the bottom left corner if the <code>alignBottom</code> property is <code>true</code>)
 *  to the map pixel corresponding to <tt>position</tt>.
 * @property {LatLng} position The geographic location at which to display the InfoBox.
 * @property {number} zIndex The CSS z-index style value for the InfoBox.
 *  Note: This value overrides a zIndex setting specified in the <tt>boxStyle</tt> property.
 * @property {string} boxClass The name of the CSS class defining the styles for the InfoBox container.
 *  The default name is <code>infoBox</code>.
 * @property {Object} [boxStyle] An object literal whose properties define specific CSS
 *  style values to be applied to the InfoBox. Style values defined here override those that may
 *  be defined in the <code>boxClass</code> style sheet. If this property is changed after the
 *  InfoBox has been created, all previously set styles (except those defined in the style sheet)
 *  are removed from the InfoBox before the new style values are applied.
 * @property {string} closeBoxMargin The CSS margin style value for the close box.
 *  The default is "2px" (a 2-pixel margin on all sides).
 * @property {string} closeBoxURL The URL of the image representing the close box.
 *  Note: The default is the URL for Google's standard close box.
 *  Set this property to "" if no close box is required.
 * @property {Size} infoBoxClearance Minimum offset (in pixels) from the InfoBox to the
 *  map edge after an auto-pan.
 * @property {boolean} isHidden Hide the InfoBox on <tt>open</tt> (default is <tt>false</tt>).
 * @property {boolean} alignBottom Align the bottom left corner of the InfoBox to the <code>position</code>
 *  location (default is <tt>false</tt> which means that the top left corner of the InfoBox is aligned).
 * @property {string} pane The pane where the InfoBox is to appear (default is "floatPane").
 *  Set the pane to "mapPane" if the InfoBox is being used as a map label.
 *  Valid pane names are the property names for the <tt>google.maps.MapPanes</tt> object.
 * @property {boolean} enableEventPropagation Propagate mousedown, click, dblclick,
 *  and contextmenu events in the InfoBox (default is <tt>false</tt> to mimic the behavior
 *  of a <tt>google.maps.InfoWindow</tt>). Set this property to <tt>true</tt> if the InfoBox
 *  is being used as a map label. iPhone note: This property setting has no effect; events are
 *  always propagated.
 */

/**
 * Creates an InfoBox with the options specified in {@link InfoBoxOptions}.
 *  Call <tt>InfoBox.open</tt> to add the box to the map.
 * @constructor
 * @param {InfoBoxOptions} [opt_opts]
 */
function InfoBox(opt_opts) {

  opt_opts = opt_opts || {};

  google.maps.OverlayView.apply(this, arguments);

  // Standard options (in common with google.maps.InfoWindow):
  //
  this.content_ = opt_opts.content || "";
  this.disableAutoPan_ = opt_opts.disableAutoPan || false;
  this.maxWidth_ = opt_opts.maxWidth || 0;
  this.pixelOffset_ = opt_opts.pixelOffset || new google.maps.Size(0, 0);
  this.position_ = opt_opts.position || new google.maps.LatLng(0, 0);
  this.zIndex_ = opt_opts.zIndex || null;

  // Additional options (unique to InfoBox):
  //
  this.boxClass_ = opt_opts.boxClass || "infoBox";
  this.boxStyle_ = opt_opts.boxStyle || {};
  this.closeBoxMargin_ = opt_opts.closeBoxMargin || "2px";
  this.closeBoxURL_ = opt_opts.closeBoxURL || "http://www.google.com/intl/en_us/mapfiles/close.gif";
  if (opt_opts.closeBoxURL === "") {
    this.closeBoxURL_ = "";
  }
  this.infoBoxClearance_ = opt_opts.infoBoxClearance || new google.maps.Size(1, 1);
  this.isHidden_ = opt_opts.isHidden || false;
  this.alignBottom_ = opt_opts.alignBottom || false;
  this.pane_ = opt_opts.pane || "floatPane";
  this.enableEventPropagation_ = opt_opts.enableEventPropagation || false;

  this.div_ = null;
  this.closeListener_ = null;
  this.eventListener1_ = null;
  this.eventListener2_ = null;
  this.eventListener3_ = null;
  this.moveListener_ = null;
  this.contextListener_ = null;
  this.fixedWidthSet_ = null;
}

/* InfoBox extends OverlayView in the Google Maps API v3.
 */
InfoBox.prototype = new google.maps.OverlayView();

/**
 * Creates the DIV representing the InfoBox.
 * @private
 */
InfoBox.prototype.createInfoBoxDiv_ = function () {

  var bw;
  var me = this;

  // This handler prevents an event in the InfoBox from being passed on to the map.
  //
  var cancelHandler = function (e) {
    e.cancelBubble = true;

    if (e.stopPropagation) {

      e.stopPropagation();
    }
  };

  // This handler ignores the current event in the InfoBox and conditionally prevents
  // the event from being passed on to the map. It is used for the contextmenu event.
  //
  var ignoreHandler = function (e) {

    e.returnValue = false;

    if (e.preventDefault) {

      e.preventDefault();
    }

    if (!me.enableEventPropagation_) {

      cancelHandler(e);
    }
  };

  if (!this.div_) {

    this.div_ = document.createElement("div");

    this.setBoxStyle_();

    if (typeof this.content_.nodeType === "undefined") {
      this.div_.innerHTML = this.getCloseBoxImg_() + this.content_;
    } else {
      this.div_.innerHTML = this.getCloseBoxImg_();
      this.div_.appendChild(this.content_);
    }

    // Add the InfoBox DIV to the DOM
    this.getPanes()[this.pane_].appendChild(this.div_);

    this.addClickHandler_();

    if (this.div_.style.width) {

      this.fixedWidthSet_ = true;

    } else {

      if (this.maxWidth_ !== 0 && this.div_.offsetWidth > this.maxWidth_) {

        this.div_.style.width = this.maxWidth_;
        this.div_.style.overflow = "auto";
        this.fixedWidthSet_ = true;

      } else { // The following code is needed to overcome problems with MSIE

        bw = this.getBoxWidths_();

        this.div_.style.width = (this.div_.offsetWidth - bw.left - bw.right) + "px";
        this.fixedWidthSet_ = false;
      }
    }

    this.panBox_(this.disableAutoPan_);

    if (!this.enableEventPropagation_) {

      // Cancel event propagation.
      //
      this.eventListener1_ = google.maps.event.addDomListener(this.div_, "mousedown", cancelHandler);
      this.eventListener2_ = google.maps.event.addDomListener(this.div_, "click", cancelHandler);
      this.eventListener3_ = google.maps.event.addDomListener(this.div_, "dblclick", cancelHandler);
      this.eventListener4_ = google.maps.event.addDomListener(this.div_, "mouseover", function (e) {
        this.style.cursor = "default";
      });
    }

    this.contextListener_ = google.maps.event.addDomListener(this.div_, "contextmenu", ignoreHandler);

    /**
     * This event is fired when the DIV containing the InfoBox's content is attached to the DOM.
     * @name InfoBox#domready
     * @event
     */
    google.maps.event.trigger(this, "domready");
  }
};

/**
 * Returns the HTML <IMG> tag for the close box.
 * @private
 */
InfoBox.prototype.getCloseBoxImg_ = function () {

  var img = "";

  if (this.closeBoxURL_ !== "") {

    img  = "<img";
    img += " src='" + this.closeBoxURL_ + "'";
    img += " align=right"; // Do this because Opera chokes on style='float: right;'
    img += " style='";
    img += " position: relative;"; // Required by MSIE
    img += " cursor: pointer;";
    img += " margin: " + this.closeBoxMargin_ + ";";
    img += "'>";
  }

  return img;
};

/**
 * Adds the click handler to the InfoBox close box.
 * @private
 */
InfoBox.prototype.addClickHandler_ = function () {

  var closeBox;

  if (this.closeBoxURL_ !== "") {

    closeBox = this.div_.firstChild;
    this.closeListener_ = google.maps.event.addDomListener(closeBox, 'click', this.getCloseClickHandler_());

  } else {

    this.closeListener_ = null;
  }
};

/**
 * Returns the function to call when the user clicks the close box of an InfoBox.
 * @private
 */
InfoBox.prototype.getCloseClickHandler_ = function () {

  var me = this;

  return function (e) {

    // 1.0.3 fix: Always prevent propagation of a close box click to the map:
    e.cancelBubble = true;

    if (e.stopPropagation) {

      e.stopPropagation();
    }

    me.close();

    /**
     * This event is fired when the InfoBox's close box is clicked.
     * @name InfoBox#closeclick
     * @event
     */
    google.maps.event.trigger(me, "closeclick");
  };
};

/**
 * Pans the map so that the InfoBox appears entirely within the map's visible area.
 * @private
 */
InfoBox.prototype.panBox_ = function (disablePan) {

  var map;
  var bounds;
  var xOffset = 0, yOffset = 0;

  if (!disablePan) {

    map = this.getMap();

    if (map instanceof google.maps.Map) { // Only pan if attached to map, not panorama

      if (!map.getBounds().contains(this.position_)) {
      // Marker not in visible area of map, so set center
      // of map to the marker position first.
        map.setCenter(this.position_);
      }

      bounds = map.getBounds();

      var mapDiv = map.getDiv();
      var mapWidth = mapDiv.offsetWidth;
      var mapHeight = mapDiv.offsetHeight;
      var iwOffsetX = this.pixelOffset_.width;
      var iwOffsetY = this.pixelOffset_.height;
      var iwWidth = this.div_.offsetWidth;
      var iwHeight = this.div_.offsetHeight;
      var padX = this.infoBoxClearance_.width;
      var padY = this.infoBoxClearance_.height;
      var pixPosition = this.getProjection().fromLatLngToContainerPixel(this.position_);

      if (pixPosition.x < (-iwOffsetX + padX)) {
        xOffset = pixPosition.x + iwOffsetX - padX;
      } else if ((pixPosition.x + iwWidth + iwOffsetX + padX) > mapWidth) {
        xOffset = pixPosition.x + iwWidth + iwOffsetX + padX - mapWidth;
      }
      if (this.alignBottom_) {
        if (pixPosition.y < (-iwOffsetY + padY + iwHeight)) {
          yOffset = pixPosition.y + iwOffsetY - padY - iwHeight;
        } else if ((pixPosition.y + iwOffsetY + padY) > mapHeight) {
          yOffset = pixPosition.y + iwOffsetY + padY - mapHeight;
        }
      } else {
        if (pixPosition.y < (-iwOffsetY + padY)) {
          yOffset = pixPosition.y + iwOffsetY - padY;
        } else if ((pixPosition.y + iwHeight + iwOffsetY + padY) > mapHeight) {
          yOffset = pixPosition.y + iwHeight + iwOffsetY + padY - mapHeight;
        }
      }

      if (!(xOffset === 0 && yOffset === 0)) {

        // Move the map to the shifted center.
        //
        var c = map.getCenter();
        map.panBy(xOffset, yOffset);
      }
    }
  }
};

/**
 * Sets the style of the InfoBox by setting the style sheet and applying
 * other specific styles requested.
 * @private
 */
InfoBox.prototype.setBoxStyle_ = function () {

  var i, boxStyle;

  if (this.div_) {

    // Apply style values from the style sheet defined in the boxClass parameter:
    this.div_.className = this.boxClass_;

    // Clear existing inline style values:
    this.div_.style.cssText = "";
    //jQuery(this.div_).find('table').border='1';

    // Apply style values defined in the boxStyle parameter:
    boxStyle = this.boxStyle_;
    for (i in boxStyle) {

      if (boxStyle.hasOwnProperty(i)) {

        this.div_.style[i] = boxStyle[i];
      }
    }

    // Fix up opacity style for benefit of MSIE:
    //
    if (typeof this.div_.style.opacity !== "undefined" && this.div_.style.opacity !== "") {

      this.div_.style.filter = "alpha(opacity=" + (this.div_.style.opacity * 100) + ")";
    }

    // Apply required styles:
    //
    this.div_.style.position = "absolute";
    this.div_.style.visibility = 'hidden';
    if (this.zIndex_ !== null) {

      this.div_.style.zIndex = this.zIndex_;
    }
  }
};

/**
 * Get the widths of the borders of the InfoBox.
 * @private
 * @return {Object} widths object (top, bottom left, right)
 */
InfoBox.prototype.getBoxWidths_ = function () {

  var computedStyle;
  var bw = {top: 0, bottom: 0, left: 0, right: 0};
  var box = this.div_;

  if (document.defaultView && document.defaultView.getComputedStyle) {

    computedStyle = box.ownerDocument.defaultView.getComputedStyle(box, "");

    if (computedStyle) {

      // The computed styles are always in pixel units (good!)
      bw.top = parseInt(computedStyle.borderTopWidth, 10) || 0;
      bw.bottom = parseInt(computedStyle.borderBottomWidth, 10) || 0;
      bw.left = parseInt(computedStyle.borderLeftWidth, 10) || 0;
      bw.right = parseInt(computedStyle.borderRightWidth, 10) || 0;
    }

  } else if (document.documentElement.currentStyle) { // MSIE

    if (box.currentStyle) {

      // The current styles may not be in pixel units, but assume they are (bad!)
      bw.top = parseInt(box.currentStyle.borderTopWidth, 10) || 0;
      bw.bottom = parseInt(box.currentStyle.borderBottomWidth, 10) || 0;
      bw.left = parseInt(box.currentStyle.borderLeftWidth, 10) || 0;
      bw.right = parseInt(box.currentStyle.borderRightWidth, 10) || 0;
    }
  }

  return bw;
};

/**
 * Invoked when <tt>close</tt> is called. Do not call it directly.
 */
InfoBox.prototype.onRemove = function () {

  if (this.div_) {

    this.div_.parentNode.removeChild(this.div_);
    this.div_ = null;
  }
};

/**
 * Draws the InfoBox based on the current map projection and zoom level.
 */
InfoBox.prototype.draw = function () {

  this.createInfoBoxDiv_();

  var pixPosition = this.getProjection().fromLatLngToDivPixel(this.position_);

  this.div_.style.left = (pixPosition.x + this.pixelOffset_.width) + "px";
  
  if (this.alignBottom_) {
    this.div_.style.bottom = -(pixPosition.y + this.pixelOffset_.height) + "px";
  } else {
    this.div_.style.top = (pixPosition.y + this.pixelOffset_.height) + "px";
  }

  if (this.isHidden_) {

    this.div_.style.visibility = 'hidden';

  } else {

    this.div_.style.visibility = "visible";
  }
};

/**
 * Sets the options for the InfoBox. Note that changes to the <tt>maxWidth</tt>,
 *  <tt>closeBoxMargin</tt>, <tt>closeBoxURL</tt>, and <tt>enableEventPropagation</tt>
 *  properties have no affect until the current InfoBox is <tt>close</tt>d and a new one
 *  is <tt>open</tt>ed.
 * @param {InfoBoxOptions} opt_opts
 */
InfoBox.prototype.setOptions = function (opt_opts) {
  if (typeof opt_opts.boxClass !== "undefined") { // Must be first

    this.boxClass_ = opt_opts.boxClass;
    this.setBoxStyle_();
  }
  if (typeof opt_opts.boxStyle !== "undefined") { // Must be second

    this.boxStyle_ = opt_opts.boxStyle;
    this.setBoxStyle_();
  }
  if (typeof opt_opts.content !== "undefined") {

    this.setContent(opt_opts.content);
  }
  if (typeof opt_opts.disableAutoPan !== "undefined") {

    this.disableAutoPan_ = opt_opts.disableAutoPan;
  }
  if (typeof opt_opts.maxWidth !== "undefined") {

    this.maxWidth_ = opt_opts.maxWidth;
  }
  if (typeof opt_opts.pixelOffset !== "undefined") {

    this.pixelOffset_ = opt_opts.pixelOffset;
  }
  if (typeof opt_opts.alignBottom !== "undefined") {

    this.alignBottom_ = opt_opts.alignBottom;
  }
  if (typeof opt_opts.position !== "undefined") {

    this.setPosition(opt_opts.position);
  }
  if (typeof opt_opts.zIndex !== "undefined") {

    this.setZIndex(opt_opts.zIndex);
  }
  if (typeof opt_opts.closeBoxMargin !== "undefined") {

    this.closeBoxMargin_ = opt_opts.closeBoxMargin;
  }
  if (typeof opt_opts.closeBoxURL !== "undefined") {

    this.closeBoxURL_ = opt_opts.closeBoxURL;
  }
  if (typeof opt_opts.infoBoxClearance !== "undefined") {

    this.infoBoxClearance_ = opt_opts.infoBoxClearance;
  }
  if (typeof opt_opts.isHidden !== "undefined") {

    this.isHidden_ = opt_opts.isHidden;
  }
  if (typeof opt_opts.enableEventPropagation !== "undefined") {

    this.enableEventPropagation_ = opt_opts.enableEventPropagation;
  }

  if (this.div_) {

    this.draw();
  }
};

/**
 * Sets the content of the InfoBox.
 *  The content can be plain text or an HTML DOM node.
 * @param {string|Node} content
 */
InfoBox.prototype.setContent = function (content) {
  this.content_ = content;

  if (this.div_) {

    if (this.closeListener_) {

      google.maps.event.removeListener(this.closeListener_);
      this.closeListener_ = null;
    }

    // Odd code required to make things work with MSIE.
    //
    if (!this.fixedWidthSet_) {

      this.div_.style.width = "";
    }

    if (typeof content.nodeType === "undefined") {
      this.div_.innerHTML = this.getCloseBoxImg_() + content;
    } else {
      this.div_.innerHTML = this.getCloseBoxImg_();
      this.div_.appendChild(content);
    }

    // Perverse code required to make things work with MSIE.
    // (Ensures the close box does, in fact, float to the right.)
    //
    if (!this.fixedWidthSet_) {
      this.div_.style.width = this.div_.offsetWidth + "px";
      if (typeof content.nodeType === "undefined") {
        this.div_.innerHTML = this.getCloseBoxImg_() + content;
      } else {
        this.div_.innerHTML = this.getCloseBoxImg_();
        // Note: don't append the content node again
      }
    }

    this.addClickHandler_();
  }

  /**
   * This event is fired when the content of the InfoBox changes.
   * @name InfoBox#content_changed
   * @event
   */
  google.maps.event.trigger(this, "content_changed");
};

/**
 * Sets the geographic location of the InfoBox.
 * @param {LatLng} latlng
 */
InfoBox.prototype.setPosition = function (latlng) {

  this.position_ = latlng;

  if (this.div_) {

    this.draw();
  }

  /**
   * This event is fired when the position of the InfoBox changes.
   * @name InfoBox#position_changed
   * @event
   */
  google.maps.event.trigger(this, "position_changed");
};

/**
 * Sets the zIndex style for the InfoBox.
 * @param {number} index
 */
InfoBox.prototype.setZIndex = function (index) {

  this.zIndex_ = index;

  if (this.div_) {

    this.div_.style.zIndex = index;
  }

  /**
   * This event is fired when the zIndex of the InfoBox changes.
   * @name InfoBox#zindex_changed
   * @event
   */
  google.maps.event.trigger(this, "zindex_changed");
};

/**
 * Returns the content of the InfoBox.
 * @returns {string}
 */
InfoBox.prototype.getContent = function () {

  return this.content_;
};

/**
 * Returns the geographic location of the InfoBox.
 * @returns {LatLng}
 */
InfoBox.prototype.getPosition = function () {

  return this.position_;
};

/**
 * Returns the zIndex for the InfoBox.
 * @returns {number}
 */
InfoBox.prototype.getZIndex = function () {

  return this.zIndex_;
};

/**
 * Shows the InfoBox.
 */
InfoBox.prototype.show = function () {

  this.isHidden_ = false;
  if (this.div_) {
    this.div_.style.visibility = "visible";
  }
};

/**
 * Hides the InfoBox.
 */
InfoBox.prototype.hide = function () {

  this.isHidden_ = true;
  if (this.div_) {
    this.div_.style.visibility = "hidden";
  }
};

/**
 * Adds the InfoBox to the specified map or Street View panorama. If <tt>anchor</tt>
 *  (usually a <tt>google.maps.Marker</tt>) is specified, the position
 *  of the InfoBox is set to the position of the <tt>anchor</tt>. If the
 *  anchor is dragged to a new location, the InfoBox moves as well.
 * @param {Map|StreetViewPanorama} map
 * @param {MVCObject} [anchor]
 */
InfoBox.prototype.open = function (map, anchor) {

  var me = this;

  if (anchor) {

    this.position_ = anchor.getPosition();
    this.moveListener_ = google.maps.event.addListener(anchor, "position_changed", function () {
      me.setPosition(this.getPosition());
    });
  }

  this.setMap(map);

  if (this.div_) {

    this.panBox_();
  }
};

/**
 * Removes the InfoBox from the map.
 */
InfoBox.prototype.close = function () {

  if (this.closeListener_) {

    google.maps.event.removeListener(this.closeListener_);
    this.closeListener_ = null;
  }

  if (this.eventListener1_) {

    google.maps.event.removeListener(this.eventListener1_);
    google.maps.event.removeListener(this.eventListener2_);
    google.maps.event.removeListener(this.eventListener3_);
    google.maps.event.removeListener(this.eventListener4_);
    this.eventListener1_ = null;
    this.eventListener2_ = null;
    this.eventListener3_ = null;
    this.eventListener4_ = null;
  }

  if (this.moveListener_) {

    google.maps.event.removeListener(this.moveListener_);
    this.moveListener_ = null;
  }

  if (this.contextListener_) {

    google.maps.event.removeListener(this.contextListener_);
    this.contextListener_ = null;
  }

  this.setMap(null);
};


//every document
jQuery(document).ready(function(jQuery) {
	jQuery('.map_canvas_post img').css('max-width','100%');
});

