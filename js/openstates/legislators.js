(function($) {
    
    var TEMPLATE = '<h4><%= full_name %></h4>' +
                   '<p><%= state %> | <%= chamber %></p>' +
                   '<p><%= party %>, <%= district %></p>' +
                   '<p><input type="button" class="button left" value="Insert left">' +
                   '<input type="button" class="button right" value="Insert right">';
    
    // models
    var Legislator = Backbone.Model.extend({
        
        defaults: {
            state      : null,
            full_name  : null,
            first_name : null,
            last_name  : null,
            chamber    : null,
            term       : null,
            district   : null,
            party      : null,
            leg_id     : null
        },
        
        initialize: function(attributes, options) {
            this.view = new LegislatorView({ model: this });
            return this;
        }
    });
    
    // this exists to hold search params
    var Query = Backbone.Model.extend({
        
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
        
        template: _.template(TEMPLATE),
        
        initialize: function(options) {
            _.bindAll(this);
            return this.render();
        },
        
        render: function() {
            $(this.el).html(this.template(this.model.toJSON()));
            return this;
        }
    });
    
    window.LegislatorSearch = Backbone.View.extend({
        
        events: {
            'submit form' : 'doSearch'
        },
        
        initialize: function(options) {
            this.apikey = options.apikey;
            this.editor = options.editor;
            
            this.collection = new LegislatorList([], options);
            this.model = new Query();
            Backbone.ModelBinding.bind(this);
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
        }
    })
        
})(window.jQuery);