<?php

/**
 * Fired during plugin activation
 *
 * @link       http://www.i3studioz.com/wp-dialogue
 * @since      1.0.0
 *
 * @package    WP_Dialogue
 * @subpackage WP_Dialogue/includes
 */

/**
 * Fired during plugin activation.
 *
 * This class defines all code necessary to run during the plugin's activation.
 *
 * @since      1.0.0
 * @package    WP_Dialogue
 * @subpackage WP_Dialogue/includes
 * @author     WP Team @ i3studioz <developer@i3studioz.com>
 */
class Live_Comments_Activator {

    /**
     * Short Description. (use period)
     *
     * Long Description.
     *
     * @since    1.0.0
     */
    public static function activate() {
        self::lc_create_options();
    }

    /**
     * Create options with default values on activation of the plugin
     */
    public static function lc_create_options() {
        // Avatar Size
        if (!get_option('lc_avatar_size')) {
            add_option('lc_avatar_size', 64);
        }

        // Form Position
        if (!get_option('lc_form_position')) {
            add_option('lc_form_position', 'top');
            update_option('comment_order', 'desc');
        }

        // Enable Mentions
        if (!get_option('lc_enable_mentions')) {
            add_option('lc_enable_mentions', 1);
        }

        // Reply Text
        if (!get_option('lc_reply_text')) {
            add_option('lc_reply_text', 'Mention');
        }

        // Mention Email Format
        if (!get_option('lc_enable_mentions_email')) {
            add_option('lc_enable_mentions_email', 1);
        }

        // Mention Mail Subject
        if (!get_option('lc_mention_mail_subject')) {
            add_option('lc_mention_mail_subject', get_option('blogname') . ' Mention Alerts');
        }

        // Mention Mail Markup
        if (!get_option('lc_mention_mail_markup')) {

            $mail_markup = 'Hello {{mentioned_author}},<br />'
                    . '{{author}} has mentioned you in his recent reply to {{comment_post_link}}.<br />'
                    . 'He wrote,<br />'
                    . '<strong>{{mention_link}}</strong> {{comment}}<br />'
                    . 'Sincerely,<br />'
                    . get_option('blogname') . ' Team';

            add_option('lc_mention_mail_markup', $mail_markup);
        }

        // Comments Refresh Interval
        if (!get_option('lc_refresh_interval')) {
            add_option('lc_refresh_interval', 30000);
        }
        // Comments Refresh Interval
        if (!get_option('lc_enable_live_refresh')) {
            add_option('lc_enable_live_refresh', 1);
        }

        // Refresh comments link text
        if (!get_option('lc_refresh_comments_text')) {
            add_option('lc_refresh_comments_text', 'Refresh Comments');
        }

        // New Comments Highlight Color
        if (!get_option('lc_highlight_color')) {
            add_option('lc_highlight_color', '#dff0d8');
        }

        // Text For No Comments Found
        if (!get_option('lc_no_more')) {
            add_option('lc_no_more', 'No more comments');
        }

        // Text For No Comments Found
        if (!get_option('lc_comment_format')) {
            add_option('lc_comment_format', 'ul');
        }
        // Comments Markup
        if (!get_option('lc_comment_markup')) {

            $comment_markup = '<article id="div-comment-{{comment_id}}" class="comment-body">'
                    . '<footer class="comment-meta">'
                    . '<div class="comment-author vcard">{{avatar}}'
                    . '<b class="fn">{{author}}</b> <span class="says">says:</span>'
                    . '</div><!-- .comment-author -->'
                    . '<div class="comment-metadata">'
                    . '<a href="{{comment_post_link}}">{{comment_date}}</a>'
                    . '</div><!-- .comment-metadata -->'
                    . '{{moderation_message}}'
                    . '</footer><!-- .comment-meta -->'
                    . '<div class="comment-content">'
                    . '<strong>{{mention_link}}</strong> {{comment}}'
                    . '</div><!-- .comment-content -->'
                    . '<div class="reply">{{reply_link}}</div>'
                    . '</article>';

            add_option('lc_comment_markup', $comment_markup);
        }

        // New Comments Alert Text
        if (!get_option('lc_new_comments_note')) {
            add_option('lc_new_comments_note', '<span>{{count}} new comments</span>');
        }

        // Comment Section Header
        if (!get_option('lc_comment_section_header')) {
            add_option('lc_comment_section_header', '{{count}} thoughts on "{{post_name}}"');
        }
    }

}
