<?php

/**
 * The public-facing functionality of the plugin.
 *
 * @link       http://www.i3studioz.com/wp-dialogue
 * @since      1.0.0
 *
 * @package    WP_Dialogue
 * @subpackage WP_Dialogue/public
 */

/**
 * The public-facing functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the dashboard-specific stylesheet and JavaScript.
 *
 * @package    Live Comments
 * @subpackage WP_Dialogue/public
 * @author     WP Team @ i3studioz <developer@i3studioz.com>
 */
class Live_Comments_Public {

    /**
     * The ID of this plugin.
     *
     * @since    1.0.0
     * @access   private
     * @var      string    $plugin_name    The ID of this plugin.
     */
    private $plugin_name;

    /**
     * The version of this plugin.
     *
     * @since    1.0.0
     * @access   private
     * @var      string    $version    The current version of this plugin.
     */
    private $version;

    /**
     * Initialize the class and set its properties.
     *
     * @since    1.0.0
     * @var      string    $plugin_name       The name of the plugin.
     * @var      string    $version    The version of this plugin.
     */
    public function __construct($plugin_name, $version) {

        $this->plugin_name = 'wp-dialogue';
        $this->version = '1.0.0';
    }

    /**
     * Register the stylesheets for the public-facing side of the site.
     *
     * @since    1.0.0
     */
    public function enqueue_styles() {
        if (is_singular() && comments_open()) {
            wp_enqueue_style($this->plugin_name, plugin_dir_url(__FILE__) . 'css/live-comments-public.css', array(), $this->version, 'all');
        }
    }

    /**
     * Register the JavaScripts for the public-facing side of the site.
     *
     * @since    1.0.0
     */
    public function enqueue_scripts() {
        if (is_singular() && comments_open()) {
            wp_enqueue_script('jquery');
            wp_enqueue_script('comment-reply');
            wp_enqueue_script('underscore');
            wp_enqueue_script('backbone');
            wp_enqueue_script($this->plugin_name . '-model', plugin_dir_url(__FILE__) . 'js/models/comment.js', array('jquery', 'comment-reply'), $this->version, true);
            wp_enqueue_script($this->plugin_name . '-collection', plugin_dir_url(__FILE__) . 'js/collections/comments.js', array('jquery', 'comment-reply'), $this->version, true);
            wp_enqueue_script($this->plugin_name . '-view', plugin_dir_url(__FILE__) . 'js/views/comments.js', array('jquery', 'comment-reply'), $this->version, true);
            wp_enqueue_script($this->plugin_name . '-app', plugin_dir_url(__FILE__) . 'js/app.js', array('jquery', 'comment-reply'), $this->version, true);

// Now we can localize the script with our data.
            $app_vars = array('db_comments' => $this->lc_get_db_comments(get_the_ID(), 0, false));
            wp_localize_script($this->plugin_name . '-app', 'app_vars', $app_vars);
        }
    }

    /**
     * override the comments template from live comments comment template
     * 
     * @global object $post
     * @param string $comment_template
     * @return string
     */
    public function lc_comments_template($comment_template) {
        global $post;
        if (!( is_singular() && ( have_comments() || 'open' == $post->comment_status ) )) {
            return;
        } else {
            return plugin_dir_path(__FILE__) . 'partials/live-comments-public-display.php';
        }
    }

