/*
	copyright 2012 clay graham. NO WARRANTIES PROVIDED
*/

//http://localhost:8082/geodb/deal/1_0/search.json?q=*:*&loc=37.8113159_-122.26822449999997&radiusKm=30
function WELOCALLY_DealFinderWidget (cfg) {		

		this.wrapper;
		this.slides;
		this.list;
		this.item_width;
		this.left_value;
		this.run;
		this.endpont;
		
		this.init = function() {
			var error;
			// validate config
			if (!cfg) {
				error = "Please provide configuration for the widget";
				cfg = {};
			}
			
			// summary (optional) - the summary of the article
			// hostname (optional) - the name of the host to use
			if (!cfg.endpoint) {
				cfg.endpoint = 'http://stage.welocally.com';
			}
			
			this.endpoint = cfg.endpoint;
			
			// Get current script object
			var script = jQuery('SCRIPT');
			script = script[script.length - 1];
	
			// Build Widget
			this.wrapper = jQuery('<div></div>');
			jQuery(this.wrapper).css('display','none');			
			jQuery(this.wrapper).attr('class','welocally_deals_widget');
			jQuery(this.wrapper).attr('id','welocally_deals_widget');
			//jQuery(this.wrapper).html('welocally widget '+cfg.info+'<br/>');
			
			this.slides = jQuery('<div></div>').attr('id','wl_deals_slides');
			this.list = jQuery('<ul></ul>').attr('id','wl_slides_list');
			jQuery(this.slides).append(this.list);
			jQuery(this.wrapper).append(this.slides);			
			
			jQuery(script).parent().before(this.wrapper);
			
			return this;
		
		};
}

WELOCALLY_DealFinderWidget.prototype.setLocation = function(lat, lng) {

	var query = {
			q: '*:*',
			loc: lat+'_'+lng,
			radiusKm: 30
	};

	var surl = this.endpoint +
			'/geodb/deal/1_0/search.json?'+WELOCALLY.util.serialize(query)+"&callback=?";
	console.log(surl);
	
	var t = this;
	
	jQuery(this.wrapper).hide();
	jQuery(this.list).empty();
		
	jQuery.ajax({
		  url: surl,
		  dataType: "json",
		  success: function(data) {
			jQuery.each(data, function(i,item){						
				console.log('deal:'+item.location.name);
				
				var listItem = jQuery('<li></li>');
				
				//float left image
				var img = jQuery('<img/>').attr('src',item.mediumImageUrl).attr('class','wl_deal_img');
				jQuery(listItem).append(img);
				jQuery(listItem).append('<div class="wl_deal_summary"><a target="_new" href="'+item.url+'">'+item.title+'</a></div>');
				jQuery(listItem).append('<div class="wl_deal_location">'+item.location.name+', '+item.location.city+' '+item.location.state+'</div>')
							
							
				jQuery(t.list).append(listItem);	
			});
			//rotation speed and timer
			var speed = 5000; 
			if(t.run){
				clearInterval(t.run);
			}
 			t.run = setInterval(function(){t.next();}, speed);
			 
			//grab the width and calculate left value
			this.item_width = jQuery('#wl_deals_slides li').outerWidth(); 
			this.left_value = this.item_width * (-1); 
				 
			//move the last item before first item, just in case user click prev button
			jQuery('#wl_deals_slides li:first').before(jQuery('#wl_deals_slides li:last'));
			 
			//set the default item to the correct position 
			jQuery('#wl_deals_slides ul').css({'left' : this.left_value});
			
			if(data.length>0){
				jQuery(t.wrapper).show();
			}
													
		}
	});
	
};

WELOCALLY_DealFinderWidget.prototype.next = function() {
 //get the right position
 		console.log('next');
        var left_indent = parseInt(jQuery('#wl_deals_slides ul').css('left')) - this.item_width;
         
        //slide the item
        jQuery('#wl_deals_slides ul').animate({'left' : left_indent}, 200, function () {
             
            //move the first item and put it as last item
            jQuery('#wl_deals_slides li:last').after(jQuery('#wl_deals_slides li:first'));                  
             
            //set the default item to correct position
            jQuery('#wl_deals_slides ul').css({'left' : this.left_value});
         
        });
                        
};




