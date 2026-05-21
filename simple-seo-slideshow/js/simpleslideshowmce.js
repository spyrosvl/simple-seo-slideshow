(function() {  
    tinymce.create('tinymce.plugins.ssslide', {  
        init : function(ed, url) {  
        
            ed.addCommand('mcessslide', function() {
              ed.windowManager.open({
                file : url + '/ssslide_dialog.htm',
                width : 480 ,
                height : 380 ,
                inline : 1
              }, {
                plugin_url : url
              });
            });
            
            ed.addButton('ssslide', {  
                title : 'Add a Simple SEO Slideshow',  
                image : url+'/mceicon.png',  
                cmd : 'mcessslide'
            });  
        },  
        createControl : function(n, cm) {  
            return null;  
        },  
    });  
    tinymce.PluginManager.add('ssslide', tinymce.plugins.ssslide);  
})();  