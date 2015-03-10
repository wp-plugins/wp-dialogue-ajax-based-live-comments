<?php

/**
 * The dashboard-specific functionality of the plugin.
 *
 * @link       http://www.i3studioz.com/wp-dialogue
 * @since      1.0.0
 *
 * @package    WP_Dialogue
 * @subpackage WP_Dialogue/admin
 */

/**
 * The dashboard-specific functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the dashboard-specific stylesheet and JavaScript.
 *
 * @package    WP_Dialogue
 * @subpackage WP_Dialogue/admin
 * @author     WP Team @ i3studioz <developer@i3studioz.com>
 */
class Live_Comments_Admin {

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
     * @var      string    $plugin_name       The name of this plugin.
     * @var      string    $version    The version of this plugin.
     */
    public function __construct($plugin_name, $version) {

        $this->plugin_name = $plugin_name;
        $this->version = $version;
    }

    /**
     * Register the stylesheets for the Dashboard.
     *
     * @since    1.0.0
     */
    public function enqueue_styles() {

        /**
         * This function is provided for demonstration purposes only.
         *
         * An instance of this class should be passed to the run() function
         * defined in Live_Comments_Admin_Loader as all of the hooks are defined
         * in that particular class.
         *
         * The Live_Comments_Admin_Loader will then create the relationship
         * between the defined hooks and the functions defined in this
         * class.
         */
        $screen = get_current_screen();

        if ($screen->id == 'options-discussion') {
            wp_enqueue_style('wp-color-picker');

            wp_enqueue_style($this->plugin_name, plugin_dir_url(__FILE__) . 'css/live-comments-admin.css', array(), $this->version, 'all');
        }
    }

    /**
     * Register the JavaScript for the dashboard.
     *
     * @since    1.0.0
     */
    public function enqueue_scripts() {

        /**
         * This function is provided for demonstration purposes only.
         *
         * An instance of this class should be passed to the run() function
         * defined in Live_Comments_Admin_Loader as all of the hooks are defined
         * in that particular class.
         *
         * The Live_Comments_Admin_Loader will then create the relationship
         * between the defined hooks and the functions defined in this
         * class.
         */
        $screen = get_current_screen();

        if ($screen->id == 'options-discussion') {
            wp_enqueue_script($this->plugin_name, plugin_dir_url(__FILE__) . 'js/live-comments-admin.js', array('jquery', 'wp-color-picker'), $this->version, false);
        }
    }

