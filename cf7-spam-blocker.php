<?php
/*
Plugin Name: CF7 Spam Blocker
Description: Blocks Contact Form 7 submissions containing disallowed keywords or links, with file-based logging and admin interface.
Version: 2.2
Author: Raw Hasan
*/

// Ensure all plugins are loaded before checking for CF7
add_action('plugins_loaded', function () {
    // Show admin notice if Contact Form 7 is not installed
    if (!defined('WPCF7_VERSION')) {
        add_action('admin_notices', function () {
            echo '<div class="notice notice-error"><p><strong>CF7 Spam Blocker</strong> requires Contact Form 7 to work. Please install and activate it.</p></div>';
        });
        return;
    }

    // Register plugin settings menu
    add_action('admin_menu', 'cf7_spam_blocker_settings_menu');
    add_action('admin_init', 'cf7_spam_blocker_register_settings');

    // Hook into form submission
    add_action('wpcf7_before_send_mail', 'cf7_spam_block_keywords_and_links');
});

// Settings Menu
function cf7_spam_blocker_settings_menu() {
    add_options_page('CF7 Spam Blocker', 'CF7 Spam Blocker', 'manage_options', 'cf7-spam-blocker', 'cf7_spam_blocker_settings_page');
}

// Register settings
function cf7_spam_blocker_register_settings() {
    register_setting('cf7_spam_blocker_settings', 'cf7_spam_blocked_keywords');
    register_setting('cf7_spam_blocker_settings', 'cf7_spam_block_links');
}

// Settings Page UI
function cf7_spam_blocker_settings_page() {
    ?>
    <div class="wrap">
        <h1>CF7 Spam Blocker Settings</h1>
        <form method="post" action="options.php">
            <?php settings_fields('cf7_spam_blocker_settings'); ?>
            <?php do_settings_sections('cf7_spam_blocker_settings'); ?>
            <table class="form-table">
                <tr valign="top">
                    <th scope="row">Blocked Keywords (comma-separated)</th>
                    <td><textarea name="cf7_spam_blocked_keywords" rows="5" cols="50"><?php echo esc_textarea(get_option('cf7_spam_blocked_keywords')); ?></textarea></td>
                </tr>
                <tr valign="top">
                    <th scope="row">Block Submissions with Links?</th>
                    <td><input type="checkbox" name="cf7_spam_block_links" value="1" <?php checked(1, get_option('cf7_spam_block_links'), true); ?> /></td>
                </tr>
            </table>
            <?php submit_button(); ?>
        </form>
    </div>
    <?php
}

// Spam Filtering Logic
function cf7_spam_block_keywords_and_links($contact_form) {
    $submission = WPCF7_Submission::get_instance();
    if (!$submission) return;

    $data = $submission->get_posted_data();
    $blocked_keywords = explode(',', get_option('cf7_spam_blocked_keywords', ''));
    $block_links = get_option('cf7_spam_block_links', false);
    $ip = $_SERVER['REMOTE_ADDR'];
    $time = date('Y-m-d H:i:s');

    foreach ($data as $field => $value) {
        foreach ($blocked_keywords as $keyword) {
            $keyword = trim($keyword);
            if ($keyword && stripos($value, $keyword) !== false) {
                $submission->set_status('spam');
                cf7_spam_blocker_log_event($time, $field, 'keyword', $keyword, $ip);
                return;
            }
        }

        if ($block_links && preg_match('/https?:\/\/\S+/i', $value)) {
            $submission->set_status('spam');
            cf7_spam_blocker_log_event($time, $field, 'link', 'http/https', $ip);
            return;
        }
    }
}

// Logging Function
function cf7_spam_blocker_log_event($time, $field, $type, $match, $ip) {
    $upload_dir = wp_upload_dir();
    $log_file = $upload_dir['basedir'] . '/cf7-spam-blocker/log.txt';

    if (!file_exists(dirname($log_file))) {
        wp_mkdir_p(dirname($log_file));
    }

    $line = sprintf("[%s] Blocked in field \"%s\" | Type: %s | Match: %s | IP: %s\n", $time, $field, $type, $match, $ip);
    file_put_contents($log_file, $line, FILE_APPEND | LOCK_EX);
}
