<style>
#infobox_content table{ border: 1px; }
</style>
<script type="text/javascript" charset="utf-8">
var boxText = document.createElement("div");
boxText.className = "wl-map-infobox"; 
boxText.innerHTML = "none selected";

var infoboxOptions = {
			content: boxText
			,disableAutoPan: false
			,maxWidth: 0
			,pixelOffset: new google.maps.Size(0, 0)
			,zIndex: null
			,boxStyle: { 
			  	opacity: 0.85,
			 }
			,closeBoxMargin: "10px 2px 2px 2px"
			,closeBoxURL: "<?php echo wl_get_option('map_infobox_close') ?>"
			,infoBoxClearance: new google.maps.Size(1, 1)
			,isHidden: false
			,pane: "floatPane"
			,enableEventPropagation: false
		};
		
var ib = new InfoBox(infoboxOptions);

var infoboxWidgetOptions = {
			content: boxText
			,disableAutoPan: false
			,maxWidth: 0
			,pixelOffset: new google.maps.Size(0, 0)
			,zIndex: null
			,boxStyle: { 
			  	opacity: 0.85,
			 }
			,closeBoxMargin: "10px 2px 2px 2px"
			,closeBoxURL: "<?php echo wl_get_option('map_infobox_close') ?>"
			,infoBoxClearance: new google.maps.Size(1, 1)
			,isHidden: false
			,pane: "floatPane"
			,enableEventPropagation: false
		};

var ib_widget = new InfoBox(infoboxWidgetOptions);

</script>