    public function lc_get_db_comments($post_id = 0, $start_id = 0, $doing_ajax = true) {
        global $comment_depth, $current_user; //, $post;
        $commenter = wp_get_current_commenter();
//print_r($commenter);
        if ($post_id == 0 && isset($_GET['post_id']))
            $post_id = $_GET['post_id'];

        $args = array(
            'post_id' => $post_id,
            'order' => 'desc',
                //'status' => 'approve'
        );

        if (get_option('page_comments') && !(isset($_GET['type']) && ($_GET['type'] == 'newer' || $_GET['type'] == 'reload'))) {
            $args['number'] = get_option('comments_per_page');
        }

        if (isset($_GET['type'])) {
            $args['date_query'] = $this->lc_date_query($_GET['type']);
        }

        $comments = get_comments($args);
        $localized_comment = array();
        foreach ($comments as $comment) {
            if ($doing_ajax && isset($_GET['type']) && $_GET['type'] == 'newer' && ($comment->comment_author_email == $commenter['comment_author_email'] || $comment->comment_author_email == $current_user->user_email)) {
                continue;
            } else if ($comment->comment_approved == 0 && !($comment->comment_author_email == $commenter['comment_author_email'] || $comment->comment_author_email == $current_user->user_email)) {
                //var_dump($comment->comment_approved == 0 && ($comment->comment_author_email == $commenter['comment_author_email'] || $comment->comment_author_email == $current_user->user_email));
                continue;
            }

            $comment_array = $this->lc_get_comment_data($comment);

            if ($comment->comment_parent) {
                $comment_array['mention_link'] = $this->lc_prepare_mention_link($comment->comment_parent);
            }

            if (get_option('thread_comments')) {
                $comment_depth = $this->lc_get_comment_depth($comment->comment_ID);

                if ((!is_user_logged_in() && $comment->comment_author != $commenter['comment_author']) || (is_user_logged_in() && $comment->comment_author_email != $current_user->user_email)) {

                    $comment_array['reply_link'] = get_comment_reply_link(array('depth' => $comment_depth, 'max_depth' => get_option('thread_comments_depth'), 'reply_text' => get_option('lc_reply_text')), $comment->comment_ID, $comment->comment_post_ID);
                }
            }

            if (isset($_GET['type']) && $_GET['type'] == 'newer') {
                $comment_array['position'] = 'new';
            } elseif (isset($_GET['type']) && $_GET['type'] == 'older') {
                $comment_array['position'] = 'old';
            } else {
                $comment_array['position'] = 'initial';
            }

            $localized_comment[] = $comment_array;
        }
        if ($doing_ajax) {
            echo json_encode($localized_comment);
            die();
        } else {
            return $localized_comment;
        }
    }

    /**
     * 
     */
    function lc_date_query($type) {
        switch ($type) {
            case 'newer':
                $date_query = array('after' => $_GET['new_start']);
                break;
            case 'older':
                $date_query = array('before' => $_GET['old_start']);
                break;
            case 'reload':
                $date_query = array();
                $date_query['after'] = $_GET['old_start'];
                if(get_option('lc_enable_live_refresh')){
                    $date_query['before'] = $_GET['new_start'];
                }
                $date_query['inclusive'] = true;
                //$date_query = array('before' => $_GET['new_start'], 'after' => $_GET['old_start'], 'inclusive' => true);
                break;
            default:
                $date_query = array('after' => $_GET['new_start']);
        }

        return $date_query;
    }

