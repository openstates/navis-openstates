(function($) {
    
    var TEMPLATE = '<h4><%= full_name %></h4>' +
                   '<p>State: <%= state.toUpperCase() %> | Chamber: <%= chamber %></p>' +
                   '<p><%= party %> Party, <%= district %></p>' +
                   '<p><input type="button" class="button left" value="Insert left">' +
                   '<input type="button" class="button right" value="Insert right">';
    
    // models
    var Legislator = Backbone.Model.extend({
        
        defaults: {
            state      : '',
            full_name  : '',
            first_name : '',
            last_name  : '',
            chamber    : '',
            term       : '',
            district   : '',
            party      : '',
            leg_id     : ''
        },
        
        initialize: function(attributes, options) {
            this.view = new LegislatorView({ model: this });
            return this;
        }
    });
    
    // this exists to hold search params
    var Query = Backbone.Model.extend({
        defaults: {
            state      : '',
            first_name : '',
            last_name  : '',
            chamber    : '',
            district   : '',
            party      : ''
        }
    });
    
    // collections
    var LegislatorList = Backbone.Collection.extend({
        model: Legislator,
        
        initialize: function(models, options) {
            this.apikey = options.apikey;
            this.params = options.params || {};
            return this;
        },
        
        comparator: function(legislator) {
            return legislator.get('last_name');
        },
        
        url: function() {
            var base = "http://openstates.org/api/v1/legislators/?";
            this.params.apikey = this.apikey;
            return base + $.param(this.params);
        }
    });
    
    // views
    var LegislatorView = Backbone.View.extend({
        
        className: "legislator result",
        events: {
            'click input.left'  : 'insertLeft',
            'click input.right' : 'insertRight'
        },
        
        template: _.template(TEMPLATE),
        
        initialize: function(options) {
            _.bindAll(this);
            return this.render();
        },
        
        render: function() {
            $(this.el).html(this.template(this.model.toJSON()));
            return this;
        },
        
        insertLeft: function(e) {
            var shortcode = this.shortcode('left');
            this.editor.execCommand('mceInsertContent', false, shortcode);
            $(this.el).addClass('selected');
        },
        
        insertRight: function(e) {
            var shortcode = this.shortcode('right');
            this.editor.execCommand('mceInsertContent', false, shortcode);
            $(this.el).addClass('selected');
        },
        
        shortcode: function(align) {
            var leg_id = this.model.get('leg_id');
            if (!leg_id) return "";
            return "[legislator leg_id=" + leg_id + " align=" + align + "]";
        },
        
    });
    
    window.LegislatorSearch = Backbone.View.extend({
        
        events: {
            'click input.search' : 'doSearch'
        },
        
        initialize: function(options) {
            _.bindAll(this)
            this.apikey = options.apikey;
            this.editor = options.editor;
            
            this.model = new Query();
            this.collection = new LegislatorList([], options);
            this.collection.bind('reset', this.render);
                        
            Backbone.ModelBinding.bind(this, { all: 'class' });
            return this;
        },
        
        doSearch: function(e) {
            e.preventDefault();
            return this.search();
        },
        
        search: function(query) {
            var params = this.model.toJSON();
            _.extend(params, query);
            this.collection.params = params;
            this.collection.fetch({ dataType: 'jsonp' });
            return this;
        },
        
        render: function() {
            var root = this.$('.results').empty();
            var editor = this.editor;
            if (this.collection.length === 0) {
                root.text("No results");
            } else {
                this.collection.each(function(legislator) {
                    legislator.view.editor = editor;
                    root.append(legislator.view.el);
                });
                root.height(this.$('form').height());
            }
            return this;
        }
    });
        
})(window.jQuery);