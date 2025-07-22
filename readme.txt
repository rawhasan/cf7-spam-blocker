=== CF7 Spam Blocker ===
Contributors: rawhasan
Tags: contact form 7, spam, block, keywords, links, log, security
Requires at least: 5.2
Tested up to: 6.5
Requires PHP: 7.2
Stable tag: 2.2
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Block spam submissions in Contact Form 7 using user-defined keywords or link detection, with admin controls and log viewer. Lightweight, database-free.

== Description ==

Tired of spam submissions through Contact Form 7?

**CF7 Spam Blocker** allows you to:

- Define your own list of keywords to block
- Block any message that contains links (optional)
- View how many messages were blocked since activation
- View, clear, and export log files
- No database storage – all logging is file-based for performance
- Fully admin-configurable interface

Developed by [Nijhoom Tours](https://nijhoom.com) — award-winning cultural tours in Bangladesh.

== Installation ==

1. Upload the plugin files to `/wp-content/plugins/cf7-spam-blocker/` or install via WordPress plugin screen.
2. Activate the plugin through the 'Plugins' screen in WordPress.
3. Go to `Settings > CF7 Spam Blocker` to configure keywords or enable link blocking.

== Frequently Asked Questions ==

= Does this work without Contact Form 7? =
No. This plugin requires Contact Form 7 to function. A warning will be shown if it is not active.

= Does this plugin use a database? =
No. Logs are stored in a flat file (`cf7-spam-log.txt`) in the plugin folder.

= Where can I see how many messages were blocked? =
Go to `Settings > CF7 Spam Blocker` to view stats and logs.

== Screenshots ==

1. Settings screen with keyword and link blocking options.
2. Log viewer showing blocked message attempts.

== Changelog ==

= 2.2 =
* Added security hardening: CSRF protection, file validation
* Improved admin interface
* First public release

== Upgrade Notice ==

= 2.2 =
Security update – recommended for all users.