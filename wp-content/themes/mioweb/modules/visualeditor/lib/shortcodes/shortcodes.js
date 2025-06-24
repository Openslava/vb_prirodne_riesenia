(function() {
    tinymce.create('tinymce.plugins.MioWebShortcodes', {
        init : function(ed, url) {
            ed.addButton('mw_addshortcode', {
                title : ed.getLang('mwshortcodes.title'),
                cmd : 'mw_addshortcode',
                image : url + '/addshortcode.png'
            });
            
            ed.addCommand('mw_addshortcode', function() {
                var selected_text = ed.selection.getContent();
                var return_text = '';
                var editor=0;
                if(jQuery("#element_setting_post_id").length) editor=1;
                openCmsLightbox({ajax_action:'save_new_shortcode',footer:'hide',title:ed.getLang('mwshortcodes.title'),prefix:"mw_shortcodes",zindex:99999});
                jQuery.ajax({
                    type:'POST',
                    data:{"action":"open_shortcode_select","mw_editor":editor},
                    url: ajaxurl,
                    success: function(content) {
                       addContentCmsLightbox(content,{prefix:"mw_shortcodes"});           
                    }
              
                }); 
            });
 
        },
    });
    // Register plugin
    tinymce.PluginManager.add( 'mwshortcodes', tinymce.plugins.MioWebShortcodes );
})();