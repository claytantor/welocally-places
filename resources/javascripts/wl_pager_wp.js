/*
 * copyright 2012 welocally. NO WARRANTIES PROVIDED
 */
function WELOCALLY_WpPager(cfg) {
	this.cfg;
	this.wrapper;

	this.init = function() {
		var error = this.initCfg(cfg);
		
		// Get current script object
		var script = jQuery('SCRIPT');
		script = script[script.length - 1];
		
		if(error){
			jQuery(script).parent().before('<div class="error">Error during configiration: '+error[0]+'</div>');
		} else {
			// Build Widget
			this.makeWrapper();	
			this.getMetadata();	
			this.load(1);	
			jQuery(script).parent().before(this.wrapper);		
		}
		
		return this;
	};

}

WELOCALLY_WpPager.prototype.initCfg = function(cfg) {
	var errors = [];
	if (!cfg) {
		errors.push("Please provide configuration for the widget.");
		cfg = {};
	}
	
	if (!cfg.table) {
		errors.push("A table name is required to make a query.");
	}
	
	if (!cfg.fields) {
		errors.push("Please specifiy which fields tou would like to query.");
	}
	
	if (!cfg.page) {
		cfg.page=1;
	}
	
	if (!cfg.pagesize) {
		cfg.pagesize=10;
	}
	
	if (!cfg.content) {
		errors.push("The content template is required.");
	}
	

	if (errors.length > 0)
		return errors;

	this.cfg = cfg;
};

WELOCALLY_WpPager.prototype.makeWrapper = function() {
	var _instance = this;
	// Build Widget
	var wrapper = jQuery('<div></div>');
	jQuery(wrapper).css('display','none');			
	jQuery(wrapper).attr('class','wl_pager_wrapper');
	jQuery(wrapper).attr('id','wl_pager_wrapper_'+_instance.cfg.id);
	
	this.statusArea = jQuery('<div></div>');
	jQuery(this.ajaxStatus).css('display','none');	
	jQuery(wrapper).append(this.statusArea);
	
	var pager_control_top = jQuery('<div></div>');
	jQuery(pager_control_top).attr('class','wl_pager_control');
	jQuery(pager_control_top).attr('id','wl_pager_control_top_'+_instance.cfg.id);
	jQuery(wrapper).append(pager_control_top);
		
	var content = jQuery('<div></div>');		
	jQuery(content).attr('class','wl_pager_content');
	jQuery(content).attr('id','wl_pager_content_'+_instance.cfg.id);
	jQuery(wrapper).append(content);
		
	this.wrapper = wrapper;
	
	return wrapper;
};

WELOCALLY_WpPager.prototype.getMetadata = function() {
	
	var _instance = this;
	
	var req = {
		action: 'get_metadata',
		table: _instance.cfg.table,
		fields: _instance.cfg.fields,
		filter: _instance.cfg.filter,
		orderBy: _instance.cfg.orderBy,
		pagesize: _instance.cfg.pagesize
	};
		   
	_instance.jqxhr = jQuery.ajax({
	  type: 'POST',		  
	  url: _instance.cfg.ajaxurl,
	  data: req,
	  beforeSend: function(jqXHR){
		_instance.jqxhr = jqXHR;
	  },
	  error : function(jqXHR, textStatus, errorThrown) {
		if(textStatus != 'abort'){
			_instance.setStatus(_instance.statusArea,'ERROR : '+textStatus, 'wl_error', false);
		}		
	  },		  
	  success : function(res, textStatus, jqXHR) {
		  var metadata = jQuery.parseJSON( res );
		  metadata.current = 1;
		  _instance.setupPaginationMetadata(metadata);
	  }
	});

};

WELOCALLY_WpPager.prototype.setupPaginationMetadata = function(metadata) {
	
	var _instance = this;
	_instance.metadata = metadata;
	jQuery(_instance.wrapper)
		.find('.wl_pager_control')
		.html(_instance.paginationInterfaceGenerator(_instance));

};


WELOCALLY_WpPager.prototype.pageHandler = function(event,ui) {
	var _instance = event.data.instance;
	var page = event.data.page;
	_instance.metadata.current = page;
	_instance.load(page);
	var paginationControl = _instance.paginationInterfaceGenerator(event.data.instance);
	jQuery(_instance.wrapper).find('.wl_pager_control').html(paginationControl);
	return false;
};

WELOCALLY_WpPager.prototype.bindPageLink = function(list, page, text) {
	var _instance = this;
	var link = jQuery('<li><a href="#">'+text+'</a></li>'); 
	jQuery(link).find('a').bind('click',{instance:_instance, page: page}, _instance.pageHandler);
	jQuery(list).append(link); 
};

