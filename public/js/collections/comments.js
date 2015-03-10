"use strict";
var app = app || {};
app.CommentList = Backbone.Collection.extend({
    //which model

    model: app.Comment,
    initialize: function () {
        this._meta = {};
    },
    meta: function (prop, value) {
        if (value === undefined) {
            return this._meta[prop]
        } else {
            this._meta[prop] = value;
        }
    },
    actionURL: function (method, old_start, new_start, read_post, read_type) {

        switch (method) {
            case 'read':
                //console.log(read_start);
                return lc_vars.ajax_url+'?action=fetch_comment&post_id=' + read_post + '&old_start=' + old_start + '&new_start=' + new_start + '&type=' + read_type;

        }
//        'read': 'http://localhost/live-comments/wp-admin/admin-ajax.php?action=fetch_comment&post_id=' + this.read_post + '&read_start=' + this.read_start + '&type=' + this.read_type,
//        'create': 'http://localhost/live-comments/wp-admin/admin-ajax.php?action=add_comment',
//        'update': 'http://localhost/live-comments/wp-admin/admin-ajax.php?action=add_comment',
//        'delete': 'http://localhost/live-comments/wp-admin/admin-ajax.php?action=remove_comment'
    },
    sync: function (method, collection, options) {
        options = options || {};
        options.url = collection.actionURL(method.toLowerCase(), collection.meta('old_start'), collection.meta('new_start'), collection.meta('read_post'), collection.meta('read_type'));
        //console.log(method.toLowerCase());
        return Backbone.sync.apply(this, arguments);
    },
});