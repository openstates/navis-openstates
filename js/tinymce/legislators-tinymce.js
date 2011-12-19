(function($) {
    tinymce.create('tinymce.plugins.OpenStatesLegislators', {
        /**
         * Initializes the plugin, this will be executed after the plugin has been created.
         * This call is done before the editor instance has finished it's initialization so use the onInit event
         * of the editor instance to intercept that event.
         *
         * @param {tinymce.Editor} ed Editor instance that the plugin is initialized in.
         * @param {string} url Absolute URL to where the plugin is located.
         */
        init : function(ed, url) {
            // Register the command so that it can be invoked by using tinyMCE.activeEditor.execCommand('mceExample');
            ed.addCommand('openstates_legislators', function() {
                function getForm() {
                    var data = {
                        action: 'get_legislator_form'
                    };
                    $.post(window.ajaxurl, data, function(resp) {
                        window.legislator_search_dialog = $(resp)
                            .appendTo( $('body') )
                            .dialog({
                                title: "OpenStates: Legislators",
                                modal: true,
                                dialogClass: 'wp-dialog',
                                width: 550,
                            });
                        window.legislator_search_loaded = true;
                        window.legislator_search = new LegislatorSearch({
                            editor: ed,
                            apikey: window.sunlight_apikey,
                            el: window.legislator_search_dialog
                        });
                    });
                }
                
                if (window.legislator_search_loaded) {
                    window.legislator_search_dialog.dialog('open');
                } else {
                    getForm();
                }
            });

            // Register example button
            ed.addButton('openstates_legislators', {
                title : 'OpenStates: Legislators',
                cmd : 'openstates_legislators',
                image : url + '/legislators.png'
            });

            // Add a node change handler, selects the button in the UI when a image is selected
            ed.onNodeChange.add(function(ed, cm, n) {
                cm.setActive('openstates_legislators', n.nodeName == 'IMG');
            });
        },

        /**
         * Creates control instances based in the incomming name. This method is normally not
         * needed since the addButton method of the tinymce.Editor class is a more easy way of adding buttons
         * but you sometimes need to create more complex controls like listboxes, split buttons etc then this
         * method can be used to create those.
         *
         * @param {String} n Name of the control to create.
         * @param {tinymce.ControlManager} cm Control manager to use inorder to create new control.
         * @return {tinymce.ui.Control} New control instance or null if no control was created.
         */
        createControl : function(n, cm) {
            return null;
        },

        /**
         * Returns information about the plugin as a name/value array.
         * The current keys are longname, author, authorurl, infourl and version.
         *
         * @return {Object} Name/value array containing information about the plugin.
         */
        getInfo : function() {
            return {
                longname : 'Navis OpenStates Legislators Plugin',
                author : 'Chris Amico',
                authorurl : 'http://stateimpact.npr.org/',
                infourl : 'http://stateimpact.npr.org/',
                version : "1.0"
            };
        }
    });

    // Register plugin
    tinymce.PluginManager.add('openstates_legislators', tinymce.plugins.OpenStatesLegislators);
})(window.jQuery);

