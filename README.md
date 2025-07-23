# 🛡️ CF7 Spam Blocker Plugin

This plugin blocks spam submissions in **Contact Form 7** (CF7) forms using regex and keyword-based filtering, with detailed logging.

**Known Issue: Form in not being submitted with CloudFlare. Keeps rotating. Need to fix.**

## ✅ Key Functionalities

### 1. **Regex-based Spam Filtering**
- Scans all CF7 form fields for suspicious content using predefined **regex patterns**.
- Blocks fields containing:
  - Email addresses in non-email fields
  - Links (e.g., `http://`, `https://`, `www.`)
  - HTML tags
  - Foreign alphabets (e.g., Cyrillic, Japanese, Arabic)
  - Encoded characters or emoji
  - Phone numbers and formatted numbers
- Patterns are defined in a PHP array named `$regex_checks`.

### 2. **Keyword-based Spam Filtering**
- Blocks messages containing blacklisted **keywords**.
- Keywords are stored in the WordPress options table under the key:
  
  ``cf7_spam_blocked_keywords``
  
- Supports:
  - Case-insensitive matching
  - Trimming for extra whitespace
- You can update the keyword list programmatically or via the database.

### 3. **Spam Logging**
- Every blocked submission is logged to a file:
  
  ``wp-content/uploads/cf7-spam-logs/spam-log.txt``

- Each entry includes:
  - Timestamp
  - Field name
  - Block type (Regex / Keyword)
  - Matched pattern or word
  - IP address

- Log entry format:

  ``[YYYY-MM-DD HH:MM:SS] Blocked in field "field_name" | Type: Regex | Match: http | IP: 123.45.67.89``

### 4. **Admin Feedback**
- Users receive the following generic error if their message is blocked:

    ````text
    There was an error trying to send your message. Please try again later.
    ````

- No spam details are disclosed to avoid filter circumvention.

### 5. **Plugin Activation Hook**
- On plugin activation:
- Initializes an empty array in the `cf7_spam_blocked_keywords` option if not already set.
- Ensures the logging directory `cf7-spam-logs` is created with write permissions.






## 🧠 Internal Mechanism

- Hooks into:

``wpcf7_before_send_mail``

- For each submission:
1. Iterates over all submitted fields.
2. Runs regex filters first.
3. If regex passes, runs keyword checks.
4. If any match is found:
   - Logs the attempt
   - Returns a generic error
   - Prevents email from sending





## 🧩 Possible Enhancements

- WP Admin settings page to manage keywords.
- Auto-purge or rotate logs.