    /**
     * Save comments to database
     * @global int $comment_depth
     */
    public function lc_add_comment_to_db() {
        global $comment_depth, $current_user; //, $post;
        $commenter = wp_get_current_commenter();
        $time = current_time('mysql');
        $post_vars = json_decode(file_get_contents("php://input"), true);

        nocache_headers();

        $comment_post_ID = isset($post_vars['comment_post_id']) ? (int) $post_vars['comment_post_id'] : 0;

        $post = get_post($comment_post_ID);

        if (empty($post->comment_status)) {
            /**
             * Fires when a comment is attempted on a post that does not exist.
             *
             * @since 1.5.0
             *
             * @param int $comment_post_ID Post ID.
             */
            do_action('comment_id_not_found', $comment_post_ID);
            exit;
        }

// get_post_status() will get the parent status for attachments.
        $status = get_post_status($post);

        $status_obj = get_post_status_object($status);

        if (!comments_open($comment_post_ID)) {
            /**
             * Fires when a comment is attempted on a post that has comments closed.
             *
             * @since 1.5.0
             *
             * @param int $comment_post_ID Post ID.
             */
            do_action('comment_closed', $comment_post_ID);
//wp_die(__('Sorry, comments are closed for this item.'));
            echo json_encode(array('error' => __('Sorry, comments are closed for this item.', $this->plugin_name)));
            die();
        } elseif ('trash' == $status) {
            /**
             * Fires when a comment is attempted on a trashed post.
             *
             * @since 2.9.0
             *
             * @param int $comment_post_ID Post ID.
             */
            do_action('comment_on_trash', $comment_post_ID);
            exit;
        } elseif (!$status_obj->public && !$status_obj->private) {
            /**
             * Fires when a comment is attempted on a post in draft mode.
             *
             * @since 1.5.1
             *
             * @param int $comment_post_ID Post ID.
             */
            do_action('comment_on_draft', $comment_post_ID);
            exit;
        } elseif (post_password_required($comment_post_ID)) {
            /**
             * Fires when a comment is attempted on a password-protected post.
             *
             * @since 2.9.0
             *
             * @param int $comment_post_ID Post ID.
             */
            do_action('comment_on_password_protected', $comment_post_ID);
            exit;
        } else {
            /**
             * Fires before a comment is posted.
             *
             * @since 2.8.0
             *
             * @param int $comment_post_ID Post ID.
             */
            do_action('pre_comment_on_post', $comment_post_ID);
        }

        $comment_author = ( isset($post_vars['author']) ) ? trim(sanitize_text_field(strip_tags($post_vars['author']))) : null;
        $comment_author_email = ( isset($post_vars['email']) ) ? trim(sanitize_email($post_vars['email'])) : null;
        $comment_author_url = ( isset($post_vars['website']) ) ? trim(sanitize_text_field($post_vars['website'])) : null;
        $comment_content = ( isset($post_vars['comment']) ) ? trim($post_vars['comment']) : null;


// If the user is logged in
        $user = wp_get_current_user();
        if ($user->exists()) {
            if (empty($user->display_name))
                $user->display_name = $user->user_login;
            $comment_author = wp_slash($user->display_name);
            $comment_author_email = wp_slash($user->user_email);
            $comment_author_url = wp_slash($user->user_url);
            if (current_user_can('unfiltered_html')) {
                if (!isset($post_vars['_wp_unfiltered_html_comment']) || !wp_verify_nonce($post_vars['_wp_unfiltered_html_comment'], 'unfiltered-html-comment_' . $comment_post_ID)
                ) {
                    kses_remove_filters(); // start with a clean slate
                    kses_init_filters(); // set up the filters
                }
            }
        } else {
            if (get_option('comment_registration') || 'private' == $status) {
//wp_die(__('Sorry, you must be logged in to post a comment.'));
                echo json_encode(array('error' => __('Sorry, you must be logged in to post a comment.', $this->plugin_name)));
                die();
            }
        }

        $comment_type = '';

        if (get_option('require_name_email') && !$user->exists()) {
            if (6 > strlen($comment_author_email) || '' == $comment_author) {
//wp_die(__('<strong>ERROR</strong>: please fill the required fields (name, email).'));
                echo json_encode(array('error' => __('<strong>ERROR</strong>: please fill the required fields (name, email).', $this->plugin_name)));
                die();
            } elseif (!is_email($comment_author_email)) {
//wp_die(__('<strong>ERROR</strong>: please enter a valid email address.'));
                echo json_encode(array('error' => __('<strong>ERROR</strong>: please enter a valid email address.', $this->plugin_name)));
                die();
            }
        }

        if ('' == $comment_content) {
//wp_die(__('<strong>ERROR</strong>: please type a comment.'));
            echo json_encode(array('error' => __('<strong>ERROR</strong>: please type a comment.', $this->plugin_name)));
            die();
        }

        $comment_parent = isset($post_vars['comment_parent']) ? absint($post_vars['comment_parent']) : 0;

        $commentdata = compact('comment_post_ID', 'comment_author', 'comment_author_email', 'comment_author_url', 'comment_content', 'comment_type', 'comment_parent', 'user_ID');

        $commentdata['comment_date'] = current_time('mysql');
        $commentdata['comment_date_gmt'] = current_time('mysql', 1);
        $commentdata['comment_author_IP'] = preg_replace('/[^0-9a-fA-F:., ]/', '', $_SERVER['REMOTE_ADDR']);
        $commentdata['comment_agent'] = isset($_SERVER['HTTP_USER_AGENT']) ? substr($_SERVER['HTTP_USER_AGENT'], 0, 254) : '';

        $commentdata = wp_filter_comment($commentdata);

        $commentdata['comment_approved'] = wp_allow_comment($commentdata);
        //echo wp_allow_comment($commentdata); die();
        $comment_id = wp_insert_comment($commentdata);
        if (!$comment_id) {
//wp_die(__("<strong>ERROR</strong>: The comment could not be saved. Please try again later."));
            echo json_encode(array('error' => __('<strong>ERROR</strong>: The comment could not be saved. Please try again later.', $this->plugin_name)));
            die();
        }

        $comment = get_comment($comment_id);
        if ($comment->comment_approved != 'spam') {
//$comment_depth = $this->lc_get_comment_depth($comment_id);
            $comment_data = $this->lc_get_comment_data($comment);

            if (get_option('thread_comments')) {
                $comment_depth = $this->lc_get_comment_depth($comment->comment_ID);
            }

            if ($comment->comment_parent) {

                $reply_link = get_comment_reply_link(array('depth' => $comment_depth, 'max_depth' => get_option('thread_comments_depth'), 'reply_text' => get_option('lc_reply_text')), $comment->comment_ID, $comment->comment_post_ID);

                $comment_data['mention_link'] = $this->lc_prepare_mention_link($comment->comment_parent);
                if (get_option('lc_enable_mentions_email')) {
                    $this->lc_send_mention_mail($comment, $reply_link);
                }
            }
        }

        /**
         * Perform other actions when comment cookies are set.
         *
         * @since 3.4.0
         *
         * @param object $comment Comment object.
         * @param WP_User $user   User object. The user may not exist.
         */
        do_action('set_comment_cookies', $comment, $user);

        echo json_encode($comment_data);

        die();
    }

