"use strict";
var app = app || {};
var $ = jQuery;
app.CommentView = Backbone.View.extend({
    el: $('#comments'),
    template: _.template($('#comments-template').html()),
    new_template: _.template($('#new-comments').html()),
    comment_section_header: _.template($('#comments-header').html()),
    events: {
        'submit form#commentform': 'saveComment',
        'click #load-new-comments': 'addNewComments',
        'click #load-old-comments': 'addPagedComments',
        'click #lc-refresh': 'reloadAll'
    },
    initialize: function (app_vars) {
        _.bindAll(this, 'render', 'saveComment', 'appendItem', 'getLiveComments', 'reloadAll', 'updateCommentHeader');
        this.$comment = this.$('#comment');
        this.$author = this.$('#author');
        this.$parent = this.$('#comment_parent');
        this.$email = this.$('#email');
        this.$website = this.$('#url');
        this.$comment_post_ID = this.$('#comment_post_ID');
        var comments_json = app_vars.db_comments; //$.parseJSON(app_vars.db_comments);
        this.collection = new app.CommentList(comments_json);
        this.collection.bind('add', this.appendItem);
        //this.collection.bind('reset', this.appendAll);
        this.collection.bind('add', this.updateCommentHeader);
        this.collection.meta('total_comments', lc_vars.initial_count);
        this.initializeLiveVars();
        this.initializePageVars();
        this.updateCommentHeader();
        this.collection.meta('read_post', lc_vars.post_id);
        this.collection.meta('read_type', 'newer');

        this.counter = 0;

        var self = this;
        this.liveLoader = setInterval(function () {
            if (Boolean(lc_vars.live_refresh)) {
                self.getLiveComments();
            }
        }, lc_vars.refresh_interval);


        this.render();
    },
    render: function () {

        var self = this;
        _(this.collection.models).each(function (comment) {
            self.appendItem(comment);
        }, this);

    },
    appendAll: function (models) {
        //console.log(models);
        this.collection.reset(models);
        this.restartLiveFetch();
        $('.comment-list', this.el).html('');
        var self = this;
        _(this.collection.models).each(function (comment) {
            self.appendItem(comment);
        }, this);

    },
    getLiveComments: function () {
        //console.log('here');
        //clearInterval(this.liveLoader);

        var self = this;
        this.collection.fetch({
            remove: false,
            silent: true,
            success: function (collection, response) {
                if (response.length > 0) {
                    $('#load-new-comments').html(self.new_template({count: response.length})).show('slow');
                }
                //console.log(response);
                //self.restartLiveFetch();
            },
            error: function (collection, response) {
                //console.log(response);
                //self.restartLiveFetch();
            }
        });
    },
    addPagedComments: function () {

        clearInterval(this.liveLoader);

        this.initializePageVars();

        var self = this;
        this.collection.fetch({
            remove: false,
            success: function (collection, response) {

                // console.log(response.length);
                self.restartLiveFetch();
                self.initializePageVars();
                if (response.length <= 0) {
                    $('.comment-navigation .nav-previous')
                            .html(lc_vars.no_more_text)
                            .fadeOut(function(){
                                $(this).remove();
                            });
                }
            },
            error: function (collection, response) {
                self.restartLiveFetch();
                //console.log(response);
            }
        });
    },
    reloadAll: function () {
        //console.log('reloadAll');

        clearInterval(this.liveLoader);
        this.collection.meta('read_type', 'reload');

        var self = this;
        this.collection.fetch({
            silent: true,
            success: function (collection, response) {

                //console.log(collection);
                self.appendAll(response);
            },
            error: function (collection, response) {
                self.restartLiveFetch();
                //console.log(response);
            }
        });
    },
    getAttributes: function () {

        return {
            comment_post_id: this.$comment_post_ID.val().trim(),
            comment_parent: this.$parent.val().trim(),
            comment_class: '',
            author: this.$author.val().trim(),
            email: this.$email.val().trim(),
            website: this.$website.val().trim(),
            avatar: '',
            comment_post_link: '',
            comment_iso_time: '',
            comment_date: '',
            comment_date_readable: '',
            comment: this.$comment.val().trim(),
            moderation_required: true,
            reply_link: '',
            position: '',
            mention_link: ''
        };

    },
    saveComment: function (e) {

        clearInterval(this.liveLoader);
        e.preventDefault();

        this.counter++;

        var self = this;
        var new_comment = new app.Comment();
        new_comment.save(this.getAttributes(),
                {
                    wait: true,
                    success: function (model, response) {
                        //console.log(response);
                        if (response.error && response.error.length > 0) {
                            $('<div/>').addClass("alert alert-danger")
                                    .html(response.error)
                                    .prependTo($("#respond"))
                                    .hide()
                                    .fadeIn(1000)
                                    .delay(3000)
                                    .fadeOut(function () {
                                        $(this).remove()
                                    });
                        } else {
                            var comment_json = new_comment.toJSON();
                            self.collection.meta('total_comments', parseInt(self.collection.meta('total_comments')) + 1);
                            self.collection.add(comment_json);
                            window.location.hash = 'comment-' + comment_json.comment_id;
                            $('#comment').val('');
                        }
                        self.restartLiveFetch();
                    },
                    error: function (model, response) {

                        $('<div/>').addClass("alert alert-danger")
                                .html(response.responseText)
                                .prependTo($("#respond"))
                                .hide()
                                .fadeIn(1000)
                                .delay(3000)
                                .fadeOut(function () {
                                    $(this).remove()
                                });
                        self.restartLiveFetch();
                        //console.log(response.responseText);
                    }
                }
        );

    },
    restartLiveFetch: function () {
        var self = this;
        self.initializeLiveVars();
        self.liveLoader = setInterval(function () {
            if (Boolean(lc_vars.live_refresh)) {
                self.getLiveComments();
            }
        }, lc_vars.refresh_interval);
    },
    appendItem: function (item) {

        var item_json = item.toJSON();
        //console.log(item_json);
        var type = item_json.position;
//        if (item_json.comment_parent != 0 && $('#comment-' + item_json.comment_parent).length > 0 && lc_vars.thread_comments == 1) {
//            $('#comment-' + item_json.comment_parent + ' > ol.children', this.el).append(this.template(item_json));
//            //} else if (type == 'old') {
//
//        } else 
        if (type == 'new') {
            if (lc_vars.comment_order == 'desc')
                $('.comment-list', this.el).prepend(this.template(item_json));
            else
                $('.comment-list', this.el).append(this.template(item_json));

            var $old_color = $('#div-comment-' + item_json.comment_id).css('background-color');
            $('#div-comment-' + item_json.comment_id).css('background-color', lc_vars.new_item_color);

            setTimeout(function () {
                $('#div-comment-' + item_json.comment_id).css('background-color', $old_color);
            }, 2000);

        } else {
            if (lc_vars.comment_order == 'desc')
                $('.comment-list', this.el).append(this.template(item_json));
            else
                $('.comment-list', this.el).prepend(this.template(item_json));
        }
    },
    updateCommentHeader: function () {
        //comment_section_header
        //alert(this.collection.meta('total_comments'));
        $('h2.comments-title').html(this.comment_section_header({count: this.collection.meta('total_comments')}));
    },
    addNewComments: function () {
        var self = this;
        _(this.collection.models).each(function (comment) {
            var comment_json = comment.toJSON();
            if (comment_json.comment_id > this.collection.meta('max_id')) {
                self.appendItem(comment);
                self.collection.meta('total_comments', parseInt(self.collection.meta('total_comments')) + 1);
            }
        }, this);
        this.updateCommentHeader();
        this.initializeLiveVars();
        $('#load-new-comments').html('').hide('slow');
    },
    initializeLiveVars: function () {
        var first = false;
        if (this.collection.length > 0) {
            var first = this.collection.max(function (model) {
                return model.get('comment_id');
            });
        }
        //console.log(first);
        this.collection.meta('read_type', 'newer');
        if (first) {
            this.collection.meta('new_start', first.get('comment_date'));
            this.collection.meta('max_id', first.get('comment_id'));
        } else {
            this.collection.meta('new_start', lc_vars.current_time);
            this.collection.meta('max_id', 0);
        }
    },
    initializePageVars: function () {
        var last = false;
        if (this.collection.length > 0) {
            var last = this.collection.min(function (model) {
                return model.get('comment_id');
            });
        }
        //console.log(last);
        this.collection.meta('read_type', 'older');
        if (last) {
            this.collection.meta('old_start', last.get('comment_date'));
        } else {
            this.collection.meta('old_start', lc_vars.current_time);
        }
    }
});