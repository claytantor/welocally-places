<?php global $post; ?>
<script type="text/javascript">
jQuery(document).ready(function(jQuery) {	
	console.log('place finder ready');
});
</script>
<style type="text/css">
	
	
	
</style>
<body>
	<div>
		<div id="wl-place-finder" class="input-section action" style="display:inline-block" >
			<script type="text/javascript">
			
				var placeSelected = new WELOCALLY_PlaceWidget({}).init();
			    var cfg = { 
						id:'finder_1',
						showLetters: true,
						zoom:4, 
						imagePath: 'http://gaudi-vb/placehound/images',
				    	endpoint:'http://stage.welocally.com',
				    	showSelection: true,
				    	observers:[placeSelected],				
			    };
			    
			    var placesFinder = 
					  new WELOCALLY_PlaceFinderWidget(cfg)
				  		.init();
		  		
		  		//now register the display for the place
			    placeSelected.setWrapper(cfg, jQuery(placesFinder.getSelectedSection()));	
			
	
		     </script>		
		</div>		
	</div>
</body>