    function lc_get_comment_data($comment) {
        return array(
            'comment_id' => $comment->comment_ID,
            'comment_post_id' => $comment->comment_post_ID,
            'comment_parent' => $comment->comment_parent,
            'comment_class' => comment_class('', $comment->comment_ID, $comment->comment_post_ID, false),
            'author' => $comment->comment_author,
            'email' => $comment->comment_author_email,
            'website' => $comment->comment_author_url,
            'avatar' => get_avatar($comment->comment_author_email, get_option('lc_avatar_size')),
            'comment_post_link' => esc_url(get_comment_link($comment->comment_ID)),
            'comment_iso_time' => date('c', strtotime($comment->comment_date)),
            'comment_date' => $comment->comment_date,
            'comment_date_readable' => date(get_option('date_format') . ' ' . get_option('time_format'), strtotime($comment->comment_date)),
            'comment' => apply_filters('the_content',$comment->comment_content),
            'moderation_required' => !$comment->comment_approved,
            'position' => 'new',
            'reply_link' => '',
            'mention_link' => ''
        );
    }

    /**
     * Send email on mentions
     */
    function lc_send_mention_mail($comment, $reply_link) {
        $mentioned = get_comment($comment->comment_parent);
        
        if(!$mentioned->comment_author_email)
            return false;
        
        $to = $mentioned->comment_author_email;
        $subject = get_option('lc_mention_mail_subject');

        $message = get_option('lc_mention_mail_markup');
        $message = str_replace('{{author}}', $comment->comment_author, $message);
        $message = str_replace('{{mentioned_author}}', $mentioned->comment_author, $message);
        $message = str_replace('{{comment_post_link}}', '<a href="'.esc_url(get_permalink($comment->comment_post_ID)).'">'.  get_the_title($comment->comment_post_ID).'</a>', $message);
        $message = str_replace('{{comment_date}}', date('d F Y', strtotime($comment->comment_date)), $message);
        $message = str_replace('{{comment}}', $comment->comment_content, $message);
        $message = str_replace('{{reply_link}}', $reply_link, $message);
        $message = str_replace('{{mention_link}}', $this->lc_prepare_mention_link($comment->comment_parent), $message);

        // carriage return type (we use a PHP end of line constant)
        $eol = PHP_EOL;

        // To send HTML mail, the Content-type header must be set
        $headers = 'MIME-Version: 1.0' . $eol;
        $headers .= 'Content-type: text/html; charset=UTF-8' . $eol;

        // Additional headers
        $headers .= 'From: ' . get_bloginfo('name') . ' <' . get_bloginfo('admin_email') . '>' . $eol;
        // @todo Mail it
        return mail($to, $subject, $message, $headers);
    }