WELOCALLY_WpPager.prototype.paginationInterfaceGenerator = function(instance) {
	
	var _instance = instance;
	
	var adjacents=2;
	var page = (_instance.metadata.current == 0 ? 1 : _instance.metadata.current);
	var start = (page - 1) * 25;
	var prev = page-1;
	var next = page+1;
	var lastpage = _instance.metadata.pages;
	var lpm1 = lastpage - 1;
	
	
	var pagination=jQuery('<div style="margin:0px;padding:0px;"></div>');
	
	if(lastpage > 1) {
		var list = jQuery('<ul class="pagination"></ul>');
		
		jQuery(list).append('<li class="wl_pager_pages details">Page '+page+' of '+lastpage+'</li>');
		
        if (lastpage < 7 + (adjacents * 2)){
        	for (counter = 1; counter <= lastpage; counter++)
        	{
              if (counter == page) {
            	  jQuery(list).append('<li><a class="current">'+counter+'</a></li>');
              } else {
            	  _instance.bindPageLink(list, counter, counter); 
              }
        	}
        	
        } else if(lastpage > 5 + (adjacents * 2)) { 
          if(page < 1 + (adjacents * 2)) {
            for (counter = 1; counter < 4 + (adjacents * 2); counter++)
            {
                if (counter == page) {
                	jQuery(list).append('<li><a class="current">'+counter+'</a></li>');
                } else {
                	 _instance.bindPageLink(list, counter, counter); 
                }
            }   
            jQuery(list).append('<li class="dot">...</li>');
            
            _instance.bindPageLink(list, lpm1, lpm1);  
            _instance.bindPageLink(list, lastpage, lastpage);             
            
          } else if(lastpage - (adjacents * 2) > page && page > (adjacents * 2)) {

        	  _instance.bindPageLink(list, 1, 1);  
        	  _instance.bindPageLink(list, 2, 2);  
        	  
        	  jQuery(list).append('<li class="dot">...</li>');
        	  
        	  for (counter = page - adjacents; counter <= page + adjacents; counter++)
        	  {
			    if (counter == page)
			    	jQuery(list).append("<li><a class='current'>"+counter+"</a></li>");
			    else {
			    	_instance.bindPageLink(list, counter, counter);  
			    }
			                         
			  }
        	  jQuery(list).append("<li class='dot'>..</li>");        	  
        	  _instance.bindPageLink(list, lpm1, lpm1);  
        	  _instance.bindPageLink(list, lastpage, lastpage);  
        	  
          } else {
        	  
        	  _instance.bindPageLink(list, 1, 1);  
        	  _instance.bindPageLink(list, 2, 2);  
        	  
        	  jQuery(list).append("<li class='dot'>..</li>");
        	  
        	  for (counter = lastpage - (2 + (adjacents * 2)); counter <= lastpage; counter++)
        	  {
					if (counter == page)
						jQuery(list).append("<li><a class='current'>"+counter+"</a></li>");
					else {
				    	_instance.bindPageLink(list, counter, counter);  
					}                
        	  }
			}
          
	        if (page < counter - 1){ 
	        	_instance.bindPageLink(list, next, 'Next');  
	        	_instance.bindPageLink(list, lastpage, 'Last'); 
	        	
		    } else {		    	
		    	jQuery(list).append("<li><a class='current'>Next</a></li>");
		    	jQuery(list).append("<li><a class='current'>Last</a></li>");
		    }   
	        
          			          
        }

        jQuery(pagination).append(list);        
	}
	
	return pagination;
	
};
	
WELOCALLY_WpPager.prototype.load = function(page) {
	
	var _instance = this;
	
	var req = {
		action: 'getpage',
		table: _instance.cfg.table,
		fields: _instance.cfg.fields,
		filter: _instance.cfg.filter,
		orderBy: _instance.cfg.orderBy,
		odd: _instance.cfg.odd,
		even: _instance.cfg.even,		
		pagesize: _instance.cfg.pagesize,
		page: page,
		content: _instance.cfg.content
	};
	
	jQuery(_instance.wrapper).show();	
	
	_instance.jqxhr = jQuery.ajax({
	  type: 'POST',		  
	  url: _instance.cfg.ajaxurl,
	  data: req,
	  beforeSend: function(jqXHR){
		_instance.jqxhr = jqXHR;
		_instance.setStatus(_instance.statusArea,'Loading...', 'wl_message', false);
	  },
	  error : function(jqXHR, textStatus, errorThrown) {
		if(textStatus != 'abort'){
			_instance.setStatus(_instance.statusArea,'ERROR : '+textStatus, 'wl_error', false);
		}		
	  },		  
	  success : function(res, textStatus, jqXHR) {
		  _instance.setStatus(_instance.statusArea,'', 'wl_message', false);
		  jQuery(_instance.wrapper).find('.wl_pager_content').html(res);
	  }
	});

};

WELOCALLY_WpPager.prototype.updateSearch = function(cfg) {
	this.initCfg(cfg);
	this.getMetadata();	
	this.load(1);	
};

WELOCALLY_WpPager.prototype.setStatus = function(statusArea, message, type, showloading){
	jQuery(statusArea).html('');
	jQuery(statusArea).removeClass();
	jQuery(statusArea).addClass(type);
	
	jQuery(statusArea).append(message);
	
	if(message != ''){
		jQuery(statusArea).show();
	} else {
		jQuery(statusArea).hide();
	}			
};

