(function($) {
    
    var TEMPLATE = '<h4><%= bill_id %></h4>' +
                   '<p><%= title %></p>' + 
                   '<p>State: <%= state.toUpperCase() %> | Chamber: <%= chamber %></p>' +
                   '<p><input type="button" class="button left" value="Insert left">' +
                   '<input type="button" class="button right" value="Insert right">';
    
    // models
    var Bill = Backbone.Model.extend({
        
        defaults: {
            state      : '',
            title      : '',
            session    : '',
            chamber    : '',
            bill_id    : '',
            type       : ''
        },
        
        initialize: function(attributes, options) {
            this.view = new BillView({ model: this });
            return this;
        }
    });
    
    // this exists to hold search params
    var Query = Backbone.Model.extend({
        defaults: {
            q          : '',
            state      : ''
        }
    });
    
    // collections
    var BillList = Backbone.Collection.extend({
        model: Bill,
        
        initialize: function(models, options) {
            this.apikey = options.apikey;
            this.params = options.params || {};
            return this;
        },
        
        url: function() {
            var base = "http://openstates.org/api/v1/bills/?";
            this.params.apikey = this.apikey;
            return base + $.param(this.params);
        }
    });
    
    // views
    var BillView = Backbone.View.extend({
        
        className: "bill result",
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
        },
        
        insertRight: function(e) {
            var shortcode = this.shortcode('right');
            this.editor.execCommand('mceInsertContent', false, shortcode);
        },
        
        shortcode: function(align) {
            var bill_id = this.model.get('bill_id');
            if (!bill_id) return "";
            return '[bill id="' + bill_id + '"' +
                   ' state=' + this.model.get('state') +
                   ' session="' + this.model.get('session') + '"' +
                   ' align=' + align + ']\n ';
        },
        
    });
    
    window.BillSearch = Backbone.View.extend({
        
        events: {
            'click input.search' : 'doSearch'
        },
        
        initialize: function(options) {
            _.bindAll(this)
            this.apikey = options.apikey;
            this.editor = options.editor;
            
            this.model = new Query();
            this.collection = new BillList([], options);
            this.collection.bind('reset', this.render);
                        
            Backbone.ModelBinding.bind(this, { all: 'name' });
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
                this.collection.each(function(bill) {
                    bill.view.editor = editor;
                    root.append(bill.view.el);
                });
                root.height('200px');
            }
            return this;
        }
    });
        
})(window.jQuery);