    /**
     * 
     * @global object $wpdb
     * @param int $comment_id
     * @return type
     */
    function lc_get_comment_depth($comment_id, $count = 1) {
        global $wpdb;
        $parent = $wpdb->get_var("SELECT comment_parent FROM $wpdb->comments WHERE comment_ID = '$comment_id'");
//$count = 0;
        if ($parent == 0) {
            return $count;
        } else {
            $count += 1;
            return $this->lc_get_comment_depth($parent, $count);
        }
    }

    function lc_prepare_mention_link($comment_id) {
        //echo $comment = $comment_id;
        $mentioned = get_comment($comment_id);
        //print_r($mentioned);
        $link = '@' . $mentioned->comment_author;
        return $link;
    }

    /**
     * Add required hidden fields for logged in users
     */
    function lc_logged_user_hidden_fields() {
        global $current_user;
//print_r($current_user);



        $fields = '';
        $fields .= '<input type="hidden" value="' . $current_user->display_name . '" id="author" />';
        $fields .= '<input type="hidden" value="' . $current_user->user_email . '" id="email" />';
        $fields .= '<input type="hidden" value="' . $current_user->user_url . '" id="url" />';
        echo $fields;
    }

    /**
     * Global JavaScript object vatiables used by the app
     * @global object $current_user
     */
    function lc_global_js_vars() {
        if (is_singular() && comments_open()) {
            global $current_user;
            $post_id = get_the_ID();
            $interval = get_option('lc_refresh_interval');
            $highlight_color = get_option('lc_highlight_color');
            $no_more = get_option('lc_no_more');
            $total_comment = get_comments_number();
            $current_time = date('Y-m-d H:i:s'); //2015-02-27 13:11:57
            $live_refresh = get_option('lc_enable_live_refresh');
//print_r($new_start);
            echo '<script type="text/javascript">
             /* <![CDATA[ */
             var lc_vars = ' . json_encode(array('post_id' => $post_id, 'ajax_url' => admin_url('admin-ajax.php'), 'new_item_color' => $highlight_color, 'thread_comments' => get_option('thread_comments'), 'comment_order' => get_option('comment_order'), 'refresh_interval' => $interval, 'no_more_text' => __($no_more, $this->plugin_name), 'initial_count' => $total_comment, 'current_time' => $current_time, 'live_refresh' => $live_refresh)) .
            '/* ]]> */
            </script>';
        }
    }

