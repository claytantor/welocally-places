(function() {

    tinymce.create('tinymce.plugins.welocally', {
        init : function(ed, url) {
            ed.addButton('Welocally', {
                title : 'Welocally',
                onclick : function() {
            		if(selectedPlace != null && WELOCALLY.meta.post != null ){
                        ed.selection.setContent(WELOCALLY.places.tag.makePlaceTag(selectedPlace, WELOCALLY.meta.post)); 
            		}
                }
            });
        },
        createControl : function(n, cm) {
            return null;
        },
    });
    tinymce.PluginManager.add('Welocally', tinymce.plugins.welocally);
})();