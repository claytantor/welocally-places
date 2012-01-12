(function() {
	
	
    tinymce.create('tinymce.plugins.welocally', {
        init : function(ed, url) {
            ed.addButton('Welocally', {
                title : 'Welocally',
                onclick : function() {
                     ed.selection.setContent('[welocally/]'); 
                }
            });
        },
        createControl : function(n, cm) {
            return null;
        },
    });
    tinymce.PluginManager.add('Welocally', tinymce.plugins.welocally);
})();