    /**
     * Prepare comments markup
     */
    /* ####################### EXAMPLE MARKUP ###############################

      <li id="comment-<%= comment_id %>" <%= comment_class %>>
      <article id="div-comment-<%= comment_id %>" class="comment-body">
      <footer class="comment-meta">
      <% if(avatar){ %>
      <div class="comment-author vcard">
      <%= avatar %>
      </div>
      <% } %>
      <div class="comment-metadata">
      <cite class="fn">
      <% if(website){ %>
      <a href="<%= website %>" rel="external nofollow" class="url"><%= author %></a>
      <% } else { %>
      <%= author %>
      <% } %>
      </cite> on
      <a href="<%= comment_post_link %>">
      <time datetime="<%= comment_iso_time %>">
      <%= comment_date_readable %>
      </time>
      </a>
      </div>
      <% if(moderation_required){ %>
      <p class="comment-awaiting-moderation">Your comment is awaiting moderation.</p>
      <% } %>
      </footer>
      <div class="comment-content">
      <p><%= comment %></p>
      </div>
      <%= reply_link %>
      </article>
      <ol class="children"></ol>
      </li>

      ############################################################### */

    function lc_comments_markup() {
        if (is_singular() && comments_open()) {
            $markup = get_option('lc_comment_markup');
            $markup = str_replace('{{comment_id}}', '<%= comment_id %>', $markup);
            $markup = str_replace('{{avatar}}', '<% if(avatar){ %><%= avatar %><% } %>', $markup);
            $markup = str_replace('{{author}}', '<% if(website){ %><a href="<%= website %>" rel="external nofollow" class="url"><%= author %></a><% } else { %><%= author %><% } %>', $markup);
            $markup = str_replace('{{comment_post_link}}', '<%= comment_post_link %>', $markup);
            $markup = str_replace('{{comment_date}}', '<time datetime="<%= comment_iso_time %>"><%= comment_date_readable %></time>', $markup);
            $markup = str_replace('{{moderation_message}}', '<% if(moderation_required){ %><p class="comment-awaiting-moderation">' . __('Your comment is awaiting moderation.', $this->plugin_name) . '</p><% } %>', $markup);
            $markup = str_replace('{{comment}}', '<%= comment %>', $markup);
            $markup = str_replace('{{mention_link}}', '<% if(mention_link){ %><%= mention_link %>: <% } %>', $markup);
            $markup = str_replace('{{reply_link}}', '<%= reply_link %>', $markup);
            $markup = str_replace('{{children}}', '<ol class="children"></ol>', $markup);
            $comment_markup = '<script type="text/template" id="comments-template">';


            $comment_format = get_option('lc_comment_format');

            $bound = 'li';
            if ($comment_format == 'div') {
                $bound = 'div';
            }

            $comment_markup .= '<' . $bound . ' id="comment-<%= comment_id %>" <%= comment_class %>>';
            $comment_markup .= $markup;
//        $comment_markup .= '<div class="lc_review">'
//                           .'<div class="lc_thumb"><a class="lc_thumb_btn lc_thumb_up_btn"></a><span class="lc_count">123</span></div>'
//                           .'<div class="lc_thumb"><a class="lc_thumb_btn lc_thumb_down_btn"></a><span class="lc_count">333</span></div>'
//                           .'</div>';
            $comment_markup .= "</$bound>";
            $comment_markup .= '</script>';

            echo $comment_markup;
        }
    }

    /**
     * Prepare new comments notification markup
     */
    function lc_new_comments_notification_markup() {
        if (is_singular() && comments_open()) {
            $note_markup = '<script type="text/template" id="new-comments">';
            $note_markup .= '<% if(count > 0){ %>' . str_replace('{{count}}', '<%= count %>', get_option('lc_new_comments_note')) . '<% } %>';
            $note_markup .= '</script>';

            echo $note_markup;
        }
    }

    /**
     * Prepare new comments notification markup
     */
    function lc_comment_section_header() {
        if (is_singular() && comments_open()) {
            $markup = get_option('lc_comment_section_header');
            $markup = '<% if(count > 0){ %>' . str_replace('{{count}}', '<%= count %>', $markup) . '<% } %>';
            $markup = str_replace('{{post_name}}', get_the_title(), $markup);
            $note_markup = '<script type="text/template" id="comments-header">';
            $note_markup .= $markup;
            $note_markup .= '</script>';

            echo $note_markup;
        }
    }

}