    /**
     * Add new setting options for the discussion section to
     * allow admins to customize the plugin behavior
     */
    //add_action('admin_init', 'lc_settings');
    function lc_settings() {
        add_settings_section('lc_settings', __('WP Dialogue Settings', $this->plugin_name), array(&$this, 'lc_discussion_options'), 'discussion');

        add_settings_field(
                'lc_avatar_size', __('Avatar Size', $this->plugin_name), array(&$this, 'lc_get_setting_field'), 'discussion', 'lc_settings', array('name' => 'lc_avatar_size', 'type' => 'number', 'description' => __('Set sefault avatar size to be displayed in comments area.', $this->plugin_name)
                )
        );

        add_settings_field(
                'lc_form_position', __('Comment Form Position', $this->plugin_name), array(&$this, 'lc_get_setting_field'), 'discussion', 'lc_settings', array('name' => 'lc_form_position', 'type' => 'radio', 'options' => array('top' => __('Above Comments', $this->plugin_name), 'bottom' => __('Below Comments', $this->plugin_name)))
        );
        add_settings_field(
                'lc_enable_bootstrap', __('Enable Bootstrap For Form Fields', $this->plugin_name), array(&$this, 'lc_get_setting_field'), 'discussion', 'lc_settings', array('name' => 'lc_enable_bootstrap', 'type' => 'checkbox', 'description' => '<i>'.__('Enable bootstrap for form fields', $this->plugin_name).'</i>')
        );
        add_settings_field(
                'lc_enable_mentions', __('Enable Mentions', $this->plugin_name), array(&$this, 'lc_get_setting_field'), 'discussion', 'lc_settings', array('name' => 'lc_enable_mentions', 'type' => 'checkbox', 'description' => '<i>'.__('Overrides the comments nesting feature of WordPress with mentions. Uses the same settings though. The nesting depth will be used to check level of mentions allowed.', $this->plugin_name).'</i>')
        );

        add_settings_field(
                'lc_reply_text', __('Mention Link Text', $this->plugin_name), array(&$this, 'lc_get_setting_field'), 'discussion', 'lc_settings', array('name' => 'lc_reply_text', 'description' => '<i>Mention Link Text</i>')
        );

        add_settings_field(
                'lc_enable_mentions_email', __('Enable Mentions', $this->plugin_name), array(&$this, 'lc_get_setting_field'), 'discussion', 'lc_settings', array('name' => 'lc_enable_mentions_email', 'type' => 'checkbox', 'description' => '<i>'.__('Enable emails on mentions.', $this->plugin_name).'</i>')
        );

        add_settings_field(
                'lc_mention_mail_subject', __('Mention Mail Subject', $this->plugin_name), array(&$this, 'lc_get_setting_field'), 'discussion', 'lc_settings', array('name' => 'lc_mention_mail_subject', 'type' => 'text', 'description' => __('Subject for mention email.', $this->plugin_name)
                )
        );

        add_settings_field(
                'lc_mention_mail_markup', __('Email Markup', $this->plugin_name), array(&$this, 'lc_get_setting_field'), 'discussion', 'lc_settings', array('name' => 'lc_mention_mail_markup', 'type' => 'textarea', 'description' => __('Configure the markup for email to be sent on mentions. Click <a href="javascript:;" class="pop-up">here</a> to see available placeholders. <div class="toggle"><h3>Available Placeholders</h2>'
                    . '<p><strong>{{author}} || </strong><span class="description">Author\'s name with link</span></p>'
                    . '<p><strong>{{mentioned_author}} || </strong><span class="description">Mentioned Author\'s name</span></p>'
                    . '<p><strong>{{comment_post_link}} || </strong><span class="description">Link for the post</span></p>'
                    . '<p><strong>{{comment_date}} || </strong><span class="description">Date of comment</span></p>'
                    . '<p><strong>{{comment}} || </strong><span class="description">Comment Text</span></p>'
                    . '<p><strong>{{reply_link}} || </strong><span class="description">Reply link for the comment</span></p>'
                    . '<p><strong>{{mention_link}} || </strong><span class="description">Mention text for the comment</span></p>'
                    . '</div>', $this->plugin_name))
        );
        
        add_settings_field(
                'lc_enable_live_refresh', __('Enable Live Refresh', $this->plugin_name), array(&$this, 'lc_get_setting_field'), 'discussion', 'lc_settings', array('name' => 'lc_enable_live_refresh', 'type' => 'checkbox', 'description' => '<i>'.__('Enable live loading of comments.', $this->plugin_name).'</i>')
        );
        
        add_settings_field(
                'lc_refresh_interval', __('Comments Refresh Interval', $this->plugin_name), array(&$this, 'lc_get_setting_field'), 'discussion', 'lc_settings', array('name' => 'lc_refresh_interval', 'type' => 'number', 'min' => 5000, 'steps' => 1000, 'description' => __('Set refresh interval for fetching live comments. <i>1000 = 1 second</i>', $this->plugin_name)
                )
        );
        //
        add_settings_field(
                'lc_refresh_comments_text', __('Refresh Comments Text', $this->plugin_name), array(&$this, 'lc_get_setting_field'), 'discussion', 'lc_settings', array('name' => 'lc_refresh_comments_text', 'type' => 'text', 'description' => __('Link text for refresh all comments link.', $this->plugin_name)
                )
        );
        add_settings_field(
                'lc_highlight_color', __('New Comment Highlight Color', $this->plugin_name), array(&$this, 'lc_get_setting_field'), 'discussion', 'lc_settings', array('name' => 'lc_highlight_color', 'type' => 'color')
        );

        add_settings_field(
                'lc_no_more', __('No More Comments Message', $this->plugin_name), array(&$this, 'lc_get_setting_field'), 'discussion', 'lc_settings', array('name' => 'lc_no_more', 'type' => 'text', 'description' => __('Message text for no more coments.', $this->plugin_name)
                )
        );
        add_settings_field(
                'lc_comment_format', __('Choose Comment Format', $this->plugin_name), array(&$this, 'lc_get_setting_field'), 'discussion', 'lc_settings', array('name' => 'lc_comment_format', 'type' => 'radio', 'options' => array('div' => __('Div', $this->plugin_name), 'ul' => __('Unordered List', $this->plugin_name), 'ol' => __('Ordered List', $this->plugin_name)))
        );
        add_settings_field(
                'lc_comment_markup', __('Comment Markup', $this->plugin_name), array(&$this, 'lc_get_setting_field'), 'discussion', 'lc_settings', array('name' => 'lc_comment_markup', 'type' => 'textarea', 'description' => __('Configure the markup for individual comments to be displayed on front end. Click <a href="javascript:;" class="pop-up">here</a> to see available placeholders. <div class="toggle"><h3>Available Placeholders</h2>'
                    . '<p><strong>{{comment_id}} || </strong><span class="description">ID for the comment</span></p>'
                    . '<p><strong>{{avatar}} || </strong><span class="description">Comment author\'s gravatar</span></p>'
                    . '<p><strong>{{author}} || </strong><span class="description">Author\'s name with link</span></p>'
                    . '<p><strong>{{comment_post_link}} || </strong><span class="description">Link for the post</span></p>'
                    . '<p><strong>{{comment_date}} || </strong><span class="description">Date of comment</span></p>'
                    . '<p><strong>{{moderation_message}} || </strong><span class="description">Message for unapproved comments</span></p>'
                    . '<p><strong>{{comment}} || </strong><span class="description">Comment Text</span></p>'
                    . '<p><strong>{{reply_link}} || </strong><span class="description">Reply link for the comment</span></p>'
                    . '<p><strong>{{mention_link}} || </strong><span class="description">Mention text for the comment</span></p>'
                    . '</div>', $this->plugin_name))
        );

        add_settings_field(
                'lc_new_comments_note', __('New Comments Notification Markup', $this->plugin_name), array(&$this, 'lc_get_setting_field'), 'discussion', 'lc_settings', array('name' => 'lc_new_comments_note', 'type' => 'textarea', 'description' => __('Configure the markup for new comments notification displayed on front end. Click <a href="javascript:;" class="pop-up">here</a> to see available placeholders. <div class="toggle"><h3>Available Placeholders</h2>'
                    . '<p><strong>{{count}} || </strong><span class="description">Count of new comments.</span></p>'
                    . '</div>', $this->plugin_name))
        );

        add_settings_field(
                'lc_comment_section_header', __('Comment Section Header', $this->plugin_name), array(&$this, 'lc_get_setting_field'), 'discussion', 'lc_settings', array('name' => 'lc_comment_section_header', 'type' => 'textarea', 'description' => __('Configure the markup for comments section header displayed on front end. Click <a href="javascript:;" class="pop-up">here</a> to see available placeholders. <div class="toggle"><h3>Available Placeholders</h2>'
                    . '<p><strong>{{count}} || </strong><span class="description">Count of comments.</span></p>'
                    . '<p><strong>{{post_name}} || </strong><span class="description">Name of curently viewed post.</span></p>'
                    . '</div>', $this->plugin_name))
        );
        // Finally, we register the fields with WordPress
        register_setting('discussion', 'lc_avatar_size');

        register_setting('discussion', 'lc_form_position');
        
        register_setting('discussion', 'lc_enable_bootstrap');

        register_setting('discussion', 'lc_enable_mentions');

        register_setting('discussion', 'lc_reply_text');

        register_setting('discussion', 'lc_enable_mentions_email');

        register_setting('discussion', 'lc_mention_mail_subject');

        register_setting('discussion', 'lc_mention_mail_markup');
        
        register_setting('discussion', 'lc_enable_live_refresh');

        register_setting('discussion', 'lc_refresh_interval');
        
        register_setting('discussion', 'lc_refresh_comments_text');

        register_setting('discussion', 'lc_highlight_color');

        register_setting('discussion', 'lc_no_more');
        
        register_setting('discussion', 'lc_comment_format');

        register_setting('discussion', 'lc_comment_markup');

        register_setting('discussion', 'lc_new_comments_note');

        register_setting('discussion', 'lc_comment_section_header');
    }

