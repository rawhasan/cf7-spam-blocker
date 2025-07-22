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


    // Exit if CF7 is not active
    if ( ! defined('WPCF7_VERSION') ) {
        add_action('admin_notices', function() {
            echo '<div class="notice notice-error"><p><strong>CF7 Spam Blocker</strong> requires Contact Form 7 to work. Please install and activate it.</p></div>';
        });
        return;
    }


    // Register plugin settings menu in WP admin
    add_action('admin_menu', 'cf7_spam_blocker_settings_menu');
    // Register plugin options (keywords and link blocking)
    add_action('admin_init', 'cf7_spam_blocker_register_settings');

    function cf7_spam_blocker_settings_menu() {
        add_options_page(
            'CF7 Spam Blocker Settings',
            'CF7 Spam Blocker',
            'manage_options',
            'cf7-spam-blocker',
            'cf7_spam_blocker_settings_page'
        );
    }

    function cf7_spam_blocker_register_settings() {
        register_setting('cf7_spam_blocker_options', 'cf7_spam_blocker_keywords');
        register_setting('cf7_spam_blocker_options', 'cf7_block_links');
    }

// Display and handle the plugin admin settings page
    function cf7_spam_blocker_settings_page() {
        // Handle import
        // Handle import of plugin settings from JSON
        if (isset($_POST['cf7_import_settings']) && current_user_can('manage_options')) {
            if (!empty($_FILES['cf7_import_file']['tmp_name'])) {
                $import_data = file_get_contents($_FILES['cf7_import_file']['tmp_name']);
                $json = json_decode($import_data, true);
                if (is_array($json)) {
                    if (isset($json['keywords'])) {
                        update_option('cf7_spam_blocker_keywords', sanitize_text_field($json['keywords']));
                    }
                    if (isset($json['block_links'])) {
                        update_option('cf7_block_links', (int) $json['block_links']);
                    }
                    echo '<div class="updated"><p>Settings imported successfully.</p></div>';
                } else {
                    echo '<div class="error"><p>Invalid import file format.</p></div>';
                }
            }
        }

        echo '<div class="wrap"><h1>CF7 Spam Blocker Settings</h1>';

        echo '<form method="post" action="options.php">';
        settings_fields('cf7_spam_blocker_options');
        do_settings_sections('cf7_spam_blocker_options');

        echo '<table class="form-table">
            <tr valign="top">
                <th scope="row">Blocked Keywords</th>
                <td>
                    <textarea name="cf7_spam_blocker_keywords" rows="5" cols="50">' . esc_textarea(get_option('cf7_spam_blocker_keywords')) . '</textarea>
                    <p class="description">Enter comma-separated keywords (e.g. viagra, casino, forex)</p>
                </td>
            </tr>
            <tr valign="top">
                <th scope="row">Block Messages with Links</th>
                <td>
                    <input type="checkbox" name="cf7_block_links" value="1" ' . checked(1, get_option('cf7_block_links'), false) . ' />
                    <label for="cf7_block_links">Enable link blocking (http://, https://, www.)</label>
                </td>
            </tr>
        </table>';

        submit_button('Save Settings');
        echo '</form><hr>';

        echo '<h2>Export Settings</h2>
        <form method="post" action="">
            <input type="submit" name="cf7_export_settings" class="button button-secondary" value="Export Settings as JSON">
        </form>';

        // Handle export of plugin settings to JSON
        if (isset($_POST['cf7_export_settings'])) {
            $export_data = [
                'keywords'    => get_option('cf7_spam_blocker_keywords', ''),
                'block_links' => get_option('cf7_block_links', 0),
            ];
            $filename = 'cf7-spam-blocker-settings-' . date('Ymd') . '.json';
            header('Content-Type: application/json');
            header('Content-Disposition: attachment; filename="' . $filename . '"');
            echo json_encode($export_data, JSON_PRETTY_PRINT);
            exit;
        }

        echo '<h2>Import Settings</h2>
        <form method="post" enctype="multipart/form-data">
            <input type="file" name="cf7_import_file" accept=".json" required>
            <input type="submit" name="cf7_import_settings" class="button button-primary" value="Import Settings">
        </form>';

        echo '<hr><h2>Spam Block Log</h2>';
        $upload_dir = wp_upload_dir();
        $log_file = trailingslashit($upload_dir['basedir']) . 'cf7-spam-blocker.log';

        // Handle log file deletion
        if (isset($_POST['cf7_clear_log']) && file_exists($log_file)) {
            unlink($log_file);
            echo '<div class="updated"><p>Log file deleted successfully.</p></div>';
        }

        // Show log file contents if available
        if (file_exists($log_file)) {
            echo '<form method="post">';
            echo '<p><input type="submit" name="cf7_clear_log" class="button" value="Delete Log File"></p>';
            echo '</form>';

            $lines = file($log_file, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
            $lines = array_reverse($lines);
            echo '<div style="max-height:300px; overflow:auto; background:#fff; padding:10px; border:1px solid #ccc;"><pre>';
            foreach (array_slice($lines, 0, 100) as $line) {
                echo esc_html($line) . "
    ";
            }
            echo '</pre></div>';
        } else {
            echo '<p><em>No log entries found.</em></p>';
        }

        echo '</div>';
    }

    add_filter('wpcf7_validate_textarea*', 'cf7_spam_block_keywords_and_links', 10, 2);
    // Hook into validation for text fields (wildcard and regular)
    add_filter('wpcf7_validate_text*', 'cf7_spam_block_keywords_and_links', 10, 2);
    add_filter('wpcf7_validate_text', 'cf7_spam_block_keywords_and_links', 10, 2);
    // Hook into validation for textarea fields
    add_filter('wpcf7_validate_textarea', 'cf7_spam_block_keywords_and_links', 10, 2);

// Main validation function that checks input for spam keywords or links
    function cf7_spam_block_keywords_and_links($result, $tag) {
        $name = $tag['name'];

    if (true) {
            $value = isset($_POST[$name]) ? strtolower($_POST[$name]) : '';
            $keywords_raw = get_option('cf7_spam_blocker_keywords', '');
            $keywords = array_filter(array_map('trim', explode(',', strtolower($keywords_raw))));

            foreach ($keywords as $word) {
                // Check for presence of any blocked keyword
                if ($word && strpos($value, $word) !== false) {
                    cf7_spam_blocker_log_event($name, 'keyword', $word);
                    $result->invalidate($tag, "Your message contains disallowed words.");
                    return $result;
                }
            }

            if (get_option('cf7_block_links')) {
                $link_patterns = ['/https?:\/\/\S+/i', '/www\.\S+/i'];
                foreach ($link_patterns as $pattern) {
                // Match message against link patterns
                    if (preg_match($pattern, $value)) {
                        cf7_spam_blocker_log_event($name, 'link', $pattern);
                        $result->invalidate($tag, "Messages with links are not allowed.");
                        return $result;
                    }
                }
            }
        }

        return $result;
    }

// Log details of blocked message to a file
    function cf7_spam_blocker_log_event($field, $type, $match) {
        $upload_dir = wp_upload_dir();
        $log_file = trailingslashit($upload_dir['basedir']) . 'cf7-spam-blocker.log';

        $time = current_time('mysql');
        $ip = $_SERVER['REMOTE_ADDR'] ?? 'unknown';

        $line = sprintf('[%s] Blocked in field "%s" | Type: %s | Match: %s | IP: %s', $time, $field, $type, $match, $ip);

        // Append log entry to spam log file
        @file_put_contents($log_file, $line, FILE_APPEND | LOCK_EX);
    }
});
