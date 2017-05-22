jQuery(document).ready(function($){
	tinymce.create('tinymce.plugins.bmf_plugin_plugin', {
		init: function(ed, url){
			ed.addCommand('bmf_plugin_insert_shortcode', function(){
				selected = tinyMCE.activeEditor.selection.getContent();
				if(selected){
					// our shortcode doesnt handle content
					content = selected + '[bmf_list]';
				} else {
					content = '[bmf_list]';
				}
				tinymce.execCommand('mceInsertContent', false, content);
			});
			ed.addButton('bmf_plugin_button', {
				title: 'Füge Shortcode für Mitfahrgelegenheiten ein',
				cmd: 'bmf_plugin_insert_shortcode',
				image: url + '/bmf_button.png'
			});
		},
	});
	tinymce.PluginManager.add('bmf_plugin_button', tinymce.plugins.bmf_plugin_plugin);
});