    function lc_discussion_options() {
        echo '<p>' . __('', $this->plugin_name) . '</p>';
    }

    function lc_get_setting_field($args) {
        //print_r($args);
        //echo get_option($args['name']);
        $html = '<fieldset>';
        $type = isset($args['type']) ? $args['type'] : '';
        switch ($type) {
            case 'checkbox':
                $html .= '<label for="' . $args['name'] . '">';
                $html .= '<input type="checkbox" id="' . $args['name'] . '" name="' . $args['name'] . '" value="1" ' . checked(1, get_option($args['name']), false) . '/>';
                $html .= $args['description'] . '</label>';
                break;

            case 'radio':
                foreach ($args['options'] as $key => $value) {
                    $html .= '<label for="' . $args['name'] . '_' . $key . '">';
                    $html .= '<input type="radio" id="' . $args['name'] . '_' . $key . '" name="' . $args['name'] . '" value="' . $key . '" ' . checked($key, get_option($args['name']), false) . '/>';
                    $html .= $value . '</label><br />';
                }
                break;

            case 'number':
                $step = isset($args["steps"]) ? $args["steps"] : 1;
                $min = isset($args["min"]) ? $args["min"] : 1;
                $html = '<input type="number" min="' . $min . '" step="' . $step . '" id="' . $args['name'] . '" name="' . $args['name'] . '" value="' . get_option($args['name']) . '" />';
                $html .= '<label for="' . $args['name'] . '"> ' . $args['description'] . '</label>';
                break;

            case 'color':
                $html .= '<input type="text" class="color-field" id="' . $args['name'] . '" name="' . $args['name'] . '" value="' . get_option($args['name']) . '" />';
                //$html .= '<label for="' . $args['name'] . '"> ' . $args['description'] . '</label>';
                break;

            case 'textarea':
                $html .= '<p><label for="' . $args['name'] . '"> ' . isset($args['description']) ? $args['description'] : '' . '</label></p>';
                $html .= '<p><textarea cols="50" rows="10" class="large-text code" id="' . $args['name'] . '" name="' . $args['name'] . '">' . get_option($args['name']) . '</textarea></p>';
                break;

            default:

                $html .= '<input type="text" id="' . $args['name'] . '" name="' . $args['name'] . '" value="' . get_option($args['name']) . '" />';
                $html .= '<label for="' . $args['name'] . '"> ' . $args['description'] . '</label>';
                break;
        }

        $html .= '</fieldset>';

        echo $html;
    }

    function lc_toggle_nesting() {
        if (get_option('lc_enable_mentions')) {
            update_option('thread_comments', 1);
        } else if (get_option('thread_comments')) {
            update_option('lc_enable_mentions', 1);
        } else {
            update_option('lc_enable_mentions', null);
            update_option('thread_comments', null);
        }
    }

}
